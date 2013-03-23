<h3>Fichier de config</h3>

<p>Le <a href="<?php echo config_file;?>"> fichier de configuration</a> permet de configurer le numéro de la section,
	les thèmes de la section, le nom du président, etc..</p>
<p>
Il est éditable via la fenêtre ci-dessous et le bouton "Mettre à jour config".<br/>
Vous pouvez également éditer le
<a href="<?php echo config_file;?>"> fichier de configuration</a>
avec un éditeur de texte, puis le copier-coller dans la fenêtre ci-dessous.<br />
	<p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="action" value="updateconfig" />
<textarea rows="20" cols="130" name="fieldconfig" ><?php echo get_raw_config(); ?></textarea>
		 <input type="submit" value="Mettre à jour config" />
</form>
</p>
<h3>Signature président</h3>
<p>
Le formulaire ci-dessous permet d'uploader la signature du président
sous forme d'un fichier image au format jpeg.
</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="type" value="signature" /> <input
		type="hidden" name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de signature: <input
		name="uploadedfile" type="file" /> <input type="submit"
		value="Uploader signature" />
</form>
<p />
