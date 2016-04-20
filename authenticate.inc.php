<?php 
require_once("config_tools.inc.php");
$is_maintenance = (get_config("maintenance","off",false,"0") === "on");

			?>

<div class="large">
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<br/>
		<br/>
		<br/>
		<br/>

<br/>
  <br/>
  <h3><a href="http://d2tq98mqfjyz2l.cloudfront.net/image_cache/1345701412322553.jpg">"Marmotte"</a>, un outil de gestion des rapports pour les sections et CID du Comit&eacute; National.</h3>
  <br/>
  <br/>
<br/>


		</div>

	<div class="content"> 
<?php 
if($is_maintenance)
{
?>
<h2><font color="red">
Site fermé pour maintenance, accès temporairement réservé aux administrateurs.
</font>
</h2>
<br/>
<br/>

<?php 
}
?>


	 <?php 
	 if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "auth_marmotte"))
	 {
	 ?>
	<h3>Authentification Marmotte</h3>
	<form method="POST">
	<table>
		<tr>
			<td><span class="label">Login</span></td>
			<td><input name="login" type="text" value=""></input></td>
			<td rowspan="2"><input type="submit" value="Valider">
			<input type="hidden" name="action" value="auth_marmotte"></td>
		</tr>
		<tr>
			<td><span class="label">Mot de passe</span></td>
			<td><input name="password" type="password" value=""></input></td>
		</tr>
	</table>
	 <?php 
	 }
	 else
	 {
	 ?>

<br/><br/><br/>
<!--
<p><font color="red">
[18/06/2015 08:20] Arrêt de Marmotte pour maintenance le lundi 22 juin de 19h à 19h30.
</font>
</p>
-->
<!--
<p><font color="red">
[10/06/2015 09:19] L'authentification par login e-valuation est temporairement indisponible
suite &agrave; un probl&egrave;me sur un serveur de la DSI.
En cas de besoin urgent, merci de contacter votre secr&eacute;taire scientifique afin d'obtenir un mot de passe provisoire.
</font>
</p>
<p>
<font color="red">
	     [11/01/2015 15:55]
L&apos;authentification via Janus est actuellement perturbée. Plus d&apos;information dans quelques minutes.<br/>
	     [11/01/2015 15:57]													   Suite à un problème matériel sur l’infrastructure du centre serveur de Trélazé, une partie du Système d’Information n’est plus accessible, et en particulier le service Janus a un problème et les indentifications via Janus ne sont actuellement pas possibles.
</font>
</p>
-->

	     <p><a href="index.php?action=auth_janus">Authentification Janus (membres des unit&eacute;s CNRS)</a></p>
	     <p><a href="index.php?action=auth_marmotte">Authentification Marmotte (membres hors-unit&eacute;s CNRS, demandez un mot de passe &agrave; votre ACN)</a></p>
	 <?php 
	 }
	 ?>
	<br/>
<p>
En cas de difficult&eacute; de connexion,
veuillez utiliser le 
<a href="https://support.dsi.cnrs.fr/webassistance/index.asp?prod=6106">
formulaire d&apos;assistance de la dsi</a>. 
</p>
<br/>	
  <br/>
<br/>	  <br/>
<br/>	
  <br/>
<p>Marmotte est le fruit d'un <a href="#" id="contributeurs_clickable">projet collaboratif open-source</a> réunissant secrétaires scientifiques, SGCN, DSI et DSI-concours.</p>
<span hidden id="contributeurs">
<p>Version: <a href="https://github.com/Numero7/Marmotte">Marmotte 2.3 "RainSeason"</a></p>
<table><tr>
<td>
<img src="http://s.scifi-universe.com/galeries/images-old/abominable/jour_sans_fin_01.jpg">
</td>
<td>
  <ul>
  <li><B>Secrétaires scientifiques</B></li>
  <li>Hugo Gimbert (d&eacute;veloppeur, coordinateur technique, section 6)</li>
  <li>Yann Ponty (d&eacute;veloppeur, section 6)</li>
		    <li>Matias Velazquez (&beta;-testeur, coordinateur d&eacute;ploiement SSC, section 15)</li>
		    <li>Caroline Strube (&beta;-testeuse, formatrice, section 25)</li>
		    <li>Sophie Achard (&beta;-testeuse, formatrice, section 7)</li>
		    <li>Guillaume Lapeyre (&beta;-testeur, formateur, section 19)</li>
		    <li>Santiago Pita (&beta;-testeur, section 1)</li>
</ul>
</td>
<td>
<ul>
<li><B>SGCN</B></li>
		    <li>Marie-Claude Labastie<br/> (ambassadrice)</li>
	   <li>Laurent Chazaly<br/> (coordinateur SGCN et formation)</li>
		    <li>Mich&egrave;le Desumeur<br/> (&beta;-testeuse ACN)</li>
</ul>
</td>
<td>
<ul>
<li><B>DSI</B></li>
  <li>Ren&eacute; Pelfresne<br/> (chef de projet DSI)</li>
</ul>
</td>
 </ul>
 </td>
 </tr>
 </table>
</span>
 </div>
  </div>
 </div>
<!-- <iframe width="90%" height="20%" src="https://lejournal.cnrs.fr/"></iframe>  -->
<script>
		    var display = false;
		      document.getElementById('contributeurs_clickable').onclick 
= function (event){
			if(!display)
    $('#contributeurs').show();
			else 
    $('#contributeurs').hide();
			display = !display;
}
</script>