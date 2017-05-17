<?php
/**
 * Created by PhpStorm.
 * User: majiwei
 * Date: 23/08/2016
 * Time: 9:44 PM
 */
$filename = $_GET['file'];
$filename = "../".$filename;
$file = fopen($filename, "w");
if ($file !== false) {
    ftruncate($file, 0);
    fclose($file);
}

