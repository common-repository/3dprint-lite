<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<div id="p3dlite-stellarnav-infill" <?php if ($settings['show_infills']!='on') echo 'style="display:none;"';?> class="stellarnav p3dlite-info">
		<div style="display:none;" class="menubtn"><?php esc_html_e( 'Infill', '3dprint-lite' );?></div>
		<ul class="nav">
			<li class="mega"><a id="p3dlite-infill-name" class="dd-title" href="javascript:void(0)"><?php esc_html_e( 'Infill', '3dprint-lite' );?> </a>
				<ul>
<?php
		foreach ( $db_infills as $db_infill ) {
			$i = (int)$db_infill['id'];
			if ((int)$db_infill['status']==0) continue;
			echo '<li class="p3dlite-tooltip '.($db_infill['photo'] ? 'p3dlite-li-photo' : '').'" data-tooltip-content="#p3dlite-tooltip-infill-'.esc_attr($i).'" onclick="p3dliteSelectInfill(this);" data-name="'.esc_attr__( $db_infill['name'] ).'"><input style="display:none;" id="p3dlite_infill_'.esc_attr($i).'" class="p3dlite-control" autocomplete="off" data-id="'.esc_attr($i).'" data-name="'.esc_attr( $db_infill['name'] ).'" data-price="'.esc_attr( $db_infill['price'] ).'" data-price_type="'.esc_attr($db_infill['price_type']).'" data-price1="'.esc_attr( $db_infill['price1'] ).'" data-price_type1="'.esc_attr($db_infill['price_type1']).'" value="'.esc_attr($db_infill['infill']).'" type="radio" name="product_infill" ><a class="p3dlite-dropdown-item" href="javascript:void(0)">'.esc_html__($db_infill['name'],'3dprint-lite').'</a></li>';
		}
?>
				</ul>
		</ul>
	</div>