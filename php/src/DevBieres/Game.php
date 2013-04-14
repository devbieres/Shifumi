<?php
namespace DevBieres;

/**
 * Représente une partie en deux joueurs.
 * Une partie est disputé entre deux joueurs. Le premier à deux points à gagné.
 */
class Game {

   /**
    * Identifiant
    * @var string
    */
    private $_id;
   /**
    * Identifiant
    * @return string
    */
   public function getID() { return $this->_id; }

   /**
    * Joueur numéro 1 : normalement celui lance le défi !
    * @var Player
    */
   private $_p1;
   /**
    * Joueur numéro 1 : normalement celui qui lance le défi !
    * @return Player
    */
   public function getPlayer1() { return $this->_p1; }

   /**
    * Score du joueur 1
    * @var int
    */
   private $_p1Score = 0;
   /**
    * Retourne le score du joueur 1
    * @return int
    */
   public function getPlayer1Score() { return $this->_p1Score; }
   /**
    * Modifie le score du joueur 1
    * @param int $value
    */
   public function setPlayer1Score($value) { $this->_p1Score = $value; }

   
   /**
    * Joueur numéro 2 : normalement celui qui accepte le défi !
    * @var Player
    */
   private $_p2;
   /**
    * Joueur numéro 2 : normalement celui qui accepte le défi !
    * @return Player
    */
   public function getPlayer2() { return $this->_p2; }


   /**
    * Score du joueur 2
    * @var int
    */
   private $_p2Score = 0;
   /**
    * Retourne le score du joueur 2
    * @return int
    */
   public function getPlayer2Score() { return $this->_p2Score; }
   /**
    * Modifie le score du joueur 2
    * @param int $value
    */
   public function setPlayer2Score($value) { $this->_p2Score = $value; }


   /**
    * Liste les tours
    * @var array
    */
   private $_runs;
   /**
    * Liste les tours
    * @var array
    */
   public function getRuns() { return $this->_runs; }

   /**
    * Le tour en cours
    * @var Run
    */
   private $_current;
   /**
    * Le tour en cours
    * @var Run
    */
   public function getCurrent() { return $this->_current; }

   /**
    * Retourne vrai si le tour en cours est fini
    * @return bool
    */
   public function IsRunFinish() { return $this->getCurrent()->IsFinished(); }

   public function NewRun() {
      if($this->getCurrent()->IsFinished()) {
          $this->_current = new Run();
          $this->_runs[] = $this->_current;
      }
   } // Fin de NewRun()

   /**
    * Retourne le nom du gagnant
    */
   public function getRunWinner() {
        if($this->IsRunFinish() && $this->getCurrent()->HasWinner()) {
           if($this->getCurrent()->IsPlayer1Winner()) { return $this->getPlayer1()->getUsername(); }
           else { return $this->getPlayer2()->getUsername(); }
        } else { return ""; }
   } // fin de getRunWinner()

   /**
    * Retourne vrai si l'un des deux joeurs a deux points
    * @return bool
    */
   public function IsGameFinish() {
       return (($this->getPlayer1Score() == 2) || ($this->getPlayer2Score() == 2));
   } // Fin de IsGameFinish

   /**
    * Retourne le gagnant
    * @return Player
    */
   public function GetWinner() {
       if(($this->getPlayer1Score() == 2)) { return $this->getPlayer1(); }
       else if(($this->getPlayer2Score() == 2)) { return $this->getPlayer2(); }
       else null;
   } // Fin de GetWinner

   /**
    * Retourne le nom du gagnant
    */
   public function getWinnerName() { return $this->getWinner()->getUsername(); }

   /**
    * Retourne le perdant
    * @return Player
    */
   public function GetLooser() {
       if(($this->getPlayer1Score() == 2)) { return $this->getPlayer2(); }
       else if(($this->getPlayer2Score() == 2)) { return $this->getPlayer1(); }
       else null;
   } // Fin de GetWinner

   /**
    * Retourne le nom du perdant
    */
   public function getLooserName() { return $this->getLooser()->getUsername(); }

   /**
    * Constructeur
    * @param Player p1
    * @param Player p2
    */
   public function __construct(Player $p1, Player $p2) {
      echo "Nouvelle partie entre $p1 et $p2 \n";
      $this->_p1 = $p1;
      $this->_p2 = $p2;

      $this->_id = sprintf("%s_%s", $this->getPlayer1()->getID(), $this->getPlayer2()->getID());
   
      $this->_runs = array();
      $this->_current = new Run();
   } // Fin du constructeur

   /**
    * Affecte le choix du joeur
    */
   public function setPlayerChoice($id, $value) {
       var_dump($value);
       echo "setPlayerChoice : $value \n";
       // En fonction du joueur
       if($this->getPlayer1()->getConnId() == $id) { 
          echo "setPlayerChoice pour le joueur 1: $value \n";
          $this->getCurrent()->setP1Choice($value); 
       }
       else { 
          echo "setPlayerChoice pour le joueur 2: $value \n";
          $this->getCurrent()->setP2Choice($value); 
       }

       // Si le tour est terminé : mise à jour des scores
       if($this->IsRunFinish()) {
           if($this->getCurrent()->HasWinner()) {
               if($this->getCurrent()->IsPlayer1Winner()) { $this->setPlayer1Score($this->getPlayer1Score() + 1); }
               else { $this->setPlayer2Score($this->getPlayer2Score()+1); }              
           }
       } // Fin de la gestion des scores

   } //fin de setPlayerChoice

   /**
    * Retourne un tableau pour le jeu 
    * TODO : voir s'il n'existe pas en PHP une méthode standardisée
    */
   public function getArray() {
      // Tableau par défaut
      $arr =  array("p1name" => $this->getPlayer1()->getUsername(),  "p2name" => $this->getPlayer2()->getUsername(),  "p1score" => $this->getPlayer1Score(), "p2score" => $this->getPlayer2Score());

      // Si la partie est terminée, ajout d'infos
      if($this->IsGameFinish()) {
           $arr["finished"] = true;
           $arr["winner"] = $this->getWinnerName();
      }

      return $arr;
   } // Fin de getArray()

} // Fin de la class
