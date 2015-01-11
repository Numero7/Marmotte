<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

	date_default_timezone_set('Europe/Paris');

	
	require_once("config/configDB.inc.php");
	require_once("config_tools.inc.php");
		
	
//	define("config_file","config/config.xml");
	define("signature_file","img/signature.jpg");
//	define("config_file_save","config/config.sauv.xml");
	
	$dossier_racine = "";
	

	$fieldsSummary = array(
		"type",
		"rapporteur",
		"rapporteur2",
		"nom",
		"prenom",
		"grade_rapport",
			"avis",
			"theme1",
			"theme2",
			"theme3",
			"unite"
	);
	
	$fieldsSummaryConcours = array(
			"type",
			"nom",
			"prenom",
			"concours",
			"sousjury",
			"avis",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"theme1",
			"theme2",
			"labo1",
			"labo2",
			"labo3"
	);
	
	$fieldsTriConcours = array(
			"nom",
			"prenom",
			"grade_rapport",
			"concours",
			"sousjury",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"theme1",
			"theme2",
			"theme3",
			"labo1",
			"labo2",
			"labo3",
			"avis",
			"date"
	);
	
	$statutsRapports = array(
			'doubleaveugle'=>'Double Aveugle',
			 'prerapport' => "Prerapport",
			 'rapport'=>"Rapport",
			 'audition'=>"Audition",
			 'publie'=>"Rapport publié"
			);

	$genreCandidat = array(
			'' => "None",
			'homme'=>'Homme',
			'femme' => "Femme",
	);
	
		
	$fieldsRapportAll = array(
		"statut" => "Statut rapport",
		"concours" => "Concours",
			"sousjury" => "Sous-jury",
			"section"=> "Section",
			"ecole" => "Ecole",
		"nom" => "Nom",
		"prenom" => "Prénom",
		"unite" => "Unité",
		"type" => "Type",
		"grade_rapport" => "Grade (rapport)",
		"rapporteur" => "Rapporteur 1",
		"rapporteur2" => "Rapporteur 2",
		"rapporteur3" => "Rapporteur 3",
			"avis" => "Avis Section",
			"avis1" => "Avis rapp. 1",
			"avis2" => "Avis rapp. 2",
			"avis3" => "Avis rapp. 3",
			"avissousjury" => "Avis sur l'audition",
			"DU" => "Au titre de DU",
			"international" => "Au titre Mobilité internationale",
			"finalisationHDR" => "Au titre  finalisation HDR",
			"national" => "Au titre d'une mobilité nationale",
			"rapport" => "Rapport Section",
		"prerapport" => "Prérapport 1",
		"prerapport2" => "Prérapport 2",
		"prerapport3" => "Prérapport 3",
			/*
			"production" => "Production<br/>scientifique",
			"production2" => "Production<br/>scientifique<br/>(rapp. 2)",
		"transfert" => "Transfert<br/>et valorisation",
		"transfert2" => "Transfert<br/>et valorisation<br/>(rapp. 2)",
		"encadrement" => "Encadrement",
		"encadrement2" => "Encadrement<br/>(rapp. 2)",
		"responsabilites" => "Responsabilités<br/>collectives",
		"responsabilites2" => "Responsabilités<br/>collectives<br/>(rapp. 2)",
		"mobilite" => "Mobilité",
		"mobilite2" => "Mobilité<br/>(rapp. 2)",
		"animation" => "Animation<br/>scientifique",
		"animation2" => "Animation<br/>scientifique<br/>(rapp. 2)",
		"rayonnement" => "Rayonnement",		
		"rayonnement2" => "Rayonnement<br/>(rapp. 2)",	
		*/	
		"auteur" => "Auteur dernière modif",
		"date" => "Date modification",
		"id" => "Id",
			"id_session" => "Id session",
			"id_origine" => "Id origine",
	);
	
	
	$global_fields_renaming = array(); //get_config_array("renommage_champs");

	$type_specific_fields_renaming = 
	array(
			"Expertise" => array("ecole" => "Intitulé du rapport"),
			"Generique" => array("ecole" => "Intitulé du rapport"),
				"GeneriqueChercheur" => array("ecole" => "Intitulé du  rapport"),
			"Changement-section" => array("ecole" => "Intitulé du  rapport")
	);
	
	/*
	 * Les champs disponibles au secrétaire pour un rapport individuel
	 */
	$fieldsIndividual0 = array(
			"type",
			"statut",
			array(
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			),
//			"nom",
//			"prenom",
	array(
		"grade_rapport",
			"statut",),
			"unite",
			"avis",
			"rapport",
	);

	/*
	 * Les champs disponibles au rapporteur 1
	*/
	$fieldsIndividual1 = array(
			"avis1",
			"prerapport"
	);

		/*
	 * Les champs disponibles au rapporteur 2
	*/
	$fieldsIndividual2 = array(
			"avis2",
			"prerapport2"
	);
	$fieldsIndividual3 = array(
			"avis3",
			"prerapport3"
	);
	
	/*
	 * Tous les champs d'un rapport individuel
	 */
	$fieldsIndividual = array_merge($fieldsIndividual0, $fieldsIndividual1, $fieldsIndividual2, $fieldsIndividual3);
	
	$fieldsChercheursAll = array(
			"nom",
			"prenom",
			"genre",
			"grade",
			"annee_recrutement",
			"labo1",
			"theme1",
			"theme2",
			"theme3",
			"fichiers",
			"rapports"
	);
	
	$fieldsRapportsIndividual = array(
			"nom",
			"prenom",
			"genre",
			"annee_recrutement",
			"labo1",
			"theme1",
			"theme2",
			"theme3",
			"fichiers",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"statut",
			"unite",
			"grade",
			"grade_rapport",
			"avis",
			"rapport",
			"avis1",
			"prerapport",
			"avis2",
			"prerapport2",
			"avis3",
			"prerapport3"
	);
	
	/*
	* Les champs disponibles aux deux rapporteurs
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat0 = array(
			"concours",
			"sousjury",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"avis",
			"avissousjury",
			"rapport"
	);

	/*
	 * Les champs disponibles au rapporteur 1
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat1 = array(
			"avis1",
			"prerapport"
	);

	$fieldsRapportsCandidat2 = array(
			"avis2",
			"prerapport2"
	);
	
	$fieldsRapportsCandidat3 = array(
			"avis3",
			"prerapport3"
	);
		
	
	$fieldsRapportsCandidat = array_merge($fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
	
	
	$fieldsIndividualAll = array(
			"nom" => "Nom",
			"prenom" => "Prénom",
			"genre" => "Genre",
			"grade" => "Grade (Individu)",
			"annee_recrutement" => "Date de recrutement",
			"labo1" => "Labo 1",
			"labo2" => "Labo 2",
			"labo3" => "Labo 3",
			"theme1" => "Theme 1",
			"theme2" => "Theme 2",
			"theme3" => "Theme 3",
			"audition" => "Rapport d'audition",
			"concourspresentes" => "Concours présentés",
			"conflits" => "Conflits"
	);


	$mandatory_edit_fields=
	array('id','nom','prenom'
	);
	
	$mandatory_export_fields= 
	array('id','nom','prenom','genre','type','concours',
			"grade",
			"annee_recrutement",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"audition",
			"concourspresentes"
	);
	
	$fieldsAll = array_merge($fieldsRapportAll, $fieldsIndividualAll, array("rapports" => "Autres rapports"));
	
	$fieldsCandidatAvantAudition = array(
			"nom",
			"prenom",
			"genre",
			"grade",
			"annee_recrutement",
			"conflits",
			"rapports",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"concourspresentes"
	);

	$fieldsCandidatAuditionne = array_merge($fieldsCandidatAvantAudition, array("audition"));
	
	$fieldsCandidat = $fieldsCandidatAuditionne;
		

	$fieldsDelegation = array(
			"statut",
			"rapporteur",
			"nom",
			"prenom",
			"DU",
			"international",
			"finalisationHDR",
			"national",
			"unite",
			"rapports",
			"avis1",
			"prerapport",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"avis",
			"rapport",
	);

	$fieldsIndividualDefault = array(
			"statut",
			"rapporteur",
			"nom",
			"prenom",
			"unite",
			"avis1",
			"prerapport",
			"avis2",
			"prerapport2",
			"avis3",
			"prerapport3",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"avis",
			"rapport",
	);
	
	
	
	$fieldsUnites0 = array(
			array(
			"type",
			"statut",
					),
			array(
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			),
			"unite",
			"fichiers",
			"rapports",
		"avis",
		"rapport",
	);

	$fieldsEcoles0 = array(
			array(
			"type",
			"statut"),
					array("ecole"),
					array("nom"), array("prenom"),
			array(
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			),
			"unite",
			"fichiers",
			"rapports",
		"avis",
		"rapport"
			);
	
	$fieldsUnites1 = array(
			"avis1",
			"prerapport"
	);

	$fieldsUnites2 = array(
			"avis2",
			"prerapport2"
	);

	$fieldsUnites3 = array(
			"avis3",
			"prerapport3"
	);
	
	$fieldsUnitesExtra = array(
			'Expertise' => array('ecole'),
			'Generique' => array('ecole'),
				'GeneriqueChercheur' => array('ecole'),
			'Changement-section' => array('ecole')
			
	);
	
	$fieldsUnites = array_merge($fieldsUnites0, $fieldsUnites1, $fieldsUnites2);
	$fieldsEcoles = array_merge($fieldsEcoles0, $fieldsUnites1, $fieldsUnites2);
	
	$fieldsUnitsDB = array(
			"code" => "Code",
			"nickname" => "Acronyme",
			"fullname" => "Nom",
			"directeur" => "Direction"
			);
	
	$fieldsGeneric = array (
			"statut",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"nom",
		"prenom",
		"unite",
		"avis",
		"rapport",
		"prerapport"
	);
	

	$empty_report = array(
			"statut" => "doubleaveugle",
			"type" => "Generique",
			"id_session" => "",
			"nom" => "",
			"prenom" => "",
			"grade_rapport" => "",
			"unite" => "",
			"ecole" => "",
			"concours" => "",
			"type" => "",
			"rapporteur" => "",
			"rapporteur2" => "",
			"rapporteur3" => "",
			"prerapport" => "",
			"prerapport2" => "",
			"anciennete_grade" => "",
			"annee_recrutement" => "",
			"production" => "",
			"production2" => "",
			"transfert" => "",
			"transfert2" => "",
			"encadrement" => "",
			"encadrement2" => "",
			"responsabilites" => "",
			"responsabilites2" => "",
			"mobilite" => "",
			"mobilite2" => "",
			"animation" => "",
			"animation2" => "",
			"rayonnement" => "",
			"rayonnement2" => "",
			"rapport" => "",
			"avis" => "",
			"avis1" => "",
			"avis2" => "",
			"avis3" => "",
			"auteur" => "",
			"date" => date(DATE_RSS),
			"id_origine" => "0",
			"labo1" => "",
			"labo2" => "",
			"labo3" => "",
			"theme1" => "",
			"theme2" => "",
			"theme3" => "",
			"avissousjury" => "",
			"sousjury" => "",
	);
	
	$empty_individual = array(
			"nom" => "",
			"prenom" => "",
			"genre" => "None",
			"grade" => "None",
			"annee_recrutement" => "1970",
			"labo1" => "",
			"labo2" => "",
			"labo3" => "",
			"theme1" => "",
			"theme2" => "",
			"theme3" => "",
			"audition" => "1. Présentation générale du candidat: Nom, Prénom, date de la thèse, situation actuelle.<br/>
2. Parcours scientifique: Intitulé et lieu de thèse et de post-doc.<br/>
3. Production scientifique: « qualité » des publications.<br/>
4. Projet de recherche: Intitulé du projet – équipe/laboratoire d’accueil souhaité.<br/>.
Une phrase de conclusion sur le candidat incluant un commentaire sur l'audition
			<b>(à préparer par le rapporteur après l'audition, et qui sera validé en jury d’admissibilité)</b>.",
			"concourspresentes" => ""
	);
					
	$virgin_report_equivalence = 
			"La ".get_config("section_shortname")." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à un doctorat d'une université française.\n\n".
			"La ".get_config("section_shortname")." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à plus de 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"La qualification professionnelle du candidat n'est pas probante.\n\n".
			"Les travaux scientifiques présentés par le candidat ne sont pas probants.\n\n".
			"Le diplôme étranger dont le candidat est titulaire est insuffisant et n'équivaut pas à un doctorat français.\n\n".
			"L'expérience professionnelle acquise par le candidat n'équivaut pas en quantité et/ou en qualité à 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"Les titres et/ou travaux dont le candidat est titulaire est /sont insuffisants ou/et n'/ne sont/est pas convaincants.";

	$report_prototypes = array(
			'Expertise' => array('ecole' => "Expertise (projet ou suivi ou intégration équipe ou restructuration)"),
			'Generique' => array('ecole' => "Rapport sur unité"),
			'GeneriqueChercheur' => array('ecole' => "Rapport sur chercheur"),
			"Changement-section" => array('ecole' => "Changement de section, évaluation permanente par une deuxième section")
	);

	/**** TYPES DE RAPPORTS **************/
	$typesRapportsChercheurs = array(
			'Evaluation-Vague' => 'Evaluation à Vague',
			'Evaluation-MiVague' => 'Evaluation à Mi-Vague',
			'Promotion' => 'Promotion',
			'Titularisation' => 'Titularisation',
			'Affectation' => 'Confirmation d\'Affectation',
			'Reconstitution' => 'Reconstitution de Carrière',
			'Delegation' => 'Demande de Délégation',
			'Emeritat' => 'Eméritat (1ere demande)',
			'Emeritat-renouvellement' => 'Eméritat (renouvellement)',
			'Changement-section' => 'Changement de section',
			'MedailleBronze' => 'Médaille de Bronze',
			'MedailleArgent' => 'Médaille d\'Argent',
			'GeneriqueChercheur' => 'Générique (chercheur)'
	);
	
	$typesRapportsChercheursShort = array(
			'Evaluation-Vague' => 'Eval à Vague',
			'Evaluation-MiVague' => 'Eval à Mi-Vague',
			'Promotion' => 'Promotion',
			'Titularisation' => 'Titularisation',
			'Delegation' => 'Délégation',
			'Affectation' => 'Affectation',
			'Reconstitution' => 'Reconstitution',
			'Changement-section' => 'Changt section',
			'Emeritat' => 'Eméritat',
			'Emeritat-renouvellement' => 'Eméritat Renouv.',
			'MedailleBronze' => 'Méd Bronze',
			'MedailleArgent' => 'Méd Argent',
			'GeneriqueChercheur' => 'Générique'
	);
	
	$typesRapportsUnites = array(
			'Changement-Directeur' => 'Changement de Directeur',
			'Changement-Directeur-Adjoint' => 'Changement de Directeur Adjoint',
			'Renouvellement' => 'Renouvellement',
			'Association' => 'Association',
			'Ecole' => 'Ecole Thematique',
			'Comite-Evaluation' => 'Comité d\'Evaluation',
			'Expertise' => 'Expertise',
			'Colloque' => 'Colloque',
			'Generique' => 'Générique'
	);
	
	$typesRapportsUnitesShort = array(
			'Changement-Directeur' => 'Changt Directeur',
			'Changement-Directeur-Adjoint' => 'Changt Dir. Adj.',
			'Renouvellement' => 'Renouvellement',
			'Association' => 'Association',
			'Ecole' => 'Ecole Thematique',
			'Comite-Evaluation' => 'Comité d\'Evaluation',
			'Expertise' => 'Expertise',
			'Colloque' => 'Colloque',
			'Generique' => 'Générique'
	);
	
	$typesRapportsConcours = array(
			'Candidature' => 'Candidature'
	);
	
	
	$typesRapports = array_merge($typesRapportsChercheurs, $typesRapportsUnites, $typesRapportsConcours);
	
	/*********** PROTOTYPES DE RAPPORT **********************/
	
	/*
	foreach($typesRapports as $type => $intitule)
	{
		$report_prototypes[$type]["rapport"] = get_config("prototype_".$type."_rapport");
		$report_prototypes[$type]["prerapport"] = get_config("prototype_".$type."_prerapport");
		$report_prototypes[$type]["prerapport2"] = get_config("prototype_".$type."_prerapport2");
	}
		
		*/
	$mergeableTypes = array("short","treslong","long","short");
	$crashableTypes = array("auteur");
	
	$theseslocs = 			 array(
			 		"" => "Pas de these",
			 		"fr" => "France",
			 		"africa" => "Afrique",
			 		"southamerica" => "Amérique du Sud",
			 		"au" => "Australie",
			 		"other" => "Autres",
			 		"eu" => "Europe",
			 		"ru" => "Russie",
			 		"us" => "Amérique du Nord",
			 		"as" => "Asie"
			 				 );
	
	$enumFields = array(
			"genre" => array(""=>"None", "homme" => "Homme","femme" => "Femme"),
			"theseloc" => $theseslocs,
			"DU" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"international" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"finalisationHDR" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"national" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"statut" => array('doubleaveugle','prerapport','rapport','publie','supprime','audition')
			);
	
	$fieldsTypes = array(
		"ecole" => "ecole",
		"concours" => "concours",
		"sousjury" => "sousjury",
		"concourspresentes" => "long",
		"nom" => "short",
		"prenom" => "short",
		"genre" => "enum",
			"DU" => "enum",
			"international" => "enum",
			"finalisationHDR" => "enum",
			"national" => "enum",
			"statut_individu"=> "enum",
		"grade" => "grade",
		"grade_rapport" => "grade",
			"theseloc" => "enum",
			"unite" => "unit",
		"type" => "type",
		"rapporteur" => "rapporteur",
		"rapporteur2" => "rapporteur",
		"rapporteur3" => "rapporteur",
			"avis" => "avis",
			"avis1" => "avis",
			"avis2" => "avis",
			"avis3" => "avis",
			"rapport" => "treslong",
		"prerapport" => "treslong",
		"prerapport2" => "treslong",
		"prerapport3" => "treslong",
		"anciennete_grade" => "short",
		"annee_recrutement" => "short",
		"production" => "long",
		"avissousjury" => "avis",
			"transfert" => "long",
		"encadrement" => "long",
		"responsabilites" => "long",
		"mobilite" => "long",
		"animation" => "long",
		"rayonnement" => "long",		
		"production2" => "long",
		"transfert2" => "long",
		"encadrement2" => "long",
		"responsabilites2" => "long",
		"mobilite2" => "long",
		"animation2" => "long",
		"rayonnement2" => "long",		
			"auteur" => "short",
		"date" => "short",
		"conflits" => "short",
			"labo1" => "unit",
			"labo2" => "unit",
			"labo3" => "unit",
			"theme1" => "topic",
			"theme2" => "topic",
			"theme3" => "topic",
			"id" =>"short",
			"audition" => "treslong",
			"fichiers" => "files",
			"rapports" => "rapports",
			"avissousjury" => "avis",
			"statut" => "statut"
	);
	
	$nonEditableFieldsTypes = array('id','auteur','date');
	$nonVisibleFieldsTypes = array('id','auteur');
	$alwaysVisibleFieldsTypes = array('fichiers','rapports');
	

	$fieldsArrayCandidat = array($fieldsCandidat, $fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
	$fieldsArrayChercheur = array($fieldsChercheursAll, $fieldsIndividual0,$fieldsIndividual1,$fieldsIndividual2,$fieldsIndividual3);
	$fieldsArrayUnite = array(array(), $fieldsUnites0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	$fieldsArrayEcole = array(array(), $fieldsEcoles0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	
	$fieldsArrayDelegation =
	array(
			array(
					"nom",
					"prenom",
					"genre",
					"grade",
					"labo1",
					"labo2",
					"labo3",
					"theme1",
					"theme2",
					"theme3",
			),
	array("rapporteur",	"avis",	"rapport"),
			array(
	"DU",
	"international",
	"finalisationHDR",
	"national",
	"unite",
	"avis1",
//	"prerapport"
					),
			array()
);
	
	
	
	$typesRapportToFields =
	array(
			'Delegation' => $fieldsArrayDelegation,
		'Candidature' => 	$fieldsArrayCandidat,
			'Evaluation-Vague' => $fieldsArrayChercheur,
			'Evaluation-MiVague' => $fieldsArrayChercheur,
			'Promotion' => $fieldsArrayChercheur,
			'Changement-section' => $fieldsArrayChercheur,
			'MedailleBronze' => $fieldsArrayChercheur,
			'MedailleArgent' => $fieldsArrayChercheur,
			'Titularisation' => $fieldsArrayChercheur,
			'Affectation' => $fieldsArrayChercheur,
			'Reconstitution' => $fieldsArrayChercheur,
			'Emeritat' => $fieldsArrayChercheur,
			'Emeritat-renouvellement' => $fieldsArrayChercheur,
			'Changement-Directeur' => $fieldsArrayUnite,
			'Changement-Directeur-Adjoint' => $fieldsArrayUnite,
			'Renouvellement' => $fieldsArrayUnite,
			'Association' => $fieldsArrayUnite,
			'Ecole' => $fieldsArrayEcole,
			'Comite-Evaluation' => $fieldsArrayUnite,
			'Generique' => $fieldsArrayUnite,
			'GeneriqueChercheur' => $fieldsArrayChercheur,
			'Colloque' => $fieldsArrayUnite,
			'Expertise' => $fieldsArrayUnite,
	);
	
	$typesRapportsToXSL = array(
			'Candidature' => 'xslt/html2.xsl',
			'Audition' => 'xslt/audition.xsl',
			'Classement' => 'xslt/classement.xsl',
			'' => 'xslt/html2.xsl'
	);
	
	/* Définition des avis possibles pour chaque type de rapport*/
	
	/* Pour les evals à vague et mi vague*/
	$avis_eval = array(
			""=>"",
			"favorable" => "Favorable",
			"differe" => "Différé",
			"reserve" => "Réservé",
			"alerte" => "Alerte",
			"sansavis" => "Pas d'avis"
	);

	/* Pour les promos*/
	$avis_classement = array(""=>"", "adiscuter"=>"à discuter", "non"=>"non-classé", "oui"=>"Oui");
	

	/* Pour les concours*/
	$avis_candidature = array(""=>"", "adiscuter"=>"à discuter", 	'desistement' => 'Desistement', "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_short = array("tous" => "", "" =>"sans avis", 'desistement' => 'Desistement', "adiscuter"=>"à discuter", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "classe"=>"Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne", "desistement");
	
	$max_classement = 30;
	for($i = 1; $i <= $max_classement; $i++)
		$avis_candidature[strval($i)] = $avis_classement[strval($i)] = "<B>$i</B>";
	
	/* Pour les SPE par exemple*/
	$avis_vide = array(""=>"");

	$avis_ie = array(
			""=>"",
			"favorable" => "Favorable",
			"adiscuter" => "A discuter",
			"defavorable" => "Défavorable"
	);

	$avis_chgt = array(
			""=>"",
			"favorable" => "Favorable",
			"defavorable" => "Défavorable",
			"sansavis" => "Pas d'avis"
	);
	
	$avis_binaire = array(
			""=>"",
			"favorable" => "Favorable",
			"reserve" => "Réservé",
			"differe" => "Différé",
			"sansavis" => "Pas d'avis"
	);

	$avis_lettre = array(
			""=>"",
			"A+"=>"A+",
			"A"=>"A",
			"A-"=>"A-",
			"B+"=>"B+",
			"B"=>"B",
			"B-"=>"B-",
			"C"=>"C"
	);

	$avis_deleg = array(
			""=>"",
			"A+"=>"A+",
			"A"=>"A",
			"B"=>"B",
			"C"=>"C"
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
	
	$avis_sessions = array_merge($avis_eval,$avis_pertinence);
	
	/* Types d'avis disponibles dans l'interface pour chaque type de rapport*/
	$typesRapportToAvis = array(
		'Evaluation-Vague' => $avis_eval,
		'Evaluation-MiVague' => $avis_eval,
		'Emeritat' => $avis_ternaire,
		'Emeritat-renouvellement' => $avis_ternaire,
		'Promotion' => $avis_classement,
		'Changement-section' => $avis_chgt,
		'Candidature' => $avis_candidature,
		'Affectation' => $avis_ternaire,
		'Reconstitution' => $avis_binaire,
		'Titularisation' => $avis_ternaire,
		'Delegation' => $avis_deleg,
		'Changement-Directeur' => $avis_pertinence,
		'Changement-Directeur-Adjoint' => $avis_pertinence,
		'Renouvellement' => $avis_pertinence,
		'Association' => $avis_pertinence,
		'Ecole' => $avis_ecoles,
		'Comite-Evaluation' => $avis_binaire,
		'Generique' => $avis_pertinence,
		'GeneriqueChercheur' => $avis_pertinence,
		'Expertise' => $avis_ternaire,
		'Colloque' => $avis_ternaire,
			'MedailleBronze' => $avis_classement,
			'MedailleArgent' => $avis_classement,
			
	);
	
	$tous_avis = array_merge($avis_eval,$avis_classement,$avis_candidature,$avis_ie,$avis_pertinence,$avis_ecoles,$avis_binaire);

	for($i = 1; $i <= $max_classement; $i++)
		$tous_avis[$i] = strval($i);
	
/* Definition des checkboxes à la fin de certains rapports*/
	
	/*Pour les evals à vague et mi vague*/
	$evalCheckboxes = array(
			"favorable" => "<B>Avis favorable</B>	
	<small> (l’activité du chercheur est conforme à ses obligations statutaires)</small>",
			"differe" => "<B>Avis différé</B>
<small> (l’évaluation est renvoyée à la session suivante en raison de l’insuffisance ou de l'absence d'éléments du dossier)</small>",
			"reserve" => "<B>Avis réservé</B>
<small> (la section a identifié dans l’activité du chercheur un ou plusieurs éléments qui nécessitent un suivi spécifique)</small>",
			"alerte" => "<B>Avis d'alerte</B>
<small> (la section exprime des inquiétudes sur l’évolution de l’activité du chercheur))</small>");

	/* Pour les renouvellements de gdr ou création d'unités*/
	$pertinenceCheckboxes = array(
			"tresfavorable" => "<B>Avis très favorable</B>",
			"favorable" => "<B>Avis favorable</B>",
			"defavorable" => "<B>Avis défavorable</B>",
			"reserve" => "<B>Avis réservé</B>",
			"sansavis" => "<B>Pas d'avis</B>"
		);

	/* Pour les écoles thématiques*/
	$ecoleCheckboxes = array(
			"tresfavorable" => "<B>Avis très favorable</B>",
			"favorable" => "<B>Avis favorable</B>",
			"defavorable" => "<B>Avis défavorable</B>"
	);

	/* Pour les écoles thématiques*/
	$delegCheckboxes = array(
			"A+" => "<B>A+</B>",
			"A" => "<B>A</B>",
			"B" => "<B>B</B>",
			"C" => "<B>C</B>",
	);
	
	$typesRapportsToCheckboxes = array(
	'Evaluation-Vague' => $evalCheckboxes,
	'Evaluation-MiVague' => $evalCheckboxes,
	'Renouvellement' => $pertinenceCheckboxes,
	'Association' => $pertinenceCheckboxes,
	'Ecole' => $ecoleCheckboxes,
	'Delegation' => $delegCheckboxes,
	);

	$typesRapportsToCheckboxesTitles = array(
			'Evaluation-Vague' => '<B>EVALUATION A VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</B>',
			'Evaluation-MiVague' => '<B>EVALUATION A MI-VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</B>',
			'Renouvellement' => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
			'Association' => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
			'Delegation' => '<B>AVIS DE LA SECTION</B>',
			'Ecole' => '<B>AVIS SUR L\'ECOLE</B>'
	);
	

	$typesRapportsToEnteteGauche = array(
			'Delegation' => '<B>Objet de l’évaluation :</B><br/><I>demande de délégation</I>',
			'Evaluation-Vague' => '<B>Objet de l’évaluation :</B><br/><I>évaluation à vague de chercheur</I>',
			'Evaluation-MiVague' => '<B>Objet de l’évaluation :</B><br/><I>évaluation à mi-vague de chercheur</I>',
			'Promotion' => '<B>Objet de l’évaluation :</B><br/>Avancement de grade<br/><B>Au grade de :</B>',
			'Changement-section' => '<B>Objet de l’évaluation :</B><br/>',
			'Candidature' => '<B>Objet de l’évaluation :</B><br/><I>Candidature au concours</I>',
			'Affectation' => '<B>Objet de l’évaluation :</B><br/>Affectation',
			'Titularisation' => '<B>Objet de l’évaluation :</B><br/>Titularisation',
			'Reconstitution' => '<B>Objet :</B><br/>Reconstitution de carrière',
			'Changement-Directeur' =>  '<B>Objet de l’évaluation :</B><br/>Changement de directeur',
			'Changement-Directeur-Adjoint' =>  '<B>Objet de l’évaluation :</B><br/>Changement de directeur adjoint',
			'Renouvellement' => '<B>Objet de l’examen :</B> <I>avis de pertinence d’association au CNRS : renouvellement</I>',
			'Association' => '<B>Objet de l’examen :</B> <I>avis de pertinence d’association au CNRS : projet d\'association</I>',
			'Ecole' => '<B>Objet de l’évaluation :</B><br/> Ecole Thématique',
			'Comite-Evaluation' => '<B>Objet de l’examen :</B> Comité d\'évaluation',
			'Generique' => '<B>Objet de l’évaluation :</B><br/>',
			'GeneriqueChercheur' => '<B>Objet de l’évaluation :</B><br/>',
			'MedailleBronze' => '<B>Objet de l’évaluation :</B><br/>Proposition de lauréat pour la médaille de bronze',
			'MedailleArgent' => '<B>Objet de l’évaluation :</B><br/>Proposition de lauréat pour la médaille d\'argent',
			'Expertise' =>  '<B>Objet de l’examen :</B><br/>',
			'Colloque' =>  '<B>Objet de l’examen :</B> Colloque',
			'Emeritat' => '<B>Objet de l’évaluation :</B><br/><I>Eméritat (1ere demande)</I>',
			'Emeritat-renouvellement' => '<B>Objet de l’évaluation :</B><br/><I>Eméritat (renouvellement)</I>',
			'' => ''
	);

	
	$enTetesDroit = array(
			'Individu' => '<B>Nom, prénom et affectation du chercheur :</B><br/>',
			'Concours' => '<B>Concours, classement, nom et prénom du candidat :</B><br/>',
			'Unite' => '<B>Code, intitulé et nom<br/>du directeur de l’unité :</B><br/>',
			'Ecole' => '<B>Nom de l\'école et du porteur de projet :</B><br/>',
			'PromotionDR' => '<B>Classement, nom et unité :</B><br/>',
			'' => '&nbsp;'
			);
	
	$typesRapportsToEnteteDroit = array(
			'Delegation' => 'Individu',
			'Evaluation-Vague' => 'Individu',
			'Evaluation-MiVague' => 'Individu',
			'MedailleBronze' => 'Individu',
			'MedailleArgent' => 'Individu',
			'Emeritat' => 'Individu',
			'Emeritat-renouvellement' => 'Individu',
			'Promotion' => 'Individu',
			'Changement-section' => 'Individu',
			'Candidature' => 'Concours',
			'Affectation' => 'Individu',
			'Titularisation' => 'Individu',
			'Reconstitution' => 'Individu',
			'Changement-Directeur' =>  'Unite',
			'Changement-Directeur-Adjoint' =>  'Unite',
			'Renouvellement' => 'Unite',
			'Association' => 'Unite',
			'Ecole' => 'Ecole',
			'Comite-Evaluation' => 'Unite',
			'Generique' => 'Unite',
			'GeneriqueChercheur' => 'Individu',
			'Expertise' => 'Unite',
			'Colloque' => 'Unite',
			'' => ''
	);
	
	
/* Definition des formaules standards à la fin de certains rapports*/
	
	$typesRapportsToFormula = array();
	
	$typesRapportsToFormula['Promotion']['oui'] = 
		get_config("formule_standard_Promotion_oui", 'La section donne un avis favorable à la demande de promotion.');

	$typesRapportsToFormula['Promotion']['non'] =
		get_config("formule_standard_Promotion_non", 'Le faible nombre de possibilités de promotions ne permet malheureusement pas à la Section 6 du Comité National de proposer ce chercheur à la Direction Générale du CNRS pour une promotion cette année.');
	
	$typesRapportsToFormula['Titularisation']['favorable'] =
		get_config("formule_standard_Titularisation_favorable", 'La section donne un avis favorable à la titularisation.');
	
/* Definition des différents grades*/
	
	$grades = array(
		'CR2' => 'Chargé de Recherche 2ème classe (CR2)',
		'CR1' => 'Chargé de Recherche 1ère classe (CR1)',
		'DR2' => 'Directeur de Recherche 2ème classe (DR2)',
		'DR1' => 'Directeur de Recherche 1ère classe (DR1)',
		'DRCE1'  => 'Dir. de Recherche Classe Except. 1er échelon (DRCE1)',
		'DRCE2'  => 'Dir. de Recherche Classe Except. 2ème échelon (DRCE2)',
		'ChaireMC' => 'Chaire MdC',
		'ChairePR' => 'Chaire PR',
		'Emerite' => 'Emérite',
		'MC' => 'MdC',
		'PR' => 'Prof',
		'PhD' => 'PhD',
		'HDR' => 'Habilité à diriger des recherches',
		'chercheur' => 'Chercheur contractuel',
			'postdoc' => 'Postdoctorant',
		'CR1_INRIA' => 'CR1 INRIA',
		'IR_CNRS' => 'IR CNRS',
	);
	
	define("NIVEAU_PERMISSION_BASE", 0);
	define("NIVEAU_PERMISSION_BUREAU", 100);
	define("NIVEAU_PERMISSION_SECRETAIRE", 500);
	define("NIVEAU_PERMISSION_PRESIDENT", 700);
	define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
	define("NIVEAU_PERMISSION_INFINI", 10000000);
	
	/* permissions levels for actions */
	$actions = array(
			"removetopic" => NIVEAU_PERMISSION_SECRETAIRE,
			"addtopic" => NIVEAU_PERMISSION_SECRETAIRE,
			"updateconfig" => NIVEAU_PERMISSION_SECRETAIRE,
			"delete" => NIVEAU_PERMISSION_SECRETAIRE,
			"change_statut" => NIVEAU_PERMISSION_SECRETAIRE,
			"view" => NIVEAU_PERMISSION_BASE,
			"deleteCurrentSelection" => NIVEAU_PERMISSION_SECRETAIRE,
			"affectersousjurys" => NIVEAU_PERMISSION_SECRETAIRE,
			"edit" => NIVEAU_PERMISSION_BASE,
			"read" => NIVEAU_PERMISSION_BASE,
			"upload" => NIVEAU_PERMISSION_SECRETAIRE,
			"update" => NIVEAU_PERMISSION_BASE,
			"change_current_session" => NIVEAU_PERMISSION_SECRETAIRE,
			"new" => NIVEAU_PERMISSION_SECRETAIRE,
			"newpwd" => NIVEAU_PERMISSION_SECRETAIRE,
			"adminnewpwd" => NIVEAU_PERMISSION_SECRETAIRE,
			"admin" => NIVEAU_PERMISSION_SECRETAIRE,
			"admindeleteaccount" => NIVEAU_PERMISSION_SECRETAIRE,
			"infosrapporteur" => NIVEAU_PERMISSION_SECRETAIRE,
			"checkpwd" => NIVEAU_PERMISSION_SECRETAIRE,
			"adminnewaccount" => NIVEAU_PERMISSION_SECRETAIRE,
			"admindeletesession" => NIVEAU_PERMISSION_SECRETAIRE,
			"changepwd" => NIVEAU_PERMISSION_BASE,
			"add_concours" => NIVEAU_PERMISSION_SECRETAIRE,
			"delete_concours" => NIVEAU_PERMISSION_SECRETAIRE,
			"ajoutlabo" => NIVEAU_PERMISSION_SECRETAIRE,
			"deletelabo" => NIVEAU_PERMISSION_SECRETAIRE,
			"mailing" => NIVEAU_PERMISSION_SECRETAIRE,
			"email_rapporteurs" => NIVEAU_PERMISSION_SECRETAIRE,
			"createhtpasswd" => NIVEAU_PERMISSION_SECRETAIRE,
			"trouverfichierscandidats" => NIVEAU_PERMISSION_SECRETAIRE,
			"creercandidats" => NIVEAU_PERMISSION_SECRETAIRE,
			"creercandidats" => NIVEAU_PERMISSION_SECRETAIRE,
			"injectercandidats" => NIVEAU_PERMISSION_SECRETAIRE,
			"displayunits" => NIVEAU_PERMISSION_SECRETAIRE,
			"displayimportexport" => NIVEAU_PERMISSION_SECRETAIRE
	);
	
	$actions1 = array(
/*		'details' => array('left' => true, 'title' => "Détails", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),*/
		'edit' => array('left' => true, 'title' => "Modifier", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),
		'download' => array('left' => true, 'title' => "Exporter", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/zip-icon-24px.png')
	);
	$actions2 = array(
			'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_SECRETAIRE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
			'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_SECRETAIRE, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'),
			'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
			'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
	);
	$actions = array_merge($actions1, $actions2);
	
	$fieldsPermissions = array(
			"statut" => NIVEAU_PERMISSION_SECRETAIRE,
			"concours" => NIVEAU_PERMISSION_SECRETAIRE,
			"type" => NIVEAU_PERMISSION_SECRETAIRE,
			"rapporteur" => NIVEAU_PERMISSION_BUREAU,
			"rapporteur2" => NIVEAU_PERMISSION_BUREAU,
			"rapporteur3" => NIVEAU_PERMISSION_BUREAU,
			"avis" => NIVEAU_PERMISSION_SECRETAIRE,
			"auteur" => NIVEAU_PERMISSION_INFINI,
			"date" => NIVEAU_PERMISSION_INFINI,
			"id" => NIVEAU_PERMISSION_INFINI,
			"id_session" => NIVEAU_PERMISSION_INFINI,
			"id_origine" => NIVEAU_PERMISSION_INFINI
	);
	

	$typeExports = array(
			/*
			"htmledit" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/htmlminimaledit.xsl",
					"name" => "HtmlEdit",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU,
			),
			*/
			"pdf" => 	array(
					"mime" => "application/x-zip",
					"xsl" => "",
					"name" => "Rapports (pdf)",
					"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE,
			),
			"html" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/html2.xsl",
					"name" => "Rapports (html)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			"text" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/html2.xsl",
					"name" => "Dossiers (text)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			/*
			"csv" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "un CSV par dossier",
					"permissionlevel" => NIVEAU_PERMISSION_BASE
			),
			*/
			"csvsingle" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "Dossiers (csv)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE
			),
			"csvbureau" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "Rapporteurs",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU
			),
			"releveconclusions" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "Relevé Conclusions",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU
			),
				
			"jad" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "JAD (pdf)",
					"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE
			),
			"jadhtml" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "JAD (html)",
					"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE
			)
			);

	$report_types_with_multiple_exports = array(
			'Candidature' => array('Audition', 'Classement')
			);
	
			$typeImports = array(
					"xml" => 	array(
							"mime" => "text/xml",
							"xsl" => "xslt/xmlidentity.xsl",
							"name" => "XML",
							"permissionlevel" => NIVEAU_PERMISSION_BASE,
					),
					"txt" => 	array(
							"mime" => "text/xml",
							"xsl" => "xslt/xmlidentity.xsl",
							"name" => "XML",
							"permissionlevel" => NIVEAU_PERMISSION_BASE,
					),
					"csv" => 	array(
							"mime" => "application/x-text",
							"xsl" => "",
							"name" => "CSV",
							"permissionlevel" => NIVEAU_PERMISSION_BASE
					)
	);
	
	global $dbh;
	$sql = "SELECT * FROM `".concours_db."` WHERE";
	$sql .= " `section`='". real_escape_string($_SESSION['filter_section'])."'";
	$sql .= " AND `session`='". real_escape_string( $_SESSION['filter_id_session'] )."'";
	$query = mysqli_query($dbh, $sql) or die("Failed to execute concours query ".$sql.":".mysqli_error($dbh));
	
	$concours_ouverts = array();
	$postes_ouverts = array();
	$presidents_sousjurys = array();
	$tous_sous_jury = array();

	
	/* Ugly hack translated from former xml configuration system ... */
	while($result = mysqli_fetch_object($query))
	{
		$code = $result->code;
		$concours_ouverts[$code] = $result->intitule;
		$postes_ouverts[$code] = $result->postes;
		
		/*
		$sous_jurys[$code][""]["nom"] = "";
		$sous_jurys[$code][""]["membres"] = array();
		$tous_sous_jury[$code] = array();
		
		for($i = 1; î <= 4 ; $i++)
		{
		if($result->$key != "")
		{
			$data = explode(";",$result->sousjury1);
			if(count($data) <= 3)
				throw new Exception("Invalid sous jury");
			$sous_jurys[$code][$data[0]]["nom"] = $data[1];
			$tous_sous_jury[$code] = $data[1];
			$sous_jurys[$code][$data[0]]["membres"] = array();
			$presidents_sousjurys[$data[0]] = $data[2];
			for($i = 2; $i < count($data); $i++)
				$sous_jurys[$code][$data[0]]["membres"][] = $data[$i];
		}
		}
		*/
	}
	
	
	$permission_levels = array(
		NIVEAU_PERMISSION_BASE => "rapporteur",
		NIVEAU_PERMISSION_BUREAU => "bureau",
		NIVEAU_PERMISSION_SECRETAIRE => "secrétaire",
		NIVEAU_PERMISSION_PRESIDENT => "président(e)",
		NIVEAU_PERMISSION_SUPER_UTILISATEUR => "admin"
	);
			
	/* initializes topics */
	$topics = get_topics();

	/** FILTERS **/
	$filtersReports = array(
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapports,'default_value' => "tous", 'default_name' => "Tous les types"),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' => "tous", 'default_name' => "Tous les grades"),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'avis' => array('name'=>"Avis Section" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			//'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			//'avis3' => array('name'=>"Avis Rapp 3" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "Tous les statuts"),
			//'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'genre' => array('name' =>"Genre", 'liste' => $genreCandidat, 'default_value' => "", 'default_name' => "Tous les genre"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1)
	);

	
	$conc = array("CR"=>"tous CR","DR"=>"tous DR");
	foreach($concours_ouverts as $code => $data)
		$conc[strval($code)] = $data;
	
	$filtersConcours = array(
			'avis' => array('name'=>"Avis" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			//'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			//'avis3' => array('name'=>"Avis Rapp 3" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "Tous les statuts"),
			//'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			//'labo2' => array('name'=>"Labo2" , 'default_value' => "tous", 'default_name' => ""),
			'genre' => array('name' =>"Genre", 'liste' => $genreCandidat, 'default_value' => "", 'default_name' => "Tous les genre"),
			'concours' => array('name'=>"Concours" , 'liste' => $conc, 'default_value' => "tous", 'default_name' => ""),
			'sousjury' => array('name'=>"Sous-jury" , 'liste' => $tous_sous_jury, 'default_value' => "tous", 'default_name' => ""),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => ""),
			'rapporteur2' => array('name'=>"Rapporteur2" , 'default_value' =>"tous", 'default_name' => ""),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapportsConcours,'default_value' => "tous", 'default_name' => ""),
			'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
			'theseloc' => array('name'=>"TheseLoc" , 'liste' => $theseslocs, 'default_value' => "tous", 'default_name' => "Toutes les locs"),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);
	
	$filtersAll = array_merge($filtersReports, $filtersConcours);
		
	$csv_composite_fields = array(
			'titrenomprenom' => array('','nom','prenom') ,
			 'nomprenom' => array('nom','prenom'),
	);
	
	$csv_preprocessing = array('nom' => 'normalizeName', 'prenom' => 'normalizeName','unit' => 'fromunittocode');
	
	$sgcn_keywords_to_eval_types = array(
			"cole th" => "Ecole",
			"Evaluation" => 'Evaluation-Vague',
			'Reconstitution' => 'Reconstitution',
			'Titularisation' => 'Titularisation',
			'promotion' => 'Promotion',
			'Changement de direction' => 'Changement-Directeur',
			'Changement de section' => 'Changement-section',
			'Expertise' => 'Expertise',
			"Renouvellement de GDR" =>  'Renouvellement',
			"Evaluation" => "",
			"Avis de pertinence sur un projet d'association au CNRS" =>'Association',
			"Avis de pertinence sur un renouvellement d'association au CNRS" => "Renouvellement",
			"Changement de direction d'unité" => "Changement-Directeur",
			"Renouvellement de GDR" => "Renouvellement",
			"Expertise" => "Expertise",
			"Rattachement" => "GeneriqueChercheur",
			"Suivi" => "GeneriqueChercheur",
			"Emeritat (renouvellement)" => "Emeritat-renouvellement",
			"Emeritat (1" => "Emeritat",
			"Renouvellement de mise" => "GeneriqueChercheur",
			"Accueil" => "GeneriqueChercheur"
	);
	
	$users_not_rapporteur = array('admin','yawn');
	
	$possible_type_labels = array("Type évaluation", "Type d\'évaluation", "type");
	
?>
