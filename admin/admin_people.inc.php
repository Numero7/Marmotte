<?php 
require_once('config.inc.php');
require_once('manage_unites.inc.php');
require_once('synchro.inc.php');

if(isSecretaire())
  synchronizePeople($_SESSION['filter_section']);

global $fieldsUnitsDB;


$sql = "SELECT DISTINCT id_session,NUMSIRHUS FROM `".reports_db."` WHERE section=\"".$_SESSION['filter_section']."\" AND NUMSIRHUS != \"\" AND type=\"".REPORT_EVAL."\" OR type=\"".REPORT_EVAL_RE."\"";
$result = sql_request($sql);
$evals = array();
while($row = mysqli_fetch_object($result))
  {
    if(!isset($evals[$row->NUMSIRHUS])) $evals[$row->NUMSIRHUS] = "";
    $evals[$row->NUMSIRHUS] .= substr($row->id_session,0,1) . substr($row->id_session,count($row->id_session)-3,2)." ";
  }


$keyword = "";
if(isset($_REQUEST["keyword"]))
  $keyword = $_REQUEST["keyword"];

$tri="nom";
$ftri="marmotte.nom";
$tris = array(
	      "nom"=>"marmotte.nom",
	      "labo"=>"marmotte.labo1",
	      "key1"=>"marmotte.theme1",
	      "key2"=>"marmotte.theme2",
	      "key3"=>"marmotte.theme3",
	      "age"=>"dsi.drecrute");

if(isset($_REQUEST["tri"]) && isset($tris[$_REQUEST["tri"]]))
  {
   $tri = $_REQUEST["tri"];
   $ftri = $tris[$tri];
  }


if($keyword == "")
  {
//$sql = "SELECT * ,".dsidbname.".".dsi_people_db.".nom AS dsi_nom, ".dsidbname.".".dsi_people_db.".prenom AS dsi_prenom FROM `people` marmotte ";
$sql = "SELECT * FROM `people` marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ON marmotte.NUMSIRHUS=dsi.numsirhus ";
$sql .= "WHERE (dsi.scn1=marmotte.section OR dsi.scn2=marmotte.section) AND marmotte.concoursid=\"\" AND marmotte.NUMSIRHUS!=\"\" AND marmotte.section=\"".$_SESSION['filter_section']."\"";
$sql .= " ORDER BY ".$ftri." ASC;";
  }
else
  {
$sql = "SELECT * FROM `people` marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ON marmotte.NUMSIRHUS=dsi.numsirhus ";
//$sql = "SELECT * FROM `people` marmotte ";
$sql .= "WHERE (dsi.scn1=marmotte.section OR dsi.scn2=marmotte.section) AND marmotte.concoursid=\"\" AND marmotte.NUMSIRHUS!=\"\" AND marmotte.section=\"".$_SESSION['filter_section']."\"";
$sql .= " AND (marmotte.theme1='".$keyword."' OR marmotte.theme2='".$keyword."' OR marmotte.theme3='".$keyword."')";
$sql .= " ORDER BY ".$ftri." ASC;";
  }

$result = sql_request($sql);

$fields =
  array(
"nom"=>"Nom",
"prenom" =>"Prénom",
"grade"=>"",
"scn1"=>"Sect",
"scn2"=>"ions",
"labo1"=>"Unité",
"labo2"=>"Unité2",
"theme1" => "Mots clés",
"theme2" => "",
"theme3" => "",
//"titus" => "Titu",
"evals" => "Evals",
"statut_sirhus" => "Statut",
//"courriel" => "Courriel",
"drecrute"=>"Recrutement",
"lieutravail"=>"Lieu de travail",
"codeposition" => "Position",
"nature_sirhus" => "Nature",
"dr" => "DR"
//,
//"NUMSIRHUS"=>"Numsirhus"
	);


global $topics;
echo "<hr/><p><b>Tri et filtrage</b></p>";
echo"<form method='post'>";
echo "<p>Filtrage par mots clés</p>";
echo "<select style='width:50%' name='keyword'>";
  echo "<option value=''>tous</option>";
foreach($topics as $key => $value)
  if($key == $keyword)
  echo "<option selected='selected' value='".$key."'>".$value."</option>";
  else
  echo "<option value='".$key."'>".$value."</option>";
echo "</select>";
$tris = array("nom"=>"Nom",
	      "labo"=>"Unité",
	      "key1"=>"Mot-clé 1",
	      "key2"=>"Mot-clé 2",
	      "key3"=>"Mot-clé 3",
	      "age"=>"Date recrutement");
echo "<br/><br/>Tri<br/><select name='tri'>";
foreach($tris as $key => $value)
  if($key == $tri)
    echo "<option selected='selected' value='".$key."'>".$value."</value>";
  else
    echo "<option value='".$key."'>".$value."</value>";
echo "</select><br/><br/>";
echo "<input type='submit'></input>";
if(isSecretaire())
  {
echo "<input type='hidden' name='action' value='admin'/>";
echo "<input type='hidden' name='admin_people' value=''/>";
  }
else
  {
echo "<input type='hidden' name='action' value='see_people'/>";
  }
echo "</form><br/><br/><hr/>";
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

$units = unitsList();

echo "<p><B>".mysqli_num_rows($result)." chercheurs</B></p>";
echo "<table class=\"people\">\n";
echo "<tr>\n";
foreach($fields as $key => $label)
  {
    if($key == "theme1") {
      echo "<th colspan=\"3\">".$label."</th>\n";
    } else if($key != "theme2" && $key != "theme3")
      {
	echo "<th>".$label."</th>\n";
      }
  }
echo "</tr>\n";

$section = currentSection();
while($row = mysqli_fetch_object($result))
  {
echo "<tr>";
  foreach($fields as $key => $label)
    {
      if(($key == "code_unite" || $key == "code_unite2") && isset($units[$row->$key]))
	  $row->$key = $units[$row->$key]->prettyname;

      if(substr($key,0,5) == "theme")
	{
	  echo '<td>';
	  // echo "<script>keywords(\"".$key."\",\"".$row->NUMSIRHUS."\",\"".$row->$key."\");</script>";
	  if(isBureauUser())
	    {
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
    }
	  else
	    {
	      echo $row->$key;
	    }
	  echo '</td>';
	}
	else if($key == "evals")
	  {
	    	    	  echo "<td>";
		  if(isset($evals[$row->NUMSIRHUS]))
		    echo $evals[$row->NUMSIRHUS];
		  //		    foreach($evals[$row->NUMSIRHUS] as $session)
		  //  echo $session." ";
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
