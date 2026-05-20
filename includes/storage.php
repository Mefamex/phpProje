<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function load_json(string $filename, array $default): array
{
    $path = DATA_DIR . '/' . $filename;
    if (!file_exists($path)) {
        save_json($filename, $default);
        return $default;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return $default;
    }

    return $data;
}

function save_json(string $filename, array $data): bool
{
    $path = DATA_DIR . '/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    $fp = fopen($path, 'c+');
    if ($fp === false) {
        return false;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}
