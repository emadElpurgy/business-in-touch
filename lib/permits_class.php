<?php 
class permits{
    function validate($id = 0){
        global $lang;        
        $errors = '';
        if(!isset($_POST['permitNumber']) || trim($_POST['permitNumber']) == ""){
            echo '
            <script>
                alert("'.$lang['error_permitNumber_is_required'].'");
                document.getElementsByName("permitNumber")[0].focus();
            </script>';
            exit();
        }
        $checkPermitNumberExistsQuery = 'select * from `permits` where `permit_id` > 0 and `permit_type_id` = '.$_POST['type'].' and `permit_number` = "'.$_POST['permitNumber'].'"';
        if($id > 0){
            $checkPermitNumberExistsQuery.=' and `permit_id` <> 0';
        }
        $checkPermitNumberExistsResult = mysql_query($checkPermitNumberExistsQuery)or die("error checkPermitNumberExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkPermitNumberExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_permitNumber_is_exists'].'");
                document.getElementsByName("permitNumber")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['permitDate']) || trim($_POST['permitDate']) == ""){
            echo '
            <script>
                alert("'.$lang['error_permitDate_is_required'].'");
                document.getElementsByName("permitDate")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['treasuryId']) || trim($_POST['treasuryId']) == "" || $_POST['treasuryId'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_treasuryId_is_required'].'");
                document.getElementsByName("treasuryId")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['categoryId']) || trim($_POST['categoryId']) == "" || $_POST['categoryId'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_categoryId_is_required'].'");
                document.getElementsByName("categoryId")[0].focus();
            </script>';
            exit();
        }
        $checkDefaultCategoryQuery = 'select * from `company_information` where `value` = "'.$_POST['categoryId'].'"';
        $checkDefaultCategoryResult = mysql_query($checkDefaultCategoryQuery)or die("error checkDefaultCategoryQuery not done ".mysql_error());
        if(mysql_num_rows($checkDefaultCategoryResult) > 0){
            $key = mysql_result($checkDefaultCategoryResult,"0","key");
            if($key == 'supplierPayment' || $key == 'supplierIncom'){
                echo '
                <script>
                    alert("'.$lang['error_supplier_is_required'].'");
                    document.getElementsByName("companyId")[0].focus();
                </script>';
                exit();
            }elseif($key == 'customerPayment' || $key == 'customerIncom'){
                echo '
                <script>
                    alert("'.$lang['error_customer_is_required'].'");
                    document.getElementsByName("companyId")[0].focus();
                </script>';
                exit();                
            }
            
        }
        if(!isset($_POST['total']) || trim($_POST['total']) == "" || $_POST['total'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_permit_sum_is_required'].'");
                document.getElementsByName("total")[0].focus();
            </script>';
            exit();
        }
        if($errors != ''){
            return array('status'=>false,'message'=>$errors);
        }else{
            return array('status'=>true,'message'=>'');
        }
    }

    function insertPermit($type,$permitNumber,$permitDate,$total,$companyId,$treasuryId,$orderId,$categoryId,$notes){        
        $insertQuery = 'insert into `permits`(`permit_number`,`permit_type_id`,`permit_date`,`total`,`extra`,`discount`,`overall`,`company_id`,`treasury_id`,`stock_id`,`parent_permit_id`,`category_id`,`notes`,`com_id`)values("'.$permitNumber.'","'.$type.'","'.$permitDate.'","'.($total+0).'",0,0,"'.($total+0).'","'.($companyId+0).'","'.$treasuryId.'",0,"'.($orderId+0).'","'.$categoryId.'","'.trim(addslashes($notes)).'","'.$_SESSION['company_id'].'")';
        file_put_contents('f',$insertQuery);
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
        $permitId = mysql_insert_id();
        if($type == 5){
            $operator = '+';
        }elseif($type == 6){
            $operator = '-';
        }
        $updateTreasuryQuery = 'update `treasuries` set `balance` = (`balance` '.$operator.' '.($total+0).') where `treasury_id` = '.$treasuryId;
        $updateTreasuryResult = mysql_query($updateTreasuryQuery)or die("error updateTreasuryQuery not done ".mysql_error());
        if($companyId > 0){
            $getCompanyQuery = 'select * from `companies` where `company_id` = '.$companyId;
            $getCompanyResult = mysql_query($getCompanyQuery)or die("error getCompanyQuery not done ".mysql_error());
            $companyType = mysql_result($getCompanyResult,"0","type");
            if(($operator == '+' && $companyType == 1) || ($operator == '-' && $companyType == 2)){
                $companyOperator = '-';
            }else{
                $companyOperator = '+';
            }
            $updateCompanyQuery = 'update `companies` set `balance` = (`balance` '.$companyOperator.' '.($total+0).') where `company_id` = '.$companyId;
            $updateCompanyResult = mysql_query($updateCompanyQuery)or die("error updateCompanyQuery not done ".mysql_error());
        }
    }

    function updatePermit($type,$id,$permitNumber,$permitDate,$total,$companyId,$treasuryId,$orderId,$categoryId,$notes){
        self::deletePermit($type,$id);
        self::insertPermit($type,$permitNumber,$permitDate,$total,$companyId,$treasuryId,$orderId,$categoryId,$notes);
    }

    function deletePermit($type,$id){
        $getInfoQuery = 'select * from `permits` where `permit_id` = '.$id;
        $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
        $oldInfo = mysql_fetch_array($getInfoResult);
        if($oldInfo['permit_type_id'] == 5){
            $operator = '-';
        }elseif($oldInfo['permit_type_id'] == 6){
            $operator = '+';
        }
        $updateTreasuryQuery = 'update `treasuries` set `balance` = (`balance` '.$operator.' '.($oldInfo['total']+0).') where `treasury_id` = '.$oldInfo['treasury_id'];
        $updateTreasuryResult = mysql_query($updateTreasuryQuery)or die("error updateTreasuryQuery not done ".mysql_error());
        if($oldInfo['company_id'] > 0){
            $getCompanyQuery = 'select * from `companies` where `company_id` = '.$oldInfo['company_id'];
            $getCompanyResult = mysql_query($getCompanyQuery)or die("error getCompanyQuery not done ".mysql_error());
            $companyType = mysql_result($getCompanyResult,"0","type");
            if(($operator == '-' && $companyType == 1) || ($operator == '+' && $companyType == 2)){
                $companyOperator = '+';
            }else{
                $companyOperator = '-';
            }
            $updateCompanyQuery = 'update `companies` set `balance` = (`balance` '.$companyOperator.' '.($oldInfo['total']+0).') where `company_id` = '.$oldInfo['company_id'];
            $updateCompanyResult = mysql_query($updateCompanyQuery)or die("error updateCompanyQuery not done ".mysql_error());
        }
        $deleteQuery = 'delete from permits where permit_id = '.$id;        
        $deleteResult= mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }

    function checkTreasury($id = 0){
        global $lang;
        $getAllTreasuriesQuery = 'select * from `treasuries` where `treasury_id` > 0 and `balance` < 0 and `com_id` = '.$_SESSION['company_id'];
        if($id > 0){
            $getAllTreasuriesQuery.= ' and `treasury_id` = '.$id;
        }
        $getAllTreasuriesResult = mysql_query($getAllTreasuriesQuery)or die("error getAllTreasuriesQuery not done ".mysql_error());
        if(mysql_num_rows($getAllTreasuriesResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_treasury_credit_is_not_valid'].'");
            </script>';
            exit();
        }
    }

    function getPermits($type,$permitNumber = 0,$permitDate = '',$companyId = 0,$categoryId = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllPermitsQuery = '
        select 
            `permits`.`permit_id`,
            `permits`.`permit_type_id`,
            `permits`.`permit_number`,
            `permits`.`permit_date`,
            `companies`.`company_name`,
            `permits`.`total`,
            `categories`.`category_name`
        from 
            `permits`
            inner join `companies` on(`companies`.`company_id` = `permits`.`company_id`)
            inner join `categories` on(`categories`.`category_id` = `permits`.`category_id`)
        where 
            `permits`.`permit_type_id` = '.$type.'
            and 
            `permits`.`com_id` = '.$_SESSION['company_id'];
            if($permitNumber > 0){
                $getAllPermitsQuery.=' 
                and 
                `permits`.`permit_number` = '.$permitNumber;
            }
            if($permitDate != ''){
                $getAllPermitsQuery.='
                and 
                `permits`.`permit_date` = "'.$permitDate.'"';
            }
            if($companyId > 0){
                $getAllPermitsQuery.='
                and 
                `permits`.`company_id` = '.$companyId;
            }
            if($categoryId > 0){
                $getAllPermitsQuery.='
                and 
                `permits`.`category_id` = '.$categoryId;
            }
            if($limit > 0){
                $getAllPermitsQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllPermitsResult = mysql_query($getAllPermitsQuery)or die("error getAllPermitsQuery not done ".mysql_error());
        $html='';
        while($permit = mysql_fetch_array($getAllPermitsResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$permit['permit_number'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$permit['category_name'].' '.$permit['company_name'].'</h6>
                        <p class="card-text p-y-1 text-right">'.$permit['permit_date'].'</p>
                        <a href="#" data-section="permits" data-action="edit" data-id="'.$permit['permit_id'].'" data-type="'.$permit['permit_type_id'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="permits" data-action="delete" data-id="'.$permit['permit_id'].'" data-type="'.$permit['permit_type_id'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;
    }

    function permitForm($type,$id = 0){
        global $lang;
        $info = array();
        if($id > 0){
            $getInfoQuery = 'select * from `permits` where `permit_id` = '.$id;
            $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getInfoResult);
            $action = 'update';
        }else{
            $action = 'insert';
            $getMaxNumberQuery = 'select (ifnull(max(`permit_number`),0)+1)as "number" from `permits` where `permit_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `permit_type_id` = '.$type;
            $getMaxNumberResult = mysql_query($getMaxNumberQuery);
            $info['permit_number'] = mysql_result($getMaxNumberResult,"0","number");
            $info['permit_date'] = date('Y-m-d');
        }
        if($type == 5){
            $categoriesType = 5;
        }elseif($type == 6){
            $categoriesType = 4;
        }
        $treasuryCredit = 0;
        $customerCredit = 0;
        $companyType = 0;
        $getAllTreasuriesQuery = 'select * from `treasuries` where `treasury_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `user_id` = '.$_SESSION['user_id'];                
        $getAllTreasuriesResult = mysql_query($getAllTreasuriesQuery)or die("error getAllTreasuriesQuery not done ".mysql_error());
        if(mysql_num_rows($getAllTreasuriesResult) == 0){
            $getAllTreasuriesQuery = 'select * from `treasuries` where `treasury_id` > 0 and `com_id` = '.$_SESSION['company_id'];
            $getAllTreasuriesResult = mysql_query($getAllTreasuriesQuery)or die("error getAllTreasuriesQuery not done ".mysql_error());    
        }
        $getAllCategoriesQuery = 'select * from `categories` where `category_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `type` = '.$categoriesType;
        $getAllCategoriesResult = mysql_query($getAllCategoriesQuery)or die("error getAllCategoriesQuery not done ".mysql_error());
        $getAllCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `com_id` = '.$_SESSION['company_id'];
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $form='
        <form method="POST" action="ajax.php?section=permits&action='.$action.'">
        <div style="width:85vw;">
            <div class="row">
                <div class="form-group w-50">
                    <label for="permitNumber">'.$lang['permit_number'].'</label>
                    <input type="text" class="form-control" name="permitNumber" id="permitNumber" aria-describedby="userNamelHelp" placeholder="'.$lang['permit_number_label'].'" value="'.$info['permit_number'].'">
                </div>        
                <div class="form-group w-50">
                    <label for="permitDate">'.$lang['permit_date'].'</label>
                    <input type="date" class="form-control" name="permitDate" id="permitDate" aria-describedby="userNamelHelp" placeholder="'.$lang['permit_date_label'].'" value="'.$info['permit_date'].'">
                </div>
            </div>
            <div class="row">
                <div class="form-group w-50">
                    <label for="treasuryId">'.$lang['treasury'].'</label>
                    <select class="form-control" id="treasuryId" name="treasuryId"  onchange=changeTreasury(this)>';
                        if(mysql_num_rows($getAllTreasuriesResult) > 1){
                            $form.='
                            <option value="0">'.$lang['select_treasury'].'</option>';                            
                        }else{
                            $treasuryCredit = mysql_deata_seek($getAllTreasuriesResult,0);
                        }
                        while($treasury = mysql_fetch_array($getAllTreasuriesResult)){
                            $form.='<option value="'.$treasury['treasury_id'].'" ';
                            if($treasury['treasury_id'] == $info['treasury_id']){
                                if($type == 5){
                                    $treasuryCredit = ($treasury['balance'] - $info['total']);
                                }elseif($type == 5){
                                    $treasuryCredit = ($treasury['balance'] + $info['total']);
                                }                                
                                $form.=' selected ';
                            }
                            $form.='>'.$treasury['treasury_name'].'</option>';
                        }
                        $form.='
                    </select>
                </div>
                <div class="form-group w-50">
                    <label for="categoryId">'.$lang['the_category'].'</label>
                    <select class="form-control" id="categoryId" name="categoryId" onchange=changeFinanceCategory(this)>
                        <option value="0">'.$lang['select_category'].'</option>';
                        while($cat = mysql_fetch_array($getAllCategoriesResult)){
                            $form.='<option value="'.$cat['category_id'].'" ';
                            if($cat['category_id'] == $info['category_id']){
                                $form.=' selected ';
                            }
                            $form.='>'.$cat['category_name'].'</option>';
                        }
                        $form.='
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="companyId">'.$lang['supplier_or_customer'].'</label>
                <div id="companyContainer">
                    <select class="form-control" id="companyId" name="companyId" onchange=changeCompany(this)>
                        <option value="0">'.$lang['select_supplier_or_customer'].'</option>';
                        while($company = mysql_fetch_array($getAllCompaniesResult)){
                            $form.='<option value="'.$company['company_id'].'" data-type="'.$company['type'].'"';
                            if($company['company_id'] == $info['company_id']){
                                $form.=' selected ';
                                if(($type == 5 && $company['type'] == 1) || ($type == 6 && $company['type'] == 2)){
                                    $customerCredit = ($company['balance'] + $info['total']);
                                }else{
                                    $customerCredit = ($company['balance'] - $info['total']);
                                }

                                $companyType = $company['type'];
                            }
                            $form.='>'.$company['company_name'].'</option>';
                        }
                        $form.='
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="total">'.$lang['permit_amount'].'</label>
                <input type="number" class="form-control" name="total" id="total"  placeholder="'.$lang['permit_amount_label'].'" value="'.$info['total'].'" onfocus=select(this); onkeyup=catcTotalPermit();>
            </div>  
            <div class="form-group">
                <label for="notes">'.$lang['notes'].'</label>
                <textarea class="form-control" rows="2" name="notes"  id="notes" placeholder="'.$lang['notes_settings'].'">'.$info['notes'].'</textarea>
            </div>  
            <div class="row">
                <div class="form-group w-50">
                    <label for="treasuryCredit">'.$lang['treasury_credit'].'</label>
                    <input type="text" class="form-control" name="treasuryCredit" id="treasuryCredit"  placeholder="" value="" disabled>
                </div>                  
                <div class="form-group w-50">
                    <label for="companyCredit">'.$lang['customer_or_supplier_credit'].'</label>
                    <input type="text" class="form-control" name="companyCredit" id="companyCredit"  placeholder="" value="" disabled>
                </div>  
            </div>        
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">'.$lang['save'].'</button>
            </div>
            <input type="hidden" id="tcredit" value="'.$treasuryCredit.'">
            <input type="hidden" id="ccredit" value="'.$customerCredit.'">
            <input type="hidden" id="ctype" value="'.$companyType.'">
            <input type="hidden" name="id" value="'.$id.'">
            <input type="hidden" name="type" value="'.$type.'">
        </div>
        </form>';        
        return $form;
    }

    function getCompanyCredit($id){
        $getCreditQuery = 'select * from `companies` where `company_id` = '.$id;
        $getCreditResult = mysql_query($getCreditQuery)or die("error getCreditQuery not done ".mysql_error());
        return mysql_result($getCreditResult,"0","balance");
    }

    function getTreasuryCredit($id){
        $getCreditQuery = 'select * from `treasuries` where `treasury_id` = '.$id;
        $getCreditResult = mysql_query($getCreditQuery)or die("error getCreditQuery not done ".mysql_error());
        return mysql_result($getCreditResult,"0","balance");
    }


    function getCompanyList($categoryId){
        global $lang;
        $checkDefaultCategoryQuery = 'select * from `company_information` where `value` = "'.$categoryId.'"';
        $checkDefaultCategoryResult = mysql_query($checkDefaultCategoryQuery)or die("error checkDefaultCategoryQuery not done ".mysql_error());
        if(mysql_num_rows($checkDefaultCategoryResult) > 0){
            $key = mysql_result($checkDefaultCategoryResult,"0","key");
        }
        if($key == 'supplierPayment' || $key == 'supplierIncom'){
            $query = 'select * from `companies` where `company_id` > 0 and `type` = 2 and `com_id` = '.$_SESSION['company_id'];
        }elseif($key == 'customerPayment' || $key == 'customerIncom'){
            $query = 'select * from `companies` where `company_id` > 0 and `type` = 1 and `com_id` = '.$_SESSION['company_id'];
        }else{
            $query = 'select * from `companies` where `company_id` > 0 and `com_id` = '.$_SESSION['company_id'];
        }
        $result = mysql_query($query)or die("error query not done ".mysql_error());
        $html = '
        <select class="form-control" id="companyId" name="companyId" onchange=changeCompany(this)>
            <option value="0">'.$lang['select_supplier_or_customer'].'</option>';
            while($company = mysql_fetch_array($result)){
                $html.='<option value="'.$company['company_id'].'" data-type="'.$company['type'].'">'.$company['company_name'].'</option>';
            }
            $html.='
        </select>';
        return $html;
    }


}
?>