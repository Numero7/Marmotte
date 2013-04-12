*****************************************************************************************
*****************************************************************************************
**                                                                                     **
**   Le logiciel Marmotte a pour but de faciliter la vie des sections du comité        **
**   national en général et de leurs secrétaires scientifiques en particulier.         **
**   Ce site web permet de gérer tout le cycle de vie des rapports,                    **
**   depuis l'attribution des rapporteurs jusqu'à la génration des pdfs                **
**   intégralement en ligne et évite tout échange d'emails et de fichiers.             **
**                                                                                     ** 
**                                                                                     **
**   Le logiciel Marmotte a été créé par Hugo Gimbert et Yann Ponty                    **
**   et est mis à disposition des sections du comité national                          **
**   selon les termes de la licence Creative Commons Attribution                       **
**   Pas d’Utilisation Commerciale - Partage dans les Mêmes Conditions 3.0 France.     **
**                                                                                     **
**                                                                                     **
*****************************************************************************************
*****************************************************************************************



Démarrage en trois étapes.

1. Déploiement (par le webmaster)
	* dézipper l'archive
	* donner les droits d'écriture au service web dans les sous-dossiers csv, config, uploads, reports
	* créer un fichier vierge nommé .htpasswd à la racine du site avec droits d'écriture pour le service web
	* éditer le fichier config/configDB.inc.php et y renseigner les bonnes valeurs permettant de se connecter à la base de données
	* initialiser la base de données à l'aide du script marmotte.sql

2. Première connexion (par le webmaster)
	* se connecter au site avec le login 'admin' et le mot de passe 'password'
	* changer le mot de passe admin (menu admin/utilisateurs)
	* créer un compte avec les logins et mot de passe choisi par le secrétaire (menu admin/utilisateurs/crétaion nouveau rapporteur)
	* donner au nouveau compte les privilèges de secrétaire (menu admin/utilisateurs/statut des membres) 

2. Configuration initiale (par le secrétaire)
	* se connecter au site et cliquer sur Admin
	* utiliser le menu "utilisateurs/création nouveau rapporteur" pour ajouter à la base les membres de la section
	* donner au président les privilèges de président et au membres du bureau les privilèges de membres du bureau (menu admin/utilisateurs/statut des membres) 
	* configurer les infos propores à la section (menu admin/configurations) 
	
	

	