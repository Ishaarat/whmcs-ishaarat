<?php
$hook = array(
    'hook' => 'AfterRegistrarRegistration',
    'function' => 'AfterRegistrarRegistration',
    'description' => array(
        'turkish' => 'Bir domain kayıt edildikten sonra mesaj gönderir',
        'english' => 'After domain registration'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Dear {firstname} {lastname}, alan adiniz basariyla kayit edildi. ({domain})',
    'variables' => '{firstname},{lastname},{domain}'
);
if(!function_exists('AfterRegistrarRegistration')){
    function AfterRegistrarRegistration($args){
    $class = new IshaaratWa();
    $template = $class->getTemplateDetails(__FUNCTION__);
    if($template['active'] == 0){
        return null;
    }
    $settings = $class->getSettings();
    if(!$settings['auth_key'] || !$settings['app_key'] || !$settings['wantsmsfield']){
        return null;
    }

    $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`phonenumber` as `gsmnumber`
	FROM `tblclients` as `a`
	
	JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
	WHERE `a`.`id` = '".$args['params']['userid']."'
	
	AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
	AND `c`.`value` = 'on'
	LIMIT 1";
    $result = mysql_query($userSql);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){
        $UserInformation = mysql_fetch_assoc($result);

        $template['variables'] = str_replace(" ","",$template['variables']);
        $replacefrom = explode(",",$template['variables']);
        $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$args['params']['sld'].".".$args['params']['tld']);
        $message = str_replace($replacefrom,$replaceto,$template['template']);

        $class->setGsmnumber($UserInformation['gsmnumber']);
        $class->setUserid($args['params']['userid']);
        $class->setMessage($message);
        $class->send();
    }

}
}

return $hook;