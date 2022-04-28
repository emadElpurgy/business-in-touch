<?php 
class users{
    function validate($id = 0){
        global $lang;
        $errors = '';
        if(!isset($_POST['userName']) || trim($_POST['userName']) == ""){
            echo '
            <script>
                alert("'.$lang['error_user_name_is_required'].'");
            </script>';
            exit();
        }
        $checkNameExistsQuery = 'select * from `users` where `user_name` = "'.trim(addslashes($_POST['userName'])).'" ';
        if($id > 0){
            $checkNameExistsQuery.=' and `user_id` <> '.$id;
        }
        $checkNameExistsResult = mysql_query($checkNameExistsQuery)or die("error checkNameExistsQuery not done ".mysql_error());
        if(mysql_num_rows($checkNameExistsResult) > 0){
            echo '
            <script>
                alert("'.$lang['error_user_name_is_exists'].'");
            </script>';
            exit();
        }
        if(!isset($_POST['password']) || trim($_POST['password']) == ""){
            echo '
            <script>
                alert("'.$lang['password_is_required'].'");
            </script>';
            exit();
        }
        if(!isset($_POST['role']) || trim($_POST['role']) == ""){
            echo '
            <script>
                alert("'.$lang['user_role_is_required'].'");
            </script>';
            exit();
        }
        return array('status'=>true,'message'=>'');
    }

    function validateDelete($id){
        return array('status'=>true,'message'=>'');
    }

    function insertUser($userName,$password,$role){
        global $lang;
        $insertQuery = 'insert into `users`(`user_name`,`password`,`role`,`com_id`)values("'.trim(addslashes($userName)).'","'.trim(addslashes($password)).'","'.$role.'","'.$_SESSION['company_id'].'")';
        $insertResult = mysql_query($insertQuery)or die("error insertQuery not done ".mysql_error());
        $id = mysql_insert_id();
        if($role == 1){
            $insertTreasuryQuery = 'insert into `treasuries` (`treasury_name`,`balance`,`user_id`,`com_id`)values("'.$lang['user_treasury_label'].' '.$userName.'",0,'.$id.','.$_SESSION['company_id'].')';
            $insertTreasuryResult = mysql_query($insertTreasuryQuery)or die("error insertTreasuryQuery not done ".mysql_error());
        }
    }
    
    function updateUser($id,$userName,$password,$role){
        $updateQuery = 'update `users` set `user_name` = "'.trim(addslashes($userName)).'" , `password` = "'.trim(addslashes($password)).'" ,`role` = "'.$role.'" where `user_id` = '.$id;
        $updateResult = mysql_query($updateQuery)or die("error updateQuery not done ".mysql_error());
    }

    function deleteUser($id){
        $deleteQuery = 'delete from `users` where `user_id` = '.$id;
        $deleteResult = mysql_query($deleteQuery)or die("error deleteQuery not done ".mysql_error());
    }

    function getUsers($userName = '',$role = 0,$start = 0,$limit = 0){
        global $lang;
        $getAllUsersQuery = '
        select 
            `users`.`user_id`,
            `users`.`user_name`,
            (case when(`users`.`role` = 0) then("'.$lang['user_category_1'].'") else("'.$lang['user_category_2'].'") end) as "role"
        from 
            `users`
        where 
            `users`.`user_id` > 0
            and 
            `com_id` = '.$_SESSION['company_id'];
            if($userName != ''){
                $getAllUsersQuery.=' 
                and 
                `users`.`user_name` like "%'.$userName.'%"';
            }
            if($role > 0){
                $getAllUsersQuery.=' 
                and 
                `users`.`role` = '.$role;
            }
            if($limit > 0){
                $getAllUsersQuery.='
                limit 
                    '.$start.' , '.$limit;
            }
        $getAllUsersResult = mysql_query($getAllUsersQuery)or die("error getAllUsersQuery not done ".mysql_error());
        $html='';
        while($user = mysql_fetch_array($getAllUsersResult)){
            $html.='
            <div class="col-md-4" style="margin-top:15px;">
                <div class="card">
                    <div class="card-block">
                        <h4 class="card-title text-center">'.$user['user_name'].'</h4>
                        <h6 class="card-subtitle text-muted text-right">'.$user['role'].'</h6>
                        <p class="card-text p-y-1"></p>
                        <a href="#" data-section="users" data-action="edit" data-id="'.$user['user_id'].'" class="card-link pull-right">'.$lang['edit'].'</a>
                        <a href="#" data-section="users" data-action="delete" data-id="'.$user['user_id'].'" class="card-link pull-left">'.$lang['delete'].'</a>
                    </div>
                </div>
            </div>';
        }
        return $html;
    }

    function userForm($id = 0){
        global $lang;
        if($id > 0){
            $getInfoQuery = 'select * from users where user_id = '.$id;
            $getInfoResult = mysql_query($getInfoQuery)or die("error getInfoQuery not done ".mysql_error());
            $info = mysql_fetch_array($getInfoResult);
            $action = 'update';
        }else{
            $info = array();
            $info['user_name'] = '';
            $info['password'] = '';
            $info['role'] = '';
            $action = 'insert';
        }
        $form='
        <form method="POST" action="ajax.php?section=users&action='.$action.'">
            <div class="form-group">
                <label for="userName">'.$lang['user_name'].'</label>
                <input type="text" class="form-control" name="userName" id="userName" aria-describedby="userNamelHelp" placeholder="'.$lang['user_name_label'].'" value="'.$info['user_name'].'">
                <small id="userNamelHelp" class="form-text text-muted">'.$lang['user_name_label_help'].'</small>
            </div>
            <div class="form-group">
                <label for="password">'.$lang['password'].'</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="'.$lang['password_label'].'" value="'.$info['password'].'">
            </div>
            <div class="form-group">
                <label for="role">'.$lang['user_category'].'</label>
                <select class="form-control" id="role" name="role">
                    <option value="">'.$lang['select_user_category'].'</option>
                    <option value="0"';
                    if($info['role'] == "0"){
                        $form.=' selected ';
                    }
                    $form.='>'.$lang['user_category_1'].'</option>
                    <option value="1" '; 
                    if($info['role'] == "1"){
                        $form.=' selected ';
                    }
                    $form.='>'.$lang['user_category_2'].'</option>
                </select>                
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">'.$lang['save'].'</button>
            </div>
            <input type="hidden" name="id" value="'.$id.'">
        </form>';
        return $form;
    }
    
    function login($userName,$password){
        $checkQuery = 'select * from `users` where `user_id` > 0 and `user_name` = "'.$userName.'" and `password` = "'.$password.'"';
        $checkResult = mysql_query($checkQuery)or die("error checkQuery not done ".mysql_error());
        if(mysql_num_rows($checkResult) > 0){
            $_SESSION['user_id'] = mysql_result($checkResult,"0","user_id");
            $_SESSION['company_id'] = mysql_result($checkResult,"0","com_id");
            $checkTreasuryQuery = 'select * from `treasuries` where `user_id` = '.$_SESSION['user_id'];
            $checkTreasuryResult = mysql_query($checkTreasuryQuery)or die("error checkTreasuryQuery not done ".mysql_error());
            $_SESSION['treasury_id'] = mysql_result($checkResult,"0","treasury_id");
            return true;
        }else{
            return false;
        }
    }

}
?>