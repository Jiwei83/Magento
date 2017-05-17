<?php
error_reporting(E_ERROR);
session_start();
$timeout = 1800;
$userStatus = $_SESSION['user'];
$supplier = "";
$file = "";
if(isset($_SESSION['timeout'])) {
    $duration = time() - (int)$_SESSION['timeout'];
    if(!$userStatus) {
        header("Location: order.php");
        session_destroy();
        session_start();
        die();
    }
    else if($duration > $timeout) {
        session_destroy();
        session_start();
        $_SESSION['user'] = false;
        header("Location: order.php");
        die();
    }
    $addTemp = $_GET['addTemp'];
    if (empty($addTemp)) {
        $id = $_GET['id'];
        $name = $_GET['name'];
        $sku = $_GET['sku'];
        $qty = $_GET['qty'];
        $cost = $_GET['cost'];
        $part_no = $_GET['partno'];
        $shelf_number = $_GET['shelf_number'];
        $supplier = substr($sku, 0, 3);
        $wholesale1 = $_GET['wholesale1'];
        $wholesale2 = $_GET['wholesale2'];
        $amount = $cost * $qty;
        $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_number.','.$wholesale1.','.$wholesale2.','.$amount;
        if (!empty($name) && !empty($sku) && !empty($qty) && !empty($part_no)) {
            $file = "temp.csv";
            $content = file_get_contents($file);
            if (strpos($content, $sku) == false) {
                $content .= $line."\r\n";
            }
            else {
                echo "<div class=\"alert\">
                <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span>
                Already Added to the list!!!!
            </div>";
            }
            file_put_contents($file, $content);
        }
        else if(!empty($sku)){
            $file = "temp.csv";
        }
    }
    if($addTemp == true) {
        $id = " ";
        $name = $_GET['name'];
        $sku = $_GET['supplier'];
        $qty = $_GET['qty'];
        $cost = " ";
        $part_no = $_GET['partno'];
        $shelf_number = " ";
        $amount = " ";
        $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_number.','.$amount;
        if (!empty($name) && !empty($qty) && !empty($part_no) && !empty($sku)) {
            $file = "temp.csv";
            $content = file_get_contents($file);
            $content .= $line."\r\n";
            file_put_contents($file, $content);
        }
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
<html lang="en" xmlns="http://www.w3.org/1999/html">
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
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<div class="container">
    <form action="temp.csv" method="get">
        <h1>China Temp Order</h1>
        <button name="export" type="submit" class="btn btn-success pull-right">Export Order</button>
    </form>
    <?php
    $fileToDisplay = fopen("temp.csv", "r");
    ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>SKU</th>
            <th>Cost</th>
            <th>Part_No</th>
            <th>QTY</th>
            <th>SN</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($fileToDisplay) {
            $i = 0;
            while(! feof($fileToDisplay)) {
                $arr = fgetcsv($fileToDisplay);
                ?>
                <tr>
                    <td>
                        <?php echo $arr[0];?>
                    </td>
                    <td>
                        <?php echo $arr[1];?>
                    </td>
                    <td>
                        <?php echo $arr[2]; $sku = $arr[2];?>
                    </td>
                    <td>
                        <?php echo $arr[3];?>
                    </td>
                    <td>
                        <?php echo $arr[4];?>
                    </td>
                    <td>
                        <?php echo $arr[5];?>
                    </td>
                    <td>
                        <?php echo $arr[6];?>
                    </td>
                    <td>
                        <?php echo $arr[7];?>
                    </td>
                    <td>
                        <?php if(!empty($arr[0])) {?>
                            <button class="btn btn-warning" onclick="testOrder('<?php echo $i?>', '<?php echo $sku?>')">Delete</button>
                        <?php }?>
                    </td>
                </tr>
                <?php $i++;} fclose($fileToDisplay); }?>
        </tbody>
    </table>
    <button class="btn btn-danger pull-right" onclick="eraseOrder('<?php echo $supplier?>')">Clear</button>
</div>
<?php exit;?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>


