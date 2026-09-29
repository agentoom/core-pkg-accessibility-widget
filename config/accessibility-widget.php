<?php

return [
    /**
     * The default configuration for the accessibility widget.
     */
    'defaults' => [
        'contrast' => false,
        'saturation' => false,
        'monochrome' => false,
        'font_size' => 100, // percentage
        'dyslexic_font' => false,
        'reading_guide' => false,
        'high_cursor' => false,
        'highlight_links' => false,
        'highlight_titles' => false,
        'line_height' => 1.5,
        'letter_spacing' => 0,
        'stop_animations' => false,
        'screen_reader' => false,
        'text_reader' => false,
        'keyboard_navigation' => false,
        'voice_navigation' => false,
        'text_position' => false,
        'page_structure' => false,
        'hide_images' => false,
        'dictionary' => false,
        'tooltips' => false,
    ],

    /**
     * API route prefix.
     */
    'route_prefix' => 'api/accessibility',
];
