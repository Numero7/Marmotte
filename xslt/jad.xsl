<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">



<xsl:template match="jad">


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
</head>

<body>
<table cellpadding="5" cellspacing="1" border="1" style="text-align:center;" border-collapse="collapse">
	<tr>
		<td  width="250">
			<img height="80" width="80" src="img/CNRSlogo.png"></img>
		</td>
		<td  width="350">
		<B>Année 2013</B><br/>
		<B>Concours <xsl:value-of select="grade_concours"/></B>
		</td>
	</tr>
	<tr>
	<td colspan="2">
	Concours n° <xsl:value-of select="code_concours"/><br/>
		<B>
		RAPPORT DU JURY D’ADMISSIBILITÉ SUR L'ENSEMBLE DES CANDIDATURES<br/>
(à l'issue de la phase d'étude des dossiers)
		</B>
		</td>
	</tr>
	<tr>
		<td  width="250">
		Nombre de postes ouverts:
			<xsl:value-of disable-output-escaping="yes" select="postes_ouverts"/>
		</td>
		<td>
		Nombre de candidatures examinées:
					<xsl:value-of disable-output-escaping="yes" select="examines"/>
		<br/>
		Nombre de candidats qui seront auditionnés: 
					<xsl:value-of disable-output-escaping="yes" select="auditionnes"/>
		</td>
	</tr>
</table>

<p>
<br/>
<br/>
<xsl:value-of disable-output-escaping="yes" select="avis_jad"/>
<br/>
<br/>
</p>
<p>
</p>

<p>
<B>
Le jury a examiné les candidatures de :
</B>
</p>
<table>
<xsl:for-each select="candidats/candidat">
<tr><td>
<B>
	<xsl:value-of disable-output-escaping="yes" select="./nom"/>
	</B></td>
	
	<td>
	<xsl:value-of disable-output-escaping="yes" select="./prenom"/>
</td></tr>
</xsl:for-each>
</table>

<p>
<B>Le jury décide d’auditionner : 
</B>
</p>

<table>
<xsl:for-each select="admissibles/candidat">
<tr><td>
<B>
	<xsl:value-of disable-output-escaping="yes" select="./nom"/>
</B>
	</td>
	<td>
	<xsl:value-of disable-output-escaping="yes" select="./prenom"/>
</td>
</tr>
</xsl:for-each>
</table>

<!-- 
<table cellpadding="5" cellspacing="1" border="1" border-collapse="collapse">
<tr>
<td>
<B>Date:</B>
<xsl:value-of disable-output-escaping="yes" select="date"/>
</td>
<td>
<B>Nom et signature du président du jury d’admissibilité:</B><br/>
<xsl:value-of disable-output-escaping="yes" select="signataire"/><br/>
<xsl:value-of disable-output-escaping="yes" select="signataire_titre"/><br/>
		<img height="150" width="150">
		<xsl:attribute name="src"><xsl:value-of select="signature_source"/></xsl:attribute>
		</img>
</td>
</tr>

</table>
 -->
 <p>
<table>
<tr>
	<td>
		Le <xsl:value-of disable-output-escaping="yes" select="date"/>,<br/>
		<xsl:value-of disable-output-escaping="yes" select="signataire"/>,<br/>
		<xsl:value-of disable-output-escaping="yes" select="signataire_titre"/>.<br/>
	</td>
	<td>
		<img height="125" width="125">
		<xsl:attribute name="src"><xsl:value-of select="signature_source"/></xsl:attribute>
		</img>
	</td>
</tr>
</table>
</p>

</body>
</html>

</xsl:template>
</xsl:stylesheet>
