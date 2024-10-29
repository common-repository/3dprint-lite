<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( isset($_POST['action']) && $_POST['action']=='update' && isset( $_POST['p3dlite_infill_name'] ) && count( $_POST['p3dlite_infill_name'] )>0 ) {

		if ( ! isset( $_POST['p3d_infills_edit'] ) || ! wp_verify_nonce( $_POST['p3d_infills_edit'], 'update' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		}

		$infills = array();
		foreach ( $_POST['p3dlite_infill_name'] as $i => $infill ) {
			if (empty($_POST['p3dlite_infill_name'][$i])) continue;
			$infills[$i]['id']=(int)$i;
			$infills[$i]['status']=(int)$_POST['p3dlite_infill_status'][$i];
			$infills[$i]['name']=sanitize_text_field( $_POST['p3dlite_infill_name'][$i] );
			$infills[$i]['infill']=sanitize_text_field( (float)$_POST['p3dlite_infill_infill'][$i] );
			$infills[$i]['description']=sanitize_textarea_field( $_POST['p3dlite_infill_description'][$i] );
			$infills[$i]['photo']=sanitize_text_field( $_POST['p3dlite_infill_photo'][$i] );
			$infills[$i]['price']= (strlen(sanitize_text_field($_POST['p3dlite_infill_price'][$i])) ? sanitize_text_field(p3dlite_fix_price($_POST['p3dlite_infill_price'][$i])) : 0);
			$infills[$i]['price_type']=sanitize_text_field($_POST['p3dlite_infill_price_type'][$i]);
			$infills[$i]['price1']= (strlen(sanitize_text_field($_POST['p3dlite_infill_price1'][$i])) ? sanitize_text_field(p3dlite_fix_price($_POST['p3dlite_infill_price1'][$i])) : 0);
			$infills[$i]['price_type1']=sanitize_text_field($_POST['p3dlite_infill_price_type1'][$i]);


			if (isset($_FILES['p3dlite_infill_photo_upload']['tmp_name'][$i]) && strlen($_FILES['p3dlite_infill_photo_upload']['tmp_name'][$i])>0) {

				$uploaded_file = p3dlite_upload_file('p3dlite_infill_photo_upload', $i);
				$infills[$i]['photo']=sanitize_text_field(str_replace('http:','',$uploaded_file['url']));
			}


		}
		foreach ($infills as $infill) {
			p3dlite_update_option( 'p3dlite_infills', $infill );
		}
		wp_redirect( admin_url( 'admin.php?page=p3dlite_infills&action=edit&infill='.(int)$_GET['infill'] ) );
	}

#	$group_names = $wpdb->get_results( "select distinct (group_name) from {$wpdb->prefix}p3d_infills", 'ARRAY_A' );
#	$groups = array();

?>

	<form method="post" action="admin.php?page=p3dlite_infills&action=edit&infill=<?php echo (int)$_GET['infill']?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="update" />
				<?php    wp_nonce_field( 'update', 'p3d_infills_edit' ); ?>
				<br style="clear:both">
				<button class="button-secondary" type="button" onclick="location.href='<?php echo esc_url(admin_url( 'admin.php?page=p3dlite_infills' ));?>'"><b>&#8592;<?php esc_html_e('Back to infills', '3dprint-lite');?></b></button>
				<h3><?php echo '#'.(int)$infill['id'].' '.esc_html($infill['name']);?></h3>
				<div>

				<table id="infill-<?php echo (int)$infill['id'];?>" class="form-table infill">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
				 	<tr>
						<td colspan="2"><span class="item_id"><?php echo "<b>ID #".(int)$infill['id']."</b>";?></span></td>
				 	</tr>
				 	<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Infill Name', '3dprint-lite' );?></th>
						<td>
							<input type="text" name="p3dlite_infill_name[<?php echo (int)$infill['id'];?>]" value="<?php echo esc_attr($infill['name']);?>" />&nbsp;

						</td>
					</tr>
				 	<tr valign="top">
						<th scope="row"><?php _e( 'Infill Value', '3dprint-lite' );?></th>
						<td>
							<input type="text" name="p3dlite_infill_infill[<?php echo (int)$infill['id'];?>]" value="<?php echo esc_attr($infill['infill']);?>" />&nbsp;
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php esc_html_e( 'Infill Description', '3dprint-lite' ); ?>
						</th>
						<td>
							<textarea name="p3dlite_infill_description[<?php echo (int)$infill['id'];?>]"/><?php if (isset($infill['description'])) echo esc_html($infill['description']);?></textarea>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Photo', '3dprint-lite' );?></th>
						<td>
						<?php
						if (isset($infill['photo'])) {
						?>
							<a href="<?php echo esc_url($infill['photo']);?>"><img class="p3dlite-preview" src="<?php echo esc_url($infill['photo']);?>"></a>
						<?php
						}
						?>
							<input type="text" name="p3dlite_infill_photo[<?php echo (int)$infill['id'];?>]" value="<?php if (isset($infill['photo'])) echo esc_url($infill['photo']);?>" />
							<input type="file" name="p3dlite_infill_photo_upload[<?php echo (int)$infill['id'];?>]" accept="image/*">
						</td>

					</tr>
					<tr>
						<th scope="row"><?php _e( 'Enabled', '3dprint-lite' );?></th>
						<td>
							<select name="p3dlite_infill_status[<?php echo (int)$infill['id'];?>]">
								<option <?php if ( $infill['status']=='1' ) echo "selected";?> value="1"><?php _e('Yes', '3dprint-lite');?></option>
								<option <?php if ( $infill['status']=='0' ) echo "selected";?> value="0"><?php _e('No', '3dprint-lite');?></option>
							</select>

						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Price', '3dprint-lite' ); ?></th>
						<td>
							<input type="text" class="p3dlite_price" name="p3dlite_infill_price[<?php echo (int)$infill['id'];?>]" value="<?php echo esc_attr($infill['price']);?>" /><?php echo esc_html($settings['currency']); ?> <?php esc_html_e('per', '3dprint-lite');?> 
							<select name="p3dlite_infill_price_type[<?php echo (int)$infill['id'];?>]">
								<option <?php if ($infill['price_type']=='fixed') echo 'selected'; ?> value="fixed"><?php esc_html_e('Fixed Price', '3dprint-lite');?></option>
								<option <?php if ($infill['price_type']=='pct_mat' ) echo "selected";?> value="pct_mat"><?php esc_html_e('+% to material price', '3dprint-lite');?></option>
								<option <?php if ($infill['price_type']=='pct' ) echo "selected";?> value="pct"><?php esc_html_e('+% to total price', '3dprint-lite');?></option>
							</select>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Extra Price', '3dprint-lite' ); ?></th>
						<td>
							<input type="text" class="p3dlite_price" name="p3dlite_infill_price1[<?php echo (int)$infill['id'];?>]" value="<?php echo esc_attr($infill['price1']);?>" /><?php echo esc_html($settings['currency']); ?> <?php esc_html_e('per', '3dprint-lite');?> 
							<select name="p3dlite_infill_price_type1[<?php echo (int)$infill['id'];?>]">
								<option <?php if ($infill['price_type']=='fixed') echo 'selected'; ?> value="fixed"><?php esc_html_e('Fixed Price', '3dprint-lite');?></option>
								<option <?php if ($infill['price_type']=='pct_mat' ) echo "selected";?> value="pct_mat"><?php esc_html_e('+% to material price', '3dprint-lite');?></option>
								<option <?php if ($infill['price_type']=='pct' ) echo "selected";?> value="pct"><?php esc_html_e('+% to total price', '3dprint-lite');?></option>
							</select>

						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Sort Order', '3dprint-lite' ); ?></th>
						<td><input type="text" disabled />
							Available in <a href="http://www.wp3dprinting.com/product/request-a-quote/">Premium</a> version
						</td>
					</tr>


				</table>

				</div>

				<br style="clear:both">

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', '3dprint-lite' ) ?>" />
				</p>

	</form>