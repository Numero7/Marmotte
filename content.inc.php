
<div class="left">

 <!-- 
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<h1>Interface de saisie des prérapports</h1>
	</div>
 -->
	<div class="content"> 
		
 <?php 
	if ($action=="view")
	{
		$id_session = -1;
		if (isset($_REQUEST["id_session"]))
		{
			$id_session = $_REQUEST["id_session"];
		}
		$type_eval = "";
		if (isset($_REQUEST["type_eval"]))
		{
			$type_eval = $_REQUEST["type_eval"];
		}
		$login_rapp = "";
		if (isset($_REQUEST["login_rapp"]))
		{
			$login_rapp = $_REQUEST["login_rapp"];
		}
		
		$sort_crit = "";
		if (isset($_REQUEST["sort"]))
		{
			$sort_crit = $_REQUEST["sort"];
		}
		displaySummary($id_session,$type_eval,$sort_crit,$login_rapp);
	}
	else if ($action=="details")
	{
		if (isset($_REQUEST["id"]))
		{
			$id_rapport = $_REQUEST["id"];
			displayReport($id_rapport);
		}
	}
	else if ($action=="edit")
	{
		if (isset($_REQUEST["id"]))
		{
			$id_rapport = $_REQUEST["id"];
			editReport($id_rapport);
		}
	}
	else if ($action=="history")
	{
		if (isset($_REQUEST["id_origine"]))
		{
			$id_origine = $_REQUEST["id_origine"];
			historyReport($id_origine);
		}
	}
	else if ($action=="update")
	{
		if (isset($_REQUEST["id_origine"]))
		{
			$id_origine = $_REQUEST["id_origine"];
			$id_nouveau = update($id_origine);
			displayReport($id_nouveau);
		}
		else
		{
			echo "Update action cannot do nothing because no id_origine provided";
		}
	}
	else if ($action=="new")
	{
		$type_eval = "";
		if (isset($_REQUEST["type_eval"]))
		{
			$type_eval = $_REQUEST["type_eval"];
		}
		newReport($type_eval);
	}
	else if ($action=="add")
	{	
		global $typesEvalUnit;
		$id_nouveau = addReport();
		displayReport($id_nouveau);
	}
	else if ($action=="newpwd" or $action=="adminnewpwd")
	{
		if (isset($_REQUEST["oldpwd"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
		{
			$old = $_REQUEST["oldpwd"];
			$pwd1 = $_REQUEST["newpwd1"];
			$pwd2 = $_REQUEST["newpwd2"];
			$login = $_REQUEST["login"];
			if (($pwd1==$pwd2))
			{
				if (changePwd($login,$old,$pwd1,$pwd2))
				{
					echo "<p><strong>Mot de passe modifié avec succès.</strong></p>";
					addCredentials($_SESSION["login"],$pwd1);
				}
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
			}
		}
		else
		{
			echo "<p><strong>Erreur :</strong> Vous n'avez fourni les informations nécessaires pour modifier votre mot de passe, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}
		if ($action=="newpwd")
		{
			include "changePwd.inc.php";
		}
		if ($action=="adminnewpwd" and isSuperUser())
		{
			include "admin.inc.php";					
		}
	}
	else if ($action=="admin")
	{
		if (isSuperUser())
		{
			include "admin.inc.php";					
		}
		else
		{
		  echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}
	}
	else if ($action=="admindeleteaccount")
	{
		if (isSuperUser())
		{
			if (isset($_REQUEST["login"]))
			{
				$login = $_REQUEST["login"];
				deleteUser($login);
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			}
			include "admin.inc.php";				
		}
		else
		{
		  echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}		
	}
	else if ($action=="adminnewaccount")
	{
		if (isSuperUser())
		{
			if (isset($_REQUEST["description"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
			{
				$desc = $_REQUEST["description"];
				$pwd1 = $_REQUEST["newpwd1"];
				$pwd2 = $_REQUEST["newpwd2"];
				$login = $_REQUEST["login"];
				if (($pwd1==$pwd2))
				{
					if (createUser($login,$pwd2,$desc))
					{
						echo "<p><strong>Utilisateur $login crée avec succès.</strong></p>";
					}
				}
				else
				{
					echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
				}
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			}			
			include "admin.inc.php";					
		}
		else
		{
		  echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}		
	}
	else if ($action=="adminnewsession")
	{
		if (isSuperUser())
		{
			if (isset($_REQUEST["sessionname"]) and isset($_REQUEST["sessiondate"]))
			{
				$name = $_REQUEST["sessionname"];
				$date = $_REQUEST["sessiondate"];
				createSession($name,$date);
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			}			
			include "admin.inc.php";					
		}
		else
		{
		  echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}		
	}
	else if ($action=="admindeletesession")
	{
		if (isSuperUser())
		{
			if (isset($_REQUEST["sessionid"]))
			{
				$id = $_REQUEST["sessionid"];
				deleteSession($id);
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour supprimer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			}			
			include "admin.inc.php";					
		}
		else
		{
		  echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		}		
	}
	else if ($action=="changepwd")
	{
		include "changePwd.inc.php";
	}
	else
	{
?>
		<p>
			Bienvenue sur le nouveau site d'édition des prérapports de la section 06.
		</p>
		<p>
			Le <b>menu situé à droite de cette page</b> vous permettra de <a href="?action=view">consulter les rapports renseignés</a> et/ou en <a href="?action=view">saisir un nouveau</a>.
		</p>
		<p>
			N'hésitez pas à nous contacter (Yann ou Hugo) en cas de difficultés.
		</p>
<?php
	}
?>
	</div>
</div>
