<?php
require_once 'session_config.php';
session_start();

// Check if GD is available
if (!extension_loaded('gd')) {
    // If GD is not available, generate a simple text CAPTCHA
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $captcha = '';
    for($i = 0; $i < 6; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    $_SESSION['captcha'] = $captcha;
    
    // Output a simple text response
    header('Content-Type: text/plain');
    echo $captcha;
    exit;
}

// Set the content type header to image/png
header('Content-Type: image/png');

// Create an image with dimensions 120x40
$image = imagecreatetruecolor(120, 40);

// Colors
$bg = imagecolorallocate($image, 245, 245, 245);
$text_color = imagecolorallocate($image, 44, 62, 80);
$noise_color = imagecolorallocate($image, 150, 150, 150);

// Fill background
imagefilledrectangle($image, 0, 0, 120, 40, $bg);

// Add random dots
for($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, 120), rand(0, 40), $noise_color);
}

// Add random lines
for($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $noise_color);
}

// Generate random string
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$captcha = '';
for($i = 0; $i < 6; $i++) {
    $captcha .= $chars[rand(0, strlen($chars) - 1)];
}

// Store the CAPTCHA text in session
$_SESSION['captcha'] = $captcha;

// Add the text to image using built-in font
$font_size = 5; // Built-in font size (1-5)
$x = 15;
for($i = 0; $i < strlen($captcha); $i++) {
    imagechar($image, $font_size, $x + ($i * 15), 10, $captcha[$i], $text_color);
}

// Output the image
imagepng($image);

// Free up memory
imagedestroy($image);
