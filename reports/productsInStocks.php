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
    `products`.`product_name`,
    `units`.`unit_name`,
    `units_in_stocks`.`expiry`,
    sum((`units_in_stocks`.`amount` / ifnull(`product_units`.`convertor`,1)))as "quantity",
    `stocks`.`stock_name`
from 
    `units_in_stocks` 
    inner join `products` on(`products`.`product_id` = `units_in_stocks`.`product_id`)    
    inner join `stocks` on (`stocks`.`stock_id` = `units_in_stocks`.`stock_id`)
    left join `product_units` on(`product_units`.`product_id` = `products`.`product_id` and `product_units`.`default` in(2,3) )
    inner join `units` on (`units`.`unit_id` = ifnull(`product_units`.`unit_id`,`units_in_stocks`.`unit_id`))
where 
    `units_in_stocks`.`stock_unit_id` > 0 
    and 
    `units_in_stocks`.`product_id` > 0 
    and 
    `units_in_stocks`.`amount` > 0';
    if($_POST['categoryId'] > 0){
        getCategoryIds($_POST['categoryId']);
        $report->query.='
        and 
        `products`.`category_id` in ('.implode(',',$ids).')';
    }
    $report->query.='
group by 
    `products`.`product_id`,`units_in_stocks`.`expiry`,`stocks`.`stock_id`
order by 
    `products`.`product_name`,`units_in_stocks`.`expiry`,`stocks`.`stock_name`';

$report->template = 'templates/template.htm';

$head = $lang['products_in_stocks_report_head'];
if($_POST['categoryId'] > 0){
    $getCategoryNameQuery = 'select * from `categories` where `category_id` = '.$_POST['categoryId'];
    $getCategoryNameResult = mysql_query($getCategoryNameQuery)or die("error getCategoryNameQuery not done ".mysql_error());
    $head.=' '.$lang['for_category'].' '.mysql_result($getCategoryNameResult,"0","category_name");
}

if($_POST['stockId'] > 0){
    $getStockNameQuery = 'select * from `stocks` where `stock_id` = '.$_POST['stockId'];
    $getStockNameResult = mysql_query($getStockNameQuery)or die("error getStockNameQuery not done ".mysql_error());
    $head.=' '.$lang['in_stock'].' '.mysql_result($getStockNameResult,"0","stock_name");
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
array_push($columns,array('name'=>$lang['product_name'],'width'=>'8','dataType'=>'text','color'=>'','align'=>'right','background'=>'','content'=>'product_name'));
array_push($columns,array('name'=>$lang['the_quantity'],'width'=>'3','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'quantity'));
array_push($columns,array('name'=>$lang['the_unit'],'width'=>'2','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'unit_name'));
array_push($columns,array('name'=>$lang['the_expiry'],'width'=>'3','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'expiry'));
if(!isset($_POST['stockId']) ||  $_POST['stockId'] == 0){
    array_push($columns,array('name'=>$lang['the_stock'],'width'=>'3','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'stock_name'));
}

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