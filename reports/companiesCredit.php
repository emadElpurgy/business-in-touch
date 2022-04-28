<?
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: no-store, must-revalidate");	
header("Pragma: no-cache");	
header("Expires: 0");	
session_start();
include("../db.php");
include("../lib/reporter.php");
include("../lang/lang_ar.php");
echo '<!DOCTYPE html>
<html dir="rtl">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../css/reporter.css" type="text/css"/>
		<script src="../js/reporter.js"></script>
	<style>
	body
	{
	  margin:0px;
	  padding:0px;
	}
	<body style="height:100vh;">';
$report = new reporter();
$ids = array();
function getCategoryIds($id){
    global $ids;
    array_push($ids,$id);
    $checkSubCategoriesQuery = 'select * from `categories` where `parent_category_id` = '.$id;
    $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
    while($cat = mysql_fetch_array($checkSubCategoriesResult)){
        getCategoryIds($cat['category_id']);
    }
}
$report->query = '
select 
    `companies`.`company_name`,
    `companies`.`balance`
from 
    `companies` 
where 
    `company_id` > 0 
    and 
    `type` = '.$_POST['type'];
    if($_POST['categoryId'] > 0){
        getCategoryIds($_POST['categoryId']);
        $report->query.='
        and 
        `category_id` in ('.implode(',',$ids).')';
    }
    if($_SESSION['company_id']){
        $report->query.='
        and 
        `com_id` = '.$_SESSION['company_id'];
    }
    $report->query.='
    union all 
    select 
        "'.$lang['total'].'",
        sum(`companies`.`balance`) as "balance"
    from 
        `companies`
    where 
        `company_id` > 0 
        and 
        `type` = '.$_POST['type'];
        if($_POST['categoryId'] > 0){
            $report->query.='
            and 
            `category_id` in ('.implode(',',$ids).')';
        }
        if($_SESSION['company_id']){
            $report->query.='
            and 
            `com_id` = '.$_SESSION['company_id'];
        }
$report->template = 'templates/template.htm';
if($_GET['type'] == "1"){
    $head = $lang['customers_credit_report_head'];
}else{
    $head = $lang['suppliers_credit_report_head'];
}
if($_POST['categoryId'] > 0){
    $getCategoryNameQuery = 'select * from `categories` where `category_id` = '.$_POST['categoryId'];
    $getCategoryNameResult = mysql_query($getCategoryNameQuery)or die("error getCategoryNameQuery not done ".mysql_error());
    $head.=' '.$lang['for_category'].' '.mysql_result($getCategoryNameResult,"0","category_name");
}
$companyProperties['report_name'] = $head;
$report->templateData = $companyProperties;
$report->font = 'arial';
$report->fontSize = '14px';
$report->fontWeight = '';
$report->fontStyle = '';
$report->headFont = 'arial';
$report->headFontWeight = 'bold';
$report->headFontStyle = 'italic';
$report->headFontSize = '16px';
$report->headGgColor = '#C0C0C0';
$columns = array();
if($_GET['type'] == "1"){
    $columnName = $lang['the_customer'];
}else{
    $columnName = $lang['the_supplier'];
}
array_push($columns,array('name'=>$columnName,'width'=>'5','dataType'=>'text','color'=>'','align'=>'right','background'=>'','content'=>'company_name'));
array_push($columns,array('name'=>$lang['the_balance'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'balance'));
$report->columns = $columns;

$pages = $report->createReport();
echo '<style>';
echo $report->style;
echo '</style>';
echo $pages;
echo '
	</body>
</html>';
?>