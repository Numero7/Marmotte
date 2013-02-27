<?php
require_once('config.inc.php');
require_once('db.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');

//set_exception_handler('exception_handler');
//set_error_handler('error_handler');

function getTypesEval($id_session)
{
	$finalResult = array();
	$sql = "SELECT DISTINCT type FROM (SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM ".reports_db." tt INNER JOIN ( SELECT id, MAX(date) AS date FROM ".reports_db." GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, ".sessions_db." ss WHERE ss.id=tt.id_session) difftypes WHERE id_session=$id_session ORDER BY type DESC;";
	$result=mysql_query($sql);
	while ($row = mysql_fetch_object($result))
	{
		if ($row->type)
			$finalResult[] = $row->type;
	}
	return $finalResult;
}


function valueFromField($row, $field,$value,$units,$users)
{
	global $topics;
	global $fieldsTypes;
	if(isset($fieldsTypes[$field]))
	{
		switch($fieldsTypes[$field])
		{
			case 'unit':
				echo isset($units[$value]) ? $units[$value]->prettyname : "";
				return;
				break;
			case 'rapporteur':
				echo isset($users[$value]->description) ? $users[$value]->description : "";
				return;
				break;
			case 'topic':
				echo isset($topics[$value]) ? $topics[$value] : "";
				return;
				break;
			case 'files':
				display_fichiers($row, $field, true);
				return;
				break;
			default:
				echo $value;
				break;
		}
	}
}


function getExample($type)
{
	global $examples;
	$tmp = "Exemple : ";
	if (isset($examples[$type]))
	{
		$tmp .= $examples[$type];
	}
	return $tmp;
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

function historyReport($id_origine)
{
	global $fieldsAll;
	global $actions;
	$specialRule = array( "nom"=>0, "prenom"=>0, "grade"=>0, "unite"=>0, "type"=>0, "nom_session"=>0, "date_session"=>0, "date"=>0, "auteur"=>0);
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM ".reports_db." tt, ".sessions_db." ss WHERE tt.id_session=ss.id AND tt.id_origine=$id_origine ORDER BY date DESC;";
	$result=mysql_query($sql);
	$prevVals = array();
	$first = true;

	date_default_timezone_set('Europe/Paris');

	while ($row = mysql_fetch_object($result))
	{
		if ($first)
	 { ?>
<div class="tools">
	<?php
	displayActionsMenu($row, "history", $actions);
	?>
</div>
<?php
$first = false;
	 }

	 ?>
<div class="history">
	<h3>
		Version
		<?php echo ($row->statut=="supprime") ? "<span class=\"highlight\">supprimée</span>" : "modifiée"; ?>
		le
		<?php echo $row->date;?>
		par
		<?php 
		$desc = getDescription($row->auteur);
		if (!$desc)
			$desc = $row->auteur;
		echo highlightDiff($prevVals,"auteur",$desc);
		?>
	</h3>
	<?php
	if (fieldDiffers($prevVals,"prenom",$row->prenom)
				or fieldDiffers($prevVals,"nom",strtoupper($row->nom))
				or fieldDiffers($prevVals,"grade",$row->grade)
				or fieldDiffers($prevVals,"unite",$row->unite))
			{
				?>
	<h1>
		<?php echo highlightDiff($prevVals,"prenom",$row->prenom);?>
		<?php echo highlightDiff($prevVals,"nom",strtoupper($row->nom));?>
		(
		<?php echo highlightDiff($prevVals,"grade",$row->grade);?>
		) -
		<?php echo highlightDiff($prevVals,"unite",$row->unite);?>
	</h1>
	<?php
			}
			if (fieldDiffers($prevVals,"type",$row->type)
				or fieldDiffers($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session))))
			{
				?>
	<h2>
		<?php echo highlightDiff($prevVals,"type",$row->type);?>
		<?php echo highlightDiff($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session)));?>
	</h2>
	<?php
			}
			?>
	<dl>
		<?php
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]) 	and !(isset($prevVals[$fieldID])and ($prevVals[$fieldID]==$row->$fieldID)))
			{
				?>
		<dt>
			<?php echo $title;?>
		</dt>
		<dd>
			<?php echo highlightDiff($prevVals,$fieldID,$row->$fieldID);?>
		</dd>
		<?php
			}
		}
		?>
	</dl>
</div>
<?php
	}
}

function remove_br($str)
{
	return str_replace("<br />","",$str);
}


function array_remove_by_value($array, $value)
{
	return array_values(array_diff($array, array($value)));
}


/*
 * Returns 0 if fiels has to be hidden,
* 1 if it can be seen but not edited,
* 2 if it can be edited
*/
/*
 function get_authorization_level($rapport,$field)
 {

if(isSecretaire())
	return 2;
else if($row->statut == "rapport" || $row->statut == "publie")
	return 1;
else
{
$login = getLogin();
if($rapport->rapporteur != $login && $rapport->rapporteur2 != $login)
	return 1
else if ($rapport->rapporteur == $login)
{
global $fieldsIndividual1;
if(in_array($field, $fieldsIndividual1) || in_array())
{
return 2
}
else if(in_array($field, $fieldsIndividual1))
{

}
}
}
global $fieldsIndividual;
global $fieldsUnites;
global $fieldsEcoles;
global $fieldsRapportsCandidat;
global $fieldsGeneric;
global $fieldsEquivalence;

global $typesRapportsUnites;
global $typesRapportsChercheurs;

global $fieldsRapportsCandidat0;
global $fieldsRapportsCandidat1;
global $fieldsRapportsCandidat2;

global $fieldsCandidat;

$eval_type = $row->type;

if($eval_type == 'Ecole')
	return in_array($fieldsEcoles, $field);
else if(array_key_exists($eval_type,$typesRapportsUnites))
	return $fieldsUnites;
else if(array_key_exists($eval_type,$typesRapportsChercheurs))
	return $fieldsIndividual;
else if($eval_type == 'Candidature')
{
$f0 = $fieldsRapportsCandidat0;
$f1 = $fieldsRapportsCandidat1;
$f2 = $fieldsRapportsCandidat2;

if(isSecretaire())
{
return array_unique(array_merge($fieldsCandidat, $f0, $f1, $f2));
}
else if($row->statut == "rapport" || $row->statut == "publie")
{
return array_unique(array_merge($fieldsCandidat, $f0));
}
else if(getLogin() == $row->rapporteur)
{
return array_unique(array_merge($fieldsCandidat, $f1));
}
else if(getLogin() == $row->rapporteur2)
{
return array_unique(array_merge($fieldsCandidat, $f2));
}
else
{
return array_unique(array_merge($fieldsCandidat, $f0));
}
}
else if($eval_type == 'Equivalence')
	return $fieldsEquivalence;
else
	return $fieldsGeneric;

}
*/

function statut_to_choose($row)
{
	return isSecretaire();
}


function type_to_choose($row)
{
	$eval_type = $row->type;
	return $eval_type == "Evaluation-Vague" || $eval_type == "Evaluation-MiVague";
}





function message_handler($subject,$body)
{
	$headers = 'From: '.get_config("webmaster"). "\r\n" . 'Reply-To: '.get_config("webmaster"). "\r\n" .'X-Mailer: PHP/' . phpversion()."\r\n";
	mail(get_config("webmaster"), $subject, "\r\n".$body."\r\n", $headers);
}

function email_handler($recipient,$subject,$body)
{
	$headers = 'From: '.get_config("webmaster"). "\r\n" . 'CC: ' .get_config("webmaster"). "\r\n". 'Reply-To: '.get_config("webmaster"). "\r\n".'Content-Type: text/plain; charset="UTF-8"\r\n'.'X-Mailer: PHP/' . phpversion()."\r\n";

	$result = mail($recipient, $subject, "\r\n".$body."\r\n", $headers);

	if($result == false)
		throw new Exception("Could not send email to ".$recipient." with subject ".$subject);
}


function replace_accents($string)
{
	return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
}

function normalizeName($name)
{
	return str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($name))));
}

function sql_request($sql)
{
	//echo $sql."<br>";
	$result = mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query: <br/>\t".mysql_error()."<br/>".$sql);
	else
		return $result;
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
	$nom = mb_convert_case(replace_accents($doc->nom), MB_CASE_TITLE);
	$prenom = mb_convert_case(replace_accents($doc->prenom), MB_CASE_TITLE);
	$grade = $doc->grade;
	$unite = $doc->unite;
	$type = $doc->type;

	$sessions = sessionArrays();
	$session = $sessions[$doc->id_session];
	$avis = $doc->avis;
	$concours = $doc->concours;
	return filename_from_params($nom, $prenom, $grade, $unite, $type, $session, $avis, $concours);
}

function filename_from_params($nom, $prenom, $grade, $unite, $type, $session, $avis, $concours = "")
{
	global $typesRapportsUnites;
	global $typesRapportsConcours;

	if($type == "Promotion")
	{
		switch($grade)
		{
			case "CR2": $grade = "CR1"; break;
			case "DR2": $grade = "DR1"; break;
			case "DR1": $grade = "DRCE1"; break;
			case "DRCE1": $grade = "DRCE2"; break;
		}
		$grade .= " - ".$avis;
	}

	if($type == "Evaluation-Vague" || $type == "Evaluation-MiVague")
		$type .=  " - ".mb_convert_case($avis,MB_CASE_TITLE);

	if(array_key_exists($type,$typesRapportsUnites))
	{
		if($type == 'Generique')
			return $session." - ".$nom." ".$prenom." - ".$unite;
		else
			return $session." - ".$type." - ".$unite;
	}
	else if(array_key_exists($type,$typesRapportsConcours))
		return $session." - ".$type." - ".$concours." - ".$nom."_".$prenom;
	else
		return $session." - ".$type." - ".$grade." - ".$nom."_".$prenom;
}

function getStyle($fieldId,$odd)
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
	
	return $style;
}
?>