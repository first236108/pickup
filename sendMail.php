<?php
header('content-type:text/html;charset=utf-8');
error_reporting(7);
ini_set('memory_limit', '-1'); 
set_time_limit(0);
$starton = microtime(true);
date_default_timezone_set('PRC');
require_once('./includes/db.class.php');
require_once('./includes/function.php');
require_once("./PhpExcel/PHPExcel.php");
require_once("./PhpExcel/PHPExcel/Writer/Excel2007.php");
include_once('./PhpMailer/phpmailer.php');
$dbconfig = array(
                'dsn'         =>    'mysql:host=localhost;dbname=pickup',
                'name'        =>    'pickup',
                'password'    =>    'pickup',
            ); 
$_DB =new DB($dbconfig);

function export($date, $user_id, $dataset){
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('�����嵥');
		$cellname  = array(
			array('repaydate','������'),
			array('name','������Ŀ'),
			array('money','���'),	
			array('times','ʱ��'),
		);
		$r = exportExcel($date.'�˵�', $cellname, $dataset[0], $objPHPExcel);
		$objWriter = new PHPExcel_Writer_Excel2007($r);
		$dirname   = './Uploads';
		if( !is_dir($dirname) )	mk_dir('./Uploads');
		$file_name = $dirname.'/'.$date.'-'.$user_id.'.xlsx';
		$objWriter->save($file_name);
		
		return $file_name;
}
function sendmail($email, $coname, $subject, $body, $file){
		$mail = new PHPMailer();
		$mail->CharSet = 'utf-8';
		$mail->IsSMTP(); // set mailer to use SMTP
		$mail->IsHTML(true);
		$mail->Host = "smtp.exmail.qq.com"; // specify main and backup server
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = "server@website.com"; // SMTP username
		$mail->Password = "mypassword"; // SMTP password
		$mail->From = "server@website.com";
		$mail->FromName = "�ʼ�ϵͳ֪ͨ";
		$mail->AddAddress($email, $coname);
		$mail->Subject =$subject;
		$mail->Body = $body;
		$mail->AddAttachment($file);		
		return $mail->Send();		 
}

try{	
	$date	=	date('Y��m��d��');
	$sql	=	"SELECT user_id, username, email from members";
	$user	=	$_DB->getAll($sql);	
	if( empty($user) ){ throw new Exception("�û���¼Ϊ��."); }
	
	foreach($user as $key=>$value){		
		$sql		= "SELECT * from bill where user_id='{$value['user_id']}' and date='{$date}'";
		$dataset	= $_DB->getAll($sql);		
		$filename	= export($date, $value['user_id'], $dataset);
		$body = "<br /><b>���û������嵥��</b><p>�𾴵�{$username}��<br />�����˵��ڸ������ʹ";
		
		sendmail($value['email'], $value['username'], $date.'�����嵥', $body, $filename);		
		write_log("�����ʼ�����".$value['email'].", ��".$value['username'].".");
	}
}catch(Exception $e){
    echo "Failed: " . $e->getMessage();
}

$time = round(microtime(true) - (float)$starton, 5);
echo '�˷Ѽ���ʱ�乲��',$time,'    �˷��ڴ湲�ƣ�', (memory_get_usage(true) / 1024), "kb\n\nDone.\n";
