<?php 
class reports{
    var $ids = array();
    function getAllReports(){
        global $lang;
        $html='
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="customerCredits" data-target="form" data-id="1" class="card-link">'.$lang['customers_credit_report'].'</a></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="customerBalance" data-target="form" data-id="1" class="card-link">'.$lang['customers_balance_report'].'</a></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="customerBalanceDetails" data-target="form" data-id="1" class="card-link">'.$lang['customers_balance_detials_report'].'</a></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="suppliersCredits" data-target="form" data-id="1" class="card-link">'.$lang['suppliers_credit_report'].'</a></h4>
                </div>
            </div>
        <div>
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="supplierBalance" data-target="form" data-id="1" class="card-link">'.$lang['supplier_balance_report'].'</a></h4>
                </div>
            </div>
        </div>        
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="supplierBalanceDetails" data-target="form" data-id="1" class="card-link">'.$lang['supplier_balance_detials_report'].'</a></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="ProductsInStocks" data-target="form" data-id="1" class="card-link">'.$lang['products_in_stocks_report'].'</a></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:15px;">
            <div class="card">
                <div class="card-block text-center" >
                    <h4 class="card-title text-center"><a href="#" data-section="reports" data-action="ProductCard" data-target="form" data-id="1" class="card-link">'.$lang['product_card_report'].'</a></h4>
                </div>
            </div>
        </div>';
        return $html;
    }

    function getSubCategories($level,$id,$type,$list){
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
            $checkSubCategoriesQuery = 'select * from `categories` where `com_id` = '.$_SESSION['company_id'].' and parent_category_id = '.$category['category_id'];
            $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());

            $list.='<option value="'.$category['category_id'].'">'.$format.$category['category_name'].'</option>';
            if(mysql_num_rows($checkSubCategoriesResult) > 0){
                $list = self::getSubCategories(($level+1),$category['category_id'],$type,$list);
            }
        }
        return $list;
    }

   
    function customersCreditForm(){
        global $lang;        
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/companiesCredit.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_category'].'</label>
                    <select class="form-control" id="categoryId" name="categoryId">
                        <option value="0">'.$lang['select_category'].'</option>
                        '.self::getSubCategories(0,0,1,'').'
                    </select>                
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="1">
            </form>
        </div>';
        return $form;
    }

    function customersBalanceForm(){
        global $lang;        
        $getAllCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `type` = 1 and `com_id` = '.$_SESSION['company_id'].' order by `company_name`';
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/company_balance.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_customer'].'</label>
                    <select class="form-control" id="company_id" name="company_id">
                        <option value="0">'.$lang['select_customer'].'</option>';
                        while($company = mysql_fetch_array($getAllCompaniesResult)){
                            $form.='<option value="'.$company['company_id'].'">'.$company['company_name'].'</option>';
                        }
                        $form.='
                    </select>                
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['from_date'].'</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['to_date'].'</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="1">
            </form>
        </div>';
        return $form;
    }

    function customerBalanceDetails(){
        global $lang;        
        $getAllCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `type` = 1 and `com_id` = '.$_SESSION['company_id'].' order by `company_name`';
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/company_balance_2.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_customer'].'</label>
                    <select class="form-control" id="company_id" name="company_id">
                        <option value="0">'.$lang['select_customer'].'</option>';
                        while($company = mysql_fetch_array($getAllCompaniesResult)){
                            $form.='<option value="'.$company['company_id'].'">'.$company['company_name'].'</option>';
                        }
                        $form.='
                    </select>                
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['from_date'].'</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['to_date'].'</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="1">
            </form>
        </div>';
        return $form;        
    }

    function suppliersCreditForm(){
        global $lang;        
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/companiesCredit.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_category'].'</label>
                    <select class="form-control" id="categoryId" name="categoryId">
                        <option value="0">'.$lang['select_category'].'</option>
                        '.self::getSubCategories(0,0,2,'').'
                    </select>                
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="2">
            </form>
        </div>';
        return $form;
    }

    function suppliersBalanceForm(){
        global $lang;        
        $getAllCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `type` = 2 and `com_id` = '.$_SESSION['company_id'].' order by `company_name`';
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/company_balance.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_supplier'].'</label>
                    <select class="form-control" id="company_id" name="company_id">
                        <option value="0">'.$lang['select_supplier'].'</option>';
                        while($company = mysql_fetch_array($getAllCompaniesResult)){
                            $form.='<option value="'.$company['company_id'].'">'.$company['company_name'].'</option>';
                        }
                        $form.='
                    </select>                
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['from_date'].'</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['to_date'].'</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="1">
            </form>
        </div>';
        return $form;
    }
    
    function supplierBalanceDetails(){
        global $lang;        
        $getAllCompaniesQuery = 'select * from `companies` where `company_id` > 0 and `type` = 2 and `com_id` = '.$_SESSION['company_id'].' order by `company_name`';
        $getAllCompaniesResult = mysql_query($getAllCompaniesQuery)or die("error getAllCompaniesQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/company_balance_2.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_supplier'].'</label>
                    <select class="form-control" id="company_id" name="company_id">
                        <option value="0">'.$lang['select_supplier'].'</option>';
                        while($company = mysql_fetch_array($getAllCompaniesResult)){
                            $form.='<option value="'.$company['company_id'].'">'.$company['company_name'].'</option>';
                        }
                        $form.='
                    </select>                
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['from_date'].'</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['to_date'].'</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
                <input type="hidden" name="type" value="1">
            </form>
        </div>';
        return $form;        
    }    

    function productsCreditForm(){
        global $lang;        
        $getAllStocksQuery = 'select * from `stocks` where `stock_id` > 0 and `com_id` = 1';
        $getAllStocksResult = mysql_query($getAllStocksQuery)or die("error getAllStocksQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/productsInStocks.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_category'].'</label>
                    <select class="form-control" id="categoryId" name="categoryId">
                        <option value="0">'.$lang['select_category'].'</option>
                        '.self::getSubCategories(0,0,3,'').'
                    </select>                
                </div>
                <div class="form-group">
                    <label for="stockId">'.$lang['the_stock'].'</label>
                    <select class="form-control" id="stockId" name="stockId">';
                        while($stock = mysql_fetch_array($getAllStocksResult)){
                            $form.='<option value="'.$stock['stock_id'].'">'.$stock['stock_name'].'</option>';
                        }
                        $form.='
                    </select>                
                </div>                
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
            </form>
        </div>';
        return $form;
    }

    function productCardForm(){
        global $lang;        
        $getAllProductsQuery = 'select * from `products` where `product_id` > 0 and `com_id` = 1 order by `product_name`';
        $getAllProductsReslt = mysql_query($getAllProductsQuery)or die("error getAllProductsQuery not done ".mysql_error());
        $form='
        <div style="width:80Vw">
            <form method="POST" action="reports/product_card.php" target="new">
                <div class="form-group">
                    <label for="categoryId">'.$lang['the_category'].'</label>
                    <select class="form-control" id="categoryId" name="categoryId" onchange=getProducts(this)>
                        <option value="0">'.$lang['select_category'].'</option>
                        '.self::getSubCategories(0,0,3,'').'
                    </select>                
                </div>
                <div class="form-group">
                    <label for="productId">'.$lang['the_product'].'</label>
                    <div id="productsList">
                        <select class="form-control" id="productId" name="productId" onchange=getProductUnits(this)>
                            <option value="0">'.$lang['select_product'].'</option>';
                            while($product = mysql_fetch_array($getAllProductsReslt)){
                                $form.='<option value="'.$product['product_id'].'">'.$product['product_name'].'</option>';
                            }
                            $form.='
                        </select>            
                    </div>    
                </div>
                <div class="form-group">
                    <label for="unitId">'.$lang['the_unit'].'</label>
                    <div id="unitsList">
                        <select class="form-control" id="unitId" name="unitId" onchange=getProductUnits(this)>
                            <option value="0">'.$lang['select_unit'].'</option>
                        </select>            
                    </div>    
                </div>                
                <div class="form-group">
                    <label for="productCode">'.$lang['code_barcode_label'].'</label>
                    <input type="number" class="form-control" id="productCode" name="productCode" placeholder="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['from_date'].'</label>
                    <input type="date" class="form-control" name="from_date" id="from_date" aria-describedby="" placeholder="" value="">
                </div>
                <div class="form-group">
                    <label for="orderDate">'.$lang['to_date'].'</label>
                    <input type="date" class="form-control" name="to_date" id="to_date" aria-describedby="" placeholder="" value="">
                </div>                
                <div class="form-group text-center">
                    <a class="btn btn-primary submit">'.$lang['view'].'</a>
                </div>
            </form>
        </div>';
        return $form;
    }


    function getCategoryIds($id){
        global $ids;
        array_push($this->ids,$id);
        $checkSubCategoriesQuery = 'select * from `categories` where `parent_category_id` = '.$id;
        $checkSubCategoriesResult = mysql_query($checkSubCategoriesQuery)or die("error checkSubCategoriesQuery not done ".mysql_error());
        while($cat = mysql_fetch_array($checkSubCategoriesResult)){
            getCategoryIds($cat['category_id']);
        }
    }
    

    function getProducts($id = 0){
        global $lang;
        $getAllProductsQuery = 'select * from `products` where `product_id` > 0 and `com_id` > 0';
        if($id > 0){
            self::getCategoryIds($id);
            $getAllProductsQuery.='  and `category_id` in('.implode(',',$this->ids).')';
        }
        $getAllProductsQuery.=' order by `product_name`';
        $getAllProductsResult = mysql_query($getAllProductsQuery)or die("error getAllProductsQuery not done ".mysql_error());
        $list = '                        
        <select class="form-control" id="productId" name="productId" onchange=getProductUnits(this)>
            <option value="0">'.$lang['select_product'].'</option>';
            while($product = mysql_fetch_array($getAllProductsResult)){
                $list.='<option value="'.$product['product_id'].'">'.$product['product_name'].'</option>';
            }
            $list.='
        </select>';            
        return $list;
    }

    function getProductUnits($productId){
        global $lang;
        $getUnisQuery = 'select `units`.`unit_id`,`units`.`unit_name` from `product_units` inner join `units` on(`units`.`unit_id` = `product_units`.`unit_id`) where `product_units`.`product_id` = '.$productId;
        $getUnisResult = mysql_query($getUnisQuery)or die("error getUnisQuery not done ".mysql_error());
        $list = '                        
        <select class="form-control" id="unitId" name="unitId">
            <option value="0">'.$lang['select_unit'].'</option>';
            while($unit = mysql_fetch_array($getUnisResult)){
                $list.='<option value="'.$unit['unit_id'].'">'.$unit['unit_name'].'</option>';
            }
            $list.='
        </select>';            
        return $list;
    }
}
?>