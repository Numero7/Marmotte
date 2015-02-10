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
	define("signature_blanche","img/signature_blanche.jpg");
	//	define("config_file_save","config/config.sauv.xml");

	global $rootdir;
	$dossier_temp = $rootdir."./tmp/".$_SESSION['filter_section']."/";
	$dossier_stockage = $rootdir."./storage/".$_SESSION['filter_section']."/";
	

	$rubriques_supplementaires = array(
			"individus" => array("rubriques_individus","Info","chercheur"),
			"candidats" => array("rubriques_candidats", "Info","candidat"),
			"concours" => array("rubriques_concours", "Generic","rapport concours"),
			"chercheurs" => array("rubriques_chercheurs", "Generic","rapport chercheur"),
			"unites" => array("rubriques_unites", "Generic","rapport unite")
	);
	
	$add_rubriques_people = get_rubriques("individus");
	$add_rubriques_candidats = get_rubriques("candidats");
	$add_rubriques_concours = get_rubriques("concours");
	$add_rubriques_chercheurs = get_rubriques("chercheurs");
	$add_rubriques_unites = get_rubriques("unites");
		
	$fieldsSummary = array(
		"type",
		"rapporteur",
		"rapporteur2",
		"rapporteur3",
			"nom",
		"prenom",
		"grade_rapport",
			"avis",
			"avis1",
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
			"avis1",
			"avis2",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"labo1",
			"labo2",
			"diploma",
			"grade_rapport"
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
			'' => "",
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
		"rapporteur" => "Rapp. 1",
		"rapporteur2" => "Rapp. 2",
		"rapporteur3" => "Rapp. 3",
			"avis" => "Avis Section",
			"avis1" => "Avis rapp. 1",
			"avis2" => "Avis rapp. 2",
			"avis3" => "Avis rapp. 3",
			"avissousjury" => "Avis sur l'audition",
/*			"DU" => "Au titre de DU",
			"international" => "Au titre Mobilité internationale",
			"finalisationHDR" => "Au titre  finalisation HDR",
			"national" => "Au titre d'une mobilité nationale",*/
			"rapport" => "Rapport Section",
		"prerapport" => "Prérapport 1",
		"prerapport2" => "Prérapport 2",
		"prerapport3" => "Prérapport 3",
		"auteur" => "Auteur dernière modif",
		"date" => "Date modification",
		"id" => "Id",
			"id_session" => "Id session",
			"id_origine" => "Id origine",
	);	
	
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

	$fieldsIndividual1 = array("avis1","prerapport");
	$fieldsIndividual2 = array("avis2","prerapport2");
	$fieldsIndividual3 = array("avis3","prerapport3");
	
	foreach($add_rubriques_chercheurs as $index => $rubrique)
	{
		$fieldsIndividual1[] = "Generic".(3*$index);
		$fieldsIndividual2[] = "Generic".(3*$index+1);
		$fieldsIndividual3[] = "Generic".(3*$index+2);
	}
	
	
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
		
	foreach($add_rubriques_people as $code => $rubrique)
		$fieldsChercheursAll[] = "Info".$code;
	
	/*
	* Les champs disponibles aux deux rapporteurs
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat0 = array(
			"type",
			"concours",
			"sousjury",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"avis",
			"avissousjury",
			"rapport"
	);

	$fieldsRapportsIE0 = array(
			"type",
			"grade_rapport",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"avis",
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
		
	foreach($add_rubriques_concours as $index => $rubrique)
	{
		$fieldsRapportsCandidat1[] = "Generic".(3*$index);
		$fieldsRapportsCandidat2[] = "Generic".(3*$index+1);
		$fieldsRapportsCandidat3[] = "Generic".(3*$index+2);
	}
	
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
			"conflits" => "Conflits",
			"fichiers"=> "Fichiers",
			"birth" => "Date naissance",
			"diploma" => "Date diplôme"
	);

	
	foreach($add_rubriques_people as $index => $rubrique)
		$fieldsIndividualAll["Info".$index] = $rubrique;
	

	$mandatory_edit_fields=
	array('id','nom','prenom'
	);
	
	$mandatory_export_fields= 
	array('id','nom','prenom','genre','type','concours',
			"grade",
			"annee_recrutement",
			"diploma",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"audition",
			"concourspresentes"
	);

	/* dirty */
	for($i = 0; $i <= 30; $i++)
	{
		$fieldsIndividualAll["Info".$i] = "Info".$i;
		$fieldsRapportAll["Generic".$i] = "Generic".$i;
	}
	
	$fieldsAll = array_merge($fieldsRapportAll, $fieldsIndividualAll, array("rapports" => "Autres rapports"));

	$fieldsCandidatAvantAudition = array(
			"nom",
			"prenom",
			"genre",
			"grade",
			"annee_recrutement",
			"diploma",
			"birth",
			"conflits",
			"fichiers",
			"rapports",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"concourspresentes"
	);
	
	foreach($add_rubriques_candidats as $index => $rubrique)
		$fieldsCandidatAvantAudition[] = "Info".$index;
	
	$fieldsCandidatAuditionne = array_merge($fieldsCandidatAvantAudition, array("audition"));
	$fieldsCandidat = $fieldsCandidatAuditionne;

	$fieldsDelegation = array(
			"statut",
			"rapporteur",
			"rapporteur2",
			"rapporteur3",
			"nom",
			"prenom",
/*			"DU",
			"international",
			"finalisationHDR",
			"national",
			*/
			"unite",
			"rapports",
			"avis1",
			"prerapport",
			"prerapport2",
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
	
	foreach($add_rubriques_unites as $index => $rubrique)
	{
		$fieldsUnites1[] = "Generic".(3*$index);
		$fieldsUnites2[] = "Generic".(3*$index+1);
		$fieldsUnites3[] = "Generic".(3*$index+2);
	}
	
	$fieldsUnitesExtra = array(
			'Expertise' => array('ecole'),
			'Generique' => array('ecole'),
				'GeneriqueChercheur' => array('ecole'),
			'Changement-section' => array('ecole')
			
	);
	
	$fieldsUnites = array_merge($fieldsUnites0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	$fieldsEcoles = array_merge($fieldsEcoles0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	
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
			"annee_recrutement" => "",
			"birth" => "",
			"diploma" => "",
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
			'Candidature' => 'Candidature',
			'Equivalence' => 'IE'
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
			"genre" => array(""=>"", "homme" => "Homme","femme" => "Femme"),
			"theseloc" => $theseslocs,
			/*
			"DU" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"international" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"finalisationHDR" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"national" => array(""=>"","oui" => "Oui","non"=>"Non"),
			*/
			"statut" => array('doubleaveugle','prerapport','rapport','publie','supprime','audition')
			);
	
	$fieldsTypes = array(
		"ecole" => "ecole",
		"concours" => "concours",
		"sousjury" => "sousjury",
		"concourspresentes" => "short",
		"nom" => "short",
		"prenom" => "short",
		"genre" => "enum",
		/*	"DU" => "enum",
			"international" => "enum",
			"finalisationHDR" => "enum",
			"national" => "enum",
			*/
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
		"avissousjury" => "avis",
			"auteur" => "short",
		"date" => "short",
		"conflits" => "short",
			"birth" => "short",
			"diploma" => "short",
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
			"statut" => "statut",
	);
	
	for($i = 0 ; $i < 30; $i++)
	{
		$fieldsTypes["Generic".$i] = "long";
		$fieldsTypes["Info".$i] = "short";
	}
	
	$nonEditableFieldsTypes = array('id','auteur','date');
	$nonVisibleFieldsTypes = array('id','auteur');
	$alwaysVisibleFieldsTypes = array('fichiers','rapports');
	
	$fieldsArrayCandidat = array($fieldsCandidat, $fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
	$fieldsArrayIE = array($fieldsCandidatAvantAudition, $fieldsRapportsIE0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
	$fieldsArrayChercheur = array($fieldsChercheursAll, $fieldsIndividual0,$fieldsIndividual1,$fieldsIndividual2,$fieldsIndividual3);
	$fieldsArrayUnite = array(array(), $fieldsUnites0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	$fieldsArrayEcole = array(array(), $fieldsEcoles0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
	
	$fieldsPeople = array_merge($fieldsCandidat, $fieldsChercheursAll);
	
	$fieldsArrayDelegation = $fieldsArrayChercheur;
	
	
	
	$typesRapportToFields =
	array(
			'Delegation' => $fieldsArrayDelegation,
		'Candidature' => 	$fieldsArrayCandidat,
		'Equivalence' => 	$fieldsArrayIE,
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
	
	$copies = array(
			"Nom" => "nom",
			"Nom d\'usage" => "nom",
			"NOMUSUEL" => "nom",
			"Prénom" => "prenom",
			"PRENOM" => "prenom",
			"GRAD_CONC" => "grade_rapport",
			"Grade" => "grade",
			"Directeur" => "directeur",
			"Affectation #1" => "unite",
			"Code Unité" => "unite",
			"Code unité" => "unite",
			"Code Colloque" => "unite",
			"Affectation #1" => "unite",
			"Titre" => "nom",
			"Responsable principal" => "prenom",
			"PUBCONC" => "concours",
			"CONCOURS" => "concours",
			"Rapporteur1" => "rapporteur",
			"Rapporteur2" => "rapporteur2",
			"Rapporteur3" => "rapporteur3",
			"Rapporteur 1" => "rapporteur",
			"Rapporteur 2" => "rapporteur2",
			"Rapporteur 3" => "rapporteur3",
			"DATNAISS" => "birth",
			"DATOBTDIP" => "diploma",
			"DATENOMIN" => "annee_recrutement"
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
	$avis_candidature_short =
	 array(
	 		 "" =>"Sans avis",
	 		 'desistement' => 'Desistement',
	 		 "adiscuter"=>"A discuter",
	 		 "nonauditionne"=>"Non-auditionné",
	 		 "oral"=>"Auditionné",
	 		 "nonclasse"=>"Non-classé",
	 		 "classe"=>"Classé", 
	 		"nonconcur"=>"Non-admis à concourir"
	 		);
	$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne", "desistement");
	
	$max_classement = 30;
	for($i = 1; $i <= $max_classement; $i++)
		$avis_candidature_short[strval($i)] = $avis_classement[strval($i)] = "<B>$i</B>";
	
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
		'Candidature' => $avis_candidature_short,
		'Equivalence' => $avis_ie,
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
	
	$tous_avis = array_merge($avis_eval,$avis_classement,$avis_candidature_short,$avis_ie,$avis_pertinence,$avis_ecoles,$avis_binaire);

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
	
	/* permissions levels for actions */
	$actions_level = array(
			"delete_units" =>NIVEAU_PERMISSION_SECRETAIRE, 
			"set_rapporteur" => NIVEAU_PERMISSION_BUREAU,
			"change_role" => NIVEAU_PERMISSION_BASE,
			"migrate" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
			"removerubrique" => NIVEAU_PERMISSION_SECRETAIRE,
			"addrubrique" => NIVEAU_PERMISSION_SECRETAIRE,
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
			"newpwd" => NIVEAU_PERMISSION_BASE,
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
		'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
		'export&amp;type=text' => array('left' => true, 'title' => "Exporter", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/zip-icon-24px.png')
	);
	$actions2 = array(
//			'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_SECRETAIRE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
			'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_SECRETAIRE, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'),
//			'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
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
	$tous_sous_jury = array();

	
	/* Ugly hack translated from former xml configuration system ... */
	
	while($result = mysqli_fetch_object($query))
	{
		$code = $result->code;
		$concours_ouverts[$code] = $result->intitule;
		$postes_ouverts[$code] = $result->postes;
		$tous_sous_jury[$code] = array();
		for($i = 1 ; $i <= 4; $i++)
		{
			$suff = "sousjury".$i;
			$suffp = "president".$i;
			$suffm = "membressj".$i;
			if($result->$suff != "")
			{
				$tous_sous_jury[$code][$result->$suff] = array("president"=> $result->$suffp, "membres" => explode(";", $result->$suffm));
			}
		}
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
	foreach($topics as $key => $value)
		$topics[$key] = $key . " - " . $value;

	/** FILTERS **/
	$filtersReports = array(
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapports,'default_value' => "tous", 'default_name' => "Tous les types"),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' => "tous", 'default_name' => "Tous les grades"),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => "tous"),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => "tous"),
			'avis' => array('name'=>"Avis Section" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			//'avis3' => array('name'=>"Avis Rapp 3" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "Tous"),
			//'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'genre' => array('name' =>"Genre", 'liste' => $genreCandidat, 'default_value' => "tous", 'default_name' => "Tous"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1)
	);

	
	
	$liste_sous_jurys = array();
	foreach($tous_sous_jury as $conc => $sousjurys)
		foreach($sousjurys as $code => $president)
			$liste_sous_jurys[$code] = $conc." - ".$code;

	$tous_concours = array("CR"=>"tous CR","DR"=>"tous DR");
	foreach($concours_ouverts as $code => $data)
		$tous_concours[strval($code)] = $data;
	
	$filtersConcours = array(
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapportsConcours,'default_value' => "tous", 'default_name' => ""),
			'concours' => array('name'=>"Concours" , 'liste' => $tous_concours, 'default_value' => "tous", 'default_name' => ""),
			'sousjury' => array('name'=>"Sous-jury" , 'liste' => $liste_sous_jurys, 'default_value' => "tous", 'default_name' => ""),
			'avis' => array('name'=>"Avis section" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis rapporteur 1" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'avis2' => array('name'=>"Avis rapporteur 2" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'rapporteur' => array('name'=>"Rapporteur 1" , 'default_value' =>"tous", 'default_name' => ""),
			'rapporteur2' => array('name'=>"Rapporteur 2" , 'default_value' =>"tous", 'default_name' => ""),
			'genre' => array('name' =>"Genre", 'liste' => $genreCandidat, 'default_value' => "tous", 'default_name' => ""),
			//'avis3' => array('name'=>"Avis Rapp 3" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => ""),
			//'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			//'labo2' => array('name'=>"Labo2" , 'default_value' => "tous", 'default_name' => ""),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
			//'theseloc' => array('name'=>"TheseLoc" , 'liste' => $theseslocs, 'default_value' => "tous", 'default_name' => "Toutes les locs"),
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