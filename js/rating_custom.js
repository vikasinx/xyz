$(function() {
    var str_count = $('#rating_star').val();
    $("#rating_star").spaceo_rating_widget({
        starLength: '5',
        initialValue: str_count,
        callbackFunctionName: 'processRating',
        imageDirectory: './wp-content/plugins/wc-author-rating/img/',
        inputAttr: '1'
    });
});
function processRating (val, attrVal){
    var authorid = jQuery("#authorid").val();
    var points = val;
    var ajaxurl = my_ajax_object.ajax_url;
    jQuery.ajax({ 
         data: {action: 'rate_buyer', authorid:authorid, points:points},
         type: 'post',
         url: ajaxurl,
         success: function(data) {
            if (data == 'ok') {
                alert('You have rated '+val+' star');
                $('#avgrat').text(data.average_rating);
                $('#totalrat').text(data.rating_number);
            }else{
                alert(data);
            }
        }
    });
    return false;
}