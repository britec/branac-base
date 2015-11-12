<?php
/**
 * Config-file for Branax. Change settings here to affect installation.
 *
 */

/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly


/**
 * Define Branax paths.
 *
 */
define('BRANAX_INSTALL_PATH', __DIR__ . '/..');
define('BRANAX_THEME_PATH', BRANAX_INSTALL_PATH . '/theme/render.php');


/**
 * Include bootstrapping functions.
 *
 */
include(BRANAX_INSTALL_PATH . '/src/bootstrap.php');


/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


/**
 * Create the Branax variable.
 *
 */
$branax = array();


/**
 * Site wide settings.
 *
 */
$branax['lang']         = 'sv';
$branax['title_append'] = ' | Branax en webbtemplate';

$branax['header'] = <<<EOD
<img class='sitelogo' src='img/branax.png' alt='Branax Logo'/>
<span class='sitetitle'>Branax webbtemplate</span>
<span class='siteslogan'>Återanvändbara moduler för webbutveckling med PHP</span>
EOD;

$branax['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright (c) Björn Rikte | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;



/**
 * Theme related settings.
 *
 */
//$branax['stylesheet'] = 'css/style.css';
$branax['stylesheets'] = array('css/style.css');
$branax['favicon']    = 'img/branax.png';



/**
 * Settings for JavaScript.
 *
 */
$branax['modernizr'] = 'js/modernizr.js';
$branax['jquery_src'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
$branax['jquery'] = null; // To disable jQuery
$branax['javascript_include'] = array();
//$branax['javascript_include'] = array('js/main.js'); // To add extra javascript files



/**
 * Google analytics.
 *
 */
$branax['google_analytics'] = 'UA-22093351-1'; // Set to null to disable google analytics
