<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$movieSearch = new CMovieSearch($branax['database']);
$movieView = new CMovieView();

$contents = $movieView->getTable($movieSearch->getRes(), $movieSearch->getRows());


// Do it and store it all in variables in the Branax container.
$branax['title'] = $movieView->getTitle('Filmer');
$branax['stylesheets'][] = 'css/movies.css';
$branax['stylesheets'][] = 'css/breadcrumb.css';

$branax['main'] = <<<EOD
{$movieView->getBreadcrumb()}
<h1>{$movieView->getTitle('Våra filmer')}</h1>
{$contents}
EOD;
 
$debug = isset($disableDebug) ? null : CDatabase::dump();
 
// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
