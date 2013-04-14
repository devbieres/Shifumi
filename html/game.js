
/**
 * Envoie le challenge vers le serveur
 */
function sendChallenge(link) {
 console.log('sendChallenge');
 // ---- Communication avec le serveur
 // Récupération de la connexion défiée
 var id  = link.getAttribute('data-conn');
 // Creation du message
 var msg = 'GAME:CHALLENGE:' + id;
 // Envoie
 conn.send(msg);

 // ---- Mise à jour de l'écran
 $('#h2Waiting').addClass('hidden');
 $('#h2Challenge').removeClass('hidden');

}

/**
 * Gestion des messages associés à une partie
 */
function handleGameMessage(msg) {
  // Récupération du message 
  var indexOf = msg.indexOf(':');
  var key = '';
  var value = '';
  if(indexOf > 0) {
    key = msg.substring(0, indexOf);  
    value = msg.substring(indexOf+1, msg.length);
  } else {
    key = msg;
  }

  // Traitement
  switch(key) {
    case "AUTO": 
          bootbox.alert('On ne joue pas contre soit même !'); remiseEnAttente(); break;
          break;
    case "CHALLENGEREFUSED":
          bootbox.alert('Challenge refusé ... le couard !!'); remiseEnAttente(); break;
    case "ALREADYTPLAYING":
          bootbox.alert('Oups : il/elle est déjà entrain de jouer.'); remiseEnAttente(); break;
    case "YOURCHALLENGED":
          var infos = JSON.parse(value);
          bootbox.confirm(infos.username + ' vous lance un défi. Votre réponse ?',
                    function(e) { 
                       if(e) { conn.send('GAME:CHALLENGEACCEPTED:' + infos.conn ); }
                       else { conn.send('GAME:CHALLENGEREFUSED:' + infos.conn  ); }
                    }
          ); // confirm
          break;
     case "START": handleStartGame(value); break; 
     case "RUNFINISH": handleRunFinish(value); break;
     case "LOOSER": handleEndGame(value, 0); break;
     case "WINNER": handleEndGame(value, 1); break;
  } // Fin du switch

} // handleGameMessage


/**
 * Actions communes entre la fin d'un run et la fin du jeu
 */
function majRun(value) {
   // Les infos sont passées en JSON
   var infos = JSON.parse(value);

   // Mise à jour des scores au tableau d'affichage
   $("#p1score").html(infos.game.p1score);
   $("#p2score").html(infos.game.p2score);

   // Mise à jour du suivi des tours
   $("#gameruns").append(
       $.render.runTmpl(infos)
   );

   return infos;
}

/**
 * C'est la fin : alors victoire ou défaite ?
 */
function handleEndGame(value, winner) {

   // Actions communes
   var infos = majRun(value);

   // Calcul du message
   var message = "";
   if(winner) { message = "WELL DONE DUDE !!! Au suivant !"; }
   else { message ="LOOSER !!! Pte que la prochaine fois sera la bonne !"; }

   // Avertissement du joueur
   bootbox.alert(
         message,
         function(e) {
            // la partie est finie : on remet la page comme il faut
            remiseEnAttente();
         }
    ); // Fin 

} // Fin de handleGame

/**
 * Gestion d'une fin de tour
 */
function handleRunFinish(value) {

   // Actions communes
   majRun(value);

   // Blocage des zones
   $('#sltChoice').removeAttr('disabled');
   $('#btnChoice').removeAttr('disabled');
} // Fin d'handleRunFinish

/**
 * Effectue les actions graphiques associés à un démarrage du jeu
 */
function handleStartGame(value) {
   // Les données sont en JSON
   infos = JSON.parse(value);

   // Gestion des éléments
   $('#h2Waiting').addClass('hidden');
   $('#h2Challenge').addClass('hidden');
   $('#h2Game').removeClass('hidden');
   $('#divGame').removeClass('hidden');
   
   // Stockage de l'identifiant de la partie 
   $('#divGame').data('game', value);

   // Rendu graphique de la zone de jeu
   $('#divGame').html( $('#gameTemplate').render(infos) );
   $('#btnChoice').click(function() { handlePlayerValidation(); return false; });
         
}

/** 
 * L'utilisateur a faire son choix : envoie sur le serveur
 */
function handlePlayerValidation() {
   // Blocage des zones
   $('#sltChoice').attr('disabled','disabled');
   $('#btnChoice').attr('disabled','disabled');

   // Récupération des infos nécessaires
   var choice = $('#sltChoice').val();
   console.log('PlayerValidation : ' + choice);
 
   // Creation de la chaîne
   conn.send('GAME:CHOICE:' + choice);
}

function remiseEnAttente() {
   $('#h2Waiting').removeClass('hidden');
   $('#h2Challenge').addClass('hidden');
   $('#h2Game').addClass('hidden');
   $('#divGame').addClass('hidden');
}
