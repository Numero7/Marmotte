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
	<xsl:for-each select="rapports">
	<xsl:for-each select="rapport">
	<a href="index.php?action=view&amp;id_session={../@id_session}&amp;type_eval={../@type_eval}&amp;sort_crit={../@sort_crit}&amp;login_rapp={../@login_rapp}">Retour à l'interface principale</a>
	<div class="rapportshort"><a name="anchor{@id_origine}"></a>
		<div class="type">
			<xsl:value-of select="type"/> <xsl:if test="(concours!='')">
			<span class="concours"><xsl:value-of select="concours"/></span>
			</xsl:if>		
<br/>
			par <xsl:value-of select="rapporteur"/><br/> (ID# <xsl:value-of select="@id"/>/<xsl:value-of select="@id_origine"/>)
		</div>
		<div class="identite">
			<xsl:if test="(ecole!='')">
			<span class="ecole"><xsl:value-of select="ecole"/></span>
			</xsl:if>		
			<xsl:if test="(prenom!='') or (nom!='') or (grade!='')">
				<span class="prenom"><xsl:value-of select="prenom"/></span>&#xA0;<span class="nom"><xsl:value-of select="nom"/></span> (<xsl:value-of select="grade"/>) -
			</xsl:if>
			<xsl:value-of select="unite"/>
			<div class="subidentite">
		<xsl:if test="(anciennete_grade!='')">Ancienneté grade : <xsl:value-of select="anciennete_grade"/></xsl:if>
		<xsl:if test="(anciennete_grade!='') or (date_recrutement!='')"> - </xsl:if>
		<xsl:if test="(date_recrutement!='')">		
		Recrutement CNRS en <xsl:value-of select="date_recrutement"/>
		</xsl:if></div>
		</div>
		<div class="prerapport">
			<span class="evaltitle">Points marquants : </span><xsl:value-of select="prerapport"/>
		</div>
		<xsl:if test="(../@id_edit=@id_origine)">
			<div class="rapport">
			<form action="export.php#anchor{@id_origine}" method="post">
				<span class="evaltitle">Rapport </span>
				<textarea name="rapport" id="rapport"><xsl:value-of select="rapport"/></textarea><br/>
				<span class="evaltitle">Avis/Classement </span> 
				<input name="avis" value="{avis}" id="avis"/>				
				<input type="hidden" name="action" value="group"/>
				<input type="hidden" name="save" value="{@id_origine}"/>
				<input type="hidden" name="id_edit" value="-1"/>
				<input type="hidden" name="id_session" value="{../@id_session}"/>
				<input type="hidden" name="type_eval"  value="{../@type_eval}"/>
				<input type="hidden" name="sort_crit"  value="{../@sort_crit}"/>
				<input type="hidden" name="login_rapp" value="{../@login_rapp}"/>
				<input type="hidden" name="type" value="htmledit"/><br/>
				<input type="submit" value="Sauvegarder"/>
				<input type="submit" name="cancel" value="Annuler"/>
			</form>
			</div>
		</xsl:if>
		<xsl:if test="(../@id_edit!=@id_origine)">
			<div class="rapport">
			<span class="evaltitle">Rapport  : </span><xsl:value-of select="rapport"/><br/>
			<span class="evaltitle">Avis/Classement : </span> <xsl:value-of select="avis"/>
			<form action="export.php#anchor{@id_origine}"  method="post">
				<input type="hidden" name="action" value="group"/>
				<input type="hidden" name="id_edit" value="{@id_origine}"/>
				<input type="hidden" name="id_session" value="{../@id_session}"/>
				<input type="hidden" name="type_eval"  value="{../@type_eval}"/>
				<input type="hidden" name="sort_crit"  value="{../@sort_crit}"/>
				<input type="hidden" name="login_rapp" value="{../@login_rapp}"/>
				<input type="hidden" name="type" value="htmledit"/>
				<input type="submit" value="Editer"/>
			</form>
			</div>
		</xsl:if>		
		<xsl:if test="(production!='') or (production_notes!='')">
		<div class="eval">
			<span class="evaltitle">Production <xsl:value-of select="production"/><xsl:if test="(production_notes!='')"> : </xsl:if></span> <xsl:value-of select="production_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(transfert!='') or (transfert_notes!='')">
		<div class="eval">
			<span class="evaltitle">Transfert <xsl:value-of select="transfert"/><xsl:if test="(transfer_notes!='')"> : </xsl:if></span> <xsl:value-of select="transfert_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(encadrement!='') or (encadrement_notes!='')">
		<div class="eval">
			<span class="evaltitle">Encadrement <xsl:value-of select="encadrement"/><xsl:if test="(encadrement_notes!='')"> : </xsl:if></span> <xsl:value-of select="encadrement_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(responsabilites!='') or (responsabilites_notes!='')">
		<div class="eval">
			<span class="evaltitle">Responsabilités <xsl:value-of select="responsabilites"/><xsl:if test="(responsablites_notes!='')"> : </xsl:if></span> <xsl:value-of select="responsabilites_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(mobilite!='') or (mobilite_notes!='')">
		<div class="eval">
			<span class="evaltitle">Mobilité <xsl:value-of select="mobilite"/><xsl:if test="(mobilite_notes!='')"> : </xsl:if></span> <xsl:value-of select="mobilite_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(animation!='') or (animation_notes!='')">
		<div class="eval">
			<span class="evaltitle">Animation <xsl:value-of select="animation"/><xsl:if test="(animation_notes!='')"> : </xsl:if></span> <xsl:value-of select="animation_notes"/><br/>
		</div>
		</xsl:if>
		<xsl:if test="(rayonnement!='') or (rayonnement_notes!='')">
		<div class="eval">
			<span class="evaltitle">Rayonnement <xsl:value-of select="rayonnement"/><xsl:if test="(rayonnement_notes!='')"> : </xsl:if></span> <xsl:value-of select="rayonnement_notes"/><br/>
		</div>
		</xsl:if>
	</div>
	</xsl:for-each>
	<a href="index.php?action=view&amp;id_session={@id_session}&amp;type_eval={@type_eval}&amp;sort_crit={@sort_crit}&amp;login_rapp={@login_rapp}">Retour à l'interface principale</a>
	</xsl:for-each>
	</div>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>