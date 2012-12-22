<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" omit-xml-declaration="yes" indent="no" encoding="UTF-8"/>
<xsl:strip-space elements="*"/>
<xsl:template match="/">


\documentclass[a4paper,11pt,francais]{article}
\usepackage{lmodern,babel}

%\usepackage[latin1]{inputenc}
\usepackage[T1]{fontenc}
\usepackage{babel,fancyhdr,color,graphicx,url,chngpage,xspace,setspace}
\usepackage[utf8]{inputenc}
%\usepackage[T1]{fontenc}
\usepackage[a4paper, 
left=1.2cm, right=1.2cm,top=1.5cm,bottom=2cm,twoside]{geometry}
%\usepackage{amssymb}
\usepackage{amsmath,amssymb,xspace,theorem}


\newcommand{\nous}{Section 6 du Comité National\xspace}
\newcommand{\Nous}{\nous}
\newcommand{\phraseDR}{Le faible nombre de possibilités de promotions 
DR1 cette année ne permet malheureusement pas à la \nous de vous 
proposer à la Direction Générale du CNRS pour une promotion cette année.}
\newcommand{\phraseDRb}{Le faible nombre de possibilités de promotions 
DR1 cette année ne permet malheureusement pas à la \nous de  
proposer ce chercheur à la Direction Générale du CNRS 
pour une promotion cette année.}
\newcommand{\phraseDRCE}{Le faible nombre de possibilités de promotions 
DRCE1 cette année ne permet malheureusement pas à la \nous de vous 
proposer à la Direction Générale du CNRS pour une promotion cette année.}
\newcommand{\phraseDRCEb}{Le faible nombre de possibilités de promotions 
DRCE1 cette année ne permet malheureusement pas à la \nous de  
proposer ce chercheur à la Direction Générale du CNRS 
pour une promotion cette année.}
\newcommand{\caeDR}{La \nous confirme l'affectation proposée.}
\parskip 1.2ex



\begin{document}

 \begin{tabular*}{0.9\textwidth}{@{\extracolsep{\fill}}|l|r|}
     \hline
     \null\vspace{0pt}\hspace{-5pt}\includegraphics[width=2.5cm]{CN} <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  \textbf{RAPPORT DE SECTION} \\
     \hline     
     \textbf{Section du Comité national : 6 }<xsl:text disable-output-escaping="yes">&amp;</xsl:text>  \textbf{<xsl:value-of select="session"/>}\\
     \hline
     \multicolumn{2}{|c|}{\textbf{Intitulé de la section: Sciences de l'information : fondements de l'informatique,}}\\
     \multicolumn{2}{|c|}{\textbf{calculs, algorithmes, représentations, exploitations}}\\
     \hline
     \textbf{Objet de l'évaluation}<xsl:text disable-output-escaping="yes">&amp;</xsl:text> \textbf{Intitulé et numéro unité}\\
     \multicolumn{1}{|c|}{\textit{<xsl:value-of select="grade"/>}} <xsl:text disable-output-escaping="yes">&amp;</xsl:text>   
     \multicolumn{1}{c|}{}  \\
     \null <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  \multicolumn{1}{c|}{\textsc{<xsl:value-of select="nom"/> <xsl:value-of select="prenom"/>}}\\
     \null <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  \multicolumn{1}{c|}{<xsl:value-of select="unite"/>}\\
     \hline
 \end{tabular*}
     
\bigskip
\noindent \textbf{Appréciations générales et recommandations de la 
section:}\\ \null\\
\setstretch{0.66}
{\scriptsize Ce rapport a été établi après délibérations de la section, sous la responsabilité de son président, à partir des appréciations du rapporteur, des observations et recommandations de la section.\\
Les avis émis par les sections ne préjugent pas de la décision qui sera prise par la direction du CNRS.}

\setstretch{1}

\bigskip

<xsl:value-of select="rapport"/>

\bigskip\bigskip  \hfill 
\begin{tabular}{cp{5cm}}
\includegraphics[width=4cm]{signature} 
<xsl:text disable-output-escaping="yes">&amp;</xsl:text>  \parbox{5cm}{\vspace{-2.5cm}
\null\hfill Fait le 18 mai 2009\\
\null\hfill Frédérique \textsc{Bassino}\\
\null\hfill Présidente de la Section 6}
 \end{tabular}

\vfill
\centerline{
\begin{tabular*}{1\textwidth}{@{\extracolsep{\fill}}|l|l|}
     \hline
     \multicolumn{2}{|l|}{\textbf{Evaluation à vague ou mi-vague}} \\
     \multicolumn{2}{|l|}{{Avis de la section sur l'activité du chercheur}} \\
     \hline     
     <xsl:if test="avis= 'Favorable' ">\times</xsl:if> <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  Avis favorable {\scriptsize (l'activité du chercheur est conforme à ses
     obligations statutaires)}\\
     \hline
     <xsl:if test="avis= 'Différé'">\times</xsl:if> <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  Avis différé {\scriptsize (l'évaluation est renvoyée à la session suivante 
     en raison de l'insuffisance ou de l'absence d'éléments du 
     dossier)}\\
     \hline
     <xsl:if test="avis= 'Réservé'">\times</xsl:if> <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  Avis réservé {\scriptsize (la section a identifié dans l'activité du
     chercheur un ou plusieurs éléments qui nécessitent un suivi
     spécifique)}\\
     \hline
     <xsl:if test="avis= 'Alerte'">\times</xsl:if> <xsl:text disable-output-escaping="yes">&amp;</xsl:text>  Avis d'alerte {\scriptsize (la section exprime des inquiétudes sur 
     l'évolution de l'activité du chercheur)}\\    
         \hline
     \hline
 \end{tabular*}}
 
 \end{document}

  </xsl:template>
</xsl:stylesheet>