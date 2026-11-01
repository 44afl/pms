<?php

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function GetPost($name) {
    if (isset($_POST[$name]) && is_string($_POST[$name])) {
        return trim($_POST[$name]);
    }
    return '';
}

function redirect_to_list() {
    header('Location: index.php', true, 303);
    exit;
}

function delete_product_image($filename) {
    if (!$filename || basename($filename) !== $filename) {
        return;
    }

    $path = __DIR__ . '/../uploads/' . $filename;
    if (is_file($path) && !@unlink($path)) {
        error_log('Nu s-a putut elimina imaginea: ' . $filename);
    }
}
