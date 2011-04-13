<?

 session_start();
 include('includes/settings.php');
 include('includes/functions.php');

 if($_POST['login'] == 'admin' && $_POST['password'] == $admPassword){
  $_SESSION['a_login'] = $_POST['login'];
  $_SESSION['a_password'] = $admPassword;
  header('Location: admin.php');
 }

 include('includes/s_header.php');

 echo '<body style="background-color:#FFFFFF;"><br><br><br><br><br><br><br><br><center>';
 open_table('Administrator Logon');

 echo '<table><form method=post>
        <tr><td>Login:</td><td><input name=login type=text></td></tr>
        <tr><td>Password:</td><td><input name=password type=password></td></tr>
        <tr><td></td><td><input type=submit value=Login></td></tr>
       </form></table>

     <script>
      onload = function(){
       document.forms[0].login.focus();
      }
     </script>
     <style>
      td{font-family: Verdana, Tahoma, Arial; font-size: 10px;}
      input, select{font-size: 10px;}
      .he{background-image: url(../img/bg01.gif); font-weight: bold; color: #005B90;}
     </style>
 ';
 close_table();

?>