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

	$content = '<head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                <meta name="format-detection" content="telephone=no" />

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
                                                <td style="text-align: center; background: rgba(255,255,255,0.6); height: 292px;">
                                                    <h4 style="font-size: 54px; line-height: 68px; font-weight: bold; margin: 25px 0;">'.$_REQUEST['dis'].'%</h4>
                                                    <h5 style="text-transform: uppercase; font-size: 24px; letter-spacing: 3px; font-weight: bold; margin: 20px 0;">Discount On</h5>
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
                                            <td align="center" valign="top" width="280" style="width: 280px;">
                                                <img class="product-img" src="'.get_the_post_thumbnail_url( $post->ID, 'full' ).'" alt="" title="" min-height="150px" max-width="100px">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top" width="280" style="width: 280px;">
                                                <h5 style=" font-size: 20px; font-weight: bold; margin: 20px 0;">'.$post->post_title.'</h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top" width="280" style="width: 280px;">
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
        //$stylesheet = file_get_contents('https://fonts.googleapis.com/css?family=PT+Sans:400,700');
        //$stylesheet2 = file_get_contents('https://fonts.googleapis.com/css?family=Fira+Sans+Extra+Condensed:400,900');
     	$stylesheet3 = file_get_contents('../assets/css/htmltopdf.css');
	    $mpdf->WriteHTML($stylesheet3,1);
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

