<?php

class Log_
{
	// 打印log
	function  log_result($file,$word) 
	{
	    $fp = fopen($file,"a");
	    flock($fp, LOCK_EX) ;
	    fwrite($fp,"执行日期：".strftime("%Y-%m-%d %X",time())."\r\n".$word."\r\n\r\n");
	    flock($fp, LOCK_UN);
	    fclose($fp);
	}
	
	// 打印log
	function  log($word)
	{
		$file=dirname(__FILE__)."/logs/".strftime("%Y-%m-%d",time())."_log.log";
		$fp = fopen($file,"a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y-%m-%d %X",time())."\r\n".$word."\r\n\r\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

?>