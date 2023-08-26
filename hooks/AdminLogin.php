<?php
$hook = array(
    'hook' => 'AdminLogin',
    'function' => 'AdminLogin_admin',
    'type' => 'admin',
    'extra' => '',
    'defaultmessage' => '{username}, Yonetici paneline giris yapti.',
    'variables' => '{username}'
);
if(!function_exists('AdminLogin_admin')){
    function AdminLogin_admin($args){
        $class = new IshaaratWa();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();

        if(!$settings['auth_key'] || !$settings['app_key'] || !$settings['gsmnumberfield']){
            return null;
        }
        $admingsm = explode(",",$template['admingsm']);

        $template['variables'] = str_replace(" ","",$template['variables']);
        $replacefrom = explode(",",$template['variables']);
        $replaceto = array($args['username']);
        $message = str_replace($replacefrom,$replaceto,$template['template']);

        foreach($admingsm as $gsm){
            if(!empty($gsm)){
                $class->setGsmnumber( trim($gsm));
                $class->setUserid(0);
                $class->setMessage($message);
                $class->send();
            }
        }
    }
}

return $hook;
