<?php
include "../../../../wp-load.php";
include( 'mpdf/mpdf.php');

if(isset($_REQUEST['products'])) {

	ob_start();
	$mpdf=new mPDF();
    $args = array(
  		'post_type' => 'product',
  		'include' => $_REQUEST['products']
	);
	$posts = get_posts($args);
	// $content = "<table class='pdf-table'>
	// 	<tr>
	// 	<td width='820' height='200' align='center'><p>".$_REQUEST['dis']." %</p><br>
	// 		<p>Discount on</p>
	// 	</td>
	// 	</tr></table><table>";
	// foreach( $posts as $post ) {
	// 	$product = wc_get_product( $post->ID );
	// 	$content .= "<tr>
	// 	<td width='320' height='220'>".$post->post_title."<p>".$post->post_content."</p><p>Price : ".$product->get_regular_price()."</p></td>
	// 	<td width='500' height='190'><img src='".get_the_post_thumbnail_url( $post->ID, 'full' )."' width='100' height='80'></td>
	// 	</tr>";
	// }
	// $content .= "</table>";

	$content = '<html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css?family=Fira+Sans+Extra+Condensed:400,900" rel="stylesheet">
            </head>
            <body style="margin:0px; padding:0px;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
              <tr>
                <td align="center">
                    <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="table-layout:fixed;">
                    <tr>
                      <td align="center" valign="top">
                        <table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
                            <tr>
                                <td valign="top" align="center" background="'.MY_PLUGIN_PATH.'assets/images/background.jpg" width="100%" height="400" style="background-repeat:no-repeat; background:url('.MY_PLUGIN_PATH.'assets/images/background.jpg);">
                                    <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tr><td>&nbsp;</td></tr>
                                            <tr><td>&nbsp;</td></tr>
                                            <tr>
                                                <td style="text-align: center; background: rgba(255,255,255,0.6);">
                                                    <h2 style="font-size: 64px; line-height: 68px; margin: 20px 0 25px; font-style: italic; font-weight: bold; font-family: "Fira Sans Extra Condensed", sans-serif; letter-spacing: 3px;"></h2>
                                                    <h4 style="font-size: 54px; line-height: 68px; font-weight: bold; margin: 0 0 25px;">'.$_REQUEST['dis'].'%</h4>
                                                    <h5 style="text-transform: uppercase; font-size: 24px; letter-spacing: 3px; font-weight: bold;">Discount On</h5>
                                                </td>
                                            </tr>
                                        </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="center" >
                                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" style="margin-top: 20px; margin-bottom: 20px;">
                                        <tr>';
        foreach( $posts as $post ) {
            $product = wc_get_product( $post->ID );
                    $content .= '<td align="center" valign="top" width="280" style="text-align: center;">
                                    <table width="280" border="0" cellspacing="0" cellpadding="0" align="center">
                                        <tr>
                                            <td align="center" valign="top">
                                                <img src="'.get_the_post_thumbnail_url( $post->ID, 'full' ).'" alt="" title="" style="width: 100%; height: auto; max-width: 160px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h5 style=" font-size: 20px; font-weight: bold; margin: 20px 0;">'.$post->post_title.'</h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="font-size: 16px; line-height: 24px; font-weight: bold;">Price:&nbsp;<span>&euro;'.$product->get_regular_price().'</span></p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>';
        }
                    $content .= '</tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            </body>
            </html>';
	$html = "test html";
    if( ! empty( $posts ) ) {
     	$stylesheet = file_get_contents('../assets/css/htmltopdf.css');
	    $mpdf->WriteHTML($stylesheet,1);
	    $mpdf->WriteHTML($content, 2);
	    
       
    }
    $mpdf->Output('recent_product_flyer.pdf','D');
    ob_end_flush();
	readfile('recent_product_flyer.pdf');
	$file = "recent_product_flyer.pdf";
	
	header("Content-Type: application/pdf");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=" . urlencode($file));
	header("Content-Length: " . filesize($file));
	header("Cache-control: private");
	exit;
}

