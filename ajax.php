<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
include('db.php');
include('lang/lang_ar.php');
function getRequestHeaders() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}
$headers = getRequestHeaders();
//$headers = apache_request_headers();
//print_r($headers);
if($headers['Companyid'] > 0){
	$_SESSION['company_id'] = $headers['Companyid'];
	$_SESSION['user_id'] = $headers['Userid'];
	//$_SESSION['treasury_id'] = $_GET['treasury_id'];
}
if($_GET['section'] == 'users'){
    include('lib/users_class.php');
    $users = new users();
    if($_GET['action'] == 'login'){
        $result = $users->login($_POST['loginEmail'],$_POST['loginPassword']);
        if($result){
            header("Location: index.php");
        }else{
            header("Location: login.php");
        }
    }

    if($_GET['action'] == 'load'){
        $html = $users->getUsers($_GET['userName'],$role,$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $users->userForm();
    }
    
    if($_GET['action'] == "edit"){        
        echo $users->userForm($_GET['id']);
    }

    if($_GET['action'] == "insert"){
        $validate = $users->validate();
        if($validate['status']){
            $users->insertUser($_POST['userName'],$_POST['password'],$_POST['role']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "update"){
        $validate = $users->validate($_POST['id']);
        if($validate['status']){
            $users->updateUser($_POST['id'],$_POST['userName'],$_POST['password'],$_POST['role']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        $validate = $userts->validateDelete($_GET['id']);
        if($validate['status']){
            $users->deleteUser($_GET['id']);
        }
    }    
}


if($_GET['section'] == 'categories'){
    include('lib/categories_class.php');
    $categories = new categories();
    if($_GET['action'] == 'load'){
        $html = $categories->getCategories($_GET['type'],$_GET['categoryId'],$_GET['categoryName'],$_GET['categoryCode'],$_GET['description'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $categories->categoryForm($_GET['type']);
    }
    
    if($_GET['action'] == "edit"){        
        echo $categories->categoryForm($_GET['type'],$_GET['id']);
    }

    if($_GET['action'] == "insert"){
        $validate = $categories->validate();
        if($validate['status']){
            $categories->insertCategory($_POST['type'],$_POST['categoryName'],$_POST['categoryCode'],$_POST['description'],$_POST['parentCategory']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "update"){
        $validate = $categories->validate($_POST['id']);
        if($validate['status']){
            $categories->updateCategory($_POST['id'],$_POST['type'],$_POST['categoryName'],$_POST['categoryCode'],$_POST['description'],$_POST['parentCategory']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        $validate = $categories->validateDelete($_GET['id']);
        if($validate['status']){
            $categories->deleteCategory($_GET['id']);
        }        
    }
    
    if($_GET['action'] == 'getCode'){
        $code = $categories->getCode($_GET['id'],$_GET['type']);
        echo $code;
    }
}



if($_GET['section'] == 'companies'){
    include('lib/companies_class.php');
    $companies = new companies();
    if($_GET['action'] == 'load'){
        $html = $companies->getCompanies($_GET['type'],$_GET['companyId'],$_GET['companyName'],$_GET['companyCode'],$_GET['categoryId'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $companies->companyForm($_GET['type']);
    }
    
    if($_GET['action'] == "edit"){        
        echo $companies->companyForm($_GET['type'],$_GET['id']);
    }

    if($_GET['action'] == "insert"){
        $validate = $companies->validate();
        if($validate['status']){
            $companies->insertCompany($_POST['type'],$_POST['companyName'],$_POST['companyCode'],$_POST['categoryId'],$_POST['phone'],$_POST['fax'],$_POST['mobile'],$_POST['email'],$_POST['address'],$_POST['balance']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "update"){
        $validate = $companies->validate($_POST['id']);
        if($validate['status']){
            $companies->updateCompany($_POST['id'],$_POST['type'],$_POST['companyName'],$_POST['companyCode'],$_POST['categoryId'],$_POST['phone'],$_POST['fax'],$_POST['mobile'],$_POST['email'],$_POST['address'],$_POST['balance']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        $validate = $companies->validateDelete($_GET['id']);
        if($validate['status']){
            $companies->deleteCompany($_GET['id']);
        }
    }
    
    if($_GET['action'] == 'getCode'){
        $code = $companies->getCode($_GET['id'],$_GET['type']);
        echo $code;
    }

}

if($_GET['section'] == 'units'){
    include('lib/units_class.php');
    $units = new units();
    if($_GET['action'] == 'load'){
        $html = $units->getUnits($_GET['unitName'],$_GET['unitCode'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $units->unitForm();
    }
    
    if($_GET['action'] == "edit"){        
        echo $units->unitForm($_GET['id']);
    }

    if($_GET['action'] == "insert"){
        $validate = $units->validate();
        if($validate['status']){
            $units->insertUint($_POST['unitName'],$_POST['unitCode']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "update"){
        $validate = $units->validate($_POST['id']);
        if($validate['status']){
            $units->updateUnit($_POST['id'],$_POST['unitName'],$_POST['unitCode']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        $validate = $units->validateDelete($_GET['id']);
        if($validate['status']){
            $units->deleteUnit($_GET['id']);
        }
    }    
}



if($_GET['section'] == 'products'){
    include('lib/products_class.php');
    $products = new products();
    if($_GET['action'] == 'load'){
        $html = $products->getProducts($_GET['unitName'],$_GET['unitCode'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $products->productForm();
    }
    
    if($_GET['action'] == "edit"){        
        echo $products->productForm($_GET['id']);
    }

    if($_GET['action'] == "units"){        
        echo $products->productUnits($_GET['id'],$_GET['oldId']);
    }

    if($_GET['action'] == "insert"){
        $validate = $products->validate();
        if($validate['status']){
            $products->insertProduct($_POST['productName'],$_POST['productCode'],$_POST['img'],$_POST['categoryId'],$_POST['description']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "update"){
        $validate = $products->validate($_POST['id']);
        if($validate['status']){
            $products->updateProduct($_POST['id'],$_POST['productName'],$_POST['productCode'],$_POST['img'],$_POST['categoryId'],$_POST['description']);
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        $validate = $products->validateDelete($_GET['id']);
        if($validate['status']){
            $products->deleteProduct($_GET['id']);
        }
    }    

    if($_GET['action'] == 'getCode'){
        $code = $products->getCode($_GET['id']);
        echo $code;
    }

    if($_GET['action'] == "insertUnit"){
        $validate = $products->validateUnit();
        if($validate['status']){
            $products->insertUnit($_POST['productId'],$_POST['unitId'],$_POST['amount'],$_POST['parentUnitId'],$_POST['sellPrice'],$_POST['purchasePrice'],$_POST['unitDefault']);
        }
    }

    if($_GET['action'] == "updateUnit"){
        $validate = $products->validateUnit();    
        if($validate['status']){
            mysql_query('begin');
            $products->updateUnit($_POST['oldId'],$_POST['productId'],$_POST['unitId'],$_POST['amount'],$_POST['parentUnitId'],$_POST['sellPrice'],$_POST['purchasePrice'],$_POST['unitDefault']);
            $validate2 = $products->validateDeleteUnit($_POST['productId']);
            mysql_query('commit');
        }
    }

    if($_GET['action'] == "deleteUnit"){
        mysql_query('begin');
        $products->deleteUnit($_GET['id']);
        $validate2 = $products->validateDeleteUnit($_GET['productId']);        
        echo $products->productUnits($_GET['productId']);        
        mysql_query('commit');
    }
}




if($_GET['section'] == 'orders'){
    include('lib/orders_class.php');
    include('lib/permits_class.php');
    $permits = new permits();
    $orders = new orders();
    if($_GET['action'] == 'load'){
        $html = $orders->getOrders($_GET['type'],$_GET['orderNumber'],$_GET['orderDate'],$_GET['companyId'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $orders->orderForm($_GET['type']);
    }
    
    if($_GET['action'] == "edit"){
        echo $orders->orderForm($_GET['type'],$_GET['id']);
    }    
    if($_GET['action'] == "insert"){
        mysql_query('begin');
        $validate = $orders->validate();
        if($validate['status']){            
            $orders->insertOrder($_POST['type'],$_POST['orderNumber'],$_POST['orderDate'],$_POST['companyId'],$_POST['stockId'],$_POST['productId'],$_POST['unitId'],$_POST['quantity'],$_POST['expiry'],$_POST['price'],$_POST['itemTotal'],$_POST['total'],$_POST['extra'],$_POST['discount'],$_POST['overall'],$_POST['paid'],$_POST['treasuryId']);
            $orders->checkProducts();
            $permits->checkTreasury();
        }else{
            echo $validate['message'];
        }
        mysql_query('commit');
    }
    
    if($_GET['action'] == "update"){
        $validate = $orders->validate($_POST['id']);
        if($validate['status']){
            mysql_query('begin');
            $orders->updateOrder($_POST['type'],$_POST['id'],$_POST['orderNumber'],$_POST['orderDate'],$_POST['companyId'],$_POST['stockId'],$_POST['productId'],$_POST['unitId'],$_POST['quantity'],$_POST['expiry'],$_POST['price'],$_POST['itemTotal'],$_POST['total'],$_POST['extra'],$_POST['discount'],$_POST['overall'],$_POST['paid'],$_POST['treasuryId']);
            $orders->checkProducts();
            $permits->checkTreasury();
            mysql_query('commit');
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        mysql_query('begin');
        $orders->deleteOrder($_GET['type'],$_GET['id']);        
        $orders->checkProducts();
        $permits->checkTreasury();
        mysql_query('commit');
    }
    
    if($_GET['action'] == 'getProduct'){
        $code = $orders->getProduct($_GET['type'],$_GET['code'],$_GET['stockId']);
        echo $code;
    }

    if($_GET['action'] == 'getUnitPrice'){
        $price = $orders->getUnitPrice($_GET['productId'],$_GET['unit'],$_GET['type']);
        echo $price;
    }
}


if($_GET['section'] == 'permits'){
    include('lib/permits_class.php');
    $permits = new permits();
    if($_GET['action'] == 'load'){
        $html = $permits->getPermits($_GET['type'],$_GET['permitNumber'],$_GET['permitDate'],$_GET['companyId'],$_GET['categoryId'],$_GET['start'],$_GET['limit']);
        echo $html;
    }

    if($_GET['action'] == "add"){
        echo $permits->permitForm($_GET['type']);
    }
    
    if($_GET['action'] == "edit"){        
        echo $permits->permitForm($_GET['type'],$_GET['id']);
    }    
    if($_GET['action'] == "insert"){
        mysql_query('begin');
        $validate = $permits->validate();
        if($validate['status']){            
            $permits->insertPermit($_POST['type'],$_POST['permitNumber'],$_POST['permitDate'],$_POST['total'],$_POST['companyId'],$_POST['treasuryId'],$_POST['orderId'],$_POST['categoryId'],$_POST['notes']);
            $permits->checkTreasury();
        }else{
            echo $validate['message'];
        }
        mysql_query('commit');
    }
    
    if($_GET['action'] == "update"){
        $validate = $permits->validate($_POST['id']);
        if($validate['status']){
            mysql_query('begin');
            $permits->updatePermit($_POST['type'],$_POST['id'],$_POST['permitNumber'],$_POST['permitDate'],$_POST['total'],$_POST['companyId'],$_POST['treasuryId'],$_POST['orderId'],$_POST['categoryId'],$_POST['notes']);
            $permits->checkTreasury();
            mysql_query('commit');
        }else{
            echo $validate['message'];
        }
    }
    
    if($_GET['action'] == "delete"){
        mysql_query('begin');
        $permits->deletePermit($_GET['type'],$_GET['id']);        
        $permits->checkTreasury();
        mysql_query('commit');
    }
    
    if($_GET['action'] == 'changeCompany'){
        $credit = $permits->getCompanyCredit($_GET['companyId']);
        echo $credit;
    }

    if($_GET['action'] == "changeTreasury"){
        $credit = $permits->getTreasuryCredit($_GET['treasuryId']);
        echo $credit;
    }

    if($_GET['action']== "changeCategory"){
        $list = $permits->getCompanyList($_GET['categoryId']);
        echo $list;
    }

}

if($_GET['section'] == "reports"){
    include('lib/reports_class.php');
    $reports = new reports();
    if($_GET['action'] == "load"){
        $html= $reports->getAllReports();
        echo $html;
    }
    if($_GET['action'] == "customerCredits"){
        $html = $reports->customersCreditForm();
        echo $html;
    }
    if($_GET['action'] == "suppliersCredits"){
        $html = $reports->suppliersCreditForm();
        echo $html;
    }

    if($_GET['action'] == "customerBalance"){
        $html = $reports->customersBalanceForm();
        echo $html;
    }

    if($_GET['action'] == "supplierBalance"){
        $html = $reports->suppliersBalanceForm();
        echo $html;
    }

    if($_GET['action'] == "customerBalanceDetails"){
        $html = $reports->customerBalanceDetails();
        echo $html;
    }

    if($_GET['action'] == "supplierBalanceDetails"){
        $html = $reports->supplierBalanceDetails();
        echo $html;
    }

    if($_GET['action'] == 'ProductsInStocks'){
        $html = $reports->productsCreditForm();
        echo $html;
    }

    if($_GET['action'] == 'ProductCard'){
        $html = $reports->productCardForm();
        echo $html;
    }

    if($_GET['action'] == 'getProducts'){
        $list = $reports->getProducts($_GET['categoryId']);
        echo $list;
    }

    if($_GET['action'] == 'getProductUnits'){
        $list = $reports->getProductUnits($_GET['productId']);
        echo $list;
    }

}



if($_GET['section'] == "main"){
    if($_GET['action'] == 'init'){
        $getMainSectionsQuery = 'select * from sections where section_id > 0 and section_id not in(select main_section_id from sections)';
        $getMainSectionsResult = mysql_query($getMainSectionsQuery)or die("error getMainSectionsQuery not done ".mysql_error());
        $html='';
        while($section = mysql_fetch_array($getMainSectionsResult)){
            $data = '';
            if($section['data'] != ""){
                $dataAttr = explode("&",$section['data']);
                foreach($dataAttr as $attr){
                    $attrArray = explode(":",$attr);
                    $data.=' data-'.$attrArray[0].'="'.$attrArray[1].'" ';
                }
            }else{
                $data = '';
            }
            $html.='
            <div class="col d-flex justify-content-center text-center">                        
                <div class="container menuItem" data-section-name="'.$section['section_url'].'" '.$data.'>
                    <div class="row">
                        <div class="col">
                            <img src="'.$section['icon'].'" width="50px">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            '.$section['section_name'].'
                        </div>
                    </div>
                </div>
            </div>';
        }
        echo $html;
    }
}
?>