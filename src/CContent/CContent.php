<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CContent {
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
    public function dump() {
        return $this->db->dump();
    }
    
    // Publik function to reset the content database
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

    // Returns a list of all contents
    public function getList() {
        $sql = 'SELECT *, (published <= NOW()) AS available FROM rm_content WHERE 1';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
        // Put results into a list
        $items = null;
        foreach($res AS $key => $val) {
            $items .= "<li>{$val->type} (" . (!$val->available ? 'inte ' : null) . "publicerad): " . htmlentities($val->title, null, 'UTF-8') . " (<a href='content_edit.php?id={$val->id}'>editera</a> <a href='content_delete.php?id={$val->id}'>radera</a> <a href='" . $this->getUrlToContent($val) . "'>visa</a>)</li>\n";
        }
        return <<<EOD
<p>$this->output</p>
<p>Här är en lista på allt innehåll i databasen.</p>
<ul>{$items}</ul>
<p><a href='content_create.php'>Lägg till innehåll</a></p>
<p><a href='content_reset.php'>Återställ databasen</a></p>
EOD;
    }


    private function getUrlToContent($content) {
      switch($content->type) {
        case 'page': return "content_page.php?url={$content->url}"; break;
        case 'post': return "content_blog.php?slug={$content->slug}"; break;
        default: return null; break;
      }
    }
       

    //Functions to reset the database
    private function doReset() {
        $cmd = $this->cmd . "  < rm_content_reset.txt 2>&1";
        $output = null;
        $res = exec($cmd, $output);
        // dump($cmd);
        // dump($res);
        // dump($output);

        $this->output = "<p>Databasen är återställd";
        return $this->output . "<p><a href='content_view.php'>Visa alla</a></p>";
    }
    private function resetForm() {
        return <<<EOD
<form method=post>
<input type=submit name=restore value='Återställ databasen'/>
<p><a href='content_view.php'>Visa alla</a></p>
</form>
EOD;
    }

    /**
     * Create a slug of a string, to be used as url.
     *
     * @param string $str the string to format as slug.
     * @returns str the formatted slug. 
     */
    private function slugify($str) {
        $str = str_replace(array('å','ä','ö','Å','Ä','Ö'), array('a','a','o','A','A','O'), $str);
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = trim(preg_replace('/-+/', '-', $str), '-');
        return $str;
    }
    private function doCreate($creator) {
        // Get parameters 
        $title  = isset($_POST['title']) ? $_POST['title'] : null;
        $slug   = $this->slugify($title);
        $url    = $this->slugify($title);
        $type   = 'post';
        $filter = 'markdown';
        // $published = date('Y-m-d H:i:s');
        $owner = $creator;
        $category = 'Nyheter';
        $sql = '
        INSERT INTO rm_content (title, slug, url, type, filter, published, owner, category, created, updated) VALUES
            (?, ?, ?, ?, ?, NOW(), ?, ?, NOW(), NOW())';

        $url = empty($url) ? null : $url;
        $params = array($title, $slug, $url, $type, $filter, $owner, $category);
        $res = $this->db->ExecuteQuery($sql, $params);

        if($res) {
            $this->output = 'Innehållet skapades.';
            $this->db->SaveDebug();
            header('Location: content_edit.php?id=' . $this->db->LastInsertId());
            exit;
        } else {
            $this->output = 'Innehållet skapades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='content_view.php'>Visa alla</a></p>";
    }
    private function createForm() {
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Skapa nytt innehåll</legend>
  <p><label>Titel:<br/><input type='text' name='title' value=''/></label></p>
  <p class=buttons><input type='submit' name='create' value='Skapa'/> <input type='reset' value='Återställ'/></p>
  <p><a href='content_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }
    
    // Update database
    private function doUpdate($id) {
        // Get parameters 
        $title  = isset($_POST['title']) ? $_POST['title'] : null;
        $slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
        $url    = isset($_POST['url'])   ? strip_tags($_POST['url']) : null;
        $data   = isset($_POST['data'])  ? $_POST['data'] : array();
        $type   = isset($_POST['type'])  ? strip_tags($_POST['type']) : array();
        $filter = isset($_POST['filter']) ? $_POST['filter'] : array();
        $published = isset($_POST['published'])  ? strip_tags($_POST['published']) : array();
        $owner = !empty($_POST['owner'])  ? strip_tags($_POST['owner']) : array();
        $category = isset($_POST['category'])  ? strip_tags($_POST['category']) : array();
        $sql = '
        UPDATE rm_content SET
          title   = ?,
          slug    = ?,
          url     = ?,
          data    = ?,
          type    = ?,
          filter  = ?,
          published = ?,
          owner = ?,
          category = ?,
          updated = NOW()
        WHERE 
          id = ?
        ';
        $url = empty($url) ? null : $url;
        $params = array($title, $slug, $url, $data, $type, $filter, $published, $owner, $category, $id);
        $res = $this->db->ExecuteQuery($sql, $params);
        if($res) {
            $this->output = 'Informationen sparades.';
        }
        else {
            $this->output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='content_view.php'>Visa alla</a></p>";
    }
    private function updateForm($id) {
        // Select from database
        $sql = 'SELECT * FROM rm_content WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Misslyckades: det finns inget innehåll med sådant id.');
        }
        // Sanitize content before using it.
        $title  = htmlentities($c->title, null, 'UTF-8');
        $slug   = htmlentities($c->slug, null, 'UTF-8');
        $url    = htmlentities($c->url, null, 'UTF-8');
        $data   = htmlentities($c->data, null, 'UTF-8');
        $type   = htmlentities($c->type, null, 'UTF-8');
        $filter = htmlentities($c->filter, null, 'UTF-8');
        $published = htmlentities($c->published, null, 'UTF-8');
        $owner = htmlentities($c->owner, null, 'UTF-8');
        $category = htmlentities($c->category, null, 'UTF-8');
        return  <<<EOD
<form method=post>
  <fieldset>
  <legend>Uppdatera innehåll</legend>
  <input type='hidden' name='id' value='{$id}'/>
  <p><label>Titel:<br/><input type='text' name='title' value='{$title}'/></label></p>
  <p><label>Text:<br/><textarea name='data'>{$data}</textarea></label></p>
  <p><label>Filter (markdown,link,nl2br):<br/><input type='text' name='filter' value='{$filter}'/></label></p>
  <p><label>Publiseringsdatum:<br/><input type='text' name='published' value='{$published}'/></label></p>
  <p><label>Owner:<br/><input type='text' name='owner' value='{$owner}'/></label></p>
  <p><label>Category (e.g. Nyheter, Film, Info, Företaget):<br/><input type='text' name='category' value='{$category}'/></label></p>
  <p><label>Type (post or page):<br/><input type='text' name='type' value='{$type}'/></label></p>
  <p><label>Slug:<br/><input type='text' name='slug' value='{$slug}'/></label></p>
  <p><label>Url:<br/><input type='text' name='url' value='{$url}'/></label></p>
  <p class=buttons><input type='submit' name='save' value='Spara'/> <input type='reset' value='Återställ'/></p>
  <p><a href='content_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }


    private function doDelete($id) {
        $sql = 'DELETE FROM rm_content WHERE id = ?';
        $res = $this->db->ExecuteQuery($sql, array($id));
        if($res) {
            $this->output = 'Innehållet raderades.';
        }
        else {
            $this->output = 'Innehållet raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }
        return $this->output . "<p><a href='content_view.php'>Visa alla</a></p>";
    }
    private function deleteForm($id) {
        // Select information on the content 
        $sql = 'SELECT * FROM rm_content WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
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
  <p><a href='content_view.php'>Visa alla</a></p>
  <output>{$this->output}</output>
  </fieldset>
</form>
EOD;
    }

    /**
     * Create a link to the content, based on its type.
     *
     * @param object $content to link to.
     * @return string with url to display content.
     */

    //Returns page contents based on url
    public function getContentByUrl($url) {
        $sql = "
        SELECT *, date(published) AS pubdate
        FROM rm_content
        WHERE
          type = 'page' AND
          url = ? AND
          published <= NOW();
        ";
        return $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));
    }

    //Returns blog contents based on slug
    public function getPostBySlug($slug, $limit=null) {
        $slugSql = $slug ? 'slug = ?' : '1';
        $limitSql = $limit ? "LIMIT $limit" : null; 
        $sql = "
        SELECT *, date(published) AS pubdate
        FROM rm_content
        WHERE
          type = 'post' AND
          $slugSql AND
          published <= NOW()
        ORDER BY updated DESC 
        $limitSql;
        ";
        return $this->db->ExecuteSelectQueryAndFetchAll($sql, array($slug));
    }

    //Returns blog contents based on category
    public function getPostByCategory($category, $limit=null) {
        $catSql = $category ? 'category LIKE ?' : '1';
        $limitSql = $limit ? "LIMIT $limit" : null; 
        $sql = "
        SELECT *, date(published) AS pubdate
        FROM rm_content
        WHERE
          type = 'post' AND
          $catSql AND
          published <= NOW()
        ORDER BY updated DESC 
        $limitSql;
        ";
        return $this->db->ExecuteSelectQueryAndFetchAll($sql, array($category));
    }
    //Returns blog contents based on category
    public function getPostCategories() {
        $sql = "SELECT category FROM rm_content WHERE type = 'post' GROUP BY category;";
        return $this->db->ExecuteSelectQueryAndFetchAll($sql, array());
    }

    //Returns published blog items formated for dynamic navbar
    public function getBlogItems() {
        $sql = "SELECT *  FROM rm_content
        WHERE
          type = 'post' AND
          published <= NOW()
        ORDER BY updated DESC";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
        // Put results into a list
        $items = [];
        foreach($res AS $key => $val) {
            $title = htmlentities($val->title, null, 'UTF-8');
            $url = $this->getUrlToContent($val);
            $items[$key] = [
                'class' => 'blogItem',
                'text' => $title,
                'url' => $url,
                'title' => "Blogposten $title", 
            ];
        }
        return $items;
    }

    //Returns published page items formated for dynamic navbar
    public function getPageItems() {
        $sql = "SELECT * FROM rm_content
        WHERE
          type = 'page' AND
          published <= NOW()
        ORDER BY updated DESC";
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
        // Put results into a list
        $items = [];
        foreach($res AS $key => $val) {
            $title = htmlentities($val->title, null, 'UTF-8');
            $url = $this->getUrlToContent($val);
            $items [$key] = [
                'class' => 'pageItem',
                'text' => $title,
                'url' => $url,
                'title' => "Sidan $title", 
            ];
        }
        return $items;
    }
}