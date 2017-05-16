<?php
/**
 * Created by PhpStorm.
 * User: majiwei
 * Date: 27/08/2016
 * Time: 4:02 PM
 */
session_start();
$timeout = 1800; // Number of seconds until it times out.
$_SESSION['timeout'] = time();
if($_SESSION['user'] == false) {
    require_once '../../app/Mage.php';
    umask(0);
    $app = Mage::app('default');
    $username = $_GET['username'];
    $password = $_GET['password'];
    if(isset($_GET['login'])) {
        Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        $user = Mage::getModel('admin/user')->loadByUsername($username); // user your admin username
        $user_id = $user->getId();
// echo $user_id;
        if(($user->getId())>=1)
        {
            $check = Mage::getModel('admin/user')->authenticate($username, $password);
            if($check)
            {
                $_SESSION['user'] = true;
                header("Location: select.php");
                die();
            }
            else
            {
                echo "<script type='text/javascript'>alert('Username or password is not correct!!!')</script>";
            }
        }
        else
        {
            echo "<script type='text/javascript'>alert('Username or password is not correct!!!')</script>";
        }
    }
}
else {
    header("Location: select.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
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
    <!--Pulling Awesome Font -->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="login.css">

</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-lg-offset-5 col-lg-6">
            <form action="" method="get">
                <div class="form-login">
                    <h2>Welcome back.</h2>
                    <input type="text" id="userName" name="username" class="form-control input-lg chat-input" placeholder="username" />
                    </br>
                    <input type="password" id="userPassword" name="password" class="form-control input-lg chat-input" placeholder="password" />
                    </br>
                    <div class="wrapper">
                        <span class="group-btn">
                            <button type="submit" name="login" class="btn btn-success">login</button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>

