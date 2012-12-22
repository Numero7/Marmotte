<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" omit-xml-declaration="yes" indent="no" encoding="UTF-8"/>
<xsl:strip-space elements="*"/>
<xsl:template match="/">

\documentclass[letterpaper,10pt]{article}
\usepackage[centering,width=480pt,height=700pt]{geometry}
\usepackage{palatino}
\usepackage[french]{babel}
\usepackage[utf8]{inputenc}
\usepackage{relsize}
\usepackage{framed}


\newcommand{\Rapport}[1]{%
	\noindent\fbox{\begin{minipage}{\textwidth}#1%
	\end{minipage}\\[1em]}
}

 \begin{document}
 <xsl:for-each select="rapports/rapport">\Rapport{\noindent{\it <xsl:value-of select="type"/>}\\
    \noindent <xsl:value-of select="prenom"/> {\sc <xsl:value-of select="nom"/>}
	 (<xsl:value-of select="grade"/>) -- <xsl:value-of select="unite"/>\\
	\noindent Rapporteur : <xsl:value-of select="rapporteur"/>
	\begin{framed}
		\noindent Prérapport : <xsl:value-of select="prerapport"/>
	\end{framed}
	\noindent Session : <xsl:value-of select="session"/>\\
	\noindent Ancienneté : <xsl:value-of select="anciennete_grade"/> (Recrutement en <xsl:value-of select="date_recrutement"/>)\\
	\noindent Production {\bf <xsl:value-of select="production"/>} : <xsl:value-of select="production_notes"/>\\
	\noindent Transfert {\bf <xsl:value-of select="transfert"/>} : <xsl:value-of select="transfert_notes"/>\\
	\noindent Encadrement {\bf <xsl:value-of select="encadrement"/>} : <xsl:value-of select="encadrement_notes"/>\\
	\noindent Responsabilités {\bf <xsl:value-of select="responsabilites"/>} : <xsl:value-of select="responsabilites_notes"/>\\
	\noindent Mobilité {\bf <xsl:value-of select="mobilite"/>} : <xsl:value-of select="mobilite_notes"/>\\
	\noindent Animation {\bf <xsl:value-of select="animation"/>} : <xsl:value-of select="animation_notes"/>\\
	\noindent Rayonnement {\bf <xsl:value-of select="rayonnement"/>} : <xsl:value-of select="rayonnement_notes"/>
	\begin{framed}
		Rapport (Avis : <xsl:value-of select="avis"/>) : <xsl:value-of select="rapport"/>
	\end{framed}
	Dernière modification par <xsl:value-of select="auteur"/> le <xsl:value-of select="date"/>.}
 </xsl:for-each>
 \end{document}
  </xsl:template>
</xsl:stylesheet>