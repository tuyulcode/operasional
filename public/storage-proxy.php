<?php

$rel = ltrim($_GET['path'] ?? '', '/');
$base = realpath(__DIR__ . '/../storage/app/public/');

if ($base === false || $rel === '') {
    http_response_code(404);
    exit('File tidak ditemukan');
}

$full = realpath($base . DIRECTORY_SEPARATOR . $rel);

$basePrefix = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $base), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$fullPrefix = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $full), DIRECTORY_SEPARATOR);

if (
    $full === false
    || ! str_starts_with(strtolower($fullPrefix), strtolower($basePrefix))
    || ! is_file($full)
) {
    http_response_code(404);
    exit('File tidak ditemukan');
}

$mime = function_exists('mime_content_type') ? mime_content_type($full) : 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full));
header('Content-Disposition: inline; filename="' . basename($full) . '"');
readfile($full);
exit;
