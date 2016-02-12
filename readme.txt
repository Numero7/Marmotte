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




1. Déploiement (par le webmaster)
	* cloner le dépôt https://github.com/Numero7/Marmotte.git
	* donner les droits d'écriture au service web dans les sous-dossiers csv, config, uploads, reports
	* éditer le fichier "config/configDB.inc.exemple.php", y renseigner les bonnes valeurs permettant de se connecter à la base de données
		et sauver ce fichier sous le nom "config/configDB.inc.php"	
	* initialiser la base de données à l'aide du script marmotte.sql

2. Première connexion (par le webmaster)
	* se connecter au site avec le login 'admin' et le mot de passe 'password'
	* changer le mot de passe admin (menu admin/utilisateurs)
	* éventuellement, créer des comptes utilsateurs via le menu admin/utilisateurs

3. Synchro
	* Le script sync.sh permet de récupérer les listes de DE depusi la table dsi


4. Prise en main:
      * cf doc du SGCN
      	   http://www.cnrs.fr/comitenational/outils/projet_marmotte.htm

	
