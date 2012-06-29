<?php

class Block_Keyword_Edit extends Block_Abstract
{
    public function output()
    {
        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');

        /** @var $keyword Model_Keyword */
        $keyword = Application::getSingleton('keyword');

        $avail_lang = array('all' => '', 'en' => 'en', 'de' => 'de');
        $options = '';
        while(list($key, $val) = each($avail_lang)){
            $options .= '<option value="'.$val.'"'.($val == $keyword->getData('query_lang') ? ' selected' : '').'>'.$key.'</option>';
        }

        if($keyword->getData('query_distanceunit') == 'mi') {
            $radio_distanceunit = '<input type="radio" name="query_distanceunit" value="mi" checked>miles
							       <input type="radio" name="query_distanceunit" value="km" >kilometer';
        } else {
            $radio_distanceunit = '<input type="radio" name="query_distanceunit" value="mi" >miles
					               <input type="radio" name="query_distanceunit" value="km" checked>kilometer';
        }

        return '<form method=post action="'.$this->getUrl('projects/savequery').'"><input type=hidden name=id value="'.$keyword->getId().'" /><input type="hidden" name="project_id" value="'.$campaign->getId().'" />
           <table>
            <tr><td>Keyword:</td><td><input type="text" value="'.$keyword->getData('query_q').'" name="query_q" /></td></tr>
            <tr><td>Language:</td><td><select name="query_lang">'.$options.'</select></td></tr>
            <!--tr><td>Geo Code:</td><td><input type="text" value="'.$keyword->getData('query_geocode').'" name="query_geocode" /></td></tr-->
			<tr><td>Near this place:</td><td><input type="text" value="'.$keyword->getData('query_nearplace').'" name="query_nearplace" /></td></tr>
			<tr><td>Within this distance:</td><td><input type="text" value="'.$keyword->getData('query_distance').'" name="query_distance" /></td></tr>
			<tr><td></td><td>'.$radio_distanceunit.'</td></tr>
           </table><br />
           <div align="center"><input type=submit value="&nbsp; Save &nbsp;" class=bu> <input type=button value="Cancel" class=bu onclick="location.href = \''.$this->getUrl('projects/queries/'.$campaign->getId()).'\'"></div>
          </form>';
    }
}