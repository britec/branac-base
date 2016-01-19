<?php
/**
 * 
 *
 */
class CImage {
    /**
    * Members
    */

    /**
    * Constructor to process images using PHP GD.
    *
    *
    */
    public function __construct() {
        //
        // Ensure error reporting is on
        //
        error_reporting(-1);              // Report all type of errors
        ini_set('display_errors', 1);     // Display all errors 
        ini_set('output_buffering', 0);   // Do not buffer outputs, write directly



        $maxWidth = $maxHeight = 2000;


        /**
         * Display error message.
         *
         * @param string $message the error message to display.
         */
        function errorMessage($message) {
          header("Status: 404 Not Found");
          die('img.php says 404 - ' . htmlentities($message));
        }



        /**
         * Display log message.
         *
         * @param string $message the log message to display.
         */
        function verbose($message) {
          echo "<p>" . htmlentities($message) . "</p>";
        }



        /**
         * Output an image together with last modified header.
         *
         * @param string $file as path to the image.
         * @param boolean $verbose if verbose mode is on or off.
         */
        function outputImage($file, $verbose) {
          $info = getimagesize($file);
          !empty($info) or errorMessage("The file doesn't seem to be an image.");
          $mime   = $info['mime'];

          $lastModified = filemtime($file);  
          $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

          if($verbose) {
            verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
            verbose("Memory limit: " . ini_get('memory_limit'));
            verbose("Time is {$gmdate} GMT.");
          }

          if(!$verbose) header('Last-Modified: ' . $gmdate . ' GMT');
          if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
            if($verbose) { verbose("Would send header 304 Not Modified, but its verbose mode."); exit; }
            header('HTTP/1.0 304 Not Modified');
          } else {  
            if($verbose) { verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
            header('Content-type: ' . $mime);  
            readfile($file);
          }
          exit;
        }



        /**
         * Sharpen image as http://php.net/manual/en/ref.image.php#56144
         * http://loriweb.pair.com/8udf-sharpen.html
         *
         * @param resource $image the image to apply this filter on.
         * @return resource $image as the processed image.
         */
        function sharpenImage($image) {
          $matrix = array(
            array(-1,-1,-1,),
            array(-1,16,-1,),
            array(-1,-1,-1,)
          );
          $divisor = 8;
          $offset = 0;
          imageconvolution($image, $matrix, $divisor, $offset);
          return $image;
        }

        /**
         * Create new image and keep transparency
         *
         * @param resource $image the image to apply this filter on.
         * @return resource $image as the processed image.
         */
        function createImageKeepTransparency($width, $height) {
            $img = imagecreatetruecolor($width, $height);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            return $img;
        }



        //
        // Get the incoming arguments
        //
        $src        = isset($_GET['src'])     ? $_GET['src']      : null;
        $verbose    = isset($_GET['verbose']) ? true              : null;
        $saveAs     = isset($_GET['save-as']) ? $_GET['save-as']  : null;
        $quality    = isset($_GET['quality']) ? $_GET['quality']  : 60;
        $ignoreCache = isset($_GET['no-cache']) ? true           : null;
        $newWidth   = isset($_GET['width'])   ? $_GET['width']    : null;
        $newHeight  = isset($_GET['height'])  ? $_GET['height']   : null;
        $cropToFit  = isset($_GET['crop-to-fit']) ? true : null;
        $sharpen    = isset($_GET['sharpen']) ? true : null;
        $filter    = isset($_GET['filter']) ? $_GET['filter'] : null;

        $pathToImage = realpath(IMG_PATH . $src);



        //
        // Validate incoming arguments
        //
        is_dir(IMG_PATH) or errorMessage('The image dir is not a valid directory.');
        is_writable(CACHE_PATH) or errorMessage('The cache dir is not a writable directory.');
        isset($src) or errorMessage('Must set src-attribute.');
        preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or errorMessage('Filename contains invalid characters.');
        substr_compare(IMG_PATH, $pathToImage, 0, strlen(IMG_PATH)) == 0 or errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
        is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg', 'gif')) or errorMessage('Not a valid extension to save image as');
        is_null($filter) or in_array($filter, array('grs', 'sep')) or errorMessage('Not a valid filter');
        is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or errorMessage('Quality out of range');
        is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $maxWidth) or errorMessage('Width out of range');
        is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $maxHeight) or errorMessage('Height out of range');
        is_null($cropToFit) or ($cropToFit and $newWidth and $newHeight) or errorMessage('Crop to fit needs both width and height to work');



        //
        // Start displaying log if verbose mode & create url to current image
        //
        if($verbose) {
          $query = array();
          parse_str($_SERVER['QUERY_STRING'], $query);
          unset($query['verbose']);
          $url = '?' . http_build_query($query);


          echo <<<EOD
<html lang='en'>
<meta charset='UTF-8'/>
<title>img.php verbose mode</title>
<h1>Verbose mode</h1>
<p><a href=$url><code>$url</code></a><br>
<img src='{$url}' /></p>
EOD;
        }



        //
        // Get information on the image
        //
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);
        !empty($imgInfo) or errorMessage("The file doesn't seem to be an image.");
        $mime = $imgInfo['mime'];

        if($verbose) {
          $filesize = filesize($pathToImage);
          verbose("Image file: {$pathToImage}");
          verbose("Image information: " . print_r($imgInfo, true));
          verbose("Image width x height (type): {$width} x {$height} ({$type}).");
          verbose("Image file size: {$filesize} bytes.");
          verbose("Image mime type: {$mime}.");
        }



        //
        // Calculate new width and height for the image
        //
        $aspectRatio = $width / $height;

        if($cropToFit && $newWidth && $newHeight) {
          $targetRatio = $newWidth / $newHeight;
          $cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
          $cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
          if($verbose) { verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}."); }
        }
        else if($newWidth && !$newHeight) {
          $newHeight = round($newWidth / $aspectRatio);
          if($verbose) { verbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); }
        }
        else if(!$newWidth && $newHeight) {
          $newWidth = round($newHeight * $aspectRatio);
          if($verbose) { verbose("New height is known {$newHeight}, width is calculated to {$newWidth}."); }
        }
        else if($newWidth && $newHeight) {
          $ratioWidth  = $width  / $newWidth;
          $ratioHeight = $height / $newHeight;
          $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
          $newWidth  = round($width  / $ratio);
          $newHeight = round($height / $ratio);
          if($verbose) { verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
        }
        else {
          $newWidth = $width;
          $newHeight = $height;
          if($verbose) { verbose("Keeping original width & heigth."); }
        }



        //
        // Creating a filename for the cache
        //
        $parts          = pathinfo($pathToImage);
        $fileExtension  = $parts['extension'];
        $saveAs         = is_null($saveAs) ? $fileExtension : $saveAs;
        $quality_       = is_null($quality) ? null : "_q{$quality}";
        $cropToFit_     = is_null($cropToFit) ? null : "_cf";
        $sharpen_       = is_null($sharpen) ? null : "_s";
        $filter_        = is_null($filter) ? null : "_f{$filter}";
        $dirName        = preg_replace('/\//', '-', dirname($src));
        $cacheFileName = CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$newWidth}_{$newHeight}{$quality_}{$cropToFit_}{$sharpen_}{$filter_}.{$saveAs}";
        $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

        if($verbose) { verbose("Cache file is: {$cacheFileName}"); }



        //
        // Is there already a valid image in the cache directory, then use it and exit
        //
        $imageModifiedTime = filemtime($pathToImage);
        $cacheModifiedTime = is_file($cacheFileName) ? filemtime($cacheFileName) : null;

        // If cached image is valid, output it.
        if(!$ignoreCache && is_file($cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
          if($verbose) { verbose("Cache file is valid, output it."); }
          outputImage($cacheFileName, $verbose);
        }

        if($verbose) { verbose("Cache is not valid, process image and create a cached version of it."); }



        //
        // Open up the original image from file
        //
        if($verbose) { verbose("File extension is: {$fileExtension}"); }

        switch($fileExtension) {  
          case 'jpg':
          case 'jpeg': 
            $image = imagecreatefromjpeg($pathToImage);
            if($verbose) { verbose("Opened the image as a JPEG image."); }
            break;  
          
          case 'png':  
            $image = imagecreatefrompng($pathToImage); 
            if($verbose) { verbose("Opened the image as a PNG image."); }
            break;  

            case 'gif':  
            $image = imagecreatefromgif($pathToImage); 
            if($verbose) { verbose("Opened the image as a GIF image."); }
            break;  

          default: errorPage('No support for this file extension.');
        }



        //
        // Resize the image if needed
        //
        if($cropToFit) {
          if($verbose) { verbose("Resizing, crop to fit."); }
          $cropX = round(($width - $cropWidth) / 2);  
          $cropY = round(($height - $cropHeight) / 2);    
//          $imageResized = imagecreatetruecolor($newWidth, $newHeight);
          $imageResized = createImageKeepTransparency($newWidth, $newHeight);
          imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
          $image = $imageResized;
          $width = $newWidth;
          $height = $newHeight;
        }
        else if(!($newWidth == $width && $newHeight == $height)) {
          if($verbose) { verbose("Resizing, new height and/or width."); }
//          $imageResized = imagecreatetruecolor($newWidth, $newHeight);
          $imageResized = createImageKeepTransparency($newWidth, $newHeight);
          imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
          $image  = $imageResized;
          $width  = $newWidth;
          $height = $newHeight;
        }



        //
        // Apply filters and postprocessing of image
        //
        if($sharpen) {
          $image = sharpenImage($image);
        }
        if($filter == 'grs') {
          if($verbose) { verbose("Applying grayscale filter"); }
          imagefilter($image, IMG_FILTER_GRAYSCALE);
        }
        if($filter == 'sep') {
          if($verbose) { verbose("Applying sepia filter"); }
          imagefilter($image, IMG_FILTER_GRAYSCALE);
          imagefilter($image, IMG_FILTER_BRIGHTNESS, -10);
          imagefilter($image, IMG_FILTER_CONTRAST, -20);
          imagefilter($image, IMG_FILTER_COLORIZE, 120,60,0,0);
        }



        //
        // Save the image
        //
        switch($saveAs) {
          case 'jpeg':
          case 'jpg':
            if($verbose) { verbose("Saving image as JPEG to cache using quality = {$quality}."); }
            imagejpeg($image, $cacheFileName, $quality);
          break;  

          case 'png':  
            if($verbose) { verbose("Saving image as PNG to cache."); }
            // Turn off alpha blending and set alpha flag
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagepng($image, $cacheFileName);  
          break;  

          case 'gif':  
            if($verbose) { verbose("Saving image as GIF to cache."); }
            // Turn off alpha blending and set alpha flag
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagegif($image, $cacheFileName);  
          break;  

          default:
            errorMessage('No support to save as this file extension.');
          break;
        }

        if($verbose) { 
          clearstatcache();
          $cacheFilesize = filesize($cacheFileName);
          verbose("File size of cached file: {$cacheFilesize} bytes."); 
          verbose("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
        }



        //
        // Output the resulting image
        //
        outputImage($cacheFileName, $verbose);
            }
}