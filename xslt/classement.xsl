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
				<table cellpadding="5" cellspacing="1" border="1"
					style="border-collapse:collapse;text-align:center;">
					<tr>
						<td width="50%">
							<img height="80" width="80" src="img/CNRSlogo.png"></img>
						</td>
						<td width="50%" style="padding-top:20px;">
											<table style="padding-top:20px;">
						<tr><td>
							<B>Année 2013</B>
							<br />
							<B>
								Concours
								<xsl:value-of select="grade_concours" />
							</B>
							</td></tr></table>
						</td>
					</tr>
					<tr style="border-bottom: none;">
						<td colspan="2">
						<table>
					<tr style="text-align:center;">
						<td colspan="2">
							<br />
							<B>
								Concours n°
								<xsl:value-of select="concours" />
							</B>
							<br />
							<br />
							<B>
							RAPPORT DU JURY D’ADMISSIBILITÉ SUR LA CANDIDATURE DE
								</B>
							<br />
						</td>
					</tr>
					<tr style="text-align:left;" >
						<td width="50%">
							<B>NOM : </B>
							<xsl:value-of disable-output-escaping="yes" select="nom" />
						</td>
						<td width="50%">
							<B>PRENOM : </B>
							<xsl:value-of disable-output-escaping="yes" select="prenom" />
						</td>
					</tr>
					</table>
					</td>
					</tr>
					<tr style="text-align:center;">
						<td colspan="2">
							<B>Candidat classé n° <xsl:value-of disable-output-escaping="yes" select="avis" />
							 </B>
						</td>
					</tr>
					
					<tr style="text-align:left;padding-top:10px;">
					<td colspan = "2" height="520" >

				<p>
					<xsl:value-of disable-output-escaping="yes" select="rapport" />
				</p>

</td></tr>
<tr style="text-align:left;"><td height="120">
										<B><FONT SIZE="-1">
											Nom et signature du président du jury d'admissibilité :
										<xsl:value-of disable-output-escaping="yes"
											select="signataire" />
										</FONT>
										</B>
									</td>
									<td>
									<B>Date :</B>
		le <xsl:value-of disable-output-escaping="yes" select="date"/>.
									</td>
								</tr>
							</table>
			</body>

</html>

</xsl:template>
</xsl:stylesheet>
			