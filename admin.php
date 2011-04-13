<?

 include('includes/settings.php');
 session_start();

 if($_SESSION['a_login'] != 'admin' || $_SESSION['a_password'] != $admPassword){
  header('Location: login.php');
  exit;
 }

 include('includes/functions.php');

 connect();

 switch($_REQUEST['a']){
  case 2: // ------------------ SERVER CONFIGURATION ------------------------------------
   include('modules/module.settings_admin.php');
   break;
  case 3: // -------------------- MODULE MANAGEMENT ------------------------------------
   include('modules/module.modules_admin.php');
   break;
  case 29: // ----------------------- SETTINGS ------------------------------------
    a_header('Settings');

    echo '<table cellpadding=0 cellspacing=0>
     <tr><td valign=top>';

    if(file_exists('modules/module.settings_admin.php')){
     include('modules/module.settings_admin.php');
    }

    echo '</td><td width=15></td><td valign=top>';

    if(file_exists('modules/module.modules_admin.php')){
     include('modules/module.modules_admin.php');
    }

    if(file_exists('modules/module.backup_admin.php')){
     echo '<br>';
     include('modules/module.backup_admin.php');
    }

    echo '</td></tr></table><br>';

    a_footer();
   break;
  default: // ------------------------- PAGES -------------------------------------
   include('modules/module.pages_admin.php');
 }

?>