<?php 
class units{
    function validate($id = 0){
        global $lang;
        $errors = '';
        if(!isset($_POST['unitName']) || trim($_POST['unitName']) == ""){
            echo '
            <script>
                alert("'.$lang['error_unitName_is_required'].'");
                document.getElementsByName("unitName")[0].focus();
            </script>';
            exit();
        }
        $checkUnitNameIsExistsQuery = 'select * from `units` where `unit_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `unit_name` = "'.trim(addslashes($_POST['unitName'])).'"';
        if($id > 0){
            $checkUnitNameIsExistsQuery.= ' `unit_id` <> '.$id;
        }
        $checkUnitNameIsExistsResult = mysql_query($checkUnitNameIsExistsQuery)or die("error checkUnitNameIsExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkUnitNameIsExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_unitName_is_exists'].'");
                document.getElementsByName("unitName")[0].focus();
            </script>';
            exit();
        }
        if(!isset($_POST['unitCode']) || trim($_POST['unitCode']) == ""){
            echo '
            <script>
                alert("'.$lang['error_unitCode_is_required'].'");
                document.getElementsByName("unitCode")[0].focus();
            </script>';
            exit();
        }
        $checkUnitCodeIsExistsQuery = 'select * from `units` where `unit_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `unit_code` = "'.trim(addslashes($_POST['uniCode'])).'"';
        if($id > 0){
            $checkUnitCodeIsExistsQuery.= ' and `unit_id` <> '.$id;
        }
        $checkUnitNameIsExistsResult = mysql_query($checkUnitCodeIsExistsQuery)or die("error checkUnitCodeIsExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkUnitNameIsExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_unitCode_is_exists'].'");
                document.getElementsByName("unitCode")[0].focus();
            </script>';
            exit();
        }
        if($errors != ''){
            return array('status'=>false,'message'=>$errors);
        }else{
            return array('status'=>true,'message'=>'');
        }        
    }

    function validateDelete($id){        
        global $lang;
        $checkProductsQuery = 'select * from `product_units` where `product_unit_id` > 0 and  `unit_id` = '.$id;
        $checkProductsResult = mysql_query($checkProductsQuery)or die("error checkProductsQuery notr done ".mysql_error());        
        if(mysql_num_rows($checkProductsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_cannot_delete_unit_for_products'].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function insertUint($unitName,$unitCode){
        $insertQuery = 'insert into `units`(`unit_name`,`unit_code`,`com_id`)values("'.trim(addslashes($unitName)).'","'.trim(addslashes($unitCode)).'","'.$_SESSION['company_id'].'")';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
    }
    
    function updateUnit($id,$unitName,$unitCode){
        $updateQuery = 'update `units` set `unit_name` = "'.trim(addslashes($unitName)).'" , `unit_code` = "'.trim(addslashes($unitCode)).'" where `unit_id` = '.$id;
        $updateResult = mysql_query($updateQuery)or die("error updateQuery not done ".mysql_error());
    }

    function deleteUnit($id){
        $deleteQuery = 'delete from `units` where `unit_id` = '.$id;
        $deleteResult = mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }

    function getUnits($unitName = '',$unitCode = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllUnitsQuery = '
        select 
            `units`.`unit_id`,
            `units`.`unit_name`,
            `units`.`unit_code`
        from 
            `units`
        where 
            `units`.`unit_id` > 0
            and 
            `units`.`com_id` = '.$_SESSION['company_id'];
            if($unitName != ''){
                $getAllUnitsQuery.=' 
                and 
                `units`.`unit_name` like "%'.$unitName.'%"';
            }
            if($unitCode > 0){
                $getAllUnitsQuery.=' 
                and 
                `units`.`unit_code` = "'.$unitCode.'" ';
            }
            if($limit > 0){
                $getAllUnitsQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllUnitsResult = mysql_query($getAllUnitsQuery)or die("error getAllUnitsQuery not done ".mysql_error());
        $html='';
        while($unit = mysql_fetch_array($getAllUnitsResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$unit['unit_name'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$unit['unit_code'].'</h6>
                        <p class="card-text p-y-1"></p>
                        <a href="#" data-section="units" data-action="edit" data-id="'.$unit['unit_id'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="units" data-action="delete" data-id="'.$unit['unit_id'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;
    }


    function unitForm($id = 0){
        global $lang;
        if($id > 0){
            $getInfoQuery = 'select * from `units` where `unit_id` = '.$id;
            $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getInfoResult);
            $action = 'update';
        }else{
            $info = array();
            $getCodeQuery = 'select lpad((ifnull(max(`units`.`unit_code`),0)+1),"2","0")as "code" from `units` where `unit_id` > 0';
            $getCodeResult = mysql_query($getCodeQuery);
            $info['unit_name'] = '';
            $info['unit_code'] = mysql_result($getCodeResult,"0","code");
            $action = 'insert';
        }
        $form='
        <form method="POST" action="ajax.php?section=units&action='.$action.'">
            <div class="form-group">
                <label for="unitName">'.$lang['unit_name'].'</label>
                <input type="text" class="form-control" name="unitName" id="unitName" aria-describedby="userNamelHelp" placeholder="'.$lang['unit_name_label'].'" value="'.$info['unit_name'].'">
            </div>
            <div class="form-group">
                <label for="unitCode">'.$lang['unit_code'].'</label>
                <input type="text" class="form-control" name="unitCode" id="unitCode" placeholder="'.$lang['unit_code_label'].'" value="'.$info['unit_code'].'">
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">'.$lang['save'].'</button>
            </div>
            <input type="hidden" name="id" value="'.$id.'">
        </form>';
        return $form;
    }    

}