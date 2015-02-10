<?php
require_once('config.inc.php');
require_once('db.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');

//set_exception_handler('exception_handler');
//set_error_handler('error_handler');
function getTypesEval($id_session,$section)
{
	$finalResult = array();
	$sql = "SELECT DISTINCT type FROM (SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM ";
	$sql .= reports_db." tt INNER JOIN ( SELECT id, MAX(date) AS date FROM ".reports_db;
	$sql .= " GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, ".sessions_db;
	$sql .=" ss WHERE ss.id=tt.id_session) difftypes WHERE id_session=$id_session AND section=$section ORDER BY type DESC;";
	
	$result=mysql_query($sql);
	while ($row = mysql_fetch_object($result))
	{
		if ($row->type)
			$finalResult[] = $row->type;
	}
	return $finalResult;
}


function highlightDiff(&$prevVals,$key,$val)
{
	if (isset($prevVals[$key]))
	{
		if ($prevVals[$key]==$val)
		{
			$prevVals[$key] = $val;
			return "<span class=\"faded\">$val</span>";
		}
		else
		{
			$prevVals[$key] = $val;
			return "<span class=\"highlight\">$val</span>";
		}
	}
	$prevVals[$key] = $val;
	return $val;
}



function fieldDiffers($prevVals,$key,$val)
{
	if(isset($prevVals[$key]))
	{
		if ($prevVals[$key]==$val)
		{
			return false;
		}
		else {
			return true;
		}
	} return true;
}


function remove_br($str)
{
	return str_replace("<br />","",$str);
}
function insert_br($str)
{
	return str_replace("\n","<br />",$str);
}


function array_remove_by_value($array, $value)
{
	return array_values(array_diff($array, array($value)));
}

function is_picture($file)
{
	if(strlen($file) < 4) return false;
	$suffix = strtolower(substr($file,-3,3));
	return $suffix == "png" || $suffix == "jpg" || $suffix == "bmp";
}

function message_handler($subject,$body)
{
	$headers = 'From: '.get_config("webmaster"). "\r\n" . 'Reply-To: '.get_config("webmaster"). "\r\n" .'X-Mailer: PHP/' . phpversion()."\r\n";
	mail(get_config("webmaster"), $subject, "\r\n".$body."\r\n", $headers);
}

function email_handler($recipient,$subject,$body, $cc = "")
{
	echo "Trying to send email to '".$recipient."' with subject '".$subject."'... ";
	
	$headers = 'From: '.get_config("webmaster"). "\r\n";
	if($cc != "")
		$headers.= 'CC: ' .get_config("webmaster") . "\r\n";
	$headers .= 'Reply-To: '.get_config("webmaster"). "\r\n".'Content-Type: text/plain; charset="UTF-8"\r\n'.'X-Mailer: PHP/' . phpversion()."\r\n";

	$result = mail($recipient, $subject, "\r\n".$body."\r\n", $headers);

	if($result == false)
	{
		echo "failed!";
		throw new Exception("Could not send email to ".$recipient." with subject ".$subject);
	}
		echo "sucess.";
}


function replace_accents($string)
{
	return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
}

function normalizeName($name)
{
	return str_replace('\' ', '\'', ucwords(strtolower($name)));
}

function real_escape_string($string)
{
	global $dbh;
	return mysqli_real_escape_string($dbh,$string);
}

function sql_request($sql)
{
	global $dbh;
//	echo $sql."<br/>";	
	$result = mysqli_query($dbh, $sql);
	if($result == false)
		throw new Exception("Failed to process sql query: <br/>\t".mysqli_error($dbh)."<br/>".$sql);
	else
		return $result;
}

function stripAccents($string){
	return strtr($string,"'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ",
			' aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function exception_handler($exception)
{
	echo "<h1>".$exception->getMessage()."</h1>";
	//message_handler("Marmotte webpage :exception ",$exception->getMessage());
}


function error_handler($errno, $errstr, $errfile, $errline)
{
	$body= "Number:".$errno."\r\n String:".$errstr."\r\n File:".$errfile."\r\n Line:".$errline;
	echo "<h1>".$body."</h1>";
	//message_handler("Marmotte webpage :error ",$body);
}

function curPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
$pageURL .= "s";
}
$pageURL .= "://";
if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= (isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "").(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "");
	}
	return $pageURL;
}


//Returns the name of the file
function filename_from_doc($doc)
{
	$nom = str_replace( array("'"," "), array("","_") , mb_convert_case(replace_accents($doc->nom), MB_CASE_TITLE));
	$prenom = mb_convert_case(replace_accents($doc->prenom), MB_CASE_TITLE);

	$sessions = sessionArrays();
	$session = $sessions[$doc->id_session];
	return filename_from_params($nom, $prenom, $doc->grade_rapport, $doc->unite, $doc->type, $session, $doc->avis, $doc->concours, $doc->sousjury, $doc->ecole);
}

function filename_from_params($nom, $prenom, $grade, $unite, $type, $session, $avis, $concours = "", $sousjury="", $ecole = "")
{
	global $typesRapportsUnites;
	global $typesRapportsConcours;
	$liste_unite = unitsList();
	
	$section = get_config("section_shortname");
	if($type == "Promotion")
		$grade .= " - ".$avis;

	if($type == "Evaluation-Vague" || $type == "Evaluation-MiVague")
		$type .=  " - ".mb_convert_case($avis,MB_CASE_TITLE);

	if(array_key_exists($type,$typesRapportsUnites))
	{
		if(isset($liste_unite[$unite]))
			$unite = $unite . " - " . $liste_unite[$unite]->nickname;

		if($type == 'Generique')
			return $section." - ".$session." - ".$nom." ".$prenom." - ".$unite;
		else if($type == 'Ecole')
			return $section." - ".$session." - ".$type." - ".$ecole."-".$nom." - ".$unite;
		else
			return $section." - ".$session." - ".$type." - ".$unite;
	}
	else if( array_key_exists($type,$typesRapportsConcours) || $type=="Audition" || $type =="Classement" )
	{
		if($type == "Classement")
		{
			$type .=  " - ".mb_convert_case($avis,MB_CASE_TITLE);
			return $session." - ".$concours." - ".$type." - ".$nom." ".$prenom;
		}
		else if($type == "Audition")
		{
			return $session." - ".$concours." - ".$type." - sousjury ".$sousjury." - ".$nom." ".$prenom;
		}
		else return $session." - ".$type." - ".$grade." - ".$nom." ".$prenom;
	}
	else
		return $section." - ".$session." - ".$type." - ".$grade." - ".$nom." ".$prenom;
}

function getStyle($fieldId,$odd, $conflict = false)
{
	global $fieldsIndividualAll;
	global $fieldsCandidat;
	$individual = isset($fieldsIndividualAll[$fieldId]) or isset($fieldsCandidat[$fieldId]);
	
	$rapp2 = ((substr($fieldId, -1)==="2")and !($individual));
	if ($odd)
	{
		$style =  "oddrow";
	}			
	else
	{
		$style =  "evenrow";
	}
	
	if ($rapp2)
	{  $style .= "Bis"; }
	else if ($individual)
	{  $style .= "Individual"; }

	if ($conflict)
	{
		$style .=  "Conflict";
	}			
	
	return $style;
}
?>