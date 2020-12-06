<?php
session_start();
function is_admin_ajax() { if(isset($_SESSION["admin"]) and $_SESSION["admin"]==true)return true; else exit_with_response("error","authentication failed");}
require_once(dirname(__FILE__)."../../../config.php");
function exit_with_response($type,$msg,$meta=null){exit(json_encode(array('type'=>$type,'msg'=>$msg,'meta'=>$meta)));}
function GVPV($key) { if(isset($_POST[$key])) { return $_POST[$key]; } else { exit_with_response("error",$key." not present in request, make sure you are sending ".$key." in POST method");}}
?>