<?php

date_default_timezone_set('Europe/Paris');


require_once("config/configDB.inc.php");
require_once("config_tools.inc.php");


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



$statutsRapports = array(
		'doubleaveugle'=>'Edition Double Aveugle',
		'edition' => "Edition",
		'avistransmis'=>"Avis transmis",
		'validation'=>"Validation président",
		'publie'=>"Rapport transmis",
		'audition'=>"Audition"
);

$statutsRapportsACN = array(
		'doubleaveugle'=>'Edition Double Aveugle',
		'edition' => "Edition",
		'avistransmis'=>"Avis transmis",
		'validation'=>"Validation président"
);

$statutsRapportsIndiv = array(
		'doubleaveugle'=>'Edition Double Aveugle (les rapporteurs ne voient que leus propres prérapports)',
		'edition' => "Edition (édition par les rapporteurs)",
		'avistransmis'=>"Transmettre l'avis (l'avis ne sera définitivement plus modifiable)",
		'validation'=>"Validation président (seuls le président et le secrétaire peuvent éditer le rapport)",
		'publie'=>"Transmettre le rapport (le RS ne sera définitivement plus modifable)"
);
$statutsRapportsMulti = array(
		'doubleaveugle'=>'Edition Double Aveugle (les rapporteurs ne voient que leurs propres prérapports)',
		'edition' => "Edition (édition par les rapporteurs)",
		'avistransmis'=>"Transmettre les avis (les avis ne sont définitivement plus modifiables)",
		'validation'=>"Validation président (seuls le président et le secrétaire peuvent éditer les rapports)",
		'publie'=>"Transmettre les rapports (les rapports ne sont définitivement plus modifables)"
);





//	define("config_file","config/config.xml");
define("signature_file","img/signature.jpg");
define("signature_blanche","img/signature_blanche.jpg");
//	define("config_file_save","config/config.sauv.xml");

global $rootdir;
global $dsirootdir;
if(!isset($_SESSION['filter_section']))
{
	removeCredentials();
	throw new Exception("Unexpected error");
}
$dossier_temp = $rootdir."./tmp/".$_SESSION['filter_section']."/";
$dossier_stockage_short = $rootdir."./storage/".$_SESSION['filter_section']."/";
$dossier_stockage = realpath($dossier_stockage_short);
$dossier_stockage_dsi = realpath($rootdir."./storage/".$dsirootdir."/");
$dossier_stockage_dsi = $rootdir."./storage/".$dsirootdir."/";

$typesdocs = array();
$sql = "SELECT * FROM ".dsidbname."."."typesdocs WHERE 1;";
$result = sql_request($sql);
while($row = mysqli_fetch_object($result))
	$typesdocs[$row->id] = $row->doctyplib;


/* champs apparaissant sur l'écran principal */

$fieldsSummary = array("type","unite","nom","prenom",/*"ecole",*/"avis","rapporteur","avis1","rapporteur2","avis2", "rapporteur3", "avis3","theme1","theme2","theme3","DKEY");

$fieldsSummaryConcours = array("concours","nom","prenom","sousjury","statut_celcc","avis","rapporteur","avis1",
			       "rapporteur2","avis2","rapporteur3","avis3","theme1","theme2","theme3","labo1","labo2","diploma"
);

if(!get_option("show_rapporteur3"))
  {
    $fieldsSummary = array_diff($fieldsSummary, array("rapporteur3","avis3"));
    $fieldsSummaryConcours = array_diff($fieldsSummaryConcours, array("rapporteur3","avis3"));
   }
for($i = 1; $i<= 3; $i++)
  {
if(!get_option("show_theme".$i))
  {
    $fieldsSummary = array_diff($fieldsSummary, array("theme".$i));
    $fieldsSummaryConcours = array_diff($fieldsSummaryConcours, array("theme".$i));
   }
  }


$genreCandidat = array(
		'' => "",
		'homme'=>'Homme',
		'femme' => "Femme",
);

/* mapping from fields in the database to labels */
$fieldsRapportACN = array(
		"dsi", "statut", "DKEY", "concours", "sousjury", "section", "intitule", "nom",
		"prenom", "unite", "type", "rapporteur", "rapporteur2", "rapporteur3",
		"avis", "avis1", "avis2", "avis3",
		"rapport", "statut","signataire"
);



$fieldsEditableACN = array(
			   "concours", "sousjury", "intitule", "unite", "type", "statut", "conflits","signataire"
			     );

if(get_option("acn_can_edit_reports"))
  $fieldsEditableACN[]= "rapport";

if(get_option("acn_can_edit_avis"))
  $fieldsEditableACN[]= "avis";

if(get_option("acn_can_edit_rapporteurs"))
  {
  $fieldsEditableACN[]= "rapporteur";
  $fieldsEditableACN[]= "rapporteur2";
  $fieldsEditableACN[]= "rapporteur3";
  }


$fieldsEditableAvisTransmis = array(
	    "unite", "labo1", "rapport", 'rapporteur', 'rapporteur2','rapporteur3',"intitule","signataire"
   );

$fieldsRapportAll = array(
		  "concoursid" => "NumCand",
		"dsi" => "Infos e-valuation",
		"statut" => "Statut rapport",
		"statut_celcc"=>"Statut candidature",
		"voeux"=>"Voeux affectation",
		"DKEY" => "DKEY",
		"signataire"=>"Président par interim",
		"NUMSIRHUS" => "NUMSIRHUS",
		"concours" => "Concours",
		"sousjury" => "Section de jury",
		"section"=> "Section",
		"intitule" => "Intitulé",
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
		"date" => "Date modification",
		"id" => "Id",
		"id_session" => "Id session",
		"id_origine" => "Id origine",
);

/*
 $type_specific_fields_renaming = array(
 		"Expertise" => array("ecole" => "Intitulé du rapport"),
 		"Generique" => array("ecole" => "Intitulé du rapport"),
 		"DEChercheur" => array("ecole" => "Intitulé du  rapport"),
 		"ChgtSection" => array("ecole" => "Intitulé du  rapport")
 );
*/

/*
 * Les champs disponibles au secrétaire pour un rapport individuel
*/
$fieldsIndividual0 = array(
		array("type", "statut"),
		"intitule","dsi",
		array(
				"rapporteur",
				"rapporteur2",
				"rapporteur3",
		),
		"unite",
		"avis",
		"rapport",
		"signataire"
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
			     array("nom","prenom"),
		"conflits",
		"infos_evaluation",
		"infos_celcc",
		"genre",
		array("grade","annee_recrutement"),
		"labo1",
		"theme1",
		"theme2",
		"theme3",
		"fichiers",
		"fichiers_evaluation",
		"rapports"
);

$refposition = array();
if(!isset($_SESSION["refposition"]))
  {
    $sql = "SELECT * FROM ".dsidbname.".refposition WHERE 1;";
    $result = sql_request($sql);
    while($row = mysqli_fetch_object($result))
	$refposition[$row->codeposition] = $row->lib_position;
    $_SESSION["refposition"] = $refposition;
  }
else
  $refposition = $_SESSION["refposition"];

$fieldsDSIChercheurs = array(
			     "courriel" => "",
			     "st" => array("statut_sirhus" => "","grade"=>"","drecrute" => "recruté le "),
			     //		     "Grade" => array("grade" => "","effet_grade" => " depuis "),
			     "Unite1" => array("code_unite" => "", "ddebcodeunite1" => "depuis"),
			     "Unite2" => array("code_unite2" => "", "ddebcodeunite2" => "depuis"),
			     "lieu" => array("lieutravail" => "Lieu de travail:", "dr" =>"Deleg"),
			     "scn1" => "Section:",
			     "scn2" => "Section2:",
			     "CodePos" => array("codeposition" => "", "nature_sirhus" => " -", "quotite" => "- quotité", "ddebposs" =>"- du","dfinposs"=>"au"),
			     );


$fieldsChercheursDelegationsAll = array(
		"nom",
		"prenom",
		"conflits",
		"genre",
		"grade",
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
		"statut_celcc",
		"voeux",
		"concours",
		"sousjury",
		"rapporteur",
		"rapporteur2",
		"rapporteur3",
		"avis",
		"avissousjury",
		"rapport",
		"signataire"
);

$fieldsRapportsIE0 = array(
		"type",
		"rapporteur",
		"rapporteur2",
		"rapporteur3",
		"avis",
		"rapport",
		"signataire"
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
  //  echo $index." ".$rubrique."<br/>";
	$fieldsRapportsCandidat1[] = "Generic".(3*$index);
	$fieldsRapportsCandidat2[] = "Generic".(3*$index+1);
	$fieldsRapportsCandidat3[] = "Generic".(3*$index+2);
}

$fieldsRapportsCandidat = array_merge($fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);


$fieldsIndividualAll = array(
		"nom" => "Nom",
		"prenom" => "Prénom",
		"infos_evaluation"=>"Infos e-valuation",
		"infos_celcc"=>"Infos celcc",
		"genre" => "Genre",
		"grade" => "Grade (Individu)",
		"annee_recrutement" => "Date de recrutement",
		"labo1" => "Labo 1",
		"labo2" => "Labo 2",
		"labo3" => "Labo 3",
		"theme1" => "Mot-clé 1",
		"theme2" => "Mot-clé 2",
		"theme3" => "Mot-clé 3",
		"audition" => "Avis sur l'audition",
		"conflits" => "Conflits",
		"fichiers"=> "Avis de personnalités scientifiques",
		"fichiers_evaluation"=> "Fichiers e-valuation",
		"fichiers_celcc"=> "Dossier candidat",
		"birth" => "Date naissance",
		"diploma" => "Date diplôme"
);

$fieldsIndividualDB = array(
		"nom" => "Nom",
		"prenom" => "Prénom",
		"genre" => "Genre",
		"grade" => "Grade (Individu)",
		"annee_recrutement" => "Date de recrutement",
		"labo1" => "Labo 1",
		"labo2" => "Labo 2",
		"labo3" => "Labo 3",
		"theme1" => "Mot-clé 1",
		"theme2" => "Mot-clé 2",
		"theme3" => "Mot-clé 3",
		"audition" => "Rapport d'audition",
		"conflits" => "Conflits",
		"birth" => "Date naissance",
		"diploma" => "Date diplôme"
);


foreach($add_rubriques_people as $index => $rubrique)
  {
	$fieldsIndividualAll["Info".$index] = $rubrique;
	$fieldsIndividualDB["Info".$index] = $rubrique;	
  }
foreach($add_rubriques_candidats as $index => $rubrique)
  {
	$fieldsIndividualAll["Info".$index] = $rubrique;
	$fieldsIndividualDB["Info".$index] = $rubrique;	
  }

$mandatory_edit_fields=
array('id','nom','prenom'
);

$mandatory_export_fields=
array('id','nom','prenom','genre','type','concours',
		"annee_recrutement",
		"diploma",
		"labo1",
		"labo2",
		"labo3",
		"theme1",
		"theme2",
		"theme3",
		"audition"
);

/* dirty */
for($i = 0; $i <= 30; $i++)
{
	$fieldsIndividualAll["Info".$i] = "Info".$i;
	$fieldsRapportAll["Generic".$i] = "Generic".$i;
}

$fieldsAll = array_merge($fieldsRapportAll, $fieldsIndividualAll, array("rapports" => "Autres rapports"));

$fieldsCandidatAvantAudition = array(
	     array("nom","prenom"),
	     array("diploma","birth"),
		array("infos_celcc","conflits"),
		"fichiers_celcc",
	     "fichiers",
		"rapports",
		"labo1",
		"labo2",
		"labo3",
		"theme1",
		"theme2",
		"theme3",
		"concourspresentes",
		"audition"
);

foreach($add_rubriques_candidats as $index => $rubrique)
	$fieldsCandidatAvantAudition[] = "Info".$index;

$fieldsCandidatAuditionne = $fieldsCandidatAvantAudition;
$fieldsCandidat = $fieldsCandidatAuditionne;

$fieldsDelegation = array("statut","rapporteur","rapporteur2","rapporteur3","nom","prenom",
		"unite", "rapports", "avis1", "prerapport", "prerapport2", "prerapport3",
		"labo1","labo2","labo3",
		"theme1","theme2","theme3",
		"avis","rapport",
);

$fieldsIndividualDefault = array(
		"DKEY",
		"dsi",
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
		array("type", "statut", "DKEY"),
		"dsi",
		"intitule",
		array(
				"rapporteur",
				"rapporteur2",
				"rapporteur3",
		),
		"unite",
		"fichiers",
		"fichiers_evaluation",
		"rapports",
		"avis",
		"rapport",
		"signataire"
);

$fieldsEcoles0 = array(
		array("type","DKEY", "statut"),
		"dsi",
		"intitule",
		array("nom", "prenom"),
		array(
				"rapporteur",
				"rapporteur2",
				"rapporteur3",
		),
		"unite",
		"fichiers",
		"fichiers_evaluation",
		"rapports",
		"avis",
		"rapport",
		"signataire"
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

$fieldsUnites = array_merge($fieldsUnites0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
$fieldsEcoles = array_merge($fieldsEcoles0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);

$fieldsUnitsDB = array(
		"code" => "Code",
		"nickname" => "Acronyme",
		"fullname" => "Nom",
		"directeur" => "Direction",
		"section" => "Section"
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
		      "DKEY"=>"",
		"statut" => "doubleaveugle",
		"type" => "Generique",
		"id_session" => "",
		"id_origine" => "0",
		  "signataire"=>"",
		      "nom" => "",
		"prenom" => "",
		"grade_rapport" => "",
		"unite" => "",
		//"ecole" => "",
		"concours" => "",
		"type" => "",
		"rapporteur" => "",
		"rapporteur2" => "",
		"rapporteur3" => "",
		"prerapport" => "",
		"prerapport2" => "",
		"anciennete_grade" => "",
		"infos_evaluation" => "",
		"infos_celcc" => "",
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
	"NUMSIRHUS"=>"",
		"nom" => "",
		"prenom" => "",
		"genre" => "None",
		"grade" => "None",
	"infos_evaluation"=>"",
	"infos_celcc"=>"",
		"annee_recrutement" => "",
		"birth" => "",
		"diploma" => "",
		"labo1" => "",
		"labo2" => "",
		"labo3" => "",
		"theme1" => "",
		"theme2" => "",
		"theme3" => "",
		"audition" => ""
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
		'Expertise' => array('intitule' => "Expertise (projet ou suivi ou intégration équipe ou restructuration)"),
		'Generique' => array('intitule' => "Rapport sur unité"),
		'DEChercheur' => array('intitule' => "Rapport sur chercheur"),
		"ChgtSection" => array('intitule' => "Changement de section, évaluation permanente par une deuxième section")
);

/**** TYPES DE RAPPORTS **************/

/* mapping between evaluation id and marmotte id */
$id_session_cn = 0;
$id_session_concours = 1;

$id_rapport_to_label = array();
$report_class_to_types = array();
$report_types_to_class = array();

$typesRapportsEcoles = array();
$typesRapportsChercheurs = array();
$typesRapportsChercheursShort = array();
$typesRapportsUnites = array();
$typesRapportsUnitesShort = array();
$typesRapportsConcours = array();
$typesRapportsSession = array();
$typesRapportsAll = array();
$typesRapportsPromotion = array("4500","4505","4510","4515","4520");
$types_needs_checkboxes = array();

define("REPORT_CLASS_CHERCHEUR", "c");
define("REPORT_CLASS_UNIT", "u");
define("REPORT_CLASS_CONCOURS", "x");
define("REPORT_CLASS_DELEGATION", "d");
define("REPORT_CLASS_ECOLE", "e");

define("REPORT_CANDIDATURE", 7777);
define("REPORT_AUDITION", 7781);


define("REPORT_ECOLE", 8515);
define("REPORT_DELEGATION", 7778);
define("REPORT_EVAL", 6005);
define("REPORT_EVAL_RE", 6008);
define("REPORT_TITU", 6015);
define("REPORT_EMERITAT", 7017);
define("REPORT_CHERCHEUR", 7779);
define("REPORT_EMERITAT_RE", 7018);
define("REPORT_INCONNU", 9999);
define("REPORT_UNIT_GENERIC",7780);
//reltypevalavis
$sql = "SELECT * FROM ".dsidbname.".reftypeval  WHERE 1 ORDER BY label ASC;";
$result = sql_request($sql);
while ($row = mysqli_fetch_object($result))
{
	$report_class_to_types[$row->classe][] = $row->id;
	$report_types_to_class[$row->id] = $row->classe;
	$id_rapport_to_label[$row->id] = $row->label;
	$typesRapportsAll[$row->id] = $row->label;

	switch($row->classe)
	{
		case REPORT_CLASS_CHERCHEUR:
		case REPORT_CLASS_DELEGATION:
			$typesRapportsChercheurs[$row->id] = $row->label;
			$typesRapportsChercheursShort[$row->id] = $row->label;
			$typesRapportsSession[$row->id] = $row->label;
			break;
		case REPORT_CLASS_CONCOURS:
			$typesRapportsConcours[$row->id] = $row->label;
			break;
		case REPORT_CLASS_UNIT:
		case REPORT_CLASS_ECOLE:
			$typesRapportsUnites[$row->id] = $row->label;
			$typesRapportsUnitesShort[$row->id] = $row->label;
			$typesRapportsSession[$row->id] = $row->label;
			break;
		case REPORT_CLASS_CONCOURS:
			$typesRapportsConcours[$row->id] = $row->label;
			break;
		case REPORT_CLASS_ECOLE:
			$typesRapportsEcoles[$row->id] = $row->label;
			break;
	}
}

/*********** PROTOTYPES DE RAPPORT **********************/
/*
 foreach($id_rapport_to_label as $type => $intitule)
 {
$report_prototypes[$type]["rapport"] = get_config("prototype_".$type."_rapport");
$report_prototypes[$type]["prerapport"] = get_config("prototype_".$type."_prerapport");
$report_prototypes[$type]["prerapport2"] = get_config("prototype_".$type."_prerapport2");
}

*/
$mergeableTypes = array("short","treslong","long","short");

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

/****************************** Mise en page des détails sur un rapport ******************************/
$fieldsTypes = array(
		     "concoursid"=>"short",
		"dsi"=>"dsi",
		"intitule" => "short",
		"signataire"=>"short",
		"concours" => "concours",
		"sousjury" => "sousjury",
	     	"nom" => "short",
		"prenom" => "short",
		"genre" => "enum",
		"statut_individu"=> "enum",
		"statut_celcc"=> "short",
		"voeux"=> "short",
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
		"infos_evaluation" => "short",
		"infos_celcc" => "short",
		"annee_recrutement" => "short",
		"avissousjury" => "avis",
		"date" => "short",
		"conflits" => "long",
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
		"fichiers_celcc" => "files_celcc",
		"fichiers_evaluation" => "files_evaluation",
		"rapports" => "rapports",
		"avissousjury" => "avis",
		"statut" => "statut"
);


for($i = 0 ; $i < 30; $i++)
{
	$fieldsTypes["Generic".$i] = "long";
	$fieldsTypes["Info".$i] = "long";
}

if(get_option("sec_can_edit"))
  {
    foreach($fieldsTypes as $key => $val)
      $fieldsEditableSecretaire[] = $key;
    }
 else
  $fieldsEditableSecretaire = 
    array(
		"intitule",
		"concours",
		"sousjury",
		"nom",
		"prenom",
		"genre",
		"statut_individu",
		"grade",
		"grade_rapport",
		"theseloc",
		"unite",
    "type",
		"rapporteur",
		"rapporteur2",
		"rapporteur3",
		"avis",
		"rapport",
		"anciennete_grade",
		"annee_recrutement",
		"avissousjury",
		"conflits",
		"birth",
		"diploma",
		"labo1",
		"labo2",
		"labo3",
		"theme1",
		"theme2",
		"theme3",
		"audition",
		"fichiers",
		"avissousjury",
		"statut","signataire"
);

$fieldsEditableOnlySecretaire = array("nom","prenom","conflits","type","signataire");

$nonEditableFieldsTypes = array('id','date',"nom","prenom","infos_celcc","infos_evaluation");
$nonVisibleFieldsTypes = array('id');
$alwaysVisibleFieldsTypes = array('nom','prenom','rapporteur','concours','avissousjury','sousjury','rapporteur2','rapporteur3','infos_evaluation','infos_celcc','unite','fichiers','fichiers_evaluation','fichiers_celcc','rapports','conflits','dsi','intitule','type','avis','statut','statut_celcc','voeux','signataire');

$fieldsArrayCandidat = array($fieldsCandidat, $fieldsRapportsCandidat0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
$fieldsArrayIE = array($fieldsCandidatAvantAudition, $fieldsRapportsIE0, $fieldsRapportsCandidat1, $fieldsRapportsCandidat2, $fieldsRapportsCandidat3);
$fieldsArrayChercheur = array($fieldsChercheursAll, $fieldsIndividual0,$fieldsIndividual1,$fieldsIndividual2,$fieldsIndividual3);
$fieldsArrayUnite = array(array(), $fieldsUnites0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);
$fieldsArrayEcole = array(array(), $fieldsEcoles0, $fieldsUnites1, $fieldsUnites2, $fieldsUnites3);

$fieldsPeople = array_merge($fieldsCandidat, $fieldsChercheursAll);



  if(get_option("bur_can_meta"))
    {
      $fieldsEditableBureau = array();
      foreach($fieldsPeople as $key)
	{
	  if(is_array($key)) $fieldsEditableBureau = array_merge($fieldsEditableBureau ,$key);
	  else $fieldsEditableBureau[] = $key;
	}
    }
  else
    $fieldsEditableBureau = array("theme1","theme2","theme3","rapporteur","rapporteur2", "rapporteur3");


if(!get_option("bur_can_affect"))
  $fieldsEditableBureau = array_diff($fieldsEditableBureau, array("rapporteur","rapporteur2","rapporteur3"));

 if(!get_option("bur_can_keywords"))
  $fieldsEditableBureau = array_diff($fieldsEditableBureau, array("theme1","theme2","theme3"));


$fieldsArrayDelegation = array($fieldsChercheursDelegationsAll, $fieldsIndividual0,$fieldsIndividual1,$fieldsIndividual2,$fieldsIndividual3);

$typesRapportToFields =	array();





function is_equivalence_type($type)
{
	return ($type == 5015) || ($type == 5010) || ($type == 5025) || ($type == 5020);
}

function is_promotion_DR($type)
{
	return ($type == 4510) || ($type == 4515) || ($type == 4520);
}

function is_promotion($type)
{
	return ($type >= 4500 && $type <= 4520);
}

function is_classement($type)
{
	return ($type == REPORT_CANDIDATURE) || is_promotion_DR($type) || ($type == REPORT_EMERITAT) || ($type == REPORT_EMERITAT_RE);
}

function is_equivalence($type)
{
  return in_array($type,array(5025,5010,5015,5020)); 
}

function is_concours($type)
{
  return ($type == REPORT_CANDIDATURE) || ($type == REPORT_AUDITION) ||  is_equivalence($type);
}

function is_avis_classement($avis)
{
	return (strlen($avis) > 1) && ($avis[0] == "c") && is_numeric($avis[1]);
}

function classement_from_avis($avis)
{
	return substr($avis,1);
}

function is_ecole_or_colloque_type($type)
{
  //  echo $type;
  //  echo $report_types_to_class[$type];
  //  foreach($report_types_to_class as $type => $class) echo $type." ".$class."<br/>";
  global $report_types_to_class;
  return (isset($report_types_to_class[$type]) && $report_types_to_class[$type] == REPORT_CLASS_ECOLE);
}


foreach($report_class_to_types as $class => $ids)
{
	foreach($ids as $id)
	{
		if(is_equivalence_type($id))
			$typesRapportToFields[$id] = $fieldsArrayIE;
		else
		{
			switch($class)
			{
				case REPORT_CLASS_ECOLE:
					$typesRapportToFields[$id] = $fieldsArrayEcole; break;
				case REPORT_CLASS_CHERCHEUR:
					$typesRapportToFields[$id] = $fieldsArrayChercheur; break;
				case REPORT_CLASS_UNIT:
					$typesRapportToFields[$id] = $fieldsArrayUnite; break;
				case REPORT_CLASS_DELEGATION:
					$typesRapportToFields[$id] = $fieldsArrayDelegation; break;
				case REPORT_CLASS_CONCOURS:
					$typesRapportToFields[$id] = $fieldsArrayCandidat; break;
			}
		}
	}
}
$typesRapportToFields[REPORT_INCONNU] = $fieldsArrayUnite;


/*********************** renommge de champs pour import ******************************************/
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
		'Audition' => 'xslt/audition.xsl',
		'Classement' => 'xslt/classement.xsl',
		'' => 'xslt/html2.xsl'
);

/******************************************************************** Définition des avis possibles pour chaque type de rapport *********************************************/


/* Pour les concours*/

$avis_lettre = array(
		"Ap"=>"A+",
		"A"=>"A",
		"Am"=>"A-",
		"Bp"=>"B+",
		"B"=>"B",
		"Bm"=>"B-",
		"C"=>"C"
);

define("avis_tres_favorable", 1);
define("avis_favorable", 2);
define("avis_defavorable", 3);
define("avis_reserve", 4);
define("avis_alerte", 5);
define("avis_differe", 6);
define("avis_pas_davis", 7);
define("avis_classe", 8);
define("avis_ouinon", 9);
define("avis_proposition", 10);
define("avis_aucunadonner", 11);
define("avis_oui", 72);
define("avis_non", 73);
define("avis_non_classe", 74);
define("avis_adiscuter", 75);
define("avis_desistement", 76);
define("avis_nonconcur",77);
define("avis_nonauditionne",78);
define("avis_oral",79);
define("avis_estclasse",80);
define("avis_admis_a_concourir",81);
define("avis_IE_oui",82);
define("avis_IE_non",83);

$avis_candidature_short =
array(
		"" =>"sans avis",
		//		avis_desistement => 'désistement',
		avis_adiscuter=>"à discuter",
		avis_nonauditionne=>"non-auditionné",
		avis_oral=>"auditionné",
		avis_non_classe=>"non-classé",
		avis_classe=>"classé",
		avis_IE_oui=>"IE oui",
		avis_IE_non=>"IE non"
		//avis_admis_a_concourir => "admis à concourir",
		//avis_nonconcur=>"non-admis à concourir"
);


$tous_avis = array(
		   avis_oui=>"oui",
		   avis_non=>"non",
		   avis_non_classe=>"non-classé",
		   avis_adiscuter=>"à discuter",
		   avis_desistement=>"désistement",
		   avis_nonconcur=>"non-admis à concourir",
		   avis_nonauditionne=>"non-auditionné",
		   avis_oral=>"auditionné",
		   avis_estclasse=>"classé",
		   avis_defavorable=>"défavorable",
		   avis_IE_oui=>"IE oui",
		   avis_IE_non=>"IE non"
);
/* Pour les promos*/
$avis_classement = array(avis_adiscuter=>"à discuter", avis_non=>"Non", avis_oui=>"Oui");

foreach($avis_lettre as $avis => $lettre)
{
	$avis_classement[$avis] = $lettre;
	$avis_candidature_short[$avis] = $lettre;
}

$max_classement = 30;
for($i = 1; $i <= $max_classement; $i++)
{
	$avis_classement["c".strval($i)] = "Classé $i";
	$avis_candidature_short["c".strval($i)] = "classé $i";
}




$sql = "SELECT * FROM dsi.reftypeavis WHERE 1;";
$result= sql_request($sql);
while($row = mysqli_fetch_object($result))
{
	if($row->id == avis_classe)
		$tous_avis[$row->id] = $avis_classement;
	else if($row->id == avis_ouinon)
		$tous_avis[$row->id] = array(avis_oui => "oui", avis_non => "non");
	else
		$tous_avis[$row->id] = $row->label;
}
foreach($avis_lettre as $avi => $label)
	if(!isset($tous_avis[$avi])) $tous_avis[$avi] = $label;
foreach($avis_candidature_short as $avi => $label)
	if(!isset($tous_avis[$avi])) $tous_avis[$avi] = $label;
foreach($avis_classement as $avi => $label)
	if(!isset($tous_avis[$avi])) $tous_avis[$avi] = $label;


//	$sql = "select label from reftypeavis where id in (select idavis from reltypevalavis where ideval = '7017');";
$typesRapportToAvis = array(
		REPORT_CANDIDATURE => $avis_candidature_short,
		REPORT_DELEGATION => $avis_lettre,
		REPORT_INCONNU=>$tous_avis
);

if(!isset($_SESSION["type_avis_classement"]))
{
	$type_avis_classement = array();
	foreach($id_rapport_to_label as $type => $data)
	{
		$typesRapportToAvis[$type][""] = "";
		$sql = "select idavis from dsi.reltypevalavis where ideval = '$type'";
		$result = sql_request($sql);
		while($row = mysqli_fetch_object($result))
		{
			if(is_array($tous_avis[$row->idavis]))
			{
				foreach($tous_avis[$row->idavis] as $id => $avis)
				  {
					$typesRapportToAvis[$type][$id] = $avis;
				  }
			}
			else
			$typesRapportToAvis[$type][$row->idavis]= $tous_avis[$row->idavis];
			if($row->idavis == avis_classe)
				$type_avis_classement[] = $type;
		}
	}
	$_SESSION["type_avis_classement"] = $type_avis_classement;
	$_SESSION["type_rapport_to_avis"] = $typesRapportToAvis;
}
else
{
	$type_avis_classement = $_SESSION["type_avis_classement"];
	$typesRapportToAvis = $_SESSION["type_rapport_to_avis"];
}


/************************* Mise en page *******************************/

$typesRapportToMiseEnPage = array(
		REPORT_CANDIDATURE => $avis_candidature_short,
		REPORT_DELEGATION => $avis_lettre
);

$avis_candidature_necessitant_pas_rapport_sousjury = array("", "adiscuter", "nonauditionne", "desistement");

$avis_sessions = array();
foreach($tous_avis as $id => $label)
  {
    if(!is_array($label))
      $avis_sessions[$id] = $label;
  }

/* Definition des checkboxes à la fin de certains rapports*/

/*Pour les evals à vague et mi vague*/
$evalCheckboxes = array(
		avis_favorable => "<B>Avis favorable</B>
		<small> (l’activité du chercheur est conforme à ses obligations statutaires)</small>",
		avis_differe => "<B>Avis différé</B>
		<small> (l’évaluation est renvoyée à la session suivante en raison de l’insuffisance ou de l'absence d'éléments du dossier)</small>",
		avis_reserve => "<B>Avis réservé</B>
		<small> (la section a identifié dans l’activité du chercheur un ou plusieurs éléments qui nécessitent un suivi spécifique)</small>",
		avis_alerte => "<B>Avis d'alerte</B>
		<small> (la section exprime des inquiétudes sur l’évolution de l’activité du chercheur)</small>");

/* Pour les renouvellements de gdr ou création d'unités*/
$labelCheckboxes = array(
		avis_tres_favorable => "<B>Avis très favorable</B>",
		avis_favorable => "<B>Avis favorable</B>",
		avis_defavorable => "<B>Avis défavorable</B>",
		avis_reserve => "<B>Avis réservé</B>",
		avis_pas_davis => "<B>Pas d'avis</B>"
);


$typesRapportsToCheckboxesTitles = array(
		REPORT_EVAL => '<B>EVALUATION A VAGUE OU MI-VAGUE DE CHERCHEUR<br/>Avis de la section sur l’activité du chercheur</B>',
		REPORT_EVAL_RE => '<B>EVALUATION DE CHERCHEUR SUITE A REEXAMEN<br/>Avis de la section sur l’activité du chercheur</B>',
		8020 => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
		8021 => '<B>AVIS DE PERTINENCE DU SOUTIEN DU CNRS AUX UNITES</B>',
		REPORT_DELEGATION => '<B>AVIS DE LA SECTION</B>',
		REPORT_ECOLE => '<B>AVIS SUR L\'ECOLE</B>'
);

/* Definition des formaules standards à la fin de certains rapports*/

$typesRapportsToFormula = array();


foreach($typesRapportsPromotion as $type)
  {
    $typesRapportsToFormula[$type][avis_oui] =
      get_config("formule_standard_Promotion_oui", 'La section donne un avis favorable à la demande de promotion.');

    $typesRapportsToFormula[$type][avis_non] =
      get_config("formule_standard_Promotion_non", 'Le faible nombre de possibilités de promotions ne permet malheureusement pas à la Section 6 du Comité National de proposer ce chercheur à la Direction Générale du CNRS pour une promotion cette année.');
  }

$typesRapportsToFormula[REPORT_TITU][avis_tres_favorable] =
  get_config("formule_standard_Titularisation_tres_favorable", 'La section donne un avis très favorable à la titularisation.');

/* Definition des différents grades*/

$grades = array(
		'CR' => 'CR Chargé de Recherche',
		'CR2' => 'CR2 Chargé de Recherche 2ème classe',
		'CR1' => 'CR1 Chargé de Recherche 1ère classe',
		'DR' => 'DR Directeur de Recherche',
		'DR2' => 'DR2 Directeur de Recherche 2ème classe',
		'DR1' => 'DR1 Directeur de Recherche 1ère classe',
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
		'IR1' => 'IR1 Ingénieur de recherche de première classe',
		'IR2' => 'IR2 Ingénieur de recherche de seconde classe',
		'IE1' => 'IE1 Ingénieur d\'étude de première classe',
		'IE2' => 'IE2 Ingénieur d\'étude de seconde classe',
		'DLRG'=>'DLRG',
		'DREM'=>'DREM',
		'AI'=>'AI',
		'CRC'=>'CRC',
		'TCN'=>'TCN'
);

/* permissions levels for actions */
$actions_level = array(
		       "sync_colleges" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		       "fix_missing_data" => NIVEAU_PERMISSION_SECRETAIRE,
		       "check_missing_data" => NIVEAU_PERMISSION_SECRETAIRE,
		       "admindeleteprerapports" => NIVEAU_PERMISSION_SECRETAIRE,
		"synchronize_with_dsi" => NIVEAU_PERMISSION_ACN,
		"synchronize_sessions_with_dsi" => NIVEAU_PERMISSION_ACN,
		"maintenance_on" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		"maintenance_off" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		       //"migrate_to_eval_codes" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		"delete_units" =>NIVEAU_PERMISSION_ACN,
		"set_rapporteur" => NIVEAU_PERMISSION_BUREAU,
		"change_role" => NIVEAU_PERMISSION_BASE,
		       //"migrate" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		"removerubrique" => NIVEAU_PERMISSION_ACN,
		"addrubrique" => NIVEAU_PERMISSION_ACN,
		"removetopic" => NIVEAU_PERMISSION_ACN,
		"addtopic" => NIVEAU_PERMISSION_ACN,
		"updateconfig" => NIVEAU_PERMISSION_ACN,
		"delete" => NIVEAU_PERMISSION_ACN,
		"change_statut" => NIVEAU_PERMISSION_ACN,
		"view" => NIVEAU_PERMISSION_BASE,
		"deleteCurrentSelection" => NIVEAU_PERMISSION_ACN,
		"affectersousjurys" => NIVEAU_PERMISSION_ACN,
		"edit" => NIVEAU_PERMISSION_BASE,
		"read" => NIVEAU_PERMISSION_BASE,
		"upload" => NIVEAU_PERMISSION_ACN,
		"update" => NIVEAU_PERMISSION_BASE,
		"change_current_session" => NIVEAU_PERMISSION_ACN,
		"new" => NIVEAU_PERMISSION_ACN,
		"newpwd" => NIVEAU_PERMISSION_BASE,
		"adminnewpwd" => NIVEAU_PERMISSION_ACN,
		"admin" => NIVEAU_PERMISSION_ACN,
		"admindeleteaccount" => NIVEAU_PERMISSION_ACN,
		"infosrapporteur" => NIVEAU_PERMISSION_ACN,
		"checkpwd" => NIVEAU_PERMISSION_ACN,
		"adminnewaccount" => NIVEAU_PERMISSION_ACN,
		"admindeletesession" => NIVEAU_PERMISSION_ACN,
		"deleteconcours" => NIVEAU_PERMISSION_SUPER_UTILISATEUR,
		"changepwd" => NIVEAU_PERMISSION_BASE,
		"add_concours" => NIVEAU_PERMISSION_ACN,
		"delete_concours" => NIVEAU_PERMISSION_ACN,
		"ajoutlabo" => NIVEAU_PERMISSION_ACN,
		"deletelabo" => NIVEAU_PERMISSION_ACN,
		"mailing" => NIVEAU_PERMISSION_ACN,
		"email_rapporteurs" => NIVEAU_PERMISSION_ACN,
		"trouverfichierscandidats" => NIVEAU_PERMISSION_ACN,
		"creercandidats" => NIVEAU_PERMISSION_ACN,
		"injectercandidats" => NIVEAU_PERMISSION_ACN,
		"displayunits" => NIVEAU_PERMISSION_ACN,
		"displayimportexport" => NIVEAU_PERMISSION_ACN
);

$actions1 = array(
		/*		'details' => array('left' => true, 'title' => "Détails", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),*/
		'edit' => array('left' => true, 'title' => "Modifier", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'', 'icon' => 'img/details-icon-24px.png'),
		/*		'viewpdf' => array('title' => "Voir en PDF", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/pdf-icon-24px.png'),
		 'export&amp;type=text' => array('left' => true, 'title' => "Exporter", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/zip-icon-24px.png')*/
);
$actions2 = array(
		//			'history' => array('title' => "Historique", 'level' => NIVEAU_PERMISSION_SECRETAIRE, 'page' =>'', 'icon' => 'img/history-icon-24px.png'),
		  'delete' => array('title' => "Supprimer", 'level' => NIVEAU_PERMISSION_ACN, 'page' =>'', 'icon' => 'img/delete-icon-24px.png'
,'warning'=>'Etes-vous sûr de vouloir supprimer ce dossier?'),
		//			'viewhtml' => array('title' => "Voir en HTML", 'level' => NIVEAU_PERMISSION_BASE, 'page' =>'export.php', 'icon' => 'img/html-icon-24px.png'),
);
$actions = array_merge($actions1, $actions2);

$fieldsPermissions = array(
		"statut" => NIVEAU_PERMISSION_ACN,
		"concours" => NIVEAU_PERMISSION_ACN,
		"type" => NIVEAU_PERMISSION_ACN,
		"rapporteur" => NIVEAU_PERMISSION_BUREAU,
		"rapporteur2" => NIVEAU_PERMISSION_BUREAU,
		"rapporteur3" => NIVEAU_PERMISSION_BUREAU,
		"avis" => NIVEAU_PERMISSION_ACN,
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
				"name" => "Génération RS",
				"permissionlevel" => NIVEAU_PERMISSION_ACN,
		),
		"html" => 	array(
				"mime" => "text/html",
				"xsl" => "xslt/html2.xsl",
				"name" => "Relecture RS",
				"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE,
		),

		"text" => 	array(
				"mime" => "text/html",
				"xsl" => "xslt/html2.xsl",
				"name" => "Word",
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
		"xlssingle" => 	array(
				"mime" => "application/x-text",
				"xsl" => "",
				"name" => "Excel",
				"permissionlevel" => NIVEAU_PERMISSION_BASE
		),
		/*
		"csvsingle" => 	array(
				"mime" => "application/x-text",
				"xsl" => "",
				"name" => "csv",
				"permissionlevel" => NIVEAU_PERMISSION_BASE
				),*/
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
		"sousjurys" => 	array(
				"mime" => "application/x-text",
				"xsl" => "",
				"name" => "Sections de jury",
				"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE
				      ),


		/*
		"html" => 	array(
				"mime" => "text/html",
				"xsl" => "xslt/html2.xsl",
				"name" => "html(rapports)",
				"permissionlevel" => NIVEAU_PERMISSION_SECRETAIRE,
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
)*/
);

$dont_export_fields = array("id_origine","statut","genre","report_id","people_id","date","auteur","type");
$dont_export_doc_fields = array("id_origine","id","section","nom","prenom","people_nom","people_prenom","statut","genre","report_id","people_id","date","auteur","type","grade");
$export_doc_fields = array("unite","avis","rapporteur","avis1","rapporteur2","avis2","rapporteur3","theme1","theme2","theme3","labo1","labo2","labo3");


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
$sql = "SELECT * FROM ".marmottedbname.".".concours_db." conc JOIN ".dsidbname.".".dsi_GOC." goc ON conc.code=goc.n_public ";
$sql .= " WHERE conc.section='".real_escape_string($_SESSION['filter_section']). "' and conc.session='".real_escape_string($_SESSION['filter_id_session'])."'";
$sql .= ";";

$query = mysqli_query($dbh, $sql) or die("Failed to execute concours query ".$sql.":".mysqli_error($dbh));

$permission_levels = array(
		NIVEAU_PERMISSION_BASE => "rapporteur",
		NIVEAU_PERMISSION_BUREAU => "bureau",
		NIVEAU_PERMISSION_ACN => "ACN",
		NIVEAU_PERMISSION_SECRETAIRE => "secrétaire",
		NIVEAU_PERMISSION_PRESIDENT => "président(e)",
		NIVEAU_PERMISSION_SUPER_UTILISATEUR => "admin"
);


/* Computation of concours I am alloed to see (should be optimized */
if(!isset($_SESSION["myconc"]))
  {
  $sql = "SELECT numconc FROM ".dsidbname.".".dsi_rapp_conc." WHERE emailpro=\"".$_SESSION["login"]."\"";
  $_SESSION["myconc"] =array();
  $result = sql_request($sql);
  while ($row = mysqli_fetch_object($result))
    $_SESSION["myconc"][$row->numconc] = $row->numconc;
  
  $sql  = "SELECT DISTINCT annee FROM ".dsidbname.".".celcc_concours;
  $result = sql_request($sql);
  while ($row = mysqli_fetch_object($result))
    $_SESSION["conc_year"] = $row->annee;


  }

    $my_conc = $_SESSION["myconc"];
$conc_year = isset($_SESSION["conc_year"]) ? $_SESSION["conc_year"] : "2016";

$concours_ouverts = array();
$postes_ouverts = array();
$tous_sous_jury = array();

/* Ugly hack translated from former xml configuration system ... */

while($result = mysqli_fetch_object($query))
{

	$code = $result->code;
  if($_SESSION['permission_mask'] >= NIVEAU_PERMISSION_SECRETAIRE)
     $my_conc[$code] = $code;
  else if(!isset($my_conc[$code]))
	  continue;

	$concours_ouverts[$code] = $result->grade_conc." ".$result->n_public." ".$result->intitule;
	$postes_ouverts[$code] = $result->nb_prop;
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



/* initializes topics */
$topics = get_topics();
foreach($topics as $key => $value)
	$topics[$key] = $key . " - " . $value;

/** FILTERS **/
$filtersReports = array(
		'type' => array('name'=>"Type d'évaluation" , 'liste' =>  $typesRapportsAll , 'default_value' => "tous", 'default_name' => "Tous les types"),
		'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
		'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
		'unite' => array('name'=>"Unite" , 'default_value' => "tous", 'default_name' => ""),
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

$filtersDelegation = array(
		'type' => array('name'=>"Type d'évaluation" , 'liste' => $id_rapport_to_label,'default_value' => "tous", 'default_name' => "Tous les types"),
		'rapporteur' => array('name'=>"Rapporteur" , 'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
		'rapporteur2' => array('name'=>"Rapporteur2" ,'default_value' =>"tous", 'default_name' => "Tous les rapporteurs"),
		'labo1' => array('name'=>"Labo1" , 'default_value' => "tous", 'default_name' => ""),
		'theme1' => array('name'=>"Theme1" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => "tous"),
		'theme2' => array('name'=>"Theme2" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => "tous"),
		'avis' => array('name'=>"Avis Section" , 'liste' => $avis_lettre, 'default_value' => "tous", 'default_name' => ""),
		'avis1' => array('name'=>"Avis Rapp 1" , 'liste' => $avis_lettre, 'default_value' => "tous", 'default_name' => ""),
		'avis2' => array('name'=>"Avis Rapp 2" , 'liste' => $avis_lettre, 'default_value' => "tous", 'default_name' => ""),
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
		'concours' => array('name'=>"Concours" , 'liste' => $tous_concours, 'default_value' => "tous", 'default_name' => ""),
		'statut_celcc' => array('name'=>"Statut candidature" ,
					'liste' => array(
							 "soumis à IE"=>"soumis à IE",
							 "soumis à CS"=>"soumis à CS",
							 "admis à concourir"=>"admis à concourir",
							 "retrait candidature"=>"retrait candidature",
							 "non-admissible"=>"non-admissible",
							 "non-admis"=>"non-admis"
),

'default_value' => "tous", 'default_name' => ""),
		'sousjury' => array('name'=>"Section de jury" , 'liste' => $liste_sous_jurys, 'default_value' => "toutes", 'default_name' => ""),
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
		'type' => array('name'=>"Type d'évaluation" , 'liste' => $typesRapportsConcours,'default_value' => "tous", 'default_name' => ""),
		//'theme3' => array('name'=>"Theme3" , 'liste' => $topics, 'default_value' => "tous", 'default_name' => ""),
		//'labo2' => array('name'=>"Labo2" , 'default_value' => "tous", 'default_name' => ""),
		'id_session' => array('name'=>"Session", 'default_value' =>-1, 'default_name' => "Toutes les sessions"),
		'avancement' => array('name'=>"Avancement" , 'default_value' => "", 'default_name' => ""),
		//'theseloc' => array('name'=>"TheseLoc" , 'liste' => $theseslocs, 'default_value' => "tous", 'default_name' => "Toutes les locs"),
		'id_origine' => array('default_value' =>-1),
		'id' => array('default_value' =>-1),
);

$csv_composite_fields = array(
		'titrenomprenom' => array('','nom','prenom') ,
		'nomprenom' => array('nom','prenom'),
);

$csv_preprocessing = array('nom' => 'normalizeName', 'prenom' => 'normalizeName','unit' => 'fromunittocode');

$sgcn_keywords_to_eval_types = array(
				     "ats DR" => 6011,
				     "ats CR" => 6010,
		"cole th" => 8515,
		"Evaluation" => 6005,
		'Reconstitution' => 6020,
		'Titularisation' => 6015,
		'promotion' => 'Promotion',
		'Changement de direction' => 8101,
		'Changement de section' => 6515,
		'Expertise' => 8104,
		"Renouvellement de GDR" =>  8025,
		"Avis de pertinence sur un projet d'association au CNRS" =>8021,
		"Avis de pertinence sur un renouvellement d'association au CNRS" => 8020,
		"Changement de direction d'unité" => 8101,
		"Suivi" => 6009,
		"Emeritat (renouvellement)" => 7018,
		"Emeritat (1" => 7017,
		"Renouvellement de mise" => 6520,
		"Accueil" => 5505
);

$possible_type_labels = array("Type évaluation", "Type d\'évaluation", "type");

$statuts_concours =
array("IE"=>"IE","JAD"=>"JAD", "auditions"=>"auditions","admissibilite"=>"admissibilité","rapports"=>"rapports", "transmis"=>"transmis");
/************************* Icones *******************************/
$icones_avis = array(
		avis_tres_favorable => "img/Icon-Yes.png",
		"A+" => "img/Icon-Yes.png",
		avis_favorable => "img/Icon-Yes.png",
		"A" => "img/Icon-Yes.png",
		"A-" => "img/Icon-Yes.png",
		avis_admis_a_concourir => "img/Icon-Yes.png",
		avis_oral=>"img/Icon-Yes.png",
		avis_classe=>"img/Icon-Yes.png",
		avis_oui=>"img/Icon-Yes.png",
		avis_IE_oui=>"img/Icon-Yes.png",


		avis_reserve => "img/Icon-NoComment.png",
		avis_desistement => "img/Icon-NoComment.png",
		avis_nonconcur => "img/Icon-NoComment.png",
		avis_differe => "img/Icon-NoComment.png",
		"B+" => "img/Icon-NoComment.png",
		"B" => "img/Icon-NoComment.png",
		"B-" => "img/Icon-NoComment.png",

		avis_defavorable => "img/Icon-No.png",
		avis_non_classe => "img/Icon-No.png",
		avis_nonauditionne => "img/Icon-No.png",
		"C" => "img/Icon-No.png",
		'desistement' => 'img/Icon-No.png',
		"nonauditionne"=>"img/Icon-No.png",
		"nonclasse"=>"img/Icon-No.png",
		"nonconcur"=>"img/Icon-No.png",
		avis_alerte=>"img/Icon-No.png",
		avis_non =>"img/Icon-No.png",
		avis_IE_non =>"img/Icon-No.png",

		avis_pas_davis => "img/Icon-Maybe.png",
		avis_adiscuter => "img/Icon-Maybe.png",
		"" =>"img/Icon-Maybe.png",
);

for($i = 1; $i <= $max_classement; $i++)
	$icones_avis["c".$i] = "img/Icon-Yes.png";

$html_tags = array("i","I","b","B","u","U","sub","SUB","sup","SUP");
?>
