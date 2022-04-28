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
    $getMaxUnitQuery = 'select * from `product_units` inner join units on(`units`.`unit_id` = `product_units`.`unit_id`) where `product_units`.`product_id` = "'.$_POST['productId'].'" and `product_units`.`unit_id` = "'.$_POST['unitId'].'"';
    $getMaxUnitResult = mysql_query($getMaxUnitQuery)or die("error getMaxUnitQuery not done ".mysql_error());
    $convertor = mysql_result($getMaxUnitResult,"0","convertor");
    $unit_name = mysql_result($getMaxUnitResult,"0","unit_name");
    if(isset($_POST['from_date']) && $_POST['from_date'] != ''){
        $getStartQuery = '
        select 
            sum((case 
                when(`permits`.`permit_type_id` in(1,4)) then((`permit_products`.`quantity` * `product_units`.`convertor`))
                when(`permits`.`permit_type_id` in(2,3)) then(((`permit_products`.`quantity` * `product_units`.`convertor`) *  (-1)))
                else(0)
            end)) as "credit"
        from 
            `permits`
            inner join `permit_products` on(`permit_products`.`permit_id` = `permits`.`permit_id`)
            inner join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
            inner join `permit_types` on(`permit_types`.`permit_type_id` = `permits`.`permit_type_id`)
            inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)
            inner join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
            inner join `product_units` on(`product_units`.`product_id` = `products`.`product_id` and `product_units`.`unit_id` = `units`.`unit_id`)
        where 
            `permits`.`permit_id` > 0 
            and 
            `permit_products`.`product_id` = "'.$_POST['productId'].'"
            and 
            `permits`.`com_id` = "'.$_SESSION['company_id'].'"
            and 
            `permits`.`permit_date` <= "'.$_POST['from_date'].'"';
            file_put_contents('f',$getStartQuery);
        $getStartResult = mysql_query($getStartQuery)or die("error getStartQuery not done ".mysql_error());
        $startCredit = mysql_result($getStartResult,"0","credit");
        query_result('set @balance = '.($startCredit+0));    
    }
    $report->query = '';
    if(isset($_POST['from_date']) && $_POST['from_date'] != ''){
        $report->query = '
        select 
            "" as "permit_number",
            "" as "permit_date",
            "" as "unit_name",
            "" as "expiry",
            "" as "quantity_in",
            "" as "quantity_out",
            "" as "price_in",
            "" as "price_out",
            "'.$lang['start_credit'].'" as "notes",
            "" as "credit2",
            round((@balance / '.$convertor.'),2)as "credit"
        union all ';
    }
    $report->query.= '
    select 
        `p`.`permit_number`,
        `p`.`permit_date`,
        `p`.`unit_name`,
        `p`.`expiry`,
        `p`.`quantity_in`,
        `p`.`quantity_out`,
        `p`.`price_in`,
        `p`.`price_out`,
        `p`.`notes`,
        @balance:=(@balance + (`p`.`quantity_in` * `p`.`convertor`) - (`p`.`quantity_out` * `p`.`convertor`))as "credit2",
        round((@balance / '.$convertor.'),2)as "credit"
    from 
    (
        select 
            `permits`.`permit_number`,
            `permits`.`permit_date`,
            `units`.`unit_name`,
            `permit_products`.`expiry`,
            if(`permits`.`permit_type_id` in(1,4),`permit_products`.`quantity`,0)as "quantity_in",
            if(`permits`.`permit_type_id` in(2,3),`permit_products`.`quantity`,0)as "quantity_out",
            if(`permits`.`permit_type_id` in(1,4),`permit_products`.`price`,0)as "price_in",
            if(`permits`.`permit_type_id` in(2,3),`permit_products`.`price`,0)as "price_out",
            `product_units`.`convertor` as "convertor",
            concat(convert(`permit_types`.`permit_type_name` using UTF8)," ",convert(`companies`.`company_name` using UTF8))as "notes"
        from 
            `permits`
            inner join `permit_products` on(`permit_products`.`permit_id` = `permits`.`permit_id`)
            inner join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
            inner join `permit_types` on(`permit_types`.`permit_type_id` = `permits`.`permit_type_id`)
            inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)
            inner join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
            inner join `product_units` on(`product_units`.`product_id` = `products`.`product_id` and `product_units`.`unit_id` = `units`.`unit_id`)
        where 
            `permits`.`permit_id` > 0 
            and 
            `permit_products`.`product_id` = "'.$_POST['productId'].'"
            and 
            `permits`.`com_id` = "'.$_SESSION['company_id'].'"';
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
        order by 
            `permits`.`permit_date`,if(permits.permit_type_id in(1,4),1,2),`permits`.`permit_number`
    ) as `p`';
    file_put_contents('f',$report->query);
    $report->template = 'templates/template.htm';
    $getProductInfoQuery = 'select * from `products` where `product_id` = "'.$_POST['productId'].'"';
    $getProductInfoResult = mysql_query($getProductInfoQuery)or die("error getProductInfoQuery not done ".mysql_error());
    $productInfo = mysql_fetch_array($getProductInfoResult);
    $head = $lang['product_card_report_head'].' '.$productInfo['product_name'];
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
    array_push($columns,array('name'=>$lang['order_number'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_number'));
    array_push($columns,array('name'=>$lang['the_date'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_date'));
    array_push($columns,array('name'=>$lang['the_unit'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'unit_name'));
    array_push($columns,array('name'=>$lang['the_expiry'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'expiry'));
    array_push($columns,array('name'=>$lang['quantity_in'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'quantity_in'));
    array_push($columns,array('name'=>$lang['price_in'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'price_in'));
    array_push($columns,array('name'=>$lang['quantity_out'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'quantity_out'));
    array_push($columns,array('name'=>$lang['price_out'],'width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'price_out'));    
    array_push($columns,array('name'=>$lang['notes'],'width'=>'10','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'notes'));
    array_push($columns,array('name'=>$lang['the_balance'].'</br>('.$unit_name.')','width'=>'4','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'credit'));    
    $report->columns = $columns;
    $pages = $report->createReport();
    echo '<style>';
    echo $report->style;
    echo '</style>';
    echo $pages;
    echo '
        </body>
    </html>';    