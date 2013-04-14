<?php
namespace DevBieres;

/**
 * Un tour dans une partie
 */
class Run {

    const ROCK = 1;
    const PAPER = 2;
    const SCISSORS = 3;


   /**
    * Choix du joueur 1
    * @var int
    */
   private $_p1Choice = -1;
   /**
    * Choix du joueur 2
    * @return int
    */
   public function getP1Choice() { return $this->_p1Choice; }
   /**
    * Affectation du choix du joueur 2
    * @return int
    */
   public function setP1Choice($value) { $this->_p1Choice = $value; $this->chooseWinner(); }

   
   /**
    * Choix du joueur 2
    * @var int
    */
   private $_p2Choice = -1;
   /**
    * Choix du joueur 2
    * @return int
    */
   public function getP2Choice() { return $this->_p2Choice; }
   /**
    * Affectation du choix du joueur 2
    * @return int
    */
   public function setP2Choice($value) { $this->_p2Choice = $value; $this->chooseWinner(); }

   /**
    * Le joueur 1 est-il le vainqueur ?
    * @var bool
    */
   private $_p1Winner = false;
   /**
    * Le joueur 1 est-il le vainqueur ?
    * @return bool
    */
   public function IsPlayer1Winner() { return $this->_p1Winner; }
   /**
    * Le joueur 2 est-il le vainqueur ?
    * @var bool
    */
   private $_p2Winner = false;
   /**
    * Le joueur 2 est-il le vainqueur ?
    * @return bool
    */
   public function IsPlayer2Winner() { return $this->_p2Winner; }
   
   /**
    * Est-ce que la partie a un vainqueur ?
    * @return bool
    */
   public function HasWinner() {
     return ($this->IsPlayer1Winner() || $this->IsPlayer2Winner());
   } // Fin de HasWinner

   /**
    * Est-ce que le tour est fini ?
    * @return bool
    */
   public function IsFinished() {
     echo "Choix du joueur 1 :" . $this->getP1Choice() . "\n"; 
     echo "Choix du joueur 2 :" . $this->getP2Choice() . "\n"; 
     return ( ($this->getP1Choice() != -1) && ($this->getP2Choice() != -1));
   } // Fin de IsFinished
   
   
   /**
    * Calcul de la vainqueur
    */
   private function chooseWinner() {
      echo " Appel de chooseWinner \n";
      // Est-ce que la partie est finie ?
      if($this->IsFinished()) {
           // Si les deux ont joués la même chose ...
           if($this->getP1Choice() == $this->getP2Choice()) {
               $this->_p1Winner = false;
               $this->_p2Winner = false;
           } else {
              // Gestion des différentes règles
              // TODO : trouver un moyen plus propre que les gros IFs ...
              if( ($this->getP1Choice() == Run::ROCK) && ($this->getP2Choice() == Run::SCISSORS)) { 
                  // La pierre base les ciseaux
                  $this->_p1Winner = true;
                  $this->_p2Winner = false;
               } // ROCK VS SCISSORS
                  
              if( ($this->getP1Choice() == Run::ROCK) && ($this->getP2Choice() == Run::PAPER)) { 
                  // La pierre est battue par les papiers
                  $this->_p1Winner = false;
                  $this->_p2Winner = true;
              } // ROCK VS PAPER 

              if( ($this->getP1Choice() == Run::SCISSORS) && ($this->getP2Choice() == Run::ROCK)) { 
                  // La pierre bat les ciseaux 
                  $this->_p1Winner = false;
                  $this->_p2Winner = true;
              } // SCISSORS vs ROCK

              if( ($this->getP1Choice() == Run::SCISSORS) && ($this->getP2Choice() == Run::PAPER)) { 
                  // Les ciseaux coupent le papier 
                  $this->_p1Winner = true;
                  $this->_p2Winner = false;
              } // SCISSORS vs PAPER

              if( ($this->getP1Choice() == Run::PAPER) && ($this->getP2Choice() == Run::ROCK)) { 
                  // Le papier bat la pierre
                  $this->_p1Winner = true;
                  $this->_p2Winner = false;
              } // PAPER vs ROCK 

              if( ($this->getP1Choice() == Run::PAPER) && ($this->getP2Choice() == Run::SCISSORS)) { 
                  // Les ciseaux coupent le papier
                  $this->_p1Winner = false;
                  $this->_p2Winner = true;
              } // PAPER vs SCISSORS

           } // Fin du calcul 
      } // Si la partie n'est pas finie : on ne fait rien
   } // Fin du chooseWinner


   /**
    * Retourne un tableau pour le tour
    * TODO : voir s'il n'existe pas en PHP une méthode standardisée
    */
   public function getArray() {
      return array("p1choice" => $this->getP1Choice(), "p2Choice" => $this->getP2Choice(), "hasWinner" => $this->HasWinner());
   } // Fin de getArray()

}
