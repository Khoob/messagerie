<?
        require_once 'chat_config.php';
        require_once 'chat.php';
        //Normalement le chat est d�marr� via php-cli, donc cette directive n'est pas indispensable
        //Cependant on peut d�marrer le chat via le browser, et l�, elle le devient
        ini_set("max_execution_time",0);
        //Instanciation de la classe chat_server
        $chat = new Chat_Server();
        //On assigne le html du fichier config � la propri�t� html de l'objet
	$chat->html=$html;
	$chat->denied=$chat->html.$denied;
	$chat->Already_In_use=$chat->html.$already_in_use;
	//On d�marre le serveur de chat
        $chat->Start($address,$port);
?>
