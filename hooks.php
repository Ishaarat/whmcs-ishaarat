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

require_once("class-ishaarat-wa.php");
$class = new IshaaratWa();
$hooks = $class->getHooks();

foreach($hooks as $hook){
    add_hook($hook['hook'], 1, $hook['function'], "");
}