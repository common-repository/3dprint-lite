<?php
/**
 *
 *
 * @author Sergey Burkov, http://www.wp3dprinting.com
 * @copyright 2015
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Output buffering allows admin screens to make redirects later on.
 */
add_action( 'admin_init', 'p3dlite_buffer', 1 );
function p3dlite_buffer() {
	ob_start();
}

function p3dlite_process_materials ($db_items, $post_items, $option_name, $material_id) {
	foreach ($db_items as $item) {
		if (isset($post_items) && isset($post_items[$material_id]) && count( $post_items[$material_id] )>0) {
			foreach ($post_items[$material_id] as $item_id) {
				$item_materials_array = explode(',', $item['materials']);
				if (!in_array($item['id'], $post_items[$material_id])) {

					foreach ($item_materials_array as $item_key=>$item_value) {
						if ((int)$item_value == (int)$material_id) {
							unset($item_materials_array[$item_key]);
						}
					}
				}
				else {
					$item_materials_array = explode(',', $item['materials']);
					if (!in_array($material_id, $item_materials_array)) {
						$item_materials_array[]=$material_id;
					}
					
				}
			}
		}
		else {
			$item_materials_array = explode(',', $item['materials']);

			foreach ($item_materials_array as $item_key=>$item_value) {
				if ((int)$item_value == (int)$material_id) {
					unset($item_materials_array[$item_key]);
				}
			}
		}
		$item['materials']=implode(',', array_filter(array_unique($item_materials_array)));
		p3dlite_update_option( 'p3dlite_'.$option_name, $item );
	}
}

add_action( 'admin_menu', 'register_3dprintlite_menu_page' );
function register_3dprintlite_menu_page() {
//add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null )
	add_menu_page( 'Settings', '3DPrint Lite', 'manage_options', '3dprint-lite', 'register_3dprintlite_settings_page_callback' );
	add_submenu_page( '3dprint-lite', 'Settings', 'Settings', 'manage_options', 'p3dlite_settings', 'register_3dprintlite_settings_page_callback' );
	add_submenu_page( '3dprint-lite', 'Materials', 'Materials', 'manage_options', 'p3dlite_materials', 'register_3dprintlite_materials_page_callback' );
	add_submenu_page( '3dprint-lite', 'Printers', 'Printers', 'manage_options', 'p3dlite_printers', 'register_3dprintlite_printers_page_callback' );
	add_submenu_page( '3dprint-lite', 'Coatings', 'Coatings', 'manage_options', 'p3dlite_coatings', 'register_3dprintlite_coatings_page_callback' );
	add_submenu_page( '3dprint-lite', 'Infills', 'Infills', 'manage_options', 'p3dlite_infills', 'register_3dprintlite_infills_page_callback' );
	add_submenu_page( '3dprint-lite', 'Post-Processings', 'Post-Processings', 'manage_options', 'p3dlite_postprocessings', 'register_3dprintlite_postprocessings_page_callback' );
	add_submenu_page( '3dprint-lite', 'Price Requests', 'Price Requests', 'manage_options', 'p3dlite_price_requests', 'register_3dprintlite_price_requests_page_callback' );
	add_submenu_page( '3dprint-lite', 'Email Templates', 'Email Templates', 'manage_options', 'p3dlite_email_templates', 'register_3dprintlite_email_templates_page_callback' );
	add_submenu_page( '3dprint-lite', 'File Manager', 'File Manager', 'manage_options', 'p3dlite_file_manager', 'register_3dprintlite_file_manager_page_callback' );
#	add_submenu_page( '3dprint-lite', 'Discounts', 'Discounts', 'manage_options', 'p3dlite_discounts', 'register_3dprintlite_discounts_page_callback' );



}


function register_3dprintlite_settings_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != '3dprint-lite' && $_GET['page'] != 'p3dlite_settings') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if ( isset( $_POST['action'] ) && $_POST['action']=='save_login' ) {
		$settings['api_login']=sanitize_text_field($_POST['api_login']);
		update_option( 'p3dlite_settings', $settings );
	}


	if ( isset( $_POST['p3dlite_settings'] ) && !empty( $_POST['p3dlite_settings'] ) ) {
	        $settings_update = array_map('sanitize_text_field', $_POST['p3dlite_settings']);

		if (isset($_FILES['p3dlite_settings']['tmp_name']['ajax_loader']) && strlen($_FILES['p3dlite_settings']['tmp_name']['ajax_loader'])>0) {
			$uploaded_file = p3dlite_upload_file('p3dlite_settings', 'ajax_loader');
			$settings_update['ajax_loader']=str_replace('http:','',$uploaded_file['url']);
		}
		else {
			$settings_update['ajax_loader']=$settings['ajax_loader'];
		}
/*
		if (isset($_FILES['woo3dv_settings']['tmp_name']['view3d_button_image']) && strlen($_FILES['woo3dv_settings']['tmp_name']['view3d_button_image'])>0) {
			$uploaded_file = woo3dv_upload_file('woo3dv_settings', 'view3d_button_image');
			$settings_update['view3d_button_image']=str_replace('http:','',$uploaded_file['url']);
		}
		else {
			$settings_update['view3d_button_image']=$settings['view3d_button_image'];
		}
*/
		if (!is_numeric($settings_update['num_decimals'])) $settings_update['num_decimals'] = 2;
		if (empty($settings_update['canvas_width'])) $settings_update['canvas_width'] = 1024;
		if (empty($settings_update['canvas_height'])) $settings_update['canvas_height'] = 768;
		if (empty($settings_update['file_max_size'])) $settings_update['file_max_size'] = 20;
		if (empty($settings_update['file_chunk_size'])) $settings_update['file_chunk_size'] = 2;
		if (empty($settings_update['items_per_page'])) $settings_update['items_per_page'] = 10;




		update_option( 'p3dlite_settings', $settings_update );
	}

	$settings=p3dlite_get_option( 'p3dlite_settings' );
#var_dump( $settings['ninjaforms_shortcode']);
#	$shortcode_atts = shortcode_parse_atts( $settings['ninjaforms_shortcode'] );
#	if (isset($shortcode_atts['id']))
#		$form_id = (int)$shortcode_atts['id'];
#	else	
#		$form_id = 0;
	
#p3dlite_clean_uploads();
	add_thickbox(); 
#p3dlite_check_install();
	$p3dlite_cache=p3dlite_get_option('p3dlite_cache');
	$p3dlite_triangulation_cache=p3dlite_get_option('p3dlite_triangulation_cache');


?>
<div class="wrap">
	<?php esc_html_e('Shortcode:', '3dprint-lite');?> <input type="text" name="textbox" value="[3dprint-lite]" onclick="this.select()" />
	<br>
	<h2><?php esc_html_e( '3D printing settings', '3dprint-lite' );?></h2>
	<form method="post" action="admin.php?page=p3dlite_settings" enctype="multipart/form-data">
	<div id="p3dlite_tabs">

		<ul>
			<li><a href="#3dp_tabs-0"><?php esc_html_e( 'Settings', '3dprint-lite' );?></a></li>
		</ul>
		<div id="p3dlite_tabs-0">
				<p><b><?php esc_html_e( 'Pricing', '3dprint-lite' );?></b></p>
				<table>
					<tr>
						<td>
							<?php esc_html_e( 'Get a Quote', '3dprint-lite' );?>
						</td>
						<td>
							<select name="p3dlite_settings[pricing]">
								<option <?php if ( $settings['pricing']=='request_estimate' ) echo 'selected';?> value="request_estimate"><?php esc_html_e( 'Give an estimate and request price', '3dprint-lite' );?></option>
								<option <?php if ( $settings['pricing']=='request' ) echo 'selected';?> value="request"><?php esc_html_e( 'Request price', '3dprint-lite' );?></option>
								<option disabled value="checkout"><?php esc_html_e( 'Calculate price and add to cart (Premium only)' , '3dprint-lite' );?></option>
			 				</select>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Minimum Price', '3dprint-lite' );?>
						</td>
						<td>
							<input type="text" size="1" name="p3dlite_settings[min_price]" value="<?php echo (float)$settings['min_price'];?>"><?php echo esc_html($settings['currency']);?>
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Minimum Price Type', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[minimum_price_type]">
								<option <?php if ( $settings['minimum_price_type']=='minimum_price' ) echo 'selected';?> value="minimum_price"><?php esc_html_e( 'Minimum Price' , '3dprint-lite' );?></option>
								<option <?php if ( $settings['minimum_price_type']=='starting_price' ) echo 'selected';?> value="starting_price"><?php esc_html_e( 'Starting Price' , '3dprint-lite' );?></option>
						 	</select>
							<img class="tooltip" title="<?php htmlentities(esc_html_e( 'Minimum Price: if total is less than minimum price then total = minimum price. Starting Price: total = total + starting price.', '3dprint-lite' ));?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Currency', '3dprint-lite' );?>
						</td>
						<td>
							<input type="text" size="1" name="p3dlite_settings[currency]" value="<?php echo esc_attr($settings['currency']);?>">
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Currency Position', '3dprint-lite' );?>
						</td>
						<td>
							<select name="p3dlite_settings[currency_position]">
								<option <?php if ($settings['currency_position']=='left') echo 'selected';?> value="left"><?php esc_html_e('Left', '3dprint-lite');?>
								<option <?php if ($settings['currency_position']=='left_space') echo 'selected';?> value="left_space"><?php esc_html_e('Left with space', '3dprint-lite');?>
								<option <?php if ($settings['currency_position']=='right') echo 'selected';?> value="right"><?php esc_html_e('Right', '3dprint-lite');?>
								<option <?php if ($settings['currency_position']=='right_space') echo 'selected';?> value="right_space"><?php esc_html_e('Right with space', '3dprint-lite');?>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Number of Decimals', '3dprint-lite' );?>
						</td>
						<td>
							<input type="text" size="1" name="p3dlite_settings[num_decimals]" value="<?php echo esc_attr($settings['num_decimals']);?>">
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Thousands Separator', '3dprint-lite' );?>
						</td>
						<td>
							<input type="text" size="1" name="p3dlite_settings[thousand_sep]" value="<?php echo esc_attr($settings['thousand_sep']);?>">
						</td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Decimal Point', '3dprint-lite' );?>
						</td>
						<td>
							<input type="text" size="1" name="p3dlite_settings[decimal_sep]" value="<?php echo esc_attr($settings['decimal_sep']);?>">
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Round Price To', '3dprint-lite' );?></td>
						<td>
							<input type="text" size="2" disabled /><?php esc_html_e('digits', '3dprint-lite'); ?> 
							<img class="tooltip" title="<?php esc_attr_e( 'Examples:<br>2 digits rounds 1.9558 to 1.96<br>-3 digits rounds 1241757 to 1242000', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
								Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version


						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Support Charges', '3dprint-lite' );?></td>
						<td>
							<input type="checkbox" disabled>
							<img class="tooltip" title="<?php esc_attr_e( 'Shows support removal charges on the product page (Analyse API required).', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
								Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Price Debug Mode', '3dprint-lite' );?></td>
						<td>
							<input type="hidden" name="p3dlite_settings[price_debug_mode]" value="0">
							<input type="checkbox" name="p3dlite_settings[price_debug_mode]" <?php if ($settings['price_debug_mode']=='on') echo 'checked';?>>
							<img class="tooltip" title="<?php esc_attr_e( 'Shows price calculation details on the product page in the browser console (F12)', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>

				</table>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />
				<hr>
				<p><b><?php esc_html_e( 'Product Viewer', '3dprint-lite' );?></b></p>
				<table>
					<tr>
						<td><?php esc_html_e( 'Canvas Resolution', '3dprint-lite' );?></td>
						<td>
							<input size="3" type="text"  placeholder="<?php esc_html_e( 'Width', '3dprint-lite' );?>" name="p3dlite_settings[canvas_width]" value="<?php echo (int)$settings['canvas_width'];?>">px &times; <input size="3"  type="text" placeholder="<?php esc_html_e( 'Height', '3dprint-lite' );?>" name="p3dlite_settings[canvas_height]" value="<?php echo (int)$settings['canvas_height'];?>">px
							<img class="tooltip" title="<?php esc_html_e('Only affects the image quality', '3dprint-lite');?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Shading', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[shading]">
								<option <?php if ( $settings['shading']=='flat' ) echo 'selected';?> value="flat"><?php esc_html_e( 'Flat', '3dprint-lite' );?></option>
								<option <?php if ( $settings['shading']=='smooth' ) echo 'selected';?> value="smooth"><?php esc_html_e( 'Smooth', '3dprint-lite' );?></option>
							</select> 
							<img class="tooltip" title="<img src='<?php echo esc_url(plugins_url( '3dprint-lite/images/shading.jpg' ));?>'>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>


					<tr>
						<td><?php esc_html_e( 'Cookie Lifetime', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[cookie_expire]">
								<option <?php if ( $settings['cookie_expire']=='0' ) echo 'selected';?> value="0">0 <?php esc_html_e( '(no cookies)', '3dprint-lite' );?> 
								<option <?php if ( $settings['cookie_expire']=='1' ) echo 'selected';?> value="1">1
								<option <?php if ( $settings['cookie_expire']=='2' ) echo 'selected';?> value="2">2
							</select> <?php esc_html_e( 'days', '3dprint-lite' );?> 
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Multistep Process', '3dprint-lite' );?></td>
						<td><input type="checkbox" disabled>
							<img class="tooltip" title="<?php esc_html_e('Enables the user to collapse & expand steps by clicking on next/back buttons', '3dprint-lite');?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
        						Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Adjust canvas position on scroll', '3dprint-lite' );?></td>
						<td><input type="checkbox" disabled>
							Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
					</tr>
					<tr>
						<td><?php esc_html_e( 'Background Color', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[background1]" value="<?php echo esc_attr($settings['background1']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Grid Color', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[plane_color]" value="<?php echo esc_attr($settings['plane_color']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Ground Color', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[ground_color]" value="<?php echo esc_attr($settings['ground_color']);?>"></td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Light Sources', '3dprint-lite' );?></td>
						<td>
							<table>
								<tr>
									<td><input type="checkbox" disabled></td>
									<td><input type="checkbox" disabled></td>
									<td><input type="checkbox" disabled checked></td>
								</tr>
								<tr>
									<td><input type="checkbox" disabled></td>
									<td><input type="checkbox" disabled></td>
									<td><input type="checkbox" disabled></td>
								</tr>
								<tr>
									<td><input type="checkbox" disabled checked></td>
									<td><input type="checkbox" disabled></td>
									<td><input type="checkbox" disabled></td>
								</tr>

							</table>

							Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version


						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Printer Color', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[printer_color]" value="<?php echo esc_attr($settings['printer_color']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Button Background', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[button_color1]" value="<?php echo esc_attr($settings['button_color1']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Button Shadow', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[button_color2]" value="<?php echo esc_attr($settings['button_color2']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Button Progress Bar', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[button_color3]" value="<?php echo esc_attr($settings['button_color3']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Button Font', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[button_color4]" value="<?php echo esc_attr($settings['button_color4']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Button Tick', '3dprint-lite' );?></td>
						<td><input type="text" class="p3dlite_color_picker" name="p3dlite_settings[button_color5]" value="<?php echo esc_attr($settings['button_color5']);?>"></td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Loading Image', '3dprint-lite' );?></td>
						<td>
							<img class="3dprint-lite-preview" src="<?php echo esc_url($settings['ajax_loader']);?>">
							<input type="file" name="p3dlite_settings[ajax_loader]" accept="image/*">
						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Auto Scale', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[auto_scale]" value="0"><input type="checkbox" name="p3dlite_settings[auto_scale]" <?php if ($settings['auto_scale']=='on') echo 'checked';?>>
							<img class="tooltip" title="<?php esc_attr_e( 'Enables automatic scaling if a model is too large or too small.', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">

						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Auto Rotation', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[auto_rotation]" value="0"><input type="checkbox" name="p3dlite_settings[auto_rotation]" <?php if ($settings['auto_rotation']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Auto Rotation Speed', '3dprint-lite' );?></td>
						<td>
							<input name="p3dlite_settings[auto_rotation_speed]" type="number" min="1" max="10" step="1" value="<?php echo (int)$settings['auto_rotation_speed'];?>">
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Auto Rotation Direction', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[auto_rotation_direction]">
								<option <?php if ( $settings['auto_rotation_direction']=='cw' ) echo "selected";?> value="cw"><?php _e('Clockwise', '3dprint-lite');?></option>
								<option <?php if ( $settings['auto_rotation_direction']=='ccw' ) echo "selected";?> value="ccw"><?php _e('Counter-Clockwise', '3dprint-lite');?></option>
							</select>

						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Resize model on scale', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[resize_on_scale]" value="0"><input type="checkbox" name="p3dlite_settings[resize_on_scale]" <?php if ($settings['resize_on_scale']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Fit camera to model on resize', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[fit_on_resize]" value="0"><input type="checkbox" name="p3dlite_settings[fit_on_resize]" <?php if ($settings['fit_on_resize']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Shadows', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_shadow]" value="0"><input type="checkbox" name="p3dlite_settings[show_shadow]" <?php if ($settings['show_shadow']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Ground Mirror', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[ground_mirror]" value="0"><input type="checkbox" name="p3dlite_settings[ground_mirror]" <?php if ($settings['ground_mirror']=='on') echo 'checked';?>></td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Show Grid', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_grid]" value="0"><input type="checkbox" name="p3dlite_settings[show_grid]" <?php if ($settings['show_grid']=='on') echo 'checked';?>></td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Show Upload Button', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_upload_button]" value="0"><input type="checkbox" name="p3dlite_settings[show_upload_button]" <?php if ($settings['show_upload_button']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Scaling Controls', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_scale]" value="0"><input type="checkbox" name="p3dlite_settings[show_scale]" <?php if ($settings['show_scale']=='on') echo 'checked';?>></td>
					</tr>


					<tr>
						<td><?php esc_html_e( 'Can Scale Axis Independently', '3dprint-lite' );?></td>
						<td><input type="checkbox" disabled>
							Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Show Rotation Controls', '3dprint-lite' );?></td>
						<td><input type="checkbox" disabled>
							Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
						</td>
					</tr>

					<tr>
						<td><?php esc_html_e( 'Show Printer Box', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_printer_box]" value="0"><input type="checkbox" name="p3dlite_settings[show_printer_box]" <?php if ($settings['show_printer_box']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Canvas Stats', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[canvas_stats]" value="0"><input type="checkbox" name="p3dlite_settings[canvas_stats]" <?php if ($settings['canvas_stats']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show File Unit', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_unit]" value="0"><input type="checkbox" name="p3dlite_settings[show_unit]" <?php if ($settings['show_unit']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Model Stats', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[model_stats]" value="0"><input type="checkbox" name="p3dlite_settings[model_stats]" <?php if ($settings['model_stats']=='on') echo 'checked';?>>
							<div id="show_model_stats_extra" style="display:none;">
								<table>
									<tr>
										<td><?php esc_html_e( 'Material Volume', '3dprint-lite' );?></td>
										<td><input type="hidden" name="p3dlite_settings[show_model_stats_material_volume]" value="0"><input type="checkbox" name="p3dlite_settings[show_model_stats_material_volume]" <?php if ($settings['show_model_stats_material_volume']=='on') echo 'checked';?>></td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Support Material Volume', '3dprint-lite' );?></td>
										<td><input type="checkbox" disabled>
											Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
										</td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Box Volume', '3dprint-lite' );?></td>
										<td><input type="hidden" name="p3dlite_settings[show_model_stats_box_volume]" value="0"><input type="checkbox" name="p3dlite_settings[show_model_stats_box_volume]" <?php if ($settings['show_model_stats_box_volume']=='on') echo 'checked';?>></td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Surface Area', '3dprint-lite' );?></td>
										<td><input type="hidden" name="p3dlite_settings[show_model_stats_surface_area]" value="0"><input type="checkbox" name="p3dlite_settings[show_model_stats_surface_area]" <?php if ($settings['show_model_stats_surface_area']=='on') echo 'checked';?>></td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Model Weight', '3dprint-lite' );?></td>
										<td><input type="hidden" name="p3dlite_settings[show_model_stats_model_weight]" value="0"><input type="checkbox" name="p3dlite_settings[show_model_stats_model_weight]" <?php if ($settings['show_model_stats_model_weight']=='on') echo 'checked';?>></td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Model Dimensions', '3dprint-lite' );?></td>
										<td><input type="hidden" name="p3dlite_settings[show_model_stats_model_dimensions]" value="0"><input type="checkbox" name="p3dlite_settings[show_model_stats_model_dimensions]" <?php if ($settings['show_model_stats_model_dimensions']=='on') echo 'checked';?>></td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Print Time', '3dprint-lite' );?></td>
										<td><input type="checkbox" disabled>
											Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
										</td>
									</tr>

								</table>
							</div>

							<a href="#TB_inline?width=300&height=200&inlineId=show_model_stats_extra" class="thickbox"><button onclick="return false;">...</button></a>

						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Selection Order', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[selection_order]">
								<option <?php if ( $settings['selection_order']=='materials_printers' ) echo 'selected';?> value="materials_printers"><?php esc_html_e( 'First materials, then printers', '3dprint-lite' );?></option>
								<option <?php if ( $settings['selection_order']=='printers_materials' ) echo 'selected';?> value="printers_materials"><?php esc_html_e( 'First printers, then materials', '3dprint-lite' );?></option>

							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Tooltip Engine', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[tooltip_engine]">
								<option <?php if ( $settings['tooltip_engine']=='tippy' ) echo 'selected';?> value="tippy"><?php _e( 'Tippy', '3dprint-lite' );?></option>
								<option <?php if ( $settings['tooltip_engine']=='tooltipster' ) echo 'selected';?> value="tooltipster"><?php _e( 'Tooltipster (deprecated)', '3dprint-lite' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Tooltip Theme', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[tooltip_theme]">
								<option <?php if ( $settings['tooltip_theme']=='light' ) echo 'selected';?> value="light"><?php _e( 'Light', '3dprint-lite' );?></option>
								<option <?php if ( $settings['tooltip_theme']=='dark' ) echo 'selected';?> value="dark"><?php _e( 'Dark', '3dprint-lite' );?></option>
							</select> 
						</td>
					</tr>


					<tr>
						<td><?php esc_html_e( 'Printers Layout', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[printers_layout]">
								<option <?php if ( $settings['printers_layout']=='lists' ) echo 'selected';?> value="lists"><?php esc_html_e( 'List', '3dprint-lite' );?></option>
								<option <?php if ( $settings['printers_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php esc_html_e( 'Dropdown (Deprecated)', '3dprint-lite' );?></option>
								<option <?php if ( $settings['printers_layout']=='dropdown_new' ) echo 'selected';?> value="dropdown_new"><?php esc_html_e( 'Dropdown (New)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Searchable Dropdown (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Slider (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Group Slider (available in Premium)', '3dprint-lite' );?></option>

							</select> 
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Materials Layout', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[materials_layout]">
								<option <?php if ( $settings['materials_layout']=='lists' ) echo 'selected';?> value="lists"><?php esc_html_e( 'List', '3dprint-lite' );?></option>
								<option <?php if ( $settings['materials_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php esc_html_e( 'Dropdown (Deprecated)', '3dprint-lite' );?></option>
								<option <?php if ( $settings['materials_layout']=='dropdown_new' ) echo 'selected';?> value="dropdown_new"><?php esc_html_e( 'Dropdown (New)', '3dprint-lite' );?></option>
								<option <?php if ( $settings['materials_layout']=='colors' ) echo 'selected';?> value="colors"><?php esc_html_e( 'Colors', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Searchable Dropdown (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Slider (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Group Slider (available in Premium)', '3dprint-lite' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Coatings Layout', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[coatings_layout]">
								<option <?php if ( $settings['coatings_layout']=='lists' ) echo 'selected';?> value="lists"><?php esc_html_e( 'List', '3dprint-lite' );?></option>
								<option <?php if ( $settings['coatings_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php esc_html_e( 'Dropdown (Deprecated)', '3dprint-lite' );?></option>
								<option <?php if ( $settings['coatings_layout']=='dropdown_new' ) echo 'selected';?> value="dropdown_new"><?php esc_html_e( 'Dropdown (New)', '3dprint-lite' );?></option>
								<option <?php if ( $settings['coatings_layout']=='colors' ) echo 'selected';?> value="colors"><?php esc_html_e( 'Colors', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Searchable Dropdown (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Slider (available in Premium)', '3dprint-lite' );?></option>
								<option disabled><?php esc_html_e( 'Group Slider (available in Premium)', '3dprint-lite' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Infills Layout', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[infills_layout]">
								<option <?php if ( $settings['infills_layout']=='lists' ) echo 'selected';?> value="lists"><?php esc_html_e( 'List', '3dprint-lite' );?></option>
								<!--<option <?php if ( $settings['infills_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php esc_html_e( 'Dropdown (Deprecated)', '3dprint-lite' );?></option>-->
								<option <?php if ( $settings['infills_layout']=='dropdown_new' ) echo 'selected';?> value="dropdown_new"><?php esc_html_e( 'Dropdown (New)', '3dprint-lite' );?></option>
							</select> 
						</td>
					</tr>




					<tr>
						<td><?php esc_html_e( 'Show Printers', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_printers]" value="0"><input type="checkbox" name="p3dlite_settings[show_printers]" <?php if ($settings['show_printers']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Materials', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_materials]" value="0"><input type="checkbox" name="p3dlite_settings[show_materials]" <?php if ($settings['show_materials']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Coatings', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_coatings]" value="0"><input type="checkbox" name="p3dlite_settings[show_coatings]" <?php if ($settings['show_coatings']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Show Infills', '3dprint-lite' );?></td>
						<td><input type="hidden" name="p3dlite_settings[show_infills]" value="0"><input type="checkbox" name="p3dlite_settings[show_infills]" <?php if ($settings['show_infills']=='on') echo 'checked';?>></td>
					</tr>

				</table>
				<hr>
				<p><b><?php esc_html_e( 'Form builder', '3dprint-lite' );?></b></p>
				<p>
					Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

				</p>

				<p>In <a target="_blank" href="https://youtu.be/ZB82ozu8I94">this video</a> you can see how to configure NinjaForms integration</p>
				<table>
					<tr>
						<td><?php esc_html_e( 'Use NinjaForms', '3dprint-lite' );?></td>
						<td>
							<input type="hidden" disabled value="0">
							<input type="checkbox" disabled>&nbsp;
							<img class="tooltip" title="<?php htmlentities(esc_html_e( 'Use NinjaForms 3.0+ builder for the price request form.', '3dprint-lite' ));?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'NinjaForms ID', '3dprint-lite' );?></td>
						<td><input id="p3dlite-ninjaforms-shortcode" type="text" placeholder="2" disabled value="">&nbsp;
							<button id="p3dlite-generate-button" type="button" disabled><?php esc_html_e('Generate', '3dprint-lite')?></button>
							<img id="p3dlite-generate-image" style="display:inline-block;visibility:hidden;" alt="Generating" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/ajax-loader-small.gif')); ?>">
						</td>
					</tr>
				</table>



				<hr>
				<p><b><?php esc_html_e( 'File Upload', '3dprint-lite' );?></b></p>
				<table>
					<tr>
						<td><?php esc_html_e( 'Max. File Size', '3dprint-lite' );?></td>
						<td><input size="3" type="text" name="p3dlite_settings[file_max_size]" value="<?php echo (int)$settings['file_max_size'];?>"><?php esc_html_e( 'mb', '3dprint-lite' );?> </td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'File Chunk Size', '3dprint-lite' );?></td>
						<td><input size="3" type="text" name="p3dlite_settings[file_chunk_size]" value="<?php echo (int)$settings['file_chunk_size'];?>"><?php esc_html_e( 'mb', '3dprint-lite' );?> </td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Allowed Extensions', '3dprint-lite' );?></td>
						<td><input size="9" type="text" name="p3dlite_settings[file_extensions]" value="<?php echo esc_attr($settings['file_extensions']);?>"></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Delete files older than', '3dprint-lite' );?></td>
						<td><input size="3" type="text" name="p3dlite_settings[max_days]" value="<?php echo (int)$settings['max_days'];?>"><?php esc_html_e( 'days', '3dprint-lite' );?> </td>
					</tr>
				</table>
				<hr>
				<p><b><?php esc_html_e( 'Other', '3dprint-lite' );?></b></p>
				<table>
					<tr>
						<td><?php esc_html_e( 'Email', '3dprint-lite' );?></td>
						<td><input type="text" placeholder="user@example.com" name="p3dlite_settings[email_address]" value="<?php echo esc_attr($settings['email_address']);?>">&nbsp;
						<img class="tooltip" title="<?php htmlentities(esc_html_e( 'The email where price requests go.', '3dprint-lite' ));?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Items per page', '3dprint-lite' );?></td>
						<td>
							<input size="3" type="text" name="p3dlite_settings[items_per_page]" value="<?php echo (int)$settings['items_per_page'];?>">
							<img class="tooltip" title="<?php esc_attr_e( 'Number of iterms per page in the admin area.', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Load On', '3dprint-lite' );?></td>
						<td>
							<select name="p3dlite_settings[load_everywhere]">
								<option <?php if ( $settings['load_everywhere']=='shortcode' ) echo "selected";?> value="shortcode"><?php esc_html_e('Pages with the shortcode', '3dprint-lite');?></option>
								<option <?php if ( $settings['load_everywhere']=='on' ) echo "selected";?> value="on"><?php esc_html_e('Everywhere', '3dprint-lite');?></option>
							</select>
							<img class="tooltip" title="<?php esc_attr_e( 'Loads css and js files on certain pages of the site.', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>">
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', '3dprint-lite' ) ?>" />
				</p>
		</div>
	</div>
	</form>
</div>
<?php
}

function register_3dprintlite_printers_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_printers') return false;
	if ( !current_user_can('administrator') ) return false;
	$settings=p3dlite_get_option( 'p3dlite_settings' );

	$wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_printers where status=1", ARRAY_A );
	if ($wpdb->num_rows==0) { //should not happen, but let's create a default one, at least one printer is required
		$printer_default_data=array( 'name'=>'At least one active printer is required for the plugin to work', 'status'=>1, 'width'=>300, 'length'=>400, 'height'=>300 );
		$wpdb->insert( $wpdb->prefix . 'p3dlite_printers', $printer_default_data );

	}

	if (isset($_POST['p3dlite_printers_description'])) {
		update_option('p3dlite_printers_description', wp_kses_post(nl2br($_POST['p3dlite_printers_description'])));
	}

	if (isset($_GET['action']) && $_GET['action'] == 'edit') {
		$printer_id = (int)$_GET['printer'];
		$printer_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_printers where id='$printer_id'", ARRAY_A );
		$printer = $printer_result[0];

		$materials=p3dlite_get_option( 'p3dlite_materials' );
		$infills=p3dlite_get_option( 'p3dlite_infills' );

		add_thickbox(); 

//		if (count($_POST)) {
//			$printers=p3dlite_get_option( 'p3dlite_printers' );
//			foreach ($printers as $key => $printer) {
//				wp_set_object_terms( 0, strval($key), 'pa_p3dlite_printer' , false );
//			}
//		}
		include('3dprint-lite-admin-printers-edit.php');
		
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'clone') {
		$printer_id = (int)$_GET['printer'];
		$printer_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_printers where id='$printer_id'", ARRAY_A );
		$clone_data = $printer_result[0];
		unset($clone_data['id']);
		$wpdb->insert($wpdb->prefix."p3dlite_printers", $clone_data);

		wp_redirect( admin_url( 'admin.php?page=p3dlite_printers&action=edit&printer='.(int)$wpdb->insert_id ) );
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'add') {

			$default_printer_data = array(
				'status' => '1',
				'name' => 'New Printer',
				'description' => '',
				'photo' => '',
				'type' => 'fff',
				'full_color' => '1',
				'platform_shape' => 'rectangle',
				'width' => '300',
				'length' => '400',
				'height' => '300',
				'diameter' => '300',
				'min_side' => '1',
				'price' => '0',
				'price_type' => 'box_volume',
				'infills' => '0,10,20,30,40,50,60,70,80,90,100',
				'default_infill' => '20',
				'materials' => "",
				'group_name' => '',
				'sort_order' => '0'
			);
			$wpdb->insert($wpdb->prefix."p3dlite_printers", $default_printer_data);
			wp_redirect( admin_url( 'admin.php?page=p3dlite_printers&action=edit&printer='.(int)$wpdb->insert_id ) );
	}
	else {
		include('3dprint-lite-admin-printers.php');
		$p3dlitep_instance = p3dliteP_Plugin::get_instance();
		$p3dlitep_instance->plugin_settings_page();
	}
}

function register_3dprintlite_materials_page_callback() {
	global $wpdb;

	if ( $_GET['page'] != 'p3dlite_materials') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if (isset($_POST['p3dlite_materials_description'])) {
		update_option('p3dlite_materials_description', wp_kses_post(nl2br($_POST['p3dlite_materials_description'])));
	}

	$wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_materials where status=1", ARRAY_A );
	if ($wpdb->num_rows==0) { //should not happen, but let's create a default one, at least one material is required
		$default_material_data=array(
				'status' => '1',
				'name' => 'At least one active material is required for the plugin to work',
				'description' => '',
				'photo' => '',
				'type' => 'filament',
				'density' => '1.26',
				'length' => '330',
				'diameter' => '1.75',
				'weight' => '1',
				'price' => '0.03',
				'price_type' => 'gram',
				'roll_price' => '20',
				'group_name' => 'PLA',
				'color' => '#08c101',
				'shininess' => 'plastic',
				'glow' => '0',
				'transparency' => 'opaque'
			);
		$wpdb->insert( $wpdb->prefix . 'p3dlite_materials', $default_material_data );
	}


	if (isset($_GET['action']) && $_GET['action'] == 'edit') {

		$material_id = (int)$_GET['material'];
		$material_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_materials where id='$material_id'", ARRAY_A );
		$material = $material_result[0];


		add_thickbox(); 
//		if (count($_POST)) {
//			$materials=p3dlite_get_option( 'p3dlite_materials' );
//			foreach ($materials as $key => $material) {
//				wp_set_object_terms( 0, strval($key), 'pa_p3dlite_material' , false );
//			}
//		}

       		include('3dprint-lite-admin-materials-edit.php');
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'clone') {
		$material_id = (int)$_GET['material'];
		$material_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_materials where id='$material_id'", ARRAY_A );
		$clone_data = $material_result[0];
		unset($clone_data['id']);
		$wpdb->insert($wpdb->prefix."p3dlite_materials", $clone_data);
		$insert_id = $wpdb->insert_id;

		$db_printers=p3dlite_get_option( 'p3dlite_printers' );
		$db_coatings=p3dlite_get_option( 'p3dlite_coatings' );


		foreach ($db_printers as $db_printer) {
			$db_printer_materials = explode(',', $db_printer['materials']);
			if (in_array($material_id, $db_printer_materials)) {
				$db_printer_materials[]=$insert_id;
				$db_printer['materials']=implode(',', $db_printer_materials);
				p3dlite_update_option( 'p3dlite_printers', $db_printer );
			}
		}

		foreach ($db_coatings as $db_coating) {
			$db_coating_materials = explode(',', $db_coating['materials']);
			if (in_array($material_id, $db_coating_materials)) {
				$db_coating_materials[]=$insert_id;
				$db_coating['materials']=implode(',', $db_coating_materials);
				p3dlite_update_option( 'p3dlite_coatings', $db_coating );
			}
		}

		wp_redirect( admin_url( 'admin.php?page=p3dlite_materials&action=edit&material='.(int)$insert_id ) );
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'add') {

			$default_material_data = array(
				'status' => '1',
				'name' => 'New Material',
				'description' => '',
				'photo' => '',
				'type' => 'filament',
				'density' => '1.26',
				'length' => '330',
				'diameter' => '1.75',
				'weight' => '1',
				'price' => '0.03',
				'price_type' => 'gram',
				'roll_price' => '20',
				'group_name' => 'PLA',
				'color' => '#08c101',
				'shininess' => 'plastic',
				'glow' => '0',
				'transparency' => 'opaque'

			);
			$wpdb->insert($wpdb->prefix."p3dlite_materials", $default_material_data);
			wp_redirect( admin_url( 'admin.php?page=p3dlite_materials&action=edit&material='.(int)$wpdb->insert_id ) );
	}
	else {
		include('3dprint-lite-admin-materials.php');
		$p3dlitem_instance = p3dliteM_Plugin::get_instance();
		$p3dlitem_instance->plugin_settings_page();
	}


}


function register_3dprintlite_coatings_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_coatings') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if (isset($_POST['p3dlite_coatings_description'])) {
		update_option('p3dlite_coatings_description', wp_kses_post(nl2br($_POST['p3dlite_coatings_description'])));
	}

	$materials=p3dlite_get_option( 'p3dlite_materials' );

	add_thickbox(); 

//	if (count($_POST)) {
//		$coatings=p3dlite_get_option( 'p3dlite_coatings' );
//		foreach ($coatings as $key => $coating) {
//			wp_set_object_terms( 0, strval($key), 'pa_p3dlite_coating' , false );
//		}
//	}

	if (isset($_GET['action']) && $_GET['action'] == 'edit') {

		$coating_id = (int)$_GET['coating'];
		$coating_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_coatings where id='$coating_id'", ARRAY_A );
		$coating = $coating_result[0];

		add_thickbox(); 
//		if (count($_POST)) {
//			$coatings=p3dlite_get_option( 'p3dlite_coatings' );
//			foreach ($coatings as $key => $coating) {
//				wp_set_object_terms( 0, strval($key), 'pa_p3dlite_coating' , false );
//			}
//		}

       		include('3dprint-lite-admin-coatings-edit.php');
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'clone') {
		$coating_id = (int)$_GET['coating'];
		$coating_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_coatings where id='$coating_id'", ARRAY_A );
		$clone_data = $coating_result[0];
		unset($clone_data['id']);
		$wpdb->insert($wpdb->prefix."p3dlite_coatings", $clone_data);

		wp_redirect( admin_url( 'admin.php?page=p3dlite_coatings&action=edit&coating='.(int)$wpdb->insert_id ) );
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'add') {

			$default_coating_data = array(
				'status' => '1',
				'name' => 'New Coating',
				'description' => '',
				'photo' => '',
				'color' => '#08c101',
			);
			$wpdb->insert($wpdb->prefix."p3dlite_coatings", $default_coating_data);
			wp_redirect( admin_url( 'admin.php?page=p3dlite_coatings&action=edit&coating='.(int)$wpdb->insert_id ) );
	}
	else {
		include('3dprint-lite-admin-coatings.php');
		$p3dlitec_instance = p3dliteC_Plugin::get_instance();
		$p3dlitec_instance->plugin_settings_page();
	}

}



function register_3dprintlite_price_requests_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_price_requests') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if (isset($_GET['action']) && $_GET['action'] == 'edit') {
		$price_request_id = (int)$_GET['price_request'];

		if (empty($price_request_id)) {
			wp_redirect( admin_url( 'admin.php?page=p3dlite_price_requests' ) );
		}

		$price_request_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_price_requests where id='$price_request_id'", ARRAY_A );
		$price_request = $price_request_result[0];

       		include('3dprint-lite-admin-price-requests-edit.php');
	}
	else {
		include('3dprint-lite-admin-price-requests.php');
		$p3dlitepr_instance = p3dlitePR_Plugin::get_instance();
		$p3dlitepr_instance->plugin_settings_page();
	}



#	p3dlite_check_install();


}

function register_3dprintlite_email_templates_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_email_templates') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if ( isset( $_POST['p3dlite_email_templates'] ) && !empty( $_POST['p3dlite_email_templates'] ) ) {
		if ( ! isset( $_POST['p3d_email_templates_edit'] ) || ! wp_verify_nonce( $_POST['p3d_email_templates_edit'], 'update' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		}

		$templates_update = p3dlite_sanitize_templates($_POST['p3dlite_email_templates']);

		if (isset($templates_update['client_email_preserve_html']) && $templates_update['client_email_preserve_html']=='on') {
			$templates_update['client_email_body'] = $templates_update['client_email_body_raw'];
		}
		else {
			$templates_update['client_email_body'] = nl2br($templates_update['client_email_body']);
		}
		if (isset($templates_update['admin_email_preserve_html']) && $templates_update['admin_email_preserve_html']=='on') {
			$templates_update['admin_email_body'] = $templates_update['admin_email_body_raw'];
		}
		else {
			$templates_update['admin_email_body'] = nl2br($templates_update['admin_email_body']);
		}
		update_option( 'p3dlite_email_templates', $templates_update );
	}
	$current_templates = get_option( 'p3dlite_email_templates' );



#p3dlite_check_install();
?>
	<form method="post" action="admin.php?page=p3dlite_email_templates" enctype="multipart/form-data">
	<?php    wp_nonce_field( 'update', 'p3d_email_templates_edit' ); ?>
	<div id="p3dlite_tabs">

		<ul>
			<li><a href="#3dp_tabs-0"><?php esc_html_e( 'Email Templates', '3dprint-lite' );?></a></li>
		</ul>
		<div id="p3dlite_tabs-0">
				<p><b><?php esc_html_e('E-mail to admin', '3dprint-lite'); ?>:</b></p>

				<p><?php esc_html_e('Available shortcodes', '3dprint-lite'); ?>:<?php echo implode(', ', array('[customer_email]','[printer_name]','[material_name]','[coating_name]','[infill]','[quantity]', '[model_file]','[unit]','[resize_scale]','[dimensions]','[estimated_price]','[estimated_price_total]','[customer_comments]','[price_requests_link]'));?></p>

				<p><?php esc_html_e('From', '3dprint-lite');?>:<input type="text" size="50" name="p3dlite_email_templates[admin_email_from]" value="<?php echo esc_attr($current_templates['admin_email_from']);?>" />&nbsp;<i>Name Surname &#x3C;me@example.net&#x3E;</i>
					<img class="tooltip" title="<?php esc_attr_e( 'Please note that if you put a domain name different from your site\'s domain  the email may go to spam.', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>"></td> 
				</p>

				<p><?php esc_html_e('Subject', '3dprint-lite');?>:<input type="text" size="50" name="p3dlite_email_templates[admin_email_subject]" value="<?php echo esc_attr($current_templates['admin_email_subject']);?>" /></p>
				<p><?php esc_html_e('Preserve HTML', '3dprint-lite');?>:<input type="checkbox" name="p3dlite_email_templates[admin_email_preserve_html]" onchange="p3dliteToggleAdminBodyRaw(this);" <?php if (isset($current_templates['admin_email_preserve_html']) && $current_templates['admin_email_preserve_html']=='on') echo 'checked="checked"';?>/></p>
				<div style="<?php if (isset($current_templates['admin_email_preserve_html']) && $current_templates['admin_email_preserve_html']=='on') echo 'display:none;';?>" id="p3dlite_wpeditor_admin_wrap">
				<?php wp_editor(wpautop(stripslashes($current_templates['admin_email_body'])), 'admin_email_body', array('textarea_name' => 'p3dlite_email_templates[admin_email_body]', 'editor_height'=>100) ); ?>
				</div>
				<textarea class="wp-editor-area" style="height: 200px;width:100%;<?php if (!isset($current_templates['admin_email_preserve_html']) || $current_templates['admin_email_preserve_html']!='on') echo 'display:none;';?>" autocomplete="off" cols="40" name="p3dlite_email_templates[admin_email_body_raw]" id="admin_email_body_raw">
				<?php
					echo esc_html(stripslashes($current_templates['admin_email_body']));
				?>
				</textarea>
				<p><b><?php esc_html_e('E-mail to client', '3dprint-lite'); ?>:</b></p>
				<p><?php esc_html_e('Available shortcodes', '3dprint-lite'); ?>:<?php echo implode(', ', array('[printer_name]', '[quantity]', '[material_name]', '[coating_name]', '[infill]', '[model_file]', '[dimensions]', '[weight]','[price]', '[price_total]', '[admin_comments]'));?></p>

				<p><?php esc_html_e('From', '3dprint-lite');?>:<input type="text" size="50" name="p3dlite_email_templates[client_email_from]" value="<?php echo esc_attr($current_templates['client_email_from']);?>" />&nbsp;<i>Name Surname &#x3C;me@example.net&#x3E;</i>
					<img class="tooltip" title="<?php esc_attr_e( 'Please note that if you put a domain name different from your site\'s domain the email may go to spam.', '3dprint-lite' );?>" src="<?php echo esc_url(plugins_url( '3dprint-lite/images/question.png' )); ?>"></td> 
				</p>

				<p><?php esc_html_e('Subject', '3dprint-lite');?>:<input type="text" size="50" name="p3dlite_email_templates[client_email_subject]" value="<?php echo esc_attr($current_templates['client_email_subject']);?>" /></p>
				<p><?php esc_html_e('Preserve HTML', '3dprint-lite');?>:<input type="checkbox" name="p3dlite_email_templates[client_email_preserve_html]" onchange="p3dliteToggleClientBodyRaw(this);" <?php if (isset($current_templates['client_email_preserve_html']) && $current_templates['client_email_preserve_html']=='on') echo 'checked="checked"';?>/></p>
				<div style="<?php if (isset($current_templates['client_email_preserve_html']) && $current_templates['client_email_preserve_html']=='on') echo 'display:none;';?>" id="p3dlite_wpeditor_client_wrap">
				<?php wp_editor(wpautop(stripslashes($current_templates['client_email_body'])), 'client_email_body', array('textarea_name' => 'p3dlite_email_templates[client_email_body]', 'editor_height'=>100) ); ?>
				</div>
				<textarea class="wp-editor-area" style="height: 200px;width:100%;<?php if (!isset($current_templates['client_email_preserve_html']) || $current_templates['client_email_preserve_html']!='on') echo 'display:none;';?>" autocomplete="off" cols="40" name="p3dlite_email_templates[client_email_body_raw]" id="client_email_body_raw">
				<?php
					echo esc_html(stripslashes($current_templates['client_email_body']));
				?>
				</textarea>


				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save', '3dprint-lite' ) ?>" />
				</p>
		</div>
	</div>
	</form>
<?php
}

function register_3dprintlite_discounts_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_discounts') return false;
	if ( !current_user_can('administrator') ) return false;


?>

Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version


<?php
}


/*
function register_3dprintlite_infills_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_infills') return false;
	if ( !current_user_can('administrator') ) return false;


?>
<p>
Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

</p>
<p>
	Requires <a href="https://www.wp3dprinting.com/feature-comparison/">a subscription!</a>

</p>
<p>
Screenshot:
</p>
<img src="<?php echo esc_url(plugins_url( '3dprint-lite/images/infills.jpg' )); ?>">

<?php
}
*/

function register_3dprintlite_infills_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_infills') return false;
	if ( !current_user_can('administrator') ) return false;

	$settings=p3dlite_get_option( 'p3dlite_settings' );

	if (isset($_POST['p3dlite_infills_description'])) {
		update_option('p3dlite_infills_description', wp_kses_post(nl2br($_POST['p3dlite_infills_description'])));
	}


	add_thickbox(); 

//	if (count($_POST)) {
//		$infills=p3dlite_get_option( 'p3dlite_infills' );
//		foreach ($infills as $key => $infill) {
//			wp_set_object_terms( 0, strval($key), 'pa_p3dlite_infill' , false );
//		}
//	}

	if (isset($_GET['action']) && $_GET['action'] == 'edit') {

		$infill_id = (int)$_GET['infill'];
		$infill_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_infills where id='$infill_id'", ARRAY_A );
		$infill = $infill_result[0];

		add_thickbox(); 
//		if (count($_POST)) {
//			$infills=p3dlite_get_option( 'p3dlite_infills' );
//			foreach ($infills as $key => $infill) {
//				wp_set_object_terms( 0, strval($key), 'pa_p3dlite_infill' , false );
//			}
//		}

       		include('3dprint-lite-admin-infills-edit.php');
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'clone') {
		$infill_id = (int)$_GET['infill'];
		$infill_result = $wpdb->get_results( "select * from {$wpdb->prefix}p3dlite_infills where id='$infill_id'", ARRAY_A );
		$clone_data = $infill_result[0];
		unset($clone_data['id']);
		$wpdb->insert($wpdb->prefix."p3dlite_infills", $clone_data);

		wp_redirect( admin_url( 'admin.php?page=p3dlite_infills&action=edit&infill='.(int)$wpdb->insert_id ) );
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'add') {

			$default_infill_data = array(
				'status' => '1',
				'name' => 'New infill',
				'description' => '',
				'photo' => '',
			);
			$wpdb->insert($wpdb->prefix."p3dlite_infills", $default_infill_data);
			wp_redirect( admin_url( 'admin.php?page=p3dlite_infills&action=edit&infill='.(int)$wpdb->insert_id ) );
	}
	else {
		include('3dprint-lite-admin-infills.php');
		$p3dlitei_instance = p3dliteI_Plugin::get_instance();
		$p3dlitei_instance->plugin_settings_page();
	}

}

function register_3dprintlite_postprocessings_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_postprocessings') return false;
	if ( !current_user_can('administrator') ) return false;


?>
<p>
	Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

</p>
<p>
Screenshot:
</p>
<img src="<?php echo esc_url(plugins_url( '3dprint-lite/images/postprocessing.jpg' )); ?>">
<?php
}

function register_3dprintlite_file_manager_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != 'p3dlite_file_manager') return false;
	if ( !current_user_can('administrator') ) return false;


?>
<p>
	Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version

</p>
<p>
Screenshot:
</p>
<img src="<?php echo esc_url(plugins_url( '3dprint-lite/images/file_manager.jpg' )); ?>">
<?php
}

?>