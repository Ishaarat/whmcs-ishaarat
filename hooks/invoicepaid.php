<?php
$hook = array(
    'hook' => 'InvoicePaid',
    'function' => 'InvoicePaid',
    'description' => array(
        'turkish' => 'Faturanız ödendiğinde mesaj gönderir',
        'english' => 'Whenyou have paidthe billsends a message.'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Dear {firstname} {lastname}, {duedate} son odeme tarihli faturaniz odenmistir. Odeme icin tesekkur ederiz.',
    'variables' => '{firstname}, {lastname}, {duedate},{invoiceid}'
);
if(!function_exists('InvoicePaid')){
    function InvoicePaid($args){

        $class = new IshaaratWa();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['auth_key'] || !$settings['app_key'] || !$settings['wantsmsfield']){
            return null;
        }

        $userSql = "
        SELECT a.duedate,b.id as userid,b.firstname,b.lastname,`b`.`phonenumber` as `gsmnumber` FROM `tblinvoices` as `a`
        JOIN tblclients as b ON b.id = a.userid
        JOIN `tblcustomfieldsvalues` as `d` ON `d`.`relid` = `a`.`userid`
        WHERE a.id = '".$args['invoiceid']."'
        AND `d`.`fieldid` = '".$settings['wantsmsfield']."'
        AND `d`.`value` = 'on'
        LIMIT 1
    ";

        $result = mysql_query($userSql);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);
            
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$class->changeDateFormat($UserInformation['duedate']),$args['invoiceid']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($UserInformation['userid']);
            $class->send();
        }
    }
}

return $hook;
