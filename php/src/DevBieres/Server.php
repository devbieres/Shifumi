<?php
namespace DevBieres;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Au tout départ, la classe est basée sur http://socketo.me/docs/hello-world
 * Puis modifié pour les besoins de la cause :)
 */
class Server implements MessageComponentInterface {

    protected $_clients;
    protected $_players;
    /**
     * Retourne un joueur par son id
     * @param int $id
     * @return Player
     */
    public function getPlayerByConn($id) {
        if(array_key_exists($id, $this->_players)) { return $this->_players[$id]; }
        else { return null; }
    }

    protected $_gameServer;

    public function __construct() {
        $this->_clients = new \SplObjectStorage();
        $this->_players = array();

        $this->_gameServer = new GameServer($this);
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->_clients->attach($conn);

        echo "Nouvelle connexion ({$conn->resourceId})\n";
    }

    /**
     * onMessage
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        /*
        $numRecv = count($this->clients) - 1;
         echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        */

        // Le message contient un prefix. En fonction du prefix, le traitement est différent
        $msg = explode(':', $msg);
        if(count($msg) < 1) { echo "Le message n'est pas conforme \n"; }
        else {
               // En fonction du prefix
               switch( strtoupper($msg[0])) {
                    case 'CNX': $this->handleConnexion($from, $msg[1]); break;
                    case 'DCNX': $this->handleDeconnexion($from); break;
                    case 'CHAT': $this->handleMessage($from, $msg[1]); break;
                    // Pour tous les messages associés au jeu : délégation à une classe dédiée
                    case 'GAME': $this->_gameServer->handleMessage($from, $msg);
               }// Fin du switch
        }
    } /* Fin de onMessage */

    /**
     * Diffusion d'un message
     */
    public function handleMessage(ConnectionInterface $from, $message) {
        // --> Récupération du joueur
        $j = $this->_players[$from->resourceId];
        // --> Creation du message 
        $arr = array(
                      'from' => $j->getUsername(),
                      'msg' => $message
                    );
        $json = json_encode($arr);

        // --> Message
        $this->sendThemAll("CHAT:" . $json);
        

    } // Fin d'handleMessage

    /**
     * Envoie un message à tous les joueurs
     */
    public function sendThemAll($msg, $nothim = null) {
        // --> Envoie le message à tous les utilisateurs
        foreach ($this->_clients as $client) { 
           if($client !== $nothim) {  $client->send($msg); }
        }
    }

    /**
     * Une demande connexion
     */
    public function handleConnexion(ConnectionInterface $from, $username) {
        echo sprintf("handleConnexion %s \n", $username);

        // Creation de la cle
        $key = $from->resourceId;

        // Validation si déjà connecté
        if(array_key_exists($key, $this->_players)) { 
           // Oui : on previent l'utilisateur
   	   $from->send('ALREADYCONNECTED'); 
        } else {
           // Non
           // TODO : gérer une unicité des surnoms
           // --> creation et enregistrement  du joueur
           $p = new Player($username, $from);
           $this->_players[$key] =  $p;
           // --> on previent le joueur qu'il est enregistre
           $from->send("CNXCF");
           // --> Envoyer une liste des à jours complètes des jours uniquement au nouveau connecté
           $this->sendListPlayers($from);
           // --> Envoyer l'info sur le nouveau joueur aux autres joueurs
           $this->sendThemAll("NEWPLAYER:" . $p->getJSON(), $from);
           
           
        } // Fin de validation

    } /* fin d'handleConnexion */

    /**
     * Envoie la liste des joueurs à la cible
     */
    public function sendListPlayers($from = null) {
       // Construction de la liste
       $arr = array();
       // Boucle
       foreach($this->_players as $j) { $arr[] = $j->getArray();  }
       // Message
       $msg = sprintf("ALLPLAYERS:%s", json_encode($arr));
       // Envoie
       if($from != null) {
             $from->send($msg);
       } else {
             $this->sendThemAll($msg, $from);
       }
    } // Fin de sendAllPlayers

    /** 
     * Deconnexion
     */
    public function handleDeconnexion($from) {
        echo sprintf("handleDeconnexion \n");
        // Creation de la cle
        $key = $from->resourceId;
        // Suppression
        if(array_key_exists($key, $this->_players)) { 
            // Gestion du tableau
            $p = $this->_players[$key];
            unset($this->_players[$key]);
            // --> Info sur la déconnexion d'un joueur
            $this->sendThemAll("LEAVEPLAYER:" . $p->getJSON(), $from);

            // TODO : Gérer s'il est en train de jouer !
        }
    } /* fin d'handleDeconnexion */

    public function onClose(ConnectionInterface $conn) {
        // gestion d'une déconnexion
        $this->handleDeconnexion($conn);

        // The connection is closed, remove it, as we can no longer send it messages
        $this->_clients->detach($conn);

        // Trace
        echo "Connection {$conn->resourceId} has disconnected\n";
    } // onClose

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        // gestion d'une déconnexion
        $this->handleDeconnexion($conn);

        $conn->close();
    }


}
