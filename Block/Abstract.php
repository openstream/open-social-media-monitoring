<?php

class Block_Abstract
{
    private $_processList = array();

    public function processBlock($blockName)
    {
        $blockName = trim($blockName);
        if($blockName) {
            $this->_processList[] = $blockName;
        }
    }

    public function renderLayout()
    {
        $this->a_header();
        foreach($this->_processList as $block){
            /** @var $block Block_Abstract */
            $block = Application::getBlock($block);
            echo $block->output();
        }
        $this->a_footer();
    }

    public function output()
    {
        return '';
    }

    public function getUrl($route = ''){
        global $directory;
        $backslash = $route && !preg_match('/\.js$/ism', $route) ? '/' : '';

        return (preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$directory.trim($route, '/').$backslash;
    }

    function a_footer(){
        include('includes/a_footer.php');
    }

    function a_header(){
        global $directory;
        include('includes/a_header.php');
    }

    function openTable($title){
        return '<table cellspacing=0 cellpadding=0 bgcolor=#E4E4DD>
                <tr><td><img src=images/a12.gif width=8 height=8></td><td background=images/a13.gif></td><td><img src=images/a15.gif width=8 height=8></td></tr>
                <tr><td></td><td style="padding:5px;">'.($title ? '<span class=he3>'.$title.'</span>' : '').($title ? '</td></tr>
                <tr><td></td><td style="border-top: #666666 solid 1px;">&nbsp;</td><td></tr><tr><td></td><td>' : '');
    }

    function closeTable(){
        return '</td><td></td></tr>
              <tr><td><img src="images/a28.gif" alt="" width="8" height="8"/></td><td></td><td><img src="images/a29.gif" alt="" width="8" height="8"/></td></tr>
              </table>';
    }
}