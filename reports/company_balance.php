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
$report->query ='
select 
    @balance as "balanceBefore",
    `p`.`permit_number`,
    `p`.`permit_date`,
    concat(`p`.`permit_type_name`," ",`p`.`category_name`," ",ifnull(`p`.`notes`,"")) as "action",
    `p`.`overall`,
    @balance:=(@balance + `p`.`credit`)as "credit"
from
    (
        select
            `permits`.`permit_number`,
            `permits`.`permit_date`,
            `permit_types`.`permit_type_name`,
            `categories`.`category_name`,
            `permits`.`notes`,
            `permits`.`overall`,
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
            `permits`.`company_id` = "'.$_POST['company_id'].'"';
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
            `permits`.`permit_id`
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
                end)),
                permit_number
    )as p';
$report->template = 'templates/template.htm';
$getCompanyInfoQuery = 'select * from `companies` where `company_id` = "'.$_POST['company_id'].'"';
$getCompanyInfoResult = mysql_query($getCompanyInfoQuery)or die("error getCompanyInfoQuery not done ".mysql_error());
$companyInfo = mysql_fetch_array($getCompanyInfoResult);
$head = $lang['company_balance_report_head_'.$companyInfo['type']].' '.$companyInfo['company_name'];
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
$columns = array();
array_push($columns,array('name'=>$lang['start_balance'],'width'=>'5','dataType'=>'text','color'=>'','align'=>'center','background'=>'','content'=>'balanceBefore'));
array_push($columns,array('name'=>$lang['order_number'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_number'));
array_push($columns,array('name'=>$lang['the_date'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'permit_date'));
array_push($columns,array('name'=>$lang['the_action'],'width'=>'10','dataType'=>'number','color'=>'','align'=>'right','background'=>'','content'=>'action'));
array_push($columns,array('name'=>$lang['permit_amount'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'overall'));
array_push($columns,array('name'=>$lang['end_balance'],'width'=>'5','dataType'=>'number','color'=>'','align'=>'center','background'=>'','content'=>'credit'));
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