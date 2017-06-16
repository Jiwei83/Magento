<?php
error_reporting(E_ERROR);
session_start();
$timeout = 1800;
$userStatus = $_SESSION['user'];
$addOrder = $_GET['addOrder'];
$addTemp = $_GET['addTemp'];
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
    if (empty($addOrder)) {
        $id = $_GET['id'];
        $name = $_GET['name'];
        $sku = $_GET['sku'];
        $qty = $_GET['qty'];
        $cost = $_GET['cost'];
        $part_no = $_GET['partno'];
        $shelf_number = $_GET['shelf_number'];
        $supplier = substr($sku, 0, 3);
        $amount = $cost * $qty;
        $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_number.','.$amount;
        if (!empty($name) && !empty($sku) && !empty($qty) && !empty($part_no)) {
            $file = "order_".$supplier.".csv";
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
            $file = "order_".$supplier.".csv";
        }
    }
    if ($addOrder == true){
        $sku = $_GET['sku'];
        $sku = $sku."%";
        $supplier = substr($sku, 0, 3);
        $time = date("Y-m-d");
        require_once "../../app/Mage.php";
        Mage::app('admin');
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('ID')// select all attributes
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('partsnumber')
            ->addAttributeToSelect('qty')
            ->addAttributeToSelect('shelf_number')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('warning_stock')
            ->addAttributeToSelect('order_qty')
            ->addAttributeToSelect('TypeId')
            ->addAttributeToFilter('sku', array('like' => ("$sku")))
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            //replace DISABLED to ENABLED for products with status enabled
            )
            ->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )
            ->load();
        foreach ($collection as $product) {
            $qty = (int)$product->getQty();
            $warning_stock = $product->getData('warning_stock');
            $order_qty = $product->getData('order_qty');
            $type = $product->getTypeId();
            if ($qty <= $warning_stock && $order_qty != 0 && $type == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $id = $product->getID();
                $name = $product->getName(); //get name
                $name = '"'.$name.'"';
                $part_no = $product->getData('partsnumber');
                $sku = $product->getSku(); //get SKU
                $cost = $product->getCost();
                $price = $product->getPrice();
                $shelf_number = $product->getData('shelf_number');
                $qty = $order_qty;

                $amount = $cost * $qty;
                $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_number.','.$amount;
                $file = "order_".$supplier.".csv";
                $content = file_get_contents($file);
                if (strpos($content, $sku) == false) {
                    $content .= $line."\r\n";
                }
                else {
                    echo "<div class=\"alert\">
                    <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span>
                    ".$name."Already Added to the list!!!!
                </div>";
                }
                file_put_contents($file, $content);
            }
        }
    }
    if ($addTemp == true) {
        $name = $_GET['name'];
        $qty = $_GET['qty'];
        $part_no = $_GET['partno'];
        $supplier = $_GET['supplier'];
        $id = " ";
        $sku = $supplier;
        $cost = " ";
        $shelf_number = " ";
        $amount = " ";
        $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_number.','.$amount;
        if (!empty($name) && !empty($qty) && !empty($part_no)) {
            $file = "order_".$supplier.".csv";
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
    <form action=<?php echo $file;?> method="get">
        <h1>Selected Product</h1>
        <button name="export" type="submit" class="btn btn-success pull-right">Export Order</button>
    </form>
    <?php
        $fileToDisplay = fopen("order_".$supplier.".csv", "r");
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
                            <button class="btn btn-warning" onclick="test('<?php echo $i?>', '<?php echo $sku?>')">Delete</button>
                        <?php }?>
                    </td>
                </tr>
                <?php $i++;} fclose($fileToDisplay); }?>
        </tbody>
    </table>
    <button class="btn btn-danger pull-right" onclick="erase('<?php echo $supplier?>')">Clear</button>
</div>
<?php exit;?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>


