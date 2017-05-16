<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2017/2/24
 * Time: 10:02
 */
session_start();
include("info.php");
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
    backup();
    echo "<div class=\"col-lg-4 text-center\">
            <form action=\"select.php\" method=\"get\">
                <button id=\"lpm-search\" class=\"btn btn-danger\" type=\"submit\" name=\"backup\" value=\"bac\">
                    Go Back
                </button>
            </form>
        </div>";
}
else {
    session_destroy();
    session_start();
    $_SESSION['user'] = false;
    header("Location: login.php");
    die();
}

function backup() {
    require_once "../../app/Mage.php";
    Mage::app('admin');
    $model = Mage::getModel('catalog/product');

    $arrayForItem = array();
    $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect('ID') // select all attributes
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('ebay_name')
        ->addAttributeToSelect('brand')
        ->addAttributeToSelect('mpn')
        ->addAttributeToSelect('upc')
        ->addAttributeToSelect('sku')
        ->addAttributeToSelect('shelf_number')
        ->addAttributeToSelect('warning_stock')
        ->addAttributeToSelect('parceltype')
        ->addAttributeToSelect('order_qty')
        ->addAttributeToSelect('width')
        ->addAttributeToSelect('height')
        ->addAttributeToSelect('depth')
        ->addAttributeToSelect('weight')
        ->addAttributeToSelect('price')
        ->addAttributeToSelect('order_qty')
        ->addAttributeToSelect('cost')
        ->addAttributeToSelect('rmb_cost')
        ->addAttributeToSelect('partsnumber')
        ->addAttributeToSelect('barcode')
        ->addAttributeToSelect('websiteId')
        ->load(); // set the offset (useful for pagination)

    $i = 0;
// we iterate through the list of products to get attribute values
    foreach ($collection as $product) {
        $_product = Mage::getModel('catalog/product')->load($product->getId());
        $shelf = $product->getData('shelf_number');
        $wholesale = $_product->getData('group_price');
        $arrayForItem[$i]['ID'] = $product->getId();
        $arrayForItem[$i]['name'] = $product->getName(); //get name
        $arrayForItem[$i]['ebay_name'] = $product->getData('ebay_name');
        $arrayForItem[$i]['brand'] = $product->getData('brand');
        $arrayForItem[$i]['mpn'] = $product->getData('mpn');
        $arrayForItem[$i]['upc'] = $product->getData('upc');
        $arrayForItem[$i]['sku'] = $product->getSku();
        $arrayForItem[$i]['shelf_number'] = $shelf;
        $arrayForItem[$i]['parcel_type'] = $product->getData('parceltype');
        $arrayForItem[$i]['warning_stock'] = $product->getData('warning_stock');
        $arrayForItem[$i]['order_qty'] = $product->getData('order_qty');
        $arrayForItem[$i]['width'] = $product->getData('width');
        $arrayForItem[$i]['height'] = $product->getData('height');
        $arrayForItem[$i]['depth'] = $product->getData('depth');
        $arrayForItem[$i]['weight'] = $product->getData('weight');
        $arrayForItem[$i]['price'] = $product->getData('price');
        $arrayForItem[$i]['cost'] = $product->getData('cost');
        $arrayForItem[$i]['rmb_cost'] = $product->getData('rmb_cost');
        if (empty($wholesale)) {
            $arrayForItem[$i]['wholesale_price1'] = 'NO';
            $arrayForItem[$i]['wholesale_price2'] = 'NO';
        }
        else {
            $a = 1;
            foreach ($wholesale as $price) {
                $arrayForItem[$i]['wholesale_price'.$a] = $price['price'];
                $a++;
            }
            if (empty($arrayForItem[$i]['wholesale_price2'])) {
                $arrayForItem[$i]['wholesale_price2'] = 'NO';
            }
        }
        $websites = $product->getWebsiteIds();
        foreach ($websites as $websiteId) {
            $website = Mage::getModel('core/website')->load($websiteId);
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if (!empty($arrayForItem[$i]['storeId'])) {
                        $arrayForItem[$i]['storeId'] = $arrayForItem[$i]['storeId'] . ", " . $store->getName();
                    }
                    else {
                        $arrayForItem[$i]['storeId'] = $arrayForItem[$i]['storeId'] . $store->getName();
                    }

                }
            }
        }
        $arrayForItem[$i]['part_number'] = $product->getData('partsnumber');
        $arrayForItem[$i]['barcode'] = $product->getData('barcode');
        $i++;
    }

    date_default_timezone_set('Australia/Melbourne');
    $time = date("Y_m_d_H_i");
    try {
        include("info.php");
        $dbh = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

        $sql = "create table backup_$time(ID int not null, name varchar(255), ebay_name varchar(255),
    brand varchar(20), mpn varchar(20), upc varchar(20), sku varchar(60), parcel_type varchar(10),
    shelf_no varchar(60), warning_stock int, order_qty int, width float, depth float, height float,
    weight float, price float, cost float, rmb_cost float, wholesale_price1 varchar(10), wholesale_price2 varchar(10),
    part_no varchar(20), barcode varchar(13), storeId varchar(50), PRIMARY KEY (ID))";
        $dbh->exec($sql);
        $result = true;
        foreach ($arrayForItem as $item) {
            $id = $item['ID'];
            $name = $item['name'];
            $name = addslashes($name);
            $ebay_name = $item['ebay_name'];
            $ebay_name = addslashes($ebay_name);
            $brand = isset($item['brand']) ? $item['brand'] : 'empty';
            $brand = addslashes($brand);
            $mpn = isset($item['mpn']) ? $item['mpn'] : 'empty';
            $upc = isset($item['upc']) ? $item['upc'] : 'empty';
            $sku = $item['sku'];
            $shelf_no = isset($item['shelf_number']) ? $item['shelf_number'] : 'empty';
            $parcel_type = isset($item['parcel_type']) ? $item['parcel_type'] : 'empty';
            $warning_stock = isset($item['warning_stock']) ? $item['warning_stock'] : 'empty';
            $order_qty = isset($item['order_qty']) ? $item['order_qty'] : 'empty';
            $width = isset($item['width']) ? $item['width'] : 'empty';
            $height = isset($item['height']) ? $item['height'] : 'empty';
            $depth = isset($item['depth']) ? $item['depth'] : 'empty';
            $weight = isset($item['weight']) ? $item['weight'] : 'empty';
            $price = $item['price'];
            $cost = isset($item['cost']) ? $item['cost'] : 'empty';
            $rmb_cost = isset($item['rmb_cost']) ? $item['rmb_cost'] : 'empty';
            $wholesale_price1 = isset($item['wholesale_price1']) ? $item['wholesale_price1'] : 'empty';
            $wholesale_price2 = isset($item['wholesale_price2']) ? $item['wholesale_price2'] : 'empty';
            $part_no = isset($item['part_number']) ? $item['part_number'] : 'empty';
            $barcode = $item['barcode'];
            $store = $item['storeId'];
            $statement = $dbh->prepare("insert into backup_$time (ID, name, ebay_name, brand, mpn, upc, sku, parcel_type, shelf_no,
                        warning_stock, order_qty, width, depth, height, weight, price, cost, rmb_cost, wholesale_price1,
                        wholesale_price2, part_no, barcode, storeId) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?)");

            $statement->bindParam(1, $id);
            $statement->bindParam(2, $name);
            $statement->bindParam(3, $ebay_name);
            $statement->bindParam(4, $brand);
            $statement->bindParam(5, $mpn);
            $statement->bindParam(6, $upc);
            $statement->bindParam(7, $sku);
            $statement->bindParam(8, $parcel_type);
            $statement->bindParam(9, $shelf_no);
            $statement->bindParam(10, $warning_stock);
            $statement->bindParam(11, $order_qty);
            $statement->bindParam(12, $width);
            $statement->bindParam(13, $depth);
            $statement->bindParam(14, $height);
            $statement->bindParam(15, $weight);
            $statement->bindParam(16, $price);
            $statement->bindParam(17, $cost);
            $statement->bindParam(18, $rmb_cost);
            $statement->bindParam(19, $wholesale_price1);
            $statement->bindParam(20, $wholesale_price2);
            $statement->bindParam(21, $part_no);
            $statement->bindParam(22, $barcode);
            $statement->bindParam(23, $store);
            $inserted = $statement->execute();
            if (!$inserted) {
                $result = false;
            }
        }
        if ($result) {
            echo "Succeed!!!";
        } 
        else {
            echo "Failed!!!";
        }
        
    }
    catch(PDOException $e) {
        echo $e->getMessage();
    }

}
