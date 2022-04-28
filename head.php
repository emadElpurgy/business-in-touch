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
<?php
    include('db.php');
    include('menu.php');
?>