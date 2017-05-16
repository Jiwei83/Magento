<?php
/**
 * Created by PhpStorm.
 * User: rcmodel
 * Date: 2017/3/2
 * Time: 13:29
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
    $finalResult = array();
    include("info.php");
    error_reporting(E_ERROR | E_PARSE);
    try {
        $dbh = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $newTable = getTableName($dbh, $database, 0);
        $oldTable = getTableName($dbh, $database, 1);
        $resultForNewBackup = compare($dbh, $newTable, $oldTable);
        $resultForOldBackup = compare($dbh, $oldTable, $newTable);
        $i = 0;
        $a = 0;
        foreach($resultForNewBackup as $itemArray1 ) {
            foreach ($itemArray1 as $key1 => $value1) {
                if ($key1 == 'ID') {
                    $key = array_search($value1, array_column($resultForOldBackup, 'ID'));
                    if (is_numeric($key)) {
                        foreach ($resultForNewBackup[$i] as $k1 => $v1) {
                            if ($k1 != "ID") {
                                $finalResult[$a]['ID'] = $value1;
                                $finalResult[$a]['attribute_name'] = $k1;
                                $finalResult[$a]['current_value'] = $v1;

                                foreach ($resultForOldBackup[$key] as $k2 => $v2) {
                                    if ($k2 == $k1) {
                                        $finalResult[$a]['previous_value'] = $v2;
                                    }
                                }
                                $a++;
                            }
                        }
                        $a++;
                    }
                }
            }
            $i++;
        }
    }
    catch(PDOException $e) {
        echo $e->getMessage();
    }
}
else {
    session_destroy();
    session_start();
    $_SESSION['user'] = false;
    header("Location: login.php");
    die();
}

function getTableName($dbh, $database, $order) {
    $stmt = $dbh->prepare("select table_name, create_time 
                            from information_schema.TABLES
                            where table_schema = :tablename
                            order by CREATE_TIME desc
                            limit 1 offset $order");
    $stmt->bindParam(':tablename', $database);

    $stmt->execute();

    // set the resulting array to associative
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $tablename = $result[0];
    return $tablename;
}

function getProductInfo($dbh, $table1, $table2) {
    $sql = "SELECT *  FROM $table1 WHERE ID IN (SELECT ID FROM $table2)order by ID asc";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function array_diff_assoc_recursive($array1, $array2)
{
    foreach($array1 as $key => $value)
    {
        if(is_array($value))
        {
            if(!isset($array2[$key]))
            {
                $difference[$key] = $value;
            }
            elseif(!is_array($array2[$key]))
            {
                $difference[$key] = $value;
            }
            else
            {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if($new_diff != FALSE)
                {
                    $difference[$key] = $new_diff;
                    $difference[$key]['ID'] = $value['ID'];
                }
            }
        }
        elseif(!isset($array2[$key]) || $array2[$key] != $value)
        {
            $difference[$key] = $value;
        }
    }
    return !isset($difference) ? 0 : $difference;
}

function compare($dbh, $newTable, $oldTable) {
    $arrayForNewBackup = getProductInfo($dbh, $newTable, $oldTable);
    $arrayForOldBackup = getProductInfo($dbh, $oldTable, $newTable);
    $result = array_diff_assoc_recursive($arrayForNewBackup, $arrayForOldBackup);
    $a = 0;
    $b = 0;
    foreach ($result as $item) {
        foreach ($item as $key => $value) {
            if ($value != null) {
                $diff[$a][$key] = $value;
            }
        }
        $a++;
    }
    foreach ($diff as $item) {
        if (count($item) > 1) {
            $final_result[$b] = $item;
            $b++;
        }
    }
    return $final_result;
}

function array_find($search_in, $search_for) {
    foreach ($search_in as $key => $element) {
        if ( ($element === $search_for) || (is_array($element) && array_find($element, $search_for))){
            return $key;
        }
    }
    return false;
}

function array_column(array $input, $columnKey, $indexKey = null) {
    $array = array();
    foreach ($input as $value) {
        if ( !array_key_exists($columnKey, $value)) {
            trigger_error("Key \"$columnKey\" does not exist in array");
            return false;
        }
        if (is_null($indexKey)) {
            $array[] = $value[$columnKey];
        }
        else {
            if ( !array_key_exists($indexKey, $value)) {
                trigger_error("Key \"$indexKey\" does not exist in array");
                return false;
            }
            if ( ! is_scalar($value[$indexKey])) {
                trigger_error("Key \"$indexKey\" does not contain scalar value");
                return false;
            }
            $array[$value[$indexKey]] = $value[$columnKey];
        }
    }
    return $array;
}
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>Backup</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <a href="select.php" id="gb" style="float:left; text-decoration:none;color:#000;background-color:#ddd;border:1px solid #ccc;padding:8px;">Go Back</a>
    <a href="#" id="od" style="float:right; text-decoration:none;color:#000;background-color:#ddd;border:1px solid #ccc;padding:8px;">Export</a>
</div>
<h4 align="center">Select Attributes</h4>
<div class="col-md-4">
    <div class="funkyradio">
        <div class="funkyradio-info">
            <input type="checkbox" name="checkbox1" id="checkbox1" value="storeId" rel="Attribute_Name"/>
            <label for="checkbox1">Store ID</label>
        </div>
        <div class="funkyradio-primary">
            <input type="checkbox" name="checkbox2" id="checkbox2" value="name" rel="Attribute_Name"/>
            <label for="checkbox2">Name</label>
        </div>
        <div class="funkyradio-success">
            <input type="checkbox" name="checkbox3" id="checkbox3" value="brand" rel="Attribute_Name"/>
            <label for="checkbox3">brand</label>
        </div>
        <div class="funkyradio-danger">
            <input type="checkbox" name="checkbox4" id="checkbox4" value="part_no" rel="Attribute_Name"/>
            <label for="checkbox4">Part NO.</label>
        </div>
        <div class="funkyradio-warning">
            <input type="checkbox" name="checkbox5" id="checkbox5" value="sku" rel="Attribute_Name"/>
            <label for="checkbox5">SKU</label>
        </div>
        <div class="funkyradio-info">
            <input type="checkbox" name="checkbox6" id="checkbox6" value="shelf_no" rel="Attribute_Name"/>
            <label for="checkbox6">Shelf Number</label>
        </div>
        <div class="funkyradio-default">
            <input type="checkbox" name="checkbox7" id="checkbox7" value="barcode" rel="Attribute_Name"/>
            <label for="checkbox7">Barcode</label>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="funkyradio-default">
        <input type="checkbox" name="checkbox8" id="checkbox8" value="parcel_type" rel="Attribute_Name"/>
        <label for="checkbox8">Parcel Type</label>
    </div>
    <div class="funkyradio-primary">
        <input type="checkbox" name="checkbox9" id="checkbox9" value="warning_stock" rel="Attribute_Name"/>
        <label for="checkbox9">Warning Stock</label>
    </div>
    <div class="funkyradio-success">
        <input type="checkbox" name="checkbox10" id="checkbox10" value="order_qty" rel="Attribute_Name"/>
        <label for="checkbox10">Order QTY</label>
    </div>
    <div class="funkyradio">
        <div class="funkyradio-warning">
            <input type="checkbox" name="checkbox11" id="checkbox11" value="width" rel="Attribute_Name"/>
            <label for="checkbox11">Width</label>
        </div>
        <div class="funkyradio-info">
            <input type="checkbox" name="checkbox12" id="checkbox12" value="height" rel="Attribute_Name"/>
            <label for="checkbox12">Height</label>
        </div>
        <div class="funkyradio-default">
            <input type="checkbox" name="checkbox13" id="checkbox13" value="depth" rel="Attribute_Name"/>
            <label for="checkbox13">Depth</label>
        </div>
        <div class="funkyradio-default">
            <input type="checkbox" name="checkbox14" id="checkbox14" value="wholesale_price1" rel="Attribute_Name"/>
            <label for="checkbox14">Wholesale Price1</label>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="funkyradio-danger">
        <input type="checkbox" name="checkbox15" id="checkbox15" value="weight" rel="Attribute_Name"/>
        <label for="checkbox15">Weight</label>
    </div>
    <div class="funkyradio-primary">
        <input type="checkbox" name="checkbox16" id="checkbox16" value="price" rel="Attribute_Name"/>
        <label for="checkbox16">Price</label>
    </div>
    <div class="funkyradio-success">
        <input type="checkbox" name="checkbox17" id="checkbox17" value="cost" rel="Attribute_Name"/>
        <label for="checkbox17">Cost</label>
    </div>
    <div class="funkyradio-warning">
        <input type="checkbox" name="checkbox18" id="checkbox18" value="rmb_cost" rel="Attribute_Name"/>
        <label for="checkbox18">RMB Cost</label>
    </div>
    <div class="funkyradio-danger">
        <input type="checkbox" name="checkbox19" id="checkbox19" value="mpn" rel="Attribute_Name"/>
        <label for="checkbox19">MPN</label>
    </div>
    <div class="funkyradio-info">
        <input type="checkbox" name="checkbox20" id="checkbox20" value="upc" rel="Attribute_Name"/>
        <label for="checkbox20">UPC</label>
    </div>
    <div class="funkyradio-default">
        <input type="checkbox" name="checkbox21" id="checkbox21" value="Wholesale_price2" rel="Attribute_Name"/>
        <label for="checkbox21">Wholesale Price2</label>
    </div>
</div>
<div class="container">
    <h2 align="center">Changed Value</h2>
    <table id="projectSpreadsheet1" class="table table-striped">
        <thead>
        <tr id="atttribute" class="first">
            <th>ID</th>
            <th>Attribute_Name</th>
            <th>Currunt Value</th>
            <th>Previous Value</th>
        </tr>
        </thead>
        <tbody>
            <?php
                $arr = $finalResult;
                foreach ($arr as $item) {
                    ?>
                    <tr>
                        <td>
                            <?php echo $item["ID"];?>
                        </td>
                        <td class="Attribute_Name" rel="<?php echo $item["attribute_name"];?>">
                            <?php echo $item["attribute_name"];?>
                        </td>
                        <td>
                            <?php echo $item["current_value"];?>
                        </td>
                        <td>
                            <?php echo $item["previous_value"];?>
                        </td>
                    </tr>
                <?php };?>
</tbody>
</table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="js/download1.js"></script>
<script src="js/select.js"></script>
</body>
</html>




