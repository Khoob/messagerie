<?
/*
Presque toutes les propri�t�s de la classe ont une valeur par d�faut
vous pouvez bien entendu les changer dans le script start_chat.php en
sp�cifiant l'instance de l'objet suivi de la propri�t� $objet->propri�t�=valeur.
*/
class Chat_Server{
//Nombre de connexions concurrentes au maximum
     var $max_clients=10;
//Un tableau qui contiendra les ID de sockets de tous les clients connect�s
     var $clients=array();
//La socket "ma�tre" sur laquelle le serveur �coute
     var $socket=null;
//Contiendra l'id de chaque nouvelle connexion
     var $client=null;
//Contient un message � afficher lorsqu'une connexion est refus�e
     var $denied;
//0=afficher les infos sur l'�cran, 1=enregistrer les log dans un fichier
     var $log=0;
//Nom du fichier log o� stocker les infos
     var $logfile='ChatServerLog.log';
//Ressource du fichier log
     var $fp_log;
//Contient l'en-t�te html � envoyer � chaque nouveau client
     var $html;
//Si un pseudo est d�j� pris, envoyer ce message au client avant de refuser sa connexion
     var $Already_In_use;
//Type d'info que l'on envoie au(x) client(s)
     var $write_type=0;
//M�thode qui d�marre le serveur
     function Start($adress,$port){
              $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
              //on lie la ressource sur laquelle le serveur va �couter
              socket_bind($this->socket, $address, $port) or die($this->destroy(null));
              //On pr�pare l'�coute
              socket_listen($this->socket);
              //Boucle infinie car le serveur ne doit s'arr�ter que si on lui demande
              while(true){
               //Le code se bloque jusqu'� ce qu'une nouvelle connexion cliente est �tablie
                $this->client=socket_accept($this->socket);
               //Lors d'une connexion, le code reprend ici, il est temps de lire ce qu'on nous envoie
                $this->read_write($this->client);
              }

     }

//Cette m�thode lit les donn�es re�ues par un client et les redistribue
     function read_write(){
           //L'en-t�te fait 5 bytes, donc on lit 5 bytes et on v�rifie s'il s'agit d'une connexion
           //cliente ou si il s'agit d'un envoi de message ou encore s'il s'agit de stopper le serveur
            $input = socket_read($this->client, 5);
            //11111 est le signal d'arr�t du serveur, vous pourriez en d�finir un autre
            if($input=='11111'){
               $this->write_type=4;
               //On envoie un message � tous les clients, notifiant l'arr�t du serveur
               $this->Write_To_Clients("<script language=\"javascript\">alert('Le serveur a �t� arr�t�')</script>");
               // Temporisation de 3 secondes pour que les clients aient le temps de lire le message
               sleep(3);
               //On appelle la m�thode qui arr�te le serveur proprement
               $this->Destroy(null);
            }
            //Si le mot cl� "get" figure dans l'en-t�te, c'est qu'il s'agit d'une nouvelle connexion
            if(substr_count($input,'GET')>0){ // Nouvelle connexion au chat.
              //Si le nombre maximum autoris� de connexions n'est pas atteint
               if($this->max_clients > count($this->clients)){
                //On lit les 50 octets suivants pour r�cup�rer le pseudo
                  $nick=urldecode(socket_read($this->client,23));
                //On r�cup�re le pseudo
                  if(substr_count($nick,'Pseudo')==0){
                    //Acc�s refus� car pseudo invalide
                    socket_close($this->client);
                    return;
                  }
                  $nick=trim(substr($nick,(strpos($nick,'=')+1),15));//On isole le pseudo
                  if(substr_count($nick,' ')>0 || $nick==null){
                    socket_close($this->client);
                    return;
                  }

                //On tente d'obtenir l'IP du client.
                  socket_getpeername($this->client,&$adress,&$port);
                //On v�rifie que le pseudo n'est pas d�j� utilis�
                  if($this->clients[$nick]==null){
                        //On ajoute la connexion au tableau des connexions
                        $this->clients[$nick]=$this->client;
                        //On avertit les autres que ce client vient de se connecter
       			$this->Write_Connected();
       			$this->write_type=1;
       			//On met � jour la liste de tous les connect�s chez tous les clients
                        $this->Write_To_Clients($nick.':'.$adress);
                        //On enregistre ou affiche qu'une nouvelle connexion a �t� �tablie
                        $this->Logging('Nouvelle connexion client : '.$adress.':'.$port);
                  }
                  else{
                   //Si le pseudo est d�j� utilis�, on refuse la connexion
                       $this->write_type=4;
		       @socket_write($this->client,$this->html.$this->Already_In_use,(strlen($this->Already_In_use)+strlen($this->html)));
		       /*temporisation d'une seconde avant la fermeture de la connexion, c'est pas l'id�al car �a p�nalise
                       les performances mais sans temporisation, le client n'a pas le temps de voir le message*/
		       sleep(1);
		       //Fermeture de la connexion
                       @socket_close($this->client);
                  }

               }
               else{
                  @socket_write($this->client,$this->denied,strlen($this->denied));
                  sleep(1);
                  socket_getpeername($this->client,&$adress,&$port);
                  $this->Logging('Client '.$this->client.' : '.$adress.':'.$port.' a eu un acc�s refus�');
                  @socket_close($this->client);
               }

            }
            else{
                $paquet=socket_read($this->client,intval(substr($input,0,2))+intval(substr($input,2,3)));
                $pseudo=substr($paquet,0,intval(substr($input,0,2)));
                $message=substr($paquet,intval(substr($input,0,2)),intval(substr($input,2,3)));
                $this->Wrap_Message(&$message);
                $full_client_msg="<font color='#FF0000'> [$pseudo a �crit:]<font><font color='#AF00AF'>$message</font><br>";
                $this->write_type=2;
                $this->Write_To_Clients($full_client_msg);
            }
     }


     function Write_To_Clients($msg){
        reset($this->clients);
        if($this->write_type==1){
           $info=split(':',$msg);
           $add_to_list="<script language=\"javascript\">add_opt(\"".$info[0]."\",\"".$info[0]."\");</script>";
           $info_board="<script language=\"javascript\">document.getElementById('info_board').innerHTML=\"".$info[0]."-".$info[1]." vient de se connecter\";</script>";
        }

        if($this->write_type == 2){
           $full_msg="<script language=\"javascript\">document.getElementById('content').innerHTML+=\"".$msg."\"</script>";
        }

        if($this->write_type == 4){
           $full_msg=$msg;
        }



        while ($value = current($this->clients)) {
              if(is_resource($value)){
                 if($this->write_type == 1){
                    if($value != $this->client){
                       	$full_msg=$add_to_list.$info_board;
                    }
                    else{
                        $full_msg=$info_board;
                    }
                 }
                      $this->Logging('Ecriture de '.$msg.' to '.$value);
                      if((@socket_write($value,$full_msg,strlen($full_msg))===false)){
                        $this->Logging ('d�connexion de '.key($this->clients).':'.$value);
                        $disconnected[]=key($this->clients);
                        unset($this->clients[key($this->clients)]); //Si l'�criture vers un client ne fonctionne pas, on en d�duit qu'il est d�connect�
                      }

             }
              next($this->clients);
        }

        if(count($disconnected)>0){ //si il y a eu des d�connect�s.
          $msg="<script language=\"javascript\">document.getElementById('info_board').innerHTML=\"";
          for($i=0;$i<count($disconnected);$i++){
            $msg.=$disconnected[$i]." s'est d�connect�<br>";
            $msg1.="<script language=\"javascript\">remove_opt('".$disconnected[$i]."');";
          }
          $msg.="\";</script>";
          $msg1.="</script>";
          $fullmsg=$msg.$msg1;
          $this->write_type=4;
          $this->Write_To_Clients($fullmsg); //Appel r�cursif pour informer les autres.
        }
        else{
            	return;
        }

     }

     //Cette m�thode rajoute des <br> pour limiter le nombre de caract�res par ligne.
     function Wrap_Message(&$msg){
       $j=0;
       for($i=0;$i<strlen($msg);$i++){
          $msg_wrapped.=$msg[$i];
          if($j == 50){
            $msg_wrapped.='<br>';
            $j=0;
          }
          $j++;
       }
          $msg=$msg_wrapped;
     }

     //Cette m�thode envoie � tous les clients ceux qui sont connect�s
     function write_Connected(){
        reset($this->clients);
        while ($value = current($this->clients)) {
              if(is_resource($value)){
		$msg.="<script language=javascript>add_opt(\"".key($this->clients)."\",\"".key($this->clients)."\")</script>";
              }

              next($this->clients);
        }
	$full_msg=$this->html.$msg;
	@socket_write($this->client,$full_msg,strlen($full_msg));

     }
     //Cette m�thode donne des infos sur le processing du serveur
     //On peut soit, stocker l'info dans un fichier log, soit afficher l'output
     //sur la sortie standard.
     function Logging($msg){
              if($this->log == 1){
                if(empty($this->fp_log)){
                  $this->fp_log=fopen($this->logfile,'w') or die($this->destroy('Erreur de cr�ation du fichier log'));
                }
                fwrite($this->fp_log,$msg."\n");
              }
              else{
                echo "\n".$msg."\n";
              }
              return;
     }

     //Cette m�thode est appel�e lorsque l'on stoppe le serveur
     //et le stoppe de mani�re propre en fermant toutes les connexions
     //clients et en leur envoyant un message au pr�alable.
     function destroy($err){
          if($err != null){
                  $this->Logging($err);
          }
          else{
              	$this->Logging(socket_strerror(socket_last_error()));
          }
          reset($this->clients);
          while ($sock_cli = current($this->clients)) {
	        @socket_close($sock_cli);
	        next($this->clients);
          }

          if(is_resource($fp)){
            fclose($fp);
          }

         @socket_close($this->socket);
          die();
     }

}

?>
