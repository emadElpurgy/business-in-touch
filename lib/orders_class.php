<?php
class orders{
    function validate($id = 0){
        global $lang;
        $errors = '';
        if(!isset($_POST['orderNumber']) || trim($_POST['orderNumber']) == ""){
            echo '
            <script>
                alert("'.$lang['error_orderNumber_is_required'].'");
                document.getElementsByName("orderNumber")[0].focus();
            </script>';
            exit();
        }
        $checkNumberExistsQuery = 'select * from `permits` where `permit_id` > 0 and `permit_number` = "'.$_POST['orderNumber'].'" and `permit_type_id` = '.$_POST['type'];
        if($id > 0){
            $checkNumberExistsQuery.='
            and 
            `permit_id` <> '.$id;
        }
        $checkNumberExistsResult = mysql_query($checkNumberExistsQuery)or die("error checkNumberExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkNumberExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_orderNumber_is_exists'].'");
                document.getElementsByName("orderNumber")[0].focus();
            </script>';
            exit();
        }
        
        if(!isset($_POST['orderDate']) || trim($_POST['orderDate']) == ""){
            echo '
            <script>
                alert("'.$lang['error_orderDate_is_required'].'");
                document.getElementsByName("orderDate")[0].focus();
            </script>';
            exit();
        }
        
        if(!isset($_POST['companyId']) || trim($_POST['companyId']) == ""  || $_POST['companyId'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_companyId_is_required_'.$_POST['type']].'");
                document.getElementsByName("companyId")[0].focus();
            </script>';
            exit();
        }
        $numOfProducts = count($_POST['productId']);
        if($numOfProducts == 0){
            echo '
            <script>
                alert("'.$lang['error_order_products_is_required'].'");
            </script>';
            exit();
        }
        for($x = 0; $x < $numOfProducts; $x++){
            if(!isset($_POST['quantity'][$x]) || trim($_POST['quantity'][$x]) == ""){
                echo '
                <script>
                    alert("'.$lang['error_quantity_is_required_for_item'].($x+1).'");
                    document.getElementsByName("quantity[]")['.$x.'].focus();
                    document.getElementsByName("quantity[]")['.$x.'].select();
                </script>';
                exit();
            }

            if(!isset($_POST['unitId'][$x]) || trim($_POST['unitId'][$x]) == "" || $_POST['unitId'][$x] == "0"){
                echo '
                <script>
                    alert("'.$lang['error_unitId_is_required_for_item'].($x+1).'");
                    document.getElementsByName("unitId[]")['.$x.'].focus();
                </script>';
                exit();
            }

            if(!isset($_POST['price'][$x]) || trim($_POST['price'][$x]) == "" || $_POST['price'][$x] == "0"){
                echo '
                <script>
                    alert("'.$lang['error_price_is_required_for_item'].($x+1).'");
                    document.getElementsByName("price[]")['.$x.'].focus();
                </script>';
                exit();
            }            
            if(!isset($_POST['itemTotal'][$x]) || trim($_POST['itemTotal'][$x]) == "" || $_POST['itemTotal'][$x] == "0"){
                echo '
                <script>
                    alert("'.$lang['error_itemTotal_is_required_for_item'].($x+1).'");
                    document.getElementsByName("itemTotal[]")['.$x.'].focus();
                </script>';
                exit();
            }            
        }

        if($errors != ''){
            return array('status'=>false,'message'=>$errors);
        }else{
            return array('status'=>true,'message'=>'');
        }
    }

    function insertOrder($type,$orderNumber,$orderDate,$companyId,$stockId,$productId,$unitId,$quantity,$expiry,$price,$itemTotal,$total,$extra,$discount,$overall,$paid = 0,$treasuryId = 0){
        global $lang;
        $insertQuery = 'insert into `permits`(`permit_type_id`,`permit_number`,`permit_date`,`company_id`,`stock_id`,`total`,`extra`,`discount`,`overall`,`com_id`)values('.$type.','.$orderNumber.',"'.$orderDate.'",'.$companyId.','.$stockId.','.($total+0).','.($extra+0).','.($discount+0).','.($overall+0).','.$_SESSION['company_id'].')';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
        $id = mysql_insert_id();        
        $insertQuery = 'insert into `permit_products`(`permit_id`,`product_id`,`unit_id`,`price`,`quantity`,`expiry`,`total`)values';
        for($x = 0; $x < count($productId); $x++){
            if($expiry[$x] == ''){
                $expiry[$x] = '0000-00-00';
            }
            if($x > 0){
                $insertQuery.=',';
            }
            $insertQuery.='('.$id.','.$productId[$x].','.$unitId[$x].','.($price[$x]+0).','.($quantity[$x]+0).',"'.$expiry[$x].'",'.($itemTotal[$x]+0).')';
        }
        if($extra > 0){
            $insertQuery.=',('.$id.',0,0,0,0,"0000-00-00",'.($extra+0).')';
        }
        if($discount > 0){
            $insertQuery.=',('.$id.',0,0,0,0,"0000-00-00",'.(($discount+0) * (-1)).')';
        }
        if($x > 0){
            $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());    
        }
        if($type == 1 || $type == 3){
            $oberator = '+';
        }else{
            $oberator = '-';
        }
        $updateCompanyQuery = 'update `companies` set `balance` = (`balance` '.$oberator.' '.$overall.') where `company_id` = '.$companyId;
        $updateCompanyResult = mysql_query($updateCompanyQuery)or die("error updateCompanyQuery not done ".mysql_error());
        if($type == 1 || $type == 4){
            $stockOberator = '+';
        }else{
            $stockOberator = '-';
        }        
        $getOrderProductsQuery = '
        select 
            `permit_products`.`product_id`,
            `products`.`product_name`,
            `permit_products`.`unit_id`,
            `permit_products`.`quantity`,
            `permit_products`.`expiry`,
            `product_units`.`convertor`,
            `product_units2`.`unit_id` as "main_unit_id"
        from 
            `permit_products`
            inner join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
            inner join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
            inner join `product_units` on(`product_units`.`product_id` = `products`.`product_id` and `product_units`.`unit_id` = `units`.`unit_id`)
            inner join `product_units` `product_units2` on(`product_units2`.`product_id` = `products`.`product_id` and `product_units2`.`parent_unit_id` = 0)
        where 
            `permit_products`.`permit_id` = '.$id.'
            and 
            `permit_products`.`product_id` > 0';        
        $getOrderProductsResult = mysql_query($getOrderProductsQuery)or die("error getOrderProductsQuery not done ".mysql_error());
        while($product = mysql_fetch_array($getOrderProductsResult)){
            $quantity = ($product['quantity'] * $product['convertor']);
            $checkQuery = 'select * from `units_in_stocks` where `product_id` = "'.$product['product_id'].'" and `unit_id` = "'.$product['main_unit_id'].'" and `expiry` = "'.$product['expiry'].'" and `stock_id` = '.$stockId;
            if($stockOberator == '-'){
                $checkQuery.=' and `amount` >= '.$quantity;
            }
            $checkResult = mysql_query($checkQuery)or die("error checkQuery not done ".mysql_error());
            if(mysql_num_rows($checkResult) > 0){
                $updateStockQuery = 'update `units_in_stocks` set `amount` = (`amount` '.$stockOberator.' '.$quantity.') where `stock_unit_id` = '.mysql_result($checkResult,"0","stock_unit_id");
                $updateStockResult = mysql_query($updateStockQuery)or die("error updateStockQuery not done ".mysql_error());
            }else{
                if($stockOberator == '+'){
                    $insertQuery = 'insert into `units_in_stocks`(`product_id`,`unit_id`,`stock_id`,`amount`,`expiry`)values('.$product['product_id'].','.$product['main_unit_id'].','.$stockId.','.$quantity.',"'.$product['expiry'].'")';
                    $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());    
                }else{                    
                    echo '
                    <script>
                        alert("'.$lang['error_product_quantity_is_not_found_for_item_1'].$product['product_name'].' '.$lang['error_product_quantity_is_not_found_for_item_2'].'");
                    </script>';
                    exit();
                    $insertQuery = 'insert into `units_in_stocks`(`product_id`,`unit_id`,`stock_id`,`amount`,`expiry`)values('.$product['product_id'].','.$product['main_unit_id'].','.$stockId.','.($quantity * (-1)).',"'.$product['expiry'].'")';
                    $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());    
                }
            }
        }
        if($paid > 0){
            $permits = new permits();
            if($type == 1 || $type == 4){
                $permitType = 6; 
            }elseif($type == 2 || $type == 3){
                $permitType = 5; 
            }
            if($type == 1){
                $paymentCategoryKey = "supplierPayment";
            }elseif($type == 2){
                $paymentCategoryKey = "supplierIncom";
            }elseif($type == 3){
                $paymentCategoryKey = "customerIncom";                
            }elseif($type == 4){
                $paymentCategoryKey = "customerPayment";                
            }
            $getPaymentCategoryQuery = 'select * from `company_information` where `key` = "'.$paymentCategoryKey.'" and `com_id` = '.$_SESSION['company_id'];
            $getPaymentCategoryResult = mysql_query($getPaymentCategoryQuery)or die("error getPaymentCategoryQuery not done ".mysql_error());
            $paymentCategoryId = mysql_result($getPaymentCategoryResult,"0","value");            
            $getMaxNumberQuery = 'select (ifnull(max(`permit_number`),0)+1)as "number" from `permits` where `permit_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `permit_type_id` = '.$permitType;
            $getMaxNumberResult = mysql_query($getMaxNumberQuery);
            $permitNumber = mysql_result($getMaxNumberResult,"0","number");
            $permits->insertPermit($permitType,$permitNumber,$orderDate,$paid,$companyId,$treasuryId,$id,$paymentCategoryId,"");
        }
    }

    function updateOrder($type,$id,$orderNumber,$orderDate,$companyId,$stockId,$productId,$unitId,$quantity,$expiry,$price,$itemTotal,$total,$extra,$discount,$overall,$paid = 0,$treasuryId = 0){
        self::deleteOrder($type,$id);
        self::insertOrder($type,$orderNumber,$orderDate,$companyId,$stockId,$productId,$unitId,$quantity,$expiry,$price,$itemTotal,$total,$extra,$discount,$overall,$paid,$treasuryId);
    }

    function deleteOrder($type,$id){
        $getOldInfoQuery = 'select * from `permits` where `permit_id` = '.$id;
        $getOldInfoResult = mysql_query($getOldInfoQuery)or die("error getOldInfoQuery not done ".mysql_error());
        $oldInfo = mysql_fetch_array($getOldInfoResult);
        $checkSubPermitsQuery = 'select * from `permits` where `permit_id` > 0 and `parent_permit_id` = '.$id;
        $checkSubPermitsResult = mysql_query($checkSubPermitsQuery)or die("error checkSubPermitsQuery not done ".mysql_error());
        if(mysql_num_rows($checkSubPermitsResult) > 0){
            $permitInfo = mysql_fetch_array($checkSubPermitsResult);
            $permits = new permits();
            $permits->deletePermit($permitInfo['permit_type_id'],$permitInfo['permit_id']);
        }
        if($type == 1 || $type == 3){
            $oldOberator = '-';
        }else{
            $oldOberator = '+';
        }
        $updateCompanyQuery = 'update `companies` set `balance` = (`balance` '.$oldOberator.' '.$oldInfo['overall'].') where `company_id` = '.$oldInfo['company_id'];
        $updateCompanyResult = mysql_query($updateCompanyQuery)or die("error updateCompanyQuery not done ".mysql_error());
        if($type == 1 || $type == 4){
            $oldStockOberator = '-';
        }else{
            $oldStockOberator = '+';
        }
        $getOrderProductsQuery = '
        select 
            `permit_products`.`product_id`,
            `permit_products`.`unit_id`,
            `permit_products`.`quantity`,
            `permit_products`.`expiry`,
            `product_units`.`convertor`,
            `product_units2`.`unit_id` as "main_unit_id"
        from 
            `permit_products`
            inner join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
            inner join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
            inner join `product_units` on(`product_units`.`product_id` = `products`.`product_id` and `product_units`.`unit_id` = `units`.`unit_id`)
            inner join `product_units` `product_units2` on(`product_units2`.`product_id` = `products`.`product_id` and `product_units2`.`parent_unit_id` = 0)
        where 
            `permit_products`.`permit_id` = '.$id;        
        $getOrderProductsResult = mysql_query($getOrderProductsQuery)or die("error getOrderProductsQuery not done ".mysql_error());
        while($product = mysql_fetch_array($getOrderProductsResult)){
            $quantity = ($product['quantity'] * $product['convertor']);
            $checkQuery = 'select * from `units_in_stocks` where `product_id` = "'.$product['product_id'].'" and `unit_id` = "'.$product['main_unit_id'].'" and `expiry` = "'.$product['expiry'].'" and `stock_id` = '.$oldInfo['stock_id'];
            if($oldStockOberator == '-'){
                $checkQuery.=' and `amount` >= '.$quantity;
            }
            $checkResult = mysql_query($checkQuery)or die("error checkQuery not done ".mysql_error());
            if(mysql_num_rows($checkResult) > 0){
                $updateStockQuery = 'update `units_in_stocks` set `amount` = (`amount` '.$oldStockOberator.' '.$quantity.') where `stock_unit_id` = '.mysql_result($checkResult,"0","stock_unit_id");
                $updateStockResult = mysql_query($updateStockQuery)or die("error updateStockQuery not done ".mysql_error());
            }else{
                if($oldStockOberator == '+'){
                    $insertQuery = 'insert into `units_in_stocks`(`product_id`,`unit_id`,`stock_id`,`amount`,`expiry`)values('.$product['product_id'].','.$product['main_unit_id'].','.$oldInfo['stock_id'].','.$quantity.',"'.$product['expiry'].'")';
                    $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
                }else{
                    $insertQuery = 'insert into `units_in_stocks`(`product_id`,`unit_id`,`stock_id`,`amount`,`expiry`)values('.$product['product_id'].','.$product['main_unit_id'].','.$oldInfo['stock_id'].','.($quantity * (-1)).',"'.$product['expiry'].'")';
                    $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
                }
            }
        }
        $deleteOrderQuery = 'delete from `permits` where `permit_id` = '.$id;
        $deleteOrderResult = mysql_query($deleteOrderQuery)or die("error deleteOrderQuery not done ".mysql_error());
        
    }

    function checkProducts(){
        $checkAllProductsQuery = 'select * from `units_in_stocks` where `stock_unit_id` > 0 and `amount` < 0';
        $checkAllProductsResult = mysql_query($checkAllProductsQuery)or die("error checkAllProductsQuery not done ".mysql_error());
        if(mysql_num_rows($checkAllProductsResult) > 0){
            exit('storage error');
        }
    }

    function getOrders($type,$orderNumber = 0,$orderDate = '',$companyId = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllOrdersQuery = '
        select 
            `permits`.`permit_id`,
            `permits`.`permit_type_id`,
            `permits`.`permit_number`,
            `permits`.`permit_date`,
            `permits`.`overall`,
            `companies`.`company_name`
        from 
            `permits`
            inner join `companies` on(`permits`.`company_id` = `companies`.`company_id`)
        where 
            `permits`.`permit_id` > 0 
            and 
            `permits`.`com_id` = '.$_SESSION['company_id'].' 
            and 
            `permits`.`permit_type_id` = '.$type;
            if($orderNumber > 0){
                $getAllOrdersQuery.='
                and 
                `permits`.`permit_number` = '.$orderNumber;
            }
            if($orderDate != ''){
                $getAllOrdersQuery.='
                and 
                `permits`.`permit_date` = "'.$orderDate.'"';
            }
            if($companyId > 0){
                $getAllOrdersQuery.='
                and 
                `permits`.`company_id` = '.$companyId;
            }
            
            if($limit > 0){
                $getAllOrdersQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllOrdersResult = mysql_query($getAllOrdersQuery)or die("error getAllOrdersQuery not done ".mysql_error());
        $html='';
        while($order = mysql_fetch_array($getAllOrdersResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$order['permit_number'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$order['company_name'].'</h6>
                        <p class="card-text p-y-1 text-left">'.$order['permit_date'].'</p>
                        <a href="#" data-section="orders" data-action="edit" data-id="'.$order['permit_id'].'" data-type="'.$order['permit_type_id'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="orders" data-action="delete" data-id="'.$order['permit_id'].'" data-type="'.$order['permit_type_id'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;
    }

    function orderForm($type,$id = 0){
        global $lang;
        $info = array();
        if($type == "1" || $type == "2"){
            $companyType = $lang['the_supplier'];
            $companyTypeId = 2;
            $checked = '';
        }else{
            $companyType = $lang['the_customer'];
            $companyTypeId = 1;
            $checked = 'checked';
        }
        if($id > 0){
            $getOrderInfoQuery = 'select * from `permits` where `permit_id` = '.$id;
            $getOrderInfoResult = mysql_query($getOrderInfoQuery);
            $info = mysql_fetch_array($getOrderInfoResult);
            $getOrderProductsQuery = '
            select 
                `products`.`product_id`,
                `products`.`product_name`,
                `units`.`unit_id`,
                `units`.`unit_name`,
                `permit_products`.`quantity`,
                `permit_products`.`expiry`,
                `permit_products`.`price`,
                `permit_products`.`total`
            from 
                `permit_products`
                inner join `products` on(`products`.`product_id` = `permit_products`.`product_id`)
                inner join `units` on(`units`.`unit_id` = `permit_products`.`unit_id`)
            where 
                `permit_products`.`permit_id` = '.$id.' 
                and 
                `permit_products`.`product_id` > 0';
            $getOrderProductsResult = mysql_query($getOrderProductsQuery);
            $action = 'update';
            $checkSubPermitQuery = 'select `permits`.`total`,`treasuries`.`balance`,`companies`.`balance` as "company_balance" from `permits` inner join `treasuries` on(`treasuries`.`treasury_id` = `permits`.`treasury_id`) inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)  where `permit_id` > 0 and `parent_permit_id` = '.$id;
            $checkSubPermitResult = mysql_query($checkSubPermitQuery);
            if(mysql_num_rows($checkSubPermitResult) > 0){
                $permitInfo = mysql_fetch_array($checkSubPermitResult);
                $checked = 'checked';
            }else{
                $getCompanyCreditQuery = 'select * from `companies` where `company_id` = '.$info['company_id'];
                $getCompanyCreditResult = mysql_query($getCompanyCreditQuery);
                $getTreasuryCreditQuery = 'select * from `treasuries` where `user_id` = '.$_SESSION['user_id'];
                $getTreasuryCreditResult = mysql_query($getTreasuryCreditQuery);
                if(mysql_num_rows($getTreasuryCreditResult) == 0){
                    $getTreasuryCreditQuery = 'select * from `treasuries` where `user_id` = 0 and `com_id` = '.$_SESSION['company_id'];
                    $getTreasuryCreditResult = mysql_query($getTreasuryCreditQuery);
                }
                $permitInfo = array();                
                $permitInfo['treasury_id'] = mysql_result($getTreasuryCreditResult,"0","treasury_id");
                $permitInfo['balance'] = mysql_result($getTreasuryCreditResult,"0","balance");
                $permitInfo['total'] = "0";
                $permitInfo['company_balance'] = mysql_result($getCompanyCreditResult,"0","balance");;
                $checked = '';
            }
        }else{
            $getMaxNumberQuery = 'select (ifnull(max(`permit_number`),0)+1) as "number" from `permits` where `permit_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `permit_type_id` = '.$type;
            $getMaxNumberResult = mysql_query($getMaxNumberQuery)or die("error getMaxNumberQuery not done ".mysql_error());
            $action = 'insert';
            $info = array();
            $info['permit_number'] = mysql_result($getMaxNumberResult,"0","number");
            $info['permit_date'] = date('Y-m-d');
            $permitInfo = array();
            $getTreasuryCreditQuery = 'select * from `treasuries` where `user_id` = '.$_SESSION['user_id'];
            $getTreasuryCreditResult = mysql_query($getTreasuryCreditQuery);
            if(mysql_num_rows($getTreasuryCreditResult) == 0){
                $getTreasuryCreditQuery = 'select * from `treasuries` where `user_id` = 0 and `com_id` = '.$_SESSION['company_id'];
                $getTreasuryCreditResult = mysql_query($getTreasuryCreditQuery);
            }
            $permitInfo['treasury_id'] = mysql_result($getTreasuryCreditResult,"0","treasury_id");
            $permitInfo['balance'] = mysql_result($getTreasuryCreditResult,"0","balance");
            $permitInfo['total'] = "0";
            $permitInfo['company_balance'] = "0";
        }
        $getCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `type` = '.$companyTypeId.' and `com_id` = '.$_SESSION['company_id'].' order by `company_name`';
        $getCompaniesResult = mysql_query($getCompaniesQuery)or die("error getCompaniesQuery not done ".mysql_error());
        if($type == "1"  || $type == "4"){
            $treasuryCredit = ($permitInfo['balance'] + $permitInfo['total']);            
        }elseif($type == "2"  ||$type == "3"){
            $treasuryCredit = ($permitInfo['balance'] - $permitInfo['total']);
        }

        if($type == "1" || $type == "3"){
            $customerCredit = $permitInfo['company_balance'] - ($info['overall'] - $permitInfo['total']);
        }elseif($type == "2" || $type == "4"){
            $customerCredit = $permitInfo['company_balance'] + ($info['overall'] - $permitInfo['total']);
        }
        $form='
        <form method="POST" action="ajax.php?section=orders&action='.$action.'">
            <div style="width:85vw;height:80vh">
                <div class="w-100 h-25">
                    <div class="row">
                        <div class="form-group w-50">
                            <label for="orderNumber">'.$lang['order_number'].'</label>
                            <input type="number" class="form-control" name="orderNumber" id="orderNumber" aria-describedby="" placeholder="'.$lang['order_number_label'].'" value="'.$info['permit_number'].'">
                        </div>
                        <div class="form-group w-50">
                            <label for="orderDate">'.$lang['the_date'].'</label>
                            <input type="date" class="form-control" name="orderDate" id="orderDate" aria-describedby="" placeholder="'.$lang['action_date_label'].'" value="'.$info['permit_date'].'">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group w-50">
                            <label for="companyId">'.$companyType.'</label>
                            <select name="companyId" id="companyId" class="form-control" onchange=changeCompany(this)>
                                <option value="0">'.$lang['select'].' '.$companyType.'</option>';
                                while($company = mysql_fetch_array($getCompaniesResult)){
                                    $form.='<option value="'.$company['company_id'].'" data-type="'.$company['type'].'"';
                                    if($company['company_id'] == $info['company_id']){
                                        $form.=' selected ';
                                    }
                                    $form.='>'.$company['company_name'].'</option>';
                                }
                                $form.='
                            </select>
                        </div>
                        <div class="form-group w-50">
                            <label for="companyId">'.$lang['company_credit_after_'.$type].'</label>
                            <input type="number" style="width:100%" class="form-control" disabled  id="companyCredit" value="">
                        </div>                        
                    </div>
                </div>
                <div class="w-100 h-50" style="overflow-y:scroll;border: 1px inset #C0C0C0;margin-top:5px;margin-bottom:5px" id="orderProducts">';
                    while($product = mysql_fetch_array($getOrderProductsResult)){
                        $getProductUnitsQuery = '
                        select 
                            `units`.`unit_id`,
                            `units`.`unit_name`,
                            `product_units`.`sell_price`,
                            `product_units`.`purchase_price`
                        from 
                            `product_units` 
                            inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`)
                        where 
                            `product_units`.`product_id` = '.$product['product_id'];
                        $getProductUnitsResult = mysql_query($getProductUnitsQuery)or die("error getProductUnitsQuery not done ".mysql_error());
                        $form.='
                        <div class="w-100">
                            <table width="100%" border="1" cellpadding="0" cellspacing="0px">
                                <tr>
                                    <td colspan="3"><input type="hidden" name="productId[]" value="'.$product['product_id'].'">'.$product['product_name'].'<span class="pull-left"><a href="javascript:;" onclick=removeProduct(this);>x</a></span></td>
                                </tr>
                                <tr>
                                    <td width="33%" align="center"><input type="number" name="quantity[]" value="'.$product['quantity'].'" onkeyup=calcTotalOrder(); onfocus=select(this); style="width:100%;text-align:center"></td>
                                    <td width="33%" align="center">
                                        <select name="unitId[]" style="width:100%" onchange=getUnitPrice(this,"'.$product['product_id'].'","'.$type.'");>';
                                        while($unit = mysql_fetch_array($getProductUnitsResult)){
                                            $form.='<option value="'.$unit['unit_id'].'"';
                                            if($unit['unit_id'] == $product['unit_id']){
                                                $form.=' selected ';
                                            }
                                            $form.='>'.$unit['unit_name'].'</option>';
                                        }
                                        $form.='
                                        </select>
                                    </td>
                                    <td width="34%" align="center">';
                                        if($type == 1 || $type == 4){
                                            $form.='<input type="date" name="expiry[]" value="" style="width:100%" value="'.$product['expiry'].'">';
                                        }else{
                                            $getExpiriesQuery = '
                                            select 
                                                `units_in_stocks`.`expiry`
                                            from 
                                                `units_in_stocks`
                                            where 
                                                `units_in_stocks`.`product_id` = '.$product['product_id'].' 
                                                and 
                                                `units_in_stocks`.`stock_id` = 1 
                                                and 
                                                `units_in_stocks`.`amount` > 0
                                            order by 
                                                `units_in_stocks`.`expiry` desc';
                                            $getExpiriesResult = mysql_query($getExpiriesQuery)or die("error getExpiriesQuery not done ".mysql_error());
                                            $form.='
                                            <select name="expiry[]" style="width:100%">';
                                                while($ex = mysql_fetch_array($getExpiriesResult)){
                                                    $form.='
                                                    <option value="'.$ex['expiry'].'"';
                                                    if($ex['expiry'] == $product['expiry']){
                                                        $form.=' selected ';
                                                    }
                                                    $form.='>'.$ex['expiry'].'</option>';    
                                                }
                                                $form.='
                                            </select>';
                                        }
                                        $form.='
                                    </td>
                                </tr>
                                <tr>
                                    <td width="33%" align="center"><input type="number" style="width:50%;text-align:center" name="price[]" onkeyup=calcTotalOrder(); onfocus=select(this); value="'.$product['price'].'"><span id="my-suffix">EGP.</span></td>
                                    <td colspan="2" width="67%" align="center"><input type="number" style="width:50%;text-align:center;" name="itemTotal[]" value="'.$product['total'].'"><span id="my-suffix">EGP.</span></td>
                                </tr>
                            </table>
                        </div>';
                    }
                    $form.='
                </div>
                <div class="w-100 h-25">
                    <table width="100%" border="1" cellpadding="0" cellspacing="0px">
                        <tr>
                            <td width="25%" align="center"><input type="number" id="barCode" name="barcode" data-type="'.$type.'" data-stock="1" onkeypress=sendCode(event,this); onchange=sendCode2(this); style="width:100%" placeholder="'.$lang['code_barcode_label'].'"></td>
                            <td colspan="3" width="75%"><input type="text" name="productName" style="width:100%" placeholder="'.$lang['product_name'].'" class="auto" data-objectname="product" data-filter="" data-target="productName"></td>
                        </tr>
                        <tr>
                            <td width="25%" align="center">'.$lang['total'].'</td>
                            <td width="25%" align="center"><input type="number" style="width:100%" name="total" id="total" value="'.$info['total'].'"></td>
                            <td width="25%" align="center">'.$lang['to_be_added'].'</td>
                            <td width="25%" align="center"><input type="number" style="width:100%" name="extra" id="extra" value="'.$info['extra'].'" onkeyup=calcTotalOrder()></td>
                        </tr>
                        <tr>
                            <td width="25%" align="center">'.$lang['discount'].'</td>
                            <td width="25%" align="center"><input type="number" style="width:100%" name="discount" id="discount" value="'.$info['discount'].'" onkeyup=calcTotalOrder()></td>
                            <td width="25%" align="center">'.$lang['total'].'</td>
                            <td width="25%" align="center"><input type="number" style="width:100%" name="overall" id="overall" value="'.$info['overall'].'"></td>
                        </tr>
                        <tr>
                            <td width="25%" align="center">'.$lang['paied'].' <input type="checkbox" '.$checked.' id="paidState" onclick=setPaid(this)></td>
                            <td width="25%" align="center"><input type="number" style="width:100%" name="paid" id="paid" value="'.$permitInfo['total'].'" onkeyup=calcCredits();></td>
                            <td width="25%" align="center">'.$lang['treasury_credit'].'</td>
                            <td width="25%" align="center"><input type="number" style="width:100%" id="treasuryCredit" disabled  value=""></td>
                        </tr>
                        <tr>
                            <td width="100%" colspan="4" align="center"><button type="button" class="btn btn-primary submitBtn" >'.$lang['save'].'</button></td>
                        </tr>
                    </table>                    
                </div>
            </div>
            <input type="hidden" name="id" value="'.$id.'">
            <input type="hidden" name="type" value="'.$type.'">
            <input type="hidden" id="tcredit" value="'.$treasuryCredit.'">
            <input type="hidden" id="ccredit" value="'.$customerCredit.'">
            <input type="hidden" name="treasuryId" value="'.$permitInfo['treasury_id'].'">
            <input type="hidden" id="ctype" value="'.$companyTypeId.'">
            <input type="hidden" name="stockId" value="1">
        </form>';
        return $form;
    }

    function getProduct($type,$productCode,$stockId){
        if($type == 1 || $type == 2){
            $default = '2';
            $price = 'purchase_price';
        }else{
            $default = '1';
            $price = 'sell_price';
        }

        $getProductQuery = '
        select 
            `products`.`product_id`,
            `products`.`product_name`,
            `units`.`unit_id`,
            `units`.`unit_name`,
            `product_units`.`sell_price`,
            `product_units`.`purchase_price`
        from 
            `products`
            inner join `product_units` on(`products`.`product_id` = `product_units`.`product_id`)
            inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`)
        where 
            `products`.`product_id` > 0 
            and 
            `products`.`com_id` = '.$_SESSION['company_id'].' 
            and 
            `products`.`product_code` = "'.$productCode.'"
            and 
            `product_units`.`default` in('.$default.',3)';
        $getProductResult = mysql_query($getProductQuery)or die("error getProductQuery not done ".mysql_error());
        $product = mysql_fetch_array($getProductResult);
        $getMinExpiryQuery = 'select min(`units_in_stocks`.`expiry`)as "expiry" from `units_in_stocks` where `product_id` = '.$product['product_id'].' and `stock_id` = '.$stockId;
        $getMinExpiryResult = mysql_query($getMinExpiryQuery)or die("error getMinExpiryQuery not done ".mysql_error());
        $expiry = mysql_fetch_array($getMinExpiryResult);
        $getProductUnitsQuery = '
        select 
            `units`.`unit_id`,
            `units`.`unit_name`,
            `product_units`.`sell_price`,
            `product_units`.`purchase_price`
        from 
            `product_units` 
            inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`)
        where 
            `product_units`.`product_id` = '.$product['product_id'];
        $getProductUnitsResult = mysql_query($getProductUnitsQuery)or die("error getProductUnitsQuery not done ".mysql_error());
        $getExpiriesQuery = '
        select 
            `units_in_stocks`.`expiry`
        from 
            `units_in_stocks`
        where 
            `units_in_stocks`.`product_id` = '.$product['product_id'].' 
            and 
            `units_in_stocks`.`stock_id` = '.$stockId.' 
            and 
            `units_in_stocks`.`amount` > 0
        order by 
            `units_in_stocks`.`expiry` desc';
        $getExpiriesResult = mysql_query($getExpiriesQuery)or die("error getExpiriesQuery not done ".mysql_error());

        $html= '
        <div class="w-100">
            <table width="100%" border="1" cellpadding="0" cellspacing="0px">
                <tr>
                    <td colspan="3"><input type="hidden" name="productId[]" value="'.$product['product_id'].'">'.$product['product_name'].'<span class="pull-left"><a href="javascript:;" onclick=removeProduct(this);>x</a></span></td>
                </tr>
                <tr>
                    <td width="33%" align="center"><input type="number" name="quantity[]" value="1" onkeyup=calcTotalOrder(); onfocus=select(this); style="width:100%;text-align:center"></td>
                    <td width="33%" align="center">
                        <select name="unitId[]" style="width:100%" onchange=getUnitPrice(this,"'.$product['product_id'].'","'.$type.'");>';
                        while($unit = mysql_fetch_array($getProductUnitsResult)){
                            $html.='<option value="'.$unit['unit_id'].'"';
                            if($unit['unit_id'] == $product['unit_id']){
                                $html.=' selected ';
                            }
                            $html.='>'.$unit['unit_name'].'</option>';
                        }
                        $html.='
                        </select>
                    </td>
                    <td width="34%" align="center">';
                        if($type == 1 || $type == 4){
                            $html.='<input type="date" name="expiry[]" value="" style="width:100%">';
                        }else{
                            $html.='
                            <select name="expiry[]" style="width:100%">';
                                while($ex = mysql_fetch_array($getExpiriesResult)){
                                    $html.='
                                    <option value="'.$ex['expiry'].'"';
                                    if($ex['expiry'] == $expiry['expiry']){
                                        $html.=' selected ';
                                    }
                                    $html.='>'.$ex['expiry'].'</option>';    
                                }
                                $html.='
                            </select>';
                        }
                        $html.='
                    </td>
                </tr>
                <tr>
                    <td width="33%" align="center"><input type="number" style="width:50%;text-align:center" name="price[]"  onkeyup=calcTotalOrder(); onfocus=select(this); value="'.$product[$price].'"><span id="my-suffix">EGP.</span></td>
                    <td colspan="2" width="67%" align="center"><input type="number" style="width:50%;text-align:center;" name="itemTotal[]" value="'.$product[$price].'"><span id="my-suffix">EGP.</span></td>
                </tr>
            </table>
        </div>';
        return $html;
    }


    function getUnitPrice($product,$unit,$type){
        $getProductQuery = '
        select 
            `products`.`product_id`,
            `products`.`product_name`,
            `units`.`unit_id`,
            `units`.`unit_name`,
            `product_units`.`sell_price`,
            `product_units`.`purchase_price`
        from 
            `products`
            inner join `product_units` on(`products`.`product_id` = `product_units`.`product_id`)
            inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`)
        where 
            `products`.`product_id` > 0 
            and 
            `products`.`product_id` = "'.$product.'"
            and 
            `product_units`.`unit_id` ='.$unit.'';
        $getProductResult = mysql_query($getProductQuery)or die("error getProductQuery not done ".mysql_error());
        $product = mysql_fetch_array($getProductResult);      
        if($type == 1 || $type == 2){
            $price = 'purchase_price';
        }else{
            $price = 'sell_price';
        }
        return $product[$price];
    }

}
?>