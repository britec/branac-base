<?php
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

$filter = new CTextFilter(); 

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Om";

$branax['main'] = <<<EOD
<article class="readable">

{$filter->markdown(<<<EOD
Om Rental Movies
==
Visst är Djungelboken en fantastisk film! Och det är också just där vår saga en gång började. Under namnet Baloo.se gav vi ut just Djungelboken som vår första och då enda titel. Det blev en enastående framgång. Nu har verksamheten vuxit avsevärt och vi känner oss redo för nästa steg i utvecklingen med en ny genomarbetad web-site och under ett nytt firmanamn, RM-Rental Movies. Välkomna! 

Till alla älskare av Djungelboken andra klassiska filmer. Leta upp din favoritfim hos oss och hyr den snabbt och enkelt för en underbar kväll i tv-soffan. Mycket nöje!

Denna förnämliga website öppnades i början av 2016. Den är resultatet av ett projektarbete i kursen "Databaser och objektorienterad programmering i PHP" vid Blekinge tekniska högskola.

Det finns inga kopplingar till något verkligt företag för uthyrning av filmer.

EOD
)}
       
{$branax['byline']}

</article>

EOD;


// Finally, leave it all to the rendering phase of Brnax.
include(BRANAX_THEME_PATH);
