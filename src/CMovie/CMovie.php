<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CMovie {
    /**
    * Members
    */
    private $db;
    private $cmd;
    private $output;
    /**
    * Constructor 
    */
    public function __construct($dbOptions) {
        $this->db = new CDatabase($dbOptions);
        $this->cmd = $dbOptions['cmd'];
        $this->output = null;
    }
    private function movieGallery($res) {
        $gallery = "<ul class='gallery'>\n";
        $html = null;
        foreach ($res as $row) {
            $href = "movie_view.php?id={$row->id}";
            $item  = "<img src=img.php?src=img/movie/{$row->image}&amp;height=250 alt=''/>";
            $caption = $row->title; 
             // Avoid to long captions breaking layout
            if(strlen($caption) > 20) {
                $caption = substr($caption, 0, 12) . '…' . substr($caption, -5);
            }
 
            $gallery .= 
                "<li><a href='{$href}' title='{$row->title}'>
                    <figure class='figure overview'>
                        {$item}
                        <figcaption>{$caption}</figcaption>
                    </figure>
                </a></li>\n";
        }
        $gallery .= "</ul>\n";
        return $gallery;        
    }
    public function getUpdated($limit) {
        $limit = $limit ? "LIMIT $limit" : null;
        $sql = "SELECT * FROM rm_movie ORDER BY updated DESC {$limit}";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array());
        return $this->movieGallery($res);
    }
    public function getRated($limit) {
        $limit = $limit ? "LIMIT $limit" : null;
        $sql = "SELECT * FROM rm_movie ORDER BY rating DESC {$limit}";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array());
        return $this->movieGallery($res);
    }
    public function getYoung($limit) {
        $limit = $limit ? "LIMIT $limit" : null;
        $sql = "SELECT * FROM rm_movie ORDER BY year DESC {$limit}";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array());
        return $this->movieGallery($res);
    }
    public function getCategories() {
        // Get all genres that are active
        $sql = '
          SELECT DISTINCT G.name
          FROM `rm_genre` AS G
            INNER JOIN `rm_movie2genre` AS M2G
              ON G.id = M2G.idGenre
        ';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array());
        $genres = [];
        foreach ($res as $row) {
            $genres[] = "<a href = 'movies.php?genre={$row->name}'>{$row->name}</a>"; 
        }  
        return implode(', ', $genres);
    }

    // Public function to reset the content database
    public function reset() {    
        if(isset($_POST['restore']) || isset($_GET['restore'])) {
            return $this->doReset();
        } else { 
            return $this->resetForm();
        }
    }

    // Public function to create new contnents
    public function create($creator) {    
        if(isset($_POST['create'])) {
            return $this->doCreate($creator);
        } else { 
            return $this->createForm();
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
        if(isset($_POST['delete'])) {
            return $this->doDelete($id);
        } else { 
            return $this->deleteForm($id);
        }
    }

       
    //Functions to reset the database
    private function doReset() {
        $cmd = $this->cmd . " < rm_movie_reset.txt 2>&1";
        $output = null;
        $res = exec($cmd, $output);

        $this->output = "<p>Databasen är återställd";
        return $this->output . "<p><a href='movies.php'>Visa alla</a></p>";
    }
    private function resetForm() {
        return <<<EOD
<form method=post>
<input type=submit name=restore value='Återställ databasen'/>
<p><a href='movies.php'>Visa alla</a></p>
</form>
EOD;
    }

    private function doCreate($creator) {
        // Get parameters 
        $title  = isset($_POST['title']) ? $_POST['title'] : array();
        $slug = $this->slugify($title);
        $synopsys   = isset($_POST['synopsys'])  ? $_POST['synopsys'] : null;;
        $year   = isset($_POST['year'])  ? intval($_POST['year']) : 1900;
        $price   = isset($_POST['price'])  ? intval($_POST['price']) : 19;
        $image = isset($_POST['image'])  ? strip_tags($_POST['image']) : "$slug.jpg";
        $imdb = isset($_POST['imdb'])  ? strip_tags($_POST['imdb']) : array();
        $youtube = isset($_POST['youtube'])  ? strip_tags($_POST['youtube']) : "$title official trailer";
        $director  = isset($_POST['director']) ? $_POST['director'] : null;
        $length   = isset($_POST['length'])  ? intval($_POST['length']) : 0;
        $rating   = isset($_POST['rating'])  ? intval($_POST['rating']) : 5;
        $genres = isset($_POST['genres'])  ? ($_POST['genres']) : array();
        $sql = 'INSERT INTO rm_movie (title, synopsys, year, price, image, imdb, director, length, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $res = $this->db->ExecuteQuery($sql, array($title, $synopsys, $year, $price, $image, $imdb, $director, $length, rating));

        $this->db->SaveDebug();
        header('Location: movie_edit.php?id=' . $this->db->LastInsertId());
        exit;
    }
    private function createForm() {
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Skapa nytt innehåll</legend>
  <p><label>Titel:<br/><input type='text' name='title' value=''/></label></p>
  <p class=buttons><input type='submit' name='create' value='Skapa'/> <input type='reset' value='Återställ'/></p>
  <p><a href='movies.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
    
    // Update database
    private function doUpdate($id) {
        // Get parameters 
        $title  = isset($_POST['title']) ? $_POST['title'] : null;
        $synopsys   = isset($_POST['synopsys'])  ? $_POST['synopsys'] : array();
        $year   = isset($_POST['year'])  ? intval($_POST['year']) : null;
        $price   = isset($_POST['price'])  ? intval($_POST['price']) : null;
        $image = isset($_POST['image'])  ? strip_tags($_POST['image']) : array();
        $imdb = isset($_POST['imdb'])  ? strip_tags($_POST['imdb']) : array();
        $youtube = isset($_POST['youtube'])  ? strip_tags($_POST['youtube']) : array();
        $director  = isset($_POST['director']) ? $_POST['director'] : null;
        $length   = isset($_POST['length'])  ? intval($_POST['length']) : null;
        $rating   = isset($_POST['rating'])  ? intval($_POST['rating']) : null;
        $genres = isset($_POST['genres'])  ? ($_POST['genres']) : array();

        if (isset($_FILES['imagef'])) {
            $target = MOVIE_PATH . $image;
            if (move_uploaded_file($_FILES['imagef']['tmp_name'], $target)) {
                $this->output .= "<p>Image file successfully uploaded</p>.";
            } else {
                $this->output .= "<p>Image file not uploaded!</p>";
            }           
        }
         
        $sql = 'DELETE FROM rm_movie2genre WHERE idMovie = ?';
        $res = $this->db->ExecuteQuery($sql, array($id));
        if(!$res) {
            die('Not able to delete genre2movie content. ' . print_r($this->db->ErrorInfo(), 1));
        }
        foreach($genres as $idGenre) {
            $sql = 'INSERT INTO rm_movie2genre (idMovie, idGenre) VALUES (?,?)';
            $res = $this->db->ExecuteQuery($sql, array($id, $idGenre));
            if(!$res) {
                die('Not able to create genre2movie content' . print_r($this->db->ErrorInfo(), 1));
            }
        }        

        $sql = '
        UPDATE rm_movie SET
          title   = ?,
          synopsys    = ?,
          year     = ?,
          price    = ?,
          image    = ?,
          imdb    = ?,
          youtube    = ?,
          director    = ?,
          length    = ?,
          rating    = ?,
          updated = NOW()
        WHERE 
          id = ?
        ';
        $params = array($title, $synopsys, $year, $price, $image, $imdb, $youtube, $director, $length, $rating, $id);
        $res = $this->db->ExecuteQuery($sql, $params);
        if($res) {
            $this->output .= 'Informationen sparades.';
        }
        else {
            $this->output .= 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
         
        return $this->output . "<p><a href='movies.php'>Visa alla</a></p>";
    }

    private function updateForm($id) {
        // Select from database
        $sql = 'SELECT * FROM rm_movie WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Misslyckades: det finns inget innehåll med sådant id.');
        }
        // Sanitize content before using it.
        $title  = htmlentities($c->title, null, 'UTF-8');
        $synopsys   = htmlentities($c->synopsys, null, 'UTF-8');
        $year    = htmlentities($c->year, null, 'UTF-8');
        $price   = htmlentities($c->price, null, 'UTF-8');
        $image   = htmlentities($c->image, null, 'UTF-8');
        $imdb   = htmlentities($c->imdb, null, 'UTF-8');
        $youtube   = htmlentities($c->youtube, null, 'UTF-8');
        $director   = htmlentities($c->director, null, 'UTF-8');
        $length   = htmlentities($c->length, null, 'UTF-8');
        $rating   = htmlentities($c->rating, null, 'UTF-8');
        
        // Prepare checkboxes for genres
        $sql = 'SELECT G.*, MG.idMovie AS checked FROM genre AS G LEFT OUTER JOIN (SELECT * FROM`rm_movie2genre` WHERE idMovie = ?) AS MG ON MG.idGenre = G.id ORDER BY G.name';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        $tickBoxes = null;
        foreach ($res as $row) {
            $genre = htmlentities($row->name);
            $checked = $row->checked ? 'checked' : null;
            $tickBoxes .= <<<EOD
<label>{$genre} <input type='checkbox' {$checked} name='genres[]' value='{$row->id}'/> </label>
EOD;
        }

        return  <<<EOD
<form method=post enctype='multipart/form-data'>
  <fieldset>
  <legend>Uppdatera innehåll</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><label>Titel:<br/><input type='text' name='title' value='{$title}'/></label></p>
  <p><label>IMDB:<br/><input type='text' name='imdb' value='{$imdb}'/></label></p>
  <p><label>Youtube:<br/><input type='text' name='youtube' value='{$youtube}'/></label></p>
  <p><label>Synopsys:<br/><textarea name='synopsys'>{$synopsys}</textarea></label></p>
  <p><label>År:<br/><input type='text' name='year' value='{$year}'/></label></p>
  <p><label>Längd(min):<br/><input type='text' name='length' value='{$length}'/></label></p>
  <p><label>Regissör:<br/><input type='text' name='director' value='{$director}'/></label></p>
  <p><label>Rating:<br/><input type='text' name='rating' value='{$rating}'/></label></p>
  <p><label>Pris:<br/><input type='text' name='price' value='{$price}'/></label></p>
  <p><label>Bild:<br/><input type='text' name='image' value='{$image}'/></label></p>
  <p><label>Bild:<br/><input type='file' name='imagef'/></label></p>
  <p>{$tickBoxes}</p> 
  <p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>
  <p><a href='movies.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }


    private function doDelete($id) {
        $sql = 'DELETE FROM rm_movie2genre WHERE idMovie = ?';
        $res = $this->db->ExecuteQuery($sql, array($id));
        if(!$res) {
            die('Not able to delete genre2movie content. ' . print_r($this->db->ErrorInfo(), 1));
        }

        $sql = 'DELETE FROM rm_movie WHERE id = ?';
        $res = $this->db->ExecuteQuery($sql, array($id));
        if($res) {
            $this->output = 'Innehållet raderades.';
        }
        else {
            $this->output = 'Innehållet raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='movies.php'>Visa alla</a></p>";
    }
    private function deleteForm($id) {
        // Select information on the content 
        $sql = 'SELECT * FROM rm_movie WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Failed: There is no content with that id');
        }
        return <<<EOD
<form method=post>
  <fieldset>
  <legend>Radera innehåll: {$c->title}</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><input type='submit' name='delete' value='Radera'/></p>
  <p><a href='movies.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
    private function slugify($str) {
        $str = str_replace(array('å','ä','ö','Å','Ä','Ö'), array('a','a','o','A','A','O'), $str);
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = trim(preg_replace('/-+/', '-', $str), '-');
        return $str;
    }
}
