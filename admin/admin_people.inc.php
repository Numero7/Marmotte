<?php 
require_once('config.inc.php');
require_once('manage_unites.inc.php');

global $fieldsUnitsDB;

$sql = "INSERT IGNORE INTO `people` (nom,prenom,NUMSIRHUS,section,labo1,labo2)";
$sql .="SELECT dsi.nom,prenom,numsirhus,scn1,code_unite,code_unite2 FROM ".dsidbname.".".dsi_people_db." dsi ";
$sql .= "WHERE dsi.scn1 = '".$_SESSION['filter_section']."';";
$result = sql_request($sql);

$sql = "INSERT IGNORE INTO `people` (nom,prenom,NUMSIRHUS,section,labo1,labo2)";
$sql .="SELECT dsi.nom,prenom,numsirhus,scn2,code_unite,code_unite2 FROM ".dsidbname.".".dsi_people_db." dsi ";
$sql .= "WHERE dsi.scn2 = '".$_SESSION['filter_section']."';";
$result = sql_request($sql);

$sql = "SELECT DISTINCT id_session,NUMSIRHUS FROM `".reports_db."` WHERE NUMSIRHUS != \"\" AND type=\"".REPORT_EVAL."\" OR type=\"".REPORT_EVAL_RE."\"";
$result = sql_request($sql);
$evals = array();
while($row = mysqli_fetch_object($result))
  {
    if(!isset($evals[$row->NUMSIRHUS])) $evals[$row->NUMSIRHUS] = array();
    $evals[$row->NUMSIRHUS][] = substr($row->id_session,0,1) . substr($row->id_session,count($row->id_session)-3,2);
  }

/*$sql = "SELECT DISTINCT id_session,NUMSIRHUS FROM `".reports_db."` WHERE NUMSIRHUS != \"\" AND type=\"".REPORT_TITU."\"";
$result = sql_request($sql);
$titus = array();
while($row = mysqli_fetch_object($result))
  {
    if(!isset($evals[$row->NUMSIRHUS])) $titus[$row->NUMSIRHUS] = array();
    $titus[$row->NUMSIRHUS][] = $row->id_session;
  }
*/


//$sql = "SELECT * ,".dsidbname.".".dsi_people_db.".nom AS dsi_nom, ".dsidbname.".".dsi_people_db.".prenom AS dsi_prenom FROM `people` marmotte ";
$sql = "SELECT * FROM `people` marmotte ";
$sql .= "INNER JOIN ".dsidbname.".".dsi_people_db." dsi ON marmotte.NUMSIRHUS = dsi.numsirhus ";
$sql .= "WHERE marmotte.section=\"".$_SESSION['filter_section']."\"";
$sql .= " AND (dsi.scn1 = \"".$_SESSION['filter_section']."\" OR dsi.scn2 = \"".$_SESSION['filter_section']."\") ORDER BY dsi.nom ASC;";


$result = sql_request($sql);

$fields =
  array(
"nom"=>"Nom",
"prenom" =>"Prénom",
"grade"=>"Grade",
"scn1"=>"Sect",
"scn2"=>"ions",
"code_unite"=>"Unité",
"code_unite2"=>"Unité2",
"theme1" => "MotClef1",
"theme2" => "MotClef2",
"theme3" => "MotClef3",
//"titus" => "Titu",
"evals" => "Evals",
"statut_sirhus" => "Statut",
"courriel" => "Courriel",
"drecrute"=>"Recrutement",
"lieutravail"=>"Lieu de travail",
"codeposition" => "Position",
"nature_sirhus" => "Nature",
"dr" => "DR",
"numsirhus"=>"Numsirhus"
	);


global $topics;
echo "<p>";
foreach($topics as $key => $value)
  echo $value."<br/>";
echo "</p>";
/*
echo '<script type="text/javascript">';
echo "\n";
echo 'function keywords(key,numsirhus,val) {';
echo "\n";
echo 'document.write(\'<form action="/">';
echo '<select class="sproperty" name="value">';
echo '"<option value=\"\"></option>\n";\');';
echo "\n";
		foreach($topics as $keyt => $value)
		{
		  echo 'if(val==\''.$keyt.'\') document.write("<option selected=on value =\"'.$keyt.'\"</option>");';
		  echo "\n";
		  echo 'else document.write("<option value =\"'.$keyt.'\"</option>");';
		  echo "\n";
		}
echo 'document.write(\'';
echo '</select>';
echo '<input type="hidden" name="action" value="set_property" />';
echo '<input type="hidden" name="property" value="\' + key + \'";/>';
echo '<input type="hidden" name="numsirhus" value="\' + numsirhus + \'"; />';
echo '</form>\');}';
echo "\n";
echo '</script>';
echo "\n";
*/
echo "<table>\n";
echo "<tr>\n";
foreach($fields as $key => $label)
  echo "<th>".$label."</th>\n";
echo "</tr>\n";

$section = currentSection();
while($row = mysqli_fetch_object($result))
  {
echo "<tr>";
  foreach($fields as $key => $label)
    {
      if(substr($key,0,5) == "theme")
	{
	  echo '<td>';
	  // echo "<script>keywords(\"".$key."\",\"".$row->NUMSIRHUS."\",\"".$row->$key."\");</script>";
?>	  
<form action="/">
	<select class="sproperty" name="value">
		<?php
		echo "<option value=\"\"></option>\n";
		foreach($topics as $keyt => $value)
		{
			$selected = ($row->$key == $keyt) ? "selected=on" : "";
			echo "<option ".$selected." value=\"".$keyt."\">".$keyt."</option>\n";
		}
		?>
	</select>
	<input type="hidden" name="action" value="set_people_property" />
	    <input type="hidden" name="property" value=<?php echo '"'.$key.'"'; ?> />
	    <input type="hidden" name="numsirhus" value=<?php echo '"'.$row->NUMSIRHUS.'"'; ?> />
</form>
<?php
	  echo '</td>';
	}
	else if($key == "evals")
	  {
	    	  echo "<td>";
		  if(isset($evals[$row->NUMSIRHUS]))
		    foreach($evals[$row->NUMSIRHUS] as $session)
		      echo $session." ";
	  echo "</td>";
	}
      /*
	else if($key == "titus")
	  {
	    	  echo "<td>";
		  if(isset($titus[$row->NUMSIRHUS]))
		    foreach($titus[$row->NUMSIRHUS] as $session)
		      echo $session." ";
	  echo "</td>";
	}
      */
      else
	{
    echo "<td>".(isset($row->$key) ? $row->$key : "")."</td>\n"; 
	}
    }
echo "</tr>\n";
  }
echo "</table>";
