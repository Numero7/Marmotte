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
	* cloner le dépôt https://github.com/Numero7/Marmotte.git
	* donner les droits d'écriture au service web dans les sous-dossiers csv, config, uploads, reports
	* créer un fichier vierge nommé .htpasswd à la racine du site avec droits d'écriture pour le service web
	* éditer le fichier "config/configDB.inc.exemple.php", y renseigner les bonnes valeurs permettant de se connecter à la base de données
		et sauver ce fichier sous le nom "config/configDB.inc.php"	
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
	
	
Prise en main:


* A propos du "statut" des rapports:
	Le statut des rapports sélectionnés peut être modifié par le menu déroulant "Statut" en haut à droite de la page d'accueil 
	Tous les rapports sont visibles par le secrétaire et le président.
	Les membres du bureau peuvent changer les rapporteurs affectés à un rapport.
	Tous les rapports qui n'ont pas le statut "publie" sont editables par le secrétaire et le président.
	Tous les rapports sont visibles par tous les membres de la section excepté pour les rapports avec le statut "vierge" ou "prerapport" et qui ont deux rapporteurs attribués,
		dans ce cas un rapporteur ne voit pas le prerapport de l'autre rapporteur.
	Tous les rapports avec le statut "vierge", "prerapport" ou "editable" sont éditables par les rapporteurs, ainsi que le secrétaire et le président.
	Statut "audition": spécifique aux concours, les rapporteurs peuvent éditer les champs correspondant au rapport d'audition.

	Pour résumer:
	Entre le bureau et la session: utilisr le statut "vierge" ou "prerapport", les prerapports écrits en double aveugle par les rapporteurs.
	Pendant la session: utiliser le mode "rapport". Le secrétaire a la main sur le rapport de la section. Si besoin basculer en mode "editable" pour que les rapporteurs complètent leurs prerapports.
	Apres la session et la relecture/correction par le président: basculer en mode "publie" et générer les pdf.

* A propos de l'import des fichiers du SGCN:
	Demander à votre ACN des extractions au format csv. Si elle ne connaît pas la procédure, elle doit se rapprocher de Florence Colombo et Laurent Chazaly.

	