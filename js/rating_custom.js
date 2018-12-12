$(function() {
    var str_count = $('input[name=rating]').val();
    var buyerID = $('input[name="rating"]').attr('buyerID');
    $("#rating_star_"+buyerID).rating_widget({
        starLength: '5',
        initialValue: str_count,
        callbackFunctionName: 'processRating',
        imageDirectory: './wp-content/plugins/wc-author-rating/img/',
        inputAttr: '1',
        buyerID : buyerID     
    });
});

function processRating (val, attr){
    var authorid = jQuery("#authorid").val();
    var points = val;
    var buyerID = $('input[name="rating"]').attr('buyerID');
    var ajaxurl = my_ajax_object.ajax_url;
    jQuery.ajax({ 
         data: {action: 'rate_buyer', authorid:authorid, points:points, bid:buyerID},
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