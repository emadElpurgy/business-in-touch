<?php
##����
session_start();
$db_hostname='localhost';
$db_database='market';
$db_username='root';
$db_password='';
$db_server=mysql_connect($db_hostname,$db_username,$db_password);
mysql_query("SET NAMES UTF8");
mysql_query("set characer set UTF8",$db_server);
if(!$db_server)die($error[10001].mysql_error());
mysql_select_db($db_database)or die ($error[10002].mysql_error());






$connectionDetails = array(
    "db" => "mysql:host=localhost;dbname=market",
    "username" => "root",
    "password" => "",
    "charset" => "UTF8"
);
$db = new PDO($connectionDetails["db"], $connectionDetails["username"], $connectionDetails["password"],array('charset'=>'utf8'));
$db->exec("set names utf8");
$lastId = false;
function query_result($query){
	global $db;
	$q = $db->prepare($query);
	$q->execute()or logerror($query,$db->errorInfo());
	if(strpos($query,'insert into')!== false){
		return $db->lastInsertId();
	}else{
    	$rows = $q->fetchAll();
		return $rows;
	}
}

function logerror($query,$error){
$err = serialize($error);
file_put_contents("error.log",$query.PHP_EOL.$err);
exit();
}
$companyProperties = array();
if($_SESSION['company_id'] > 0){
	$companyInfoQuery = 'select * from company_information where com_id = '.$_SESSION['company_id'];
	$companyInfoResult = query_result($companyInfoQuery);	
	foreach($companyInfoResult as $info){
		$companyProperties[$info['key']] = $info['value'];
	}
}

?>