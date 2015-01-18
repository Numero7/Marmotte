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
					<select   onchange="window.location='index.php?action=view&filter_<?php echo $filter?>=' + this.value;">
							<option value="<?php echo $data['default_value']; ?>">
								<?php echo $data['default_name']; ?>
							</option>
							<?php
							foreach ($data['liste'] as $value => $nomitem)
							{
								$sel = "";
								if ($value === $filter_values[$filter])
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
</tr><tr>
<td>
		<form onsubmit="return confirm('Supprimer ces rapports?');" method="post" action="index.php">
				<input type="hidden" name="action" value="deleteCurrentSelection" /> <input	type="submit" value="Supprimer rapports" />
		</form>
</td>
</tr>
<tr>
<td>
<form method="post" action="index.php" onsubmit="return confirm('Affecter les sous-jurys?');">
			<input type="hidden" name="action" value="affectersousjurys" /> <input
				type="submit" value="Affecter sous-jurys" />
				</form>
	</td>
	</tr></table>
	</td>
	<?php 
}
?>
			</tr>
	</table>
<hr />
<table class="summary">
	<tr>
		<th class="oddrow"><span class="nomColonne"></span></th>
		<?php
		$rapporteurs = listNomRapporteurs();
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

			if($type=="rapporteur")
			{
				?>
		<!-- Displaying rapporteur menu -->
		<?php 
/*		displayRapporteurMenu($fieldID,$row,$users);*/
		
		echo (isset($rapporteurs[$row->$fieldID]) ? $rapporteurs[$row->$fieldID] : $row->$fieldID);
			}
			else if(isSecretaire() &&  $type=="avis")
			{
				?>
		<!-- Displaying avis menu -->
		<?php 
/*
		displayAvisMenu($fieldID,$row);*/
		echo isset($tous_avis[$row->$fieldID]) ? $tous_avis[$row->$fieldID] : $row->$fieldID;
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
<p>
Le site web Marmotte a été développé par Hugo Gimbert et Yann Ponty.<br/>
Code libre d'utilisation par les sections du Comité national.<br/>
Utilisations commerciales réservées.
</p>
<?php
} ;


?>