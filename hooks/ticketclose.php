<?php
$hook = array(
    'hook' => 'TicketClose',
    'function' => 'TicketClose',
    'description' => array(
        'turkish' => 'Ticket kapatıldığında mesaj gönderir.',
        'english' => 'When the ticket is closed it sends a message.'
    ),
    'type' => 'client',
    'extra' => '',
	'defaultmessage' => 'Dear {firstname} {lastname}, ({ticketno}) numarali ticket kapatilmistir.',
    'variables' => '{firstname}, {lastname}, {ticketno}',
);

if(!function_exists('TicketClose')){
    function TicketClose($args){
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
        SELECT a.tid,b.id as userid,b.firstname,b.lastname,`b`.`phonenumber` as `gsmnumber` FROM `tbltickets` as `a`
        JOIN tblclients as b ON b.id = a.userid
        JOIN `tblcustomfieldsvalues` as `d` ON `d`.`relid` = `a`.`userid`
        WHERE a.id = '".$args['ticketid']."'
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
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$UserInformation['tid']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($UserInformation['userid']);
            $class->send();
        }
    }
}

return $hook;
