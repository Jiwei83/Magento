<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2017/5/12
 * Time: 10:56
 */
session_start();
$userStatus = $_SESSION['user'];

if($userStatus == true) {
    $mageFilename = '../../app/Mage.php';
    require_once $mageFilename;
    include "backup.php";
    Mage::setIsDeveloperMode(true);
    ini_set('display_errors', 1);
    umask(0);
    Mage::app('admin');
    Mage::register('isSecureArea', 1);
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

    set_time_limit(0);
    ini_set('memory_limit','1024M');

    /***************** UTILITY FUNCTIONS ********************/
    function _update($msg, $count, $data){
        $time       = date("Y/m/d h:i:s");
        $productId      = $data[0];
        $attribute      = $data[1];
        $value          = $data[2];
        
        $productId      = preg_replace('/\s+/', '', $productId);
        $attribute      = trim($attribute);
        $value          = trim($value);
        $product = Mage::getModel('catalog/product')->setStoreId(0)->load($productId);
        if ($product != false || $product->getSku()) {
            if ($product->getData($attribute) != null) {
                $product->setData($attribute, $value);
                $product->save();
                $msg .= $time . ': ' . $count.'> Success: Value ' . $value . ' of Attribute (' . $attribute . ') of ID: (' . $productId . ') has been updated. <br />'."\r\n";
            }
            else {
                $msg .= $time . ': ' . $count.'> Error: Attribute ' . $attribute . ' of ID '. $productId . ' does NOT exist. <br />'."\r\n";
            }
        }
        else {
            $msg .= $time . ': ' . $count.'> Error: ID ' . $productId . ' does NOT exist. <br />'."\r\n";
        }
        return $msg;
    }
    
    if (isset($_POST['submit'])) {
        $infoFile = "info.log";
        $time       = date("Y/m/d h:i:s");
        $fh = realpath($_FILES['file']['tmp_name']);
        /***************** UTILITY FUNCTIONS ********************/

        $csv                = new Varien_File_Csv();
        $data               = $csv->getData($fh); //path to csv
        $msg     = '';
        $count   = 1;
        $message = '';
        $flag = true;
        foreach($data as $_data){
            if($flag) { $flag = false; continue; }
            if($_data[0] != 0){
                try{
                    $message .= _update($msg, $count, $_data);
                }catch(Exception $e){
                    $message .=  $time . ': ' .$count .'> Error: while Updating Attribute (' . $_data[1] . ') of ID (' . $_data[0] . ') => '.$e->getMessage().'<br />';
                }
            }
            $count++;
        }
        echo $message;
        $content = file_get_contents($infoFile);
        $content .= $message.PHP_EOL."----------------------------------------------------------------------------------------------------------------------------------------------";
        file_put_contents($infoFile, $content);
        backup();
        exit;
    }
}
else {
    header("Location: login.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>Update Attribute</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!--Pulling Awesome Font -->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading"><strong>Upload Files</strong> <small>Update stock files upload</small></div>
        <div class="panel-body">

            <!-- Standar Form -->
            <h4>Select files from your computer</h4>
            <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="js-upload-form">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="file" name="file" id="js-upload-files" multiple>
                    </div>
                    <button name="submit" type="submit" class="btn btn-sm btn-primary" id="js-upload-submit">Upload files</button>
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

