<?php
$hook = array(
    'hook' => 'ClientAdd',
    'function' => 'ClientAdd_admin',
    'type' => 'admin',
    'extra' => '',
    'defaultmessage' => 'Sitenize yeni musteri kayit oldu.',
    'variables' => ''
);
if(!function_exists('ClientAdd_admin')){
    function ClientAdd_admin($args){
        $class = new IshaaratWa();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['auth_key'] || !$settings['app_key'] ){
            return null;
        }
        $admingsm = explode(",",$template['admingsm']);

        foreach($admingsm as $gsm){
            if(!empty($gsm)){
                $class->setGsmnumber(trim($gsm));
                $class->setUserid(0);
                $class->setMessage($template['template']);
                $class->send();
            }
        }
    }
}
return $hook;