<?php
/**
 * 财付通接口类
 *
 * 
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
// defined ( 'InShopNC' ) or exit ( 'Access Invalid!' );
class wxpay {
	/**
	 * 支付接口网关
	 *
	 * @var string
	 */
	private $gateway = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi';
	/**
	 * 支付接口标识
	 *
	 * @var string
	 */
	private $code = 'wxpay3';
	/**
	 * 支付接口配置信息
	 *
	 * @var array
	 */
	private $payment;
	/**
	 * 订单信息
	 *
	 * @var array
	 */
	private $order;
	/**
	 * 发送至财付通的参数
	 *
	 * @var array
	 */
	private $parameter;
	/**
	 * 订单类型 product_buy商品购买,predeposit预存款充值
	 *
	 * @var unknown
	 */
	private $order_type;
	/**
	 * 支付结果
	 *
	 * @var unknown
	 */
	private $pay_result;
	public function __construct($payment_info, $order_info) {
		$this->wxpay ( $payment_info, $order_info );
	}
	public function wxpay($payment_info = array(), $order_info = array()) {
		if (! empty ( $payment_info ) and ! empty ( $order_info )) {
			$this->payment = $payment_info;
			$this->order = $order_info;
		}
	}
	/**
	 * 获取支付表单
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function get_payurl() {
		
		/**
		 * Native（原生）支付-模式二-demo
		 * ====================================================
		 * 商户生成订单，先调用统一支付接口获取到code_url，
		 * 此URL直接生成二维码，用户扫码后调起支付。
		 */
		include_once(dirname(__FILE__) . "/WxPayPubHelper/WxPayPubHelper.php");
		include_once(dirname(__FILE__) . "/WxPayPubHelper/log_.php");
		
		$log_ = new Log_ ();
		$log_name = dirname ( __FILE__ ) . "/logs/get_payurl.log"; // log文件路径
		$log_->log_result ( $log_name, "=========begin===========" );
		
		// 使用统一支付接口
		$unifiedOrder = new UnifiedOrder_pub ();
		
		// 设置统一支付接口参数
		// 设置必填参数
		// appid已填,商户无需重复填写
		// mch_id已填,商户无需重复填写
		// noncestr已填,商户无需重复填写
		// spbill_create_ip已填,商户无需重复填写
		// sign已填,商户无需重复填写
		
		// var_dump ( $this );
		// print_r($this->order);
		
		$timeStamp = time ();
		// $out_trade_no = WxPayConf_pub::APPID . "$timeStamp";
		$out_trade_no = $this->order ['pay_sn'];
		$condition = array ();
		$condition ['pay_sn'] = $out_trade_no;
		
		// print_r($orderInfo);
		$unifiedOrder->setParameter ( "out_trade_no", $out_trade_no ); // 商户订单号
		$unifiedOrder->setParameter ( "total_fee", floatval ( $this->order ['pay_amount'] ) * 100 ); // 总金额
		                                                                                             // $return_url = SHOP_SITE_URL . "/api/payment/wxpay3/return_url.php";
		$unifiedOrder->setParameter ( "notify_url", WxPayConf_pub::NOTIFY_URL ); // 通知地址
		$unifiedOrder->setParameter ( "trade_type", "NATIVE" ); // 交易类型
		
		$unifiedOrder->setParameter ( "attach", $this->order ['order_type'] ); // 附加数据
		$unifiedOrder->setParameter ( "body", $this->order ['subject'] ); // 商品描述
		$unifiedOrder->setParameter ( "time_start", date ( "YmdHis" ) ); // 交易起始时间
		$unifiedOrder->setParameter ( "time_expire", date ( "YmdHis", time () + 600 ) ); // 交易结束时间
		
		$unifiedOrder->setParameter ( "product_id", $this->order ['pay_sn'] ); // 商品ID
		                                                                       
		// 非必填参数，商户可根据实际情况选填
		                                                                       
		// $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
		                                                                       
		// $unifiedOrder->setParameter("device_info","XXXX");//设备号
		                                                                       
		// $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
		                                                                       
		// $unifiedOrder->setParameter("openid","XXXX");//用户标识
		                                                                       
		// 获取统一支付接口结果
		$unifiedOrderResult = $unifiedOrder->getResult ();
		// print_r($unifiedOrder);
		
		$log_->log_result ( $log_name, print_r ( $this, true ) );
		$log_->log_result ( $log_name, "url:" . $unifiedOrderResult ["code_url"] );
		$log_->log_result ( $log_name, "=========end===========" );
		
		$payinfo = "";
		// 商户根据实际情况设置相应的处理流程
		if ($unifiedOrderResult ["return_code"] == "FAIL") {
			// 商户自行增加处理流程
			$payinfo = "通信出错：" . $unifiedOrderResult ['return_msg'] . "<br>";
		} elseif ($unifiedOrderResult ["result_code"] == "FAIL") {
			// 商户自行增加处理流程
			$payinfo = "错误代码：" . $unifiedOrderResult ['err_code'] . "<br>";
			$payinfo .= "错误代码描述：" . $unifiedOrderResult ['err_code_des'] . "<br>";
		} elseif ($unifiedOrderResult ["code_url"] != NULL) {
			// 从统一支付接口获取到code_url
			$code_url = $unifiedOrderResult ["code_url"];
			$payinfo = "";
			// 商户自行增加处理流程
			// ......
		}
		
		$order_info = Model ( 'order' )->getOrderInfo ( $condition, array (
				'order_goods',
				'order_common',
				'store' 
		) );
		// print_r($order_info);
		 //payment_code
		Tpl::output ( 'payment_code', $_REQUEST['payment_code'] );
		Tpl::output ( 'daddress_info', $daddress_info );
		Tpl::output ( 'order_info', $order_info );
		Tpl::output ( 'pay_amount', $this->order ['pay_amount'] );
		Tpl::output ( 'out_trade_no', $out_trade_no );
		Tpl::output ( 'code_url', $code_url );
		Tpl::output ( 'payinfo', $payinfo );
		Tpl::showpage ( 'payment_wxpay' );
	}
	
	/**
	 * 返回地址验证
	 *
	 * @param        	
	 *
	 * @return array
	 */
	public function return_verify() {
		require_once ("./classes/PayResponseHandler.class.php");
		
		/* 密钥 */
		$key = $this->payment ['payment_config'] ['tenpay_key'];
		
		/* 创建支付应答对象 */
		$resHandler = new PayResponseHandler ();
		$resHandler->setKey ( $key );
		
		// 判断签名
		if ($resHandler->isTenpaySign ()) {
			
			// 交易单号
			$transaction_id = $resHandler->getParameter ( "transaction_id" );
			
			// 金额,以分为单位
			$total_fee = $resHandler->getParameter ( "total_fee" );
			
			// 支付结果
			$pay_result = $resHandler->getParameter ( "pay_result" );
			
			$attach = $resHandler->getParameter ( 'attach' );
			$sp_billno = $resHandler->getParameter ( 'sp_billno' );
			if ("0" == $pay_result) {
				// 判断返回金额
				$order_amount = $total_fee / 100;
				if (! empty ( $this->order ['pdr_amount'] )) {
					$this->order ['pay_amount'] = $this->order ['pdr_amount'];
				}
				if ($this->order ['pay_amount'] != $order_amount) {
					return false;
				}
				$this->order_type = $attach;
				$this->pay_result = true;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 通知地址验证
	 *
	 * @return bool
	 */
	public function notify_verify() {
		include_once(dirname(__FILE__) . "/WxPayPubHelper/log_.php");
		include_once(dirname(__FILE__) . "/WxPayPubHelper/WxPayPubHelper.php");
		
		// 以log文件形式记录回调信息
		$log_ = new Log_ ();
		$log_name = dirname ( __FILE__ ) . "/logs/notify_url_" . date ( "Y-m-d" ) . ".log"; // log文件路径
		$log_->log_result ( $log_name, "开始：notify_verify" );
		
		// 使用通用通知接口
		$notify = new Notify_pub ();
		
		// 存储微信的回调
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$notify->saveData ( $xml );
		$log_->log_result ( $log_name, "do：saveData" );
		
		// 验证签名，并回应微信。
		// 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		// 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		// 尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
		}
		$returnXml = $notify->returnXml ();
		// echo $returnXml;
		
		// ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
		
		$log_->log_result ( $log_name, "【接收到的notify通知】:\n" . $xml . "\n" );
		
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result ( $log_name, "【通信出错】:\n" . $xml . "\n" );
				return false;
			} elseif ($notify->data ["result_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result ( $log_name, "【业务出错】:\n" . $xml . "\n" );
				return false;
			} else {
				// 此处应该更新一下订单状态，商户自行增删操作
				$log_->log_result ( $log_name, $notify->data ['appid'] . "【支付成功】:\n" . $xml . "\n" );
				$this->order_type = "product_buy";
				return true;
			}
			// 商户自行增加处理流程,
			// 例如：更新订单状态
			// 例如：数据库操作
			// 例如：推送支付完成信息
		}
	}
	/**
	 * 取得订单支付状态，成功或失败
	 *
	 * @param array $param        	
	 * @return array
	 */
	public function getPayResult() {
		include_once(dirname(__FILE__) . "/WxPayPubHelper/log_.php");
		include_once(dirname(__FILE__) . "/WxPayPubHelper/WxPayPubHelper.php");
		
		// 退款的订单号
		
		//$out_trade_no = $_POST ["out_trade_no"]? $_POST ["out_trade_no"]:$_GET['out_trade_no'];
		$out_trade_no = $this->order ['pay_sn'];
		// 使用订单查询接口
		$orderQuery = new OrderQuery_pub ();
		// 设置必填参数
		// appid已填,商户无需重复填写
		// mch_id已填,商户无需重复填写
		// noncestr已填,商户无需重复填写
		// sign已填,商户无需重复填写
		$orderQuery->setParameter ( "out_trade_no", "$out_trade_no" ); // 商户订单号
		                                                               // 非必填参数，商户可根据实际情况选填
		                                                               // $orderQuery->setParameter("sub_mch_id","XXXX");//子商户号
		                                                               // $orderQuery->setParameter("transaction_id","XXXX");//微信订单号
		                                                               
		// 获取订单查询结果
		$orderQueryResult = $orderQuery->getResult ();	
		
		// 商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($orderQueryResult ["return_code"] == "FAIL") {
			$res['status']=0;
			$res['info']=$orderQueryResult ['return_msg'] ;			
			//echo "通信出错：" . $orderQueryResult ['return_msg'] . "<br>";
		} elseif ($orderQueryResult ["result_code"] == "FAIL") {
			$res['status']=0;
			$res['err_code']= $orderQueryResult ['err_code'];
			$res['info']=$orderQueryResult ['return_msg'] ;			
		} else {			
			$trade_state=$orderQueryResult ['trade_state'] ;
			if($trade_state=="SUCCESS"){
				$res['status']=1;
				$res['info']=$trade_state."支付成功！";// "支付完成时间：" . $orderQueryResult ['time_end'] ;
				$res['trade_state']= "交易状态：" . $orderQueryResult ['trade_state'] ;
			}
			else{
				$res['status']=0;
				$res['info']=$trade_state;
			}
			
// 			echo "设备号：" . $orderQueryResult ['device_info'] . "<br>";
// 			echo "用户标识：" . $orderQueryResult ['openid'] . "<br>";
// 			echo "是否关注公众账号：" . $orderQueryResult ['is_subscribe'] . "<br>";
// 			echo "交易类型：" . $orderQueryResult ['trade_type'] . "<br>";
// 			echo "付款银行：" . $orderQueryResult ['bank_type'] . "<br>";
// 			echo "总金额：" . $orderQueryResult ['total_fee'] . "<br>";
// 			echo "现金券金额：" . $orderQueryResult ['coupon_fee'] . "<br>";
// 			echo "货币种类：" . $orderQueryResult ['fee_type'] . "<br>";
// 			echo "微信支付订单号：" . $orderQueryResult ['transaction_id'] . "<br>";
// 			echo "商户订单号：" . $orderQueryResult ['out_trade_no'] . "<br>";
// 			echo "商家数据包：" . $orderQueryResult ['attach'] . "<br>";
			
		}
		return $res;
		
	}
	public function __get($name) {
		return $this->$name;
	}
}
