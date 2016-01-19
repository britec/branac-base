<?php 
/**
 * A C100 class for the 100-game.
 *
 */
class C100 {

    /**
    * Properties
    *
    */
    private $playerNo;
    private $players;
    private $hand;
    private $gameOver;

    // Save the points for a the current player, init a new round, swap player and return the active player. 
    private function saveAndSwap($points) {
        $this->players[$this->playerNo]->points += $points;
        $this->players[$this->playerNo]->rounds++;
        $this->playerNo = ($this->playerNo + 1) % count($this->players);
        $this->hand->InitRound();
        return $this->players[$this->playerNo];
    }

    // Get the active player 
    private function getCurrentPlayer() {
        return $this->players[$this->playerNo];
    }
  
  /**
   * Constructor
   *
   * Creates the player objects and a round object
   */
  public function __construct($humans = 1, $autos = 0) {

    $this->players = array(); 
    $humans = max(min(5, $humans), 1);
    $autos = max(min(1, $autos), 0);
    for ($i=1; ($humans--); $i++) {
        $this->players[] = new CPlayer("Spelare_$i");        
    }
    for ($i=1; ($autos--); $i++) {
        $this->players[] = new CPlayer("Datorn", 'auto');        
    }
    $this->playerNo = 0;
    $this->hand = new CDiceHand(1);
    $this->gameOver = false;    
  }

    /**
    * Destructor
    *
    */
    public function __destruct() {
    //echo __METHOD__;
    }

    // Returns a boolean telling if the game has finished
    public function IsOver() {
        return $this->gameOver;
    }
  
    // Run the game
    public function Play() {
        // Get the player information
        $player = $this->getCurrentPlayer();

        // Prepare default presentation naterial if no valid query string
        $diceImages = $this->hand->GetRoundAsImageList();
        $resultMessage = "En runda är igång för {$player->name}. Rundans poäng hittills är {$this->hand->GetRoundTotal()}. ";
        
        // Get the arguments from the query string
        $auto = isset($_GET['auto']) || $player->typeIsAuto ? true : false; // to run in auto, always for auto player
        $roll = isset($_GET['roll']) || $auto ? true : false; // to roll the dice, also in auto mode
        $save = isset($_GET['save']) ? true : false; // to save the round 


        do {
            if($roll) {
                $this->hand->Roll(); // roll the dice
                $diceImages = $this->hand->GetRoundAsImageList(); // prepare the dice images of the round
                $total = $this->hand->GetRoundTotal() + $player->points;
                $resultMessage = "Rundans poäng hittils är {$this->hand->GetRoundTotal()} ($total totalt). Du kan fortsätta."; // message               
                // Round lost: prepare message, next player and exit auto loop 
                if ($this->hand->GetTotal() == 1) {
                    $resultMessage = "{$player->name} slog en 1:a. Inga poäng sparade. Ny runda."; 
                    $player = $this->saveAndSwap(0);
                    $auto = false;
                }
                // 100 points - game over: prepare message, save-result exit auto loop 
                if ($this->hand->GetRoundTotal() + $player->points > 99) {
                    $resultMessage = "{$player->name} har vunnit!";
                    $player = $this->saveAndSwap($this->hand->GetRoundTotal());
                    $this->gameOver = true;
                    $auto = false;
                }
                // 20 point of a round: Save strategy in auto mode
                if($this->hand->GetRoundTotal() > 19) {
                    $roll = false;
                    $save = true;
                }
            }
            else if($save) {
                // Prepare message, save the points and prepare next player, and exit auto mode
                $resultMessage = "Poängen ({$this->hand->GetRoundTotal()}) har sparats för {$player->name}. Ny runda.";
                $player = $this->saveAndSwap($this->hand->GetRoundTotal());
                $auto = false;
            }
        } while ($auto);

               
        // Prepare the game controller section
        $saveItem = $this->hand->GetRoundTotal() > 0 ? "<a href='?save'>Spara rundans poäng</a>" : null;
        $gameControl = "<strong>Spelkontroll ({$player->name})</strong><br>\n";
        $gameControl  .= $player->typeIsAuto ? "<a href='?auto'>Låt datorn spela denna runda</a>" : <<<EOD
<a href='?roll'>Gör ett kast</a><br>
$saveItem
EOD;
        $gameControl = $this->gameOver ? null : $gameControl; 
      
        // Prepare the consolidated results table  
        $resultsList = "<strong>Resultatställning</strong><br>";
        foreach ($this->players as $val) {
            $resultsList .= "<span>{$val->name} har {$val->points} poäng efter {$val->rounds} rundor.</span><br>";  
        }

        $diceClass = $this->hand->GetRoundTotal() > 0 ? null : "class='obsolete'"; 

        // Compile the html output: Message field, Results table, Game controller, Dicearea, New-game option 
        $html = <<<EOD
<div $diceClass>$diceImages</div>
<p><strong>Meddelande</strong><br>$resultMessage</p>
<p>$resultsList</p>
<p>$gameControl</p>
<p><a href='?destroy'>Starta nytt spel</a>.</p>
EOD;

        return  $html;
    }

}
