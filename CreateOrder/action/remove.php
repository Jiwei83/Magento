<?php
/**
 * Created by PhpStorm.
 * User: majiwei
 * Date: 24/08/2016
 * Time: 8:47 PM
 */
error_reporting(E_ERROR);
$row = $_GET['row'];
$supplier = $_GET['supplier'];
$file = fopen("../order_".$supplier.".csv", "r");
$i = 0;
while(! feof($file)) {
    $content[$i] = fgetcsv($file);
    $i++;
}
array_splice($content, $row, 1);
fclose($file);
unlink("../order_".$supplier.".csv");
$file1 = fopen("../order_".$supplier.".csv", "w");
foreach($content as $item) {
    fputcsv($file1, $item);
}
fclose($file1);
