<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" omit-xml-declaration="yes" indent="yes" encoding="UTF-8"/>
<xsl:template match="/">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<meta name="description" content="description"/>
	<meta name="keywords" content="keywords"/> 
	<meta name="author" content="author"/> 
	<link rel="stylesheet" type="text/css" href="default.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="cn.css" media="all"/>
	<link rel="stylesheet" type="text/css" href="cseprint.css" media="print"/>  
	<title>Edition des rapports de section</title>
</head>
<body class="full">
	<div class="full">
	<div class="content"> 
	<xsl:for-each select="rapports/rapport">
	<div class="rapportshort"><a name="anchor{@id_origine}"></a>
		<div class="typerapportshort">
			<xsl:value-of select="type"/><br/>
			par <xsl:value-of select="rapporteur"/> (ID#<xsl:value-of select="@id"/>/<xsl:value-of select="@id_origine"/>)
		</div>
		<div class="identiterapportshort">
			<xsl:if test="(prenom!='') or (nom!='') or (grade!='')">
				<xsl:value-of select="prenom"/>&#xA0;<span style="font-variant: small-caps;"><xsl:value-of select="nom"/></span> (<xsl:value-of select="grade"/>) -
			</xsl:if>
			<xsl:value-of select="unite"/><br/>
		<xsl:if test="(anciennete_grade!='')">Ancienneté grade : <xsl:value-of select="anciennete_grade"/></xsl:if>
		<xsl:if test="(anciennete_grade!='') or (date_recrutement!='')"> - </xsl:if>
		<xsl:if test="(date_recrutement!='')">		
		Recr. CNRS en <xsl:value-of select="date_recrutement"/>
		</xsl:if>
		</div>
		<div class="clearer"></div>
		<div class="prerapportrapportshort">
			<span class="evaltitlerapportshort">Pré-rapport : </span><xsl:value-of select="prerapport"/>
		</div>
		<xsl:if test="(production!='') or (production_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Production <xsl:value-of select="production"/> : </span> <xsl:value-of select="production_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(transfert!='') or (transfert_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Transfert <xsl:value-of select="transfert"/> : </span> <xsl:value-of select="transfert_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(encadrement!='') or (encadrement_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Encadrement <xsl:value-of select="encadrement"/> : </span> <xsl:value-of select="encadrement_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(responsabilites!='') or (responsabilites_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Responsabilités <xsl:value-of select="responsabilites"/> : </span> <xsl:value-of select="responsabilites_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(mobilite!='') or (mobilite_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Mobilité <xsl:value-of select="mobilite"/> : </span> <xsl:value-of select="mobilite_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(animation!='') or (animation_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Animation <xsl:value-of select="animation"/> : </span> <xsl:value-of select="animation_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(rayonnement!='') or (rayonnement_notes!='')">
		<div class="evalrapportshort">
			<span class="evaltitlerapportshort">Rayonnement <xsl:value-of select="rayonnement"/> : </span> <xsl:value-of select="rayonnement_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(../@id_edit=@id_origine)">
			<form action="export.php#anchor{@id_origine}" method="post">
				<span class="evaltitlerapportshort">Avis :</span> 
				<input name="avis" value="{avis}" /><br/>
				<span class="evaltitlerapportshort">Rapport  : </span>
				<textarea name="rapport"><xsl:value-of select="rapport"/></textarea><br/>
				<input type="hidden" name="action" value="group"/>
				<input type="hidden" name="save" value="{@id_origine}"/>
				<input type="hidden" name="id_edit" value="-1"/>
				<input type="hidden" name="id_session" value="{../@id_session}"/>
				<input type="hidden" name="type_eval"  value="{../@type_eval}"/>
				<input type="hidden" name="sort_crit"  value="{../@sort_crit}"/>
				<input type="hidden" name="login_rapp" value="{../@login_rapp}"/>
				<input type="hidden" name="type" value="htmlmin"/>
				<input type="submit" value="Sauvegarder"/>
			</form>
		</xsl:if>
		<xsl:if test="(../@id_edit!=@id_origine)">
			<div class="rapportrapportshort">
			<span class="evaltitlerapportshort">Avis :</span> <xsl:value-of select="avis"/><br/>
			<span class="evaltitlerapportshort">Rapport  : </span><xsl:value-of select="rapport"/>
			<form action="export.php#anchor{@id_origine}"  method="post">
				<input type="hidden" name="action" value="group"/>
				<input type="hidden" name="id_edit" value="{@id_origine}"/>
				<input type="hidden" name="id_session" value="{../@id_session}"/>
				<input type="hidden" name="type_eval"  value="{../@type_eval}"/>
				<input type="hidden" name="sort_crit"  value="{../@sort_crit}"/>
				<input type="hidden" name="login_rapp" value="{../@login_rapp}"/>
				<input type="hidden" name="type" value="htmlmin"/>
				<input type="submit" value="Editer"/>
			</form>
			</div>
		</xsl:if>
	</div>
	</xsl:for-each>
	</div>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>