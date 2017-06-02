<?

class Chat_Server{
     var $max_clients=10;
     var $clients=array();
     var $socket=null;
     var $client=null;
     var $denied;
     var $log=1;
     var $logfile='ChatServerLog.log';
     var $fp_log;
     var $html;
     var $Already_In_use='<script language="javascript">alert(\'Ce pseudo est déjà utilisé, connexion refusée\');</script>';
     var $status=0;
     function Start($adress,$port){
              $this->denied=$this->html.'<script language="javascript">alert(\'Le nombre de clients maximal a été atteint. Accès refusé!\');</script>';
              $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
              socket_bind($this->socket, $address, $port) or die($this->destroy(null));
              socket_listen($this->socket);
              while(true){
                $this->client=socket_accept($this->socket);
                $this->read_write($this->client);
              }

     }

     function read_write(){
            $input = socket_read($this->client, 5);
            if(substr($input,0,5)=='11111'){ //Signal d'arrêt du serveur
               $this->status=4;
               $this->Write_To_Clients("<script language=\"javascript\">alert('Le serveur a été arrêté')</script>");
               sleep(3); // Arrêt de 3 secondes pour que les clients aient le temps de lire le message
               $this->Destroy(null);
            }

            if(eregi('get',$input)){ // Nouvelle connexion au chat.
               if($this->max_clients > count($this->clients)){
                  $nick=socket_read($this->client,50);
                  $nick=substr($nick,(strpos($nick,'=')+1),(strpos($nick,' ')-7));//On isole le pseudo
                  socket_getpeername($this->client,&$adress,&$port);
                  if($this->clients[$nick]==null){ // on vérifie qu'il n'y a pas de session active pour ce pseudo
                        $this->clients[$nick]=$this->client;
       			$this->Write_Connected();
       			$this->status=1;
                        $this->Write_To_Clients($nick.':'.$adress);
                        $this->Logging('Nouvelle connexion client : '.$adress.':'.$port);
                  }
                  else{
                       $this->status=4;
		       @socket_write($this->client,$this->html.$this->Already_In_use,(strlen($this->Already_In_use)+strlen($this->html)));
		       sleep(1); //arrêt d'une seconde avant la fermeture de la connexion
                       @socket_close($this->client);//sinon, on la refuse et on la ferme!
                  }

               }
               else{
                  @socket_write($this->client,$this->denied,strlen($this->denied));
                  sleep(1);
                  socket_getpeername($this->client,&$adress,&$port);
                  $this->Logging('Client '.$this->client.' : '.$adress.':'.$port.' a eu un accès refusé');
                  @socket_close($this->client);
               }

            }
            else{
                $paquet=socket_read($this->client,intval(substr($input,0,2))+intval(substr($input,2,3)));
                $pseudo=substr($paquet,0,intval(substr($input,0,2)));
                $message=substr($paquet,intval(substr($input,0,2)),intval(substr($input,2,3)));
                $this->Wrap_Message(&$message);
                $full_client_msg="<font color='#FF0000'> [$pseudo a écrit:]<font><font color='#AF00AF'>$message</font><br>";
                $this->status=2;
                $this->Write_To_Clients($full_client_msg);
            }
     }


     function Write_To_Clients($msg){
        reset($this->clients);
        if($this->status==1){
           $info=split(':',$msg);
           $add_to_list="<script language=\"javascript\">add_opt(\"".$info[0]."\",\"".$info[0]."\");</script>";
           $info_board="<script language=\"javascript\">document.getElementById('info_board').innerHTML=\"".$info[0]."-".$info[1]." vient de se connecter\";</script>";
        }

        if($this->status == 2){
           $full_msg="<script language=\"javascript\">document.getElementById('content').innerHTML+=\"".$msg."\"</script>";
        }

        if($this->status == 4){
           $full_msg=$msg;
        }



        while ($value = current($this->clients)) {
              if(is_resource($value)){
                 if($this->status == 1){
                    if($value != $this->client){
                       	$full_msg=$add_to_list.$info_board;
                    }
                    else{
                        $full_msg=$info_board;
                    }
                 }
                      $this->Logging('Writing '.$msg.' to '.$value);
                      if((@socket_write($value,$full_msg,strlen($full_msg))===false)){
                        $this->Logging ('déconnexion de '.key($this->clients).':'.$value);
                        $disconnected[]=key($this->clients);
                        unset($this->clients[key($this->clients)]); //Si l'écriture vers un client ne fonctionne pas, on en déduit qu'il est déconnecté
                      }

             }
              next($this->clients);
        }

        if(count($disconnected)>0){ //si il y a eu des déconnectés.
          $msg="<script language=\"javascript\">document.getElementById('info_board').innerHTML=\"";
          for($i=0;$i<count($disconnected);$i++){
            $msg.=$disconnected[$i]." s'est déconnecté<br>";
            $msg1.="<script language=\"javascript\">remove_opt('".$disconnected[$i]."');";
          }
          $msg.="\";</script>";
          $msg1.="</script>";
          $fullmsg=$msg.$msg1;
          $this->status=4;
          $this->Write_To_Clients($fullmsg); //Appel récursif pour informer les autres.
        }
        else{
            	return;
        }

     }

     //Cette méthode rajoute des <br> pour limiter le nombre de caractères par ligne.
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

     //Cette méthode envoie à tous les clients ceux qui sont connectés
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
     //Cette méthode donne des infos sur le processing du serveur
     //On peut soit, stocker l'info dans un fichier log, soit afficher l'output
     //sur la sortie standard.
     function Logging($msg){
              if($this->log == 1){
                if(empty($this->fp_log)){
                  $this->fp_log=fopen($this->logfile,'w') or die($this->destroy('Erreur de création du fichier log'));
                }
                fwrite($this->fp_log,$msg."\n");
              }
              else{
                echo "\n".$msg."\n";
              }
              return;
     }

     //Cette méthode est appelée lorsque l'on stoppe le serveur
     //et le stoppe de manière propre en fermant toutes les connexions
     //clients et en leur envoyant un message au préalable.
     function destroy($err){
          if($err != null){
                  $this->Logging($err);
          }
          else{
              	$this->Logging(socket_strerror(socket_last_error()));
          }

          for($i=0;$i<count($this->clients);$i++)
            @socket_close($this->clients[$i]);

          if(is_resource($fp)){
            fclose($fp);
          }

         @socket_close($this->socket);
          die();
     }

}

?>
