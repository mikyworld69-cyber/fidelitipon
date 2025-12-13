<?php

$type = $_GET["type"] ?? null;
$file = $_GET["file"] ?? null;

if (!$type || !$file) {
    http_response_code(400);
    exit("Bad request");
}

$base = "/var/data/uploads/";

$paths = [
    "qr"       => "qrs/",
    "comercio" => "comercios/"
];

if (!isset($paths[$type])) {
    http_response_code(400);
    exit("Invalid type");
}

$path = $base . $paths[$type] . basename($file);

if (!file_exists($path)) {
    http_response_code(404);
    exit("File not found");
}

$mime = mime_content_type($path);
header("Content-Type: $mime");
readfile($path);
exit;
