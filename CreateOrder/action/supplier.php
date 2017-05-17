<?php
session_start();
$timeout = 1800;
$userStatus = $_SESSION['user'];
if(isset($_SESSION['timeout'])) {
    $duration = time() - (int)$_SESSION['timeout'];
    if(!$userStatus) {
        header("Location: order.php");
        die();
    }
    else if($duration > $timeout) {
        session_destroy();
        session_start();
        $_SESSION['user'] = false;
        header("Location: order.php");
        die();
    }
}
else {
    session_destroy();
    session_start();
    $_SESSION['user'] = false;
    header("Location: order.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>Create Order In Australia</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <h2 class="text-center">Select The Supplier</h2>
    <div class="row">
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="imake-search" class="btn btn-danger" type="submit" name="sku" value="IMK">
                    Imake
                </button>
            </form>
        </div>
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="kegking-search" class="btn btn-info" type="submit" name="sku" value="KEK">
                    Kegking
                </button>
            </form>
        </div>
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="Bintani-search" class="btn btn-warning" type="submit" name="sku" value="BTN">
                    Bintani
                </button>
            </form>
        </div>
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="Winequip-search" class="btn btn-success" type="submit" name="sku" value="WQP">
                    Winequip
                </button>
            </form>
        </div>
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="Trj-search" class="btn btn-danger" type="submit" name="sku" value="TRJ">
                    TripleJ
                </button>
            </form>
        </div>
        <div class="col-lg-2 text-center">
            <form action="createOrderInAU.php" method="get">
                <button id="Bundle-search" class="btn btn-info" type="submit" name="sku" value="Bundle">
                    Bundle
                </button>
            </form>
        </div>
   </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>




























