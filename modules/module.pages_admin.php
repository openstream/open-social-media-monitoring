<?

 if($query_mode){
  $module_name = 'Keyword Management';
  $module_version = '1.0.0';
  $module_description = '';
  $module_author = 'OpenStream';
  $module_release_date = 'April 12, 2011';
 }else{

  // Normal Mode

  switch($_REQUEST['b']){
   default:
    a_header('Pages');

    echo '<table cellpadding=5 cellspacing=0 width=100% class=t1>
           <tr><td class=he2><b>Keyword</b></td>
               <td class=he2 width=150><b>Language</b></td>
               <td class=he2 width=150><b>Geo Code</b></td>
               <td align=center class=he2 width=190><b>Action</b></td>
           </tr>';
    $query = 'SELECT * FROM '.$prefix.'query';
    $res = mysql_query($query);
    while($res && $page = mysql_fetch_object($res))
     echo '<tr>
            <td align=center class=rw>'.$page->query_q.'</td>
            <td class=rw align=center>'.($page->query_lang ? $page->query_lang : '-').'</td>
            <td class=rw align=center>'.($page->query_geocode ? $page->query_geocode : '-').'</td>
            <td class=rw align=center>
             <a href="'.url_param('a', 1).'b=3&id='.$page->query_id.'">results</a> -
             <a href="'.url_param('a', 1).'b=1&id='.$page->query_id.'">edit</a>
            </td>
           </tr>';
    echo '</table>';

    a_footer();
    break;

    case 1: // ----------------------- EDIT QUERY ----------------------------------
    a_header('');

    $query = 'SELECT * FROM '.$prefix.'query WHERE query_id = '.$_REQUEST['id'];
    $res = mysql_query($query);
    if($res && mysql_num_rows($res))
     $page = mysql_fetch_object($res);

    echo '<form method=post action="'.url_param('a', 1).'b=2"><input type=hidden name=id value="'.(int)$_REQUEST['id'].'">
           <table>
            <tr><td>Keyword:</td><td><input type="text" value="'.$page->query_q.'" name="query_q" /></td></tr>
            <tr><td>Language:</td><td><input type="text" value="'.$page->query_lang.'" name="query_lang" /></td></tr>
            <tr><td>Geo Code:</td><td><input type="text" value="'.$page->query_geocode.'" name="query_geocode" /></td></tr>
           </table><br />
           <div align="center"><input type=submit value="&nbsp; Save &nbsp;" class=bu> <input type=button value="Cancel" class=bu onclick="location.href = \''.url_param('a').'\'"></div>
          </form>';
          
    a_footer();

    break;

   case 2: // ----------------------- SAVE QUERY -----------------------------

    $query = 'SELECT * FROM '.$prefix.'query WHERE query_id = '.$_REQUEST['id'];
    $res = mysql_query($query);
    if($res && mysql_num_rows($res)){
     $query = 'UPDATE '.$prefix.'query 
                  SET query_q = "'.$_REQUEST['query_q'].'", 
                      query_lang = "'.$_REQUEST['query_lang'].'",
                      query_geocode = "'.$_REQUEST['query_geocode'].'" 
                WHERE query_id = "'.$_REQUEST['id'].'"';
    }else{
     $query = 'INSERT INTO '.$prefix.'query 
                  SET query_q = "'.$_REQUEST['query_q'].'", 
                      query_lang = "'.$_REQUEST['query_lang'].'",
                      query_geocode = "'.$_REQUEST['query_geocode'].'"';
    }
    $res = mysql_query($query);
    header('Location: '.url_param('a'));

    break;

   case 3: // ------------------- RESULTS ----------------------------------------------
    a_header('');

    $query = 'SELECT s.search_id 
                FROM '.$prefix.'search s
          INNER JOIN '.$prefix.'search_entity se ON se.search_id = s.search_id
               WHERE s.query_id = '.(int)$_REQUEST['id'].'
                 AND se.search_entity_name = "published"
            ORDER BY se.search_entity_value DESC';
    $res = mysql_query($query);
    while($res && $search = mysql_fetch_object($res)){
     $query = 'SELECT * FROM '.$prefix.'search_entity WHERE search_id = '.(int)$search->search_id;
     $re2 = mysql_query($query);
     $entity = array();
     while($re2 && $obj = mysql_fetch_object($re2)){
      if($obj->search_entity_name == 'link'){
       if(!isset($entity['link'])){
        $entity['link'] = array();
       }
       $entity['link'][] = json_decode(stripslashes($obj->search_entity_value));
      }else{
       $entity[$obj->search_entity_name] = $obj->search_entity_value;
      }
     }
     while(is_array($entity['link']) && list($key, $link) = each($entity['link'])){
      if($link->{'@attributes'}->type == 'image/png'){
       echo '<img src="'.$link->{'@attributes'}->href.'" alt="" align="left" style="height:48px; width:48px; margin-right:10px; margin-bottom:5px;" />';
      }
     }
     echo $entity['content'].'<br><i>'.date('l, F jS, Y H:i', $entity['published']).'</i><hr style="clear:both;" />';
    }

    a_footer();
    break;
  }
 }

?>