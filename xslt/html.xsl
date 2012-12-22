<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="rapport">


<table cellpadding="5" cellspacing="1" border="1" style="text-align:center;">
	<tr>
		<td>
			<img height="50" width="204" src="img/CN.png"></img>
		</td>
		<td>
		<h1>
		<B>
			RAPPORT DE SECTION
			</B>
			</h1>
		</td>
	</tr>
	<tr>
		<td>
			<B>Section du Comité national : <xsl:value-of select="section_nb"/></B>
		</td>
		<td>
			<B>Session : <xsl:value-of select="session"/></B>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<B>
		Intitulé de la section:
		</B> <xsl:value-of select="section_intitule"/>
		</td>
	</tr>
	<tr>
		<td>
			<B>Objet de l'évaluation:</B><br/> <xsl:value-of select="type"/>
		</td>
		<td>
			<B>Nom, prénom et affectation du chercheur:</B><br/>
			<xsl:value-of select="nom"/><xsl:text> </xsl:text>
			<xsl:value-of select="prenom"/><br/>
			<xsl:value-of select="unite"/>
			
		</td>
	</tr>
</table>
<p><B>Appréciations générales et recommandations de la section :</B><br/>
<small>
<i> Ce rapport a été établi après délibérations de la section, sous la responsabilité de son président, à partir des appréciations du rapporteur, des observations et recommandations de la section.<br/>
Les avis émis par les sections ne préjugent pas de la décision qui sera prise par la direction du CNRS.
</i></small>
</p>

<p>
<xsl:value-of select="avis"/>
</p>

<br/>
<br/>
<br/>
<br/>

<table>
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		Le <xsl:value-of select="date"/>,
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
<tr>
	<td><xsl:text> </xsl:text></td>
	<td>
		<img height="150" width="150" src="img/signature.jpg"></img>
	</td>
</tr>
</table>

</xsl:template>
</xsl:stylesheet>
