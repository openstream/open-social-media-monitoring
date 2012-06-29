<?php

class Block_Settings_Form extends Block_Settings
{
    public function output(){
        global $admPassword, $adminEmail, $defaultFrom, $dbHost, $dbUser, $dbPassword, $dbName, $prefix, $keep_history, $alchemy_api_key;

        return $this->openTable('Server Configuration').'<table width="632" cellpadding=4 cellspacing=2><form method=post action="'.$this->getUrl('settings/save').'">
            <tr><td align=right><b>Administrator E-Mail:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=adminEmail size="50" value="'.$adminEmail.'"></td></tr>
            <tr><td align=right><b>Administator Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=admPassword size="50" value="'.$admPassword.'"></td></tr>
            <tr><td align=right><b>Default From Address:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=defaultFrom size="50" value="'.$defaultFrom.'"></td></tr>
            <tr><td align=right><b>MySQL Server:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbHost value="'.$dbHost.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Login:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbUser value="'.$dbUser.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=dbPassword value="'.$dbPassword.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Database:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbName value="'.$dbName.'" size="50"></td></tr>
            <tr><td align=right><b>Database Prefix:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=prefix value="'.$prefix.'" size="50"></td></tr>
            <tr><td align=right><b>Show timeline for last:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type="text" name="keep_history" value="'.$keep_history.'" size="3"/> days</td></tr>
            <tr><td align=right><b>Alchemy API Key</b> (<a href="" target="_blank">Get It Here</a>):</td><td style="padding-top:2px;padding-bottom:2px;"><input type="text" name="alchemy_api_key" value="'.$alchemy_api_key.'" size="50"/></td></tr>
            <tr><td></td><td><input type=submit value="Save Settings" class=bu></td></tr>
           </form></table>'.$this->closeTable();

    }
}