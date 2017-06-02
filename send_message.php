<?
/*
L'interface client est consitutée de deux frames, ce script-ci est dans la frame du bas
et permet au client d'envoyer un message au serveur qui le retransmet à tous les clients.
*/
?>
<html>
<head>
<style>
input{
      background-color:#E7F2F8;
}
</style>
</head>
<body background="images/background2.jpg">
<center><form name="msg" method="post">
<textarea style="background-color:#E7F2F8;color:#AF00AF;" rows="2" cols="50" name="message"></textarea>
 <input type="submit" value="Envoyer">
</form></center>
</body>
</html>

<?
define('HEADER',5);
require_once 'chat_config.php';
if(!empty($_POST['message'])){
//remplacement des caractères enter par des espaces
 $message=ereg_replace(chr(13).chr(10),' ',$_POST['message']);
//Creation de la socket
 $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('Création de socket refusée');
//Connexion au serveur
 socket_connect($sock,$address,$port) or die('Connexion impossible');
//Codage de la longueur du Pseudo
 $header=sprintf('%02d',strlen($_GET['Pseudo']));
//Codage de la longueur du message
 $header.=sprintf('%03d',strlen($message));
//Construction du paquet à envoyer au serveur
 $paquet=$header.$_GET['Pseudo'].$message;
//Calcul de la longueur du paquet
 $write_len=strlen($paquet)+HEADER;
//Ecriture du paquet vers le serveur
 socket_write($sock,$paquet,$write_len);
//Fermeture de la connexion
 socket_close($sock);
}
?>
