<?php


	require_once("config/configDB.inc.php");

	
	define("config_file",$rootdir."config/config.xml");
	define("signature_file",$rootdir."img/signature.jpg");
	define("config_file_save",$rootdir."config/config.sauv.xml");
	
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
		"unite",
		"date",
			"id"
	);
	
	$fieldsSummaryCandidates = array(
			"type",
			"nom",
			"prenom",
			"grade",
			"concours",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"theme3",
			"labo1",
			"labo2",
			"labo3",
			"avis",
			"id"
	);

	$fieldsTriCandidates = array(
			"nom",
			"prenom",
			"grade",
			"concours",
			"rapporteur",
			"rapporteur2",
			"theme1",
			"theme2",
			"theme3",
			"labo1",
			"labo2",
			"labo3",
			"avis"
	);
	
	$statutsRapports = array( 'vierge' => "Rapport vierge", 'prerapport'=>'Prérapport', 'rapport'=>"Rapport", 'publie'=>"Rapport publié");
	$statutsRapportsPluriel = array( 'vierge' => "rapports vierges", 'prerapport'=>'prérapports', 'rapport'=>"rapports", 'publie'=>"rapport publiés");
	
	
	$fieldsAll = array(
		"statut" => "Statut",
		"concours" => "Concours",
		"ecole" => "Ecole",
		"nom" => "Nom",
		"prenom" => "Prénom",
		"unite" => "Unité",
		"grade" => "Grade",
		"anciennete_grade" => "Ancienneté dans grade",
		"type" => "Type",
		"rapporteur" => "Rapporteur 1",
		"rapporteur2" => "Rapporteur 2",
			"avis" => "Avis Section",
			"avis1" => "Avis rapp. 1",
			"avis2" => "Avis rapp. 2",
			"rapport" => "Rapport Section",
		"prerapport" => "Prérapport/remarques rapp 1.",
		"prerapport2" => "Prérapport/remarques rapp 2.",
			"date_recrutement" => "Date de recrutement",
		"labo1" => "Labo 1",
		"labo2" => "Labo 2",
		"labo3" => "Labo 3",
			"theme1" => "Theme 1",
			"theme2" => "Theme 2",
			"theme3" => "Theme 3",
			"theseAnnee" => "Année+mois thèse",
			"theseLieu" => "Lieu thèse",
			"HDRAnnee" => "Annee HDR",
			"HDRLieu" => "Lieu HDR",
			"anneesequivalence" => "Années d'équivalence",
		"production" => "Production scientifique",
		"avissousjury" => "Avis du sous-jury (resume succint pour rapport concours)",
			"production2" => "Production scientifique (rapp. 2)",
		"transfert" => "Transfert et valorisation",
		"transfert2" => "Transfert et valorisation (rapp. 2)",
		"encadrement" => "Encadrement",
		"encadrement2" => "Encadrement (rapp. 2)",
		"responsabilites" => "Responsabilités collectives",
		"responsabilites2" => "Responsabilités collectives (rapp. 2)",
		"mobilite" => "Mobilité",
		"mobilite2" => "Mobilité (rapp. 2)",
		"animation" => "Animation scientifique",
		"animation2" => "Animation scientifique (rapp. 2)",
		"rayonnement" => "Rayonnement",		
		"rayonnement2" => "Rayonnement (rapp. 2)",		
		"auteur" => "Auteur Dernière(s) modif(s)",
		"date" => "Date modification",
		"id" => "Id",
			"id_session" => "Id session",
			"id_origine" => "Id origine",
			"anneecandidature" => "Année de candidature",
			"production" => "Production scientifique",
			"projetrecherche" => "Projet recherche (resume succint pour rapport concours)",
			"parcours" => "Parcours scientifique (resume succint pour rapport concours)",
			"concours" => "Concours",
			"fichiers" => "Fichiers associés",
			"cle" => "cle"
	);
	

	$specialtr_fields = array("parcours","concours", "nom", "date_recrutement", "prenom", "grade", "projetrecherche", "labo1","labo2","labo3","theme1","theme2","theme3", "theseLieu", "HDRAnnee", "theseAnnee","HDRLieu");
	$start_tr_fields = array("projetrecherche", "grade", "nom", "labo1","theme1", "theseAnnee", "productionResume");
	$end_tr_fields = array("concours", "date_recrutement", "labo3","theme3", "prenom", "HDRLieu");
	
	$fieldsIndividual0 = array(
			"rapporteur",
			"rapporteur2",
			"nom",
			"prenom",
			"unite",
			"grade",
			"anciennete_grade",
			"avis",
			"theme1",
			"theme2",
			"theme3",
			"rapport",
			"date_recrutement",
	);

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
	
	$fieldsIndividual = array_merge($fieldsIndividual0, $fieldsIndividual1, $fieldsIndividual2);
	
	$fieldsRapportsCandidat0 = array(
			"rapporteur",
			"rapporteur2",
			"avis",
			"rapport",
	);

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
	
	$fieldsCandidatAll = array(
			"anneecandidature" => "Année de candidature",
			"nom" => "Nom",
			"prenom" => "Prénom",
			"grade" => "Grade",
			"date_recrutement" => "Date de recrutement",
			"labo1" => "Labo 1",
			"labo2" => "Labo 2",
			"labo3" => "Labo 3",
			"theme1" => "Theme 1",
			"theme2" => "Theme 2",
			"theme3" => "Theme 3",
			"theseAnnee" => "Année+mois thèse",
			"theseLieu" => "Lieu thèse",
			"HDRAnnee" => "Annee HDR",
			"HDRLieu" => "Lieu HDR",
			"productionResume" => "Production scientifique (pour rapport concours)",
			"projetrecherche" => "Projet recherche  (pour rapport concours)",
			"parcours" => "Parcours scientifique  (pour rapport concours)",
			"concours" => "Concours",
			"fichiers" => "Fichiers associés",
			"cle" => "cle",
			"avissousjury" => "Avis du sous-jury",
	);

	$fieldsCandidatAvantAudition = array(
			"nom",
			"prenom",
			"grade",
			"date_recrutement",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"theseAnnee",
			"theseLieu",
			"HDRAnnee",
			"HDRLieu",
			"projetrecherche",
			"parcours",
			"concours",
			"fichiers"
	);

	$fieldsCandidat = array_merge($fieldsCandidatAvantAudition, array("avissousjury"));
	
	$fieldsEquivalence = array(
			"rapporteur",
			"nom",
			"prenom",
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
			"HDRAnnee",
			"HDRLieu",
			"rapport",
			"prerapport",
	);
	
	$fieldsUnites = array(
			"rapporteur",
			"unite",
		"avis",
		"rapport",
		"prerapport"
	);

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
		"date_recrutement" => "1999",
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
		"date_recrutement" => "",
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
			"theme3" => ""
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
	
	$mergeableTypes = array("concours","short","treslong","long","short");
	
	$fieldsTypes = array(
		"ecole" => "ecole",
		"concours" => "concours",
		"nom" => "short",
		"prenom" => "short",
		"grade" => "grade",
		"unite" => "unit",
		"type" => "short",
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
		"date_recrutement" => "short",
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
		"date" => "date",
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
			"concours" => "long",
			"fichiers" => "files",
			"avissousjury" => "long"
	);
	
	$typesRapportsIndividuels = array(
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
	
	$typesRapports = array_merge($typesRapportsIndividuels, $typesRapportsUnites, $typesRapportsConcours);
		
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
	$avis_classement = array(""=>"", "adiscuter"=>"à discuter", "non"=>"non-classé", "oui"=>"Oui", "1"=>"<span  style=\"font-weight:bold;\" >1</span>", "2"=>"<span  style=\"font-weight:bold;\" >2</span>", "3"=>"<span  style=\"font-weight:bold;\" >3</span>", "4"=>"<span  style=\"font-weight:bold;\" >4</span>",
			 "5"=>"<span  style=\"font-weight:bold;\" >5</span>", "6"=>"<span  style=\"font-weight:bold;\" >6</span>", "7"=>"<span  style=\"font-weight:bold;\" >7</span>" , "8"=>"<span  style=\"font-weight:bold;\" >8</span>", "9"=>"<span  style=\"font-weight:bold;\" >9</span>"
			, "10"=>"<span  style=\"font-weight:bold;\" >10</span>", "11"=>"<span  style=\"font-weight:bold;\" >11</span>", "12"=>"<span  style=\"font-weight:bold;\" >12</span>", "13"=>"<span  style=\"font-weight:bold;\" >13</span>", "14"=>"<span  style=\"font-weight:bold;\" >14</span>", "15"=>"<span  style=\"font-weight:bold;\" >15</span>", "16"=>"<span  style=\"font-weight:bold;\" >16</span>",
			 "17"=>"<span  style=\"font-weight:bold;\" >17</span>", "18"=>"<span  style=\"font-weight:bold;\" >18</span>", "19"=>"<span  style=\"font-weight:bold;\" >19</span>",
			 "20"=>"<span  style=\"font-weight:bold;\" >20</span>", "21"=>"<span  style=\"font-weight:bold;\" >21</span>");

	/* Pour les concours*/
	$avis_candidature = array(""=>"", "adiscuter"=>"à discuter", "nonauditionne"=>"Non Auditionné", "oral"=>"Auditionné", "nonclasse"=>"Non Classé", "1"=>"<span  style=\"font-weight:bold;\" >1</span>", "2"=>"<span  style=\"font-weight:bold;\" >2</span>", "3"=>"<span  style=\"font-weight:bold;\" >3</span>", "4"=>"<span  style=\"font-weight:bold;\" >4</span>",
			 "5"=>"<span  style=\"font-weight:bold;\" >5</span>", "6"=>"<span  style=\"font-weight:bold;\" >6</span>", "7"=>"<span  style=\"font-weight:bold;\" >7</span>" , "8"=>"<span  style=\"font-weight:bold;\" >8</span>", "9"=>"<span  style=\"font-weight:bold;\" >9</span>"
			, "10"=>"<span  style=\"font-weight:bold;\" >10</span>", "11"=>"<span  style=\"font-weight:bold;\" >11</span>", "12"=>"<span  style=\"font-weight:bold;\" >12</span>", "13"=>"<span  style=\"font-weight:bold;\" >13</span>", "14"=>"<span  style=\"font-weight:bold;\" >14</span>", "15"=>"<span  style=\"font-weight:bold;\" >15</span>", "16"=>"<span  style=\"font-weight:bold;\" >16</span>",
			 "17"=>"<span  style=\"font-weight:bold;\" >17</span>", "18"=>"<span  style=\"font-weight:bold;\" >18</span>", "19"=>"<span  style=\"font-weight:bold;\" >19</span>",
			 "20"=>"<span  style=\"font-weight:bold;\" >20</span>", "21"=>"<span  style=\"font-weight:bold;\" >21</span>", "nonconcur"=>"Non Admis à Concourir");
	
	$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne");
	
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
		'ChaireMC' => 'Chaire MC',
		'ChairePR' => 'Chaire PR',
		'Emerite' => 'Emerite',
		'MC' => 'MC',
		'PR' => 'PR',
		'PhD' => 'PhD',
		'HDR' => 'Habilité à diriger des recherches',
		'None' => 'Pas de grade'
	);
	
	define("NIVEAU_PERMISSION_BASE", 0);
	define("NIVEAU_PERMISSION_BUREAU", 100);
	define("NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE", 500);
	define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
	
	$actions1 = array(
		'details' => array('left' => true, 'title' => "Détails", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),
		'edit' => array('left' => true, 'title' => "Modifier", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/edit-icon-24px.png'),
	);
	$actions2 = array(
			'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
			'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'),
			'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
			'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
	);
	$actions = array_merge($actions1, $actions2);
	

	$typeExports = array(
			"htmledit" => 	array(
					"mime" => "text/html",
					"xsl" => "xslt/htmlminimaledit.xsl",
					"name" => "Html",
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU,
			),
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
					"permissionlevel" => NIVEAU_PERMISSION_BUREAU,
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
					"permissionlevel" => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE
			)
				
			/*			"latex" => 	array(
			 "mime" => "application/x-latex",
					"xsl" => "",
					"name" => "Zip",
					"permissionlevel" => NIVEAU_PERMISSION_BASE,
			),*/
					
	);
	
	
	$concours_ouverts = array(
			"06/01" => "DR2 (06/01)", "06/02" => "CR1 (06/02)", "06/03" => "CR2 (06/03)"
			);
	
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
			'login_rapp' => array('name'=>"Rapporteur" , 'sql_col'=>'rapporteur','default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'login_rapp2' => array('name'=>"Rapporteur2" , 'sql_col'=>'rapporteur2','default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);

	$filtersConcours = array(
			'theme1' => array('name'=>"Theme1" , 'liste' => get_config("topics"), 'default_value' => "tous", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => get_config("topics"), 'default_value' => "tous", 'default_name' => ""),
			'theme3' => array('name'=>"Theme3" , 'liste' => get_config("topics"), 'default_value' => "tous", 'default_name' => ""),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
			'labo2' => array('name'=>"Labo2" , 'default_value' => "tous", 'default_name' => ""),
			'labo3' => array('name'=>"Labo3" , 'default_value' => "tous", 'default_name' => ""),
			'concours' => array('name'=>"Concours" , 'liste' => $concours_ouverts, 'default_value' => "tous", 'default_name' => ""),
			'login_rapp' => array('name'=>"Rapporteur" , 'sql_col'=>'rapporteur','default_value' =>"tous", 'default_name' => ""),
			'login_rapp2' => array('name'=>"Rapporteur2" , 'sql_col'=>'rapporteur2','default_value' =>"tous", 'default_name' => ""),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => ""),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapportsConcours,'default_value' => "tous", 'default_name' => ""),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);
	
	$filtersAll = array_merge($filtersReports, $filtersConcours);
	
	$empty_filter = array(
			'grade' => "",
			'statut' => "",
			'id_session' => -1,
			'type_eval' => "",
			'login_rapp' => "",
			'id_origine' => -1,
			'id' => -1,
	);

	$empty_filter_concours = array(
			'labo1' => "",
			'labo2' => "",
			'labo3' => "",
			'theme1' => "",
			'theme2' => "",
			'theme3' => "",
			'statut' => "",
			'id_session' => -1,
			'login_rapp' => "",
			'id_origine' => -1,
			'id' => -1,
	);
	
	
	$labos_csv  = array ('code', 'fullname', 'nickname', 'directeur');
	$equivalence_csv  = array ('titrenomprenom', 'prerapport', 'annnesequivalence', 'rapporteur');
	$candidature_csv  = array ('nom', 'prenom', 'prerapport', 'grade', 'concours', 'prerapport', 'rapporteur', 'rapporteur2');
	$chercheur_csv  = array ('nomprenom', 'unite', 'grade', 'rapporteur', 'rapporteur2');
	
	$csv_composite_fields = array(
			'titrenomprenom' => array('','nom','prenom') ,
			 'nomprenom' => array('nom','prenom'),
	);
	
	$csv_fields = array_merge($labos_csv,$equivalence_csv, $chercheur_csv, $candidature_csv);
	
	$csv_preprocessing = array('nom' => 'normalizeName', 'prenom' => 'normalizeName','unit' => 'fromunittocode');
	
	$uploaded_csv_files = array(
			'unites' => 'uploads/labos.csv',
			'rapports' => 'uploads/rapports.csv'
	);
	
	
	$type_rapport_to_csv_fields = array(
	'Evaluation-Vague' => $chercheur_csv,
	'Evaluation-MiVague' => $chercheur_csv,
	'Promotion' => $chercheur_csv,
	'Suivi-PostEvaluation' => $chercheur_csv,
	'Titularisation' => $chercheur_csv,
	'Affectation' => $chercheur_csv,
	'Reconstitution' => $chercheur_csv,
			'Changement-Directeur' => $labos_csv,
			'Changement-Directeur-Adjoint' => $labos_csv,
			'Renouvellement' => $labos_csv,
			'Association' => $labos_csv,
			'Ecole' => $labos_csv,
			'Comite-Evaluation' => $labos_csv,
			'Generique' => $labos_csv,
			'Candidature' => $candidature_csv,
			'Equivalence' => $equivalence_csv,
	);
	
	$users_not_rapporteur = array('admin');
?>