<?php
session_start();

if (!extension_loaded('gd')) {
    die('GD library not installed');
}

if (ob_get_length()) {
    ob_end_clean();
}

/* CAPTCHA SETTINGS */
$width  = 160;
$height = 50;
$length = 6;

$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$%';

/* Generate CAPTCHA */
$captcha = '';
for ($i = 0; $i < $length; $i++) {
    $captcha .= $characters[random_int(0, strlen($characters) - 1)];
}

$_SESSION['captcha'] = $captcha;

/* Create image */
$image = imagecreatetruecolor($width, $height);

/* Colors */
$bg_color   = imagecolorallocate($image, 100, 100, 140);
$text_color = imagecolorallocate($image, 255, 255, 255);
$noise_color= imagecolorallocate($image, 200, 200, 220);

imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

/* Noise dots */
for ($i = 0; $i < 120; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

/* Noise lines */
for ($i = 0; $i < 4; $i++) {
    imageline(
        $image,
        rand(0, $width),
        rand(0, $height),
        rand(0, $width),
        rand(0, $height),
        $noise_color
    );
}

/* TEXT SETTINGS */
$font_path = __DIR__ . '/assets/arial.ttf';
$font_size = 22; 
$angle     = rand(-5, 5);

/* Center text */
$bbox = imagettfbbox($font_size, 0, $font_path, $captcha);
$text_width  = $bbox[2] - $bbox[0];
$text_height = $bbox[1] - $bbox[7];

$x = ($width - $text_width) / 2;
$y = ($height + $text_height) / 2;

/* Draw text */
imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_path, $captcha);

/* Headers */
header("Content-Type: image/png");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

imagepng($image);
imagedestroy($image);
exit;
