<?php

class Block_Campaign_Edit extends Block_Campaign
{
    public function output()
    {
        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');

        return '<form method=post action="'.$this->getUrl('projects/save').'"><input type="hidden" name="id" value="'.$campaign->getId().'" />
           <table>
            <tr><td>Project Name:</td><td><input type="text" value="'.$campaign->getData('project_name').'" name="project_name" /></td></tr>
           </table><br />
           <div align="center"><input type=submit value="&nbsp; Save &nbsp;" class=bu> <input type=button value="Cancel" class=bu onclick="location.href = \''.$this->getUrl('projects').'\'"></div>
          </form>';

    }
}