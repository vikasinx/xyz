(function(a){
    a.fn.rating_widget = function(p){
        var p = p||{};
        var b = p&&p.starLength?p.starLength:"5";
        var bid = p&&p.customerID?p.customerID:"";
        var ratingAVG = p&&p.ratingAVG?p.ratingAVG:"";
        var c = p&&p.callbackFunctionName?p.callbackFunctionName:"";
        var e = p&&p.initialValue?p.initialValue:"0";
        var d = p&&p.imageDirectory?p.imageDirectory:"img";
        var r = p&&p.inputAttr?p.inputAttr:"";
        var f = e;
        var g = a(this);
        b = parseInt(b);
        init();
        g.next("ul").children("span").hover(function(){
            jQuery(this).parent().children("span").addClass('dashicons-star-empty');
            var a = jQuery(this).parent().children("span").index(jQuery(this));
            jQuery(this).parent().children("span").slice(0,a+1).removeClass('dashicons-star-empty');
            jQuery(this).parent().children("span").slice(0,a+1).removeClass('dashicons-star-half');
            jQuery(this).parent().children("span").slice(0,a+1).addClass('dashicons-star-filled');
        },function(){});
        g.next("ul").children("span").click(function(){
            var a = jQuery(this).parent().children("span").index(jQuery(this));
            var attrVal = (r != '')?g.attr(r):'';
            f = a+1;
            g.val(f);
            if(c != ""){
                eval(c+"("+g.val()+", "+attrVal+")")
            }
             if(((f - Math.floor(f)) === 0)) {
                    jQuery(this).children("span").slice(f,f+1).addClass('dashicons-star-empty');
                    
                    if((ratingAVG - Math.floor(ratingAVG)) !== 0) {
                        console.log('Not clicked');
                        jQuery(this).children("span").slice(f,f+1).removeClass('dashicons-star-empty');
                        jQuery(this).children("span").slice(f,f+1).addClass('dashicons-star-half');             
                    }
                }
        });
        g.next("ul").hover(function(){},function(){
            if(f == ""){
                jQuery(this).children("span").slice(0,f).addClass('dashicons-star-empty');              
            }else{
                f = parseInt(f);
                jQuery(this).children("span").addClass('dashicons-star-empty');
                jQuery(this).children("span").slice(0,f).removeClass('dashicons-star-empty');
                jQuery(this).children("span").slice(0,f).addClass('dashicons-star-filled');
            }
        });
        function init(){
            jQuery('<div style="clear:both;"></div>').insertAfter(g);
            g.css("float","left");
            var a = jQuery("<ul>");
            a.addClass("rating_widget");
            for(var i=1;i<=b;i++){
                a.append('<span class="dashicons dashicons-star-empty"></span>')
            }
            a.insertAfter(g);
            if(e != ""){
                f = e;
                g.val(e);          
                f = parseInt(f);
                if(((ratingAVG - Math.floor(ratingAVG)) !== 0) && (((f - Math.floor(f)) === 0))) 
                { 
                    g.next("ul").children("span").slice(0,f).removeClass('dashicons-star-empty');
                    g.next("ul").children("span").slice(0,f).addClass('dashicons-star-filled');
                    g.next("ul").children("span").slice(f,f+1).removeClass('dashicons-star-empty');
                    g.next("ul").children("span").slice(f,f+1).addClass('dashicons-star-half');
                } else {
                    g.next("ul").children("span").slice(0,f).removeClass('dashicons-star-empty');
                    g.next("ul").children("span").slice(0,f).addClass('dashicons-star-filled');                     
                }
                
            }
        }
    }

})(jQuery);