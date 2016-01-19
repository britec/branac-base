<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$user = new CUser($branax['database']);
$user->isAuthenticated() or die('Check: You must login to edit.');

$content = new CUser($branax['database']);

// Do it and store it all in variables in the Anax container.
$branax['title'] = "Ny Användare";
$branax['main'] = <<<EOD
<h1>{$branax['title']}</h1>
{$content->create()}
EOD;

// Finally, leave it all to the rendering phase of Anax.
include(BRANAX_THEME_PATH);
