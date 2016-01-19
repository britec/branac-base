<?php 
/**
 * This is a Branax pagecontroller.
 *
 */
// Include the essential config-file which also creates the $branax variable with its defaults.
include(__DIR__.'/config.php'); 

// Create the user object
$user = new CUser($branax['database']);

// Check if user is authenticated.
$output = $user->isAuthenticated() ? "Du är inloggad som: {$user->getAcronym()} ({$user->getName()})" : "Du är INTE inloggad.";

// Check if user and password is okey and login the user
if(isset($_POST['login'])) {
    $user->login($_POST['acronym'], $_POST['password']);
    header('Location: user_status.php');        
}

// Do it and store it all in variables in the Branax container.
$branax['title'] = "Login";
$branax['main'] = <<<EOD
<h1>{$branax['title']}</h1>

<form method=post>
  <fieldset>
  <legend>Login</legend>
  <p><label>Användare:<br/><input type='text' name='acronym' value=''/></label></p>
  <p><label>Lösenord:<br/><input type='password' name='password' value=''/></label></p>
  <p><input type='submit' name='login' value='Login'/></p>
  <p><a href='user_logout.php'>Logout</a></p>
  <p><a href='user_register.php'>Skapa ny användare</a></p>
  <output><b>{$output}</b></output>
  </fieldset>
</form>

EOD;






 

// Finally, leave it all to the rendering phase of Branax.
include(BRANAX_THEME_PATH);
