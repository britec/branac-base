<?php 
/**
 * A C100Wrapper class to run the 100-game. Gets an ongoing game from the session or starts a new one.
 *
 */
class C100Wrapper {

    /**
    * Properties
    *
    */
    
    /**
    * Constructor
    *
    */
    public function __construct() {

    }

    /**
    * Destructor
    *
    */
    public function __destruct() {
    //echo __METHOD__;
    }


    public function runGame() {

        if(isset($_GET['destroy'])) {
          // Unset the game session variable.
          $_SESSION['oneHundred'] = null;
        }

        // Create a new game object and save it in the session variable. 
        if(isset($_GET['players'])) {
            switch($_GET['players']) {
                case 2 :
                    $game = new C100(2);
                    break;
                case 3 :
                    $game = new C100(3);
                    break;
                case 0 :
                    $game = new C100(1, 1);
                    break;
                default :
                    $game = new C100();
            }
            $_SESSION['oneHundred'] = $game;
        }


        // Start a new game or get it from the session
        if(isset($_SESSION['oneHundred'])) {
            // Objektet finns redan i sessionen
            $game = $_SESSION['oneHundred'];
            $html = $game->Play();
            if ($game->IsOver()) {
                $_SESSION['oneHundred'] = null;
            }
        } else {
            $html = <<<EOD

<p><strong>*** Just nu har vi en rolig tävling. Vinner du väntar en gratisfilm på dig i kassan. Läs reglerna nedan och spela på. Lycka till! *** </strong></p>  
<p> Ett enkelt tärningsspel. Slå tärningen upprepade gånger och avsluta rundan med att spara poängsumman. Slå så många gånger du vill, men får du en etta går rundan förlorad. Först till 100 vinner. Utmana dig själv, dina vänner, eller spela mot datorn.</p>
<p>
    <a href = '?players=1'>Spela själv</a><br>
    <a href = '?players=2'>Två spelare</a><br>
    <a href = '?players=3'>Tre spelare</a><br>
    <a href = '?players=0'>Spela mot datorn</a> <strong> *** Här kan du vinna en gratis film! ***</strong>
</p>
EOD;
        }


        return  <<<EOD
<h1>Tärningsspelet 100</h1>
{$html}    
EOD;
    }

}
