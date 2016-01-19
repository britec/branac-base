<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$content = new CUser($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Edit";
$branax['main'] = <<<EOD
<h1>Editera innehåll</h1>
{$content->regedit()}
EOD;

// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
