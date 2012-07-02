<?php

class Block_Installer_Run extends Block_Installer
{
    public $message;

    public function output()
    {
        return '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel=stylesheet href=st.css></head><body class="installer"><center>'.
        $this->openTable('Error').
        '<p>Not able to connect to database.</p>'.
        $this->message.
        $this->closeTable().
        '</body></html>';
    }
}