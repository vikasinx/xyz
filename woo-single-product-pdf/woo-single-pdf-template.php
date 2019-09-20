<?php
 if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    //German Number Format
    function germanNumberFormat($value){
        $vv = explode(' ', $value);
        $numberValue = number_format($vv[0], 0, ',', '.');
        return $numberValue." ".$vv[1];
    }

    $product = wc_get_product( $product_id );
    $product_meta         = get_post_meta( $product_id );
    
    //product categories
    $terms = get_the_terms ( $product_id, 'product_tag' );

    $cat_name = [];
    foreach ( $terms as $term ) { $cat_name[] = $term->name; }

    $attributes = unserialize($product_meta['_product_attributes'][0]);
   // wsm_debug($attributes);

    $widthLabel             = wc_attribute_label( 'pa_breite', $product );
    $width                  = germanNumberFormat( $product->get_attribute( 'pa_breite' ) );
    $heightLabel            = wc_attribute_label( 'pa_hoehe', $product );
    $height                 = germanNumberFormat( $product->get_attribute( 'pa_hoehe' ) );
    $clearHeightLabel       = wc_attribute_label( 'pa_lichte-hoehe', $product );
    $clearHeight            = germanNumberFormat( $product->get_attribute( 'pa_lichte-hoehe' ) );
    $depthLabel             = wc_attribute_label( 'pa_tiefe', $product );
    $depth                  = germanNumberFormat( $product->get_attribute( 'pa_tiefe' ) );
    
    //unset width
    if( isset( $attributes['pa_breite'] ) ){  unset( $attributes['pa_breite'] ); }
    //unset height
    if( isset( $attributes['pa_hoehe'] ) ){  unset( $attributes['pa_hoehe'] ); }
    //unset clear height
    if( isset( $attributes['pa_lichte-hoehe'] ) ){  unset( $attributes['pa_lichte-hoehe'] ); }
    //unset depth
    if( isset( $attributes['pa_tiefe'] ) ){  unset( $attributes['pa_tiefe'] ); }

    $att = array();
    $i = 0;
    foreach ($attributes as $a) {
        if(!empty($product->get_attribute( $a['name'] ))) :
            $att[$i]['name']    = wc_attribute_label( $a['name'], $product )." ";
            $att[$i]['value']   = $product->get_attribute( $a['name'] )."</br>";
            $i++;
        endif;
    }
    
    $pImage = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

    $logo                = plugin_dir_url(__FILE__) . 'images/logo_wsm_pdf.jpg';
    $plusIcon            = plugin_dir_url(__FILE__) . 'images/plus.png';
    $title               = get_the_title( $product_id );
    $catName             = implode( ' | ', $cat_name );
    $shortDesc           = get_post($product_id)->post_excerpt;
    $atrticalNo          = $product_meta['_sku'][0];
    $productURL          = get_permalink( $product_id ) ;
    $productDesc         = get_post($product_id)->post_content;
    $productImage        = $image[0];

    $lang                = get_bloginfo('language');
    $lang                = preg_replace('/-.*/', '', $lang);
    $regularPrice        = $product_meta['_regular_price'][0];
    $sku                 = $product_meta['_sku'][0];


$html = '
 <table width="100%" cellpadding="0" align="center" cellspacing="0" bgcolor="#ffffff" style="max-width: 705px;"
        class="wrapper">
        <tr>
            <td style="padding: 0px 30px 10px">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td
                                        style="font-size: 18px ;padding-top:15px; padding-bottom: 0px; font-family: myridpro;  color:#00823e; line-height:20px"> '.$catName.'</td>
                                </tr>
                                <tr>
                                    <td
                                        style="font-size: 30px ; font-family: myridpro;  color:#4c4c4e; line-height:35px">'.$title.'</td>
                                </tr>
                            </table>
                        </td>';
                    if( $checklogo != 'no' ){
               $html .= '<td align="right">
                            <img src="'.$logo.'" alt="" width="210">
                        </td>';
                    }
            $html .= '</tr>
                </table>
            </td>
        </tr>
        <tr>
            <td
                style="padding: 10px 30px 0px; font-size: 13px ; font-family: myridpro; font-weight: 700; color:#231f20; line-height:20px">
                <b>Elegant, versatile, flexible and with type statics.</b> </td>
        </tr>
        <tr>

        <td style="padding: 0px 0px 30px 30px;font-size: 10px; font-family: myridpro;  color:#4c4c4e; line-height:16px;">
                <table cellpadding="0" cellspacing="0" width="70%">
                    <tr>
                        <td style="font-size:13px; line-height:16px;">'.$shortDesc.'</td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    <table width="100%" cellpadding="0" align="center" cellspacing="0" bgcolor="#e6e7e9" style="max-width: 705px;"
        class="wrapper">
        <tr>
            <td style="padding: 30px 30px 0px">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="background: #fff; padding: 0 5px 0 5px; ">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                        <b>'.(($lang != "en") ? "Artikelnummer" : "Article no.").'</b>
                                    </td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                        <b>'.$widthLabel.'</b></td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                        <b>'.$heightLabel.'</b></td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                        <b>'.$clearHeightLabel.'</b></td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                        <b>'.$depthLabel.'</b></td>

                                </tr>
                                <tr>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">
                                        '.$atrticalNo.'</td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$width.'</td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$height.'</td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$clearHeight.'</td>
                                    <td
                                        style=" padding: 5px;  font-size: 12px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$depth.'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 10px;">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td valign="top" width="50%">
                                        <img src="'.$pImage[0].'" alt="" width="350">
                                    </td>
                                    <td valign="top" width="50%"
                                        style=" background: #fff; padding: 5px;">
                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td
                                                    style=" padding:0 0 2px; background: #fff; font-size: 12px ; font-weight: 600; font-family: myridpro; border-bottom: 1px solid #00823e; color:#4c4c4e; line-height:20px">
                                                    <b>Technische Daten</b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table cellpadding="0" cellspacing="0" width="100%">';
                                                    $incr = 1;
                                                    foreach ($att as $value) {
                                                        $attName  =  $value['name'];
                                                        $attValue  =  $value['value'];
                                                        if( $incr % 2 ) {
                                                        $html.= '<tr>
                                                                    <td width="45%"
                                                                        style=" padding: 1px 8px 1px; font-size: 11px; font-weight: 600; font-family: myridpro; color:#4c4c4e; line-height:20px">
                                                                        <b>'.$attName.'</b></td>
                                                                    <td
                                                                        style=" padding: 1px 8px 1px ;font-size: 11px ;  font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$attValue.'</td>
                                                                </tr>';
                                                            } else {
                                                        $html .= '<tr>
                                                                    <td
                                                                        style=" padding: 1px 8px 1px; ; background: #e6e7e9;   border-right:3px solid #fff;  font-size: 11px ; font-weight: 600; font-family: myridpro; color:#4c4c4e; line-height:20px">
                                                                        <b>'.$attName.'</b></td>
                                                                    <td
                                                                        style=" padding: 1px 8px 1px;  background: #e6e7e9;  font-size: 11px ; font-weight: 400; font-family: myridpro; color:#4c4c4e; line-height:20px">'.$attValue.'</td>
                                                                </tr>';
                                                           } $incr++; }

                                                $html .='</table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>

                    <td align="right" style="padding-top: 30px;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width=""></td>
                                    <td align="right" valign="bottom"
                                        style=" padding:1px 8px 0px; background: #fff;  font-size: 9px ; font-weight: 400; font-family: myridpro; color:#000; line-height:20px">
                                        '.(($lang != "en") ? "Produkt entdecken unter: " : "Discover product at: ").'
                                         <a href="'.$productURL.'"
                                            style="color: #00823e9e;">'.$productURL.'</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>

    </table>
    <table width="100%" cellpadding="0" align="center" cellspacing="0" bgcolor="#ffffff" style="max-width: 705px;"
        class="wrapper">
        <tr>
            <td style="padding: 30px 30px 10px">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td valign="top"
                            style="text-align: justify; padding: 0px 0px 5px; font-size: 12px ;font-family: myridpro;  color:#4c4c4e; line-height:20px">'.$productDesc.'
                        </td>
                        
                    </tr>
                </table>
            </td>
        </tr>
    </table>
     <table width="100%" cellpadding="0" align="center" cellspacing="0" bgcolor="#ffffff" style="max-width: 705px; page-break-inside: avoid;"
        class="wrapper">
        <tr>
            <td
                style="padding: 0px 30px 5px; font-size: 13px ; font-weight: 600; font-family: myridpro;  color:#4c4c4e; line-height:20px">
                <img src="'.$plusIcon.'" style="margin-right: 10px;" alt="">
                 <b>Hot-dip galvanised flat roof steel
                construction</b>
            </td>
        </tr>
        <tr>
            <td
                style="padding: 0px 30px 5px; font-size: 13px ; font-weight: 600; font-family: myridpro;  color:#4c4c4e; line-height:20px">
                <img src="'.$plusIcon.'" style="margin-right: 10px;" alt=""> 
                <b>Side- and back walls available in different
                variants and configurations</b>
            </td>
        </tr>
        <tr>
            <td
                style="padding: 0px 30px 5px; font-size: 13px ; font-weight: 600; font-family: myridpro;  color:#4c4c4e; line-height:20px">
                <img src="'.$plusIcon.'" style="margin-right: 10px;" alt="">
                 <b>Custom painting in min. C4 medium quality</b>
            </td>
        </tr>
        <tr>
            <td
                style="padding: 0px 30px 5px; font-size: 13px ; font-weight: 600; font-family: myridpro;  color:#4c4c4e; line-height:20px">
                <img src="'.$plusIcon.'" style="margin-right: 10px;" alt="">
                <b> Type statics for high wind and snow loads</b>
            </td>
        </tr>
        <tr>
            <td
                style="padding: 0px 30px 5px; font-size: 13px ; font-weight: 600; font-family: myridpro;  color:#4c4c4e; line-height:20px">
                <img src="'.$plusIcon.'" style="margin-right: 10px;" alt="">
                <b> Available with a wide range of accessories</b>
            </td>
        </tr>
     </table>
';

$footer ='
        <table  cellpadding="1" align="center" cellspacing="0" bgcolor="#ffffff" style="display:block; padding: 0 30px;" class="wrapper">
            <tr>
                <td colspan="3" align="center" style=" padding: 10px 30px 0px; font-size: 10px ; text-align: center; font-weight: 300; font-family: myridpro;  color:#888888; line-height:20px; ">

                '.(($lang != "en") ? "Es kann keine Garantie für die Vollständigkeit und Richtigkeit der Daten und Abbildungen übernommen werden." : "We don‘t guarantee the accuracy or completeness of the provided information or images.").'
                </td>
            </tr>
            <tr>
                <td valign="center" style="width:30%;padding: 30px 0 15px">
                    <table cellpadding="0" cellspacing="0" width="100%" style="text-align: center">
                        <tr>
                            <td style="height:1px; border-top:1px solid #888888; width: 35%;">&nbsp; </td>
                        </tr>

                    </table>
                </td>
                <td style="padding: 15px 0">
                    <table cellpadding="0" cellspacing="0" width="100%" style="text-align: center">
                        <tr>
                            <td style="color: #333333; font-size:14px; padding-bottom: 5px;  ">
                            WSM – Walter
                            Solbach Metallbau GmbH</td>
                        </tr>
                        <tr>
                            <td style="color: #888888; font-size:13px; font-weight:300 ">Industriestraße 20 · 51545 Waldbröl
                                · www.wsm.eu</td> 
                        </tr>
                    </table>

                </td>   
                <td valign="center" style="width:30%;padding: 30px 0 15px">
                    <table cellpadding="0" cellspacing="0" width="100%" style="text-align: center">
                        <tr>
                            <td valign="center" style="height:1px; border-top:1px solid #888888; width: 35%;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>   
        </table>
    ';

    $upload_dir         = wp_upload_dir();
    $location           = trailingslashit( $upload_dir['path'] );
    $title              =  str_replace(array(' ', '/'), '-', strtolower($title));

    require_once( 'mpdf/vendor/autoload.php' );

    $defaultConfig      = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs           = $defaultConfig['fontDir'];
    $defaultFontConfig  = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData           = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 10,
        'margin_bottom' => 22,
        'margin_header' => 0,
        'margin_footer' => 0,
        'fontDir' => array_merge($fontDirs, [__DIR__ . '/custom_fonts']),
        'fontdata' => $fontData + [
           'myridpro' => [
               'R' => 'MyriadPro-Regular.ttf',
               'B' => 'MyriadPro-Semibold.ttf'
           ]
        ],
        'default_font' => 'myridpro'
    ]);
    $mpdf->curlAllowUnsafeSslRequests = true;
    $mpdf->SetHTMLFooter($footer);

    $mpdf->WriteHTML( $html );
    $mpdf->Output($location .$sku.'-'.$title.'-'.$lang.'.pdf', "F");

    $filename = $sku.'-'.$title.'-'.$lang;
    $file = [];
    $file['name'] = $filename;
    $file['path'] =  $location .$filename.'.pdf';
    $file['url']  =trailingslashit( $upload_dir['url'] ).$filename.'.pdf';
    echo json_encode($file);
?>