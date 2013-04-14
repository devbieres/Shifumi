/**
 Declarations des variables
 */
var conn;
var deconnexionDemande = 0;


$(document).ready(function() {
   console.log("La page est chargée !");

   // Quelques abonnements nécessaires:)
   console.log("Abonnement aux events ");
   $('#btnConnexion').click(function() { connexion(); return false; });
   $('#btnDeconnexion').click(function() { deconnexion(); return false; });
   $('#btnMessage').click(function() { sendMessage(); return false; });

   $.templates({
        runTmpl: {
          markup: "#runTemplate",
          helpers: {
               formatUtils: {
                   format: function(val) {
                      switch(val) {
                         case "1" : return "Pierre"; break;
                         case "2" : return "Papier"; break;
                         case "3" : return "Ciseaux"; break;
                      }
                      
                   } // fin de format
               }
          } // helpers
        } // runTmpl
   }); // fin de templates

});


/**
 * Envoie d'un message
 */
function sendMessage() {
   console.log("Envoie d'un message");
   // Récupération du message
   var msg = $('#txtMessage').val();
   console.log(msg);
   //
   if(msg.length > 0) {
     conn.send("CHAT:" + msg);
   }

} /* Fin de sendMessage */

/** 
 * Gestion de la connexion au serveur
 */
function connexion() {

   // Preparation de la connexion
   console.log('connexion');
   var surname = $('#txtSurname').val();
   console.log(surname);
   if(surname.length  <= 0) { bootbox.alert('Il faut renseigner le champ ...'); return; }

   // Creation de la web socket et abonnenement aux différents évènements
   console.log("Creation de la websocket ");
   conn = new WebSocket('ws://localhost:8080');
   conn.onopen = function(e) { 
       console.log(e); 
       console.log(conn);
       conn.send('CNX:' + surname);
   };
   conn.onmessage = function(e) { handleMessage(e);  };
   conn.onerror = function(e) { bootbox.alert("Une erreur est surnenue"); console.log(e); };
   conn.onclose = function(e) { 
       if(deconnexionDemande==0) { bootbox.alert("Impossible de se connecter ou déconnexion du serveur");  }
       handleDeconnexionCallBack(); 
   };
   

} /* Fin de connexion */

function deconnexion() {
  deconnexionDemande = 1;
  conn.close(); 
}

/**
 * Traitement d'un message. En fonction du prefix, va effectuer différent traitement
 */
function handleMessage(e) {
  console.log("Gestion d'un message");
  console.log(e);
  
  // Les actions dépendent des messages qui sont reçus :)
  var msg = e.data;

  // Split selon le premier :
  console.log(msg);
  var indexOf = msg.indexOf(':');
  var key = '';
  var value = '';
  if(indexOf > 0) {
    key = msg.substring(0, indexOf);  
    value = msg.substring(indexOf+1, msg.length);
  } else {
    key = msg;
  }

  // En fonction de la chaîne
  switch(key) {
    case "CNXCF" : handleConnexionConfirmed(); break;
    case "ALREADYCONNECTED" : bootbox.alert("Vous êtes déjà connecté"); break; 
    case "CHAT": handleChat(value); break;
    case "ALLPLAYERS": handleAllPlayers(value); break;
    case "NEWPLAYER": handleNewPlayer(value); break;
    case "LEAVEPLAYER": handleLeavePlayer(value); break;
    case "GAME": handleGameMessage(value); break;
  } // Fin du swicth
} /* Fin d'handleMessage */

/**
 * Un joueur est partie : le lâche !!!
 */
function handleLeavePlayer(value) {
  console.log("handleLeaverPlayer");
  var infos = JSON.parse(value);
  
  $("#p_" + infos.id).remove();
  

} // Fin d'handleLeaverPlayer

/**
 * Un nouveau joueur se connecte : ajout à la liste
 */
function handleNewPlayer(value) {
   console.log("handleNewPlayer");
   infos = JSON.parse(value);
   this.addPlayer(infos);
   $('a.challenge').click(function(e) { sendChallenge(this); return false; });
}

/**
 * Mise à jour de la liste des joueurs
 */
function handleAllPlayers(json) {
   console.log("handleAllPlayers");
   // DécodageA
   var infos = JSON.parse(json);
   // Vidage de la liste
   $("#lstPlayers").empty();
   // Boucle sur les infos
   for(var j = 0; j < infos.length; j++) { 
          console.log(infos[j]);
          this.addPlayer(infos[j]); 
   }
   $('a.challenge').click(function(e) { sendChallenge(this); return false; });
} // handleAllPlayers

function addPlayer(p) {
      $("#lstPlayers").append("<li id='p_" + p.id  + "' > <a href='#' class='challenge' data-conn='" + p.conn + "' ><i class='icon-fire' ></i></a>" +  p.username + "(" + p.score + ") </a></li>");
}


/**
 * Réception d'un message 
 */
function handleChat(json) {
  // Decodage
  var infos = JSON.parse(json);
  console.log(infos);
 
  // Generation de la ligne
  var str = "<strong>" + infos.from + "</strong>: " + infos.msg + "<br/>";

  // Html 
  // TODO : Gérer une liste pour supprimer quand trop de message
  $('#divMessages').html( str + $('#divMessages').html() );

} /* Fin d'handleChat */

/**
 * Effectue les actions suite à la confirmation de connexion
 */
function handleConnexionConfirmed() {
    // Modification du bouton de déconnexion
    $('#btnDeconnexion').html($('#txtSurname').val());
    // Affichage des zones associées à la connexion
    $('#divConnected').removeClass('hidden');
    $('#divMain').removeClass('hidden');
    // Masquage de la zone de connexion
    $('#divConnexion').addClass('hidden');
} // Fin d'handleConnexionConfirmed

function handleDeconnexionCallBack() {
  console.log("Gestion d'un retour de déconnexion");
  // Affichage de la zone contenant le bouton de déconnexion
  $('#divConnected').addClass('hidden');
  $('#divMain').addClass('hidden');
  // Masquage de la zone de connexion
  $('#divConnexion').removeClass('hidden');
  // Pour la partie jeu
  remiseEnAttente();
  //
  deconnexionDemande = 0;
  
} // Fin d'handleDeconnexionCallBack
