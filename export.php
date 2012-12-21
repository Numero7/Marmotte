<?php
	include("utils.inc.php");
	$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
    if($dbh!=0)
    {
		if (authenticate())
		{
			$type = "xml";
			if (isset($_REQUEST["type"]))
			{
				$type = $_REQUEST["type"];
			}
			if (isset($typeExports[$type]))
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
				$xml = getReportsAsXML($id_session,$type_eval,$sort_crit,$login_rapp);

				$conf = $typeExports[$type];
				$mime = $conf["mime"];
				$xslpath = $conf["xsl"];
				
				header("Content-type: $mime; charset=utf-8");
				$xsl = new DOMDocument();
				$xsl->load($xslpath);
				$proc = new XSLTProcessor();
				$proc->importStyleSheet($xsl);
				echo $proc->transformToXML($xml);
			}
			else 
			{
				?>
				<html>
				<head>
				<title>Erreur : Format indisponible</title>
				</head>
				<body>
				<strong>Error : <?php echo $type;?></strong> n'est pas un type d'export valide.
				</body>
				</html>
				<?php				
			}
			
		}
	}
?>