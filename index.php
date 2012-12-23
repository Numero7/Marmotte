<?php header('Content-type: text/html; charset=utf-8');?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<meta name="description" content="description"/>
	<meta name="keywords" content="keywords"/> 
	<meta name="author" content="author"/> 
	<link rel="stylesheet" type="text/css" href="default.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="cn.css" media="all"/>
	<link rel="stylesheet" type="text/css" href="cseprint.css" media="print"/>
	<title>MARMOTTE : Gestion des rapports de section du coCNRS</title>
</head>
<body>
	<?php
	require_once("utils.inc.php");
	require_once("manage_users.inc.php");
	$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
    if($dbh!=0)
    {
		$action="";
		$errorLogin = 0;
		if (isset($_REQUEST["action"]))
		{
			$action = $_REQUEST["action"];
		}		
		if ($action=="logout")
		{
			removeCredentials();
		}
		if ($action=="auth" and isset($_REQUEST["login"]) and isset($_REQUEST["password"]))
		{
			$login =  $_REQUEST["login"];
			$pwd =  $_REQUEST["password"];
			addCredentials($login,$pwd);
			if (!authenticate())
			{
				$errorLogin = 1;					
			}
		}
		if (authenticate())
		{
			include("content.inc.php");
			include("sidemenu.inc.php");
			include("authbar.inc.php");
		}
		else
		{
			include("authenticate.inc.php");
		}
		db_disconnect($dbh);
    }
    else
    {
		include("errordb.inc.php");
    }
	?>
</body>
</html>