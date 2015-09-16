<?php
require_once('config_tools.inc.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');

$admin_sessions = isset($_REQUEST["admin_sessions"]) && isSecretaire();
$admin_maintenance = isset($_REQUEST["admin_maintenance"]) && isSecretaire();
$admin_users = isset($_REQUEST["admin_users"]) && isSecretaire();
$admin_concours = isset($_REQUEST["admin_concours"]) && isSecretaire() && !isSuperUser();
$admin_config = isset($_REQUEST["admin_config"]) && isSecretaire() && !isACN() && !isSuperUser();
$admin_keywords = isset($_REQUEST["admin_keywords"]) && isSecretaire();
$admin_rubriques = isset($_REQUEST["admin_rubriques"]) && isSecretaire() && !isSuperUser();
$admin_migration = isset($_REQUEST["admin_migration"]) && isSuperUser();
$admin_unites = isset($_REQUEST["admin_unites"]) && isSecretaire();
?>
  <h1>Interface d&#39;administration</h1>
<ul>
	<?php 
	if(!isSuperUser())
	{
	  ?>
	  <li><a href="index.php?action=admin&amp;admin_sessions=">Sessions</a></li>
	    <?php 
	}
	?>
	<li><a href="index.php?action=admin&amp;admin_users=">Membres</a></li>
	<li><a href="index.php?action=admin&amp;admin_unites">Unit&eacute;s</a>	
<?php
if(!isACN() && !isSuperUser())
{
?>
	<li><a href="index.php?action=admin&amp;admin_config=">Configuration</a>	</li>

    <?php 
}
	if(!isSuperUser())
	{
	  if( is_current_session_concours() )
	    {
	      ?>
	      <li>
	      <a href="index.php?action=admin&amp;admin_concours=">Concours</a></li>
		<?php
	    }
	  ?>
	<li><a href="index.php?action=admin&amp;admin_rubriques=">Rubriques</a>
	</li>
	   <li><a href="index.php?action=admin&amp;admin_keywords=">Mots-cl&eacute;s</a>
	</li>
	<?php 
	}
	?>
	<li><a href="index.php?action=admin&amp;admin_maintenance=">Synchronisation</a></li>
</ul>

<hr />

<?php 	
if($admin_maintenance)
  include 'admin/admin_maintenance.inc.php';

if($admin_sessions)
  include 'admin/admin_sessions.php';

if($admin_users)
  include "admin_users.inc.php";

if($admin_unites)
  include "admin_units.php";

if($admin_concours && is_current_session_concours() )
  include 'admin/admin_concours.inc.php';

if($admin_config)
  include 'admin/admin_config.inc.php';

if($admin_keywords)
  include 'admin/admin_keywords.inc.php';

if($admin_rubriques)
  include 'admin/admin_rubriques.inc.php';

?>