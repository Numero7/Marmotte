<?php 
require_once('manage_sessions.inc.php');
require_once('manage_rapports.inc.php');
require_once('utils.inc.php');
require_once('config.php');

function countReports($filters, $or = true)
{

	$filters["id_session"] = current_session_id();
	$sql = "SELECT COUNT(*) AS \"total\" FROM ".reports_db." WHERE id = id_origine AND statut!=\"supprime\"";

	$sql .= filtersCriteriaToSQL(getCurrentFiltersList(),$filters,$or);
	$sql .= ";";

	//echo $sql."<br/>\n";

	$result = sql_request($sql);

	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$truc = mysql_fetch_object($result);

	if($truc == false || ! isset($truc->total))
		throw new Exception("Empty answer from sql count request");

	return $truc->total;
}

function statBaseAvancement($filters)
{
	$total  = countReports($filters);
	$filters["avancement"] = "done";
	$dones = 	countReports($filters);
	if($total  > 0)
	{
		$coef = intval(100.0 * $dones /$total);
		return $coef ."% (".$dones."/".$total.")";
	}
	else
		return "";
}

function sous_jury_row($used_topics, $jurys, $code, $login, $data)
{
	$filters = array('type' => "Candidature", 'concours' => $code, 'avis' => "oral", 'rapporteur' => $login, 'rapporteur2' => "tous");
	$total1 = countReports($filters,false);

	$filters['rapporteur2'] = $login;
	$filters['rapporteur'] = "tous";
	$total2 = countReports($filters,false);

	if($total1 >0 || $total2 > 0)
	{
		echo '<tr><td>'.$data->description.'</td>';
		echo "<td>$total1 ($total2)</td>";

		foreach($jurys as $jury => $total)
			echo (isSousJury($jury,$login)? "<td>X</td>\n" : "<td></td>\n");

		foreach($used_topics as $topic => $total	)
		{
			$filters['theme1'] = $topic;
			$filters['rapporteur'] = $login;
			$filters['rapporteur2'] = "tous";
			$total1 = countReports($filters, false);
			$filters['rapporteur2'] = $login;
			$filters['rapporteur'] = "tous";
			$total2 = countReports($filters, false);
			$filters['avis'] = "oral";

			if($total1 > 0 || $total2 > 0)
				echo "<td>$total1 ($total2)</td>";
			else
				echo "<td></td>";
		}
	}


	echo '</tr>';
}


?>

<h1>Avis Candidatures</h1>

<?php 


if(is_current_session_concours())
{
	$with_topics = false;
	foreach($concours_ouverts as $code => $concours)
	{
		?>
<div style="float: top">
	<h2>
		<?php  echo $concours; ?>
	</h2>
	<table>
		<tr>
			<?php 

			foreach(array("avis" => "Avis section","avis1" => "Avis rapp1","avis2" => "Avis rapp2") as $typeavis => $nomavis)
			{
				?>
			<td>
				<table class="stats">
					<tr>
						<th><?php  echo $nomavis;?></th>
						<th>Total</th>
						<th>%</th>
						<?php 

						/*
						 $topics = get_config("topics");
						foreach($topics as $id => $topic)
							echo "<th>$id</th>";
						*/
						?>
					</tr>
					<?php
					$filters = array("concours" => $code, "type" => "Candidature");
					$total = countReports($filters);
					foreach($avis_candidature_short as $avis => $nom)
					{
						if($avis != "tous")
						{
							echo "<tr>";
							$filters = array("concours" => $code,  "type" => "Candidature");
							$filters[$typeavis] = $avis;
							$count = countReports($filters);
							echo "<td>$nom</td><td> ".$count." </td>";
							if($total > 0 && $count > 0)
								echo "<td> ".(int) (100.0 * $count / $total)."% </td>";
							else
								echo "<td></td>";

							/*
							 foreach($topics as $id => $topic)
							 {
							$filters["theme1"] = $id;
							echo "<td>".countReports($filters)."</td>";
							}
							*/
							echo "</tr>";
						}
					}
					echo "<tr><td></td><td>".$total ."</td></tr>";

					?>

				</table>
			</td>
			<?php 
			}
			?>
		</tr>
	</table>
</div>
<?php 
	}
}
?>

<h1>Stats rapporteurs</h1>

<?php 

if(is_current_session_concours())
{
	$users = listUsers();
	global $topics;
	global $sous_jurys;
	global $concours_ouverts;

	foreach($concours_ouverts as $code => $concours)
	{


		echo "<h2>Concours $concours</h2>";

		echo '<table class="stats">';

		$topics[""] = "horssection";

		$used_topics = array();


		echo "<tr><td></td><td></td>";

		foreach($topics as $topic =>$prettytopic )
		{
			$filters = array();
			$filters['type'] = "Candidature";
			$filters['concours'] = $code;
			$filters['theme1'] = $topic;
			$filters['avis'] = "oral";

			$total = countReports($filters);

			if($total > 0)
				$used_topics[$topic] = $total;
		}

		$jurys = array();
		foreach($sous_jurys[$code] as $val => $nom)
		{
			$filters = array();
			$filters['type'] = "Candidature";
			$filters['concours'] = $code;
			$filters['sousjury'] = $val;
			$filters['avis'] = "oral";

			$total = countReports($filters);
			$jurys[$val] = $total;
		}
			

		echo '</tr><tr>';
		echo '<td></td><td>Total</td>';
		foreach($jurys as $jury => $total)
			echo '<th>'.($jury == "" ? "aucun" : $jury).'</th>';
		foreach($used_topics as $topic => $total)
			echo '<th>'.($topic == "" ? "hs" : $topic).'</th>';
		echo "</tr>\n";

		echo '<tr><td></td><td></td>';
		foreach($jurys as $jury => $total)
			echo '<th>'.$total.'</th>';
		foreach($used_topics as $topic => $total)
			echo "<td>$total</td>";
		echo "</tr>\n";

		foreach($sous_jurys[$code] as $val => $nom)
			foreach($users as $login => $data)
			if(isSousJury($val,$login))
			sous_jury_row($used_topics, $jurys, $code, $login, $data);


		echo "</table>";

		echo "<p>";

		foreach($sous_jurys[$code] as $val => $nom)
		{
			if($nom != "")
			{
				$total = 0;
				foreach($users as $login => $data)
					if(isSousJury($val,$login))
					$total += 1;

				$filters = array();
				$filters['type'] = "Candidature";
				$filters['concours'] = $code;
				$filters['sousjury'] = $val;
				$filters['avis'] = "oral";

				echo "Sous-jury ". $nom . " membres ".$total." auditions ".countReports($filters)."<br/>";
			}
		}
		echo "</p>";

	}
}
?>

<h1>Avancement prérapports</h1>
<table class="stats">
	<tr>
		<th>Nom</th>
		<th>Total</th>
		<?php 
		if(is_current_session_concours())
		{
			echo "<th>Equivalence</th>\n";
			foreach($concours_ouverts as $code => $nom)
				echo '<th>'.$nom."</th>\n";
		}
		?>
	</tr>
	<?php

	$users = listUsers();

	$filters = array();
	if(countReports($filters) > 0)
	{
		echo "<tr>\n<td></td><td></td><td>".statBaseAvancement($filters)."</td>\n";

		if(is_current_session_concours())
		{
			$filters['type'] = "Equivalence";
			echo '<td>'.statBaseAvancement($filters)."</td>\n";
			$filters['type'] = "Candidature";
			foreach($concours_ouverts as $code => $nom)
			{
				$filters['concours'] = $code;
				echo '<td>'.statBaseAvancement($filters)."</td>\n";
			}
		}
		echo "</tr>\n";
	}

	foreach($users as $login => $data)
	{
		$filters = array("rapporteur" => $login);
		if(countReports($filters) > 0)
		{
			echo '<tr><td>'.$data->description.'</td><td>'.statBaseAvancement($filters)."</td>\n";

			if(is_current_session_concours())
			{
				$filters['type'] = "Equivalence";
				echo '<td>'.statBaseAvancement($filters)."</td>\n";
				$filters['type'] = "Candidature";
				foreach($concours_ouverts as $code => $nom)
				{
					$filters['concours'] = $code;
					echo '<td>'.statBaseAvancement($filters)."</td>\n";
				}
			}
			echo "</tr>\n";
		}
			
	}

	?>
</table>

<h2>Todo</h2>


<div id="horizontalContainer" style="float: none">
	<?php 

	foreach($concours_ouverts as $code => $nom)
	{
		echo '<div style="float: left">';
		echo '<table class="stats">';

		$filters = array("concours" => $code, "avis1" => "", "type"=>"Candidature");
		$missing1 = filterSortReports(getCurrentFiltersList(),$filters);
		$filters = array("concours" => $code, "avis2" => "", "type"=>"Candidature");
		$missing2 = filterSortReports(getCurrentFiltersList(),$filters);

		foreach($missing1 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp1 ". $report->rapporteur. ")</td></tr>\n";
		foreach($missing2 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp2 ". $report->rapporteur2. ")</td></tr>\n";
		echo '</table></div>';
	}
	?>
</div>
