<?php
/**
 * 
 *
 */
class CUserSearch { 
    /**
    * Members
    */
    private $form;
    private $rows;
    private $res;

    /**
    * Constructor 
    *
    */
    public function __construct($dbOptions) {
        $db = new CDatabase($dbOptions);

        $id     = isset($_GET['id'])  ? $_GET['id']  : null;
        $name    = isset($_GET['name']) ? $_GET['name'] : null;
        $acronym    = isset($_GET['acronym']) ? $_GET['acronym'] : null;
        $role    = isset($_GET['role']) ? $_GET['role'] : null;
        $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
        $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
        $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
        $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';

        // Check that incoming parameters are valid
        is_numeric($hits) or die('Check: Hits must be numeric.');
        is_numeric($page) or die('Check: Page must be numeric.');

        
        // Prepare the query based on incoming arguments
        $sqlOrig = '
          SELECT * FROM rm_user
        ';
        $where    = null;
        $limit    = null;
        $sort     = " ORDER BY $orderby $order";
        $params   = array();

        // Select by name
        if($name) {
          $where .= ' AND name LIKE ?';
          $params[] = $name;
        } 

        // Select by acronym
        if($acronym) {
          $where .= ' AND acronym LIKE ?';
          $params[] = $acronym;
        } 

        // Select by type
        if($role) {
          $where .= ' AND role LIKE ?';
          $params[] = $role;
        } 

        // Select by id
        if($id) {
          $where .= ' AND id = ?';
          $params[] = $id;
        } 

        // Pagination
        if($hits && $page) {
          $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
        }

        // Complete the sql statement
        $where = $where ? " WHERE 1 {$where}" : null;
        $sql = $sqlOrig . $where . $sort . $limit;
        $this->res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);

        // Get max pages for current query, for navigation
        $sql = "
          SELECT
            COUNT(id) AS rows
          FROM 
          (
            $sqlOrig $where
          ) AS Movie
        ";
        $res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);
        $this->rows = $res[0]->rows;
        $userSelected = $role == 'user' ? 'selected' : null;
        $adminSelected = $role == 'admin' ? 'selected' : null;

        $this->form = <<<EOD
<form class='usersearch'>
    <fieldset>
    <legend>Sök profil</legend>
    <input type=hidden name=hits value='{$hits}'/>
    <input type=hidden name=page value='1'/>
    <p><label>Namn (använd % som *)<input type='text' name='name' value='{$name}' /></label><br>
       <label>Akronym (använd % som *)<input type='text' name='acronym' value='{$acronym}' /> </label><br>
    <p><select name='role'>
        <option value=''>Select role</option>>
        <option  {$userSelected}>user</option>>
        <option  {$adminSelected}>admin</option>>
    </select>
    <input type='submit' name='submit' value='Sök'/> <a href='?'>Visa Alla</a></p>
    </fieldset>
</form>
EOD;

    }

    public function getForm() {
        return $this->form;
    }  
    public function getRes() {
        return $this->res;
    }  
    public function getRows() {
        return $this->rows;
    }  

}