<?php

class Cron_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $cron = $bootstrap->getOption('cron');
        if (is_array($cron)) {
            foreach ($cron as $model) {
                if (class_exists($model)) {
                    $model = new $model;
                    if (method_exists($model, 'run')) {
                        $model->run($bootstrap);
                    }
                }
            }
        }
        exit;
    }
}
