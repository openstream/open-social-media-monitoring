<?php

class Block_Keyword_List extends Block_Keyword
{
    public function output()
    {
        global $prefix;

        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');

        $ret = '<table cellpadding=5 cellspacing=0 width=100% class=t1>
          <tr><td class=he2><strong>Keyword</strong></td>
              <td class=he2 width=150><strong>Language</strong></td>
              <td class=he2 width=150><strong>Geo Code</strong></td>
              <td align=center class=he2 width=190><strong>Action</strong></td>
          </tr>';
        $query = 'SELECT q.* FROM '.$prefix.'query q INNER JOIN '.$prefix.'project_to_query p2q ON p2q.query_id = q.query_id WHERE p2q.project_id = '.$campaign->getId();
        $res = mysql_query($query);
        while($res && $page = mysql_fetch_object($res)) {
            $ret .= '<tr>
           <td align=center class=rw>'.$page->query_q.'</td>
           <td class=rw align=center>'.($page->query_lang ? $page->query_lang : '-').'</td>
           <td class=rw align=center>'.($page->query_geocode ? $page->query_geocode : '-').'</td>
           <td class=rw align=center>
            <a href="'.$this->getUrl('projects/results/query/'.$page->query_id).'">results</a> -
            <a href="'.$this->getUrl('projects/editquery/'.$campaign->getId().'/'.$page->query_id).'" onclick="return confirm(\'Are you sure? All keyword stored data will be wiped out.\');">edit</a> -
            <a href="'.$this->getUrl('projects/delete/'.$campaign->getId().'/'.$page->query_id).'" onclick="return confirm(\'Are you sure? All keyword stored data will be wiped out.\');">delete</a>
           </td>
          </tr>';
        }
        $ret .= '</table><br /><input type="button" class="bu" value="Add Query" onclick="location.href = \''.$this->getUrl('projects/editquery/'.$campaign->getId()).'\'" />';

        return $ret;
    }
}