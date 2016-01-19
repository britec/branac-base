<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$user = new CUser($branax['database']);
$user->isAuthenticated() or die('Check: You must login to edit.');

$content = new CContent($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Innehåll";
$branax['main'] = <<<EOD
<h1>Administrera innehåll</h1>
{$content->getList()}
EOD;

// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
