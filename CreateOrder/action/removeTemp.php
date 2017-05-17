<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2016/12/1
 * Time: 15:19
 */
error_reporting(E_ERROR);
$row = $_GET['row'];
$supplier = $_GET['supplier'];
$file = fopen("../temp.csv", "r");
$i = 0;
while(! feof($file)) {
    $content[$i] = fgetcsv($file);
    $i++;
}
array_splice($content, $row, 1);
fclose($file);
unlink("../order_".$supplier.".csv");
$file1 = fopen("../temp.csv", "w");
foreach($content as $item) {
    fputcsv($file1, $item);
}
fclose($file1);