<?php

require_once dirname(__DIR__) . '/config.php';

function getBaseUrl() {
    return BASE_URL;
}

function generateSlug($text) {
    // Convert the text to lowercase
    $text = strtolower($text);
    
    // Remove special characters and replace with dashes
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    
    // Replace multiple dashes with single dash
    $text = preg_replace('/-+/', '-', $text);
    
    // Remove dashes from beginning and end
    $text = trim($text, '-');
    
    return $text;
}

function getPostUrl($post_id, $title) {
    return getBaseUrl() . '/id/' . $post_id . '/' . generateSlug($title);
}

function getCategoryUrl($category_id, $name) {
    return getBaseUrl() . '/category/' . $category_id . '/' . generateSlug($name);
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
