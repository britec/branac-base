<?php
/**
 * 
 *
 */
class CPage {
    /**
    * Members
    */
    private $title;
    private $main;
    /**
    * Constructor 
    *
    */
    public function __construct($url, $dbOptions) {
        $user = new CUser($dbOptions);
        $content = new CContent($dbOptions);
        $filter = new CTextFilter();

        $res = $content->getContentByUrl($url);

        if(isset($res[0])) {
            $c = $res[0];
        }
        else {
            die('Misslyckades: det finns inget innehåll.');
        }

        // Sanitize content before using it.
        $title  = htmlentities($c->title, null, 'UTF-8');
        $data   = $filter->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);


        // Prepare content and store it all in variables in the Anax container.
        $this->title = $title;

        $editLink = $user->isAuthenticated() ? "<a href='content_edit.php?id={$c->id}'>Uppdatera sidan</a>
        <a href='content_delete.php?id={$c->id}'>Radera sidan</a>" : null;

        $this->main = <<<EOD
<article>
<header>
<h1>{$title}</h1>
</header>

{$data}

<footer>
<p>(Skriven av {$c->owner}. Publicerad {$c->pubdate})</p>
{$editLink}
</footer
</article>
EOD;
    }

    public function getMain() {
        return $this->main;        
    }
    public function getTitle() {
        return $this->title;
    }
}