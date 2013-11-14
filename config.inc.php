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

	
	define("config_file","config/config.xml");
	define("signature_file","img/signature.jpg");
	define("config_file_save","config/config.sauv.xml");
	
	require_once("config.php");
	
	load_config(true);
	//save_config();
	
	$dossiers_candidats = get_config("people_files_root");

	//include_once(section_config_file);
	
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
			"theseAnnee",
			"grade_rapport",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"labo1",
			"labo2",
	);
	
	$fieldsTriConcours = array(
			"nom",
			"prenom",
			"grade_rapport",
			"concours",
			"sousjury",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"theme3",
			"labo1",
			"labo2",
			"labo3",
			"avis",
			"theseAnnee",
			"date"
	);
	
	$statutsRapports = array( 'vierge' => "Rapport vierge", 'prerapport'=>'Prérapport', 'editable' => "Editable", 'rapport'=>"Rapport", 'audition'=>"Audition", 'publie'=>"Rapport publié");
	
	
	$fieldsRapportAll = array(
		"statut" => "Statut rapport",
		"concours" => "Concours",
			"sousjury" => "Sous-jury",
			"ecole" => "Ecole",
		"nom" => "Nom",
		"prenom" => "Prénom",
		"unite" => "Unité",
		"type" => "Type",
		"grade_rapport" => "Grade (rapport)",
		"rapporteur" => "Rapporteur 1",
		"rapporteur2" => "Rapporteur 2",
			"avis" => "Avis Section",
			"avis1" => "Avis rapp. 1",
			"avis2" => "Avis rapp. 2",
			"avissousjury" => "Avis sur l'audition",
			"DU" => "Au titre de DU",
			"international" => "Au titre Mobilité internationale",
			"finalisationHDR" => "Au titre  finalisation HDR",
			"national" => "Au titre d'une mobilité nationale",
			"rapport" => "Rapport Section",
		"prerapport" => "Prérapport 1",
		"prerapport2" => "Prérapport 2",
			"anneesequivalence" => "Années d'équivalence",
		"production" => "Production<br/>scientifique",
		"avissousjury" => "Avis sur l'audition",
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
		"auteur" => "Auteur dernière modif",
		"date" => "Date modification",
		"id" => "Id",
			"id_session" => "Id session",
			"id_origine" => "Id origine",
	);
	
	/*
	$specialtr_fields = array("parcours","concourspresentes", "nom", "annee_recrutement", "prenom", "genre", "grade", "projetrecherche", "labo1","labo2","labo3","theme1","theme2","theme3", "theseLieu", "HDRAnnee", "theseAnnee","theseloc", "HDRLieu");
	$start_tr_fields = array("projetrecherche", "grade", "nom", "labo1","theme1", "theseAnnee", "productionResume");
	$end_tr_fields = array("concourspresentes", "annee_recrutement", "labo3","theme3", "genre", "HDRLieu");
	*/
	/*
	 * Les champs disponibles aux deux rapporteurs
	 * pour un rapport individuel
	 */
	$fieldsIndividual0 = array(
			"type",
			array(
			"rapporteur",
			"rapporteur2",),
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
			"prerapport",
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement",
	);

		/*
	 * Les champs disponibles au rapporteur 2
	*/
	$fieldsIndividual2 = array(
			"avis2",
			"prerapport2",
			"production2",
			"transfert2",
			"encadrement2",
			"responsabilites2",
			"mobilite2",
			"animation2",
			"rayonnement2",
	);
	
	/*
	 * Tous les champs d'un rapport individuel
	 */
	$fieldsIndividual = array_merge($fieldsIndividual0, $fieldsIndividual1, $fieldsIndividual2);
	
	$fieldsChercheursAll = array(
			array(
			"nom",
			"prenom",),array(
			"genre",
			"statut_individu"),
			array(
			"grade",
			"annee_recrutement",),
			array(
			"labo1",
			"theme1",
			"theme2",
			"theme3",),
			array(
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",),
			"fichiers",
			"rapports"
	);
	
	$fieldsRapportsIndividual = array(
			"nom",
			"prenom",
			"genre",
			"statut_individu",
			"annee_recrutement",
			"labo1",
			"theme1",
			"theme2",
			"theme3",
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
			"fichiers",
			"rapporteur",
			"rapporteur2",
			"statut",
			"unite",
			"grade",
			"grade_rapport",
			"avis",
			"rapport",
			"avis1",
			"prerapport",
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement",
			"avis2",
			"prerapport2",
			"production2",
			"transfert2",
			"encadrement2",
			"responsabilites2",
			"mobilite2",
			"animation2",
			"rayonnement2",
	);
	
	/*
	* Les champs disponibles aux deux rapporteurs
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat0 = array(
			"statut",
			"sousjury",
			"rapporteur",
			"rapporteur2",
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
			"prerapport",
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement"
	);

	/*
	 * Les champs disponibles au rapporteur 2
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat2 = array(
			"avis2",
			"prerapport2",
			"production2",
			"transfert2",
			"encadrement2",
			"responsabilites2",
			"mobilite2",
			"animation2",
			"rayonnement2"
	);
	
	
	$fieldsRapportsCandidat = array_merge($fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2);
	
	
	$fieldsIndividualAll = array(
			"anneecandidature" => "Année de candidature",
			"nom" => "Nom",
			"prenom" => "Prénom",
			"genre" => "Genre",
			"statut_individu" => "Statut",
			"grade" => "Grade (Individu)",
			"annee_recrutement" => "Date de recrutement",
			"labo1" => "Labo 1",
			"labo2" => "Labo 2",
			"labo3" => "Labo 3",
			"theme1" => "Theme 1",
			"theme2" => "Theme 2",
			"theme3" => "Theme 3",
			"theseAnnee" => "Année+mois thèse",
			"theseLieu" => "Lieu thèse",
			"theseloc" => "Loc thèse",
			"HDRAnnee" => "Annee HDR",
			"HDRLieu" => "Lieu HDR",
			"productionResume" => "Production scientifique (pour rapport d'audition)",
			"projetrecherche" => "Projet recherche  (pour rapport d'audition)",
			"parcours" => "Parcours scientifique  (pour rapport d'audition)",
			"concourspresentes" => "Concours",
			"fichiers" => "Fichiers associés",
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
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
			"productionResume",
			"projetrecherche",
			"parcours",
			"concourspresentes"
	);
	
	$fieldsAll = array_merge($fieldsRapportAll, $fieldsIndividualAll, array("rapports" => "Autres rapports"));
	
	$fieldsCandidatAvantAudition = array(
			"nom",
			"prenom",
			"genre",
			"grade",
			"annee_recrutement",
			"fichiers",
			"rapports",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
			"concourspresentes"
	);

	$fieldsCandidatAuditionne = array_merge($fieldsCandidatAvantAudition, array("parcours","productionResume", "projetrecherche"));
	
	$fieldsCandidat = $fieldsCandidatAuditionne;
	
	$fieldsEquivalence = array(
			"statut",
			"rapporteur",
			"nom",
			"prenom",
			"genre",
			"grade",
			"avis",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
			"rapports",
			"rapport",
			"avis1",
			"prerapport",
	);
	

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
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
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
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement",
			"avis2",
			"prerapport2",
			"production2",
			"transfert2",
			"encadrement2",
			"responsabilites2",
			"mobilite2",
			"animation2",
			"rayonnement2",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"theseAnnee",
			"theseLieu",
			"theseloc",
			"HDRAnnee",
			"HDRLieu",
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
			"rapporteur2",),
			"unite",
			"rapports",
		"avis",
		"rapport",
	);

	$fieldsUnites1 = array(
			"avis1",
			"prerapport"
	);

	$fieldsUnites2 = array(
			"avis2",
			"prerapport2"
	);
	
	$fieldsUnites = array_merge($fieldsUnites0, $fieldsUnites1, $fieldsUnites2);
	
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
			"nom",
		"prenom",
		"unite",
		"avis",
		"rapport",
		"prerapport"
	);
	
	$fieldsEcoles = array(
			"statut",
			"rapporteur",
			"rapporteur2",
			"ecole",
			"nom",
			"prenom",
			"unite",
			"avis",
			"rapport",
			"prerapport"
	);
	
	$examples = array(
		"nom" => "",
		"prenom" => "",
		"grade" => "",
		"unite" => "",
		"concours" => "06/01",
		"ecole" => "Ecole de Pythagore",
		"type" => "Promotion",
		"rapporteur" => "Anne ONYME",
		"rapporteur2" => "Anne ONYME",
			"theseAnnee" => "1979",
			"theseLieu" => "Université de Turin",
			"HDRAnnee" => "1985",
			"HDRLieu" => "Université Bordeaux 1",
			"anneesequivalence" => "0",
		"prerapport" => "Candidat au fort potentiel, proche de la retraite ...",
		"anciennete_grade" => "~4 ans",
		"annee_recrutement" => "1999",
		"production" => "A-",
		"production" => "Nombreuses revues et conférences ...",
		"transfert" => "A",
		"transfert" => "Un brevet et quelques logiciels diffusés ...",
		"encadrement" => "B",
		"encadrement" => "Un étudiant en thèse, quelques stagiaires de M2 ...",
		"responsabilites" => "A+",
		"responsabilites" => "Membre du comité national ...",
		"mobilite" => "C",
		"mobilite" => "Peu de visites, en poste dans son labo de thèse ...",
		"animation" => "A+",
		"animation" => "Jongle et joue de l'harmonica tout en présidant son GDR ...",
		"rayonnement" => "B+",		
		"rayonnement" => "Travaux assez cités relativement aux pratiques de son domaine ...",		
		"rapport" => "",
		"avis" => "Réservé",
		"auteur" => "joe",
		"date" => "3/02/2013",
			"labo1" => "labo1",
			"labo2" => "",
			"labo3" => "",
			"theme1" => "theme1",
			"theme2" => "",
			"theme3" => "",
				
	);

	$empty_report = array(
			"statut" => "vierge",
			"type" => "Generique",
			"id_session" => "",
			"nom" => "",
			"prenom" => "",
			"grade_rapport" => "",
			"unite" => "",
			"ecole" => "",
			"concours" => "",
			"type" => "",
			"theseAnnee" => "",
			"theseLieu" => "",
			"HDRAnnee" => "",
			"HDRLieu" => "",
			"anneesequivalence" => "0",
			"rapporteur" => "",
			"rapporteur2" => "",
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
			"statut_individu" => "None",
			"anneecandidature" => "1970",
			"nom" => "",
			"prenom" => "",
			"genre" => "None",
			"statut_individu" => "candidat",
			"grade" => "None",
			"annee_recrutement" => "1970",
			"labo1" => "",
			"labo2" => "",
			"labo3" => "",
			"theme1" => "",
			"theme2" => "",
			"theme3" => "",
			"theseAnnee" => "",
			"theseLieu" => "",
			"theseloc" => "None",
			"HDRAnnee" => "",
			"HDRLieu" => "",
			"productionResume" => "",
			"projetrecherche" => "",
			"parcours" => "",
			"concourspresentes" => "",
			"fichiers" => "",
	);
				
	$sousjurys = get_config("sousjurys");
	
	$virgin_report_equivalence = 
			"La ".get_config("section_shortname")." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à un doctorat d'une université française.\n\n".
			"La ".get_config("section_shortname")." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à plus de 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"La qualification professionnelle du candidat n'est pas probante.\n\n".
			"Les travaux scientifiques présentés par le candidat ne sont pas probants.\n\n".
			"Le diplôme étranger dont le candidat est titulaire est insuffisant et n'équivaut pas à un doctorat français.\n\n".
			"L'expérience professionnelle acquise par le candidat n'équivaut pas en quantité et/ou en qualité à 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"Les titres et/ou travaux dont le candidat est titulaire est /sont insuffisants ou/et n'/ne sont/est pas convaincants.";

	$report_prototypes = array(
			'Equivalence' => array('rapport' => $virgin_report_equivalence)
	);

	$candidat_prototypes = array(
			'avissousjury' => "Un commentaire sur l'audition du candidat, à renseigner par le premier rapporteur après l'audition."
	);
	
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
			 		"us" => "USA",
			 		"as" => "Asie"
			 				 );
	
	$enumFields = array(
			"genre" => array(""=>"None", "homme" => "Homme","femme" => "Femme"),
			"theseloc" => $theseslocs,
			"statut_individu" => array(
					''=>'',
					'candidat' => 'Candidat',
					'auditionne' => 'Auditionné',
					'nonauditionne' => 'Non-auditionné',
					'admissible' => 'Admissible',
					'non-admissible'=> 'Non-admissible',
					'admis' => 'Admis',
					'non-admis'=> 'Non-admis',
					'stagiaire' => 'Stagiaire',
					'titulaire' => 'Titulaire'),
			"DU" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"international" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"finalisationHDR" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"national" => array(""=>"","oui" => "Oui","non"=>"Non"),
			"statut" => array('vierge','editable','prerapport','rapport','publie','supprime','audition')
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
			"avis" => "avis",
			"avis1" => "avis",
			"avis2" => "avis",
		"rapport" => "treslong",
		"prerapport" => "treslong",
		"prerapport2" => "treslong",
		"anciennete_grade" => "short",
		"theseAnnee" => "short",
		"theseLieu" => "short",
		"HDRAnnee" => "short",
		"HDRLieu" => "short",
		"annee_recrutement" => "short",
		"production" => "long",
		"avissousjury" => "long",
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
			"labo1" => "unit",
			"labo2" => "unit",
			"labo3" => "unit",
			"theme1" => "topic",
			"theme2" => "topic",
			"theme3" => "topic",
			"anneesequivalence" =>"short",
			"id" =>"short",
			"anneecandidature" => "short",
			"productionResume" => "long",
			"projetrecherche" => "long",
			"parcours" => "long",
			"fichiers" => "files",
			"rapports" => "rapports",
			"avissousjury" => "long",
			"statut" => "statut"
	);
	
	$nonEditableFieldsTypes = array('id','auteur','date');
	$nonVisibleFieldsTypes = array('id','auteur');
	$alwaysVisibleFieldsTypes = array('fichiers','rapports');
	
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
		'MedailleArgent' => 'Médaille d\'Argent'
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
			'MedailleArgent' => 'Méd Argent'
			
	);
	
	$typesRapportsUnites = array(
			'Changement-Directeur' => 'Changement de Directeur',
			'Changement-Directeur-Adjoint' => 'Changement de Directeur Adjoint',
			'Renouvellement' => 'Renouvellement',
			'Association' => 'Association',
			'Ecole' => 'Ecole Thematique',
			'Comite-Evaluation' => 'Comité d\'Evaluation',
			'Expertise' => 'Expertise',
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
			'Generique' => 'Générique'
	);
	
	$typesRapportsConcours = array(
		'Candidature' => 'Candidature',
		'Equivalence' => 'Equivalence',
	);
	
	$typesRapports = array_merge($typesRapportsChercheurs, $typesRapportsUnites, $typesRapportsConcours);

	$fieldsArrayCandidat = array($fieldsCandidat, $fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2);
	$fieldsArrayChercheur = array($fieldsChercheursAll, $fieldsIndividual0,$fieldsIndividual1,$fieldsIndividual2);
	$fieldsArrayUnite = array(array(), $fieldsUnites0, $fieldsUnites1, $fieldsUnites2);
	$fieldsArrayEquivalence =
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
	"theseAnnee",
	"theseLieu",
	"theseloc",
	"HDRAnnee",
	"HDRLieu",
	),
	 		array("rapporteur",	"rapport", 	"avis"),
	 		array("avis1", "prerapport"),
	 		array()
	 		);
	
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
					"theseAnnee",
					"theseLieu",
					"theseloc",
					"HDRAnnee",
					"HDRLieu"
			),
	array("rapporteur",	"avis",	"rapport"),
			array(
	"DU",
	"international",
	"finalisationHDR",
	"national",
	"unite",
	"avis1",
	"prerapport"
					),
			array()
);
	
	
	
	$typesRapportToFields =
	array(
			'Equivalence' => $fieldsArrayEquivalence,
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
			'Ecole' => $fieldsArrayUnite,
			'Comite-Evaluation' => $fieldsArrayUnite,
			'Generique' => $fieldsArrayUnite,
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
			"alerte" => "Alerte"
	);

	/* Pour les promos*/
	$avis_classement = array(""=>"", "adiscuter"=>"à discuter", "non"=>"non-classé", "oui"=>"Oui");
	

	/* Pour les concours*/
	$avis_candidature = array(""=>"", "adiscuter"=>"à discuter", 	'desistement' => 'Desistement', "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_short = array("tous" => "", "" =>"sans avis", 'desistement' => 'Desistement', "adiscuter"=>"à discuter", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "classe"=>"Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne", "desistement");
	
	$max_classement = 30;
	for($i = 1; $i <= $max_classement; $i++)
		$avis_candidature[strval($i)] = $avis_classement[strval($i)] = "<span  style=\"font-weight:bold;\" >$i</span>";
	
	/* Pour les SPE par exemple*/
	$avis_vide = array(""=>"");

	$avis_ie = array(
			""=>"",
			"favorable" => "Favorable",
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
		'Equivalence' => $avis_ie,
		'Affectation' => $avis_ternaire,
		'Reconstitution' => $avis_binaire,
		'Titularisation' => $avis_ternaire,
		'Delegation' => $avis_lettre,
		'Changement-Directeur' => $avis_pertinence,
		'Changement-Directeur-Adjoint' => $avis_pertinence,
		'Renouvellement' => $avis_pertinence,
		'Association' => $avis_pertinence,
		'Ecole' => $avis_ecoles,
		'Comite-Evaluation' => $avis_binaire,
		'Generique' => $avis_ternaire,
		'Expertise' => $avis_ternaire,
			'MedailleBronze' => $avis_classement,
			'MedailleArgent' => $avis_classement,
			
	);
	
	$tous_avis = array_merge($avis_eval,$avis_classement,$avis_candidature,$avis_ie,$avis_pertinence,$avis_ecoles,$avis_binaire);

	for($i = 1; $i <= $max_classement; $i++)
		$tous_avis[$i] = strval($i);
	
/* Definition des checkboxes à la fin de certains rapports*/
	
	/*Pour les evals à vague et mi vague*/
	$evalCheckboxes = array(
			"favorable" => "<span  style=\"font-weight:bold;\" >Avis favorable</span>	
	<small> (l’activité du chercheur est conforme à ses obligations statutaires)</small>",
			"differe" => "<span  style=\"font-weight:bold;\" >Avis différé</span>
<small> (l’évaluation est renvoyée à la session suivante en raison de l’insuffisance ou de l'absence d'éléments du dossier)</small>",
			"reserve" => "<span  style=\"font-weight:bold;\" >Avis réservé</span>
<small> (la section a identifié dans l’activité du chercheur un ou plusieurs éléments qui nécessitent un suivi spécifique)</small>",
			"alerte" => "<span  style=\"font-weight:bold;\" >Avis d'alerte</span>
<small> (la section exprime des inquiétudes sur l’évolution de l’activité du chercheur))</small>");

	/* Pour les renouvellements de gdr ou création d'unités*/
	$pertinenceCheckboxes = array(
			"tresfavorable" => "<span  style=\"font-weight:bold;\" >Avis très favorable</span>",
			"favorable" => "<span  style=\"font-weight:bold;\" >Avis favorable</span>",
			"defavorable" => "<span  style=\"font-weight:bold;\" >Avis défavorable</span>",
			"reserve" => "<span  style=\"font-weight:bold;\" >Avis réservé</span>",
			"sansavis" => "<span  style=\"font-weight:bold;\" >Pas d'avis</span>"
		);

	/* Pour les écoles thématiques*/
	$ecoleCheckboxes = array(
			"tresfavorable" => "<span  style=\"font-weight:bold;\" >Avis très favorable</span>",
			"favorable" => "<span  style=\"font-weight:bold;\" >Avis favorable</span>",
			"defavorable" => "<span  style=\"font-weight:bold;\" >Avis défavorable</span>"
	);
	
	$typesRapportsToCheckboxes = array(
	'Evaluation-Vague' => $evalCheckboxes,
	'Evaluation-MiVague' => $evalCheckboxes,
	'Renouvellement' => $pertinenceCheckboxes,
	'Association' => $pertinenceCheckboxes,
	'Ecole' => $ecoleCheckboxes
	);

	$typesRapportsToCheckboxesTitles = array(
			'Evaluation-Vague' => '<span  style=\"font-weight:bold;\" >EVALUATION A VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</span>',
			'Evaluation-MiVague' => '<span  style=\"font-weight:bold;\" >EVALUATION A MI-VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</span>',
			'Renouvellement' => '<span  style=\"font-weight:bold;\" >AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</span>',
			'Association' => '<span  style=\"font-weight:bold;\" >AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</span>',
			'Ecole' => '<span  style=\"font-weight:bold;\" >AVIS SUR L\'ECOLE</span>'
	);
	

	$typesRapportsToEnteteGauche = array(
			'Evaluation-Vague' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>évaluation à vague de chercheur</EM>',
			'Evaluation-MiVague' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>évaluation à mi-vague de chercheur</EM>',
			'Promotion' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Avancement de grade<br/><span  style=\"font-weight:bold;\" >Au grade de :</span>',
			'Changement-section' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Changement de section, évaluation permanente par une deuxième section</EM>',
			'Candidature' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Candidature au concours</EM>',
			'Affectation' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Affectation',
			'Titularisation' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Titularisation',
			'Reconstitution' => '<span  style=\"font-weight:bold;\" >Objet :</span><br/>Reconstitution de carrière',
			'Changement-Directeur' =>  '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Changement de directeur',
			'Changement-Directeur-Adjoint' =>  '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Changement de directeur adjoint',
			'Renouvellement' => '<span  style=\"font-weight:bold;\" >Objet de l’examen :</span> <EM>avis de pertinence d’association au CNRS : renouvellement</EM>',
			'Association' => '<span  style=\"font-weight:bold;\" >Objet de l’examen :</span> <EM>avis de pertinence d’association au CNRS : projet d\'association</EM>',
			'Ecole' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/> Ecole Thématique',
			'Comite-Evaluation' => '<span  style=\"font-weight:bold;\" >Objet de l’examen :</span> Comité d\'évaluation',
			'Generique' => '&nbsp;',
			'MedailleBronze' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Proposition de lauréat pour la médaille de bronze',
			'MedailleArgent' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/>Proposition de lauréat pour la médaille d\'argent',
			'Expertise' =>  '<span  style=\"font-weight:bold;\" >Objet de l’examen :</span> Expertise (projet ou suivi ou intégration équipe ou restructuration)',
			'Equivalence' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Equivalence titres et travaux</EM>',
			'Emeritat' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Eméritat (1ere demande)</EM>',
			'Emeritat-renouvellement' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Eméritat (renouvellement)</EM>',
			'' => ''
	);

	$enTetesDroit = array(
			'Individu' => '<span  style=\"font-weight:bold;\" >Nom, prénom et affectation du chercheur :</span><br/>',
			'Concours' => '<span  style=\"font-weight:bold;\" >Concours, classement, nom et prénom du candidat :</span><br/>',
			'Equivalence' => '<span  style=\"font-weight:bold;\" >Nom et prénom du candidat :</span><br/>',
			'Unite' => '<span  style=\"font-weight:bold;\" >Code, intitulé et nom<br/>du directeur de l’unité :</span><br/>',
			'Ecole' => '<span  style=\"font-weight:bold;\" >Nom de l\'école et du porteur de projet :</span><br/>',
			'PromotionDR' => '<span  style=\"font-weight:bold;\" >Classement, nom et unité :</span><br/>',
			'' => '&nbsp;'
			);
	
	$typesRapportsToEnteteDroit = array(
			'Evaluation-Vague' => 'Individu',
			'Evaluation-MiVague' => 'Individu',
			'MedailleBronze' => 'Individu',
			'MedailleArgent' => 'Individu',
			'Emeritat' => 'Individu',
			'Emeritat-renouvellement' => 'Individu',
			'Promotion' => 'Individu',
			'Changement-section' => 'Individu',
			'Candidature' => 'Concours',
			'Equivalence' => 'Equivalence',
			'Affectation' => 'Individu',
			'Titularisation' => 'Individu',
			'Reconstitution' => 'Individu',
			'Changement-Directeur' =>  'Unite',
			'Changement-Directeur-Adjoint' =>  'Unite',
			'Renouvellement' => 'Unite',
			'Association' => 'Unite',
			'Ecole' => 'Ecole',
			'Comite-Evaluation' => 'Unite',
			'Generique' => '',
			'Expertise' => 'Unite',
			'' => ''
	);
	
	
/* Definition des formaules standards à la fin de certains rapports*/
	
	$promotionFormula = array(
			'oui'=> 'La section donne un avis favorable à la demande de promotion.',
			'non'=> 'Le faible nombre de possibilités de promotions ne permet malheureusement pas à la Section 6 du Comité National de proposer ce chercheur à la Direction Générale du CNRS pour une promotion cette année.'
			);

	$equivalenceFormula = array(
			'favorable'=> 'La section donne un avis favorable à la demande d\'équivalence.',
			'defavorable'=> 'La section donne un avis défavorable à la demande d\'équivalence.'
	);
	
	$typesRapportsToFormula = array(
			
		'Promotion' => $promotionFormula,
		'Equivalence' =>$equivalenceFormula,
			'Titularisation' => array('favorable'=> 'La section donne un avis favorable à la titularisation.')
	);

	$typesRapportsToFormula = get_config("formules_standards");
	
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
			'None' => 'Pas de grade'
	);
	
	define("NIVEAU_PERMISSION_BASE", 0);
	define("NIVEAU_PERMISSION_BUREAU", 100);
	define("NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE", 500);
	define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
	define("NIVEAU_PERMISSION_INFINI", 10000000);
	
	$actions1 = array(
/*		'details' => array('left' => true, 'title' => "Détails", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),*/
		'edit' => array('left' => true, 'title' => "Modifier", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),
	);
	$actions2 = array(
			'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
			'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'),
			'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
			'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
	);
	$actions = array_merge($actions1, $actions2);
	
	$fieldsPermissions = array(
			"statut" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			"concours" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			"type" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			"rapporteur" => NIVEAU_PERMISSION_BUREAU,
			"rapporteur2" => NIVEAU_PERMISSION_BUREAU,
			"avis" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			"auteur" => NIVEAU_PERMISSION_INFINI,
			"date" => NIVEAU_PERMISSION_INFINI,
			"id" => NIVEAU_PERMISSION_INFINI,
			"id_session" => NIVEAU_PERMISSION_INFINI,
			"id_origine" => NIVEAU_PERMISSION_INFINI,
			"fichiers" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
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
			"text" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/html2.xsl",
					"name" => "Texte",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			"pdf" => 	array(
					"mime" => "application/x-zip",
					"xsl" => "",
					"name" => "PDF (rapport final)",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			),
			"csv" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "CSV (un par dossier)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE
			),
			"csvsingle" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "CSV (un pour tous les dossiers)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE
			),
			"csvbureau" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "CSV (attribution rapporteurs)",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU
			),
			"releveconclusions" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "CSV (relevé conclusions)",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU
			),
				
			"html" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/html2.xsl",
					"name" => "Html (prévisualisation des rapports)",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			"jad" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "JAD",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			),
			"jadhtml" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "JAD - html",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			),
			"xml" => 	array(
					"mime" => "text/xml",
					"xsl" => "xslt/xmlidentity.xsl",
					"name" => "XML",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			
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
	
	
			$concours_ouverts = get_config("concours");
			$postes_ouverts = get_config("postes_ouverts");
			$presidents_sousjurys = get_config("presidents_sousjurys");
				
	
	$sous_jurys = get_config("sousjurys");
	$tous_sous_jury = array();
	foreach($sous_jurys as $code => $liste)
	{
		$tous_sous_jury = array_merge($tous_sous_jury, $liste);
		$sous_jurys[$code][""] = "";
	}
	
	$permission_levels = array(
		NIVEAU_PERMISSION_BASE => "rapporteur",
		NIVEAU_PERMISSION_BUREAU => "bureau",
		NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE => "secrétaire/président(e)",
		NIVEAU_PERMISSION_SUPER_UTILISATEUR => "super utilisateur",
	);
	
	if(!isset($_SESSION['current_session']))
		$_SESSION['current_session'] = "Automne 2012";
		
	$topics = get_config("topics");
	$topics[""] = "Aucun";
	
	$filtersReports = array(
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapports,'default_value' => "tous", 'default_name' => "Tous les types"),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' => "tous", 'default_name' => "Tous les grades"),
			'avis' => array('name'=>"Avis Section" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_sessions, 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "Tous les statuts"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);

	
	$filtersConcours = array(
			'avis' => array('name'=>"Avis" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_candidature_short, 'default_value' => "tous", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'labo2' => array('name'=>"Labo2" , 'default_value' => "tous", 'default_name' => ""),
			'labo3' => array('name'=>"Labo3" , 'default_value' => "tous", 'default_name' => ""),
			'concours' => array('name'=>"Concours" , 'liste' => array_merge($concours_ouverts,array("CR"=>"tous CR","DR"=>"tous DR")), 'default_value' => "tous", 'default_name' => ""),
			'sousjury' => array('name'=>"Sous-jury" , 'liste' => $tous_sous_jury, 'default_value' => "tous", 'default_name' => ""),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => ""),
			'rapporteur2' => array('name'=>"Rapporteur2" , 'default_value' =>"tous", 'default_name' => ""),
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' =>"tous", 'default_name' => "Tous les grades"),
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
			"Evaluation" => 'Evaluation-Vague',
			'Reconstitution' => 'Reconstitution',
			'Titularisation' => 'Titularisation',
			'promotion' => 'Promotion',
			'Changement de direction' => 'Changement-Directeur',
			'Changement de section' => 'Changement-section',
			'Expertise' => 'Expertise',
			"Renouvellement de GDR" =>  'Renouvellement',
			"Evaluation" => "",
	);
	
	$users_not_rapporteur = array('admin','yawn');
	
	$possible_type_labels = array("Type évaluation", "Type d\'évaluation", "type");
	
?>