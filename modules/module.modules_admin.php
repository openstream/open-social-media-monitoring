<?

 if($query_mode){
  $module_name = 'Module Management';
  $module_version = '1.0.0';
  $module_description = 'List all installed modules at the settings page of the administrator interface. Also dispalys the detailed module information. This module is not critical, but once disabled you will not have further possibility to manage, enable or disable installed modules.';
  $module_author = 'OpenStream';
  $module_release_date = 'April 12, 2011';
 }else{

  // Normal Mode

  switch($_REQUEST['b']){
   default:
    open_table('Installed Modules');

    $dir = 'modules';

    // Open a modules directory, and proceed to read its contents

    if(is_dir($dir)){
     if($dh = opendir($dir)){
      $query_mode = 1;

      echo '<table width=250 cellpadding=4 cellspacing=2>';
      while(($file = readdir($dh)) !== false){
       if(!preg_match('/^\.+$/', $file)){
        include($dir.'/'.$file);
        echo '<tr><td>'.$module_name.'</td><td align=right><a href='.url_param().'?a=3&b=1&file='.$file.'>details</a></td></tr>';
       }
      }
      closedir($dh);
      echo '</table>';

      $query_mode = 0;
     }
    }

    close_table();
    break;

   case 1: // ---------------------- MODULE INFO ----------------------------------
    a_header('');

    if(file_exists('modules/'.$_REQUEST['file'])){
     $query_mode = 1;
     include('modules/'.$_REQUEST['file']);

     echo '
      <table cellspacing=1 cellpadding=2>
       <tr><td width=100><b>Module Name:</b></td><td>'.$module_name.'</td></tr>
       <tr><td><b>Version:</b></td><td>'.$module_version.'</td></tr>
       <tr><td><b>Author:</b></td><td>'.$module_author.'</td></tr>
       <tr><td><b>Release Date:</b></td><td>'.$module_release_date.'</td></tr>
       <tr><td valign=top><b>Description:</b></td><td>'.$module_description.'</td></tr>
       <tr><td></td><td><br><input type=button class=bu value="Back" onclick="location.href = \''.url_param().'?a=29\'"></td></tr>
      </table><br>';

     $query_mode = 0;
    }else{
     echo 'Module does not exists.';
    }

    a_footer();
    break;
  }

 }

?>