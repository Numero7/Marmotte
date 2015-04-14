<?php
/**
 * Exemple de client PMSP.
 *
 * Auteur:
 *   Roland Dirlewanger, CNRS - Délégation Aquitaine, 2011-2013
 *
 * @package Examples
 * @version $Id: client.php 3 2013-03-25 12:42:20Z rdirlewanger $
 * @author Roland Dirlewanger <Roland.Dirlewanger@dr15.cnrs.fr>
 * @copyright CeCILL C, http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html
 * 
 */
require 'Pmsp.php';

# Indiquez ci-dessous l'URL de ce script sur votre site WWW ou null
# si vous voulez laisser PMSP déterminer cet URL (nécessite le
# mod_rewrite et RewriteEngine=on)
$client = null;

# Indiquez ci-dessous l'URL du service de découverte (WAYF
# ou DS) de l'IdP de l'utilisateur ou null si vous ne souhaitez pas
# utiser cette fonctionnalité
#$wayf = "https://monsp.mon-domaine.fr/Shibboleth.sso/WAYF";
$wayf = null;

# Indiquez ci-dessous le fichier contenant la clé publique du
# serveur PMSP
$pubkey = "/etc/pmsp/pmsp.pub";

# Indiquez ci-dessous l'URL du serveur PMSP
$server = "https://monsp.mon-domaine.fr/secure/pmsp-server.php";

# Indiquez ci-dessous l'identifiant de l'application
$appid = "Exemple d'utilisation de PMSP"

# Indiquez ci-dessous la liste des attributs demandés au
# serveur PMSP
$attributs = 'mail,ou,cn,sn,givenName,displayname';

if (/* l'utilisateur n'est pas encore authentifié */) {
  try {
    # Fabrique un objet PMSP
    $pmsp = new Pmsp($server, $pubkey, $appid, $client);

    # Indique si nécessaire le service de découverte
    if ($wayf) {
      $pmsp->select_idp($wayf);
    }

    # Effectue l'authentification
    $pmsp->authentify($attributs);
  } catch (Exception $e) {
    Header("Content-type: text/plain");
    echo $e->getMessage();
    echo "\n";
    echo $e->getTraceAsString();
    exit (0);
  }

  #
  # $_SERVER[$attr] contient les valeurs de tous les attributs
  # indiqués dans la liste $attributes, sauf ceux que le
  # fournisseur de service qui héberge le serveur PMSP
  # n'a pas obtenu via Shibboleth. L'attribut REMOTE_USER est
  # automatiquement ajouté à la liste.
  # 
  $user = $_SERVER['REMOTE_USER'];
  $cn = $_SERVER['cn'];
}
