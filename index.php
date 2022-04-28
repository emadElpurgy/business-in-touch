<?php
    header("Cache-Control: no-cache, must-revalidate");
    error_reporting(0);
    session_start();
    include("auth.php");
?>
<!DOCTYPE html>
<html dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">        
        <meta name="description" content="Business In-Touch Web Application">        
        <title>Business In-Touch</title>
        <!-- manifest and meta theme color -->
        <link rel="manifest" href="manifest.json">
        <meta name="theme-color" content="#2F3BA2">
		<link rel="icon" type="image/png" href="img/icons/apple-60.png" />
        <!-- Add to home screen for Safari on iOS -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Business In-Touch">
        <link rel="apple-touch-icon" sizes="60x60" href="img/icons/apple-60.png">
        <link rel="apple-touch-icon" sizes="76x76" href="img/icons/apple-76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="img/icons/apple-120.png">
        <link rel="apple-touch-icon" sizes="152x152" href="img/icons/apple-152.png">
        <link rel="apple-touch-icon" sizes="167x167" href="img/icons/apple-167.png">
        <link rel="apple-touch-icon" sizes="180x180" href="img/icons/apple-180.png">                
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Material Design Bootstrap -->
        <link href="css/mdb.min.css" rel="stylesheet">
        <!-- Your custom styles (optional) -->
        <link href="css/style.css" rel="stylesheet">
        <!-- animate css -->
        <link href="css/animate.min.css" rel="stylesheet">

        <link href="css/jquery-ui.min.css" rel="stylesheet">
        <link href="css/table.css" rel="stylesheet">
    </head>
    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <!-- Bootstrap tooltips -->
    <script type="text/javascript" src="js/popper.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <!-- MDB core JavaScript -->
    <script type="text/javascript" src="js/mdb.min.js"></script>
    <!-- jquery aniview-->
    <!-- animate -->
    <script type="text/javascript" src="js/jquery.aniview.js"></script>   
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   
    <script type="text/javascript" src="js/app.js"></script>   
    <body>
        <header>
		    <?php
				include('head.php');
			?>
        </header>
        <!-- new customer form-->
		<main id="mainScreen">
            <div class="container" id="Loader" style="height:100%;border:1px solid green;overflow-y:scroll;">
                <div class="section py-1">
                    <div class="container">
                        <div  class="row hidden-md-up" id="load_data" >
                                <!-- time line -->

                        </div>
                    </div>
                    <div class="process-comm" id="load_data_message">
                        <div class="spinner">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div><!--process-comm end-->
                </div><!--posts-section end-->
                <div class="addBtn"><a href="javascript:;"><img src="img/new.png" width="30px"></a></div>
            </div>
		</main>
		
        <footer class="page-footer unique-color-dark pt-0">
			<?php
				include('foot.php');
			?>
        </footer>
        <div style="display:none" id="hiddenAjax"></div>
        <script>
            $('.anim').AniView({animateThreshold: 100,scrollPollInterval: 0});
        </script>
    </body>
</html>