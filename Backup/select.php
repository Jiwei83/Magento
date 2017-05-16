<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2017/3/10
 * Time: 11:14
 */
session_start();
$timeout = 1800;
$userStatus = $_SESSION['user'];
if(isset($_SESSION['timeout'])) {
    $duration = time() - (int)$_SESSION['timeout'];
    if(!$userStatus) {
        header("Location: login.php");
        die();
    }
    else if($duration > $timeout) {
        session_destroy();
        session_start();
        $_SESSION['user'] = false;
        header("Location: login.php");
        die();
    }
}
else {
    session_destroy();
    session_start();
    $_SESSION['user'] = false;
    header("Location: login.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>Magento Data Backup</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <h2 class="text-center">Select The Category</h2>
    <div class="row">
        <div class="btn-group btn-group-justified">
            <div class="btn-group">
                <form action="backup.php" method="get">
                    <button id="lpm-search" class="btn btn-danger" type="submit" name="backup" value="bac">
                        Backup
                    </button>
                </form>
            </div>
            <div class="btn-group">
                <form action="compare.php" method="get">
                    <button id="lpc-search" class="btn btn-info" type="submit" name="compare" value="com">
                        New Prodcut or Deleted Product
                    </button>
                </form>
            </div>
            <div class="btn-group">
                <form action="check.php" method="get">
                    <button id="Bintani-search" class="btn btn-warning" type="submit" name="check" value="che">
                        Check Value Change
                    </button>
                </form>
            </div>
            <div class="btn-group">
                <form action="update.php" method="get">
                    <button id="Bintani-search" class="btn btn-success" type="submit" name="check" value="che">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
