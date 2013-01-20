<?php
	require_once("configDB.inc.php");
	
	
	define("president","Frédérique Bassino");
	define("president_titre","Présidente de la Section 6");
	define("secretaire","Hugo Gimbert");
	define("section_nb","6");
	define("section_shortname","Section 6");
	define("section_fullname","Section 6 du CoNRS");
	define("section_intitule","Sciences de l'information : fondements de l'informatique, calculs, algorithmes, représentations, exploitations");
	define("webmaster","hugo.gimbert@labri.fr");
	define("addresse_du_site","http://cn6.labri.fr/Marmotte");
		
	define("current_session",2);
	
	define("welcome_message",
	"	<p>Bienvenue sur le site de gestion des rapports de la section 6.
			N'hésitez pas à nous contacter (Yann ou Hugo) en cas de difficultés.</p>
		");
	
	$topics = array(
			"" => "",
"1a" => "Algorithmique, combinatoire, graphes",
"1b" => "Automates, systèmes dynamiques discrets",
"2a" => "Calcul formel et calcul certifié, arithmétique des ordinateurs",
"2b" => "Codage et cryptographie",
"3a" => "Logique, complexité algorithmique et structurelle",
"3b" => "Sémantique, modèles de calcul",
"4a" => "Programmation, génie logiciel",
"4b" => "Vérification et preuves",
"5" => "Recherche opérationnelle, aide à la décision, optimisation discrète et continue, satisfaction de contraintes, SAT",
"6" => "Systèmes de production, logistique, ordonnancement",
"7" => "I.A., système multi-agent, ingénierie / rep. et trait. des connaissances, de l'incertitude, form. des raisonnements, fusion information",
"8" => "Environnements informatiques pour l'apprentissage humain",
"9a" => "Sûreté de fonctionnement, sécurité informatique, protection de la vie privée",
"9b" => "Réseaux sociaux",
"10a" => "Réseaux, télécommunications, réseaux de capteurs",
"10b" => "Systèmes distribués",
"11" => "Internet du futur, intelligence ambiante",
"12a" => "Calcul distribué, grilles, cloud, calcul à haute performance, parallélisme, infrastructures à grande échelle",
"12b" => "Architecture et compilation",
"13" => "Cognition, modélisation pour la médecine, neurosciences computationnelles",
"14" => "Systèmes d'informations, web sémantique, masses de données, fouille de données, base de données, gestion de données, recherche d'informations, apprentissage",
"15" => "Bioinformatique");
			
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
			"rapporteur",
			"rapporteur2",
			"nom",
			"prenom",
			"grade",
			"concours",
			"avis",
			"labo1",
			"labo2",
			"labo3",
			"theme1",
			"theme2",
			"theme3",
			"id"
	);
	
	$statutsRapports = array( 'vierge' => "Rapport vierge", 'prerapport'=>'Prérapport', 'rapport'=>"Rapport", 'publie'=>"Rapport publié");
	$statutsRapportsPluriel = array( 'vierge' => "rapports vierges", 'prerapport'=>'prérapports', 'rapport'=>"rapports", 'publie'=>"rapport publiés");
	
	
	$fieldsAll = array(
		"statut" => "Statut",
		"concours" => "Concours",
		"ecole" => "Ecole",
		"nom" => "Nom",
		"prenom" => "Prenom",
		"unite" => "Unité",
		"grade" => "Grade",
		"anciennete_grade" => "Ancienneté dans grade",
		"type" => "Type",
		"rapporteur" => "Rapporteur",
		"rapporteur2" => "Rapporteur2",
			"avis" => "Proposition d'avis",
			"rapport" => "Proposition de rapport",
		"prerapport" => "Prérapport/Remarques",
		"date_recrutement" => "Date de recrutement",
		"labo1" => "Labo 1",
		"labo2" => "Labo 2",
		"labo3" => "Labo 3",
			"theme1" => "Theme 1",
			"theme2" => "Theme 2",
			"theme3" => "Theme 3",
			"theseAnnee" => "Annee thèse",
			"theseLieu" => "Lieu thèse",
			"HDRAnnee" => "Annee HDR",
			"HDRLieu" => "Lieu HDR",
			"anneesequivalence" => "Années d'équivalence",
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
		"id" => "Id",
	);

	$specialtr_fields = array("labo1","labo2","labo3","theme1","theme2","theme3");
	$start_tr_fields = array("labo1","theme1");
	$end_tr_fields = array("labo3","theme3");
	
	$fieldsIndividual = array(
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
			"prerapport",
			"date_recrutement",
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement"
	);

	$fieldsConcours = array(
			"concours",
			"rapporteur",
			"rapporteur2",
			"nom",
			"prenom",
			"avis",
			"grade",
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
			"prerapport",
			"rapport",
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement"
	);

	$fieldsCandidat = array(
			"rapporteur",
			"rapporteur2",
			"nom",
			"prenom",
			"grade",
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
			"production",
			"transfert",
			"encadrement",
			"responsabilites",
			"mobilite",
			"animation",
			"rayonnement"
	);
	
	$fieldsEquivalence = array(
			"rapporteur",
			"rapporteur2",
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
			"rapporteur2",
			"unite",
		"avis",
		"rapport",
		"prerapport"
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
		"id_origine" => "0",
			"labo1" => "",
			"labo2" => "",
			"labo3" => "",
			"theme1" => "",
			"theme2" => "",
			"theme3" => ""
	);
		
	
	$virgin_report_equivalence = 
			"La ".section_shortname." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à un doctorat d'une université française.\n\n".
			"La ".section_shortname." réunie en instance d'équivalence considère que la somme des titres et travaux présentés dans le dossier du candidat est équivalente à plus de 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"La qualification professionnelle du candidat n'est pas probante.\n\n".
			"Les travaux scientifiques présentés par le candidat ne sont pas probants.\n\n".
			"Le diplôme étranger dont le candidat est titulaire est insuffisant et n'équivaut pas à un doctorat français.\n\n".
			"L'expérience professionnelle acquise par le candidat n'équivaut pas en quantité et/ou en qualité à 4/8/12 années d'exercice des métiers de la recherche.\n\n".
			"Les titres et/ou travaux dont le candidat est titulaire est /sont insuffisants ou/et n'/ne sont/est pas convaincants.";

	$report_prototypes = array(
			'Equivalence' => array('rapport' => $virgin_report_equivalence)
	);
	
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
		"rapport" => "treslong",
		"prerapport" => "treslong",
		"anciennete_grade" => "short",
		"theseAnnee" => "short",
		"theseLieu" => "short",
		"HDRAnnee" => "short",
		"HDRLieu" => "short",
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
		"auteur" => "short",
		"date" => "short",
			"labo1" => "unit",
			"labo2" => "unit",
			"labo3" => "unit",
			"theme1" => "topic",
			"theme2" => "topic",
			"theme3" => "topic",
			"anneesequivalence" =>"short",
			"id" =>"short"
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
	$avis_classement = array(""=>"", "non"=>"<em>non-classé</em>", "oui"=>"Oui", "1"=>"<B>1</B>", "2"=>"<B>2</B>", "3"=>"<B>3</B>", "4"=>"<B>4</B>",
			 "5"=>"<B>5</B>", "6"=>"<B>6</B>", "7"=>"<B>7</B>" , "8"=>"<B>8</B>", "9"=>"<B>9</B>"
			, "10"=>"<B>10</B>", "11"=>"<B>11</B>", "12"=>"<B>12</B>", "13"=>"<B>13</B>", "14"=>"<B>14</B>", "15"=>"<B>15</B>", "16"=>"<B>16</B>",
			 "17"=>"<B>17</B>", "18"=>"<B>18</B>", "19"=>"<B>19</B>",
			 "20"=>"<B>20</B>", "21"=>"<B>21</B>");

	/* Pour les concours*/
	$avis_candidature = array(""=>"", "nonauditionne"=>"<em>Non Auditionné</em>", "oral"=>"Auditionné", "nonclasse"=>"<em>non-classé</em>", "1"=>"<B>1</B>", "2"=>"<B>2</B>", "3"=>"<B>3</B>", "4"=>"<B>4</B>",
			 "5"=>"<B>5</B>", "6"=>"<B>6</B>", "7"=>"<B>7</B>" , "8"=>"<B>8</B>", "9"=>"<B>9</B>"
			, "10"=>"<B>10</B>", "11"=>"<B>11</B>", "12"=>"<B>12</B>", "13"=>"<B>13</B>", "14"=>"<B>14</B>", "15"=>"<B>15</B>", "16"=>"<B>16</B>",
			 "17"=>"<B>17</B>", "18"=>"<B>18</B>", "19"=>"<B>19</B>",
			 "20"=>"<B>20</B>", "21"=>"<B>21</B>");
	
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
	
	$typesRapportsToCheckboxes = array(
	'Evaluation-Vague' => $evalCheckboxes,
	'Evaluation-MiVague' => $evalCheckboxes,
	'Renouvellement' => $pertinenceCheckboxes,
	'Association' => $pertinenceCheckboxes,
	'Ecole' => $ecoleCheckboxes
	);

	$typesRapportsToCheckboxesTitles = array(
			'Evaluation-Vague' => '<B>EVALUATION A VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</B>',
			'Evaluation-MiVague' => '<B>EVALUATION A MI-VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</B>',
			'Renouvellement' => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
			'Association' => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
			'Ecole' => '<B>AVIS SUR L\'ECOLE</B>'
	);
	

	$typesRapportsToEnteteGauche = array(
			'Evaluation-Vague' => '<B>Objet de l’évaluation :</B><br/><EM>évaluation à vague de chercheur</EM>',
			'Evaluation-MiVague' => '<B>Objet de l’évaluation :</B><br/><EM>évaluation à mi-vague de chercheur</EM>',
			'Promotion' => '<B>Objet de l’évaluation :</B><br/>Avancement de grade<br/><B>Au grade de :</B>',
			'Candidature' => '<B>Objet de l’évaluation :</B><br/><EM>Candidature au concours</EM>',
			'Suivi-PostEvaluation' => '<B>Objet de l’évaluation :</B><br/><EM>Suivi post-évaluation</EM>',
			'Affectation' => '<B>Objet de l’évaluation :</B><br/>Affectation',
			'Titularisation' => '<B>Objet de l’évaluation :</B><br/>Titularisation',
			'Reconstitution' => '<B>Objet :</B><br/>Reconstitution de carrière',
			'Changement-Directeur' =>  '<B>Objet de l’évaluation :</B><br/>Changement de directeur',
			'Changement-Directeur-Adjoint' =>  '<B>Objet de l’évaluation :</B><br/>Changement de directeur adjoint',
			'Renouvellement' => '<B>Objet de l’examen :</B> <EM>avis de pertinence d’association au CNRS : renouvellement</EM>',
			'Association' => '<B>Objet de l’examen :</B> <EM>avis de pertinence d’association au CNRS : projet d\'association</EM>',
			'Ecole' => '<B>Objet de l’évaluation :</B><br/> Ecole Thématique',
			'Comite-Evaluation' => '<B>Objet de l’examen :</B> Comité d\'évaluation',
			'Generique' => '&nbsp;',
			'Equivalence' => '<B>Objet de l’évaluation :</B><br/><EM>Equivalence titres et travaux</EM>',
						
			'' => ''
	);

	$enTetesDroit = array(
			'Individu' => '<B>Nom, prénom et affectation du chercheur :</B><br/>',
			'Concours' => '<B>Concours, classement, nom et prénom du candidat :</B><br/>',
			'Equivalence' => '<B>Nom et prénom du candidat :</B><br/>',
			'Unite' => '<B>Code, intitulé et nom<br/>du directeur de l’unité :</B><br/>',
			'Ecole' => '<B>Nom de l\'école et du porteur de projet :</B><br/>',
			'PromotionDR' => '<B>Classement, nom et unité :</B><br/>',
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

	define("NIVEAU_PERMISSION_BASE", 0);
	define("NIVEAU_PERMISSION_BUREAU", 100);
	define("NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE", 500);
	define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
	
	$actions = array(
		'edit' => array('title' => "Modifier", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/edit-icon-24px.png'),
		'details' => array('title' => "Détails", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),
		'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
		'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'),
		'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
		'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
	);
	
	

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
			'grade' => array('name'=>"Grade" , 'liste' => $grades, 'default_value' => "", 'default_name' => "Tous les grades"),
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "", 'default_name' => "Tous les statuts"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'type_eval' => array('name'=>"Type d'évaluation" , 'sql_col'=>'type', 'liste' => $typesRapports,'default_value' => "", 'default_name' => "Tous les types"),
			'login_rapp' => array('name'=>"Rapporteur" , 'sql_col'=>'rapporteur','default_value' =>"", 'default_name' => "Tous les rapporteurs"),
			'login_rapp2' => array('name'=>"Rapporteur2" , 'sql_col'=>'rapporteur2','default_value' =>"", 'default_name' => "Tous les rapporteurs"),
			'id_origine' => array('default_value' =>-1),
			'id' => array('default_value' =>-1),
	);

	$filtersConcours = array(
			'statut' => array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "", 'default_name' => "Tous les statuts"),
			'concours' => array('name'=>"Concours" , 'liste' => $concours_ouverts, 'default_value' => "", 'default_name' => "Tous les concours"),
			'type_eval_concours' => array('name'=>"Type d'évaluation" , 'sql_col'=>'type', 'liste' => $typesRapportsConcours,'default_value' => "", 'default_name' => "Tous les types"),
			'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
			'labo1' => array('name'=>"Labo1" , 'default_value' => "", 'default_name' => ""),
			'labo2' => array('name'=>"Labo2" , 'default_value' => "", 'default_name' => ""),
			'labo3' => array('name'=>"Labo3" , 'default_value' => "", 'default_name' => ""),
			'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "", 'default_name' => ""),
			'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "", 'default_name' => ""),
			'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "", 'default_name' => ""),
			'login_rapp' => array('name'=>"Rapporteur" , 'sql_col'=>'rapporteur','default_value' =>"", 'default_name' => "Tous les rapporteurs"),
			'login_rapp2' => array('name'=>"Rapporteur2" , 'sql_col'=>'rapporteur2','default_value' =>"", 'default_name' => "Tous les rapporteurs"),
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
	
	$csv_preprocessing = array('nom' => 'normalizename', 'prenom' => 'normalizename');
	
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