<?php
/**
 * Config-file for Branax. Change settings here to affect installation.
 *
 */
$disableDebug = true;
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
$branax['title_append'] = ' | oophp';

$searchForm = CMovieSearch::getTitleForm('movies.php');
$branax['header'] = <<<EOD
<img class='sitelogo' src="img/logo.jpg" alt="logo" />
<span class="sitetitle">RM Rental Movies</span>
$searchForm
<span class="siteslogan">Hyr din favoritfilm på nätet</span>
EOD;

$branax['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright (c) 2016 RM Rental Movies | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;

$branax['byline'] = <<<EOD
<footer class='byline'>
    <figure class='right'>
        <img class='byline-image' src='img/byline.jpg' alt='Jag'>
    </figure>
    <p><strong>Björn Rikte</strong> har designat denna website som ett delmoment i kursen "Databaser och objektorienterad programmering i PHP" vid Blekinge Tekniska Högskola. Björn är civilingenjör och har jobbat inom telekomindustrin i många år. Här förkovrar han sig nu med kompletterande studier i bla webprogrammering.</p>
 </footer>
EOD;

/**
 * Settings for the database.
 *
 */
$branax['database']['dsn']        = 'mysql:host=localhost;dbname=bjri15;';
$branax['database']['username']   = 'root';
$branax['database']['password']   = '';
$branax['database']['cmd']        = "C:/xampp/mysql/bin/mysql -hlocalhost -u{$branax['database']['username']}";

if ($_SERVER['HTTP_HOST'] == 'www.student.bth.se') {
    $branax['database']['dsn']        = 'mysql:host=blu-ray.student.bth.se;dbname=bjri15;';
    $branax['database']['username']   = 'bjri15';
    $branax['database']['password']   = 'UOYYH9*x';
    $branax['database']['cmd']        = "/usr/bin/mysql -hblu-ray.student.bth.se -u{$branax['database']['username']} -p'{$branax['database']['password']}'";
}
$branax['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");

/**
 * The navbar
 *
 */
//$anax['navbar'] = null; // To skip the navbar
// $content = new CContent($branax['database']);
// $blogItems = $content->getBlogItems();
// $pageItems = $content->getPageItems();
// $content = null;
$userId = CUser::isUser() ? '?id=' . CUser::isUser() : null;
$blogSubMenuItems = CUser::isAuthenticated() ? array(
    'items' => array(
        'view' => array('text'=>'Ändra','url'=>'content_view.php', 'title'=>'Ändra innehåll'),
        'create' => array('text'=>'Lägg till','url'=>'content_create.php', 'title'=>'Nytt innehåll'),
        'reset' => array('text'=>'Återställ','url'=>'content_reset.php', 'title'=>'Återställ innehåll'),
    )
) : null;
$movieSubMenuItems = CUser::isAuthenticated() ? array(
    'items' => array(
        'create' => array('text'=>'Lägg till','url'=>'movie_create.php', 'title'=>'Nytt innehåll'),
        'reset' => array('text'=>'Återställ','url'=>'movie_reset.php', 'title'=>'Återställ innehåll'),
    )
) : null;

$userSubMenuItems = CUser::isUser() ? array(
    // 'profile' => array('text'=>'Min profil','url'=>"user_view.php{$userId}", 'title'=>'Visa min profil'),
    'logout' => array('text'=>'Logout', 'url'=>'user_logout.php', 'title'=>'Utloggning'),
) : array();
$userSubMenuItems += array(
    'login' => array('text'=>'Login','url'=>'user_login.php','title'=>'Inloggning'),
    'status' => array('text'=>'Status','url'=>'user_status.php','title'=>'Inloggningsstatus'),
);
$userSubMenuItems += CUser::isAuthenticated() ? array(
    'create' => array('text'=>'Lägg till','url'=>'user_create.php', 'title'=>'Nytt innehåll'),
    'reset' => array('text'=>'Återställ','url'=>'user_reset.php', 'title'=>'Återställ')
) : array();

$searchForm = CMovieSearch::getTitleForm('movies.php');
  
$branax['navbar'] = array(
    'class' => 'navbar',
    'items' => array(
        'hem' => array('text'=>'Hem', 'url'=>'home.php', 'title'=>'RM Rental Movies'),
        'filmer' => array('text'=>'Filmer', 'url'=>'movies.php', 'title'=>'Våra filmer', 'submenu' => $movieSubMenuItems),
        'nyheter' => array(
            'text'=>'Nyheter',
            'url'=>'content_blog.php',
            'title'=>'Blogg',
            'submenu' => $blogSubMenuItems
        ),
        'dice' => array('text'=>'Tävling', 'url'=>'dice100.php', 'title'=>'Spela och vinn en film'),
        'user' => array(
            'text'=>'Användare',
            'url'=>"user_view.php{$userId}",
            'title'=>'Användarprofiler',
            'submenu' => array('items' => $userSubMenuItems),
        ),
        'about' => array('text'=>'Om RM', 'url'=>'about.php', 'title'=>'Om företaget'),
//        'kallkod' => array('text'=>'Källkod', 'url'=>'source.php', 'title'=>'Se källkoden'),
    ),
    'callback' => function($url) {
        if(basename($_SERVER['SCRIPT_NAME']) == $url) {
            return true;
        }
        if(in_array(basename($_SERVER['SCRIPT_NAME']), ['content_edit.php', 'content_delete.php', 'content_view.php']) && in_array($url, ['content_edit.php', 'content_delete.php', 'content_view.php'])) {
            return true;
        }
        if(in_array(basename($_SERVER['SCRIPT_NAME']), ['movie_view.php', 'movie_edit.php', 'movie_delete.php']) && in_array($url, ['movies.php'])) {
            return true;
        }
        if(in_array(basename($_SERVER['SCRIPT_NAME']), ['user_view.php', 'user_register.php', 'user_regedit.php', 'user_edit.php', 'user_delete.php']) && in_array(parse_url($url, PHP_URL_PATH), ['user_view.php'])) {
            return true;
        }
    }
);

/**
 * Theme related settings.
 *
 */
//$branax['stylesheet'] = 'css/style.css';
$branax['stylesheets'] = array('css/style.css');
$branax['favicon']    = 'img/baloo4.ico';


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
