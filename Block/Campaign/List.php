<?php

class Block_Campaign_List extends Block_Campaign
{
    public function output(){
        global $prefix;

        $ret = '<table cellpadding=5 cellspacing=0 width=100% class="t1t">';
        $query = 'SELECT * FROM '.$prefix.'project';
        $res = mysql_query($query);
        while($res && $page = mysql_fetch_object($res)){
            $query = 'SELECT q.*, COUNT(s.search_id) cnt, s.search_published
	            FROM '.$prefix.'project_to_query p2q
		   LEFT JOIN '.$prefix.'query q ON q.query_id = p2q.query_id
		   LEFT JOIN '.$prefix.'search s ON s.query_id = p2q.query_id
			   WHERE p2q.project_id = "'.$page->project_id.'"
			GROUP BY p2q.query_id
			ORDER BY s.search_published ASC';
            $re0 = mysql_query($query);
            $keywords = array();
            $cnt = $first_date = 0;
            while($re0 && $keyword = mysql_fetch_object($re0)){
                $keywords[] = '<a href="'.$this->getUrl('projects/results/query/'.$keyword->query_id).'">'.$keyword->query_q.($keyword->query_lang ? ' ('.$keyword->query_lang.')' : '').'</a>';
                $cnt += $keyword->cnt;
                $first_date = !$first_date || $first_date > $keyword->search_published ? $keyword->search_published : $first_date;
            }
            $keywords = implode(', ', $keywords);
            $ret .= '<tr'.($page->project_status ? '' : ' class="disabled"').'>
           <td class="rw"><h4><a href="'.$this->getUrl('projects/results/project/'.$page->project_id).'">'.$page->project_name.'</a></h4>Weekly mentions: '.round($cnt/ceil((time() - $first_date)/(3600*24*7))).' | Daily Mentions: '.round($cnt/ceil((time() - $first_date)/(3600*24))).'<br />Keywords: '.$keywords.'</td>
           <td class="rw" style="padding-right:25px;" align="right">
		    <a href="'.$this->getUrl('projects/results/project/'.$page->project_id).'">results</a> -
            <a href="'.$this->getUrl('projects/queries/'.$page->project_id).'">keywords</a> -
            <a href="'.$this->getUrl('projects/edit/'.$page->project_id).'">edit</a> -
            <a href="'.$this->getUrl('projects/status/'.$page->project_id).'">'.($page->project_status ? 'disable' : 'enable').'</a> -
            <a href="'.$this->getUrl('projects/delete/'.$page->project_id).'" onclick="return confirm(\'Are you sure? All keyword(s) data will be wiped out.\');">delete</a>
           </td>
          </tr>';
        }
        $ret .= '</table><br /><input type="button" class="bu" value="Add Project" onclick="location.href = \''.$this->getUrl('projects/edit').'\'" />';

        return $ret;
    }
}