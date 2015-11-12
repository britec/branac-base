<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 


// Define what to include to make the plugin to work
$branax['stylesheets'][]        = 'css/slideshow.css';
$branax['javascript_include'][] = 'js/slideshow.js';


// Do it and store it all in variables in the Branax container.
$branax['title'] = "Slideshow för att testa JavaScript i Branax";

$branax['main'] = <<<EOD
<div id="slideshow" class='slideshow' data-host="" data-path="img/me/" data-images='["me-1.jpg", "me-2.jpg", "me-4.jpg", "me-6.jpg"]'>
<img src='img/me/me-6.jpg' width='950px' height='180px' alt='Me'/>
</div>

<h1>En slideshow med JavaScript</h1>
<p>Detta är en exempelsida som visar hur Branax fungerar tillsammans med JavaScript.</p>
EOD;


// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
