<?php
	include "configDB.inc.php";

	$typeExports = array(
		"xml" => 	array(
						"mime" => "text/xml",		
						"xsl" => "xslt/xmlidentity.xsl",
						"name" => "XML",
					),
		"latex" => 	array(
						"mime" => "application/x-latex",		
						"xsl" => "xslt/latexshort.xsl",
						"name" => "Latex",
					),
	);
	
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
		"grade" => "Grade",
		"unite" => "Unité",
		"type" => "Type",
		"rapporteur" => "Rapporteur",
		"avis" => "Proposition d'avis",
		"rapport" => "Proposition de rapport",
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
		"date" => date_default_timezone_get()
	);
		
	$fieldsTypes = array(
		"nom" => "short",
		"prenom" => "short",
		"grade" => "short",
		"unite" => "short",
		"type" => "short",
		"rapporteur" => "short",
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
		"avis" => "short",
		"auteur" => "short",
		"date" => "short",
	);
	
	$fieldsEdit = array(
		"id" => "Identifiant",
		"nom" => "Nom",
		"prenom" => "Prenom",
	);
	
	$typesEvalChercheurs = array(
		'Evaluation-Vague',
		'Evaluation-MiVague',
		'Promotion',
		'Candidature',
		'Suivi-PostEvaluation',
		'Titularisation',
		'Confirmation-Affectation',
	);

	$typesEvalUnites = array(
			'Changement-Direction-Unité',
			'Renouvellement-Unité',
			'Expertise',
			'Ecole',
			'Comité-AERES',
	);
	
	$grades = array(
		'CR2' => 'Chargé de Recherche 2ème classe (CR2)',
		'CR1' => 'Chargé de Recherche 1ère classe (CR1)',
		'DR2' => 'Directeur de Recherche 2ème classe (DR2)',
		'DR1' => 'Directeur de Recherche 1ère classe (DR2)',
		'DRCE1'  => 'Dir. de Recherche Classe Except. 1er échelon (DRCE1)',
		'DRCE2'  => 'Dir. de Recherche Classe Except. 2ème échelon (DRCE2)',
	);
	
	$evaluations = array(
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
		'edit'  => "Modifier", 
	);
	
?>