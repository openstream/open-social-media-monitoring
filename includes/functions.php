<?

 function connect(){
  global $dbName, $dbHost, $dbUser, $dbPassword;

  if($rd = mysql_connect($dbHost, $dbUser, $dbPassword))
   mysql_select_db($dbName, $rd);
 }

 function __autoload($class_name) {
   require_once(dirname(__file__).'/../classes/'.$class_name.'.php');
 }

 function url_param($param = '', $close = 0, $url = ''){
  $url = $url ? $url : $_SERVER['REQUEST_URI'];
  if(!$param){
   $res = preg_replace('/.*\/|\?.*/', '', $url);
   $res = $res ? $res : 'index.php';
  }elseif($param == '^'){
   $res = preg_replace('/.*\//', '', $url);
   $res = $res ? $res : 'index.php';
  }elseif(preg_match('/^\^/', $param)){
   $rep = preg_replace('/^\^/', '', $param);
   $res = preg_replace('/.*\/|\?.*/', '', $url);
   if(preg_match('/\?/', $url)){
    $par = preg_replace('/.*\?/', '', $url);
    $vars = split(',', $rep);
   
     foreach($vars as $rep)
      $par = preg_replace('/.*\?|&?'.$rep.'=[^&]*/', '', $par);
   }
   $res = $res.'?'.$par;
  }else{
   $res = preg_replace('/.*\/|\?.*/', '', $url);
   preg_match('/('.$param.'=[^=&]*)/', $url, $mat);
   $res = $res.'?'.$mat[1];
  }

  $res .= $close && !preg_match('/\?$/', $res) ? (preg_match('/\?/', $res) ? '&' : '?') : '';
  return $res;
 }

 function ip_dec($param){
  return hexdec(substr($param, 0, 2)).'.'.hexdec(substr($param, 2, 2)).'.'.hexdec(substr($param, 4, 2)).'.'.hexdec(substr($param, 6, 2));
 }

 function ip_hex($param){
  $ret = '';
  $arr = split('\.', $param);
  foreach($arr as $dec)
   $ret .= strlen(dechex($dec)) == 1 ? '0'.dechex($dec) : (string)dechex($dec);
  return $ret;
 }

 function a_footer(){
  include('includes/a_footer.php');
 }

 function a_header($title = '', $menu = 1){
  global $wysiwyg, $plugDir;
  echo ($wysiwyg ? '<script src='.$plugDir.'/wysiwyg/includes/eInterface.php></script>' : '');
  include('includes/'.($menu ? 'a' : 's').'_header.php');
 }

 function open_table($title = ''){
  echo '<table cellspacing=0 cellpadding=0 bgcolor=#E4E4DD>
     <tr><td><img src=images/a12.gif width=8 height=8></td><td background=images/a13.gif></td><td><img src=images/a15.gif width=8 height=8></td></tr>
     <tr><td></td><td style="padding:5px;">'.($title ? '<span class=he3>'.$title.'</span>' : '').($title ? '</td></tr>
     <tr><td></td><td style="border-top: #666666 solid 1px;">&nbsp;</td><td></tr><tr><td></td><td>' : '');
 }

 function close_table(){
  echo '</td><td></td></tr>
     <tr><td><img src=images/a28.gif width=8 height=8></td><td></td><td><img src=images/a29.gif width=8 height=8></td></tr>
    </table>';
 }

?>