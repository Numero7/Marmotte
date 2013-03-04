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

function displayExportGeneral()
{
	global $typeExports;

	
	?>
	<table align="left" ">
	<tr>
	<td><h2>Export</h2></td>
	<?php 
	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		$level = $exp["permissionlevel"];
		if (getUserPermissionLevel()>=$level)
		{
			echo '<td style="padding-left: 20px"><a href="export.php?action=export&amp;type='.$idexp.'">';
			//echo "<img class=\"icon\" width=\"40\" height=\"40\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"/></a>";
			echo "$expname</a></td>";
		}
	}
	?>
	</tr></table>
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
		<td style="width: 10em;"><h2>Filtrage</h2>
		</td>
		<td>
			<table class="inputreport">
				<tr>

					<?php
					$count = 0;
					foreach($filters as $filter => $data)
						if(isset($data['liste']))
						{
							$count++;
							/*
							 if(in_array($filter, $start_tr_fields))
								echo '<tr><td></td><td><table><tr>';
							if(!in_array($filter, $specialtr_fields))
								echo '<tr>';
							*/
							?>
					<td><?php echo $data['name'];?></td>
					<td><select name="filter_<?php echo $filter?>">
							<option value="<?php echo $data['default_value']; ?>">
								<?php echo $data['default_name']; ?>
							</option>
							<?php
							foreach ($data['liste'] as $value => $nomitem)
							{
								$sel = "";
								if ($value == $filter_values[$filter])
									$sel = " selected=\"selected\"";
								echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
							}
							?>
					</select></td>
					<?php 
					if($count %3 == 0)
						echo '</tr><tr>';
					/*
					 if(!in_array($filter, $specialtr_fields))
						echo '</tr>';
					if(in_array($filter, $end_tr_fields))
						echo '</tr></table></td></tr>';
					*/
						}
						?>
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
<form method="post" action="index.php">
	<table>
	<tr>
	<td>
	<?php 
	displayExportGeneral();
	?>
	<td>
	</tr>
		<tr>
			<td>
				<table>
					<tr>
						<td><?php 
						//displayTri($rows, $sort_fields, $sorting_values);
						?>
						</td>
					</tr>
					<tr>
						<td><hr /> <?php 
						displayFiltrage($rows, $fields, $filters, $filter_values);
						?>
						</td>
					</tr>
				</table>

			</td>
		</tr>
		<tr>
			<td><hr /> <input type="hidden" name="action" value="view" /> <input
				type="submit" value="Rafraîchir" /> <?php 	echo "(".count($rows)." rapports)";?>
			</td>
		</tr>

	</table>
</form>
<hr />
<table class="summary">
	<tr>
		<th></th>
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
			$style = getStyle("",$odd);
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
<?php
} ;


?>