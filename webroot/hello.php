<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 


// Do it and store it all in variables in the Branax container.
$branax['title'] = "Hello World";

$branax['header'] = <<<EOD
<img class='sitelogo' src='img/branax.png' alt='Branax Logo'/>
<span class='sitetitle'>Branax webbtemplate</span>
<span class='siteslogan'>Återanvändbara moduler för webbutveckling med PHP</span>
EOD;

$branax['main'] = <<<EOD
<h1>Hej Världen</h1>
<p>Detta är en exempelsida som visar hur Branax ser ut och fungerar.</p>
EOD;

$branax['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright (c) Björn Rikte | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;


// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
