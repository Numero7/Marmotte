<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">



	<xsl:template match="rapport">


		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
			<head>
				<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
			</head>

			<body>
				<table cellpadding="5" cellspacing="1" border="1"
					style="border-collapse:collapse;text-align:center;">
					<tr>
						<td width="250">
							<img height="80" width="80" src="img/CNRSlogo.png"></img>
						</td>
						<td width="350" style="padding-top:20px;">
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
					<tr>
						<td colspan="2">
							<br />
							<B>
								Concours n°
								<xsl:value-of select="concours" />
							</B>
							<br />
							<br />
							<B>
								RAPPORT DE LA SECTION DE JURY D’ADMISSIBILITÉ SUR LA CANDIDATURE
								(à l'issue de la phase d’audition)
							</B>
							<br />
							<br />
							<B>
								Section de jury d’admissibilité :
								<xsl:value-of select="sousjury" />
							</B>
							<br />
						</td>
					</tr>
					<tr style="text-align:left;">
						<td style="border-right: none;">
							<br />
							<B>NOM : </B>
							<xsl:value-of disable-output-escaping="yes" select="nom" />
						</td>
						<td style="border-left: none;">
							<B>Prénom : </B>
							<xsl:value-of disable-output-escaping="yes" select="prenom" />
						</td>
					</tr>
					</table>
					<table style="padding-top:10px;">
					<tr >
					<td colspan = "2" height="520" >

				<p>
					<xsl:value-of disable-output-escaping="yes" select="parcours" />
				</p>

				<p>
					<xsl:value-of disable-output-escaping="yes" select="projet" />
				</p>

				<p>
					<xsl:value-of disable-output-escaping="yes" select="avissousjury" />
				</p>

</td></tr>
<tr><td >
				<table cellspacing="1" border="1" 
					style="border-collapse:collapse;text-align:left;height:120px;">
					<tr>
						<td width="450">
							<table>
								<tr>
									<td colspan="2">
										<B>
											Nom et signature du président de la section de jury :
											<br />
										</B>
									</td>
								</tr>
								<tr>
									<td>
										<xsl:value-of disable-output-escaping="yes"
											select="signataire" />
									</td>
									<td>
										<img height="100">
											<xsl:attribute name="src"><xsl:value-of
												select="signature" /></xsl:attribute>
										</img>
									</td>
								</tr>
							</table>
						</td>
						<td width="150">
							<B>Date :
							</B>
							<br />
							<xsl:value-of disable-output-escaping="yes" select="date" />
							.
						</td>
					</tr>
				</table>
				</td>
				</tr>
				</table>

			</body>
		</html>

	</xsl:template>
</xsl:stylesheet>
