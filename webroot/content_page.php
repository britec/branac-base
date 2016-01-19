<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Get url 
$url     = isset($_GET['url']) ? $_GET['url'] : null;

$page = new CPage($url, $branax['database']);

$branax['title'] = $page->getTitle();
$branax['main'] = $page->getMain();

// Finally, leave it all to the rendering phase of Anax.
include(BRANAX_THEME_PATH);
