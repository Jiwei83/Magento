<?php
error_reporting(E_ERROR);
/**
 * Created by PhpStorm.
 * User: majiwei
 * Date: 22/08/2016
 * Time: 5:37 PM
 */
session_start();
$timeout = 1800;
$userStatus = $_SESSION['user'];
if(isset($_SESSION['timeout'])) {
    $duration = time() - (int)$_SESSION['timeout'];
    if (!$userStatus) {
        header("Location: order.php");
        die();
    } else if ($duration > $timeout) {
        session_destroy();
        session_start();
        $_SESSION['user'] = false;
        header("Location: order.php");
        die();
    }
    require_once "../../app/Mage.php";
    Mage::app('admin');
    $model = Mage::getModel('catalog/product');
    $arrayForItem = array();
    $selectedItems = array();
    $checked = array();
    $title = $_GET['title'];
    $sku = $_GET['sku'];
    $sku = $sku . "%";
    if (empty($title)) {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('ID')// select all attributes
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('partsnumber')
            ->addAttributeToSelect('qty')
            ->addAttributeToSelect('TypeId')
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            //replace DISABLED to ENABLED for products with status enabled
            )
            ->load();
        foreach ($collection as $product) {
            $qty = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product)->getQty();
            $type = $product->getTypeId();
            if ($qty <= 0 && $type == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $_product = Mage::getModel('catalog/product')->load($product->getId());
                $wholesale = $_product->getData('group_price');
                $arrayForItem[$i]['ID'] = $product->getID();
                $arrayForItem[$i]['name'] = $product->getName(); //get name
                $arrayForItem[$i]['sku'] = $product->getSku(); //get SKU
                $arrayForItem[$i]['cost'] = $product->getCost();
                $arrayForItem[$i]['part_no'] = $product->getData('partsnumber');
                $arrayForItem[$i]['qty'] = $qty;
                foreach ($wholesale as $price) {
                    $arrayForItem[$i]['wholesale_price'] = $price['price'];
                }
                $i++;
            }
        }
    }
    else {
        $title = "%" . $title . "%";
        $i = 0;
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('ID')// select all attributes
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('partsnumber')
            ->addAttributeToSelect('shelf_number')
            ->addAttributeToSelect('TypeId')
            ->addAttributeToSelect('group_price')
            ->addAttributeToSelect('Status')
            ->addAttributeToFilter(array(
            		array('attribute'=> 'sku','like' => "$title"),
            		array('attribute'=> 'name','like' => "$title"),
        	))
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            //replace DISABLED to ENABLED for products with status enabled
            )
            ->load(); // limit number of results returned// set the offset (useful for pagination)
        // we iterate through the list of products to get attribute values
        foreach ($collection as $product) {
            $qty = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product)->getQty();
            $type = $product->getTypeId();
            if ($type == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                $_product = Mage::getModel('catalog/product')->load($product->getId());
                $wholesale = $_product->getData('group_price');
                $arrayForItem[$i]['ID'] = $product->getID();
                $arrayForItem[$i]['name'] = $product->getName(); //get name
                $arrayForItem[$i]['sku'] = $product->getSku(); //get SKU
                $arrayForItem[$i]['cost'] = $product->getCost();
                $arrayForItem[$i]['shelf_number'] = $product->getData('shelf_number');

                $arrayForItem[$i]['part_no'] = $product->getData('partsnumber');
                $a = 1;
                foreach ($wholesale as $price) {
                    $arrayForItem[$i]['wholesale_price'.$a] = $price['price'];
                    $a++;
                }
                $i++;
            }
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
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"
      xmlns="http://www.w3.org/1999/html">
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

    <link rel="stylesheet" href="style/result.css">
</head>
<body>
<div class="table-responsive">
    <h2>Results</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="col-md-6">Name</th>
            <th class="col-md-3">SKU</th>
            <th class="col-md-1">Cost</th>
            <th class="col-md-2">Part_No</th>
            <th class="col-md-1">Wholesale1</th>
            <th class="col-md-1">Wholesale2</th>
            <th class="col-md-1">QTY</th>
            <th class="col-md-1"></th>
            <th class="col-md-1"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 0;
        $j = 0;
        foreach($arrayForItem as $item) {
            $qtyBox[$i] = "filled".$i;
            $input[$i]  = "input-qty".$i;
            ?>
            <tr>
                <td>
                    <?php
                    echo $item['name'];
                    ?>
                </td>
                <td>
                    <?php
                    echo $item['sku'];
                    ?>
                </td>
                <td>
                    <?php
                    echo number_format((float)$item['cost'], 2, '.', '');
                    ?>
                </td>
                <td>
                <?php
                    echo $item['part_no'];
                    $name = $item['name'];
                    $sku = $item['sku'];
                    $cost = number_format((float)$item['cost'], 2, '.', '');
                    $id = $item['ID'];
                    $part_no = $item['part_no'];
                    $shelf_number = $item['shelf_number'];
                ?>
                </td>
                <?php
                    $wholesale1 = number_format((float)$item['wholesale_price1'], 2, '.', '');
                    $wholesale2 = number_format((float)$item['wholesale_price2'], 2, '.', '');
                ?>
                <td>
                    <input class="none-input-border" id="price<?php echo $i;?>" type="text" value="<?php echo $wholesale1;?>" readonly>
                </td>
                <td>
                    <input class="input-border" id="price<?php echo $i;?>" type="text" value="<?php echo $wholesale2;?>" readonly>
                </td>
                <td>
                    <input id="<?php echo $qtyBox[$i]?>" type="text" name="qty" onkeypress="return searchKeyPress1(event, <?php echo $i?>);">
                </td>
                <td>
                    <button id="<?php echo $input[$i]?>" type="button" onclick="createFile('<?php echo $id?>','<?php echo addslashes($name)?>','<?php echo $sku?>','<?php echo $cost?>', '<?php echo $i?>', '<?php echo $part_no?>', '<?php echo $shelf_number?>')">ADD</button>
                </td>
                <td>
                    <button id="<?php echo "a".$input[$i]?>" type="button" 
                            onclick="addFile('<?php echo $id?>','<?php echo addslashes($name)?>',
                                '<?php echo $sku?>','<?php echo $cost?>', '<?php echo $i?>', 
                                '<?php echo $part_no?>', '<?php echo $shelf_number?>', 
                                '<?php echo $wholesale1?>', '<?php echo $wholesale2?>')">TEMP</button>
                </td>
            </tr>
            <?php
            $i++;
            }
            ?>
        </tbody>
    </table>
    <table class="table table-striped">
        <tbody>
            <tr>
                <th>Name</th>
                <th>Part No.</th>
                <th>QTY</th>
                <th>Supplier</th>
            </tr>
            <tr>
                <td><input id="name" type="text"></td>
                <td><input id="partno" type="text"></td>
                <td><input id="qty" type="text"></td>
                <td>
                    <select id="supplier">
                        <option value="IMK">Imake</option>
                        <option value="KEK">Kegking</option>
                        <option value="BTN">Bintani</option>
                        <option value="WQP">Winequip</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-success"
                            onclick="createTempFile(document.getElementById('name').value,
                                                        document.getElementById('partno').value,
                                                        document.getElementById('qty').value,
                                                        document.getElementById('supplier').value)">
                        ADD
                    </button>
                </td>
                <td>
                    <button type="button" class="btn btn-success"
                            onclick="addTempFile(document.getElementById('name').value,
                                                        document.getElementById('partno').value,
                                                        document.getElementById('qty').value,
                                                        document.getElementById('supplier').value)">
                        ADD TO TEMP ORDER
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    function searchKeyPress1(e, id) {
        // look for window.event in case event isn't passed in
        e = e || window.event;
        if (e.keyCode == 13)
        {
            document.getElementById('input-qty'+id).click();
            return false;
        }
        return true;
    }
</script>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>

