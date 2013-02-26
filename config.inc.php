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
	save_config();
	

	//include_once(section_config_file);
	
	$fieldsSummary = array(
		"type",
		"rapporteur",
		"rapporteur2",
		"nom",
		"prenom",
		"grade",
			"avis",
			"theme1",
			"theme2",
			"theme3",
			"unite",
			"id"
	);
	
	$fieldsSummaryConcours = array(
			"type",
			"nom",
			"prenom",
			"concours",
			"sousjury",
			"grade",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"labo1",
			"labo2",
			"avis",
	);
	
	$fieldsTriConcours = array(
			"nom",
			"prenom",
			"grade",
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
			"date"
	);
	
	$statutsRapports = array( 'vierge' => "Rapport vierge", 'prerapport'=>'Prérapport', 'rapport'=>"Rapport", 'publie'=>"Rapport publié");
	$statutsRapportsPluriel = array( 'vierge' => "rapports vierges", 'prerapport'=>'prérapports', 'rapport'=>"rapports", 'publie'=>"rapport publiés");
	
	
	$fieldsRapportAll = array(
		"statut" => "Statut rapport",
		"concours" => "Concours",
			"sousjury" => "Sous-jury",
			"ecole" => "Ecole",
		"nom" => "Nom",
		"prenom" => "Prénom",
		"unite" => "Unité",
		"grade" => "Grade",
		"type" => "Type",
		"rapporteur" => "Rapporteur 1",
		"rapporteur2" => "Rapporteur 2",
			"avis" => "Avis Section",
			"avis1" => "Avis rapp. 1",
			"avis2" => "Avis rapp. 2",
			"avissousjury" => "Avis du sous-jury",
			"rapport" => "Rapport Section",
		"prerapport" => "Prérapport/remarques<br/>rapp 1.",
		"prerapport2" => "Prérapport/remarques<br/>rapp 2.",
			"anneesequivalence" => "Années d'équivalence",
		"production" => "Production<br/>scientifique",
		"avissousjury" => "Avis du sous-jury (resume succint pour rapport concours)",
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
	
	$specialtr_fields = array("parcours","concourspresentes", "nom", "annee_recrutement", "prenom", "genre", "grade", "projetrecherche", "labo1","labo2","labo3","theme1","theme2","theme3", "theseLieu", "HDRAnnee", "theseAnnee","theseloc", "HDRLieu");
	$start_tr_fields = array("projetrecherche", "grade", "nom", "labo1","theme1", "theseAnnee", "productionResume");
	$end_tr_fields = array("concourspresentes", "annee_recrutement", "labo3","theme3", "genre", "HDRLieu");
	
	/*
	 * Les champs disponibles aux deux rapporteurs
	 * pour un rapport individuel
	 */
	$fieldsIndividual0 = array(
			"rapporteur",
			"rapporteur2",
//			"nom",
//			"prenom",
			"statut",
			"unite",
			"grade",
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
	
	/*
	* Les champs disponibles aux deux rapporteurs
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat0 = array(
			"sousjury",
			"rapporteur",
			"rapporteur2",
			"avis",
			"rapport",
	);

	/*
	 * Les champs disponibles au rapporteur 1
	* pour un rapport candidat
	*/
	$fieldsRapportsCandidat1 = array(
			"rapporteur",
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
			"rapporteur2",
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
			"grade" => "Grade",
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
			"productionResume" => "Production scientifique (pour rapport concours)",
			"projetrecherche" => "Projet recherche  (pour rapport concours)",
			"parcours" => "Parcours scientifique  (pour rapport concours)",
			"concourspresentes" => "Concours",
			"fichiers" => "Fichiers associés",
	);

	$fieldsChercheursAll = array(
			"nom",
			"prenom",
			"genre",
			"statut_individu",
			"grade",
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
	
	$fieldsAll = array_merge($fieldsRapportAll, $fieldsIndividualAll);
	
	$fieldsCandidatAvantAudition = array(
			"nom",
			"prenom",
			"genre",
			"grade",
			"annee_recrutement",
			"fichiers",
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
			"projetrecherche",
			"parcours",
			"concourspresentes"
	);

	$fieldsCandidat = $fieldsCandidatAvantAudition;
	
	$fieldsEquivalence = array(
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
			"rapport",
			"prerapport",
	);
	
	$fieldsUnites0 = array(
			"rapporteur",
			"rapporteur2",
			"unite",
		"avis",
		"rapport",
	);

	$fieldsUnites1 = array(
			"prerapport1"
	);

	$fieldsUnites2 = array(
			"prerapport2"
	);
	
	$fieldsUnites = array_merge($fieldsUnites0, $fieldsUnites1, $fieldsUnites2);
	
	$fieldsUnitsDB = array(
			"code" => "Code",
			"nickname" => "Nom",
			"fullname" => "Nom complet",
			"directeur" => "Direction"
			);
	
	$fieldsGeneric = array (
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
			"grade" => "",
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
			'avissousjury' => 
			"[A editer en session]\n"
			."L'audition du candidat....\n"
			."Le candidat a une production scientifique de qualité ne lui permettant pas d'être classé.\n"
			."Le candidat a une production scientifique de qualité exceptionnelle.\n",
			'projetrecherche' =>
			"[A editer par les rapporteurs]\n"
			."Le projet de recherche du candidat s'intitule [] et porte sur [].\n",
			'parcours' =>
			 "[A editer par les rapporteurs]\n"
			."Situation actuelle\n"
			."Intitulé et lieu de thèse, des postdoc(s) et de(s) postes."
	);
	
	$mergeableTypes = array("short","treslong","long","short");
	$crashableTypes = array("auteur");
	
	$enumFields = array(
			"genre" => array("homme" => "Homme","femme" => "Femme"),
			 "theseloc" => array(
			 		"fr" => "France",
			 		"africa" => "Afrique",
			 		"southamerica" => "Amérique du Sud",
			 		"au" => "Australie",
			 		"other" => "Autres",
			 		"eu" => "Europe",
			 		"ru" => "Russie",
			 		"us" => "USA",
			 				 ),
			"statut_individu" => array(
					'candidat' => 'Candidat',
					'auditionne' => 'Auditionné',
					'nonauditionne' => 'Non-auditionné',
					'admissible' => 'Admissible',
					'non-admissible'=> 'Non-admissible',
					'admis' => 'Admis',
					'non-admis'=> 'Non-admis',
					'stagiaire' => 'Stagiaire',
					'titulaire' => 'Titulaire')
			);
	
	$fieldsTypes = array(
		"ecole" => "ecole",
		"concours" => "concours",
		"sousjury" => "sousjury",
		"concourspresentes" => "long",
		"nom" => "short",
		"prenom" => "short",
		"genre" => "enum",
			"statut_individu"=> "enum",
		"grade" => "grade",
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
			"production" => "long",
			"projetrecherche" => "long",
			"parcours" => "long",
			"fichiers" => "files",
			"avissousjury" => "long",
			"statut" => "statut"
	);
	
	$nonEditableFieldsTypes = array('id','auteur','date');
	$nonVisibleFieldsTypes = array('id','auteur');
	
	$typesRapportsChercheurs = array(
		'Evaluation-Vague' => 'Evaluation à Vague',
		'Evaluation-MiVague' => 'Evaluation à Mi-Vague',
		'Promotion' => 'Promotion',
		'Suivi-PostEvaluation' => 'Suivi Post-Evaluation',
		'Titularisation' => 'Titularisation',
		'Affectation' => 'Confirmation d\'Affectation',
		'Reconstitution' => 'Reconstitution de Carrière'
	);

	$typesRapportsUnites = array(
			'Changement-Directeur' => 'Changement de Directeur',
			'Changement-Directeur-Adjoint' => 'Changement de Directeur Adjoint',
			'Renouvellement' => 'Renouvellement',
			'Association' => 'Association',
			'Ecole' => 'Ecole Thematique',
			'Comite-Evaluation' => 'Comité d\'Evaluation',
			'Generique' => 'Générique'
	);

	$typesRapportsConcours = array(
		'Candidature' => 'Candidature',
		'Equivalence' => 'Equivalence',
	);
	
	$typesRapports = array_merge($typesRapportsChercheurs, $typesRapportsUnites, $typesRapportsConcours);
		
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
	$avis_candidature = array(""=>"", "adiscuter"=>"à discuter", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_short = array("tous" => "", "" =>"sans avis", "adiscuter"=>"à discuter", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "classe"=>"Classé", "nonconcur"=>"Non Admis à Concourir");
	$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne");
	
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
	
	/* Types d'avis disponibles dans l'interface pour chaque type de rapport*/
	$typesRapportToAvis = array(
		'Evaluation-Vague' => $avis_eval,
		'Evaluation-MiVague' => $avis_eval,
		'Promotion' => $avis_classement,
		'Candidature' => $avis_candidature,
		'Equivalence' => $avis_ie,
		'Suivi-PostEvaluation' => $avis_vide,
		'Affectation' => $avis_binaire,
		'Reconstitution' => $avis_vide,
		'Titularisation' => $avis_binaire,
		'Changement-Directeur' => $avis_pertinence,
		'Changement-Directeur-Adjoint' => $avis_pertinence,
		'Renouvellement' => $avis_pertinence,
		'Association' => $avis_pertinence,
		'Ecole' => $avis_ecoles,
		'Comite-Evaluation' => $avis_binaire,
		'Generique' => $avis_vide,
		);
	
	$tous_avis = array_merge($avis_eval,$avis_classement,$avis_candidature,$avis_ie,$avis_pertinence,$avis_ecoles,$avis_binaire);

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
			'Candidature' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Candidature au concours</EM>',
			'Suivi-PostEvaluation' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Suivi post-évaluation</EM>',
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
			'Equivalence' => '<span  style=\"font-weight:bold;\" >Objet de l’évaluation :</span><br/><EM>Equivalence titres et travaux</EM>',
						
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
			'Promotion' => 'Individu',
			'Candidature' => 'Concours',
			'Equivalence' => 'Equivalence',
			'Suivi-PostEvaluation' => 'Individu',
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
		'Emerite' => 'Emerite',
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
		'edit' => array('left' => true, 'title' => "Modifier", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/edit-icon-24px.png'),
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
			"rapport" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
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
			"html" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/html2.xsl",
					"name" => "Html",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			"xml" => 	array(
					"mime" => "text/xml",
					"xsl" => "xslt/xmlidentity.xsl",
					"name" => "XML",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),
			"pdf" => 	array(
					"mime" => "application/x-zip",
					"xsl" => "",
					"name" => "PDF",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE,
			),
			"zip" => 	array(
					"mime" => "application/x-zip",
					"xsl" => "",
					"name" => "ZIP",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			),
			"csv" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "CSV",
					"permissionlevel" => NIVEAU_PERMISSION_BASE
			),
			"singlecsv" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "SingleCSV",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			),
			"jad" => 	array(
					"mime" => "application/x-text",
					"xsl" => "",
					"name" => "JAD",
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			)
			);

			$typeImports = array(
					"xml" => 	array(
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
				
	
	$sous_jurys[""] = array();
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
		
	$filtersReports = array(
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' => "tous", 'default_name' => "Tous les grades"),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "Tous les statuts"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapports,'default_value' => "tous", 'default_name' => "Tous les types"),
			'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);

	$topics = get_config("topics");
	$topics[""] = "Aucun";
	
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
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);
	
	$filtersAll = array_merge($filtersReports, $filtersConcours);
		
	$csv_composite_fields = array(
			'titrenomprenom' => array('','nom','prenom') ,
			 'nomprenom' => array('nom','prenom'),
	);
	
	$csv_preprocessing = array('nom' => 'normalizeName', 'prenom' => 'normalizeName','unit' => 'fromunittocode');
	
	$users_not_rapporteur = array('admin','yawn');
?>