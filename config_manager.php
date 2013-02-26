<h2>Config</h2>
<p>Le fichier de config permet de configurer le numéro de la section,
	les thèmes de la section, le nom du président, etc..</p>
<p>
	Télécharger et éditer <a href="<?php echo config_file;?>">le fichier de
		configuration</a>.<br/>
		Pour cela faire (click droit + enregistrer la cible du
	lien sous...) puis éditer le fichier téléchargé avec un éditeur de texte.<br />
	En cas de souci télécharger <a
		href="<?php echo config_file_save;?>">la config de secours</a>.</p>
		<p>
	Après édition, le formulaire ci-dessous permet d'uploader la nouvelle
	configuration.</p>
	<p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="action" value="updateconfig" />
<textarea rows="20" cols="130" name="fieldconfig" ><?php echo get_raw_config(); ?></textarea>
		 <input type="submit" value="Uploader config" />
</form>
</p>
<p>
Le formulaire ci-dessous permet d'uploader la signature du président
sous forme d'un fichier image au format jpeg.
</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="type" value="signature" /> <input
		type="hidden" name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de config: <input
		name="uploadedfile" type="file" /> <input type="submit"
		value="Uploader signature" />
</form>
<p />
