<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Create the user object
$user = new CUser($branax['database']);

// Check if user is authenticated
$output = $user->isAuthenticated() ? "Du är inloggad som: {$user->getAcronym()} ({$user->getName()})" : "Du är INTE inloggad.";

// Logout the user
if(isset($_POST['logout'])) {
    $user->logout();
    header('Location: user_status.php');
}

// Do it and store it all in variables in the Anax container.
$branax['title'] = "Logout";

$branax['main'] = <<<EOD
<h1>{$branax['title']}</h1>
<form method=post>
  <fieldset>
  <legend>Logout</legend>
  <p><input type='submit' name='logout' value='Logout'/></p>
  <p><a href='user_login.php'>Login</a></p>
  <output><b>{$output}</b></output>
  </fieldset>
</form>

EOD;
 
// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
