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
<table border="1" cellpadding="10" cellspacing="0" style="text-align:center;">
	<tr >
		<td  width="250" >
			<img height="80" width="80" src="img/CNRSlogo.png"></img>
		</td>
		<td  width="380" valign="center">
		<B>Année <xsl:value-of select="annee_concours" /></B><br/>
		<B>Concours <xsl:value-of select="grade_concours"/></B>
		</td>
	</tr>
	<tr>
	<td colspan="2">
	<br/>
	<B>Concours n° <xsl:value-of select="code_concours"/></B><br/>
	<br/>
		<B>
		RAPPORT DU JURY D’ADMISSIBILITÉ SUR L'ENSEMBLE DES CANDIDATURES<br/>
(à l'issue de la phase d'étude des dossiers)
		</B>
		<br/>
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
		<br/>
		Nombre de candidats qui seront auditionnés: 
					<xsl:value-of disable-output-escaping="yes" select="auditionnes"/>
		</td>
	</tr>
	
		<tr>
	<td colspan="2" style="text-align:left;">
	
<p>
<br/>
Le jury a examiné les dossiers des <xsl:value-of disable-output-escaping="yes" select="examines"/> candidats admis à concourir.
<br/>
<br/>
<xsl:value-of disable-output-escaping="yes" select="avis_jad"/>
<br/>
<br/>
A l'issue de l'examen de l'ensemble des dossiers des candidats admis à concourir,
le jury a décidé d'auditionner les <xsl:value-of disable-output-escaping="yes" select="auditionnes"/> candidats listés ci-dessous. 
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
	<xsl:value-of disable-output-escaping="yes" select="./nom"/>
</td>
	
	<td>
	<xsl:value-of disable-output-escaping="yes" select="./prenom"/>
</td></tr>
</xsl:for-each>
<tr >
<td ><br/><B><br/>Le jury décide d’auditionner :<br/> 
</B>
</td>
</tr>
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
 </td>
 </tr>
 <tr style="text-align:left;">
 <td>
 <B>Date:</B>
 <br/>
 <!-- 
		Le <xsl:value-of disable-output-escaping="yes" select="date_jad"/>
		 -->
		<br/>
		<br/>
		<br/>
		<br/>		<br/>
		
	</td>
	<td>
	<B>Nom et signature du président du jury d'admissibilité:</B>
		<br/>
		<br/>
		<br/>
				<br/>
		<br/>
		<br/>
		<!--
		<xsl:value-of disable-output-escaping="yes" select="signataire"/>,<br/>
		<img height="125" width="125">
		<xsl:attribute name="src"><xsl:value-of select="signature_source"/></xsl:attribute>
		</img>
				-->
		
	</td>
</tr>
</table>

</body>
</html>

</xsl:template>
</xsl:stylesheet>
