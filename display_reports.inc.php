<?php
require_once('utils.inc.php');
require_once('manage_filters_and_sort.inc.php');

function displayTri($rows, $sortFields, $sorting_values)
{
	global $fieldsAll;

	$tritypes = getTriTypes($sorting_values);

	?>
<table>
	<tr>
		<td style="width: 10em;"><h2>Tri</h2>
		</td>
		<td>
			<table class="inputreport">
				<tr>

					<?php
					$count = 0;
					foreach($sortFields as $criterion)
					{
						if(isset($sorting_values[$criterion]))
						{
							$count++;
							?>
					<td><?php echo $fieldsAll[$criterion];?></td>
					<td><select name="tri_<?php echo $criterion;?>" style="width: 4em;">
							<?php
							foreach ($tritypes as $value => $nomitem)
							{
								$sel = "";
								if ($value == $sorting_values[$criterion])
									$sel = " selected=\"selected\"";
								echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
							}
							?>
					</select></td>
					<?php 
					if($count %6 == 0)
						echo '</tr><tr>';
						}
					}
					?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php

}


function displayFiltrage($rows, $fields, $filters, $filter_values)
{
	global $fieldsAll;
	global $actions;
	global $fieldsTypes;
	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;

	?>
<!--  Menu filtrage -->
<table>
	<tr>
		<td>
			<table class="inputreport">
				<tr>
					<?php
					$count = 0;
					foreach($filters as $filter => $data)
						if(isset($data['liste']))
						{
							$count++;
							?>
					<td><?php echo $data['name'];?></td>
					<td>
					<select   onchange="window.location='index.php?action=view&amp;filter_<?php echo $filter?>=' + this.value;">
							<option value="<?php echo $data['default_value']; ?>">
								<?php echo $data['default_name']; ?>
							</option>
							<?php
							foreach ($data['liste'] as $value => $nomitem)
							{
								if(is_numeric($value))
									$value = strval($value);
								$cur_val = $filter_values[$filter];
								if(is_numeric($cur_val))
									$cur_val = strval($cur_val);
								$sel = "";
								if ($value === $cur_val)
									$sel = " selected=\"selected\"";
								echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
							}
							?>
					</select></td>
					<?php 
					if($count %3 == 0)
						echo '</tr><tr>';
						}
						?><td></td>
								<td style="width: 10em;"><h3><a href="index.php?action=view&reset_filter=">Réinitialiser filtres</a></h3>
		</td>
						
				</tr>
			</table>
		</td>
	</tr>
</table>

<!-- END  Menu filtrage -->

<?php
}


function displayRows($rows, $fields, $filters, $filter_values, $sort_fields, $sorting_values)
{
	global $fieldsAll;
	global $actions;
	global $fieldsTypes;
	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;

	?>
	<table>
		<tr>
			<td>
			<table>
					<tr>
						<td><?php 
						displayFiltrage($rows, $fields, $filters, $filter_values);
						?>
						</td>
					</tr>
			</table>
			</td>
<?php 
if(isSecretaire())
{
	?>
<td>
<table><tr>
<td>
		<form onsubmit="return confirm('Changer les statuts des rapports?');" method="post"  action="index.php">
		<table><tr><td>
			<input type="submit" value="Changer statuts"/>
			</td><td>
			<select name="new_statut">
			<?php  
			global $statutsRapports;
			foreach ($statutsRapports as $val => $nom)
			{
				$sel = "";
				echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
			}
			?>
			</select>
			<input type="hidden" name="action" value="change_statut"/>
			</td>
			</tr></table>
		</form>
</td>
</tr>
<tr>
<td>
		<form onsubmit="return confirm('Supprimer ces rapports?');" method="post" action="index.php">
				<input type="hidden" name="action" value="deleteCurrentSelection" /> <input	type="submit" value="Supprimer rapports" />
		</form>
</td>
</tr>
</table>
	</td>
	<?php 
}
?>
			</tr>
	</table>
<hr />
<p><?php  echo count($rows); ?> rapports</p>

<?php 
$rapporteurs = listNomRapporteurs();
$bur = isBureauUser();

if(isBureauUser() && !isSecretaire() && is_current_session_concours())
{
	
	$allstats = get_bureau_stats();
	$stats = $allstats["rapporteurs"];
	$statssj = $allstats["sousjurys"];
	
	$roles = array("rapporteur","rapporteur2","rapporteur3");
	?>
	<table>
	<tr><th>CR</th><th>DR</th><th>Sousjurys</th></tr>
	<tr>
	<?php 
	foreach($stats as $niveau => $data)
	{
		?>
		<td>
		<table class="stats">
		<tr>
		<th>login</th><th>rapp</th><th>rapp 2</th><th>rapp 3</th><th>Total</th></tr>
		<?php 
		foreach($data as $login => $data_rapporteur)
		{
			$nom= isset($rapporteurs[$login])? $rapporteurs[$login] : $login;
			echo "<tr ><td>".$nom."</td>";
			$total = 0;
			foreach($roles as $role)
			{
				if(isset($data_rapporteur[$role]))
				{
					$stat = $data_rapporteur[$role]["counter"];
				echo "<td>".$stat."</td>";
				$total += $stat;
				}
				else
					echo "<td></td>";
			}
			echo "<td>".$total."</td>";
			echo "</tr>";
		}
		?>
		</table>
		</td>
		<?php 
	}
	?>		<td>
	<?php 
	
	foreach($statssj as $sj => $compteur)
	{
		echo "Sousjury ".$sj.": ".$compteur."<br/>";
	}
	?>
	</td>
	</tr>
	</table>
	<?php 
}
?>
<table class="summary">
<tr>
		<th class="oddrow"><span class="nomColonne"></span></th>
		<?php
		
		$sec = isSecretaire();
		$concours = getConcours();
		
		global $tous_avis;
		$prettyunits = unitsList();
		
		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			$style = getStyle("",true);
			?>
		<th class="<?php echo $style;?>"><span class="nomColonne"> <?php 
		echo '<a href="?action=view&amp;reset_tri=&amp;tri_'.$fieldID."=1\">".$title.'</a>';
		?>
		</span>
		</th>
		<?php
		}
		echo '</tr>';


		global $actions1;
		global $actions2;

		$odd = false;
		foreach($rows as $row)
		{
			// is_in_conflict(getLogin(), $candidate)
			$candidate = get_or_create_candidate($row);
			$conflit = is_in_conflict(getLogin(), $candidate);
			$style = getStyle("",$odd,$conflit);
			$odd = !$odd;
			?>
	
	<tr id="t<?php echo $row->id;?>" class="<?php echo $style;?>">
		<?php
			
		echo '<td>';
		displayActionsMenu($row,"", $actions1,$row->rapporteur, $row->rapporteur);
		echo '</td>';

		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			echo '<td>';
			$data = $row->$fieldID;
			$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";

			if(!$sec && $type=="rapporteur")
			{
				if($bur)
				{
				?>
				<select onchange="window.location='index.php?action=set_rapporteur&property=<?php echo $fieldID; ?>&id_origine=<?php echo $row->id_origine; ?>&value=' + this.value;">
				<?php 
				foreach($rapporteurs as $rapporteur => $nom)
				{
					$selected = ($rapporteur == $row->$fieldID) ? "selected=on" : "";
					echo "<option ".$selected." value=\"".$rapporteur."\">".$nom."</option>\n";
				}
				?>
				</select>
				<?php 
				}
				else
					echo (isset($rapporteurs[$row->$fieldID]) ? $rapporteurs[$row->$fieldID] : $row->$fieldID);
			}
			else if($sec &&  $type=="avis")
			{
		//		displayAvisMenu($fieldID,$row);
				echo isset($tous_avis[$row->$fieldID]) ? $tous_avis[$row->$fieldID] : $row->$fieldID;
			}
			else if($fieldID == "concours")
			{
				echo isset($concours[$row->$fieldID]) ? $concours[$row->$fieldID]->intitule : "";
			}
			else if($fieldID=="sousjury")
			{
				?>
		<!-- Displaying sous jury menu -->
		<?php 
		/***
		displaySousJuryMenu($fieldID,$row);***/
		echo $row->$fieldID;
			}
			else if($data != "")
			{
				?>
		<!-- Displaying field <?php echo $fieldID; ?>menu -->
		<?php 

		if($fieldID=="nom")
		{
			echo "<a href=\"?action=edit&amp;id=".($row->id)."\">";
			echo '<span class="valeur">'.$data.'</span>';
			echo '</a>';
		}
		else
		{
			if( ($type == "unit") && isset($prettyunits[$row->$fieldID]))
				$data = $prettyunits[$row->$fieldID]->nickname;
			echo '<span class="valeur">'.$data.'</span>';
		}
			}
			echo '</td>';
		}
		?>
		<!-- Displaying action menu -->
		<?php 
		echo '<td>';
		displayActionsMenu($row,"", $actions2);
		echo '</td>';
		?>
	</tr>
	<?php
		}
		?>
</table>
<br/>
<br/>
<br/>
<p>
Marmotte a été développé par Hugo Gimbert et Yann Ponty.<br/>
Code libre d'utilisation par les sections du Comité National de la Recherche Scientifique.<br/>
Utilisations commerciales réservées.
</p>
<?php
} ;


?>