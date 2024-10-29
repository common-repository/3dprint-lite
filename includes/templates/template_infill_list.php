<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
	<div <?php if ($settings['show_infills']!='on') echo 'style="display:none;"';?> class="p3dlite-info">
		<fieldset id="infill_fieldset" class="p3dlite-fieldset">
			<legend><?php esc_html_e( 'Infill', '3dprint-lite' );?></legend>
			<ul class="p3dlite-list">
<?php
		foreach ( $db_infills as $db_infill ) {
			$i = (int)$db_infill['id'];
			if ((int)$db_infill['status']==0) continue;
			echo '<li class="p3dlite-tooltip '.($db_infill['photo'] ? 'p3dlite-li-photo' : '').'" data-tooltip-content="#p3dlite-tooltip-infill-'.esc_attr($i).'" onclick="p3dliteSelectInfill(this);" data-name="'.esc_attr__( $db_infill['name'] ).'"><input id="p3dlite_infill_'.esc_attr($i).'" class="p3dlite-control" autocomplete="off" data-id="'.esc_attr($i).'" data-name="'.esc_attr__( $db_infill['name'] ).'" data-price="'.esc_attr( $db_infill['price'] ).'" data-price_type="'.esc_attr($db_infill['price_type']).'" data-price1="'.esc_attr( $db_infill['price1'] ).'" data-price_type1="'.esc_attr($db_infill['price_type1']).'" type="radio" value="'.esc_attr($db_infill['infill']).'" name="product_infill">'.esc_html__($db_infill['name'], '3dprint-lite').'</li>';
		}
?>
		  	</ul>
	  	</fieldset>
	</div>