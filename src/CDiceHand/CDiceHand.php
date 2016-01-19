<?php
/**
 * A hand of dices, with graphical representation, to roll.
 *
 */
class CDiceHand {

  /**
   * Properties
   *
   */
  private $dices;
  private $numDices;
  private $sum;
  private $sumRound;
  private $rollsRound;


  /**
   * Constructor
   *
   * @param int $numDices the number of dices in the hand, defaults to six dices. 
   */
  public function __construct($numDices = 5) {
    for($i=0; $i < $numDices; $i++) {
      $this->dices[] = new CDice(6);
    }
    $this->numDices = $numDices;
    $this->sum = 0;
    $this->sumRound = 0;
    $this->rollsRound = array ();
  }


  /**
   * Roll all dices in the hand.
   *
   */
  public function Roll() {
    $this->sum = 0;
    for($i=0; $i < $this->numDices; $i++) {
      $roll = $this->dices[$i]->Roll(1);
      $this->sum += $roll;
      $this->sumRound += $roll;
      $this->rollsRound[] = $roll;
    }
  }


  /**
   * Get the sum of the last roll.
   *
   * @return int as a sum of the last roll, or 0 if no roll has been made.
   */
  public function GetTotal() {
    return $this->sum;
  }


  /**
   * Init the round.
   *
   */
  public function InitRound() {
    $this->sumRound = 0;
    $this->rollsRound = array ();
  }


  /**
   * Get the accumulated sum of the round.
   *
   * @return int as a sum of the round, or 0 if no roll has been made.
   */
  public function GetRoundTotal() {
    return $this->sumRound;
  }


  /**
   * Get the rolls as a serie of images.
   *
   * @return string as the html representation of the last roll.
   */
  public function GetRollsAsImageList() {
    $html = "<ul class='dice'>";
    foreach($this->dices as $dice) {
      $val = $dice->GetLastRoll();
      $html .= "<li class='dice-{$val}'></li>";
    }
    $html .= "</ul>";
    return $html;
  }
  
  /**
   * Get the round as a serie of images.
   *
   * @return string as the html representation of all rolls.
   */
  public function GetRoundAsImageList() {
    $html = "<ul class='dice'>";
    foreach($this->rollsRound as $val) {
      $html .= "<li class='dice-{$val}'></li>";
    }
    $html .= "</ul>";
    return $html;
  }


}
