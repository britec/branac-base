<?php
/**
 * 
 *
 */
class CMovieSearch { 
    /**
    * Members
    */
    private $rows;
    private $res;

    /**
    * Constructor 
    *
    */
    public function __construct($dbOptions) {
        // Connect to a MySQL database using PHP PDO
        $db = new CDatabase($dbOptions);

        // Get parameters 
        $title    = isset($_GET['title']) ? $_GET['title'] : null;
        $genre    = isset($_GET['genre']) ? $_GET['genre'] : null;
        $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
        $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
        $year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
        $year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
        $orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
        $order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
        $id = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);

        // Check that incoming parameters are valid
        is_numeric($hits) or die('Check: Hits must be numeric.');
        is_numeric($page) or die('Check: Page must be numeric.');
        is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
        is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');


        // Get all genres that are active
        $sql = '
          SELECT DISTINCT G.name
          FROM `rm_genre` AS G
            INNER JOIN `rm_movie2genre` AS M2G
              ON G.id = M2G.idGenre
        ';
        $res = $db->ExecuteSelectQueryAndFetchAll($sql);
        $_SESSION['genres'] = $res;
        
        $genres = null;
        foreach($res as $val) {
          if($val->name == $genre) {
            $genres .= "$val->name ";
          }
          else {
            $genres .= "<a href='" . CFunc::getQueryString(array('genre' => $val->name)) . "'>{$val->name}</a> ";
          }
        }

        $genreOptions = "<option value=''>Select genre</option>\n";
        foreach($res as $val) {
            $selected = ($val->name == $genre) ? 'selected' : null;
            $genreOptions .= "<option value='{$val->name}' {$selected}>{$val->name}</option>\n";
        }
        
        
        // Prepare the query based on incoming arguments
        $sqlOrig = '
          SELECT 
            M.*,
            GROUP_CONCAT(G.name) AS genre
          FROM `rm_movie` AS M
            LEFT OUTER JOIN `rm_movie2genre` AS M2G
              ON M.id = M2G.idMovie
            LEFT OUTER JOIN `rm_genre` AS G
              ON M2G.idGenre = G.id
        ';
        $where    = null;
        $groupby  = ' GROUP BY M.id';
        $limit    = null;
        $sort     = " ORDER BY $orderby $order";
        $params   = array();

        // Select by title
        if($title) {
          $where .= ' AND title LIKE ?';
          $params[] = $title;
        } 

        // Select by year
        if($year1) {
          $where .= ' AND year >= ?';
          $params[] = $year1;
        } 
        if($year2) {
          $where .= ' AND year <= ?';
          $params[] = $year2;
        } 

        // Select by genre
        if($genre) {
          $where .= ' AND G.name = ?';
          $params[] = $genre;
        } 

        // Select by id
        if($id) {
          $where .= ' AND M.id = ?';
          $params[] = $id;
        } 

        // Pagination
        if($hits && $page) {
          $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
        }

        // Complete the sql statement
        $where = $where ? " WHERE 1 {$where}" : null;
        $sql = $sqlOrig . $where . $groupby . $sort . $limit;
        $this->res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);

        // Get max pages for current query, for navigation
        $sql = "
          SELECT
            COUNT(id) AS rows
          FROM 
          (
            $sqlOrig $where $groupby
          ) AS Movie
        ";
        $res = $db->ExecuteSelectQueryAndFetchAll($sql, $params);
        $this->rows = $res[0]->rows;
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

    public static function getTitleForm($url) {
        $title    = isset($_GET['title']) ? $_GET['title'] : null;
        $genre    = isset($_GET['genre']) ? $_GET['genre'] : null;
        $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
        $res      = isset($_SESSION['genres']) ? $_SESSION['genres'] : array();
        $genreOptions = "<option value=''>Select genre</option>\n";
        foreach($res as $val) {
            $selected = ($val->name == $genre) ? 'selected' : null;
            $genreOptions .= "<option value='{$val->name}' {$selected}>{$val->name}</option>\n";
        }
        return <<<EOD
<form class='smallsearch' action='{$url}'>
    <fieldset>
    <legend>Sök film</legend>
    <input type=hidden name=hits value='{$hits}'/>
    <input type=hidden name=page value='1'/>
    <input type='search' name='title' value='{$title}' placeholder='Titelsök (använd % som *)'/>
    <select onchange='this.form.submit();' name='genre'>{$genreOptions}</select>
    <a href='movies.php'>Rensa</a>
    </fieldset>
</form>
EOD;
        return  <<<EOD
<form action='{$url}'>
    <input type='search' name='title' placeholder='Titelsökning (använd % som *)'/>
</form>
EOD;
    }    
}