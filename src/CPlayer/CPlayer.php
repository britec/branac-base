<?php 
/**
 * A CPlayer class for the 100-game.
 *
 */
class CPlayer {

    /**
    * Properties
    *
    */
    public $name;
    public $typeIsAuto;
    public $points;
    public $rounds;
  
    /**
    * Constructor
    *
    *
    */
    public function __construct($name = 'Spelare_1', $type = '') {
        $this->name = $name;
        $this->typeIsAuto = $type == 'auto' ? true : false;        
        $this->rounds = 0;
        $this->points = 0;
    }


    /**
    * Destructor
    *
    */
    public function __destruct() {
    //echo __METHOD__;
    }

}
