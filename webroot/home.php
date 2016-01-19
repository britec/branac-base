<?php
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 
$branax['stylesheets'][] = 'css/figure.css';
$branax['stylesheets'][] = 'css/gallery.css';

$movie = new CMovie($branax['database']);
$blog = new CBlog($branax['database']);

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Välkommen";

$branax['main'] = <<<EOD
<h1>{$branax['title']}</h1>
<aside>
    <h2>Nyheter</h2>
    {$blog->getMain(3)}
</aside>
<section>
    <h2>Våra filmkategorier</h2>
    {$movie->getCategories()}
    <h2>Nyinkommet</h2>{$movie->getUpdated(3)}  
    <h2>Populärast</h2>{$movie->getRated(3)}  
    <h2>Senast hyrda</h2>{$movie->getYoung(3)}  
</section>
EOD;


// Finally, leave it all to the rendering phase of Brnax.
include(BRANAX_THEME_PATH);
