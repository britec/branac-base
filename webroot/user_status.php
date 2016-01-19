<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Create the user object
$user = new CUser($branax['database']);

// Check if logged in user
$admin = $user->isAdmin() ? 'med administratörsrättigheter' : null;
$output = $user->isUser() ? "Du är inloggad som {$user->getAcronym()} ({$user->getName()}) $admin" : "Du är INTE inloggad.";

// Do it and store it all in variables in the Anax container.
$branax['title'] = "User status";

$branax['main'] = <<<EOD
<h1>{$branax['title']}</h1>
<form method=post>
  <fieldset>
  <legend>Login status</legend>
  <output><b>{$output}</b></output>
  <p><a href='user_login.php'>Login</a></p>
  <p><a href='user_logout.php'>Logout</a></p>
  </fieldset>
</form>

EOD;
 
// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
