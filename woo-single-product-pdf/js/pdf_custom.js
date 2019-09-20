jQuery(function($){
    $(document).on("click",".generateSinglePDF",function(e) {
        e.preventDefault(); 
        var product_id      = $(this).attr( 'product_id' );
        var checklogo       = $(this).attr( 'with_logo' );
        ajax_call_function( product_id, checklogo );
        return false;
    });
});

// ajax function call back 
function ajax_call_function( product_id, checklogo ) {
    jQuery.ajax({
            type: 'post',
            url: woo_doo_pdf.ajax_url,
            data: { action: 'download_product_details_pdf', product_id: product_id,checklogo : checklogo, secure_download: woo_doo_pdf.ajax_nonce },
            beforeSend: function() {
                    jQuery( "body" ).after( '<div id="woo_pdf_loading_img"></div>' );
                },
            success: function(data){
                //return false;
                var obj = JSON.parse(data);
                // download file by creating a tag
                const a = document.createElement("a");
                          a.style.display = "none";
                          document.body.appendChild(a);
                a.href = obj.url;
                a.setAttribute("download", obj.name);
                a.click();
                // Cleanup
                window.URL.revokeObjectURL(a.href);
                document.body.removeChild(a);

                jQuery('#woo_pdf_loading_img').remove();
                //Ajax call to delete downloaded file
                jQuery.ajax({
                        type: 'post',
                        url: woo_doo_pdf.ajax_url,
                        data: { action: 'delete_downloaded_pdf', filepath: obj.path , secure_delete: woo_doo_pdf.ajax_nonce},
                        success: function(data){
                            //console.log(data);
                        }
                    });
            },
            error: function(data){
                  jQuery('#woo_pdf_loading_img').remove();  
            }
        });
}