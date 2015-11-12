<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 



// Do it and store it all in variables in the Brnax container.
$branax['title'] = "404";
$branax['header'] = "";
$branax['main'] = "This is a Branax 404. Document is not here.";
$branax['footer'] = "";

// Send the 404 header 
header("HTTP/1.0 404 Not Found");


// Finally, leave it all to the rendering phase of Anax.
include(BRANAX_THEME_PATH);
