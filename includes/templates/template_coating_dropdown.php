<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ($db_coatings && count($db_coatings)>0) {
?>
	<nav <?php if ($settings['show_coatings']!='on') echo 'style="display:none;"';?> class="applePie p3dlite-info">
		<div style="display:none;" class="menubtn"><?php esc_html_e( 'Coating', '3dprint-lite' );?></div>
		<ul class="nav">
			<li class="p3dlite-dropdown-li"><a id="p3dlite-coating-name" href="javascript:void(0)"><?php esc_html_e( 'Coating', '3dprint-lite' );?></a>
				<ul>
<?php
		foreach ( $db_coatings as $db_coating ) {
			$i = (int)$db_coating['id'];
			echo '<li class="p3dlite-tooltip '.($db_coating['photo'] ? 'p3dlite-li-photo' : '').'" data-tooltip-content="#p3dlite-tooltip-coating-'.esc_attr($i).'" data-color=\''.esc_attr($db_coating['color']).'\' data-shininess=\''.(isset($db_coating['shininess']) ? esc_attr($db_coating['shininess']) : 'none').'\' data-glow=\''.(isset($db_coating['glow']) ? esc_attr($db_coating['glow']) : '0').'\' data-transparency=\''.(isset($db_coating['transparency']) ? esc_attr($db_coating['transparency']) : 'none').'\' data-name="'.esc_attr__( $db_coating['name'] ).'" onclick="p3dliteSelectCoating(this);"><input style="display:none;" id="p3dlite_coating_'.esc_attr($i).'" class="p3dlite-control" autocomplete="off" type="radio" data-id="'.esc_attr($i).'"  data-color=\''.esc_attr($db_coating['color']).'\' data-name="'.esc_attr( $db_coating['name'] ).'"  data-materials="'.(isset($db_coating['materials']) && strlen($db_coating['materials']) ? esc_attr($db_coating['materials']) : '').'" data-price="'.esc_attr( $db_coating['price'] ).'" data-price_type="'.esc_attr( $db_coating['price_type'] ).'" data-price1="'.esc_attr( $db_coating['price1'] ).'" data-price_type1="'.esc_attr( $db_coating['price_type1'] ).'" name="product_coating" ><a class="p3dlite-dropdown-item" href="javascript:void(0)"><div style="background-color:'.esc_attr($db_coating['color']).'" class="color-sample"></div>'.esc_html__($db_coating['name'],'3dprint-lite').'</a></li>';
		}
?>
			</ul>
	</nav>
<?php
}
?>