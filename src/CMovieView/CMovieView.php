<?php
/**
 * 
 *
 */
class CMovieView {
    /**
    * Members
    */
    private $title;
    private $path;
    /**
    * Constructor creating a PDO object connecting to a choosen database.
    *
    * @param array $options containing details for connecting to the database.
    *
    */
    public function __construct() {
        $this->path = array(
            array('text' => 'Hem', 'url' => 'home.php'),
            array('text' => 'Filmer', 'url' => 'movies.php'),
        );
    }

    public function getTitle($title = "Filmer") {
        return isset($this->title) ? $this->title : $title;
    }

    public function getBreadcrumb($path = null) {
        $breadcrumb = "<ul class='breadcrumb'>\n";
        foreach ($this->path as $part) {
            $breadcrumb .= "<li><a href='{$part['url']}'>{$part['text']}</a> » </li>\n";
        }
        $breadcrumb .= "</ul>\n";
        return $breadcrumb;
    }
    
    public function getDetails($res) {
        if (isset($res[0])) {
            $c=$res[0];
        } else {
            die('Not such movie.');
        }
        $this->title = $c->title;
        $this->path[] = array('text' => htmlentities($c->title), 'url' => "?id={$c->id}");
        
        return<<<EOD
<div class='movieDetails'>
  <p>{$c->synopsys}</p>
  <p><strong>IMDB:</strong> <a href='http://www.imdb.com/title/{$c->imdb}'>{$c->imdb}</a><br>
    <strong>Längd:</strong> {$c->length}<br>
    <strong>Genre:</strong> {$c->genre}<br>
    <strong>Regissör:</strong> {$c->director}<br>
    <strong>År:</strong> {$c->year}<br>
    <strong>Rating:</strong> {$c->rating}%<br>
    <strong>Pris:</strong> {$c->price} SEK</p>
    <p><img src="img.php?src=img/movie/{$c->image}&height=500"> 
    <iframe width='500' height='500' src='https://www.youtube.com/embed?listType=search&list={$c->youtube}      '></iframe></p>
</div>    
EOD;
    }

    public function getTable($res, $rows) {
        // Get parameters 
        $genre     = isset($_GET['genre'])  ? htmlentities($_GET['genre'])  : null;
        $hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
        $page     = isset($_GET['page'])  ? $_GET['page']  : 1;
        $id     = isset($_GET['id'])  ? $_GET['id']  : null;
        if ($id !== null) {
            return $this->getDetails($res);
            exit;
        }

        if ($genre) {
            $this->path[] = array('text' => $genre, 'url' => "?genre=$genre");
        }
        
        // Check that incoming parameters are valid
        is_numeric($hits) or die('Check: Hits must be numeric.');
        is_numeric($page) or die('Check: Page must be numeric.');

        // Prepare the header row
        $admin = CUser::isAuthenticated() ? '<th>Admin</th>': NULL;
        $tr = "<tr>
        <th>Bild</th>
        <th>Titel " . CFunc::orderby('title') . "</th>
        <th>Synopsis</th>
        <th>Rating " . CFunc::orderby('rating') . "</th>
        <th>Genre</th>
        <th>Pris</th>
        {$admin}
        </tr>";


        // Prepare the data rows
        foreach($res AS $key => $val) {
            $tmp = explode(',', $val->genre);
            $genres = [];
            foreach($tmp as $gen) {
                $genres[] = "<a href='?genre=$gen'>$gen</a>";
            }
            $genres = implode('<br>', $genres);        

            $admin = CUser::isAuthenticated() ? "<th><a href='movie_edit.php?id={$val->id}'>Edit</a> <a href='movie_delete.php?id={$val->id}'>Delete</a></th>" : NULL;
            $synopsys = $val->synopsys;
            $words = explode(' ', $synopsys);
            if (count($words) >= 30) {
                $synopsys = implode(' ', array_splice($words, 0, 25));
                $synopsys .= " ...  <a href='?id={$val->id}'>Read more</a>";
            }  

            $tr .= "<tr>
            <td><a href='?id={$val->id}'>
                <img src=img.php?src=img/movie/{$val->image}&amp;width=75&amp alt='{$val->title}'/>
            </a></td>
            <td>{$val->title}</td>
            <td>{$synopsys}</td>
            <td>{$val->rating}%</td>
            <td>{$genres}</td>
            <td>{$val->price} SEK</td>
             {$admin}
            </tr>";
        }

        // Prepare navigation bars and admin information 
        $max = ceil($rows / $hits);
        $hitsPerPage = CFunc::getHitsPerPage(array(4, 8, 16), $hits);
        $navigatePage = CFunc::getPageNavigation($hits, $page, $max);
        $adminAnchors = CUser::isAuthenticated() ? "<p><a href='movie_create.php'>Ny film</a> <a href='movie_reset.php'>Återställ filmdatabasen</a></p>" : null;

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