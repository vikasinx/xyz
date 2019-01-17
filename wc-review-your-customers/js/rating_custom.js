$(function() {
    var str_count = $('input[name=rating]').val();
    var customerID = $('input[name="rating"]').attr('customerID');
    var ratingAVG = $('input[name="rating"]').attr('ratingAVG');
        $("#rating_star_"+customerID).rating_widget({
            starLength: '5',
            initialValue: str_count,
            callbackFunctionName: 'processRating',
            imageDirectory: '/img',
            inputAttr: '1',
            customerID : customerID,
            ratingAVG: ratingAVG
        });
});

function processRating (val, attr){
    jQuery('#Submit').click(function(e){
    e.preventDefault();        
        var authorid = jQuery("#authorid").val();
        var points = val;
        var review_comment = jQuery("#review_comment").val();
        var customerID = $('input[name="rating"]').attr('customerID');
        var ajaxurl = my_ajax_object.ajax_url;
        jQuery.ajax({
             data: {action: 'rate_customer', authorid:authorid, points:points, bid:customerID, review_comment:review_comment },
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
    });
}