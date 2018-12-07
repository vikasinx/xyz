$(function() {
    $("#rating_star").spaceo_rating_widget({
        starLength: '5',
        initialValue: '',
        callbackFunctionName: 'processRating',
        imageDirectory: './wp-content/plugins/wc-author-rating/img/',
        inputAttr: 'post_id'
    });
});

/*function processRating(val, attrVal){
    $.ajax({
        type: 'POST',
        url: 'rating.php',
        data: 'post_id=1&points='+val,
        dataType: 'json',
        success : function(data) {
            if (data.status == 'ok') {
                alert('You have rated '+val+' to SPACE-O');
                $('#avgrat').text(data.average_rating);
                $('#totalrat').text(data.rating_number);
            }else{
                alert('please after some time.');
            }
        }
    });
}*/

function processRating (val, attrVal){
    var post_id = jQuery("#post_id").val();
    var points = val;
    var ajaxurl = my_ajax_object.ajax_url;
    jQuery.ajax({ 
         data: {action: 'rate_buyer', post_id:post_id, points:points},
         type: 'post',
         url: ajaxurl,
         success: function(data) {
            if (data.status == 'ok') {
                alert('You have rated '+val+' to SPACE-O');
                $('#avgrat').text(data.average_rating);
                $('#totalrat').text(data.rating_number);
            }else{
                alert(data);
            }
        }
    });
    return false;
}