<?php
    header("Cache-Control: no-cache, must-revalidate");
    error_reporting(0);
    session_start();        
    include('lang/lang_ar.php');
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
            <div id="header">
                <!--Navbar-->
                <nav id="nav" class="navbar navbar-expand-lg navbar-dark  fixed-top scrolling-navbar">
                    <div class="container">
                        <!-- Navbar brand -->
                        <a class="navbar-brand" href="">In-Touch</a>
                        <!-- Collapse button -->
                        <button class="navbar-toggler" type="button" id="mainMenuBtn" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                </nav>
            </div>
            <div id="overlay">
                <div class="container" style="height:100%;">
                    <div class="row align-items-center" style="height:100%;">
                        <div class="col d-flex justify-content-center text-center" >
                            <span id="overlayContent" style="background-color:#EAEAEA;border 1px solid #C0C0C0;padding:20px; border-radius: 25px;box-shadow: 2px 5px #C0C0C0;" class="text-right">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- new customer form-->
		<main id="mainScreen">
            <div class="container" id="Loader" style="height:100%;border:1px solid green;overflow-y:scroll;">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4"><?php echo $lang['system_login_page_head']; ?></h3></div>
                                    <div class="card-body">
                                        <form method="POST" action="ajax.php?section=users&action=login">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="loginEmail" id="inputEmail" type="text" placeholder="<?php echo $lang['user_name_label']; ?>" required/>
                                                <label for="inputEmail"><?php echo $lang['user_name']; ?></label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="loginPassword" id="inputPassword" type="password" placeholder="<?php echo $lang['password_label'];?>" required />
                                                <label for="inputPassword"><?php echo $lang['password'];?></label>
                                            </div>
                                            <div class="text-center">
                                                <button class="btn btn-primary" type="submit"><?php echo $lang['login']; ?></button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="register.html"><?php echo $lang['create_account']; ?></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
