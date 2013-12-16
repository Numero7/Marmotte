<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">



<xsl:template match="rapport">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
</head>

			<body>


<table cellpadding="5" cellspacing="1" border="1" style="text-align:center;">
	<tr>
		<td  width="250">
			<img height="50" width="204" src="img/CN.png"></img>
		</td>
		<td  width="350">
		<h1>
		<B>
			RAPPORT DE SECTION
			</B>
			</h1>
		</td>
	</tr>
	<tr>
		<td width="250">
			<B>Section du Comité national : <xsl:value-of select="section_nb"/></B>
		</td>
		<td width="350">
			<B>Session : <xsl:value-of disable-output-escaping="yes" select="session"/></B>
		</td>
	</tr>
	<tr>
		<td colspan="2" width="600">
		<B>
		Intitulé de la section:
		</B> <xsl:value-of disable-output-escaping="yes" select="section_intitule"/>
		</td>
	</tr>
	<tr>
		<td  width="250">
			<xsl:value-of disable-output-escaping="yes" select="entetegauche"/>
		</td>
		<td  width="350">
			<xsl:value-of disable-output-escaping="yes" select="entetedroit"/>
		</td>
	</tr>
</table>

<table>
<tr>
<xsl:text> </xsl:text>
<br/>
<td  height="400">
<p><B>Appréciations générales et recommandations de la section :</B><br/>
<small>
<i> Ce rapport a été établi après délibérations de la section, sous la responsabilité de son président, à partir des appréciations du rapporteur, des observations et recommandations de la section.<br/>
Les avis émis par les sections ne préjugent pas de la décision qui sera prise par la direction du CNRS.
</i></small>
</p>




<p>
<span style="text-align:justify;">
<xsl:value-of disable-output-escaping="yes" select="rapport"/>
</span>
</p>

</td>
</tr>
<tr>
<td>
<br/>
<br/>
<table>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		Le <xsl:value-of disable-output-escaping="yes" select="date"/>,
	</td>
	<td rowspan="3">
	<img height="120" width="120">
	<xsl:attribute name="src"> <xsl:value-of disable-output-escaping="yes" select="signature_source"/>
	</xsl:attribute>
	</img>
	</td>
	
</tr>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		<xsl:value-of disable-output-escaping="yes" select="signataire"/>,
	</td>
</tr>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		<xsl:value-of disable-output-escaping="yes" select="signataire_titre"/>.
	</td>
</tr>
</table>

<xsl:if test="checkboxes">
	<table cellpadding="2" cellspacing="1" border="1" style="text-align:left;">
		<tr>
			<td colspan="2" width="665">
				<xsl:value-of  disable-output-escaping="yes" select="checkboxes/@titre"/>
			</td>
		</tr>
		<xsl:for-each select="checkboxes/checkbox">
			<tr>
				<td width="25">
					<xsl:if test="./@mark = 'checked'"><B>X</B></xsl:if>
					<xsl:if test="./@mark != 'checked'"><xsl:text> </xsl:text></xsl:if>
				</td>
				<td width="640">
				<xsl:text> </xsl:text>
					<xsl:value-of disable-output-escaping="yes" select="."/>
				</td>
			</tr>
		</xsl:for-each>
	</table>
</xsl:if>
</td></tr>
</table>

</body>

</html>

</xsl:template>
</xsl:stylesheet>
