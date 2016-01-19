<?php
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Add style for csource
$branax['stylesheets'][] = 'css/source.css';

// Create the object to display sourcecode
//$source = new CSource();
$source = new CSource(array('secure_dir' => '..', 'base_dir' => '..'));


// Do it and store it all in variables in the Branax container.
$branax['title'] = "Källkod";

$branax['main'] = "<h1>Visa källkod</h1>\n" . $source->View();
       


// Finally, leave it all to the rendering phase of Brnax.
include(BRANAX_THEME_PATH);
