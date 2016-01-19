<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CUser {
    /**
    * Members
    */
    private $db;
    private $cmd;
    private $output;
    /**
    * Constructor 
    *
    */
    public function __construct($dbOptions) {
        $this->db = new CDatabase($dbOptions);
        $this->cmd = $dbOptions['cmd'];
        $this->output = null;
    }
    public function dump() {
        return $this->db->dump();
    }
    
    public function login($acronym, $pwd) {
        $sql = "SELECT id, acronym, name, role FROM rm_user WHERE acronym = ? AND password = md5(concat(?, salt))";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($_POST['acronym'], $_POST['password']));
        if(isset($res[0])) {
            $_SESSION['user'] = $res[0];
        }
    }

    public function logout() {
        unset($_SESSION['user']);
    }

    public static function isCurrentUser($id) {
        return isset($_SESSION['user']) && ($_SESSION['user']->id == $id);
    }
    
    public static function isAuthenticated($id = null) {
        return isset($_SESSION['user']) && (($_SESSION['user']->role == 'admin') || ($_SESSION['user']->id == $id)) ? true : false;
    }
    public static function isUser() {
        return isset($_SESSION['user']) ? $_SESSION['user']->id : null;
    }

    public static function isAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']->role == 'admin';
    }
    
    public function getAcronym() {
        return isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;        
    }
    public function getName() {
        return isset($_SESSION['user']) ? $_SESSION['user']->name : null;
    }


    // Publik function to reset the content database
    public function reset() {    
        if(isset($_POST['restore']) || isset($_GET['restore'])) {
            $this->logout();
            return $this->doReset();
        } else { 
            return $this->resetForm();
        }
    }

    // Public function to create new contnents
    public function register($creator = null) {    
        if(isset($_POST['create'])) {
            return $this->doRegister($creator);
        } else { 
            return $this->createForm();
        }
    }

    // Public function to create new contnents
    public function create($creator = null) {    
        if(isset($_POST['create'])) {
            return $this->doCreate($creator);
        } else { 
            return $this->createForm();
        }
    }

    // Public function to update contents
    public function regedit() {    
        $id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
        is_numeric($id) or die('Check: Id must be numeric.');
        $this->isAuthenticated($id) or die('Check: You must login to edit.');
        if(isset($_POST['save'])) {
            return $this->doRegedit($id);
        } else { 
            return $this->regeditForm($id);
        }
    }

    // Public function to update contents
    public function update() {    
        $id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
        is_numeric($id) or die('Check: Id must be numeric.');
        if(isset($_POST['save'])) {
            return $this->doUpdate($id);
        } else { 
            return $this->updateForm($id);
        }
    }

    // Public function to delete contents
    public function delete() {    
        $id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
        is_numeric($id) or die('Check: Id must be numeric.');
        $this->isAuthenticated($id) or die('Check: You must login to edit.');
        if(isset($_POST['delete'])) {
            ($this->isCurrentUser($id) && $this->logout());
            return $this->doDelete($id);
        } else { 
            return $this->deleteForm($id);
        }
    }

    // Returns a list of all contents
    public function getList() {
        $sql = 'SELECT * FROM rm_user WHERE 1';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
        // Put results into a list
        $items = null;
        foreach($res AS $c) {
            $acronym = htmlentities($c->acronym, null, 'UTF-8');
            $name = htmlentities($c->name, null, 'UTF-8');
            $role = htmlentities($c->role, null, 'UTF-8');
            $adminLinks = "
                <a href='user_edit.php?id={$c->id}'>Edit</a> 
                <a href='user_delete.php?id={$c->id}'>Delete</a>";
            $items .= "<li>{$role} {$acronym} ({$name}) {$adminLinks}</li>\n";
        }
        return <<<EOD
<p>$this->output</p>
<p>Här är en lista på allt innehåll i databasen.</p>
<ul>{$items}</ul>
<p><a href='user_create.php'>Lägg till innehåll</a></p>
<p><a href='user_reset.php'>Återställ databasen</a></p>
EOD;
    }

       

    //Functions to reset the database
    private function doReset() {
        $cmd = $this->cmd . " --verbose  < rm_user_reset.txt 2>&1";
        $output = null;
        $res = exec($cmd, $output);
//        dump($cmd);
//        dump($res);
//        dump($output);

        $this->output = "<p>Databasen är återställd";
        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }
    private function resetForm() {
        return <<<EOD
<form method=post>
<input type=submit name=restore value='Återställ databasen'/>
<p><a href='user_view.php'>Visa alla</a></p>
</form>
EOD;
    }


    private function doCreate($creator) {
        // Get parameters 
        $acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
        $password = isset($_POST['password'])  ? $_POST['password']  : null;
        $sql = 'INSERT INTO rm_user (acronym, salt) VALUES (?, unix_timestamp())';
        $res = $this->db->ExecuteQuery($sql, array($acronym));
        if($res) {
            $id = $this->db->LastInsertId();
            $this->output = 'Användaren skapades.';
            $this->db->SaveDebug();
            $sql = 'UPDATE rm_user SET password = md5(concat(?, salt)) WHERE acronym = ?';
            $params = array($password, $acronym);
            $res = $this->db->ExecuteQuery($sql, $params);        
        
            header('Location: user_edit.php?id=' . $id);
            exit;
        } else {
            $this->output = 'Användaren skapades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }
    private function createForm() {
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Skapa ny profil</legend>
  <p><label>Användarnamn:<br/><input type='text' name='acronym' value=''/></label></p>
  <p><label>Lösenord:<br/><input type='text' name='password' value=''/></label></p>
  <p class=buttons><input type='submit' name='create' value='Skapa'/> <input type='reset' value='Återställ'/></p>
  <p><a href='user_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
    
    // Update database
    private function doUpdate($id) {
        // Get parameters 
        $name  = isset($_POST['name']) ? $_POST['name'] : null;
        $email  = isset($_POST['email']) ? $_POST['email'] : null;
        $role   = isset($_POST['role'])  ? $_POST['role']  : null;
        $sql = '
        UPDATE rm_user SET
          name    = ?,
          email    = ?,
          role    = ?
        WHERE 
          id = ? AND NOT protected
        ';
        $params = array($name, $email, $role, $id);
        $res = $this->db->ExecuteQuery($sql, $params);
        if($res) {
            $this->output = 'Information saved.';
        }
        else {
            $this->output = 'Information not saved.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }
    private function updateForm($id) {
        // Select from database
        $sql = 'SELECT * FROM rm_user WHERE id = ? AND NOT protected';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Failed: This id is protected or does not exist');
        }
        // Sanitize content before using it.
        $acronym  = htmlentities($c->acronym, null, 'UTF-8');
        $name  = htmlentities($c->name, null, 'UTF-8');
        $email   = htmlentities($c->email, null, 'UTF-8');
        $role   = $c->role;
        $adminSelected = ($role == 'admin') ? 'checked' : null;
        $userSelected = ($role == 'admin') ? null : 'checked';
                    
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Uppdatera profil för {$acronym} ({$name})</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><label>Namn:<br/><input type='text' name='name' value='$name'/></label></p>
  <p><label>Email:<br/><input type='text' name='email' value='$email'/></label></p>
  <p>
    <label>Admin<input type='radio' name='role' value='admin' {$adminSelected}/></label>
    <label>User<input type='radio' name='role' value='user' {$userSelected}/></label>
  </p>
  <p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>
  <p><a href='user_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }


    private function doDelete($id) {
        $sql = 'DELETE FROM rm_user WHERE id = ? AND NOT protected';
        $res = $this->db->ExecuteQuery($sql, array($id));
        if($res) {
            $this->output = 'Innehållet raderades.';
        }
        else {
            $this->output = 'Innehållet raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }
    private function deleteForm($id) {
        // Select information on the content 
        $sql = 'SELECT * FROM rm_user WHERE id = ? AND NOT protected';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Failed: This id is protected or does not exist');
        }
        $acronym = htmlentities($c->acronym, null, 'UTF-8');
        $name = htmlentities($c->name, null, 'UTF-8');
        return <<<EOD
<form method=post>
  <fieldset>
  <legend>Radera användare: {$acronym} ({$name})</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><input type='submit' name='delete' value='Radera'/></p>
  <p><a href='user_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
    
    private function doRegister($creator) {
        // Get parameters 
        $acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
        $password = isset($_POST['password'])  ? $_POST['password']  : null;
        $role = 'user';
        $sql = 'INSERT INTO rm_user (acronym, salt, role, protected) VALUES (?, unix_timestamp(), ?, 0)';
        $res = $this->db->ExecuteQuery($sql, array($acronym, $role));
        if($res) {
            $id = $this->db->LastInsertId();
            $this->output = 'Användaren skapades.';

            $sql = 'UPDATE rm_user SET password = md5(concat(?, salt)) WHERE acronym = ?';
            $params = array($password, $acronym);
            $res = $this->db->ExecuteQuery($sql, $params);

            $this->login($acronym, $password);            
        
            $this->db->SaveDebug();
            header('Location: user_regedit.php?id=' . $id);
            exit;
        } else {
            $this->output = 'Användaren skapades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }



    // Update database
    private function doRegedit($id) {
        // Get parameters 
        $name  = isset($_POST['name']) ? $_POST['name'] : null;
        $password   = isset($_POST['password'])  ? $_POST['password']  : null;
        $email = isset($_POST['email'])  ? $_POST['email']  : null;
        $sql = '
        UPDATE rm_user SET
          name = ?,
          email = ?
        WHERE 
          id = ?
        ';
        $params = array($name, $email, $id);
        $res = $this->db->ExecuteQuery($sql, $params);
        if($res) {
            $this->output = 'Informationen sparades.';
        }
        else {
            $this->output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='user_view.php'>Visa alla</a></p>";
    }
    private function regeditForm($id) {
        // Select from database
        $sql = 'SELECT * FROM rm_user WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Failed: No such id');
        }
        // Sanitize content before using it.
        $name  = htmlentities($c->name, null, 'UTF-8');
        $acronym   = htmlentities($c->acronym, null, 'UTF-8');
        $email   = htmlentities($c->email, null, 'UTF-8');
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Uppdatera profil för {$acronym} ({$name})</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><label>Ditt Namn:<br/><input type='text' name='name' value='$name'/></label></p>
  <p><label>Din E-mail:<br/><input type='text' name='email' value='$email'/></label></p>
  <p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>
  <p><a href='user_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
}