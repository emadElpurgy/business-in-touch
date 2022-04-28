<?php 
class products{
    function validate($id = 0){
        global $lang;
        $errors = '';
        if(!isset($_POST['productName']) || trim($_POST['productName']) == ""){
            echo '
            <script>
                alert("'.$lang['error_productName_is_required'].'");
                document.getElementsByName("productName")[0].focus();
            </script>';
            exit();
        }
        $checkproductNameIsExistsQuery = 'select * from `products` where `product_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `product_name` = "'.trim(addslashes($_POST['productName'])).'"';
        if($id > 0){
            $checkproductNameIsExistsQuery.= ' `product_id` <> '.$id;
        }
        $checkproductNameIsExistsResult = mysql_query($checkproductNameIsExistsQuery)or die("error checkproductNameIsExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkproductNameIsExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_productName_is_exists'].'");
                document.getElementsByName("productName")[0].focus();
            </script>';
            exit();
        }

        if(!isset($_POST['categoryId']) || trim($_POST['categoryId']) == ""){
            echo '
            <script>
                alert("'.$lang['error_categoryId_is_required'].'");
                document.getElementsByName("categoryId")[0].focus();
            </script>';
            exit();
        }
        $checkCategoryQuery = 'select * from `categories` where `parent_category_id` = '.$_POST['categoryId'];
        $checkCategoryResult = mysql_query($checkCategoryQuery)or die("error checkCategoryQuery not done ".mysql_error());
        if(mysql_num_rows($checkCategoryResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_categoryId_has_sub_categories'].'");
                document.getElementsByName("categoryId")[0].focus();
            </script>';
            exit();
        }


        if(!isset($_POST['productCode']) || trim($_POST['productCode']) == ""){
            echo '
            <script>
                alert("'.$lang['error_productCode_is_required'].'");
                document.getElementsByName("productCode")[0].focus();
            </script>';
            exit();
        }
        $checkProductCodeIsExistsQuery = 'select * from `products` where `product_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `product_code` = "'.trim(addslashes($_POST['productCode'])).'"';
        if($id > 0){
            $checkProductCodeIsExistsQuery.= ' and `product_id` <> '.$id;
        }
        $checkProductCodeIsExistsResult = mysql_query($checkProductCodeIsExistsQuery)or die("error checkProductCodeIsExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkProductCodeIsExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_productCode_is_exists'].'");
                document.getElementsByName("productCode")[0].focus();
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
        $checkpermitsQuery = 'select * from `permit_products` where  `product_id` = '.$id;
        $checkpermitsResult = mysql_query($checkpermitsQuery)or die("error checkpermitsQuery notr done ".mysql_error());        
        if(mysql_num_rows($checkpermitsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_cannot_delete_product_for_permits'].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function validateUnit($id = 0){
        global $lang;
        $errors = '';
        if(!isset($_POST['unitId']) || $_POST['unitId'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_unitId_is_required'].'");
                document.getElementsByName("unitId")[0].focus();
            </script>';
            exit();
        }
        $getProductUnitsQuery = 'select * from `product_units` where `product_id` = '.$_POST['productId'];
        $getProductUnitsResult = mysql_query($getProductUnitsQuery)or die("error getProductUnitsQuery not done ".mysql_error());
        if(mysql_num_rows($getProductUnitsResult) == 0){
            $_POST['amount'] = "1";
        }elseif(!isset($_POST['amount']) || $_POST['amount'] == "" || $_POST['amount'] == "0"){
            echo '
            <script>
                alert("'.$lang['error_contents_is_required'].'");
                document.getElementsByName("amount")[0].focus();
            </script>';
            exit();
        }
        if($_POST['parentUnitId'] == "0"){
            $getProductUnitsQuery = 'select * from `product_units` where `product_id` = '.$_POST['productId'];
            $getProductUnitsResult = mysql_query($getProductUnitsQuery)or die("error getProductUnitsQuery not done ".mysql_error());
            if(mysql_num_rows($getProductUnitsResult) > 0){
                echo '
                <script>
                    alert("'.$lang['error_parentUnit_is_required'].'");
                    document.getElementsByName("parentUnitId")[0].focus();
                </script>';
                exit();
            }            
        }
        if(!isset($_POST['sellPrice']) || $_POST['sellPrice'] == "0"  || $_POST['sellPrice'] == ""){
            echo '
            <script>
                alert("'.$lang['error_sellPrice_is_required'].'");
                document.getElementsByName("sellPrice")[0].focus();
            </script>';
            exit();
        }        
        if(!isset($_POST['purchasePrice']) || $_POST['purchasePrice'] == "0" || $_POST['purchasePrice'] == ""){
            echo '
            <script>
                alert("'.$lang['error_purchasePrice_is_required'].'");
                document.getElementsByName("purchasePrice")[0].focus();
            </script>';
            exit();
        }        
        return array('status'=>true,'message'=>'');
    }

    function validateDeleteUnit($productId){
        global $lang;
        $checkProductUnitsQuery = 'select * from `permit_products` where `product_id` = "'.$productId.'" and `unit_id` not in(select `unit_id` from `product_units` where `product_id` = "'.$productId.'")';
        $checkProductUnitsResult = mysql_query($checkProductUnitsQuery)or die("error checkProductUnitsQuery not done ".mysql_error());
        if(mysql_num_rows($checkProductUnitsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_unitId_exist_in_permits'].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function insertProduct($productName,$ProductCode,$img,$categoryId,$description){
        $insertQuery = 'insert into `products`(`product_name`,`product_code`,`image`,`category_id`,`description`,`com_id`)values("'.trim(addslashes($productName)).'","'.trim(addslashes($ProductCode)).'","'.trim(addslashes($img)).'",'.$categoryId.',"'.trim(addslashes($description)).'","'.$_SESSION['company_id'].'")';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
    }
    
    function updateProduct($id,$productName,$ProductCode,$img,$categoryId,$description){
        $updateQuery = 'update `products` set `product_name` = "'.trim(addslashes($productName)).'" , `product_code` = "'.trim(addslashes($ProductCode)).'" ,`image` = "'.trim(addslashes($img)).'",`category_id` = '.$categoryId.', `description` = "'.trim(addslashes($description)).'" where `product_id` = '.$id;
        $updateResult = mysql_query($updateQuery)or die("error updateQuery not done ".mysql_error());
    }

    function deleteProduct($id){
        $deleteQuery = 'delete from `products` where `product_id` = '.$id;
        $deleteResult = mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }



    function insertUnit($productId,$unitId,$amount,$parentUnitId,$sellPrice,$purchasePrice,$unitDefault){
        if($parentUnitId > 0){
            $query = 'select * from `product_units` where `product_unit_id` = '.$parentUnitId;
            $result = mysql_query($query)or die("error query not done ".mysq_error());
            $convertor = ($amount * mysql_result($result,"0","convertor"));
        }else{
            $convertor = 1;
            $amount = 1;
        }
        if($unitDefault == 1){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` = 1';
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
            $updateQuery2 = 'update `product_units` set `default` = 2 where `product_id` = "'.$productId.'" and `default` = 3';
            $updateResult2 = mysql_query($updateQuery2)or die("error updateQuery2 not done ".mysql_error());
        }elseif($unitDefault == 2){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` = 2';
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
            $updateQuery2 = 'update `product_units` set `default` = 1 where `product_id` = "'.$productId.'" and `default` = 3';
            $updateResult2 = mysql_query($updateQuery2)or die("error updateQuery2 not done ".mysql_error());
        }elseif($unitDefault == 3){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` in(1,2,3)';
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
        }
        $insertQuery = 'insert into `product_units`(`product_id`,`unit_id`,`parent_unit_id`,`amount`,`purchase_price`,`sell_price`,`convertor`,`default`)values("'.$productId.'","'.$unitId.'","'.$parentUnitId.'","'.($amount+0).'","'.($purchasePrice+0).'","'.($sellPrice+0).'","'.($convertor+0).'","'.($unitDefault+0).'")';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
    }

    function updateUnit($id,$productId,$unitId,$amount,$parentUnitId,$sellPrice,$purchasePrice,$unitDefault){
        if($parentUnitId > 0){
            $query = 'select * from `product_units` where `product_unit_id` = '.$parentUnitId;
            $result = mysql_query($query)or die("error query not done ".mysq_error());
            $convertor = ($amount * mysql_result($result,"0","convertor"));
        }else{
            $convertor = 1;
            $amount = 1;
        }
        if($unitDefault == 1){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` = 1 and `product_unit_id` <> '.$id;
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
            $updateQuery2 = 'update `product_units` set `default` = 2 where `product_id` = "'.$productId.'" and `default` = 3 and `default` = 1 and `product_unit_id` <> '.$id;
            $updateResult2 = mysql_query($updateQuery2)or die("error updateQuery2 not done ".mysql_error());
        }elseif($unitDefault == 2){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` = 2 and `product_unit_id` <> '.$id;
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
            $updateQuery2 = 'update `product_units` set `default` = 1 where `product_id` = "'.$productId.'" and `default` = 3 and `product_unit_id` <> '.$id;
            $updateResult2 = mysql_query($updateQuery2)or die("error updateQuery2 not done ".mysql_error());
        }elseif($unitDefault == 3){
            $updateQuery1 = 'update `product_units` set `default` = 0 where `product_id` = "'.$productId.'" and `default` in(1,2,3) and `product_unit_id` <> '.$id;
            $updateResult1 = mysql_query($updateQuery1)or die("error updateQuery1 not done ".mysql_error());
        }
        $updateQuery = 'update `product_units` set `product_id` = '.$productId.' , `unit_id` = '.$unitId.' , `parent_unit_id` = '.$parentUnitId.' , `purchase_price` = '.($purchasePrice+0).' , `sell_price` = '.($sellPrice+0).' , `convertor` = '.($convertor+0).',`amount` = '.($amount+0).', `default` = '.($unitDefault+0).' where `product_unit_id` = '.$id;
        $updateResult = mysql_query($updateQuery)or die("error updateQuery not done ".mysql_error());
    }

    function deleteUnit($id){
        $deleteQuery = 'delete from `product_units` where `product_unit_id` = '.$id;
        $deleteResult = mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }



    function getProducts($productName = '',$productCode = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllProductsQuery = '
        select 
            `products`.`product_id`,
            `products`.`product_name`,
            `products`.`product_code`,
            `categories`.`category_name`
        from 
            `products`
            inner join `categories` on(`categories`.`category_id` = `products`.`category_id`)
        where 
            `products`.`product_id` > 0
            and 
            `products`.`com_id` = '.$_SESSION['company_id'];
            if($productName != ''){
                $getAllProductsQuery.=' 
                and 
                `products`.`product_name` like "%'.$productName.'%"';
            }
            if($productCode > 0){
                $getAllProductsQuery.=' 
                and 
                `products`.`product_code` = "'.$productCode.'" ';
            }
            if($limit > 0){
                $getAllProductsQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllProductsResult = mysql_query($getAllProductsQuery)or die("error getAllProductsQuery not done ".mysql_error());
        $html='';
        while($product = mysql_fetch_array($getAllProductsResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block text-center" >
                        <h4 class="card-title text-center">'.$product['product_name'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$product['category_name'].'</h6>
                        <p class="card-text p-y-1"></p>
                        <a href="#" data-section="products" data-action="edit" data-id="'.$product['product_id'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="products" data-action="units" data-target="form" data-id="'.$product['product_id'].'" class="card-link pull-center">'.$lang['the_units'].'</a>
                        <a href="#" data-section="products" data-action="delete" data-id="'.$product['product_id'].'" class="card-link pull-left">'.$lang['delete'].'</a>                        
                    </div>
                </div>
            </div>';
        }
        return $html;
    }


    function getSubCategories($level,$id,$selectedId,$list){
        $getSubCategoriesQuery = 'select `category_id`,`category_name` from `categories` where `category_id` > 0 and `type` = 3 and `com_id` = '.$_SESSION['company_id'].' and `parent_category_id` = '.$id;
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
            $checkSubCategoriesQuery = 'select * from `categories` where `com_id` = '.$_SESSION['company_id'].' and parent_category_id = '.$category['category_id'];
            $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
            if(mysql_num_rows($checkSubCategoriesResult) > 0){
                $list .=' disabled ';
            }
            $list.='>'.$format.$category['category_name'].'</option>';
            if(mysql_num_rows($checkSubCategoriesResult) > 0){
                $list = self::getSubCategories(($level+1),$category['category_id'],$selectedId,$list);
            }
        }
        return $list;
    }

    function getCode($id){
        $code = '';
        if($id > 0){
            $getCategoryCodeQuery = 'select * from `categories` where `category_id` = '.$id;
            $getCategoryCodeResult = mysql_query($getCategoryCodeQuery)or die("error getCategoryCode not done ".mysql_error());
            $code=mysql_result($getCategoryCodeResult,"0","category_code");
        }
        $getNewCodeQuery = 'select lpad((ifnull(max(replace(`product_code`,"'.$code.'","")),0)+1),"3","0") as "code" from `products` where `product_id` > 0 and `com_id` = '.$_SESSION['company_id'].' and `category_id` = '.$id;
        $getNewCodeResult = mysql_query($getNewCodeQuery)or die("error getNewCodeResult not done ".mysql_error());
        $code.=mysql_result($getNewCodeResult,"0","code");
        return $code;
    }


    function productForm($id = 0){
        global $lang;
        if($id > 0){
            $getInfoQuery = 'select * from products where product_id = '.$id;
            $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getInfoResult);
            $action = 'update';
        }else{
            $info = array();
            $getCodeQuery = 'select lpad((ifnull(max(`products`.`product_code`),0)+1),"4","0")as "code" from `products` where `product_id` > 0';
            $getCodeResult = mysql_query($getCodeQuery);
            $info['product_name'] = '';
            $info['product_code'] = mysql_result($getCodeResult,"0","code");
            $info['img'] = '';
            $info['category_id'] = '';
            $info['description'] = '';
            $action = 'insert';
        }
        $form='
        <form method="POST" action="ajax.php?section=products&action='.$action.'">
            <div class="form-group">
                <label for="productName">'.$lang['product_name'].'</label>
                <input type="text" class="form-control" name="productName" id="productName" aria-describedby="" placeholder="'.$lang['product_name_label'].'" value="'.$info['product_name'].'">
            </div>
            <div class="form-group">
                <label for="categoryId">'.$lang['the_category'].'</label>
                <select class="form-control" id="categoryId" name="categoryId" data-section="products" data-action="getCode" data-target="productCode">
                    <option value="0">'.$lang['select_category'].'</option>
                    '.self::getSubCategories(0,0,$info['category_id'],'').'
                </select>                
            </div>
            <div class="form-group">
                <label for="ProductCode">'.$lang['product_code'].'</label>
                <input type="text" class="form-control" name="productCode" id="productCode" placeholder="'.$lang['product_code_label'].'" value="'.$info['product_code'].'">
            </div>
            <div class="form-group">
                <label for="description">'.$lang['product_descreption'].'</label>
                <textarea class="form-control" name="description" id="description" placeholder="'.$lang['product_descreption_label'].'">'.$info['description'].'</textarea>
            </div>            
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">'.$lang['save'].'</button>
            </div>
            <input type="hidden" name="id" value="'.$id.'">
        </form>';
        return $form;
    }    


    function productUnits($id,$oldId = 0){
        global $lang;
        $getAllUnitsQuery = 'select * from `units` where `unit_id` > 0 and `com_id` = '.$_SESSION['company_id'];
        if($oldId > 0){
            $getAllUnitsQuery.=' and `unit_id` not in (select `product_units`.`unit_id` from `product_units` where `product_units`.`product_id` = '.$id.' and `product_unit_id` <> '.$oldId.')';
        }else{
            $getAllUnitsQuery.=' and `unit_id` not in (select `product_units`.`unit_id` from `product_units` where `product_units`.`product_id` = '.$id.')';
        }
        $getAllUnitsReslt = mysql_query($getAllUnitsQuery)or die("error getAllUnitsQuery not done ".mysql_error());        
        if($oldId > 0){
            $getInfoQuery = 'select * from `product_units` where `product_unit_id` = '.$oldId;
            $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getInfoResult);
            $action = 'updateUnit';
        }else{
            $info = array();
            $action = 'insertUnit';
        }        
        $getProductUnitsQuery = '
        select 
            `product_units`.`product_unit_id`,
            `units`.`unit_id`,
            `units`.`unit_name`,
            `product_units`.`purchase_price`,
            `product_units`.`sell_price`,
            `units2`.`unit_name` as "parent_unit",
            `product_units`.`amount`
        from 
            `product_units`
            inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`)
            left join `product_units` `product_units2` on (`product_units`.`parent_unit_id` = `product_units2`.`product_unit_id`)
            inner join `units` `units2` on(`units2`.`unit_id` = `product_units2`.`unit_id`)
        where 
            `product_units`.`product_id` = '.$id;
        $getProductUnitsResult = mysql_query($getProductUnitsQuery)or die("error getProductUnitsQuery not done ".mysql_error());
        $form = '
        <form method="POST" action="ajax.php?section=products&action='.$action.'">
            <table width="100%" border="1px" cellpadding="0px" cellspacing="0px">
                <tr>
                    <td style="padding:10px;" align="center" width="35%">'.$lang['the_unit'].'</td>
                    <td style="padding:10px;" align="center" width="35%">'.$lang['contents'].'</td>
                    <td style="padding:10px;" align="center" width="15%">'.$lang['sell_price'].'</td>
                    <td style="padding:10px;" align="center" width="15%">'.$lang['purchase_price'].'</td>
                </tr>';
                while($unit = mysql_fetch_array($getProductUnitsResult)){
                    $form.='
                    <tr>
                        <td align="center"><a href="javascript:;" class="unitLink" data-id="'.$id.'" data-old="'.$unit['product_unit_id'].'">'.$unit['unit_name'].'</a></td>
                        <td align="center">'.$unit['amount'].' '.$unit['parent_unit'].'</td>
                        <td align="center">'.$unit['sell_price'].'</td>
                        <td align="center">'.$unit['purchase_price'].'</td>
                    <tr>';
                }                
                $form.='
                <tr>
                    <td>
                        <select name="unitId" class="form-control" placeholder="Select Unit">
                            <option value="0">'.$lang['select_unit'].'</option>';
                            while($unit = mysql_fetch_array($getAllUnitsReslt)){
                                $form.='
                                <option value="'.$unit['unit_id'].'"';
                                if($unit['unit_id'] == $info['unit_id']){
                                    $form.=' selected ';
                                }
                                $form.='>'.$unit['unit_name'].'</option>';
                            }                        
                            $form.='
                        </select>
                        <select name="unitDefault" class="form-control" placeholder="">
                            <option value="0">'.$lang['select'].'</option>
                            <option value="2" ';
                            if($info['default'] == 2){
                                $form.=' selected ';
                            }
                            $form.='>'.$lang['default_purchase_unit'].'</option>
                            <option value="1"';
                            if($info['default'] == 1){
                                $form.=' selected ';
                            }
                            $form.='>'.$lang['default_sell_unit'].'</option>
                            <option value="3"';
                            if($info['default'] == 3){
                                $form.=' selected ';
                            }
                            $form.='>'.$lang['default_purchase_sell_unit'].'</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" id="amount" name="amount" value="'.$info['amount'].'" class="form-control" placeholder="'.$lang['content_label'].'">
                        <select name="parentUnitId" class="form-control" placeholder="Select Sub Unit">';
                            if(mysql_num_rows($getProductUnitsResult) == 0 || $info['parent_unit_id'] == "0"){
                                $form.='
                                <option value="0"></option>';
                            }
                            mysql_data_seek($getProductUnitsResult,0);
                            while($unit = mysql_fetch_array($getProductUnitsResult)){
                                if($unit['product_unit_id'] == $oldId){
                                    continue;
                                }
                                $form.='<option value="'.$unit['product_unit_id'].'"';
                                if($unit['product_unit_id'] == $info['parent_unit_id']){
                                    $form.=' selected ';
                                }
                                $form.='>'.$unit['unit_name'].'</option>';
                            }
                            $form.='
                        </select>
                    </td>
                    <td><input type="number" name="sellPrice" class="form-control" value="'.$info['sell_price'].'" placeholder="'.$lang['sell_price_label'].'"></td>
                    <td><input type="number" name="purchasePrice" class="form-control" value="'.$info['purchase_price'].'" placeholder="'.$lang['purchase_price_label'].'"></td>
                </tr>
                <tr>
                    <td colspan="4" align="center">
                        <button type="submit" class="btn btn-primary unitsReload" data-id="'.$id.'" data-action="close">'.$lang['save'].'</button>';
                        if($oldId > 0){
                            $form.='
                             <button type="button" class="btn btn-danger unitsReload deleteUnit" data-id="'.$id.'" data-old="'.$oldId.'" data-action="close">'.$lang['save'].'</button>';
                        }
                        $form.='
                    </td>
                </tr>
            </table>
            <input type="hidden" name="productId" value="'.$id.'">
            <input type="hidden" name="oldId" value="'.$oldId.'">
        </form>';        
        return $form;
    }
}