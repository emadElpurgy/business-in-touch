<?php
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
    
query_result('set @balance = 0');

if(isset($_POST['from_date']) && $_POST['from_date'] != ''){
    $getStartCreditQuery = '        
    select
        sum(
            (case 
                when((`companies`.`type` = 2 and `permits`.`permit_type_id` in(1,5)) or(`companies`.`type` = 1 and `permits`.`permit_type_id` in(3,6)) )then(`permits`.`overall`) 
                else((`permits`.`overall` * (-1)))
            end)
        )as "credit"
    from 
        `permits` 
        inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)
        inner join `categories` on(`categories`.`category_id` = `permits`.`category_id`)
        inner join `permit_types` on(`permit_types`.`permit_type_id` = `permits`.`permit_type_id`)
    where 
        `permits`.`permit_id` > 0 
        and 
        `permits`.`com_id` = "'.$_SESSION['company_id'].'"
        and 
        `permits`.`company_id` = "'.$_POST['company_id'].'"
        and 
        `permits`.`permit_date` < "'.$_POST['from_date'].'"';
    $getStartCreditResult = mysql_query($getStartCreditQuery)or die("error getStartCreditQuery not done ".mysql_error());
    $startCredit = mysql_result($getStartCreditResult,"0","credit");        
    query_result('set @balance = '.($startCredit+0));
}
$report->query ='';
if(isset($_POST['from_date']) && $_POST['from_date'] != ''){
    $report->query ='
    select 
        "" as "permit_number",
        "" as "permit_date",
        "'.$lang['start_credit'].'" as "product_name",
        "" as "unit_name",
        "" as "quantity",
        "" as "price",
        "" as "total",
        "'.($startCredit+0).'" as "credit"
    union all ';
}
$report->query.='
select 
    `p`.`permit_number`,
    `p`.`permit_date`,
    `p`.`product_name`,
    `p`.`unit_name`,
    `p`.`quantity`,
    `p`.`price`,
    `p`.`total`,
    @balance:=(@balance + p.credit)as "credit"
from
(
    select
        `permits`.`permit_number`,
        `permits`.`permit_date`,        
        `permits`.`notes`,
        (case 
            when(`products`.`product_id` = 0 and `permit_products`.`total` > 0)then(convert("'.$lang['to_be_added'].'" using UTF8))
            when(`products`.`product_id` = 0 and `permit_products`.`total` < 0)then(convert("'.$lang['discount'].'" using UTF8))
            when(`permit_products`.`permit_product_id` > 0 )then(
                concat((case when(`permit_types`.`permit_type_id` in(2,4)) then(convert("'.$lang['return'].' " using UTF8)) else("") end),`products`.`product_name`)
            ) 
            else(convert(`categories`.`category_name` using UTF8)) 
        end) as "product_name",
        if(`products`.`product_id` > 0,`units`.`unit_name`,"----") as "unit_name",
        if(`products`.`product_id` > 0,`permit_products`.`price`,"----") as "price",
        if(`products`.`product_id` > 0,`permit_products`.`quantity`,"----") as "quantity",
        (case when(`permit_products`.`permit_product_id` > 0)then(`permit_products`.`total`) else(`permits`.`overall`)end) as "total",
        sum(
            (case
                when(`permit_products`.`permit_product_id` > 0 and `permits`.`permit_type_id` in (1,3))then(`permit_products`.`total`) 
                when(`permit_products`.`permit_product_id` > 0 and permits.`permit_type_id` in (2,4))then((`permit_products`.`total` * (-1))) 
                when((`permits`.`permit_type_id` = 5 and `companies`.`type` = 2) or (`permits`.`permit_type_id` = 6 and `companies`.`type` = 1) ) then(`permits`.`overall`)
                when((`permits`.`permit_type_id` = 6 and `companies`.`type` = 2) or (`permits`.`permit_type_id` = 5 and `companies`.`type` = 1) ) then((`permits`.`overall` * (-1)))
            end)
        )as "credit"
    from 
        `permits` 
        inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)
        inner join `categories` on(`categories`.`category_id` = `permits`.`category_id`)
        inner join `permit_types` on(`permit_types`.`permit_type_id` = `permits`.`permit_type_id`)
        left join `permit_products` on(`permit_products`.`permit_id` = `permits`.`permit_id`)
        left join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
        left join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
    where 
        `permits`.`permit_id` > 0 
        and 
        `permits`.`com_id` = '.$_SESSION['company_id'].' 
        and 
        `permits`.`company_id` = "'.$_POST['company_id'].'" ';
        if(isset($_POST['from_date']) && $_POST['from_date'] != ''){
            $report->query.='
            and 
            `permits`.`permit_date` >= "'.$_POST['from_date'].'"';    
        }
        if(isset($_POST['to_date']) && $_POST['to_date'] != ''){
            $report->query.='
            and 
            `permits`.`permit_date` <= "'.$_POST['to_date'].'"';    
        }
        $report->query.='        
    group by 
        concat(`permits`.`permit_id`,"-",ifnull(`permit_products`.`permit_product_id`,0))
    order by 
        `permits`.`permit_date`,
        round(
            (case 
                when(`companies`.`type` = 2) then(
                    (case 
                        when(`permits`.`permit_type_id` = 1) then(1)
                        when(`permits`.`permit_type_id` = 2) then(2)
                        when(`permits`.`permit_type_id` = 6) then(3)
                        when(`permits`.`permit_type_id` = 5) then(4)
                    end)
                )
                when(`companies`.`type` = 1) then(
                    (case 
                        when(`permits`.`permit_type_id` = 3) then(1)
                        when(`permits`.`permit_type_id` = 4) then(2)
                        when(`permits`.`permit_type_id` = 5) then(3)
                        when(`permits`.`permit_type_id` = 6) then(4)
                    end)
                )
            end)
        ),
        `permit_number`,
        `permit_products`.`permit_product_id`
)as p';

$report->template = 'templates/template.htm';
$getCompanyInfoQuery = 'select * from `companies` where `company_id` = "'.$_POST['company_id'].'"';
$getCompanyInfoResult = mysql_query($getCompanyInfoQuery)or die("error getCompanyInfoQuery not done ".mysql_error());
$companyInfo = mysql_fetch_array($getCompanyInfoResult);
$head = $lang['company_balance_2_report_head_'.$companyInfo['type']].' '.$companyInfo['company_name'];
if(isset($_POST['from_date']) && $_POST['from_date'] != ""){
    $head.=' '.$lang['from_date'].' '.$_POST['from_date'];
}
if(isset($_POST['to_date']) && $_POST['to_date'] != ""){
    $head.=' '.$lang['to_date'].' '.$_POST['to_date'];
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
$report->width = 29;
$report->height = 21;
$report->headHeigh = 3;
$report->footHeigh = 3;
$report->margin['top'] = 0;
$report->margin['bottom'] = 0;
$columns = array();
array_push($columns,array('name'=>$lang['order_number'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_number'));
array_push($columns,array('name'=>$lang['the_date'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_date'));
array_push($columns,array('name'=>$lang['the_product'],'width'=>'10','dataType'=>'number','color'=>'','align'=>'right','background'=>'','content'=>'product_name'));
array_push($columns,array('name'=>$lang['the_unit'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'unit_name'));
array_push($columns,array('name'=>$lang['the_quantity'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'quantity'));
array_push($columns,array('name'=>$lang['the_price'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'price'));
array_push($columns,array('name'=>$lang['total'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'total'));
array_push($columns,array('name'=>$lang['the_balance'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'credit'));
$report->columns = $columns;
$pages = $report->createReport();
echo '<style>';
echo $report->style;
echo '</style>';
echo $pages;
echo '
	</body>
</html>';