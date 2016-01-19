<?php
/**
 *
 *
 */
class CBlog {
    /**
    * Members
    */
    private $title;
    private $content;
    private $path;
    /**
    * Constructor 
    *
    */
    public function __construct($dbOptions) {
        $this->content = new CContent($dbOptions);
        $this->title = "Bloggen"; 
        $this->path = array(
            array('text' => 'Hem', 'url' => 'home.php'),
            array('text' => 'Nyheter', 'url' => '?'),
        );
    }
    public function getBreadcrumb() {
        $breadcrumb = "<ul class='breadcrumb'>\n";
        foreach ($this->path as $part) {
            $breadcrumb .= "<li><a href='{$part['url']}'>{$part['text']}</a> » </li>\n";
        }
        $breadcrumb .= "</ul>\n";
        return $breadcrumb;
    }

    public function getMain($limit=null) {
        $filter = new CTextFilter();

        // Get GET-variables 
        $slug    = isset($_GET['slug']) ? $_GET['slug'] : null;
        $category    = isset($_GET['category']) ? $_GET['category'] : null;
        
        // Get blogitems 
        if ($category) {
            $res = $this->content->getPostByCategory($category, $limit);            
            $this->path[] = array('text' => htmlentities($category), 'url' => "?category=$category");
        } else {
            $res = $this->content->getPostBySlug($slug, $limit);
        }

        // Prepare the blogg roll
        $main = null;
        if(isset($res[0])) {
            foreach($res as $c) {
                // Sanitize content before using it.
                $title  = htmlentities($c->title, null, 'UTF-8');
                $category  = htmlentities($c->category, null, 'UTF-8');
                $data   = $filter->doFilter(htmlentities($c->data, null, 'UTF-8'), $c->filter);

                if($slug) {
                    $this->title = "$title | " . $this->title;
                    $this->path[] = array('text' => $category, 'url' => "?category=$category");
                    $this->path[] = array('text' => $title, 'url' => "?id={$c->id}");
                } else {
                    $words = explode(' ', $data);
                    if (count($words) >= 30) {
                        $data = implode(' ', array_splice($words, 0, 25));
                        $data .= " ...  <a href='content_blog.php?slug={$c->slug}'>Read more</a>";
                    }                      
                } 
                $adminLink = CUser::isAuthenticated() ? "<a href='content_edit.php?id={$c->id}'>Edit</a> <a href='content_delete.php?id={$c->id}'>Delete</a>" : null;

                $main .= <<<EOD
<article>
<header>
<h2><a href='content_blog.php?slug={$c->slug}'>{$title}</a></h2>
</header>

{$data}

<footer>
<p>
Publicerad {$c->pubdate} av {$c->owner}<br>
Kategori: <a href=content_blog.php?category{$c->category}>{$c->category}<br>
<a>{$adminLink}</p>
</footer
</article>
EOD;
            }
        }
        else if($slug) {
            $main = "Det fanns inte en sådan bloggpost.";
        }
        else {
            $main = "Det fanns inga bloggposter.";
        }
        return $main;        
    }
    
    public function getTitle() {
        return $this->title;
    }
    public function getCategories($current=null, $page=null) {
        $categories = $this->content->getPostCategories();
        $items = null;
        foreach($categories as $row) {
            $c = $row->category;
            $href = "href='$page?category=$c'"; 
            $selected = ($c == $current) ? "class='selected" : null;
            $items .= "<li><a $selected $href>$c</a></li>";
        }        
        return "<ul class=categories>{$items}</ul>";
    }

    public function dump() {
        return $this->content->dump();
    }
}
