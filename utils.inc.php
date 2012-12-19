<?php
	session_start();
	include_once "config.inc.php";
	function db_connect($serverName,$dbname,$login,$password)
	{
		$dbh = @mysql_connect($serverName, $login, $password);
		if ($dbh)
		{
		  @mysql_select_db($dbname, $dbh) or die ("<strong>Error: Could not access the required table!</strong>");
		  mysql_query("SET NAMES utf8;");
		}
		return $dbh;
	} ;
	
	function db_disconnect(&$dbh)
	{
		mysql_close($dbh);
		$dbh=0;
	} ;

	function addCredentials($login,$pwd)
	{
		$_SESSION['login'] = $login;
		$_SESSION['pass'] = $pwd;
	} ;

	function removeCredentials()
	{
		unset($_SESSION['login']);
		unset($_SESSION['pass']);
	} ;

	function authenticateBase($login,$pwd)
	{
			$realPassHash = getPassHash($login);
			if ($realPassHash != NULL)
			{
				if (crypt($pwd, $realPassHash) == $realPassHash) 
				{
					return true;
				}
			}
			return false;
	}
	
	function authenticate()
	{
		if (isset($_SESSION['login']) and isset($_SESSION['pass']))
		{
			$login  = $_SESSION['login'];
			$pwd = $_SESSION['pass'];
			return authenticateBase($login,$pwd);
		}
		return false;
	} ;
	  
	function getPassHash($login)
	{
		$sql = "SELECT * FROM users WHERE login='$login';";
        $result=mysql_query($sql);
        if ($row = mysql_fetch_object($result))
        {
			return $row->passHash;
        }
		return NULL;
	} ;

	function getDescription($login)
	{
		$sql = "SELECT * FROM users WHERE login='$login';";
        $result=mysql_query($sql);
        if ($row = mysql_fetch_object($result))
        {
			return $row->description;
        }
		return NULL;
	} ;
	
	function parseSortCriteria($sort_crit)
	{
		$result = array();
		$pieces = explode(";", $sort_crit);
		foreach($pieces as $crit)
		{
			$firstChar = substr($crit,0,1);
			$crit = substr($crit,1);
			if ($firstChar=="*")
			{ $result[$crit]= "ASC"; }
			else if ($firstChar=="-")
			{ $result[$crit]= "DESC"; }			
		}
		return $result;
	}
	
	function showCriteria($sortCrit, $crit)
	{
		$order = "";
		if (isset($sortCrit[$crit]))
		{ $order = $sortCrit[$crit];}
		if ($order=="ASC")
		{  return "<img src=\"img/sortup.png\" alt=\"$crit sorted ascendently\">";  }
		else if ($order=="DESC")
		{  return "<img src=\"img/sortdown.png\" alt=\"$crit sorted descendently\">";  }
		else 
		{  return "<img src=\"img/sortneutral.png\" alt=\"$crit sorted neutrally\">";  }
	}

	
	function dumpEditedCriteria($sortCrit, $edit_crit)
	{
		$result = "";
		$order = "";
		if (isset($sortCrit[$edit_crit]))
		{ $order = $sortCrit[$edit_crit];}
		if ($order=="ASC")
		{  $sortCrit[$edit_crit] = "DESC";  }
		else if ($order=="")
		{  $sortCrit[$edit_crit] = "ASC";  }			
		else if ($order=="DESC")
		{  unset($sortCrit[$edit_crit]);  }			
		foreach($sortCrit as $crit => $order)
		{
			if ($order=="ASC")
			{  $order = "*";  }
			else if ($order=="DESC")
			{  $order = "-";  }
			if ($result != "")
			{ $result .= ";";}
			$result .=  $order.$crit;
		}
		return urlencode($result);
	}

	function sortCriteriaToSQL($sortCrit)
	{
		$sql = "";
		foreach($sortCrit as $crit => $order)
		{
			if ($sql == "")
			{ $sql = "ORDER BY ";}
			else
			{ $sql .= ", ";}
			$sql .= $crit." ".$order;
		}
		return $sql;
	}
	
	
	function filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp)
	{
		$sortCrit = parseSortCriteria($sort_crit);
		
		$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
		if ($id_session!=-1)
		{ $sql .= " AND id_session=$id_session";}
		if ($type_eval!="")
		{ 
			$sql .= " AND type=\"$type_eval\"";
		}
		if ($login_rapp!="")
		{ 
			$sql .= " AND rapporteur=\"$login_rapp\"";
		}
		$sql .= sortCriteriaToSQL($sortCrit);
		$sql .= ";";
		//echo $sql;
		$result=mysql_query($sql);
		return $result;
	}
	
	function displaySummary($id_session, $type_eval, $sort_crit, $login_rapp)
	{
		global $fieldsSummary;
		global $fieldsAll;
		global $actions;
		global $typesEval;
		
		$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);
		$sortCrit = parseSortCriteria($sort_crit);
		$rapporteurs = array();
		$sessions =  showSessions();
		$rows = array();
		while ($row = mysql_fetch_object($result))
		{
			$rows[] = $row;
			$rapporteurs[$row->rapporteur] = 1;
		}
		$krapp = array_keys($rapporteurs);
		natcasesort($krapp);
		$rapporteurs = $krapp;

		?>
			<h2>Filtrage</h2>
			<form  method="get">
				<table class="inputreport">
					<tr>
						<td style="width:20em;">Session</td>
						<td style="width:20em;"><select name="id_session"> 
						<option value="-1">Toutes les sessions</option>
						<?php
							foreach ($sessions as $val)
							{
								$sel = "";
								if ($val["id"]==$id_session)
								{ $sel = " selected=\"selected\""; }
								echo "<option value=\"".$val["id"]."\" $sel>".ucfirst($val["nom"])." ".date("Y",strtotime($val["date"]))."</option>";
							}
						?>
						</select></td>
						<td>
							<input type="hidden" name="login_rapp" value="<?php echo $login_rapp;?>">
							<input type="hidden" name="type_eval" value="<?php echo $type_eval;?>">
							<input type="hidden" name="sor_crit" value="<?php echo $sort_crit;?>">
							<input type="hidden" name="action" value="view">
							<input type="submit" value="Filtrer">
						</td>
					</tr>
				</table>
			</form>
			<form  method="get">
				<table class="inputreport">
					<tr>
						<td style="width:20em;">Rapporteur</td>
						<td style="width:20em;"><select name="login_rapp"> 
						<option value="">Tous les rapporteurs</option>
						<?php
							foreach ($rapporteurs as $rapp)
							{
								$sel = "";
								if ($rapp==$login_rapp)
								{ $sel = " selected=\"selected\""; }
								echo "<option value=\"$rapp\"$sel>".ucfirst($rapp)."</option>";
							}
						?>
						</select></td>
						<td>
							<input type="hidden" name="id_session" value="<?php echo $id_session;?>">
							<input type="hidden" name="type_eval" value="<?php echo $type_eval;?>">
							<input type="hidden" name="sor_crit" value="<?php echo $sort_crit;?>">
							<input type="hidden" name="action" value="view">
							<input type="submit" value="Filtrer">
						</td>
					</tr>
				</table>
			</form>
			<form  method="get">
				<table class="inputreport">
					<tr>
						<td style="width:20em;">Type évaluation</td>
						<td style="width:20em;"><select name="type_eval"> 
						<option value="">Tous les types</option>
						<?php
							foreach ($typesEval as $ty)
							{
								$sel = "";
								if ($ty==$type_eval)
								{ $sel = " selected=\"selected\""; }
								echo "<option value=\"$ty\"$sel>".ucfirst($ty)."</option>";
							}
						?>
						</select></td>
						<td>
							<input type="hidden" name="id_session" value="<?php echo $id_session;?>">
							<input type="hidden" name="sor_crit" value="<?php echo $sort_crit;?>">
							<input type="hidden" name="action" value="view">
							<input type="hidden" name="login_rapp" value="<?php echo $login_rapp;?>">
							<input type="submit" value="Filtrer">
						</td>
					</tr>
				</table>
			</form>
			<br><hr><br>
			<h2>Rapports disponibles</h2>
			<table class="summary">
				<tr>
		<?php
		foreach($fieldsSummary as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
		?>
					<th><span class="nomColonne"><a href="?action=view&amp;sort=<?php echo dumpEditedCriteria($sortCrit, $fieldID);?>"><?php echo $title.showCriteria($sortCrit, $fieldID);?></a></span></th>
		<?php
		}
		foreach($actions as $action => $actionTitle)
		{?>
					<th></th>
		<?php
		}
		?>
				</tr>
		<?php
		foreach($rows as $row)
		{
		?>
				<tr>
			<?php
			foreach($fieldsSummary as $fieldID)
			{
				$title = $fieldsAll[$fieldID];
			?>
					<td><span class="valeur"><?php echo $row->$fieldID;?></span></td>
			<?php
			}
			foreach($actions as $action => $actionTitle)
			{
				echo "<td><a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$actionTitle\"></a></td>";
			}
?>
				<tr>
		<?php
		}
		?>
			</table>
		<?php
	} ;	

	function showSessions()
	{
		$finalResult = array();
		$sql = "SELECT * FROM sessions ORDER BY date DESC;";
		$result=mysql_query($sql);
		
		while ($row = mysql_fetch_object($result))
		{
			$finalResult[] = array(
			  "id" => $row->id,
			  "nom" => $row->nom,
			  "date" => $row->date,
			  );
		}
		return $finalResult;
	} ;	

	function getTypesEval($id_session){
		$finalResult = array();
		$sql = "SELECT DISTINCT type FROM (SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session) difftypes WHERE id_session=$id_session ORDER BY type DESC;";
		$result=mysql_query($sql);
		while ($row = mysql_fetch_object($result))
		{
			if ($row->type)
				$finalResult[] = $row->type;
		}
		return $finalResult;	
	}
	
	function displayReport($id_rapport)
	{
		global $fieldsAll;
		global $actions;
		$specialRule = array(
			"nom"=>0,
			"prenom"=>0,
			"grade"=>0,
			"unite"=>0,
			"type"=>0,
			"nom_session"=>0,
			"date_session"=>0,
		);
		$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport AND tt.id_session=ss.id;";
		//echo $sql;
		$result=mysql_query($sql);
		if ($row = mysql_fetch_object($result))
		{
		?>
			<div class="tools">
				<?php
				foreach($actions as $action => $actionTitle)
				{
					if ($action!="details")
					{ echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$action\"></a>"; }
				}
				?>
			</div>
			<h1> 
				<?php echo $row->prenom;?> 
				<?php echo strtoupper($row->nom);?>
				(<?php echo $row->grade;?>) - 
				<?php echo $row->unite;?>
			</h1>
			<h2> 
				<?php echo $row->type;?> 
				<?php echo $row->nom_session." ".date("Y",strtotime($row->date_session));?> 
			</h2>
			<dl>
			<?php
			foreach($fieldsAll as  $fieldID => $title)
			{
				if (!isset($specialRule[$fieldID]))
				{
			?>
					<dt><?php echo $title;?></dt>
					<dd><?php echo $row->$fieldID;?></dd>
			<?php
				}
			}
			?>
			</dl>
			<div>
			</div>
		<?php
		}
		?>
		<?php
	} ;	

	function getExample($type)
	{
		global $examples;
		$tmp = "Exemple : ";
		if (isset($examples[$type]))
		{
			$tmp .= $examples[$type];
		}
		return $tmp;
	}

	function highlightDiff(&$prevVals,$key,$val)
	{
		if (isset($prevVals[$key]))
		{
			if ($prevVals[$key]==$val)
			{
				$prevVals[$key] = $val;
				return "<span class=\"faded\">$val</span>";
			}
			else
			{
				$prevVals[$key] = $val;
				return "<span class=\"highlight\">$val</span>";
			}
		}
		$prevVals[$key] = $val;
		return $val;
	}
	
	function fieldDiffers($prevVals,$key,$val)
	{
		if (isset($prevVals[$key]))
		{
			if ($prevVals[$key]==$val)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		return true;
	}
	
	function historyReport($id_origine)
	{
		global $fieldsAll;
		global $actions;
		$specialRule = array(
			"nom"=>0,
			"prenom"=>0,
			"grade"=>0,
			"unite"=>0,
			"type"=>0,
			"nom_session"=>0,
			"date_session"=>0,
			"date"=>0,
			"auteur"=>0,
		);
		
		$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id_session=ss.id AND tt.id_origine=$id_origine ORDER BY date DESC;";
		$result=mysql_query($sql);
		$prevVals = array();
		$first = true;
		while ($row = mysql_fetch_object($result))
		{
			if ($first)
			{
			?>
			<div class="tools">
			<?php
				foreach($actions as $action => $actionTitle)
				{
					if ($action!="history")
					{ echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$action\"></a>"; }
				}
			?>
			</div>
			<?php
				$first = false;
			}

			?>
		<div class="history">
			<h3> Version modifiée le <?php echo $row->date;?> par <?php 
				$desc = getDescription($row->auteur);
				if (!$desc)
					$desc = $row->auteur;
				echo highlightDiff($prevVals,"auteur",$desc);
			?>
			</h3>
			<?php
			if (fieldDiffers($prevVals,"prenom",$row->prenom) 
				or fieldDiffers($prevVals,"nom",strtoupper($row->nom))
				or fieldDiffers($prevVals,"grade",$row->grade)
				or fieldDiffers($prevVals,"unite",$row->unite))
			{
			?>
			<h1> 
				<?php echo highlightDiff($prevVals,"prenom",$row->prenom);?> 
				<?php echo highlightDiff($prevVals,"nom",strtoupper($row->nom));?>
				(<?php echo highlightDiff($prevVals,"grade",$row->grade);?>) - 
				<?php echo highlightDiff($prevVals,"unite",$row->unite);?>
			</h1>
			<?php
			}
			if (fieldDiffers($prevVals,"type",$row->type) 
				or fieldDiffers($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session))))
			{
			?>
			<h2> 
				<?php echo highlightDiff($prevVals,"type",$row->type);?> 
				<?php echo highlightDiff($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session)));?> 
			</h2>
			<?php
			}
			?>
			<dl>
			<?php
			foreach($fieldsAll as  $fieldID => $title)
			{
				if (!isset($specialRule[$fieldID]) 	and !(isset($prevVals[$fieldID])and ($prevVals[$fieldID]==$row->$fieldID)))
				{
			?>
					<dt><?php echo $title;?></dt>
					<dd><?php echo highlightDiff($prevVals,$fieldID,$row->$fieldID);?></dd>
			<?php
				}
			}
			?>
			</dl>
		</div>
		<?php
		}
		?>
		<?php
	} ;		
	function editReport($id_rapport)
	{
		global $fieldsAll;
		global $fieldsTypes;
		global $actions;
		global $typesEval;
		global $grades;
		global $evaluations;
		$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"nom_session"=>0,
			"date_session"=>0,
			"type"=>0,
			"grade" => 0,
		);
		
		$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport ORDER BY date DESC LIMIT 1;";
		$result=mysql_query($sql);
		if ($row = mysql_fetch_object($result))
		{
		?>
			<form method="post" action="index.php" style="width:100%">
			<table class="inputreport">
			<tr>
				<td>Session</td>
				<td>
					<select name="fieldid_session" style="width:100%;">				
					<?php 		
					$sessions = showSessions();
					foreach($sessions as $s)
					{
						$sel = "";
						if ($row->id_session==$s["id"])
						{ $sel = "selected=\"selected\""; }
						
						echo  "\t\t\t\t\t<option value=\"".$s["id"]."\" $sel>".$s["nom"]." ".date("Y",strtotime($s["date"]))."</option>";
					}
					?>
					</select> 
				</td>
			</tr>
			<tr>
				<td>Type d'évaluation</td>
				<td>
					<select name="fieldtype"  style="width:100%;">				
					<?php
					foreach($typesEval as $ty)
					{
						$sel = "";
						if ($row->type==$ty)
						{ $sel = "selected=\"selected\""; }
						echo  "\t\t\t\t\t<option value=\"$ty\" $sel>$ty</option>";
					}
					?>
					</select> 
				</td>
			</tr>
			<tr>
				<td>Grade</td>
				<td>
					<select name="fieldgrade"  style="width:100%;">				
					<?php
						foreach($grades as $idg => $txtg)
						{
							$sel = "";
							if ($row->grade==$idg)
							{ $sel = "selected=\"selected\""; }
							echo  "\t\t\t\t\t<option value=\"$idg\" $sel>$txtg</option>";
						}
					?>
					</select> 
				</td>
			</tr>
			<?php
			foreach($fieldsAll as  $fieldID => $title)
			{
				if (!isset($specialRule[$fieldID]))
				{
					$type = "";
					if (isset($fieldsTypes[$fieldID]))
					{ $type = $fieldsTypes[$fieldID]; }
					
			?>
				<tr>
					<td  style="width:17em;"><span><?php echo $title;?></span></td>
				<?php
					if ($type=="long")
					{
				?>
					<td colspan="2"><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<tr>
					<td colspan="3"><textarea name="field<?php echo $fieldID;?>" style="width:100%;"><?php echo $row->$fieldID;?></textarea></td>
				<?php
					}
					else if ($type=="short")
					{
				?>
					<td  style="width:30em;"><input name="field<?php echo $fieldID;?>" value="<?php echo $row->$fieldID;?>"  style="width:100%;"></td>
					<td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				<?php
					}
					else if ($type=="evaluation")
					{
				?>
					<td  style="width:30em;"><select name="field<?php echo $fieldID;?>" style="width:100%;"> 
					<?php
						foreach($evaluations as $val)
						{
							$sel = "";
							if ($row->$fieldID==$val)
							{ $sel = "selected=\"selected\""; }
							echo  "\t\t\t\t\t<option value=\"$val\" $sel>$val</option>";
						}
					?>
					</select></td>
					<td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				<?php
					}
					else
					{
				?>
					<td colspan="2"><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<tr>
					<td><textarea name="field<?php echo $fieldID;?>"><?php echo $row->$fieldID;?></textarea></td>
				<?php
					}
				?>
				</tr>
			<?php
				}
			}
			?>
				<tr>
					<td colspan="2"><input type="submit" value="Enregistrer les changements">
						<input type="hidden" name="id_origine" value="<?php echo $row->id_origine;?>">
						<input type="hidden" name="action"  value="update">
					</td>
				</tr>
			</table>
			</form>
		<?php
		}
		?>
		<?php
	} ;	
	

	
	function update($id_origine)
	{
		global $fieldsAll;
		$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"nom_session"=>0,
			"date_session"=>0,
		);
		$fields = "id_session, id_origine, auteur";
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]))
			{
				$fields.=",";
				$fields.=$fieldID;
			}
		}
		$values = mysql_real_escape_string($_REQUEST["fieldid_session"]);
		$values .= ",".mysql_real_escape_string($id_origine);
		$values .= ",\"".mysql_real_escape_string($_SESSION["login"])."\"";
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]))
			{
				$values.=",";
				$values.="\"".mysql_real_escape_string($_REQUEST["field".$fieldID])."\"";
			}
		}
		$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
		mysql_query($sql);
		return mysql_insert_id ();
	}
	
	
	function newReport()
	{
		global $fieldsAll;
		global $fieldsTypes;
		global $grades;
		global $evaluations;
		global $actions;
		global $typesEval;
		$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"date_session"=>0,
			"grade" => 0,
		);
		?>
		<form method="get" action="index.php" style="width:100%">
		<table class="inputreport">
		<tr>
			<td style="width:18em; white-space: nowrap;">Session</td>
			<td>
				<select name="fieldid_session" style="width:100%;">				
				<?php 		
				$sessions = showSessions();
				foreach($sessions as $s)
				{
					echo  "\t\t\t\t\t<option value=\"".$s["id"]."\">".$s["nom"]." ".date("Y",strtotime($s["date"]))."</option>";
				}
				?>
				</select> 
			</td>
		</tr>
		<tr>
			<td>Type d'évaluation</td>
			<td>
				<select name="fieldtype" style="width:100%;">				
				<?php
					foreach($typesEval as $ty)
					{
						echo  "\t\t\t\t\t<option value=\"$ty\">$ty</option>";
					}
				?>
				</select> 
			</td>
		</tr>
		<tr>
			<td>Grade</td>
			<td>
				<select name="fieldgrade" style="width:100%;">				
				<?php
					foreach($grades as $idg => $txtg)
					{
						echo  "\t\t\t\t\t<option value=\"$idg\">$txtg</option>";
					}
				?>
				</select> 
			</td>
		</tr>
		<?php
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]))
			{
				$type = "";
				if (isset($fieldsTypes[$fieldID]))
				{ $type = $fieldsTypes[$fieldID]; }
					if ($type=="long")
					{
				?>
				<tr>
					<td><span><?php echo $title;?></span></td><td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<tr>
					<td colspan="3"><textarea name="field<?php echo $fieldID;?>" style="width:100%;"></textarea></td>
				</tr>
				<?php
					}
					else if ($type=="short")
					{
				?>
				<tr>
					<td><?php echo $title;?> </td>
					<td><input name="field<?php echo $fieldID;?>" value="" style="width:100%;"> </td>
					<td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<?php
					}
					else if ($type=="evaluation")
					{
				?>
				<tr>
					<td><?php echo $title;?> </td>
					<td  style="width:30em;"><select name="field<?php echo $fieldID;?>" style="width:100%;"> 
					<?php
						foreach($evaluations as $val)
						{
							echo  "\t\t\t\t\t<option value=\"$val\">$val</option>";
						}
					?>
					</select></td>
					<td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<?php
					}
					else
					{
				?>
				<tr>
					<td><span><?php echo $title;?></span></td><td><span class="examplevaleur"><?php echo getExample($fieldID);?></span></td>
				</tr>
				<tr>
					<td colspan="2"><textarea name="field<?php echo $fieldID;?>" style="width:100%;"></textarea></td>
				</tr>
				<?php
					}
				?>
			<?php
			}
		}
		?>
			<tr>
				<td colspan="2"><input type="submit" value="Ajouter Rapport">
					<input type="hidden" name="action"  value="add">
				</td>
			</tr>
		</table>
		</form>
	<?php
	?>
	<?php
} ;	
	function addReport()
	{
		$newid = update(0);
		$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
		mysql_query($sql);
		return $newid;
	};
	
	function isSuperUser($login = "")
	{
		if ($login=="")
		{
			if (isset($_SESSION["login"]))
			{
				$login = $_SESSION["login"];
			}
			else return false;
		}
		return $login == "admin";
	};
	
	function listUsers()
	{
	  $users = array();
	  if (isSuperUser())
	  {
		$sql = "SELECT login FROM users ORDER BY login ASC;";
		$result=mysql_query($sql);
		while ($row = mysql_fetch_object($result))
		{
			$users[] = $row->login;
		}
	  }
	  return $users;
	}
	
	function changePwd($login,$old,$new1,$new2)
	{
		if (isset($_SESSION["login"]))
		{
			$currLogin = $_SESSION["login"];
			if ($currLogin==$login or isSuperUser())
			{
				if (authenticateBase($login,$old) or isSuperUser())
				{
					$oldPassHash = getPassHash($login);
					if ($oldPassHash != NULL)
					{
						$newPassHash = crypt($new1, $oldPassHash);
						$sql = "UPDATE users SET passHash='$newPassHash' WHERE login='$login';";
						mysql_query($sql);
						return true;
					}
				}
				else
				{
					echo "<p><strong>Erreur :</strong> La saisie du mot de passe courant est incorrecte, veuillez réessayer.</p>";
					return false;
				};
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Seuls les administrateurs du site peuvent modifier les mots de passes d'autres utilisateurs, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
				return false;
			}
		}
		else
		{
			echo "<p><strong>Erreur :</strong> Login manquant, veuillez vous reconnecter.</p>";
			return false;
		}
	}
	function createUser($login,$pwd,$desc)
	{
		if (isSuperUser())
		{
			$passHash = crypt($pwd);
			$sql = "INSERT INTO users(login,passHash,description) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."');";
			mysql_query($sql);
			return true;
		}
	}

	function deleteUser($login)
	{
		if (isSuperUser())
		{
			$sql = "DELETE FROM users WHERE login='".mysql_real_escape_string($login)."';";
			mysql_query($sql);
		}
	}

	function createSession($name,$date)
	{
		if (isSuperUser())
		{
			echo $date."<br>";
			echo strtotime($date)."<br>";
			echo date("Y-m-d h:m:s",strtotime($date));
			$sql = "INSERT INTO sessions(nom,date) VALUES ('".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
			mysql_query($sql);
			return true;
		}
	}
	
	function deleteSession($id)
	{
		if (isSuperUser())
		{
			$sql = "DELETE FROM sessions WHERE id=$id;";
			mysql_query($sql);
		}
	}


?>