<?php
/**
 * 财付通返回地址
 *
 * 
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

// $_GET['act']	= 'payment';
// $_GET['op']		= 'return';
// $_GET['payment_code'] = 'wxpay3';

// //赋值，方便后面合并使用支付宝验证方法
// $_GET['out_trade_no'] = $_GET['sp_billno'];
// $_GET['extra_common_param'] = $_GET['attach'];
// $_GET['trade_no'] = $_GET['transaction_id'];


$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'wxpay3';
setPost();
require_once(dirname(__FILE__).'/../../../index.php');

function setPost(){
	include_once(dirname(__FILE__) . "/WxPayPubHelper/log_.php");
	include_once(dirname(__FILE__) . "/WxPayPubHelper/WxPayPubHelper.php");
	//以log文件形式记录回调信息
	$log_ = new Log_();
	$log_name=dirname(__FILE__)."/logs/notify_url_".date("Y-m-d").".log";//log文件路径
	
	
	//使用通用通知接口
	$notify1 = new Notify_pub();	
	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	$notify1->saveData($xml);	
	$_POST['out_trade_no']=$notify1->data["out_trade_no"];
	$_POST['extra_common_param']="product_buy";
	$_GET['out_trade_no']=$notify1->data["out_trade_no"];
	$_GET["trade_no"]=$notify1->data["transaction_id"];
	
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
	
	$log_->log_result($log_name,"out_trade_no：".$_POST["out_trade_no"]);
	$log_->log_result($log_name,"setPost 已经执行");
	$log_->log_result($log_name,"post:\n".print_r($_POST,true));
	$log_->log_result($log_name,"get:\n".print_r($_GET,true));
}
/*

$myfile = "logs/".date('Y-m-d').".log";
log_save(date("Y-m-d H:i:s"));
$txt = get_url()."\r\n";
log_save($txt);


include_once dirname(__FILE__)."/demo/notify_url.php";

function  log_save($str){
	$myfile = "logs/".date('Y-m-d').".log";
	file_put_contents($myfile,$str."\r\n", FILE_APPEND);
};


function get_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


// require_once(dirname(__FILE__).'/../../../index.php');

 */
?>