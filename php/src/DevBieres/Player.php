<?php
namespace DevBieres;

use Ratchet\ConnectionInterface;

/**
 * Classe Player (joueur) : permet de stocker différentes informations
 */
class Player {

   /**
    * Identifiant
    * @var string
    */
   private $_id;
   /**
    * Retourne l'identifiant
    * @return string
    */
   public function getID() { return $this->_id; }

   /**
    * Le surnom du joueur
    * @var string
    */
   private $_username;
   /**
    * Retourne le surnom
    * @return string
    */
   public function getUsername() { return $this->_username; }
   /**
    * Spécialisation de la méthode toString()
    */
   public function __toString() { return $this->getUsername(); }

   /**
    * La connexion associée au joueur
    * @var ConnectionInterface
    */
   private $_conn;
   /**
    * Retourne la connexion pour communiquer avec le joueur
    * @return ConnectionInterface
    */
   public function getClient() { return $this->_conn; }
   public function getConnId() { return $this->getClient()->resourceId; }

   /**
    * Le score du joueur
    * @var int
    */
   private $_score = 0;
   /**
    * Retourne le score du joueur
    * @return int
    */
   public function getScore() { return $this->_score; }

   /**
    * Constructeur d'un joueur
    * @param string $pusername
    * @param ConnectionInterface $pconn : la connexion
    */
   public function __construct($pusername, ConnectionInterface $pconn) {
       $this->_username = $pusername;
 
       $this->_id = strtolower(str_replace(" ","",  $this->_username));
       
       $this->_conn = $pconn;
   } // Constructeur d'un joueur
  
   /**
    * Le joueur est un gagnant !
    */
   public function IsAWinner() { $this->_score += 10; }
   /* Fin de IsAWinner */

   /**
    * Le joueur est un perdant !
    */
   public function IsALooser() { $this->_score -= 10; }
   /* Fin de IsALooser */

   /**
    * Retourne une chaîne au format JSON pour le joeur
    */
   public function getArray() {
      // 
      return array("id" => $this->getID(), "username" => $this->getUsername(), "score" => $this->getScore(), "conn" => $this->getClient()->resourceId);
   }

   /**
    * Retourne une chaîne au format JSON pour le joeur
    */
   public function getJSON() {
      // 
      return json_encode( $this->getArray() );
   }

}


