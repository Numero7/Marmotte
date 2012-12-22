<?php
//Returns the name of the zip file
function filename_from_node(DOMNode $node)
{
	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";
	
	foreach($node->childNodes as $child)
	{
		if($child->nodeName == "nom")
			$nom = $child->nodeValue;
		else if($child->nodeName == "prenom")
			$prenom = $child->nodeValue;
		else if($child->nodeName == "grade")
			$grade = $child->nodeValue;
		else if($child->nodeName == "unite")
			$unite = $child->nodeValue;
		else if($child->nodeName == "type")
			$type = $child->nodeValue;
	}
	
	if($unite != "")
		return $type."_".$unite.".tex";
	else
		return $type."_".$grade." ".$nom." ".$prenom.".tex";
}

function xml_to_zipped_tex(DOMDocument $xml)
{

	$docs = $xml->getElementsByTagName("rapport");

	$zip = new ZipArchive();
	// On ouvre l’archive.
	
	$xsl = new DOMDocument();
	$xsl->load("xslt/latexshort.xsl");
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
		
	if($zip->open('reports3.zip',ZipArchive::OVERWRITE))
	{
		$zip->addFromString("compile.bat", "for /r %%x in (*.tex) do pdflatex \"%%x\"\r\ndel *.log\r\ndel *.aux");
		foreach($docs as $doc)
		{
			$filename = filename_from_node($doc);
			$zip->addFromString($filename,$proc->transformToXML($doc));
		}
		$zip->close();
	}
	return "reports3.zip";

}
?>
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

			if($type=="latex")
			{
				$filename=xml_to_zipped_tex($xml);
				$filepath="./";

				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"".$filename."\"");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: ".filesize($filename));
				ob_clean();
				flush();
				readfile($filename);

				/*
				 header("Pragma: public");
				header('Content-disposition: attachment; filename='.$file);
				$finfo = new finfo(FILEINFO_MIME);
				header("Content-type: ".$finfo->file($file));
				$finfo->close();
				header('Content-Transfer-Encoding: binary');
				ob_clean();
				flush();
				readfile($file);
				*/
			}
			else
			{
				header("Content-type: $mime; charset=utf-8");
				$xsl = new DOMDocument();
				$xsl->load($xslpath);
				$proc = new XSLTProcessor();
				$proc->importStyleSheet($xsl);
				echo $proc->transformToXML($xml);
			}

		}
		else
		{
			?>
<html>
<head>
<title>Erreur : Format indisponible</title>
</head>
<body>
	<strong>Error : <?php echo $type;?>
	</strong> n'est pas un type d'export valide.
</body>
</html>
<?php				
		}
			
	}
}
?>