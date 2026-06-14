<?php

function react_asset($entry) {
    $basePath     = $_SERVER['DOCUMENT_ROOT'] . '/faith/';
    $manifestPath = $basePath . 'frontend/dist/.vite/manifest.json';

    if (!file_exists($manifestPath)) return '';

    $manifest = json_decode(file_get_contents($manifestPath), true);
    return '/faith/frontend/dist/' . ($manifest[$entry]['file'] ?? '');
}

function react_asset_css($entry) {
    $basePath     = $_SERVER['DOCUMENT_ROOT'] . '/faith/';
    $manifestPath = $basePath . 'frontend/dist/.vite/manifest.json';

    if (!file_exists($manifestPath)) return '';

    $manifest = json_decode(file_get_contents($manifestPath), true);
    $cssFiles = $manifest[$entry]['css'] ?? [];

    $tags = '';
    foreach ($cssFiles as $css) {
        $tags .= '<link rel="stylesheet" href="/faith/frontend/dist/' . $css . '">' . "\n";
    }
    return $tags;
}
