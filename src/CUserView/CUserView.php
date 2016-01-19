<?php
/**
 * 
 *
 */
class CUserView {

    public function __construct() {
    }
 
    public function getDetails($val) {
        $admin = CUser::isCurrentUser($val->id) ? "<a href='user_regedit.php?id={$val->id}'>Edit Profile</a><br><a href='user_delete.php?id={$val->id}'>Delete Profile</a>" : NULL;
        $admin = CUser::isUser($val->id) ? "<td>$admin</td>" : NULL;
        $admin = CUser::isAuthenticated() ? "<td><a href='user_edit.php?id={$val->id}'>Edit</a><br><a href='user_delete.php?id={$val->id}'>Delete</a></td>" : $admin;
        // $synopsys = CTextFilter::nl2br($val->synopsys);
        return <<<EOD
<h2>{$val->name}</h2>
<p> <strong>Acronym:</strong> {$val->acronym} </p>
<p> <strong>E-mail:</strong> {$val->email} </p>
<p> <strong>Roll:</strong> {$val->role} </p>
<p> {$admin}

EOD;
    }
    
    public function getTable($res, $rows) {
        // Get parameters 
        $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
        $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
        $id     = isset($_GET['id'])  ? $_GET['id']  : null;
        // Check that incoming parameters are valid
        is_numeric($hits) or die('Check: Hits must be numeric.');
        is_numeric($page) or die('Check: Page must be numeric.');
        if ($id !== null) {
            return $this->getDetails($res[0]);
            exit;
        }

        // Prepare the table, firt the header row
        $admin = CUser::isUser() ? '<th>Admin</th>': NULL;
        $tr = "<tr>
        <th>Id</th>
        <th>Akronym " . CFunc::orderby('acronym') . "</th>
        <th>Namn  " . CFunc::orderby('name') . "</th>
        <th>Roll</th>
        {$admin}
        </tr>";


        // Prepare the data rows
        foreach($res AS $key => $val) {
            
            $admin = CUser::isCurrentUser($val->id) ? "<a href='user_regedit.php?id={$val->id}'>Edit</a> <a href='user_delete.php?id={$val->id}'>Delete</a>" : NULL;
            $admin = CUser::isUser($val->id) ? "<td>$admin</td>" : NULL;
            $admin = CUser::isAuthenticated() ? "<td><a href='user_edit.php?id={$val->id}'>Edit</a> <a href='user_delete.php?id={$val->id}'>Delete</a></td>" : $admin;
            // $synopsys = CTextFilter::nl2br($val->synopsys);
            
            $href = CFunc::getQueryString(array('id'=>$val->id));

            $tr .= "<tr>
            </a></td>
            <td>{$val->id}</td>
            <td><a href='{$href}'>{$val->acronym}</a></td>
            <td>{$val->name}</td>
            <td>{$val->role}</td>
            {$admin}
            </tr>";
        }

        // Prepare navigation bars and admin information 
        $max = ceil($rows / $hits);
        $hitsPerPage = CFunc::getHitsPerPage(array(4, 8, 16), $hits);
        $navigatePage = CFunc::getPageNavigation($hits, $page, $max);
        $adminAnchors = CUser::isAuthenticated() ? "<p><a href='user_create.php'>Skapa ny användare</a> <a href='user_reset.php'>Återställ användardatabasen</a></p>" : null;

        // Put rows in HTML-table with navigation bars
        $htmlTable = <<<EOD
<div class='dbtable'>
  <div class='rows'>{$rows} träffar. {$hitsPerPage}</div>
  <table>
  {$tr}
  </table>
  <div class='pages'>{$navigatePage}</div>
  {$adminAnchors}
</div>
EOD;

        return $htmlTable;
    }
}