<?
/*
Ecran d'accueil du chat où l'on doit spécifier son pseudo.
*/
?>
<script language="javascript">
function Enter_Chat(frm){
  if(frm.elements['Pseudo'].value != ""){
    window.open('chatindex.php?Pseudo='+frm.elements['Pseudo'].value,'','toolbar=0,scrollbars=1,resizable=0,menuBar=0,width=550,height=400');
  }
}
</script>
<html>
<body>
<form>
<input type="text" name="Pseudo">
<input type="button" value="Entrer" onclick="Enter_Chat(this.form)">
</form>
</body>
</html>

