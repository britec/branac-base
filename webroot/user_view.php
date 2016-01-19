<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$search = new CUserSearch($branax['database']);
$display = new CUserView($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['stylesheets'][] = 'css/movies.css';
$branax['title'] = "Användare";
$branax['main'] = <<<EOD
<h1>Användare</h1>
<aside>{$search->getForm()}</aside>
<section>{$display->getTable($search->getRes(), $search->getRows())}</section>
EOD;

$debug = isset($disableDebug) ? null : CDatabase::dump();

// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
