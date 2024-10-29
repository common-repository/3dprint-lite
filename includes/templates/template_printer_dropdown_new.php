<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<div id="p3dlite-stellarnav-printer" <?php if ($settings['show_printers']!='on') echo 'style="display:none;"';?> class="stellarnav p3dlite-info">
		<div style="display:none;" class="menubtn"><?php esc_html_e( 'Printer', '3dprint-lite' );?></div>
		<ul class="nav">
			<li class="mega"><a id="p3dlite-printer-name" class="dd-title" href="javascript:void(0)"><?php esc_html_e( 'Printer', '3dprint-lite' );?> </a>
				<ul>
<?php
		foreach ( $db_printers as $db_printer ) {
			$i = (int)$db_printer['id'];
			echo '<li class="p3dlite-tooltip '.($db_printer['photo'] ? 'p3dlite-li-photo' : '').'" data-tooltip-content="#p3dlite-tooltip-printer-'.esc_attr($i).'" onclick="p3dliteSelectPrinter(this);" data-name="'.esc_attr__( $db_printer['name'] ).'"><input style="display:none;" id="p3dlite_printer_'.esc_attr($i).'" class="p3dlite-control" autocomplete="off" data-full_color="'.( isset($db_printer['full_color']) ? esc_attr($db_printer['full_color']) : '1' ).'" data-platform_shape="'.esc_attr( isset($db_printer['platform_shape']) ? esc_attr($db_printer['platform_shape']) : 'rectangle' ).'" data-diameter="'.(float)$db_printer['diameter'].'" data-width="'.(float)$db_printer['width'].'" data-length="'.(float)$db_printer['length'].'" data-height="'.(float)$db_printer['height'].'" data-min_side="'.(float)$db_printer['min_side'].'" data-id="'.esc_attr($i).'" data-name="'.esc_attr( $db_printer['name'] ).'" data-infills="'.($db_printer['type']!='other' ? esc_attr($db_printer['infills']) : '').'" data-default-infill="'.($db_printer['type']!='other' ? esc_attr($db_printer['default_infill']) : '').'" data-materials="'.(strlen($db_printer['materials']) ? esc_attr($db_printer['materials']) : '').'" data-price="'.esc_attr( $db_printer['price'] ).'" data-price_type="'.esc_attr($db_printer['price_type']).'" data-price1="'.esc_attr( $db_printer['price1'] ).'" data-price_type1="'.esc_attr($db_printer['price_type1']).'" data-price2="'.esc_attr( $db_printer['price2'] ).'" data-price_type2="'.esc_attr($db_printer['price_type2']).'" data-price3="'.esc_attr( $db_printer['price3'] ).'" data-price_type3="'.esc_attr($db_printer['price_type3']).'" type="radio" name="product_printer" ><a class="p3dlite-dropdown-item" href="javascript:void(0)">'.esc_html__($db_printer['name'],'3dprint-lite').'</a></li>';
		}
?>
				</ul>
		</ul>
	</div>