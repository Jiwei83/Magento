/**
 * Created by rcmodel on 2017/5/11.
 */
$("input:checkbox").click(function () {
    var showAll = true;
    $('tr').not('.first').hide();
    $('input[type=checkbox]').each(function () {
        if ($(this)[0].checked) {
            showAll = false;
            var status = $(this).attr('rel');
            var value = $(this).val();
            $('td.' + status + '[rel="' + value + '"]').parent('tr').show();
        }
    });
    if(showAll){
        $('tr').show();
    }
});