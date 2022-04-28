<?php
if($_SESSION['user_id'] > 0){

}else{
    header("Location: login.php");
}
?>