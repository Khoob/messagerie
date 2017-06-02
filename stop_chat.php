<?
/*
Ce script permet de stopper le serveur proprement. Lorsque le serveur lira cette en-tête, il appelera la métode 
destroy() qui avertira les clients que le serveur est arrêté et les déconnectera. Ensuite, le serveur sera arrêté.
*/
 require_once 'chat_config.php';
 $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die('Could not create socket');
 socket_connect($sock,$address,$port) or die('Could not connect');
 socket_write($sock,'11111');
 socket_close($sock);
?>
