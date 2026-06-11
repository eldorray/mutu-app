@props(['name'])

@php
    $class = $attributes->get('class', 'w-5 h-5');
    $svgAttrs =
        'fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"';
@endphp

@switch($name)
    @case('home')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
    @break

    @case('search')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <circle cx="11" cy="11" r="8" />
            <line x1="21" x2="16.65" y1="21" y2="16.65" />
        </svg>
    @break

    @case('calendar')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <rect height="18" rx="2" ry="2" width="18" x="3" y="4" />
            <line x1="16" x2="16" y1="2" y2="6" />
            <line x1="8" x2="8" y1="2" y2="6" />
            <line x1="3" x2="21" y1="10" y2="10" />
        </svg>
    @break

    @case('wallet')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <rect height="14" rx="2" width="20" x="2" y="5" />
            <line x1="2" x2="22" y1="10" y2="10" />
        </svg>
    @break

    @case('star')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
    @break

    @case('message')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
    @break

    @case('settings')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <circle cx="12" cy="12" r="3" />
            <path
                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </svg>
    @break

    @case('arrow-up')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="12" x2="12" y1="19" y2="5" />
            <polyline points="5 12 12 5 19 12" />
        </svg>
    @break

    @case('arrow-down')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="12" x2="12" y1="5" y2="19" />
            <polyline points="19 12 12 19 5 12" />
        </svg>
    @break

    @case('trend-up')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="7" x2="17" y1="17" y2="7" />
            <polyline points="7 7 17 7 17 17" />
        </svg>
    @break

    @case('trend-down')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="7" x2="17" y1="7" y2="17" />
            <polyline points="17 7 17 17 7 17" />
        </svg>
    @break

    @case('clock')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
        </svg>
    @break

    @case('tag')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
            <line x1="7" x2="7.01" y1="7" y2="7" />
        </svg>
    @break

    @case('filter')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
        </svg>
    @break

    @case('download')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
    @break

    @case('plus')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="12" x2="12" y1="5" y2="19" />
            <line x1="5" x2="19" y1="12" y2="12" />
        </svg>
    @break

    @case('close')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="18" x2="6" y1="6" y2="18" />
            <line x1="6" x2="18" y1="6" y2="18" />
        </svg>
    @break

    @case('bars')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="4" x2="4" y1="21" y2="14" />
            <line x1="4" x2="4" y1="10" y2="3" />
            <line x1="12" x2="12" y1="21" y2="12" />
            <line x1="12" x2="12" y1="8" y2="3" />
            <line x1="20" x2="20" y1="21" y2="16" />
            <line x1="20" x2="20" y1="12" y2="3" />
            <line x1="1" x2="7" y1="14" y2="14" />
            <line x1="9" x2="15" y1="8" y2="8" />
            <line x1="17" x2="23" y1="16" y2="16" />
        </svg>
    @break

    @case('file')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" x2="8" y1="13" y2="13" />
            <line x1="16" x2="8" y1="17" y2="17" />
        </svg>
    @break

    @case('dollar')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <line x1="12" x2="12" y1="1" y2="23" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </svg>
    @break

    @case('users')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
    @break

    @case('chevron-down')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <polyline points="6 9 12 15 18 9" />
        </svg>
    @break

    @case('eye')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
    @break

    @case('eye-off')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path
                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
            <line x1="1" x2="23" y1="1" y2="23" />
        </svg>
    @break

    @case('mail')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
            <polyline points="22,6 12,13 2,6" />
        </svg>
    @break

    @case('lock')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
    @break

    @case('clipboard')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
        </svg>
    @break

    @case('check-circle')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
    @break

    @case('upload')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="17 8 12 3 7 8" />
            <line x1="12" x2="12" y1="3" y2="15" />
        </svg>
    @break

    @case('link')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
        </svg>
    @break

    @case('user')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    @break

    @case('leaf')
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
            <path d="M2 21c0-3 1.85-5.36 5.08-6" />
        </svg>
    @break

    @default
        <svg class="{{ $class }}" {!! $svgAttrs !!}>
            <circle cx="12" cy="12" r="10" />
        </svg>
@endswitch
