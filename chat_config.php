<?
//Pour toutes questions, envoyez moi un MP sur le forum en attendant
//que l'on crée un forum dédié aux tutos.
//Ce fichier contient l'en-tête HTML à envoyer au client + l'hôte et l'adresse auxquels on lie la socket.
$address='localhost';
$port=9814;
//Messages à l'intention des clients
$already_in_use='<script language="javascript">alert(\'Ce pseudo est déjà utilisé, connexion refusée\');</script>';
$denied='<script language="javascript">alert(\'Le nombre de clients maximal a été atteint. Accès refusé!\');</script>';
//En-tête HTML & javascript à envoyer aux clients lorsqu'ils se connectent
$html="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
$html.="<html><head><style>body{background-image:url(http://localhost/stephaneey_chat/images/background.jpg);}</style><script language=\"JavaScript\">function add_opt(textval,valueval){document.getElementById('pseudos')";
$html.=".options.add(new Option(textval,valueval));}function remove_opt(valueval){for(i=0;i<document.getElementById('pseudos')";
$html.=".length;i++){if(document.getElementById('pseudos').options[i].value == valueval){document.getElementById('pseudos')";
$html.=".options[i]=null;}}}\n<!--\n\n//-->\n</script>\n</head>\n<body>";
$html.="<div id=\"content\" style=\"position:absolute;left:10%;width:70%;height:280px;top:10%;border:solid 1px;background-color:#E7F2F8;overflow:auto;\"></div>";
$html.="<div id=\"info_board\" style=\"position:absolute;top:0;left:0px;width:550px;background-color:#4075BD;color:#FFFFFF;text-align:center\">Bienvenue sur le chat</div>";
$html.="<select style=\"position:absolute;left:80%;width:18%;height:280px;top:10%;color:#AF00AF;font:verdana bold;background-color:#E7F2F8\" multiple id='pseudos'></select>";

?>


