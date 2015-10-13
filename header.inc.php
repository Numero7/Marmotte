<?php header('Content-type: text/html; charset=utf-8');?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="description" content="description" />
<meta name="keywords" content="keywords" />
<meta name="author" content="author" />
<link rel="stylesheet" type="text/css" href="default.css" media="screen" />
<link rel="stylesheet" type="text/css" href="cn.css" media="all" />
<link rel="stylesheet" type="text/css" href="cseprint.css" media="print" />
<title>Marmotte</title>
<script type='text/javascript' src='js/jquery.min.js'></script>
</head>

<script>
   $(document).ready(function() {
       $('.sproperty').change(function() {
	   $(this).css({"color" : "#FF0000"});
	$.post( 
		  "action.php",
		  $(this).parent().serialize()
		)
	  	  	  .done(function(data) {

			    })
	 .fail(function(jqXHR, textStatus, errorThrown) {
       alert( "Erreur, impossible d'enregistrer le rapporteur: " + errorThrown);
	   });
	 });
});
</script>
<body>
