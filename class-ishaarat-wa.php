<?php
/* WHMCS Ishaarat Whatsapp Addon with GNU/GPL Licence
 * Ishaarat - http://www.ishaart.com
 *
 * https://github.com/ishaarat/whmcs-ishaarat
 *
 * Developed at Ishaarat (www.ishaarat.com)
 * Licence: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.txt)
 * */

class IshaaratWa{
    public $params;
    public $gsmnumber;
    public $message;

    public $userid;
    var $errors = array();
    var $logs = array();

    /**
     * @param mixed $gsmnumber
     */
    public function setGsmnumber($gsmnumber){
        $this->gsmnumber = $this->util_gsmnumber($gsmnumber);
    }

    /**
     * @return mixed
     */
    public function getGsmnumber(){
        return $this->gsmnumber;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message){
        $this->message = $this->util_convert($message);
    }

    /**
     * @return mixed
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param int $userid
     */
    public function setUserid($userid){
        $this->userid = $userid;
    }

    /**
     * @return int
     */
    public function getUserid(){
        return $this->userid;
    }

    /**
     * @return array
     */
    public function getParams(){
        $settings = $this->getSettings();
        $params = [
                'api_auth'=> $settings['auth_key'],
                'app_key'=> $settings['app_key']
        ];
        return $params;
    }

    /**
     * @return array
     */
    public function getSettings(){
        $result = select_query("ishaarat_wa_settings", "*", "id=1");
        return mysql_fetch_array($result);
    }
    function send(){
        
        $params = $this->getParams();
        $message = $this->message;

        $this->addLog("Params: ".json_encode($params));
        $this->addLog("To: ".$this->getGsmnumber());
        $this->addLog("Message: ".$message);

        $headers     = array(
			'Authorization: Bearer ' . $params['api_auth'],
            'Content-Type: application/json'
		);
		$data        = [
			'appkey'      => $params['app_key'],
			'to'          => $this->getGsmnumber(),
			'message'     => trim($message),
			'template_id' => 0
		];
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_URL,"https://ishaarat.com/api/create-message");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER , $headers); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if($result != false){
            $result =json_decode($result, true);
            if ( $result['status'] == 'success' ) {
                $this->saveToDb('success');
                return true;
            }else{
              $this->saveToDb('error',json_encode($result));
              return true;  
            }
        }else{
            $this->saveToDb('error',curl_error($ch),curl_error($ch));
            $this->addError(curl_error($ch));
        }
        return false;
    }
    function getHooks(){
        if ($handle = opendir(dirname(__FILE__).'/hooks')) {
            while (false !== ($entry = readdir($handle))) {
                if(substr($entry,strlen($entry)-4,strlen($entry)) == ".php"){
                    $file[] = require_once('hooks/'.$entry);
                }
            }
            closedir($handle);
        }
        return $file;
    }

    function saveToDb($status,$errors = null,$logs = null){
        $now = date("Y-m-d H:i:s");
        $table = "ishaarat_wa_log";
        $values = array(
            "to" => $this->getGsmnumber(),
            "text" => $this->getMessage(),
            "status" => $status,
            "errors" => $errors,
            "logs" => $logs,
            "user" => $this->getUserid(),
            "datetime" => $now
        );
        insert_query($table, $values);
        $this->addLog("Message saved to DB");
    }

    /* Main message convert function. Will be removed next release */
    function util_convert($message){
        $changefrom = array('ı', 'İ', 'ü', 'Ü', 'ö', 'Ö', 'ğ', 'Ğ', 'ç', 'Ç','ş','Ş');
        $changeto = array('i', 'I', 'u', 'U', 'o', 'O', 'g', 'G', 'c', 'C','s','S');
        return str_replace($changefrom, $changeto, $message);
    }

    /* Default number format */
    function util_gsmnumber($number){
        $replacefrom = array('-', '(',')', '.', ',', '+', ' ');
        $number = str_replace($replacefrom, '', $number);

        return $number;
    }

    public function addError($error){
        $this->errors[] = $error;
    }

    public function addLog($log){
        $this->logs[] = $log;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $res = '<pre><p><ul>';
        foreach($this->errors as $d){
            $res .= "<li>$d</li>";
        }
        $res .= '</ul></p></pre>';
        return $res;
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        $res = '<pre><p><strong>Debug Result</strong><ul>';
        foreach($this->logs as $d){
            $res .= "<li>$d</li>";
        }
        $res .= '</ul></p></pre>';
        return $res;
    }

    /*
     * Runs at addon install/update
     * This function controls that if there is any change at hooks files. Such as new hook, variable changes at hooks.
     */
    function checkHooks($hooks = null){
        if($hooks == null){
            $hooks = $this->getHooks();
        }

        $i=0;
        foreach($hooks as $hook){
            $sql = "SELECT `id` FROM `ishaarat_wa_templates` WHERE `name` = '".$hook['function']."' AND `type` = '".$hook['type']."' LIMIT 1";
            $result = mysql_query($sql);
            $num_rows = mysql_num_rows($result);
            if($num_rows == 0){
                if($hook['type']){
                    $values = array(
                        "name" => $hook['function'],
                        "type" => $hook['type'],
                        "template" => $hook['defaultmessage'],
                        "variables" => $hook['variables'],
                        "extra" => $hook['extra'],
                        "description" => json_encode(@$hook['description']),
                        "active" => 1
                    );
                    insert_query("ishaarat_wa_templates", $values);
                    $i++;
                }
            }else{
                $values = array(
                    "variables" => $hook['variables']
                );
                update_query("ishaarat_wa_templates", $values, "name = '" . $hook['name']."'");
            }
        }
        return $i;
    }

    function getTemplateDetails($template = null){
        $where = array("name" => $template);
        $result = select_query("ishaarat_wa_templates", "*", $where);
        $data = mysql_fetch_assoc($result);

        return $data;
    }

    function changeDateFormat($date = null){
        $settings = $this->getSettings();
        $dateformat = $settings['dateformat'];
        if(!$dateformat){
            return $date;
        }

        $date = explode("-",$date);
        $year = $date[0];
        $month = $date[1];
        $day = $date[2];

        $dateformat = str_replace(array("%d","%m","%y"),array($day,$month,$year),$dateformat);
        return $dateformat;
    }

}
