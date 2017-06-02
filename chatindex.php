<? require_once 'chat_config.php';
if(!isset($_GET['Pseudo'])){
  die('Pseudo obligatoire!');
}
if(strlen($_GET['Pseudo'])>15 || substr_count($_GET['Pseudo'],' ')>0){
  die('Le pseudo ne peut comporter d\'espace et doit faire au maximum 15 octets');
}
$pseudo=sprintf('% 15s',$_GET['Pseudo']);
?>
<frameset rows="80%,*" border="0">
<frame noresize src="http://<? echo $address.':'.$port;?>?Pseudo=<? echo urlencode($pseudo);?>">
<frame noresize src="send_message.php?Pseudo=<? echo $_GET['Pseudo'];?>">
</frameset>
