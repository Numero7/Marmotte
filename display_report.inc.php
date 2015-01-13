
<?php

require_once('config.inc.php');
require_once('db.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');
require_once('display_field.inc.php');
require_once('utils.inc.php');

function displayEditableCandidate($candidate,$report = NULL,$canedit = true)
{
	global $fieldsCandidat;
	
	global $avis_candidature_necessitant_pas_rapport_sousjury;
	global $fieldsCandidatAvantAudition;
	global $fieldsCandidatAuditionne;


	$hidden = array("action" => "update");

	$hidden["previousnom"] = $candidate->nom;
	$hidden["previousprenom"] = $candidate->prenom;

	$session = current_session();

	if($report != NULL)
	{
		$hidden["id_origine"] = $report->id_origine;
		$hidden["type"] = $report->type;
		if(($report->avis == "IE") || (isset($report->avis) && in_array($report->avis, $avis_candidature_necessitant_pas_rapport_sousjury)))
			$fields = $fieldsCandidatAvantAudition;
		else
			$fields = $fieldsCandidatAuditionne;

		$candidate->rapporteur = $report->rapporteur;
		$candidate->rapporteur2 = $report->rapporteur2;
		$candidate->rapporteur3 = $report->rapporteur3;
		$candidate->type = $report->type;
		$candidate->statut = $report->statut;

		if(isset($report->id_session))
			$session = $report->id_session;
	}
	$submit = array("conflit" => "Se déclarer en conflit");

	displayEditionFrameStart("",$hidden,$submit);
	displayEditableObject("", $candidate, $fields,$canedit,$session);
	displayEditionFrameEnd("Données candidat");
}

function displayEditableChercheur($chercheur,$report = NULL, $canedit = true)
{
	
global $fieldsChercheursAll;
	$hidden = array("action" => "update");


	$hidden["previousnom"] = $chercheur->nom;
	$hidden["previousprenom"] = $chercheur->prenom;

	$session = current_session();

	if($report != NULL)
	{
		$hidden["id_origine"] = $report->id_origine;
		$hidden["type"] = $report->type;

		if(isset($report->rapporteur))
			$chercheur->rapporteur = $report->rapporteur;
		else
			$chercheur->rapporteur = "";
			
		if(isset($report->rapporteur2))
			$chercheur->rapporteur2 = $report->rapporteur2;
		else
			$chercheur->rapporteur2 = "";

		if(isset($report->rapporteur3))
			$chercheur->rapporteur3 = $report->rapporteur3;
		else
			$chercheur->rapporteur3 = "";
		
		if(isset($report->type))
			$chercheur->type = $report->type;
		else
			$chercheur->type = "";

		if(isset($report->statut))
			$chercheur->statut = $report->statut;
		else
			$chercheur->statut = "";

		if(isset($report->id_session))
			$session = $report->id_session;

	}

	if(isset($chercheur->genre) && $chercheur->genre == "femme")
		echo '<h1>Chercheuse : '.$chercheur->nom." ".$chercheur->prenom." ".'</h1>';
	else
		echo '<h1>Chercheur : '.$chercheur->nom." ".$chercheur->prenom." ".'</h1>';

	displayEditionFrameStart("",$hidden,array());

	displayEditableObject("", $chercheur, $fieldsChercheursAll, $canedit, $session);

	displayEditionFrameEnd("Données chercheur");

}

function displayEditionFrameStart($titlle, $hidden, $submit)
{

	if($titlle != "")
		echo '<span  style="font-weight:bold;" >'.$titlle.'</span>';

	foreach($hidden as $key => $value)
		echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
	foreach($submit as $key => $value)
		echo '<input type="submit" name="'.$key.'" value="'.$value.'" />'."\n";

}

function displayEditionFrameEnd($titlle)
{
}

function displayEditableField($row, $fieldId, $canedit, $session)
{
	global $fieldsAll;
	global $fieldsTypes;
	global $mandatory_edit_fields;

	$title = compute_title($row, $fieldId);
	
	if($title != "" && is_field_visible($row, $fieldId))
	{		
		if(isset($fieldsTypes[$fieldId]))
			{
				$editable = $canedit && is_field_editable($row, $fieldId);
				if($fieldId === "fichiers")
					if(isset($row->statut) && $row->statut == "audition")
						$editable = true;

				echo '<td style="width:10%"><span>'.$title.'</span>';
				echo '</td>';

				if(!isset($row->$fieldId))
					$row->$fieldId = '';

				if(!$editable && in_array($fieldId, $mandatory_edit_fields))
					echo '<input type="hidden" name="field'.$fieldId.'" value="'.$row->$fieldId.'"/>';
					
				switch($fieldsTypes[$fieldId])
				{
					case "enum":
						display_enum($row, $fieldId, !$editable);
						break;
					case "topic":
						display_topic($row, $fieldId, !$editable);
						break;
					case "long":
						display_long($row, $fieldId, !$editable);
						break;
					case "treslong":
						display_treslong($row, $fieldId, !$editable);
						break;
					case "short":
						display_short($row, $fieldId, !$editable);
						break;
					case "avis":
						display_avis($row, $fieldId, !$editable);
						break;
					case "rapporteur":
						display_rapporteur($row, $fieldId, !$editable);
						break;
					case "unit":
						display_unit($row, $fieldId, !$editable);
						break;
					case "grade":
						display_grade($row, $fieldId, !$editable);
						break;
					case "concours":
						display_concours($row, $fieldId, !$editable);
						break;
					case "ecole":
						display_ecole($row, $fieldId, !$editable);
						break;
					case "files":
						display_fichiers($row, $fieldId, $session, !$editable);
						break;
					case "rapports":
						display_rapports($row, $fieldId);
						break;
					case "statut":
						display_statut2($row, $fieldId, !$editable); break;
					case "type":
						display_type($row, $fieldId, !$editable); break;
					case "sousjury":
						display_sousjury($row, $fieldId, !$editable); break;
				}
			}
		}
}

function displayEditableObject($titlle, $row, $fields, $canedit, $session)
{
	if($titlle != "")
		echo '<table><tr><td><h2><span  style="font-weight:bold;" >'.$titlle.'</span></h2></td></tr>';
	else
		echo '<table>';

	global $fieldsTypes;
	global $mandatory_edit_fields;

	$inline = false;

	$odd = true;
	foreach($fields as  $fieldId)
	{
		$style = is_array($fieldId) ? getStyle($fieldId[0],$odd): getStyle($fieldId,$odd);
		$odd = !$odd;

		echo '<tr class="'.$style.'" style="width:90%"><td><table style="width:90%"><tr class="'.$style.'">';

		if(is_array($fieldId))
		{
			foreach($fieldId as $singleField)
			{
				echo '<td style="width:'.strval(round(100/(count($fieldId) ))).'%">';
				echo '<table style="width:100%"><tr class="'.$style.'">'."\n";
				displayEditableField($row, $singleField,$canedit,$session);
				echo "\n".'</tr></table></td>'."\n";
			}
		}
		else
		{	
			echo '<td style="100%"><table><tr class="'.$style.'">'."\n";
			displayEditableField($row, $fieldId,$canedit,$session);
			echo "\n".'</tr></table></td>'."\n";
		}
		echo '</tr></table></td></tr>';

	}
	echo "</table>\n";

}

function voir_rapport_pdf($row)
{
	$eval_type = $row->type;

	if($eval_type  == "Candidature" && is_auditionne($row))
	{
		echo "<B>Rapports:</B>";
		if(is_auditionneCR($row))
		{
			echo "<a href=\"export.php?action=viewpdf&amp;option=Audition&amp;id=".$row->id_origine."&amp;id_origine=".$row->id_origine."\">\n";
			echo "d'audition\n";
			echo "</a>\n";
		}
		if(is_classe($row))
		{
			echo "et <a href=\"export.php?action=viewpdf&amp;option=Classement&amp;id=".$row->id_origine."&amp;id_origine=".$row->id_origine."\">\n";
			echo "sur le candidat classé\n";
			echo "</a>\n";
		}
	}
	else
	{
		echo "<a href=\"export.php?action=viewpdf&amp;id=".$row->id_origine."&amp;id_origine=".$row->id_origine."\">\n";
		echo "Voir le rapport final\n";
		echo "</a>\n";
	}

	echo "<br/>";
}

function displayEditableReport($row, $canedit = true)
{
	global $fieldsTypes;
	global $actions;
	global $avis_eval;

	global $typesRapports;
	global $statutsRapports;

	global $typesRapportsChercheurs;
	global $typesRapportsConcours;
	global $typesRapportsUnites;


	//phpinfo();
	if(!isset($row->id_origine))
		$row->id_origine = 0;

	echo '<div id="debut"></div>';
	echo '<form enctype="multipart/form-data" method="post" action="index.php" style="width: 100%">'."\n";

	$next = next_report($row->id_origine);
	$previous = previous_report($row->id_origine);

	$hidden = array(
			"next_id" => strval($next),
			"previous_id" => strval($previous),
			"action" => "update",
			"create_new" => true,
			"id_origine" => $row->id_origine
	);

	$submits = array();

	if($canedit)
		$submits["editprevious"] = "<<";
	else
		$submits["viewprevious"] = "<<";

	if($canedit)
	{
		$submits["submitandkeepediting"] = "Enregistrer";
		$submits["read"] = "Voir";
	}
	else
	{
		if(isSecretaire())
			$submits["submitandkeepviewing"] = "Enregistrer";
		$submits["edit"] = "Editer";
	}

	if(isSecretaire())
		$submits["deleteandeditnext"] = "Supprimer dernière version";
	$submits["retourliste"] = "Retour à la liste";

	if($canedit)
		$submits["editnext"] = ">>";
	else
		$submits["viewnext"] = ">>";

	$eval_type = $row->type;

	displayEditionFrameStart("",$hidden,$submits);

	voir_rapport_pdf($row);

	$is_unite = array_key_exists($eval_type,$typesRapportsUnites);
	$statut = $row->statut;

	$eval_name = $eval_type;
	if(array_key_exists($eval_type, $typesRapports))
		$eval_name = $typesRapports[$eval_type];

	$hidden = array("fieldtype" => $eval_type);

	$rapporteurs  = listNomRapporteurs();

	global $typesRapportToFields;

	if(isset($row->id_session))
		$session = $row->id_session;
	else
		$session = current_session();

	$year = substr($session, strlen($session) - 4, 4);
	
	$nb_rapporteurs = 0;
	$has_rapp = (isset($row->rapporteur) && $row->rapporteur != "");
	$has_rapp2 = (isset($row->rapporteur2) && $row->rapporteur2 != "");
	$has_rapp3 = (isset($row->rapporteur3) && $row->rapporteur3 != "");
	
	if($has_rapp) $nb_rapporteurs++;
	if($has_rapp2) $nb_rapporteurs++;
	if($has_rapp3) $nb_rapporteurs++;
	
	if(array_key_exists($eval_type, $typesRapportsConcours))
	{
		$titre = "";
		if($eval_name == "Equivalence")
			$titre= "<h1>".$year." / Equivalence: ". $row->nom." ".$row->prenom. ( (isset($row->grade_rapport) &&  $row->grade_rapport != "") ? (" (grade  " .$row->grade_rapport) .")" : "") . "</h1>";
		else
			$titre= "<h1>".$year." / ".$eval_name. ": ". $row->nom." ".$row->prenom.( isset($row->concours)  ? (" / concours ".$row->concours) : ""). ( (isset($row->sousjury) && $row->sousjury != "")  ? (" sousjury ".$row->sousjury) : ""). "</h1>";

		$candidate = get_or_create_candidate($row);
		$conflit = (
				 is_in_conflict(getLogin(), $candidate))
		 && !isSecretaire()
		 && !( isset($row->avis) && ($row->avis == "nonauditionne" )
		 && !(isset($row->statut) && ( $row->statut="rapport" || $row->statut="publie") ) );

		echo $titre;


		if(true)
		{
			displayEditableCandidate($candidate,$row,$canedit);
				
			$other_reports = find_somebody_reports($candidate,$eval_type);
			echo "<br/><hr/><br/>";
				
			$fieldsRapportsCandidat0 = $typesRapportToFields[$eval_type][1];
			$fieldsRapportsCandidat1 = $typesRapportToFields[$eval_type][2];
			$fieldsRapportsCandidat2 = $typesRapportToFields[$eval_type][3];
			$fieldsRapportsCandidat3 = $typesRapportToFields[$eval_type][4];
			
			echo $titre;

			$submits = array();
				
			foreach($other_reports as $report)
				if($report->concours != $row->concours)
					$submits["importconcours".$report->concours] = "Importer données concours ".$report->concours;
				
			$hidden['fieldconcours'] = $row->concours;
				
			displayEditionFrameStart("",$hidden,$submits);
							
			if(!$conflit)
			{
				echo'<table><tr>';
				if($has_rapp)
				{
					echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
					displayEditableObject("Prérapport 1".(isset($rapporteurs[$row->rapporteur]) ? (" - ".$rapporteurs[$row->rapporteur]) : "" ),$row,$fieldsRapportsCandidat1,$canedit, $session);
					echo'</td>';
				}
				if($has_rapp2)
				{
					echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
					displayEditableObject("Prérapport 2".(isset($rapporteurs[$row->rapporteur2]) ? (" - ".$rapporteurs[$row->rapporteur2]) : "" ),$row,$fieldsRapportsCandidat2,$canedit, $session);
					echo'</td>';
				}
				if($has_rapp3)
				{
					echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
					displayEditableObject("Prérapport 3".(isset($rapporteurs[$row->rapporteur3]) ? (" - ".$rapporteurs[$row->rapporteur3]) : "" ),$row,$fieldsRapportsCandidat3,$canedit, $session);
					echo'</td>';
				}
				echo'</tr></table>';
			}

			displayEditableObject("Rapport section", $row, array_merge(array("statut"),$fieldsRapportsCandidat0),$canedit, $session);
		}
	}
	else if(array_key_exists($eval_type, $typesRapportsChercheurs))
	{
		$chercheur = get_or_create_candidate($row);
		$conflit = ( is_in_conflict(getLogin(), $chercheur)) && !isSecretaire()  ;

		displayEditableChercheur($chercheur,$row,$canedit);

		//$other_reports = find_somebody_reports($chercheur,$eval_type);
		echo "<br/><hr/><br/>";

		$fieldsIndividual0 = $typesRapportToFields[$eval_type][1];
		$fieldsIndividual1 = $typesRapportToFields[$eval_type][2];
		$fieldsIndividual2 = $typesRapportToFields[$eval_type][3];
		$fieldsIndividual3 = $typesRapportToFields[$eval_type][4];
		
		global $fieldsUnitesExtra;

		if(key_exists($eval_type,$fieldsUnitesExtra))
			$fieldsIndividual0 = array_merge($fieldsUnitesExtra[$eval_type], $fieldsIndividual0);

			
		echo "<h1>".$eval_name. ": ". (isset($row->nom) ? $row->nom : "")." ".(isset($row->prenom) ? $row->prenom : "");
		echo " (".(isset($row->id) && $row->id != 0 ? "#".$row->id : "New").")</h1>";


		if(!$conflit)
		{

			displayEditionFrameStart("",$hidden,array());

			echo'<table><tr>';
			if($has_rapp)
			{
				echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
				displayEditableObject("Prérapport 1".(isset($rapporteurs[$row->rapporteur]) ? (" - ".$rapporteurs[$row->rapporteur]) : "" ),$row,$fieldsIndividual1,$canedit, $session);
				echo'</td>';
			}
			if($has_rapp2)
			{
				echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
				displayEditableObject("Prérapport 2".(isset($rapporteurs[$row->rapporteur2]) ? (" - ".$rapporteurs[$row->rapporteur2]) : "" ),$row,$fieldsIndividual2,$canedit, $session);
				echo'</td>';
			}
			if($has_rapp3)
			{
				echo '<td VALIGN="top" style="width: ".(100 / $nb_rapporteurs)."%">';
				displayEditableObject("Prérapport 3".(isset($rapporteurs[$row->rapporteur3]) ? (" - ".$rapporteurs[$row->rapporteur3]) : "" ),$row,$fieldsIndividual3,$canedit, $session);
				echo'</td>';
			}
						echo '</tr></table>';
		}
		displayEditableObject("Rapport section", $row,$fieldsIndividual0, $canedit, $session);
	}
	else if(array_key_exists($eval_type, $typesRapportsUnites))
	{
		$units = unitsList();

		$fieldsUnites0 = $typesRapportToFields[$eval_type][1];
		$fieldsUnites1 = $typesRapportToFields[$eval_type][2];
		$fieldsUnites2 = $typesRapportToFields[$eval_type][3];

		global $fieldsUnitesExtra;

		if(key_exists($eval_type,$fieldsUnitesExtra))
			$fieldsUnites0 = array_merge($fieldsUnitesExtra[$eval_type],$fieldsUnites0);
			
		echo "<h1>".$eval_name. ": ". (isset($row->unite) ? $row->unite : "")." (#".(isset($row->id) && $row->id != 0 ? $row->id : "New").")</h1>";

		displayEditionFrameStart("",$hidden,array());

		echo'<table><tr>';

		if(isset($row->rapporteur) && $row->rapporteur != "")
		{
			echo'<td>';
			displayEditableObject("Prérapport 1", $row,$fieldsUnites1, $canedit, $session);
			echo'</td>';
		}
		if(isset($row->rapporteur2) && $row->rapporteur2 != "")
		{
			echo'<td>';
			displayEditableObject("Prérapport 2",$row,$fieldsUnites2, $canedit, $session);
			echo'</td>';
		}
		if(isset($row->rapporteur3) && $row->rapporteur3 != "")
		{
			echo'<td>';
			displayEditableObject("Prérapport 3",$row,$fieldsUnites3, $canedit, $session);
			echo'</td>';
		}
		
		echo'</tr></table>';
		displayEditableObject("Rapport section", $row,$fieldsUnites0, $canedit, $session);

	}
	echo "</form>\n";

	echo('
			<script type="text/javascript">');
	echo('
			document.getElementById("debut").scrollIntoView();');

	/*
	 echo('
	 		var elt = document.getElementById( '$id' );
	 		var top = (	return elt.offsetTop + ( elt.offsetParent ? elt.offsetParent.documentOffsetTop() : 0 )) - ( window.innerHeight / 2 );
	 		window.scrollTo( 0, top );
	 		');
	*/
	echo('		</script>');

}

function editReport($id_rapport)
{
	try
	{
		$report = getReport($id_rapport);

		$row = normalizeReport($report);

		$candidat = get_or_create_candidate($row);
		displayEditableReport($row, true);
	}
	catch(Exception $exc)
	{
		throw new Exception("Echec de l'édition du rapport:\n ".$exc->getMessage());
	}

};

function viewReport($id_rapport)
{
	try
	{
		$report = getReport($id_rapport);
		$row = normalizeReport($report);
		$candidat = get_or_create_candidate($row);
		displayEditableReport($row, false);
	}
	catch(Exception $exc)
	{
		throw new Exception("Echec de l'édition du rapport:\n ".$exc->getMessage());
	}

};

function displayActionsMenu($row, $excludedaction = "", $actions)
{
	$id = $row->id;
	$id_origine = $row->id_origine;
	echo "<table><tr>";
	foreach($actions as $action => $actiondata)
	{
		if ($action!=$excludedaction)
		{
			$title = $actiondata['title'];
			$icon = $actiondata['icon'];
			$page = $actiondata['page'];
			$level = $actiondata['level'];
			if(getUserPermissionLevel() >= $level )
			{

				echo "<td>\n<a href=\"$page?action=$action&amp;id=$id&amp;id_origine=$id_origine\">\n";
				echo "<img class=\"icon\" width=\"24\" height=\"24\" src=\"$icon\" alt=\"$title\"/>\n</a>\n</td>\n";
			}
		}
	}
	//	rrr();
	echo "</tr></table>";
}

function displaySummary($filters, $filter_values, $sorting_values)
{
	global $fieldsSummary;
	global $fieldsSummaryConcours;
	global $typesRapports;
	global $statutsRapports;
	global $filtersReports;
	global $fieldsTypes;

	global $avis_classement;

	$rows = filterSortReports($filters, $filter_values, $sorting_values);

	$rows_id = array();
	foreach($rows as $row)
		$rows_id[] = $row->id;
	$_SESSION['rows_id'] = $rows_id;


	if(is_current_session_concours())
		$fields = $fieldsSummaryConcours;
	else
		$fields = $fieldsSummary;

	if( isset($filter_values["type"]) && $filter_values["type"] == "Promotion")
	{
		$filters["avis"]["liste"] = $avis_classement;
		$filters["avis1"]["liste"] = $avis_classement;
		$filters["avis2"]["liste"] = $avis_classement;
	}

	if(isSecretaire())
		$fields = array_unique(array_merge($fields,array(/*"date","auteur","id",*/"statut")));

	if($filter_values['type'] != $filters['type']['default_value'] )
	{
		$new_field = array();
		foreach($fields as $field)
			if($field != 'type')
			$new_field[] = $field;
		$fields = $new_field;
	}

	displayRows($rows,$fields, $filters, $filter_values, getCurrentSortingList(), $sorting_values);
}



?>