<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$user = new CUser($branax['database']);
$user->isAuthenticated() or die('Check: You must login to delete.');

$content = new CContent($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Radera";
$branax['main'] = <<<EOD
<h1>Radera innehåll</h1>
{$content->delete()}
EOD;


// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
