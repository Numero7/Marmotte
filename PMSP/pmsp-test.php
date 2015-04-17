<?php
  //
  // Exemple de client PMSP.
  //
  // $Id: pmsp-test.php 3 2013-03-25 12:42:20Z rdirlewanger $
  //
require 'Pmsp.php';

# Indiquez ci-desous l'URL de ce script sur votre site WWW ou null
# si vous voulez laisser PMSP déterminer cet URL (nécessite le
# mod_rewrite et RewriteEngine=on)
$client = "http://127.0.0.1";

# Indiquez ci-dessous l'URL du service de découverte (WAYF
# ou DS) de l'IdP de l'utilisateur ou null si vous ne souhaitez pas
# utiser cette fonctionnalité
#$wayf = "https://mon-sp.mon-domaine.fr/Shibboleth.sso/WAYF";
$wayf = null;

# Indiquez ci-dessous le fichier contenant la clé publique du
# serveur PMSP
$pubkey = "/etc/pmsp/pmsp.pub";

# Indiquez ci-dessous l'URL du serveur PMSP
$server = "https://vigny.dr15.cnrs.fr/secure/pmsp-server.php";
$debug = 1;

# Indiquez ci-dessous le nom de l'application
$appid = "Test de PMSP";

try {
  # Fabrique un objet PMSP
  $pmsp = new Pmsp($server, $pubkey, $appid, $client, $debug);

  # Indique si nécessaire le service de découverte
  if ($wayf) {
    $pmsp->select_idp($wayf);
  }

  # Effectue l'authentification
  $pmsp->authentify('mail,cn,ou,givenname,displayname');
} catch (Exception $e) {
  Header("Content-type: text/plain");
  echo $e->getMessage();
  echo "\n";
  echo $e->getTraceAsString();
  exit (0);
}

$user = $_SERVER['REMOTE_USER'];
$cn = $_SERVER['cn'];
?>
<html>
  <head><title>PMSP test</title></head>

  <body>
  Welcome to <?php echo $cn ?>, uid=<?php echo $user ?>
  </body>
</html>


    
