<?php 
class companies{
    function validate($id = 0){
        global $lang;
        if(!isset($_POST['companyName']) || trim($_POST['companyName']) == ""){
            echo '
            <script>
                alert("'.$lang['error_company_name_is_required_'.$_POST['type']].'");
                document.getElementsByName("companyName")[0].focus();
            </script>';
            exit();
        }
        $checkNameExistsQuery = 'select * from `companies` where `company_id` > 0 and `type` = '.$_POST['type'].' and `com_id` = '.$_SESSION['company_id'].' and `company_name` = "'.addslashes(trim($_POST['companyName'])).'"';
        if($id > 0){
            $checkNameExistsQuery.=' and `company_id` <> '.$id;
        }
        $checkNameExistsResult = mysql_query($checkNameExistsQuery)or die("error checkNameExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkNameExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_company_name_is_exists_'.$_POST['type']].'");
                document.getElementsByName("companyName")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['categoryId']) || trim($_POST['categoryId']) == "" || $_POST['categoryId'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_category_is_required'].'");
                document.getElementsByName("categoryId")[0].focus();
            </script>';
            exit();
        }
        $checkSubCategoriesQuery = 'select * from `categories` where `parent_category_id` = '.$_POST['categoryId'];
        $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
        if(mysql_num_rows($checkSubCategoriesResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_category_has_sub_categories'].'");
                document.getElementsByName("categoryId")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['companyCode']) || trim($_POST['companyCode']) == ""){
            echo '
            <script>
                alert("'.$lang['error_company_code_required_'.$_POST['type']].'");
                document.getElementsByName("companyCode")[0].focus();
            </script>';
            exit();
        }
        $checkCodeExistsQuery = 'select * from `companies` where `company_id` > 0 and `type` = '.$_POST['type'].' and `company_code` = "'.trim(addslashes($_POST['companyCode'])).'"';
        if($id > 0){
            $checkCodeExistsQuery.=' and `company_id` <> '.$id;
        }
        $checkCodeExistsResult = mysql_query($checkCodeExistsQuery)or die("error checkCodeExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkCodeExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_company_code_exists_'.$_POST['type']].'");
                document.getElementsByName("companyCode")[0].focus();
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function validateDelete($id){        
        global $lang;
        $checkpermitsQuery = 'select * from `permits` where `permit_id` > 0 and  `company_id` = '.$id;
        $checkpermitsResult = mysql_query($checkpermitsQuery)or die("error checkpermitsQuery notr done ".mysql_error());        
        if(mysql_num_rows($checkpermitsResult) > 0){
            $getTypeQuery = 'select * from companies where company_id = '.$id;
            $getTypeResult = mysql_query($getTypeQuery)or die("error getTypeQuery not done ".mysql_error());
            $type = mysql_result($getTypeResult,"0","type");
            echo '
            <script>
                alert("'.$lang['error_cannot_delete_company_for_sub_permits_'.$type].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function insertCompany($type,$companyName,$companyCode,$categoryId,$phone,$fax,$mobile,$email,$address,$balance){
        $insertCompanyQuery = 'insert `companies` (`company_name`,`company_code`,`type`,`category_id`,`phone`,`fax`,`mobile`,`email`,`address`,`balance`,`com_id`)values("'.trim(addslashes(($companyName))).'","'.trim(addslashes($companyCode)).'",'.$type.','.$categoryId.',"'.trim(addslashes($phone)).'","'.trim(addslashes($fax)).'","'.trim(addslashes($mobile)).'","'.trim(addslashes($email)).'","'.trim(addslashes($address)).'",'.($balance+0).',"'.$_SESSION['company_id'].'")';
        $insertCompanyResult = mysql_query($insertCompanyQuery)or die("error insertCompanyQuery not done ".mysql_error());
    }

    function updateCompany($id,$type,$companyName,$companyCode,$categoryId,$phone,$fax,$mobile,$email,$address,$balance){
        $updateCompanyQuery = 'update `companies` set `company_name` ="'.trim(addslashes(($companyName))).'" , `company_code` = "'.trim(addslashes($companyCode)).'" , `category_id` = '.$categoryId.' , `phone` = "'.trim(addslashes($phone)).'" , `fax` = "'.trim(addslashes($fax)).'" , `mobile` = "'.trim(addslashes($mobile)).'" , `email` = "'.trim(addslashes($email)).'", `address` = "'.trim(addslashes($address)).'" where `company_id` = '.$id;
        $updateCompanyResult = mysql_query($updateCompanyQuery)or die("error updateCompanyQuery not done ".mysql_error());
    }

    function deleteCompany($id){
        $deleteCompanyQuery = 'delete from `companies` where `company_id` = '.$id;
        $deleteCompanyResult = mysql_query($deleteCompanyQuery)or die("error deleteCompanyQuery not done ".mysql_error());
    }

    function getCompanies($type,$companyId = 0,$companyName = '',$companyCode = '',$categoryId = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllCompaniesQuery = '
        select 
            `companies`.`company_id`,
            `companies`.`type`,
            `companies`.`company_name`,
            `companies`.`company_code`,
            `categories`.`category_id`,
            concat(`parent_categories`.`category_name`," ", `categories`.`category_name`)as "category_name",
            `companies`.`phone`,
            `companies`.`fax`,
            `companies`.`email`,
            `companies`.`address`,
            `companies`.`balance`
        from 
            `companies`
            inner join `categories` on(`categories`.`category_id` = `companies`.`category_id`)
            inner join `categories` `parent_categories` on(`parent_categories`.`category_id` = `categories`.`parent_category_id`)
        where 
            `companies`.`company_id` > 0 
            and 
            `companies`.`com_id` = '.$_SESSION['company_id'].' 
            and 
            `companies`.`type` = '.$type;
            if($companyId > 0){
                $getAllCompaniesQuery.='
                and 
                `companies`.`company_id` = '.$companyId;
            }
            if($companyName != ''){
                $getAllCompaniesQuery.='
                and 
                `companies`.`company_name` like "%'.$companyName.'%"';
            }
            if($companyCode != ''){
                $getAllCompaniesQuery.='
                and 
                `companies`.`company_code` like "%'.$companyCode.'%"';
            }
            if($categoryId > 0){
                $getAllCompaniesQuery.='
                and 
                `companies`.`category_id` = "'.$categoryId.'"';
            }
            if($limit > 0){
                $getAllCompaniesQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $html='';
        while($company = mysql_fetch_array($getAllCompaniesResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$company['company_name'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$company['category_name'].'</h6>
                        <p class="card-text p-y-1 text-center">'.$company['balance'].' EGP</p>
                        <a href="#" data-section="companies" data-action="edit" data-id="'.$company['company_id'].'" data-type="'.$company['type'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="companies" data-action="delete" data-id="'.$company['company_id'].'" data-type="'.$company['type'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;

    }

    function getSubCategories($type,$level,$id,$selectedId,$list){
        $getSubCategoriesQuery = 'select `category_id`,`category_name` from `categories` where `category_id` > 0 and `type` = '.$type.' and `com_id` = '.$_SESSION['company_id'].' and `parent_category_id` = '.$id;
        $getSubCategoriesResult = mysql_query($getSubCategoriesQuery)or die("error getSubCategoriesQuery not done ".mysql_error());
        $format = '';
        for($x = 0; $x < $level; $x++){
            $format.='----';
        }
        if($format != ''){
            $format.='>';
        }
        while($category = mysql_fetch_array($getSubCategoriesResult)){
            $list.='<option value="'.$category['category_id'].'" ';
            if($category['category_id'] == $selectedId){
                $list.=' selected ';
            }
            $checkSubCategoriesQuery = 'select * from `categories` where `com_id` = '.$_SESSION['company_id'].' and `parent_category_id` = '.$category['category_id'];
            $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
            if(mysql_num_rows($checkSubCategoriesResult) > 0){
                $list .=' disabled ';
            }
            $list.='>'.$format.$category['category_name'].'</option>';
            if(mysql_num_rows($checkSubCategoriesResult) > 0){
                $list = self::getSubCategories($type,($level+1),$category['category_id'],$selectedId,$list);
            }
        }
        return $list;
    }

    function getCode($id,$type){
        $code = '';
        if($id > 0){
            $getCategoryCodeQuery = 'select * from categories where category_id = '.$id;
            $getCategoryCodeResult = mysql_query($getCategoryCodeQuery)or die("error getCategoryCode not done ".mysql_error());
            $code=mysql_result($getCategoryCodeResult,"0","category_code");
        }
        $getNewCodeQuery = 'select lpad((ifnull(max(replace(`company_code`,"'.$code.'","")),0)+1),"3","0") as "code" from `companies` where `company_id` > 0 and `category_id` = '.$id.' and `type`= '.$type;
        $getNewCodeResult = mysql_query($getNewCodeQuery)or die("error getNewCodeResult not done ".mysql_error());
        $code.=mysql_result($getNewCodeResult,"0","code");
        return $code;
    }

    function companyForm($type,$id = 0){
        global $lang;
        if($id > 0){
            $getCompanyInfoQuery = 'select * from companies where company_id = '.$id;
            $getCompanyInfoResult = mysql_query($getCompanyInfoQuery)or die("error getCompanyInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getCompanyInfoResult);
            $action = 'update';
        }else{
            $info = array();
            $info['company_name'] = '';
            $info['company_code'] = '';
            $info['category_id'] = '';
            $info['phone'] = '';
            $info['fax'] = '';
            $info['mobile'] = '';
            $info['email'] = '';
            $info['address'] = '';
            $info['balance'] = '';
            $action = 'insert';
        }
        if($type == 1){
            $name = $lang['the_customer'];
        }elseif($type == 2){
            $name = $lang['the_supplier'];
        }
        $form='
        <form method="POST" action="ajax.php?section=companies&action='.$action.'" >
            <div class="form-group">
                <label for="companyName">'.$lang['name'].' '.$name.'</label>
                <input type="text" class="form-control" name="companyName" id="companyName" aria-describedby="userNamelHelp" placeholder="'.$lang['enter'].' '.$lang['name'].' '.$name.'" value="'.$info['company_name'].'">
            </div>
            <div class="row">
                <div class="form-group w-50">
                    <label for="categoryId">'.$lang['category'].' '.$name.'</label>
                    <select class="form-control" id="categoryId" name="categoryId" data-section="companies" data-action="getCode" data-type="'.$type.'" data-target="companyCode">
                        <option value="0">'.$lang['select_category'].'</option>
                        '.self::getSubCategories($type,0,0,$info['category_id'],'').'
                    </select>                
                </div>
                <div class="form-group w-50">
                    <label for="companyCode">'.$lang['code'].' '.$name.'</label>
                    <input type="text" class="form-control" name="companyCode" id="companyCode" aria-describedby="userNamelHelp" placeholder="'.$lang['enter'].' '.$lang['code'].' '.$name.'" value="'.$info['company_code'].'">
                </div>
            </div>
            <div class="row">
                <div class="form-group w-50">
                    <label for="phone">'.$lang['phone_number'].'</label>
                    <input type="number" class="form-control" name="phone" id="phone" aria-describedby="userNamelHelp" placeholder="'.$lang['phone_number_label'].'" value="'.$info['phone'].'">
                </div>
                <div class="form-group w-50">
                    <label for="fax">'.$lang['fax_number'].'</label>
                    <input type="number" class="form-control" name="fax" id="fax" aria-describedby="userNamelHelp" placeholder="'.$lang['fax_number_label'].'" value="'.$info['fax'].'">
                </div>
            </div>
            <div class="row">
                <div class="form-group w-50">
                    <label for="mobile">'.$lang['mobile_number'].'</label>
                    <input type="number" class="form-control" name="mobile" id="mobile" aria-describedby="userNamelHelp" placeholder="'.$lang['mobile_number_label'].'" value="'.$info['mobile'].'">
                </div>            
                <div class="form-group w-50">
                    <label for="email">'.$lang['email'].'</label>
                    <input type="text" class="form-control" name="email" id="email" aria-describedby="userNamelHelp" placeholder="'.$lang['email_label'].'" value="'.$info['email'].'">
                </div>
            </div>
            <div class="form-group">
                <label for="address">'.$lang['address'].'</label>
                <textarea class="form-control" name="address" id="address" placeholder="'.$lang['address_label'].'">'.$info['address'].'</textarea>
                <label for="balance">'.$lang['the_balance'].'</label>
                <input type="number" class="form-control" name="balance" id="balance" aria-describedby="userNamelHelp" placeholder="'.$lang['balance_label'].'" value="'.$info['balance'].'">
            </div>
            <div class="form-group">

            </div>            
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">'.$lang['save'].'</button>
            </div>
            <input type="hidden" name="id" value="'.$id.'">
            <input type="hidden" name="type" value="'.$type.'">
        </form>';
        return $form;

    }
}
?>