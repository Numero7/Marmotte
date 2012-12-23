<?php
	include "configDB.inc.php";
	
	
	define("president","Frédérique Bassino");
	define("president_titre","Présidente de la Section 6");
	define("secretaire","Hugo Gimbert");
	define("section_nb","6");
	define("section_fullname","Section 6 du CoNRS");
	define("section_intitule","Sciences de l'information : fondements de l'informatique, calculs, algorithmes, représentations, exploitations");
	
	
	
	$fieldsSummary = array(
		"nom",
		"prenom",
		"grade",
		"unite",
		"type",
		"rapporteur",
		"date",
	);

	$fieldsAll = array(
		"nom" => "Nom",
		"prenom" => "Prenom",
		"unite" => "Unité",
		"grade" => "Grade",
		"type" => "Type",
		"rapporteur" => "Rapporteur",
		"prerapport" => "Points marquants",
		"anciennete_grade" => "Ancienneté dans grade",
		"date_recrutement" => "Date de recrutement",
		"production" => "Production scientifique",
		"production_notes" => "Production scientifique",
		"transfert" => "Transfert et valorisation",
		"transfert_notes" => "Détails transfert/valorisation",
		"encadrement" => "Encadrement",
		"encadrement_notes" => "Détails sur l'encadrement",
		"responsabilites" => "Responsabilités collectives",
		"responsabilites_notes" => "Détails responsabilités",
		"mobilite" => "Mobilité",
		"mobilite_notes" => "Détails sur la mobilité",
		"animation" => "Animation scientifique",
		"animation_notes" => "Détails sur l'animation",
		"rayonnement" => "Rayonnement",		
		"rayonnement_notes" => "Détails sur le rayonnement",		
		"rapport" => "Proposition de rapport",
		"avis" => "Proposition d'avis",
		"auteur" => "Auteur Dernière(s) modif(s)",
		"date" => "Date modification",
	);
	
	$fieldsUnites = array(
		"unite",
		"type",
		"rapporteur",
		"rapport",
		"prerapport",
		"avis",
		"auteur",
		"date"
	);
		
	$examples = array(
		"nom" => "Doe",
		"prenom" => "John",
		"grade" => "DRCE",
		"unite" => "UMR 6666 (HELL)",
		"type" => "Promotion",
		"rapporteur" => "Anne ONYME",
		"prerapport" => "Candidat au fort potentiel, proche de la retraite ...",
		"anciennete_grade" => "~4 ans",
		"date_recrutement" => "1999",
		"production" => "A-",
		"production_notes" => "Nombreuses revues et conférences ...",
		"transfert" => "A",
		"transfert_notes" => "Un brevet et quelques logiciels diffusés ...",
		"encadrement" => "B",
		"encadrement_notes" => "Un étudiant en thèse, quelques stagiaires de M2 ...",
		"responsabilites" => "A+",
		"responsabilites_notes" => "Membre du comité national ...",
		"mobilite" => "C",
		"mobilite_notes" => "Peu de visites, en poste dans son labo de thèse ...",
		"animation" => "A+",
		"animation_notes" => "Jongle et joue de l'harmonica tout en présidant son GDR ...",
		"rayonnement" => "B+",		
		"rayonnement_notes" => "Travaux assez cités relativement aux pratiques de son domaine ...",		
		"rapport" => "La section 06 vous invite à renouveler votre garde robe. ..",
		"avis" => "Réservé",
		"auteur" => "joe",
		"date" => "3/02/2013",
	);

	$empty_report = array(
		"id_session" => "",
		"nom" => "",
		"prenom" => "",
		"grade" => "",
		"unite" => "",
		"type" => "",
		"rapporteur" => "",
		"prerapport" => "",
		"anciennete_grade" => "",
		"date_recrutement" => "",
		"production" => "",
		"production_notes" => "",
		"transfert" => "",
		"transfert_notes" => "",
		"encadrement" => "",
		"encadrement_notes" => "",
		"responsabilites" => "",
		"responsabilites_notes" => "",
		"mobilite" => "",
		"mobilite_notes" => "",
		"animation" => "",
		"animation_notes" => "",
		"rayonnement" => "",		
		"rayonnement_notes" => "",		
		"rapport" => "",
		"avis" => "",
		"auteur" => "",
		"date" => date(DATE_RSS),
		"id_origine" => "-1"
	);
		
	$fieldsTypes = array(
		"nom" => "short",
		"prenom" => "short",
		"grade" => "short",
		"unite" => "unit",
		"type" => "short",
		"rapporteur" => "rapporteur",
		"prerapport" => "treslong",
		"anciennete_grade" => "short",
		"date_recrutement" => "short",
		"production" => "evaluation",
		"production_notes" => "long",
		"transfert" => "evaluation",
		"transfert_notes" => "long",
		"encadrement" => "evaluation",
		"encadrement_notes" => "long",
		"responsabilites" => "evaluation",
		"responsabilites_notes" => "long",
		"mobilite" => "evaluation",
		"mobilite_notes" => "long",
		"animation" => "evaluation",
		"animation_notes" => "long",
		"rayonnement" => "evaluation",		
		"rayonnement_notes" => "long",		
		"rapport" => "treslong",
		"avis" => "avis",
		"auteur" => "short",
		"date" => "short",
	);
	
	$fieldsEdit = array(
		"id" => "Identifiant",
		"nom" => "Nom",
		"prenom" => "Prenom",
	);
	
	$typesRapportsIndividuels = array(
		'Evaluation-Vague' => 'Evaluation à Vague',
		'Evaluation-MiVague' => 'Evaluation à Mi-Vague',
		'Promotion' => 'Promotion',
		'Candidature' => 'Candidature',
		'Suivi-PostEvaluation' => 'Suivi Post-Evaluation',
		'Titularisation' => 'Titularisation',
		'Affectation' => 'Confirmation d\'Affectation'
	);

	$typesRapportsUnites = array(
			'Changement-Direction' => 'Changement de Direction',
			'Renouvellement' => 'Renouvellement',
			'Expertise' => 'Expertise',
			'Ecole' => 'Evaluation d\'Ecole Thematique',
			'Comite-Evaluation' => 'Comité d\'Evaluation'
	);
	
	$typesRapports = array_merge($typesRapportsIndividuels, $typesRapportsUnites);
	
	$typesRapportsUpperCase = array(
			'Evaluation-Vague' => 'EVALUATION A VAGUE DE CHERCHEUR',
			'Evaluation-MiVague' => 'EVALUATION A MI-VAGUE DE CHERCHEUR',
	);
	
	
	$avis_eval = array(
			""=>"",
			"favorable" => "Favorable",
			"differe" => "Différé",
			"reserve" => "Réservé",
			"alerte" => "Alerte"
	);

	$avis_classement = array(""=>"", "non"=>"Non", "oui"=>"Oui", "1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7" , "8"=>"8", "9"=>"9"
			, "10"=>"10", "11"=>"11", "12"=>"12", "13"=>"13", "14"=>"14", "15"=>"15", "16"=>"16", "17"=>"17", "18"=>"18", "19"=>"19",
			 "20"=>"20", "21"=>"21");

	$avis_candidature = array(""=>"", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non-Classé", "1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7" , "8"=>"8", "9"=>"9"
			, "10"=>"10", "11"=>"11", "12"=>"12", "13"=>"13", "14"=>"14", "15"=>"15", "16"=>"16", "17"=>"17", "18"=>"18", "19"=>"19",
			"20"=>"20", "21"=>"21");
	
	$avis_vide = array(""=>"");

	$avis_binaire = array(
			""=>"",
			"favorable" => "Favorable",
			"reserve" => "Réservé",
			"differe" => "Différé",
			"sansavis" => "Pas d'avis"
	);

	$avis_ternaire = array(
			""=>"", 
			"tresfavorable" => "Très Favorable",
			"favorable" => "Favorable",
			"reserve" => "Réservé",
			"differe" => "Différé",
			"sansavis" => "Pas d'avis"
	);

	$avis_ecoles = array(
			""=>"",
			"tresfavorable" => "Très Favorable",
			"favorable" => "Favorable",
			"defavorable" => "Défavorable",
			"sansavis" => "Pas d'avis"
	);
	
	$avis_pertinence = array(
			""=>"",
			"tresfavorable" => "Très Favorable",
			"favorable" => "Favorable",
			"defavorable" => "Défavorable",
			"reserve" => "Réservé",
			"sansavis" => "Pas d'avis"
	);
	
	$typesRapportToAvis = array(
		'Evaluation-Vague' => $avis_eval,
		'Evaluation-MiVague' => $avis_eval,
		'Promotion' => $avis_classement,
		'Candidature' => $avis_candidature,
		'Suivi-PostEvaluation' => $avis_vide,
		'Affectation' => $avis_binaire,
		'Titularisation' => $avis_binaire,
		'Changement-Direction' => $avis_pertinence,
		'Renouvellement' => $avis_pertinence,
		'Expertise' => $avis_pertinence,
		'Ecole' => $avis_ecoles,
		'Comite-Evaluation' => $avis_binaire
	);
	

/* Definition des checkboxes à la fin de certains rapports*/
	$evalCheckboxes = array(
			"favorable" => "Favorable",
			"differe" => "Différé",
			"reserve" => "Réservé",
			"alerte" => "Alerte");

	$pertinenceCheckboxes = array(
			"tresfavorable" => "Très Favorable",
			"favorable" => "Favorable",
			"defavorable" => "Défavorable",
			"reserve" => "Réservé",
			"sansavis" => "Pas d'avis"
		);

	$ecoleCheckboxes = array(
			"tresfavorable" => "Très Favorable",
			"favorable" => "Favorable",
			"defavorable" => "Défavorable"
	);
	
	$typesRapportsToCheckboxes = array(
	'Evaluation-Vague' => $evalCheckboxes,
	'Evaluation-MiVague' => $evalCheckboxes,
	'Renouvellement' => $pertinenceCheckboxes,
	'Expertise' => $pertinenceCheckboxes,
	'Ecole' => $ecoleCheckboxes
	);
	

/* Definition des formaules standards à la fin de certains rapports*/
	
	$promotionFormula = array(
			'non'=> 'Le faible nombre de possibilités de promotions DR1 cette année ne permet malheureusement pas à la Section 6 du Comité National de proposer ce chercheur à la Direction Générale du CNRS pour une promotion cette année.'
			);
	
	$typesRapportsToFormula = array(
		'Promotion' => $promotionFormula,
			);

	
/* Definition des différents grades*/
	
	$grades = array(
		'CR2' => 'Chargé de Recherche 2ème classe (CR2)',
		'CR1' => 'Chargé de Recherche 1ère classe (CR1)',
		'DR2' => 'Directeur de Recherche 2ème classe (DR2)',
		'DR1' => 'Directeur de Recherche 1ère classe (DR2)',
		'DRCE1'  => 'Dir. de Recherche Classe Except. 1er échelon (DRCE1)',
		'DRCE2'  => 'Dir. de Recherche Classe Except. 2ème échelon (DRCE2)',
		'ChaireMC' => 'Chaire MC',
		'ChairePR' => 'Chaire PR',
		'Emerite' => 'Emerite',
		'MC' => 'MC',
		'PR' => 'PR',
		'PhD' => 'PhD',
		'HDR' => 'Habilité à diriger des recherches',
		'None' => 'Pas de grade'
	);
	
/* Definition des différentes notes*/
	$notes = array(
		' ',
		'A+',
		'A',
		'A-',
		'B+',
		'B',
		'B-',
		'C+',
		'C',
		'C-',
	);
	
	$actions = array(
		'details' => "Détails", 
		'history' => "Historique", 
		'edit'  => "Modifier"
	);
	
	$typeExports = array(
			"pdf" => 	array(
					"mime" => "application/x-pdf",
					"xsl" => "",
					"name" => "XML",
			),
			"xml" => 	array(
					"mime" => "text/xml",
					"xsl" => "xslt/xmlidentity.xsl",
					"name" => "XML",
			),
			"latex" => 	array(
					"mime" => "application/x-latex",
					"xsl" => "",
					"name" => "Zip",
			),
			"htmlmin" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/htmlminimaledit.xsl",
					"name" => "Html",
			),
	);
	
?>