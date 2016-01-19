<?php 
/**
 * This is a PHP skript to process images using PHP GD.
 *
 */

//
// Define constant values required by CImage, append slash
// Use DIRECTORY_SEPARATOR to make it work on both windows and unix.
//
define('IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('CACHE_PATH', __DIR__ . '/cache/');

include '../src/CImage/CImage.php';
new CImage();
