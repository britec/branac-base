<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 


$blog = new CBlog($branax['database']);
$limit = null;
$main = $blog->getMain();

$branax['stylesheets'][] = 'css/breadcrumb.css';

$branax['title'] = 'Nyhetsblogg';
$branax['main'] = <<<EOD
{$blog->getBreadcrumb()}
<h1>{$branax['title']}</h1>
<aside>
    <h2>Sök på kategori</h2>
    {$blog->getCategories()}
</aside>
<section>
    {$main}
</section>
EOD;

$debug = isset($disableDebug) ? null : $blog->dump();
 
// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
