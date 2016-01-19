<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Create a wrapper object to recover game status and continue playing 
$gameWrapper = new C100Wrapper();

// Do it and store it all in variables in the Branax container.
$branax['title'] = "100-spel";
$branax['stylesheets'][] = 'css/dice.css'; 
$branax['main'] = $gameWrapper->runGame();    


// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
