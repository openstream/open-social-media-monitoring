<?

 if($query_mode){
  $module_name = 'Settings';
  $module_version = '1.0.0';
  $module_description = 'Displays various configurable options like administrator password, database configurations etc. This module is not critical, but once disabled you will not have further possibility to change any settings.';
  $module_author = 'OpenStream';
  $module_release_date = 'April 12, 2011';
 }else{

  // Normal Mode

  switch($_REQUEST['b']){
   default:
    open_table('Server Configuration');
    echo '<table width=355 cellpadding=4 cellspacing=2><form method=post action='.url_param().'?a=2&b=1>
            <tr><td align=right><b>Administator Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=admPassword size=30 value="'.$admPassword.'"></td></tr>
            <tr><td align=right><b>Administrator E-Mail:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=adminEmail size=30 value="'.$adminEmail.'"></td></tr>
            <tr><td align=right><b>Default From Address:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=defaultFrom size=30 value="'.$defaultFrom.'"></td></tr>
            <tr><td align=right><b>MySQL Server:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbHost value="'.$dbHost.'" size=30></td></tr>
            <tr><td align=right><b>MySQL Login:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbUser value="'.$dbUser.'" size=30></td></tr>
            <tr><td align=right><b>MySQL Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=dbPassword value="'.$dbPassword.'" size=30></td></tr>
            <tr><td align=right><b>MySQL Database:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbName value="'.$dbName.'" size=30></td></tr>
            <tr><td align=right><b>Database Prefix:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=prefix value="'.$prefix.'" size=30></td></tr>
            <tr><td></td><td><input type=submit value="Save Settings" class=bu></td></tr>
           </form></table>';

    close_table();
    break;

   case 1: // -------------------- SAVE PREFERENCES -------------------------
    $fp = fopen('includes/settings.php', 'w');
    fputs($fp, "<?\n\n");
    while(list($var, $val) = each($_POST))
     fputs($fp, ' $'.$var." = '".$val."';\n");
    fputs($fp, "\n\n?>");
    fclose($fp);
    header('Location: '.url_param().'?a=29');
    break;

  }

 }


?>