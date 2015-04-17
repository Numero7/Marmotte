<?php
/**
 * Ce fichier contient la définition de la classe Pmsp.
 *
 * PMSP permet à une application hébergée sur un serveur qui n'est pas
 * configuré en fournisseur de service (SP, Service provider Shibboleth)
 * de déporter l'authentification vers un SP existant.
 *
 * @example ../examples/client.php Exemple d'utilisation
 *
 * Auteur:
 *   Roland Dirlewanger, CNRS - Délégation Aquitaine, 2011-2013
 *
 * @package Client
 * @version $Id: Pmsp.php 3 2013-03-25 12:42:20Z rdirlewanger $
 * @author Roland Dirlewanger <Roland.Dirlewanger@dr15.cnrs.fr>
 * @copyright CeCILL C, http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html
 */

/**
 * Définition de la classe Pmsp.
 *
 * La classe Pmsp implémente la partie cliente de PMSP (Poor Man's Service
 * Provider). Elle définit un constructeur permettant d'indiquer l'URL du
 * serveur PMSP, le fichier qui contient la clé publique de ce dernier et
 * l'identifiant de l'application.
 * Elle ne définit qu'une seule méthode publique, authentify, qui permet
 * de requérir du serveur PMSP une authentification Shibboleth.
 *
 * @package Client
 *
 */

class Pmsp {
  /** Les attributs de la classe */
  private $server;		/* URL du serveur PMSP */
  private $pubkey;		/* La clé publique du serveur */
  private $application_id;	/* L'identifiant de l'application */
  private $debug;		/* Niveau de debug */
  private $client_url;		/* L'URL du client */
  private $discover_url;	/* L'URL d'un service de découverte de l'IdP */

  /** Les contantes */
  const DEFAULT_ATTRS = "cn,o,ou,mail";

  /**
   * Le constructeur de la classe.
   *
   * @param string $server URL du serveur PMSP.
   *
   * @param string $pubkey Nom du fichier contenant la clé publique du
   *   serveur PMSP. La signature des données transmises par le serveur
   *   PMSP sera vérifiée à l'aide de la clé contenue dans ce fichier ou
   *   bien de celle contenue dans le fichier $pubkey.old.
   *
   * @param string $application_id Permet de spécifier un identifiant
   *   pour l'application courante. L'identifiant est une chaine de
   *   caractères quelconque, par exemple "Mon application sur tel serveur".
   *   Elle est utilisée en interne par PMSP afin de garantir qu'une
   *   authentification effectuée pour le compte d'une application ayant
   *   application_id=X ne pourra pas être rejouée vers une application
   *   ayant application_id=Y.
   *
   * @param string $client_url Permet de spécifier l'URL du script courant.
   *   La valeur par défaut est celle de la variable SERVER_URI. Il y a des
   *   situations où cette valeur par défaut n'est pas satisfaisante :
   *   1. si l'application est accessible à travers un proxy, auquel cas
   *      SERVER_URI est l'URL de l'application vue par le proxy et non pas
   *      l'URL vue par le navigateur,
   *   2. si le mod_rewrite d'Apache n'est pas activé, alors la variable
   *      SERVER_URI n'est pas initialisée.
   *
   * @param int $debug Spécifie le niveau de debug. S'il est non
   *   nul, les formulaires utilisés dans les différentes phases de
   *   l'authentification s'affichent et doivent s'enchainer
   *   manuellement.
   */
  function __construct($server, $pubkey, $application_id, $client_url = null, $debug = 0) {
    $this->server = $server;
    $this->pubkey = $pubkey;
    $this->application_id = $application_id;
    $this->client_url = $client_url;
    $this->debug = $debug;

    if (empty($this->client_url) && !empty($_SERVER['SCRIPT_URI'])) {
      $this->client_url = $_SERVER['SCRIPT_URI'];
    }
    if (! $this->client_url) {
      throw new Exception("PMSP: impossible de déterminer l'URL du client");
    }
  }

  /**
   * Indique l'URL permettant de sélectionner le founisseur d'identité
   * à utiliser pour identifier la requête.
   *
   * @param $discover_url URL d'un service WAYF ou DS permettant de
   *   sélectionner l'IdP de l'utilisateur
   */
  public function select_idp($discover_url) {
    $this->discover_url = $discover_url;
  }

  /**
   * Renvoie une valeur aléatoire
   *
   * @return Valeur aléatoire calculée avec openssl_random_pseudo_bytes
   *   ou mt_rand si cette fonction n'existe pas
   */
  private function compute_random() {
    if (function_exists("openssl_random_pseudo_bytes")) {
      // existe à partir de PHP 5.3
      return sha1(openssl_random_pseudo_bytes(64));
    } else {
      return mt_rand().mt_rand().mt_rand().mt_rand();
    }
  }

  /**
   * Calcule l'aléa du client.
   *
   * L'aléa est est composé est le hash de deux valeurs : la
   * chaine aléatoire fournie en paramètre et l'identifiant
   * de l'application.
   *
   * @see Pmsp::check_response Pour en savoir plus sur l'utilisation de cet aléa
   *
   * @param string $random Une chaine aléatoire
   * @return string L'aléa du client.
   */
  private function compute_client_random($random) {
    $random .= $this->application_id;
    $random = sha1($random);
    return $random;
  }

  /**
   * Envoie une requête d'identification au serveur PMSP.
   *
   * L'argument $attributes est la liste d'attributs dont le client
   * souhaite obtenir la valeur. Dans le cas où le serveur PMSP dispose
   * de la valeur de cet attribut, il vérifie dans une liste blanche 
   * qu'il est autorisé à renvoyer sa valeur.
   *
   * La requête contient les quatre paramètres pmsp_client_random,
   * pmsp_client_url, pmsp_client_attributes et pmsp_debug.
   *
   * La valeur de pmsp_client_random est l'aléa du client. Il
   * est composé de trois parties : le hash d'une chaine aléatoire
   * de 64 bits, du numéro de la session et de l'adresse IP du
   * client. La première partie est stockée dans la session.
   * (voir la methode check_response() pour l'utilisation de
   * cet aléa).
   *
   * @param string $attributes Liste séparée par des virgules
   *   des attributs dont on demande la valeur au serveur PMSP.
   */
  private function send_request($attributes = self::DEFAULT_ATTRS) {
    // initialise les paramètres transmis au serveur
    $pmsp_client_url = $this->client_url;
    $pmsp_client_attributes = $attributes;
    $pmsp_client_debug = $this->debug;
    $pmsp_client_random = $this->compute_random();

    // écrit l'aléa du client dans la session et rajoute
    // à l'aléa les éléments relatifs à la connexion
    $_SESSION['pmsp_client_random'] = $pmsp_client_random;
    $pmsp_client_random = $this->compute_client_random($pmsp_client_random);

    // fabrique les éléments du formulaire selon qu'on utilise
    // ou pas le service de découverte
    $action = $inputs = "";
    if ($this->discover_url) {
      // on utilise un service de découverte
      $action = $this->discover_url;
      $target =
	"$this->server?".
	"pmsp_client_debug=$pmsp_client_debug&".
	"pmsp_client_random=$pmsp_client_random&".
	"pmsp_client_url=$pmsp_client_url&".
	"pmsp_client_attributes=$pmsp_client_attributes";
      $inputs = "\n      <input type=\"hidden\" name=\"target\" value=\"$target\">\n";
    } else {
      // on n'utilise pas de service de découverte
      $action = $this->server;
      $inputs =
	"\n      <input type=\"hidden\" name=\"pmsp_client_debug\" value=\"$pmsp_client_debug\">\n".
	"      <input type=\"hidden\" name=\"pmsp_client_random\" value=\"$pmsp_client_random\">\n".
	"      <input type=\"hidden\" name=\"pmsp_client_url\" value=\"$pmsp_client_url\">\n".
	"      <input type=\"hidden\" name=\"pmsp_client_attributes\" value=\"$pmsp_client_attributes\">\n";
    }

    // fabrique la page HTML pour la requete
    echo <<<EOT
<html>
  <head>
    <title>PMSP Client</title>
    <script language="JavaScript">
      var debug = $this->debug;

      function submit() {
	if (debug > 0) {
	  var content = window.document.getElementById("pmsp_client_content");
	  content.style.display = 'inline';
          return;
	}
        var form = window.document.getElementById("pmsp_client_form");
        form.submit();
      }
    </script>
  </head>
  <body onload="submit()">
    <div id="pmsp_client_content" style="display: none">
    <h1>PMSP Client</h1>
    <form ID="pmsp_client_form" method="GET" action="$action">
      $inputs
      <input type="submit" value="Authentify me">
    </form>
    </div>
  </body>
</html>
EOT;
    exit(0);
  }

  /**
   * Vérifie la réponse du serveur.
   *
   * La vérification porte sur :
   * - la valeur de la date contenue dans pmsp_signed_expire qui ne doit pas
   *   être dépassée,
   *
   * - la signature des variables POST pmsp_signed_* qui doit être valide
   *   pour au moins une des clés publiques contenues dans le fichier
   *   $this->pubkey ou $this->pubkey.old.
   *
   * - la valeur de la variable de session pmsp_client_random qui doit
   *   être présente
   *
   * - la valeur de la variable POST pmsp_client_random qui doit être égale
   *   au hash de la variable de session pmsp_client_random + l'identifiant
   *   de l'application.
   *
   * Si la vérification échoue, c'est qu'un ou plusieurs des évènements
   * ci-dessous se sont produits entre la demande d'authentification et
   * la réponse :
   * - l'aléa du client est absent de la session : c'est le signe
   *   d'un rejeu d'une authentification PMSP qui concernait soit
   *   la même application (alors que l'authentification est à
   *   usage unique) soit une autre application.
   *
   * - la signature des variables pmsp_signed_* échoue : c'est le
   *   signe que, soit le client ne dispose pas de la clé publique
   *   correspondant à la clé du serveur, soit la réponse du serveur
   *   a été altérée.
   *
   * - l'aléa stocké dans la session a changé : c'est le signe
   *   que l'utilisateur a tenté plusieurs authentifications
   *   simultanées vers la même application ou bien qu'une
   *   attaque par un homme du milieu a remplacé la réponse
   *   attendue par la réponse d'une précédente requête.
   *
   * - l'identifiant de l'application a changé : c'est le signe
   *   du rejeu d'une authentification antérieure vers une autre
   *   application.
   */
  public function check_response() {
    // Vérifie la validité du ticket
    $pmsp_signed_expire = $_POST['pmsp_signed_expire'];
    $now = gmdate("c");
    if ($pmsp_signed_expire < $now) {
      throw new Exception("PMSP: the ticket has expired at $pmsp_signed_expire");
    }

    // Vérifie la validité de l'aléa client
    if (empty($_SESSION['pmsp_client_random'])) {
      throw new Exception("PMSP: no pmsp_client_random in current session");
    }
    $client_random = $this->compute_client_random($_SESSION['pmsp_client_random']);
    if ($client_random != $_POST['pmsp_signed_c_random']) {
      throw new Exception("PMSP: Client random mismatch");
    }

    // Fabrique la chaine à signer
    $data = "";
    $params = preg_grep("/^pmsp_signed_/", array_keys($_POST));
    sort($params);
    foreach ($params as $i => $param) {
      $values[] = "$param=$_POST[$param]";
    }
    $data .= join(":", $values);

    // Vérifie la signature des paramètres
    $signature = base64_decode($_POST['pmsp_server_signature']);
    if (! $this->check_signature($data, $signature, $this->pubkey, true) and
	! $this->check_signature($data, $signature, "$this->pubkey.old", false)) {
      throw new Exception("PMSP: invalid signature");
    }

    // On efface de la session l'aléa du client afin que l'authentification
    // ne soit pas rejouable
    unset( $_SESSION['pmsp_client_random']);
  }

  /**
   * Vérifie la signature de données.
   *
   * @param string $data Les données dont on veut vérifier la signature
   *
   * @param string $signature La signature de ces données
   *
   * @param string $file Le fichier contenant la clé publique
   *
   * @param bool $check_file Indique s'il faut faut retourner une erreur
   *  si le fichier de clé n'existe pas.
   *
   * @return bool Indique si la signature est valide ou pas
   */
  private function check_signature(&$data, &$signature, $file, $check_file) {
    // Récupère la clé publique
    @$fp = fopen($file, "r");
    if (! $fp) {
      if ($check_file) {
	throw new Exception("PMSP: can't open $file");
      } else {
	return false;
      }
    }
			    
    $pub_key = fread($fp, 8192);
    fclose($fp);
    $pkeyid = openssl_pkey_get_public($pub_key);
    if (! $pkeyid) {
      throw new Exception("PMSP: invalid public key in $file");
    }

    // Vérifie la signature
    return openssl_verify($data, $signature, $pkeyid);
  }

  /**
   * Met à jour les valeurs $_SERVER en fonction de la réponse du serveur.
   *
   * Transfère la valeur de la variable POST pmsp_signed_remote_user dans
   * la variable SERVER REMOTE_USER. Idem pour les variables
   * pmsp_signed_attribute_*.
   */
  private function update_server_vars() {
    $_SERVER['REMOTE_USER'] = $_POST['pmsp_signed_remote_user'];
    $params = preg_grep("/^pmsp_signed_attribute_/", array_keys($_POST));
    foreach ($params as $i => $param) {
      $var = preg_replace("/^pmsp_signed_attribute_/", "", $param);
      $_SERVER[$var] = $_POST[$param];
    }
  }

  /**
   * Effectue une authentification PMSP.
   *
   * En cas de succès, met à jour la variable REMOTE_USER et les variables
   * correspondant aux attributs indiqués.
   *
   * @param string $attributs Les attributs souhaités dans la réponse du
   *   du serveur. REMOTE_USER fait partie implicitement de cette liste
   *   et n'a pas besoin d'être spécifié.
   */
  public function authentify($attributs = self::DEFAULT_ATTRS) {
    // ouvre la session, si ce n'est pas déjà fait
    @session_start();

    // s'il n'y a pas de signature, on demande une authentification
    if (empty($_POST['pmsp_server_signature'])) {
    	$this->send_request($attributs);
      return;
    }

    // sinon, on vérifie la réponse du serveur et on repasse la main
    // à l'application
    $this->check_response();
    $this->update_server_vars();

    // Dans le mode DEBUG, on affiche les valeurs des variables
    // mises à jour.
    if ($this->debug > 0) {
      // récupère la liste des valeurs des variables à afficher
      $expr = $_POST['pmsp_signed_attributes'];
      $expr = str_replace(',', '|', $expr);
      $expr .= '|REMOTE_USER';

      // affiche les variables et leur valeur
      Header("Content-Type: text/plain");
      $vars = preg_grep("/^($expr)$/", array_keys($_SERVER));
      foreach ($vars as $i => $var) {
	echo "$var = $_SERVER[$var]\n";
      }
      echo "\n";

      // affiche les informations sur la session et le cookie
      $session = session_name();
      $cookie = session_get_cookie_params();
      echo "Paramètres du cookie = ".print_r($cookie, true)."\n";
      echo "Nom de la session = $session\n";
      echo "Contenu de la session = ".print_r($_SESSION, true)."\n";

      exit(0);
    }
  }
}
