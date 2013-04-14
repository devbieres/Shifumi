<?php
namespace DevBieres;

use Ratchet\ConnectionInterface;

/**
 * Le server "délègue" la partie gestion des parties à cette classe.
 */
class GameServer {

    /**
     * Le serveur
     * @var Server
     */
    private $_server;

    /**
     * Les jeux en cours : indexes par clé
     * @var array
     */
    private $_games;

    /**
     * Les parties par identifiant utililisateur
     * @var array
     */
    private $_gamesByConn;
    /**
     * Retourne vrai si le joueur est déjà entrain de jouer
     * @param int $id l'identifiant de la connexion du joueur
     */
    public function IsPlaying($id) {
           return array_key_exists($id, $this->_gamesByConn); 
    } // Fin de isPlaying

    /**
     * Retourne le jeu associé au joueur
     * @param int $id l'identifiant de la connexion du joueur
     */
    public function getGameByConn($id) {
        if($this->IsPlaying($id)) { return $this->_gamesByConn[$id]; } 
        else { return null; }
    }

    /**
     * Instanciation : le serveur est en param
     * @param Server
     */
    public function __construct($pserver) {
       $this->_server = $pserver;

       $this->_games = array();
       $this->_gamesByConn = array();
    }

    /**
     * Gestion du message
     * le format est GAME:<CLE>:<INFO>. Normalement le split est déjà fait
     * @param ConnectionInterface $from
     * @param array $msg
     */
    public function handleMessage(ConnectionInterface $from, $msg) {

        // En fonction de la clé
        switch(strtoupper($msg[1])) {
           case 'CHALLENGE' : $this->handleNewChallenge($from, $msg); break;
           case 'CHALLENGEACCEPTED' : $this->handleChallengeAccepted($from, $msg); break;
           case 'CHALLENGEREFUSED' : $this->handleChallengeRefused($from, $msg); break;
           case 'CHOICE': $this->handleChoice($from, $msg); break;
        } // Fin du switch

    } // Fin du message

    /**
     * Fin du jeu : nettoyage des éléments conservés
     * @param Game $game
     */
    protected function finishTheGame(Game $game) {

       // Suppression de la liste des jeux
       if(array_key_exists($game->getId(), $this->_games )) { unset($this->_games[$game->getId()]); } 

       // Suppression des joueurs
       if($this->IsPlaying($game->getPlayer1()->getConnId())) { 
            unset($this->_gamesByConn[$game->getPlayer1()->getConnId()]);
       }
       if($this->IsPlaying($game->getPlayer2()->getConnId())) { 
            unset($this->_gamesByConn[$game->getPlayer2()->getConnId()]);
       }


       // Envoie de la liste des joueurs mises à jours
       $this->_server->sendListPlayers();

    } // Fin de finishTheGame

    /**
     * Un des joueurs a fait son joie : il faut mettre le jeu à jour
     */
    public function handleChoice($from, $msg) {
       echo "Un jour à joué \n";
       $msgSend = "";
      
       // TODO : mettre en place les contrôles : est-il vraiment en train de jouer ? le jeu est-il en cours ?
       // Récupération du jeu
       $game = $this->getGameByConn($from->resourceId);
       if($game == null) { return; }

       // On demande au jeu d'enregistrer la bonne valeur
       $game->setPlayerChoice($from->resourceId, $msg[2]);
       echo "Run finish : " . $game->IsRunFinish() . "\n";
       
       if($game->IsGameFinish()) {
          // Le jeu est terminé !
          // Le principe est le même que pour la fin d'un tour mais avec quelques traitements en plus :)
          $game->getWinner()->IsAWinner();
          $game->getLooser()->IsALooser();
          
          // Preparation du message
          $arr["game"] = $game->getArray();
          $arr["run"] = $game->getCurrent()->getArray();
          $arr["runwinner"] = $game->getRunWinner();
          $json = json_encode($arr);

          // Deux messages : un pour le gagnant / un pour le perdant !
          $game->getWinner()->getClient()->send('GAME:WINNER:' . $json);
          $game->getLooser()->getClient()->send('GAME:LOOSER:' . $json);

          // La partie est terminée ==> nettoyage
          $this->finishTheGame($game);

       }
       else if($game->IsRunFinish()) {
           // Le tour est fini :  quoi qu'il arrive il faudra communiquer aux joueurs au moins la fin du tour

           // Preparation du message
           $arr = array();
           // TODO : Mettre cela dans la classe Game
           $arr["game"] = $game->getArray();
           $arr["run"] = $game->getCurrent()->getArray();
           $arr["runwinner"] = $game->getRunWinner();
           $msgSend = 'GAME:RUNFINISH:' . json_encode($arr);

           $game->getPlayer1()->getClient()->send($msgSend);
           $game->getPlayer2()->getClient()->send($msgSend);

           // Creation du nouveau run
           $game->NewRun();
       } 

    } // Fin d'handleChoice

    /**
     * La challenge est refusé !
     * @param ConnectionInterface $from
     * @param array $msg
     */
    public function handleChallengeRefused(ConnectionInterface $from, $msg) {
      // Ici très simple : envoie simplement du message à celui qui avait challengé
      $p1 = $this->_server->getPlayerByConn($msg[2]);
      $p1->getClient()->send('GAME:CHALLENGEREFUSED');
    } // Fin d'handleChallengeRefused

    /**
     * Le challenge est accepté, le jeu peut commencer :)
     * @param ConnectionInterface $from
     * @param array $msg
     */
    public function handleChallengeAccepted($from, $msg) {
        echo "handleChallengeAccepted\n";
        // Récupération des infos
        $p1Conn = $msg[2]; // l'index 2 doit contenir le connexion du joueur à l'origine 
        if($this->IsPlaying($p1Conn)) { 
            // Tsss : en fait, il a déjà une partie en cours !
            $from->send('GAME:ALREADYPLAYING'); 
            return; 
        }
        $p2Conn = $from->resourceId;
        if($this->IsPlaying($p2Conn)) { 
            // Tsss, en fait, il a déjà une partie en cours
            $from->send('GAME:ALREADYPLAYING'); 
            return; 
        }
        
        // Non il ne joue pas : récupération du joueur
        $p1 = $this->_server->getPlayerByConn($p1Conn);
        $p2 = $this->_server->getPlayerByConn($p2Conn);

        // Creation et enregistrement
        $game = new Game($p1, $p2);
        $this->_games[$game->getId()] = $game;
        $this->_gamesByConn[$p1Conn] = $game;
        $this->_gamesByConn[$p2Conn] = $game;

        // Previent les deux joueurs que la partie est commencé
        // Preparation du flux
        $arr = array();
        $arr["game"] = $game->getId();
        $arr["p1"] = $p1->getArray();
        $arr["p2"] = $p2->getArray();
        $msg = 'GAME:START:' . json_encode($arr);
         
        $p1->getClient()->send($msg);
        $p2->getClient()->send($msg);

    } // Fin d'handleChallengeAccepted

    /**
     * Yes !! Un nouveau défi ?
     * @param ConnectionInterface $from
     * @param array $msg
     */
    public function handleNewChallenge(ConnectionInterface $from, $msg) {
        echo "handleNewChallenge\n";
        echo "P2 : $msg[2] \n";
        echo "$from->resourceId \n"; 
        // Récupération des infos
        $p2Conn = $msg[2]; // l'index 2 doit contenir la connexion du joueur
        // Test si le joueur n'essaye pas de se challenger (le fourbe !)
        if($p2Conn == $from->resourceId) { $from->send('GAME:AUTO'); return; }
        // Test si le joueur défié n'est pas déjà entrain de jouer
        if($this->IsPlaying($p2Conn)) { $from->send('GAME:ALREADYPLAYING'); return; }

        // Non il ne joue pas : récupération du joueur
        $p1 = $this->_server->getPlayerByConn($from->resourceId);
        $p2 = $this->_server->getPlayerByConn($p2Conn);
        if($p2 == null) { $from->send('GAME:UNKNOWPLAYER'); return; }

        // Oui, il est connu : envoie du défi : les deux joueurs sont prévenues
        $from->send('GAME:CHALLENGESEND:' . $p2Conn);
        $p2->getClient()->send('GAME:YOURCHALLENGED:' . $p1->getJSON());

    } // fin de handleNewChallege


}
