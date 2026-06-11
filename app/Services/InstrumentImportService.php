<?php

namespace App\Services;

use App\Models\AccreditationComponent;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorEvidenceSuggestion;
use App\Models\AccreditationInstrument;
use App\Models\AccreditationItem;
use App\Models\AccreditationRubric;
use App\Models\AccreditationRubricLevel;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Smalot\PdfParser\Parser as PdfParser;

class InstrumentImportService
{
    /**
     * Import instrument data from an Excel file.
     *
     * Expected Excel format (columns):
     * A: Komponen (number)
     * B: Butir (number)
     * C: Kode Indikator (e.g. 1.1.1)
     * D: Judul Indikator
     * E: Definisi
     * F: Rubrik Kurang (skor 1)
     * G: Rubrik Cukup Baik (skor 2)
     * H: Rubrik Baik (skor 3)
     * I: Rubrik Sangat Baik (skor 4)
     * J: Boleh N/A (ya/tidak) - optional
     * K: Saran Bukti (pisahkan dengan ;) - optional
     */
    public function importFromExcel(string $filePath, int $instrumentId): array
    {
        $reader = new XlsxReader();
        $reader->open($filePath);

        $rows = [];
        $isFirstRow = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue; // Skip header row
                }

                $cells = $row->getCells();
                $rowData = array_map(fn ($cell) => trim((string) $cell->getValue()), $cells);

                // Skip empty rows
                if (empty($rowData[2] ?? '')) {
                    continue;
                }

                $rows[] = [
                    'component_number' => (int) ($rowData[0] ?? 0),
                    'item_number' => (int) ($rowData[1] ?? 0),
                    'code' => $rowData[2] ?? '',
                    'title' => $rowData[3] ?? '',
                    'definition' => $rowData[4] ?? '',
                    'rubric_kurang' => $rowData[5] ?? '',
                    'rubric_cukup_baik' => $rowData[6] ?? '',
                    'rubric_baik' => $rowData[7] ?? '',
                    'rubric_sangat_baik' => $rowData[8] ?? '',
                    'is_na_allowed' => strtolower($rowData[9] ?? 'tidak') === 'ya',
                    'evidence_suggestions' => array_filter(array_map('trim', explode(';', $rowData[10] ?? ''))),
                ];
            }
            break; // Only first sheet
        }

        $reader->close();

        if (empty($rows)) {
            return ['success' => false, 'message' => 'File Excel kosong atau format tidak sesuai.', 'count' => 0];
        }

        return $this->saveToDatabase($rows, $instrumentId);
    }

    /**
     * Extract text from PDF for preview/manual processing.
     */
    public function extractFromPdf(string $filePath): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }

    /**
     * Try to auto-parse PDF text into structured data using regex patterns.
     */
    public function parseFromPdfText(string $text, int $instrumentId): array
    {
        $rows = [];
        $currentComponent = 0;
        $currentItem = 0;

        // Pattern: match indicator codes like 1.1.1, 2.5.3, 3.14.2
        $lines = explode("\n", $text);
        $i = 0;
        $totalLines = count($lines);

        while ($i < $totalLines) {
            $line = trim($lines[$i]);

            // Detect component headers (Komponen 1, 2, 3)
            if (preg_match('/^Komponen\s+(\d+)/i', $line, $m)) {
                $currentComponent = (int) $m[1];
                $i++;
                continue;
            }

            // Detect item/butir headers (Butir 1, Butir 14)
            if (preg_match('/^Butir\s+(\d+)/i', $line, $m)) {
                $currentItem = (int) $m[1];
                $i++;
                continue;
            }

            // Detect indicator code pattern
            if (preg_match('/^(\d+\.\d+\.\d+)\s*[:\-–]?\s*(.+)$/u', $line, $m)) {
                $code = $m[1];
                $title = $m[2];

                // Try to extract component and item from code
                $parts = explode('.', $code);
                if (count($parts) === 3) {
                    $currentComponent = (int) $parts[0];
                    $currentItem = (int) $parts[1];
                }

                $rows[] = [
                    'component_number' => $currentComponent,
                    'item_number' => $currentItem,
                    'code' => $code,
                    'title' => $title,
                    'definition' => '',
                    'rubric_kurang' => '',
                    'rubric_cukup_baik' => '',
                    'rubric_baik' => '',
                    'rubric_sangat_baik' => '',
                    'is_na_allowed' => false,
                    'evidence_suggestions' => [],
                ];
            }

            $i++;
        }

        if (empty($rows)) {
            return ['success' => false, 'message' => 'Tidak dapat mendeteksi indikator dari PDF. Gunakan format Excel untuk hasil lebih akurat.', 'count' => 0];
        }

        return $this->saveToDatabase($rows, $instrumentId);
    }

    /**
     * Save parsed rows to database.
     */
    private function saveToDatabase(array $rows, int $instrumentId): array
    {
        $count = 0;

        DB::transaction(function () use ($rows, $instrumentId, &$count) {
            $now = now();

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

            // Group by component and item
            $componentCache = [];
            $itemCache = [];
            $sortOrder = 0;

            foreach ($rows as $row) {
                $compNum = $row['component_number'];
                $itemNum = $row['item_number'];

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
                    ['item_id' => $itemCache[$itemKey], 'code' => $row['code']],
                    [
                        'title' => $row['title'],
                        'definition' => $row['definition'] ?: null,
                        'is_na_allowed' => $row['is_na_allowed'],
                        'is_contextual' => false,
                        'sort_order' => $sortOrder,
                    ]
                );

                // Create rubrics
                $rubricMap = [
                    'kurang' => $row['rubric_kurang'],
                    'cukup_baik' => $row['rubric_cukup_baik'],
                    'baik' => $row['rubric_baik'],
                    'sangat_baik' => $row['rubric_sangat_baik'],
                ];

                foreach ($rubricMap as $levelCode => $description) {
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
                foreach ($row['evidence_suggestions'] as $idx => $suggestion) {
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

        return ['success' => true, 'message' => "Berhasil mengimpor $count indikator.", 'count' => $count];
    }
}
