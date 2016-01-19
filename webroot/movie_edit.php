<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$user = new CUser($branax['database']);
$user->isAuthenticated() or die('Check: You must login to edit.');

define('MOVIE_PATH', __DIR__ . '/img/movie/');

$content = new CMovie($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Edit";
$branax['main'] = <<<EOD
<h1>Editera innehåll</h1>
{$content->update()}
EOD;

// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
