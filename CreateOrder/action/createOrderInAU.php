<?php
//create session to test the user login or not
session_start();
$userStatus = $_SESSION['user'];
$timeout = 1800;
//is user log out go back to login page
if(isset($_SESSION['timeout'])) {
    $duration = time() - (int)$_SESSION['timeout'];
    if(!$userStatus) {
        header("Location: order.php");
        die();
    }
    else if($duration > $timeout) {
        session_destroy();
        session_start();
        $_SESSION['user'] = false;
        header("Location: order.php");
        die();
    }
    //if user login get the previous page sku to go into specific supplier page
    else if($userStatus){
        $time = date("Y-m-d");
        $sku = $_GET['sku'];
        if ($sku == 'Bundle') {
            $items = getBundleItem();
            $output = fopen("php://output",'w') or die("Can't open php://output");
            header("Content-Type:application/csv");
            header("Content-Disposition:attachment;filename=$time"."_".$sku."_order.csv");
            fputcsv($output, array('id','title','sku', 'shelf', 'QTY'));
            foreach($items as $item) {
                fputcsv($output, $item);
            }
            fclose($output) or die("Can't close php://output");
            exit;
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

//get the bundle products 
function getBundleItem() {
    require_once "../../app/Mage.php";
    Mage::app('admin');
    $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect('ID')// select all attributes
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('sku')
        ->addAttributeToSelect('qty')
        ->addAttributeToSelect('shelf_number')
        ->addAttributeToSelect('warning_stock')
        ->addAttributeToSelect('order_qty')
        ->addFieldToFilter('sku', array('like' => 'BDL-%'))
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
    $i = 0;
    foreach ($collection as $product) {
        $qty = (int)$product->getQty();
        $warning_stock = $product->getData('warning_stock');
        $order_qty = $product->getData('order_qty');
        if ($qty <= $warning_stock && $order_qty != 0) {
            $arrayForItem[$i]['id'] = $product->getID();
            $arrayForItem[$i]['title'] = $product->getName();
            $arrayForItem[$i]['sku'] = $product->getSku(); //get SKU
            $arrayForItem[$i]['shelf'] = $product->getData('shelf_number');
            $arrayForItem[$i]['qty'] = $order_qty;
            $i++;
        }
    }
    return $arrayForItem;
}
?>

<!DOCTYPE html>
<html lang="en">
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
    <div class="row">
        <h2>Search For Homebrew Product</h2>
        <div class="input-group col-md-12">
            <button id="input-add-order" class="btn btn-danger pull-right" type="submit" name="searchZero">
            Add Low Stock Product</button>
        </div>
        <div id="custom-search-input ">
            <div class="input-group col-md-12">
                <input id="input-title" type="text" class="search-query form-control" placeholder="Search" name="title"
                       onkeypress="return searchKeyPress(event);" />
                    <span class="input-group-btn">
                        <input id="sku" value="<?php echo $sku?>" hidden>
                        <button id="input-search" class="btn btn-danger" type="submit" name="search">
                            <span class=" glyphicon glyphicon-search"></span>
                        </button>
                    </span>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading"><strong>Upload Files</strong> <small>Update stock files upload</small></div>
        <div class="panel-body cent">

            <!-- Standar Form -->
            <form id="upload-file" action="" method="post" enctype="multipart/form-data">
                <h4>Select files from your computer</h4>
                <div class="form-inline">
                    <div class="form-group">
                        <input type="file" name="file" id="js-upload-files" multiple>
                    </div>
                    <button id="upload" name="submit" type="submit" class="btn btn-sm btn-primary" id="js-upload-submit">Upload files</button>
                    <div id="message">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container" name="result">
    <p id="result">
        <div class="divContainer">
            <img src="image/loading.gif" id="load" alt="loading..." style="display: none;" />
        </div>
    </p>
</div>
<div class="container">
    <p id="demo">
    </p>
</div>
<div class="container">
    <p id="temp">
    </p>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="js/createOrder.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#demo").load("result.php?sku=<?php echo $sku;?>");
    });
    $(document).ready(function(){
        $("#temp").load("temp.php");
    });
    $("#input-search").click(function(){
        $("#result").empty();
        $("#load").show();
        var sku = $("#sku").val();
        var title = $("#input-title").val();
        if (title == "") {
            alert("Input is empty!!!");
        }
        else {
            $("#result").load("searchResult.php?sku="+sku+"&title="+title, function(responseTxt, statusTxt, xhr){
                if(statusTxt == "success") {
                    $("#load").fadeOut("slow");
                }
            });
        }
    });
    $("#input-add-order").click(function(){
        $("#load").show();
        var sku = $("#sku").val();
        $("#demo").load("result.php?sku="+sku+"&addOrder=true", function(responseTxt, statusTxt, xhr){
            if(statusTxt == "success") {
                $("#load").fadeOut("slow");
            }
        });
    });
    $("#upload-file").on('submit',(function(e) {
        e.preventDefault();
        $("#message").empty();
        var fd = new FormData(this);
        var sku = $("#sku").val();
        var url = "action/upload.php?sku="+sku;
        $.ajax({
            url: url, // Url to which the request is send
            type: "POST",             // Type of request to be send, called as method
            data: fd, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false,        // To send DOMDocument or non processed data file it is set to false
            success: function(data)   // A function to be called if request succeeds
            {
                $("#message").html(data);
                $("#demo").load("result.php?sku=<?php echo $sku;?>");
                $("#upload") .attr("disabled", true);
            }
        });
    }));
</script>
</body>
</html>




























