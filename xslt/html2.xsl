<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="rapport">


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
			<B>Session : <xsl:value-of select="session"/></B>
		</td>
	</tr>
	<tr>
		<td colspan="2" width="600">
		<B>
		Intitulé de la section:
		</B> <xsl:value-of select="section_intitule"/>
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
<xsl:value-of select="rapport"/>
</p>

</td>
</tr>

<tr>
<td>
<table>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		Le <xsl:value-of select="date"/>,
	</td>
	<td rowspan="3">
		<img height="150" width="150" src="img/signature.jpg"></img>
	</td>
	
</tr>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		<xsl:value-of select="signataire"/>,
	</td>
</tr>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		<xsl:value-of select="signataire_titre"/>.
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
<xsl:if test="position()!=last()"><hr/></xsl:if>
</xsl:template>
</xsl:stylesheet>
