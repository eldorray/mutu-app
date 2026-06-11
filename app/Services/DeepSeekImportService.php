<?php

namespace App\Services;

use App\Models\AccreditationComponent;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorEvidenceSuggestion;
use App\Models\AccreditationItem;
use App\Models\AccreditationRubric;
use App\Models\AccreditationRubricLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class DeepSeekImportService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.deepseek.com';

    public function __construct()
    {
        $this->apiKey = env('DEEPSEEK_API_KEY', '');
        $this->model = env('DEEPSEEK_MODEL', 'deepseek-chat');
    }

    /**
     * Import from PDF using DeepSeek AI to parse and structure the content.
     */
    public function importFromPdf(string $filePath, int $instrumentId): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API Key DeepSeek belum dikonfigurasi di file .env (DEEPSEEK_API_KEY).', 'count' => 0];
        }

        // Step 1: Extract text from PDF
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        if (empty(trim($text))) {
            return ['success' => false, 'message' => 'PDF tidak mengandung teks yang bisa dibaca (mungkin berupa scan/gambar).', 'count' => 0];
        }

        Log::info('DeepSeek Import: PDF text extracted', ['length' => strlen($text)]);

        // Step 2: Split text into chunks and process each
        $chunkSize = 20000;
        $chunks = str_split($text, $chunkSize);
        $allIndicators = [];

        foreach ($chunks as $index => $chunk) {
            Log::info("DeepSeek Import: Processing chunk " . ($index + 1) . "/" . count($chunks));

            $result = $this->parseWithAI($chunk, $index + 1, count($chunks));

            if (isset($result['error'])) {
                // If first chunk fails, return error. Otherwise continue with what we have.
                if (empty($allIndicators)) {
                    return ['success' => false, 'message' => $result['error'], 'count' => 0];
                }
                Log::warning("DeepSeek Import: Chunk " . ($index + 1) . " failed, continuing with existing data", ['error' => $result['error']]);
                break;
            }

            if (! empty($result['indicators'])) {
                $allIndicators = array_merge($allIndicators, $result['indicators']);
            }
        }

        if (empty($allIndicators)) {
            return ['success' => false, 'message' => 'AI tidak berhasil mendeteksi indikator dari dokumen ini.', 'count' => 0];
        }

        // Step 3: Save to database
        return $this->saveToDatabase($allIndicators, $instrumentId);
    }

    /**
     * Send extracted text to DeepSeek API for structuring.
     */
    private function parseWithAI(string $text, int $chunkNumber = 1, int $totalChunks = 1): array
    {
        $chunkInfo = $totalChunks > 1 ? " (bagian $chunkNumber dari $totalChunks)" : '';

        $prompt = <<<PROMPT
Kamu adalah parser dokumen instrumen akreditasi sekolah/madrasah Indonesia. Dari teks berikut{$chunkInfo}, extract semua indikator dan rubrik penilaian ke dalam format JSON.

Format output JSON yang diharapkan (HANYA output JSON, tanpa teks lain):
{
  "indicators": [
    {
      "component_number": 1,
      "item_number": 1,
      "code": "1.1.1",
      "title": "Judul indikator",
      "definition": "Definisi/penjelasan indikator",
      "is_na_allowed": false,
      "rubrics": {
        "kurang": "Deskripsi rubrik level Kurang (skor 1)",
        "cukup_baik": "Deskripsi rubrik level Cukup Baik (skor 2)",
        "baik": "Deskripsi rubrik level Baik (skor 3)",
        "sangat_baik": "Deskripsi rubrik level Sangat Baik (skor 4)"
      },
      "evidence_suggestions": ["Saran bukti 1", "Saran bukti 2"]
    }
  ]
}

Aturan:
- component_number diambil dari digit pertama kode (1.x.x = komponen 1, 2.x.x = komponen 2, 3.x.x = komponen 3)
- item_number diambil dari digit kedua kode (x.1.x = butir 1, x.5.x = butir 5)
- Jika rubrik tidak ditemukan untuk suatu level, isi dengan string kosong ""
- Jika ada indikator yang boleh N/A, set is_na_allowed = true
- Output HANYA JSON valid, tanpa markdown, tanpa penjelasan
- Jika tidak ada indikator dalam teks ini, output: {"indicators": []}

Teks dokumen:
---
{$text}
---
PROMPT;

        try {
            Log::info('DeepSeek Import: Sending request to API', ['model' => $this->model, 'text_length' => strlen($text)]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(180)->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah parser dokumen akreditasi. Output HANYA JSON valid tanpa markdown code block.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
                'max_tokens' => 32000,
                'stream' => false,
            ]);

            Log::info('DeepSeek Import: Response status', ['status' => $response->status()]);

            if (! $response->successful()) {
                $errorBody = $response->body();
                Log::error('DeepSeek Import: API error', ['status' => $response->status(), 'body' => $errorBody]);
                return ['error' => "DeepSeek API error (HTTP {$response->status()}): " . substr($errorBody, 0, 200)];
            }

            $content = $response->json('choices.0.message.content', '');

            // Some DeepSeek models put output in reasoning_content
            if (empty($content)) {
                $content = $response->json('choices.0.message.reasoning_content', '');
            }

            if (empty($content)) {
                Log::error('DeepSeek Import: Empty response content', ['response' => substr($response->body(), 0, 500)]);
                return ['error' => 'DeepSeek mengembalikan response kosong. Model mungkin membutuhkan max_tokens lebih besar.'];
            }

            Log::info('DeepSeek Import: Response received', ['content_length' => strlen($content)]);

            // Extract JSON from response (might be wrapped in ```json ... ```)
            $jsonContent = $content;
            if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $matches)) {
                $jsonContent = $matches[1];
            }

            // Try to find JSON object in the content
            if (! str_starts_with(trim($jsonContent), '{')) {
                // Try to find first { and last }
                $start = strpos($jsonContent, '{');
                $end = strrpos($jsonContent, '}');
                if ($start !== false && $end !== false) {
                    $jsonContent = substr($jsonContent, $start, $end - $start + 1);
                }
            }

            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('DeepSeek Import: JSON parse error', ['error' => json_last_error_msg(), 'content_preview' => substr($content, 0, 500)]);
                return ['error' => 'Gagal parse JSON dari response AI: ' . json_last_error_msg()];
            }

            if (! isset($data['indicators']) || ! is_array($data['indicators'])) {
                Log::error('DeepSeek Import: No indicators in response', ['keys' => array_keys($data)]);
                return ['error' => 'Response AI tidak mengandung key "indicators".'];
            }

            return ['indicators' => $data['indicators']];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepSeek Import: Connection timeout', ['message' => $e->getMessage()]);
            return ['error' => 'Koneksi ke DeepSeek timeout. Coba lagi.'];
        } catch (\Exception $e) {
            Log::error('DeepSeek Import: Exception', ['message' => $e->getMessage()]);
            return ['error' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Save AI-parsed data to database.
     */
    private function saveToDatabase(array $indicators, int $instrumentId): array
    {
        $count = 0;

        DB::transaction(function () use ($indicators, $instrumentId, &$count) {
            // Ensure rubric levels exist
            $levels = ['kurang' => 1, 'cukup_baik' => 2, 'baik' => 3, 'sangat_baik' => 4];
            $labels = ['kurang' => 'Kurang', 'cukup_baik' => 'Cukup Baik', 'baik' => 'Baik', 'sangat_baik' => 'Sangat Baik'];

            $levelIds = [];
            foreach ($levels as $code => $score) {
                $level = AccreditationRubricLevel::firstOrCreate(
                    ['code' => $code],
                    ['label' => $labels[$code], 'score_value' => $score, 'sort_order' => $score]
                );
                $levelIds[$code] = $level->id;
            }

            $componentCache = [];
            $itemCache = [];
            $sortOrder = AccreditationIndicator::max('sort_order') ?? 0;

            foreach ($indicators as $row) {
                $compNum = (int) ($row['component_number'] ?? 1);
                $itemNum = (int) ($row['item_number'] ?? 1);
                $code = $row['code'] ?? '';

                if (empty($code)) {
                    continue;
                }

                // Get or create component
                if (! isset($componentCache[$compNum])) {
                    $component = AccreditationComponent::firstOrCreate(
                        ['instrument_id' => $instrumentId, 'number' => $compNum],
                        ['name' => "Komponen $compNum", 'sort_order' => $compNum]
                    );
                    $componentCache[$compNum] = $component->id;
                }

                // Get or create item
                $itemKey = "$compNum-$itemNum";
                if (! isset($itemCache[$itemKey])) {
                    $item = AccreditationItem::firstOrCreate(
                        ['component_id' => $componentCache[$compNum], 'number' => $itemNum],
                        ['title' => "Butir $itemNum", 'sort_order' => $itemNum]
                    );
                    $itemCache[$itemKey] = $item->id;
                }

                $sortOrder++;

                // Create indicator
                $indicator = AccreditationIndicator::updateOrCreate(
                    ['item_id' => $itemCache[$itemKey], 'code' => $code],
                    [
                        'title' => $row['title'] ?? $code,
                        'definition' => $row['definition'] ?? null,
                        'is_na_allowed' => (bool) ($row['is_na_allowed'] ?? false),
                        'is_contextual' => false,
                        'sort_order' => $sortOrder,
                    ]
                );

                // Create rubrics
                $rubrics = $row['rubrics'] ?? [];
                foreach (['kurang', 'cukup_baik', 'baik', 'sangat_baik'] as $levelCode) {
                    $description = $rubrics[$levelCode] ?? '';
                    if (! empty($description)) {
                        AccreditationRubric::updateOrCreate(
                            [
                                'indicator_id' => $indicator->id,
                                'rubric_level_id' => $levelIds[$levelCode],
                                'context' => null,
                            ],
                            ['description' => $description]
                        );
                    }
                }

                // Create evidence suggestions
                $suggestions = $row['evidence_suggestions'] ?? [];
                foreach ($suggestions as $idx => $suggestion) {
                    if (! empty($suggestion)) {
                        AccreditationIndicatorEvidenceSuggestion::firstOrCreate(
                            ['indicator_id' => $indicator->id, 'name' => $suggestion],
                            ['sort_order' => $idx + 1]
                        );
                    }
                }

                $count++;
            }
        });

        return ['success' => true, 'message' => "Berhasil mengimpor $count indikator menggunakan AI (DeepSeek).", 'count' => $count];
    }
}
