<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2016/10/5
 * Time: 16:42
 */
$sku = $_GET['sku'];
if(isset($_FILES["file"]["type"]))
{
    $validextensions = array("csv");
    $temporary = explode(".", $_FILES["file"]["name"]);
    $file_extension = end($temporary);
    if (($_FILES["file"]["size"] < 100000)//Approx. 100kb files can be uploaded.
        && in_array($file_extension, $validextensions)) {
        if ($_FILES["file"]["error"] > 0)
        {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br/><br/>";
        }
        else
        {
            if (file_exists("upload/" . $_FILES["file"]["name"])) {
                echo $_FILES["file"]["name"] . " <span id='invalid'><b>already exists.</b></span> ";
            }
            else
            {
                $time = time();
                $sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable
                $targetPath = "../upload/".$time."_china_order"; // Target path where file is to be stored
                move_uploaded_file($sourcePath,$targetPath) ; // Moving Uploaded file
//                echo "<span id='success'>Image Uploaded Successfully...!!</span><br/>";
//                echo "<br/><b>File Name:</b> " . $_FILES["file"]["name"] . "<br>";
//                echo "<b>Type:</b> " . $_FILES["file"]["type"] . "<br>";
//                echo "<b>Size:</b> " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
//                echo "<b>Temp file:</b> " . $_FILES["file"]["tmp_name"] . "<br>";
                $file = fopen($targetPath, "r");
                add2Order($file);
                echo "Succeed!!!";
            }
        }
    }
    else
    {
        echo "<span id='invalid'>***Invalid file Size or Type***<span>";
    }
}

function add2Order($file) {
    require_once "../../../app/Mage.php";
    Mage::app('admin');
    while(! feof($file)) {
        $arr = fgetcsv($file);
        $id = $arr[0];
        if ($id != " ") {
            $name = $arr[1];
            $sku = $arr[2];
            $supplier = substr($sku, 0, 3);
            $path = "order_" . $supplier . ".csv";
            $qty = $arr[5];
            $product = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect('partsnumber')
                ->addAttributeToSelect('shelf_number')
                ->addAttributeToSelect('warning_stock')
                ->addAttributeToSelect('order_qty')
                ->addAttributeToFilter('entity_id', $id)
                ->addAttributeToFilter('type_id', 'simple')//select simple product
                ->joinField(
                    'qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )
                ->getFirstItem();
            $cost = $product->getCost();
            $part_no = $product->getData('partsnumber');
            $shelf_no = $product->getData('shelf_number');
            $warning_stock = $product->getData('warning_stock');
            $order_qty = $product->getData('order_qty');
            $stock = $product->getQty();
            if ($stock >= $qty && (($stock - $qty) < $warning_stock) && $order_qty != 0) {
                if (!empty($id) && !empty($name) && !empty($sku) && !empty($qty)) {
                    $content = file_get_contents($path);
                    $array = explode("\r\n", $content);
                    for ($i = 0; $i < sizeof($array); $i++) {
                        if (strpos($array[$i], $sku) !== false) {
                            $product = explode(",", $array[$i]);
                            $oldLine = $product[0] . ',' . $product[1] . ',' . $product[2] . ',' . $product[3] . ',' . $product[4] . ',' . $product[5]
                                . ',' . $product[6] . ',' . $product[7];
                            $product[5] = $product[5] + $qty;

                            $newLine = $product[0] . ',' . $product[1] . ',' . $product[2] . ',' . $product[3] . ',' . $product[4] . ',' . $product[5]
                                . ',' . $product[6] . ',' . $product[7];
                            $content = str_replace($oldLine, $newLine, $content);
                        }
                    }
                    if (strpos($content, $sku) === false) {
                        $qty = $warning_stock + $order_qty + $qty - $stock;
                        $amount = $cost * $qty;
                        $line = $id . ',' . $name . ',' . $sku . ',' . $cost . ',' . $part_no . ',' . $qty . ',' . $shelf_no . ',' . $amount . "\r\n";
                        $content .= $line;
                    }
                    file_put_contents($path, $content);
                }
            } else if ($stock < $qty && $order_qty != 0) {
                if (!empty($id) && !empty($name) && !empty($sku) && !empty($qty)) {
                    $content = file_get_contents($path);
                    $array = explode("\r\n", $content);
                    for ($i = 0; $i < sizeof($array); $i++) {
                        if (strpos($array[$i], $sku) !== false) {
                            $product = explode(",", $array[$i]);
                            $oldLine = $product[0] . ',' . $product[1] . ',' . $product[2] . ',' . $product[3] . ',' . $product[4] . ',' . $product[5]
                                . ',' . $product[6] . ',' . $product[7];
                            $product[5] = $product[5] + $qty;
                            $product[7] = $product[4] * $product[5];

                            $newLine = $product[0] . ',' . $product[1] . ',' . $product[2] . ',' . $product[3] . ',' . $product[4] . ',' . $product[5]
                                . ',' . $product[6] . ',' . $product[7];
                            $content = str_replace($oldLine, $newLine, $content);
                        }
                    }
                    if (strpos($content, $sku) === false) {
                        $qty = $qty + $order_qty;
                        $amount = $cost * $qty;
                        $line = $id . ',' . $name . ',' . $sku . ',' . $cost . ',' . $part_no . ',' . $qty . ',' . $shelf_no . ',' . $amount;
                        $content .= $line . "\r\n";
                    }
                    file_put_contents($path, $content);
                }
            }
        }
        
        else {
            $name = $arr[1];
            $sku = $arr[2];
            $supplier = $sku;
            $path = "order_".$supplier.".csv";
            $part_no = $arr[4];
            $qty = $arr[5];
            $cost = " ";
            $shelf_no = " ";
            $amount = " ";
            $line = $id.','.$name.','.$sku.','.$cost.','.$part_no.','.$qty.','.$shelf_no.','.$amount."\r\n";
            $content = file_get_contents($path);
            $content .= $line;
            file_put_contents($path, $content);
        }
    }
}

function array_find($search_in, $search_for) {
    foreach ($search_in as $key => $element) {
        if ( ($element === $search_for) || (is_array($element) && array_find($element, $search_for))){
            return $key;
        }
    }
    return false;
}

