<?php
/* WHMCS Ishaarat Whatsapp Addon with GNU/GPL Licence
 * Ishaarat - http://www.ishaart.com
 *
 * https://github.com/ishaarat/whmcs-ishaarat
 *
 * Developed at Ishaarat (www.ishaarat.com)
 * Licence: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.txt)
 * */
if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function ishaarat_wa_config() {
    $configarray = array(
        "name" => "Ishaarat Whatsapp",
        "description" => "WHMCS Sms Addon. You can see details from: https://github.com/ishaarat/whmcs-ishaarat",
        "version" => "1.0.0",
        "author" => "Ishaarat Tech Team",
		"language" => "english",
    );
    return $configarray;
}

function ishaarat_wa_activate() {

    $query = "CREATE TABLE IF NOT EXISTS `ishaarat_wa_log` (`id` int(11) NOT NULL AUTO_INCREMENT,`to` varchar(15) DEFAULT NULL,`text` text,`status` varchar(10) DEFAULT NULL,`errors` text,`logs` text,`user` int(11) DEFAULT NULL,`datetime` datetime NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `ishaarat_wa_settings` (`id` int(11) NOT NULL AUTO_INCREMENT,`auth_key` varchar(255) CHARACTER SET utf8 NOT NULL,`app_key` varchar(255) CHARACTER SET utf8 NOT NULL,`wantsmsfield` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `ishaarat_wa_templates` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(50) CHARACTER SET utf8 NOT NULL,`type` enum('client','admin') CHARACTER SET utf8 NOT NULL,`admingsm` varchar(255) CHARACTER SET utf8 NOT NULL,`template` varchar(240) CHARACTER SET utf8 NOT NULL,`variables` varchar(500) CHARACTER SET utf8 NOT NULL,`active` tinyint(1) NOT NULL,`extra` varchar(3) CHARACTER SET utf8 NOT NULL,`description` text CHARACTER SET utf8,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);

    //Creating hooks
	require("class-ishaarat-wa.php");
    $class = new IshaaratWa();
    $class->checkHooks();

    return array('status'=>'success','description'=>'Ishaarat Whatsapp succesfully activated :)');
}

function ishaarat_wa_deactivate() {

    $query = "DROP TABLE `ishaarat_wa_log`";
	mysql_query($query);
    $query = "DROP TABLE `ishaarat_wa_settings`";
    mysql_query($query);
    $query = "DROP TABLE `ishaarat_wa_templates`";
    mysql_query($query);

    return array('status'=>'success','description'=>'Ishaarat Whatsapp succesfully deactivated :(');
}

function ishaarat_wa_output($vars){
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	putenv("TZ=Africa/Cairo");
    $class = new IshaaratWa();

    $tab = $_GET['tab'];
    echo '
    <div id="clienttabs">
        <ul class="nav nav-tabs admin-tabs">
            <li class="' . (($tab == "settings")?"tabselected":"tab") . '"><a href="addonmodules.php?module=ishaarat_wa&tab=settings">'.$LANG['settings'].'</a></li>
            <li class="' . ((@$_GET['type'] == "client")?"tabselected":"tab") . '"><a href="addonmodules.php?module=ishaarat_wa&tab=templates&type=client">'.$LANG['clientsmstemplates'].'</a></li>
            <li class="' . ((@$_GET['type'] == "admin")?"tabselected":"tab") . '"><a href="addonmodules.php?module=ishaarat_wa&tab=templates&type=admin">'.$LANG['adminsmstemplates'].'</a></li>
            <li class="' . (($tab == "sendbulk")?"tabselected":"tab") . '"><a href="addonmodules.php?module=ishaarat_wa&tab=sendbulk">'.$LANG['sendsms'].'</a></li>
            <li class="' . (($tab == "messages")?"tabselected":"tab") . '"><a href="addonmodules.php?module=ishaarat_wa&amp;tab=messages">'.$LANG['messages'].'</a></li>
        </ul>
    </div>
    ';
    if (!isset($tab) || $tab == "settings")
    {
        /* UPDATE SETTINGS */
        if ($_POST['auth_key']) {
            $update = array(
                "auth_key" => $_POST['auth_key'],
                "app_key" => $_POST['app_key'],
                'wantsmsfield' => $_POST['wantsmsfield'] ?? null
            );
            $sql = "SELECT `id` FROM `ishaarat_wa_settings` WHERE `id` = 1 LIMIT 1";
            $result = mysql_query($sql);
            $num_rows = mysql_num_rows($result);
            if($num_rows > 0){
                update_query("ishaarat_wa_settings", $update, "id = '1'");
            }else{
                $update['id'] = "1";
                insert_query("ishaarat_wa_settings", $update);
            }
            
        }
        /* UPDATE SETTINGS */

        $settings = $class->getSettings();
        $where = array(
            "fieldtype" => array("sqltype" => "LIKE", "value" => "tickbox"),
            "showorder" => array("sqltype" => "LIKE", "value" => "on")
        );
        $result = select_query("tblcustomfields", "id,fieldname", $where);
        $wantsms = '';
        while ($data = mysql_fetch_array($result)) {
            if ($data['id'] == $settings['wantsmsfield']) {
                $selected = 'selected="selected"';
            } else {
                $selected = "";
            }
            $wantsms .= '<option value="' . $data['id'] . '" ' . $selected . '>' . $data['fieldname'] . '</option>';
        }

        $where = array(
            "fieldtype" => array("sqltype" => "LIKE", "value" => "text"),
            "showorder" => array("sqltype" => "LIKE", "value" => "on")
        );
        $result = select_query("tblcustomfields", "id,fieldname", $where);
        echo '
        <form action="" method="post" id="form">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['auth_key'].'</td>
                            <td class="fieldarea"><input type="text" name="auth_key" size="40" value="' . $settings['auth_key'] . '"> e.g:  123456789123</td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['app_key'].'</td>
                            <td class="fieldarea"><input type="text" name="app_key" size="40" value="' . $settings['app_key']. '"> e.g:  1234-5678-90123</td>
                        </tr>
                        <tr>
                        <td class="fieldlabel" width="30%">'.$LANG['wantsmsfield'].'</td>
                        <td class="fieldarea">
                            <select name="wantsmsfield">
                                ' . $wantsms . '
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" value="'.$LANG['save'].'" class="button" /></p>
        </form>
        ';
    }
    elseif ($tab == "templates")
    {
        if ($_POST['submit']) {
            $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
            $result = select_query("ishaarat_wa_templates", "*", $where);
            while ($data = mysql_fetch_array($result)) {
                if ($_POST[$data['id'] . '_active'] == "on") {
                    $tmp_active = 1;
                } else {
                    $tmp_active = 0;
                }
                $update = array(
                    "template" => $_POST[$data['id'] . '_template'],
                    "active" => $tmp_active
                );

                if(isset($_POST[$data['id'] . '_extra'])){
                    $update['extra']= trim($_POST[$data['id'] . '_extra']);
                }
                if(isset($_POST[$data['id'] . '_admingsm'])){
                    $update['admingsm']= $_POST[$data['id'] . '_admingsm'];
                    $update['admingsm'] = str_replace(" ","",$update['admingsm']);
                }
                update_query("ishaarat_wa_templates", $update, "id = " . $data['id']);
            }
        }

        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>';
        $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
        $result = select_query("ishaarat_wa_templates", "*", $where);

        while ($data = mysql_fetch_array($result)) {
            if ($data['active'] == 1) {
                $active = 'checked = "checked"';
            } else {
                $active = '';
            }
            $desc = json_decode($data['description']);
            if(isset($desc->$LANG['lang'])){
                $name = $desc->$LANG['lang'];
            }elseif(!empty($LANG[strtolower($data['name'])])){
                $name = $LANG[strtolower($data['name'])];
            }else{
                $name = $data['name'];
            }
            echo '
                <tr>
                    <td class="fieldlabel" width="30%">' . $name . '</td>
                    <td class="fieldarea">
                        <textarea cols="50" name="' . $data['id'] . '_template">' . $data['template'] . '</textarea>
                    </td>
                </tr>';
            echo '
            <tr>
                <td class="fieldlabel" width="30%" style="float:right;">'.$LANG['active'].'</td>
                <td><input type="checkbox" value="on" name="' . $data['id'] . '_active" ' . $active . '></td>
            </tr>
            ';
            echo '
            <tr>
                <td class="fieldlabel" width="30%" style="float:right;">'.$LANG['parameter'].'</td>
                <td>' . $data['variables'] . '</td>
            </tr>
            ';

            if(!empty($data['extra'])){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['ekstra'].'</td>
                    <td class="fieldarea">
                        <input type="text" name="'.$data['id'].'_extra" value="'.$data['extra'].'">
                    </td>
                </tr>
                ';
            }
            if($_GET['type'] == "admin"){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['admingsm'].'</td>
                    <td class="fieldarea">
                        <input type="text" name="'.$data['id'].'_admingsm" value="'.$data['admingsm'].'">
                        '.$LANG['admingsmornek'].'
                    </td>
                </tr>
                ';
            }
            echo '<tr>
                <td colspan="2"><hr></td>
            </tr>';
        }
        echo '
        </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" name="submit" value="'.$LANG['save'].'" class="button" /></p>
        </form>';

    }
    elseif ($tab == "messages")
    {
        if(!empty($_GET['deletesms'])){
            $smsid = (int) $_GET['deletesms'];
            $sql = "DELETE FROM ishaarat_wa_log  WHERE id = '$smsid'";
            mysql_query($sql);
        }
        echo  '
        <!--<script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" type="text/css">
        <link rel="stylesheet" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables_themeroller.css" type="text/css">
        <script type="text/javascript">
            $(document).ready(function(){
                $(".datatable").dataTable();
            });
        </script>-->

        <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
        <table class="datatable" border="0" cellspacing="1" cellpadding="3">
        <thead>
            <tr>
                <th>#</th>
                <th>'.$LANG['client'].'</th>
                <th>'.$LANG['message'].'</th>
                <th>'.$LANG['datetime'].'</th>
                <th>'.$LANG['status'].'</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        ';

        // Getting pagination values.
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = (isset($_GET['limit']) && $_GET['limit']<=50) ? (int)$_GET['limit'] : 10;
        $start  = ($page > 1) ? ($page*$limit)-$limit : 0;
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        /* Getting messages order by date desc */
        $sql = "SELECT `m`.*,`user`.`firstname`,`user`.`lastname`
        FROM `ishaarat_wa_log` as `m`
        JOIN `tblclients` as `user` ON `m`.`user` = `user`.`id`
        ORDER BY `m`.`datetime` {$order} limit {$start},{$limit}";
        $result = mysql_query($sql);
        $i = 0;

        //Getting total records
        $total = "SELECT count(id) as toplam FROM `ishaarat_wa_log`";
        $sonuc = mysql_query($total);
        $sonuc = mysql_fetch_array($sonuc);
        $toplam = $sonuc['toplam'];

        //Page calculation
        $sayfa = ceil($toplam/$limit);

        while ($data = mysql_fetch_array($result)) {
            $status = $data['status'];
            $i++;
            echo  '<tr>
            <td>'.$data['id'].'</td>
            <td><a href="clientssummary.php?userid='.$data['user'].'">'.$data['firstname'].' '.$data['lastname'].'</a></td>
            <td>'.$data['to'].'</td>
            <td>'.$data['text'].'</td>
            <td>'.$data['datetime'].'</td>
            <td>'.$LANG[$status].'</td>
            <td><a href="addonmodules.php?module=ishaarat_wa&tab=messages&deletesms='.$data['id'].'" title="'.$LANG['delete'].'"><img src="images/delete.gif" width="16" height="16" border="0" alt="Delete"></a></td></tr>';
        }
        /* Getting messages order by date desc */

        echo '
        </tbody>
        </table>

        ';  
        $list="";
        for($a=1;$a<=$sayfa;$a++)
        {
            $selected = ($page==$a) ? 'selected="selected"' : '';
            $list.="<option value='addonmodules.php?module=ishaarat_wa&tab=messages&page={$a}&limit={$limit}&order={$order}' {$selected}>{$a}</option>";
        }
        echo "<select  onchange=\"this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);\">{$list}</select></div>";

    }
    elseif($tab=="sendbulk")
    {
        $settings = $class->getSettings();

        if(!empty($_POST['client'])){
            $userinf = explode("_",$_POST['client']);
            $userid = $userinf[0];
            $gsmnumber = $userinf[1];

            $class->setGsmnumber($gsmnumber);
            $class->setMessage($_POST['message']);
            $class->setUserid($userid);

            $result = $class->send();
            if($result == false){
                echo $class->getErrors();
            }else{
                echo $LANG['smssent'].' '.$gsmnumber;
            }

            if($_POST["debug"] == "ON"){
                $debug = 1;
            }
        }

        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`phonenumber` as `gsmnumber`
        FROM `tblclients` as `a`
            JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
        WHERE `b`.`fieldid` = '".$settings['gsmnumberfield']."'
        AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
        AND `c`.`value` = 'on' order by `a`.`firstname`";
        $clients = '';
        $result = mysql_query($userSql);
        while ($data = mysql_fetch_array($result)) {
            $clients .= '<option value="'.$data['id'].'_'.$data['gsmnumber'].'">'.$data['firstname'].' '.$data['lastname'].' (#'.$data['id'].')</option>';
        }
        echo '
        <script>
        jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
          return this.each(function() {
            var select = this;
            var options = [];
            $(select).find("option").each(function() {
              options.push({value: $(this).val(), text: $(this).text()});
            });
            $(select).data("options", options);
            $(textbox).bind("change keyup", function() {
              var options = $(select).empty().scrollTop(0).data("options");
              var search = $.trim($(this).val());
              var regex = new RegExp(search,"gi");

              $.each(options, function(i) {
                var option = options[i];
                if(option.text.match(regex) !== null) {
                  $(select).append(
                     $("<option>").text(option.text).val(option.value)
                  );
                }
              });
              if (selectSingleMatch === true && 
                  $(select).children().length === 1) {
                $(select).children().get(0).selected = true;
              }
            });
          });
        };
        $(function() {
          $("#clientdrop").filterByText($("#textbox"), true);
        });  
        </script>';
        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 10px;">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['client'].'</td>
                            <td class="fieldarea">
                                <input id="textbox" type="text" placeholder="Filter" style="width:498px;padding:5px"><br>
                                <select name="client" multiple id="clientdrop" style="width:512px;padding:5px">
                                    <option value="">'.$LANG['selectclient'].'</option>
                                    ' . $clients . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['mesaj'].'</td>
                            <td class="fieldarea">
                               <textarea cols="70" rows="20" name="message" style="width:498px;padding:5px"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p align="center"><input type="submit" value="'.$LANG['send'].'" class="button" /></p>
        </form>';

        if(isset($debug)){
            echo $class->getLogs();
        }
    }
	echo $LANG['lisans'];
}
