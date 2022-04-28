<?php 
class categories{
    function validate($id = 0){
        global $lang;
        if(!isset($_POST['categoryName']) || trim($_POST['categoryName']) == ''){
            echo '
            <script>
                alert("'.$lang['category_name_is_requiered'].'");
                document.getElementsByName("categoryName")[0].focus();
            </script>';
            exit();
        }
        $checkCategoryNameExistsQuery = 'select * from `categories` where `category_id` > 0 and `category_name` = "'.trim(addslashes($_POST['categoryName'])).'" and `com_id` = '.$_SESSION['company_id'].' and `categories`.`type` = '.$_POST['type'];
        if($id > 0){
            $checkCategoryNameExistsQuery.=' 
            and 
            `categories`.`category_id` <> '.$id;
        }
        $checkCategoryNameExistsResult = mysql_query($checkCategoryNameExistsQuery)or die("error checkCategoryNameExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkCategoryNameExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_category_name_is_exists'].'");
                document.getElementsByName("categoryName")[0].select();
            </script>';
            exit();    
        }
        if(!isset($_POST['categoryCode']) || trim($_POST['categoryCode']) ==""){
            echo '
            <script>
                alert("'.$lang['category_code_is_requiered'].'");
                document.getElementsByName("categoryCode")[0].focus();
            </script>';
            exit();
        }
        $checkCategoryCodeExistsQuery = 'select * from `categories` where `category_id` > 0 and `category_code` = "'.trim(addslashes($_POST['categoryCode'])).'" and `com_id` = '.$_SESSION['company_id'].' and `type` = '.$_POST['type'];
        if($id > 0){
            $checkCategoryCodeExistsQuery.=' and category_id <> '.$id;
        }
        $checkCategoryCodeExistsResult = mysql_query($checkCategoryCodeExistsQuery)or die("error checkCategoryCodeExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkCategoryCodeExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_category_code_is_exists'].'");
                document.getElementsByName("categoryCode")[0].select();
            </script>';
            exit();
        }
        if($_POST['parentCategory'] > 0){
            $checkForCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `category_id` = '.$_POST['parentCategory'];
            $checkForCompaniesResult = mysql_query($checkForCompaniesQuery)or die("error checkForCompaniesQuery not done ".mysql_error());
            if(mysql_num_rows($checkForCompaniesResult) > 0){
                echo '
                <script>
                    alert("'.$lang['error_parent_category_has_companies_'.$_POST['type']].'");
                    document.getElementsByName("parentCategory")[0].focus();
                </script>';
                exit();
            }
        }
        $errors = '';
        if($errors != ''){
            return array('status'=>false,'message'=>$errors);
        }else{
            return array('status'=>true,'message'=>'');
        }
    }

    function validateDelete($id){        
        global $lang;
        $checkSubCategoriesQuery = 'select * from `categories` where `category_id` > 0 and  `parent_category_id` = '.$id;
        $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery notr done ".mysql_error());
        if(mysql_num_rows($checkSubCategoriesResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_cannot_delete_category_for_sub_categories'].'");
            </script>';
            exit();
        }
        $checkCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `category_id` = '.$id;
        $checkCompaniesResult = mysql_query($checkCompaniesQuery)or die("error checkCompaniesQuery not done ".mysql_error());
        if(mysql_num_rows($checkCompaniesResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_cannot_delete_category_for_companies'].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function insertCategory($type,$categoryName,$categoryCode,$description,$parentCategory){
        $insertQuery = 'insert into `categories` (`category_name`,`category_code`,`description`,`type`,`parent_category_id`,`com_id`)values("'.trim(addslashes($categoryName)).'","'.trim(addslashes($categoryCode)).'","'.trim(addslashes($description)).'",'.$type.',"'.$parentCategory.'","'.$_SESSION['company_id'].'")';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
    }

    function updateCategory($id,$type,$categoryName,$categoryCode,$description,$parentCategory){
        $updateQuery = 'update `categories` set `category_name` = "'.trim(addslashes($categoryName)).'" , `category_code` = "'.trim(addslashes($categoryCode)).'" ,`description` = "'.trim(addslashes($description)).'" , `parent_category_id` = '.$parentCategory.' where `category_id` = '.$id;
        $updateResult = mysql_query($updateQuery)or die("error updateQuery not done ".mysql_error());
    }

    function deleteCategory($id){
        $deleteQuery = 'delete from `categories` where `category_id` = '.$id;
        $deleteResult = mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }

    function getCategories($type,$categoryId = 0,$categoryName = '',$categoryCode = '',$description = '',$start = 0,$limit = 0){
        global $lang;
        $getAllCategoriesQuery = '
        select 
            `categories`.`category_id`,
            `categories`.`type`,
            `categories`.`category_name`,
            `categories`.`description`,
            `categories2`.`category_name` as "parent_category"
        from 
            `categories`
            inner join `categories` `categories2` on (`categories2`.`category_id` = `categories`.`parent_category_id`)
        where 
            `categories`.`category_id` > 0
            and 
            `categories`.`com_id` = '.$_SESSION['company_id'].'
            and 
            `categories`.`type` = '.$type;
            if($categoryId > 0){
                $getAllCategoriesQuery.=' 
                and 
                `categories`.`category_id` = '.$categoryId;
            }
            if($categoryName != ''){
                $getAllCategoriesQuery.='
                and 
                `categories`.`category_name` like "%'.$categoryName.'%"';
            }
            if($categoryCode != ''){
                $getAllCategoriesQuery.='
                and 
                `categories`.`category_code` like "%'.$categoryCode.'%"';
            }
            if($description != ''){
                $getAllCategoriesQuery.='
                and 
                `categories`.`description` like "%'.$description.'%"';
            }
            if($limit > 0){
                $getAllCategoriesQuery.='
                limit 
                    '.$start.' , '.$limit;
            }

        $getAllCategoriesResult = mysql_query($getAllCategoriesQuery)or die("error getAllCategoriesQuery not done ".mysql_error());
        $html='';
        while($category = mysql_fetch_array($getAllCategoriesResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$category['category_name'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$category['parent_category'].'</h6>
                        <p class="card-text p-y-1 text-right">'.$category['description'].'</p>
                        <a href="#" data-section="categories" data-action="edit" data-id="'.$category['category_id'].'" data-type="'.$category['type'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="categories" data-action="delete" data-id="'.$category['category_id'].'" data-type="'.$category['type'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;

    }

    function getSubCategories($type,$level,$id,$selectedId,$list){
        $getSubCategoriesQuery = 'select `category_id`,`category_name` from `categories` where `category_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `type` = '.$type.' and `parent_category_id` = '.$id;
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
            $list.='>'.$format.$category['category_name'].'</option>';
            $checkSubCategoriesQuery = 'select * from `categories` where parent_category_id = '.$category['category_id'];
            $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
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
        $getNewCodeQuery = 'select lpad((ifnull(max(replace(`category_code`,"'.$code.'","")),0)+1),"3","0") as "code" from `categories` where `category_id` > 0 and `parent_category_id` = '.$id.' and `type`= '.$type;
        $getNewCodeResult = mysql_query($getNewCodeQuery)or die("error getNewCodeResult not done ".mysql_error());
        $code.=mysql_result($getNewCodeResult,"0","code");
        return $code;
    }

    function categoryForm($type,$id = 0){
        global $lang;
        if($id > 0){
            $getCategoryInfoQuery = 'select * from `categories` where `category_id` = '.$id;
            $getCategoryInfoResult = mysql_query($getCategoryInfoQuery)or die("error getCategoryInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getCategoryInfoResult);
            $action = 'update';
        }else{
            $getMaxCodeQuery = 'select lpad((ifnull(max(`category_code`),0)+1),"3","0") as "code" from `categories` where `category_id` > 0 and `parent_category_id` = 0 and `com_id` = '.$_SESSION['company_id'].' and `type`= '.$type;
            $getMaxCodeResult = mysql_query($getMaxCodeQuery);
            $info = array();
            $info['category_name'] = '';
            $info['category_code'] = mysql_result($getMaxCodeResult,"0","code");
            $info['description'] = '';
            $info['parent_category_id'] = '';
            $action = 'insert';
        }
        $form='
        <form method="POST" action="ajax.php?section=categories&action='.$action.'">
            <div class="form-group">
                <label for="categoryName">'.$lang['category_name'].'</label>
                <input type="text" class="form-control" name="categoryName" id="categoryName" aria-describedby="userNamelHelp" placeholder="'.$lang['category_name_lable'].'" value="'.$info['category_name'].'">
            </div>
            <div class="form-group">
                <label for="description">'.$lang['category_description'].'</label>
                <textarea class="form-control" name="description" id="description" placeholder="'.$lang['category_description_lable'].'">'.$info['description'].'</textarea>
            </div>
            <div class="form-group">
                <label for="parentCategory">'.$lang['category_of'].'</label>
                <select class="form-control" id="parentCategory" name="parentCategory" data-section="categories" data-action="getCode" data-type="'.$type.'" data-target="categoryCode">
                    <option value="0">'.$lang['main_category'].'</option>
                    '.self::getSubCategories($type,0,0,$info['parent_category_id'],'').'
                </select>                
            </div>
            <div class="form-group">
                <label for="categoryCode">'.$lang['category_code'].'</label>
                <input type="text" class="form-control" name="categoryCode" id="categoryCode" aria-describedby="userNamelHelp" placeholder="'.$lang['category_code_label'].'" value="'.$info['category_code'].'">
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