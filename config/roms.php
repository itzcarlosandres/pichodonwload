<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modo Piloto Automático y Publicación Dosificada (Drip-Feed SEO)
    |--------------------------------------------------------------------------
    |
    | Controla la automatización del rastreo, anti-duplicados y publicación
    | por goteo continuo para evitar penalizaciones de Google.
    |
    */

    // Habilita o pausa el piloto automático completo
    'autopilot_enabled' => env('ROMS_AUTOPILOT_ENABLED', true),

    // Cantidad de posts a publicar por cada tanda del cron
    'posts_per_batch' => (int) env('ROMS_POSTS_PER_BATCH', 4),

    // Intervalo de horas entre cada publicación automática
    'batch_interval_hours' => (int) env('ROMS_BATCH_INTERVAL_HOURS', 2),

    // Reescritura / Generación con IA para no duplicar textos de la fuente
    'auto_ai_enrich' => env('ROMS_AUTO_AI_ENRICH', true),

    // Cantidad máxima de juegos a recolectar automáticamente en cada cosecha
    'auto_harvest_limit' => (int) env('ROMS_AUTO_HARVEST_LIMIT', 15),

    // Proveedores activos para auto-recolección
    'providers' => ['cdromance', 'romspedia', 'romsemu'],
];
