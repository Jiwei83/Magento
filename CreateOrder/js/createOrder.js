/**
 * Created by rcmodel on 2016/9/23.
 */
function createFile(id,name,sku,cost,i, part_no, shelf_number) {
    var qty = document.getElementById('filled'+i).value;
    if (qty != "" && qty > 0 && qty < 200) {
        var xhttp;
        xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("demo").innerHTML = xhttp.responseText;
                var e = document.getElementById("demo");
                if (!!e && e.scrollIntoView) {
                    e.scrollIntoView();
                    
                }
            }
        };
        url = encodeURI("result.php?id="+id+"&name="+name+"&sku="+sku+"&qty="+qty+"&cost="+cost+"&partno="+part_no+"&shelf_number="+shelf_number);
        url = url.replace(/#/g, '%23');
        xhttp.open("GET", url, true);
        
        xhttp.send();
    }
    else {
        alert("QTY is EMPTY or NOT IN 1~100");
    }
}
function createTempFile(name, part_no, qty, supplier) {
    if (qty != "" && qty > 0 && qty < 200) {
        var xhttp;
        xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("demo").innerHTML = xhttp.responseText;
                var e = document.getElementById("demo");
                if (!!e && e.scrollIntoView) {
                    e.scrollIntoView();

                }
            }
        };
        url = encodeURI("result.php?addTemp="+true+"&name="+name+"&partno="+part_no+"&qty="+qty+"&supplier="+supplier);
        url = url.replace(/#/g, '%23');
        xhttp.open("GET", url, true);
        xhttp.send();
    }
    else {
        alert("QTY is EMPTY or NOT IN 1~100");
    }
}
function addFile(id,name,sku,cost,i, part_no, shelf_number, wholesale1, wholesale2) {
    var qty = document.getElementById('filled'+i).value;
    if (qty != "" && qty > 0 && qty < 200) {
        var xhttp;
        xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("temp").innerHTML = xhttp.responseText;
                var e = document.getElementById("temp");
                if (!!e && e.scrollIntoView) {
                    e.scrollIntoView();
                }
            }
        };
        xhttp.open("GET", "temp.php?id="+id+"&name="+name+"&sku="+sku+"&qty="+qty+"&cost="+cost+"&partno="+part_no+"&shelf_number="+shelf_number+"&wholesale1="+wholesale1+"&wholesale2="+wholesale2, true);
        xhttp.send();
    }
    else {
        alert("QTY is EMPTY or NOT IN 1~200");
    }
}
function addTempFile(name, partno, qty, supplier) {
    if (qty != "" && qty > 0 && qty < 200) {
        var xhttp;
        xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("temp").innerHTML = xhttp.responseText;
                var e = document.getElementById("temp");
                if (!!e && e.scrollIntoView) {
                    e.scrollIntoView();
                }
            }
        };
        xhttp.open("GET", "temp.php?addTemp=true&name="+name+"&partno="+partno+"&qty="+qty+"&supplier="+supplier, true);
        xhttp.send();
    }
    else {
        alert("QTY is EMPTY or NOT IN 1~200");
    }
}
function search(sku) {
    var title = document.getElementById('input-title').value;
    var xhttp;
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            document.getElementById("result").innerHTML = xhttp.responseText;
        }
    };
    xhttp.open("GET", "searchResult.php?title="+title+"&sku="+sku, true);
    xhttp.send();
}
function searchKeyPress(e) {
    // look for window.event in case event isn't passed in
    e = e || window.event;
    if (e.keyCode == 13)
    {
        document.getElementById('input-search').click();
        return false;
    }
    return true;
}
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

function erase(supplier) {
    var r = confirm("Are you sure you want to delete this file?")
    if(r == true)
    {
        $.ajax({
            url: 'action/delete.php',
            data: {'file' : "order_"+supplier+".csv"},
            success: function (response) {
                //alert("success");
                $("#demo").load("result.php");
                $("html, body").animate({ scrollTop: 0 }, "fast");
            },
            error: function () {
                // do something
            }
        });
    }
}

function test(row, sku) {
    var r = confirm("Are you sure you want to delete this record?");
    var supplier = sku.substring(0, 3);
    if(r == true)
    {
        $.ajax({
            url: 'action/remove.php',
            data: {'row': row, 'supplier': supplier},
            success: function (response) {
                $("#demo").load("result.php?sku="+sku);
                $("html, body").animate({ scrollTop: 0 }, "fast");
            },
            error: function () {
                // do something
            }
        });
    }
}

function testOrder(row, sku) {
    var r = confirm("Are you sure you want to delete this record?");
    var supplier = sku.substring(0, 3);
    if(r == true)
    {
        $.ajax({
            url: 'action/removeTemp.php',
            data: {'row': row, 'supplier': supplier},
            success: function (response) {
                $("#temp").load("temp.php");
                $("html, body").animate({ scrollTop: 0 }, "fast");
            },
            error: function () {
                // do something
            }
        });
    }
}

function eraseOrder(supplier) {
    var r = confirm("Are you sure you want to delete this file?")
    if(r == true)
    {
        $.ajax({
            url: 'action/delete.php',
            data: {'file' : "temp.csv"},
            success: function (response) {
                //alert("success");
                $("#temp").load("temp.php");
                $("html, body").animate({ scrollTop: 0 }, "fast");
            },
            error: function () {
                // do something
            }
        });
    }
}