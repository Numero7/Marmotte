<?php
require_once("db.inc.php");
require_once('authenticate_tools.inc.php');

session_start();

db_connect($servername,marmottedbname,$serverlogin,$serverpassword);

if(isset($_SESSION['REMOTE_USER']) && ($_SESSION['REMOTE_USER'] != ''))
  addCredentials($_SESSION['REMOTE_USER'], "",true);

if(!authenticate())
 {
   removeCredentials();
   $data = array('type' => 'error', 'message' => 'Mauvaise authentification');
   header('HTTP/1.1 400 Bad Request');
   header('Content-Type: application/json; charset=UTF-8');
   echo json_encode($data);
   die(0);
 }

require_once('manage_rapports.inc.php');
require_once('manage_people.inc.php');

$action = isset($_POST['action']) ? $_POST['action'] : '?';

switch($action)
{
case 'set_property':
  $property = $_POST['property'];
  $id_origine = $_POST['id_origine'];
  $value = $_POST['value'];
  set_property($property,$id_origine, $value, true);
  echo "set property '".$property."' with value '".$value."' for report '".$id_origine."'";
  break;
case 'set_people_property':
 $property = $_REQUEST["property"];
 $numsirhus = $_REQUEST["numsirhus"];
 $value = $_REQUEST["value"];
 set_people_property($property,$numsirhus, $value);
break;
}

db_disconnect();


?>
