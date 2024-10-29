<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class p3dlitePriceRequests_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {


		parent::__construct( [
			'singular' => esc_html__( 'Price Request', '3dprint-lite' ), //singular name of the listed records
			'plural'   => esc_html__( 'Price Requests', '3dprint-lite' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	public static function apply_filter($sql) {
		global $wpdb;

		$settings=p3dlite_get_option( 'p3dlite_settings' );

		$where_str = ' where 1=1 ';
		if (isset($_REQUEST['price_request_text']) && strlen($_REQUEST['price_request_text'])>0) {

			$price_request_text = $wpdb->esc_like( sanitize_text_field(trim($_REQUEST['price_request_text'])));
			$where_str .= " and ( original_filename like '%$price_request_text%' OR request_comment like '%$price_request_text%' ) ";

		}

		$sql.=$where_str;


		$sql .= " group by id ";



		return $sql;
	}

	/**
	 * Retrieve p3dliteprice_requests data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_p3dliteprice_requests( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

        	$sql = "select id, printer, material, coating, infill, quantity, infill, scale, ts, email as email_address, original_filename, thumbnail_url as image, attributes, estimated_price, sum(estimated_price) as estimated_price_total, estimated_price_currency, price, sum(price) as price_total, status, request_key from ".$wpdb->prefix."p3dlite_price_requests";

		$sql = self::apply_filter($sql);



		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . $this->validate_column( esc_sql($_REQUEST['orderby'] ));
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . $this->validate_order( esc_sql($_REQUEST['order'] )) : ' ASC';
		}
		else {
			$sql .= ' ORDER BY id desc ';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;



		$result = $wpdb->get_results( $sql, 'ARRAY_A' );


		return $result;
	}

	public function validate_column( $column_name ) {
		$sortable_columns = $this->get_sortable_columns();

		if (in_array($column_name, array_keys($sortable_columns))) return $column_name;
		else return 'id';
	}
	public function validate_order( $order ) {
		if (in_array(strtolower($order), array('asc', 'desc'))) return $order;
		else return 'ASC';
	}


	/**
	 * Delete a price_request record.
	 *
	 * @param int $id price_request ID
	 */
	public static function delete_price_request( $id ) {
		global $wpdb;
		$settings=p3dlite_get_option( 'p3dlite_settings' );

		$wpdb->delete(
			"{$wpdb->prefix}p3dlite_price_requests",
			[ 'id' => $id ],
			[ '%d' ]
		);

	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$where_str='';

        	$sql = "select count(*) from ".$wpdb->prefix."p3dlite_price_requests ";
		//$sql = self::apply_filter($sql);

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no price_request data is available */
	public function no_items() {
		esc_html_e( 'No price requests avaliable.', '3dprint-lite' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		global $wpdb;
		$settings=p3dlite_get_option( 'p3dlite_settings' );

		switch ( $column_name ) {


			case 'image':

				$image_html = '<a href="'.esc_url($item['image']).'"><img class="p3dlite-thumb" src="'.esc_url($item['image']).'"></a>';

				return $image_html;
			break; 
			case 'estimated_price':

				$price_html = p3dlite_format_price($item[ $column_name ], $settings['currency'], $settings['currency_position']);

				return $price_html;
			break; 
			case 'price':

				$price_html = p3dlite_format_price($item[ $column_name ], $settings['currency'], $settings['currency_position']);

				return $price_html;
			break; 
			case 'quantity':

				$quantity_html = '&#10005;'.$item[$column_name];

				return $quantity_html;

			break; 
			case 'printer':

				$printer_html = $item[$column_name];

				return $printer_html;

			break; 

			case 'material':

				$material_html = $item[$column_name];

				return $material_html;

			break; 
			case 'coating':

				$coating_html = $item[$column_name];

				return $coating_html;

			break; 

			case 'infill':

				$infill_html = $item[$column_name].'%';

				return $infill_html;

			break; 


			case 'price':
				if ($item[ $column_name ]>0)
					return p3dlite_format_price($item[ $column_name ], $settings['currency'], $settings['currency_position']);
				else {
					return '';
				}
			break; 

			case 'status':

				$request_status = (int)$item[ $column_name ];

				if ($request_status == 3) return '<span class="p3dlite-failed-email">'.esc_html__('Failed to send out e-mail', '3dprint-lite').'</span>';
				if ($request_status == 2) return '<span class="p3dlite-order-placed">'.esc_html__('Order placed', '3dprint-lite').'</span>';
				if ($request_status == 1) return '<span class="p3dlite-quote-sent">'.esc_html__('Quote sent', '3dprint-lite').'</span>';
				if ($request_status == 0) return '<span class="p3dlite-request-received">'.esc_html__('Request received', '3dprint-lite').'</span>';
			break;
			default:
#				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
				return $item[ $column_name ];
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-action[]" value="%s" />', esc_attr($item['id'])
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_original_filename( $item ) {
		global $wpdb;
		$settings=p3dlite_get_option( 'p3dlite_settings' );
		$delete_nonce = wp_create_nonce( 'sp_delete_price_request' );
		$edit_nonce = wp_create_nonce( 'sp_edit_price_request' );
		$clone_nonce = wp_create_nonce( 'sp_clone_price_request' );

//		$title = '<strong>' . $item['original_filename'] . '</strong>';



		$name_html = $item[ 'original_filename' ];


//		return $name_html;


		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&price_request=%s&_wpnonce=%s">'.esc_html__("Edit", '3dprint-lite').'</a>', 'p3dlite_price_requests', 'edit', absint( $item['id'] ), $edit_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&price_request=%s&_wpnonce=%s">'.esc_html__("Delete", '3dprint-lite').'</a>', 'p3dlite_price_requests', 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $name_html . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'id'    => esc_html__( 'ID', '3dprint-lite' ),
			'image'    => esc_html__( 'Image', '3dprint-lite' ),
			'original_filename'    => esc_html__( 'File name', '3dprint-lite' ),
			'quantity'    => esc_html__( 'QTY', '3dprint-lite' ),
			'printer'    => esc_html__( 'Printer', '3dprint-lite' ),
			'material'    => esc_html__( 'Material', '3dprint-lite' ),
			'coating'    => esc_html__( 'Coating', '3dprint-lite' ),
			'infill'    => esc_html__( 'Infill', '3dprint-lite' ),
			'estimated_price'    => esc_html__( 'Estimated Price', '3dprint-lite' ),
			'price'    => esc_html__( 'Set Price', '3dprint-lite' ),
			'email_address'    => esc_html__( 'E-mail', '3dprint-lite' ),
			'ts'    => esc_html__( 'Date', '3dprint-lite' ),
			'status'    => esc_html__( 'Status', '3dprint-lite' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id' => array( 'id', false ),
			'original_filename' => array( 'original_filename', false ),
			'quantity' => array( 'quantity', false ),
			'printer' => array( 'printer', false ),
			'material' => array( 'material', false ),
			'coating' => array( 'coating', false ),
			'infill' => array( 'infill', false ),
			'estimated_price' => array( 'estimated_price', false ),
			'email_address' => array( 'email_address', false ),
			'price' => array( 'price', false ),
			'ts' => array( 'ts', false ),
			'status' => array( 'status', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$settings=p3dlite_get_option( 'p3dlite_settings' );
//		$this->_column_headers = $this->get_column_info();
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);



		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = (int)$settings['items_per_page'];
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$items = self::get_p3dliteprice_requests( $per_page, $current_page );

		$this->items = $items;
	}

	public function process_bulk_action() {
		global $wpdb;

		if ( 'edit' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = sanitize_key( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_edit_price_request' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				wp_redirect( admin_url( 'admin.php?page=p3dlite_price_requests&action=edit&price_request_id='.(int)$_GET['price_request'] ) );
				exit;
			}

		}

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = sanitize_key( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_price_request' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_price_request( absint( $_GET['price_request'] ) );

				wp_redirect( admin_url( 'admin.php?page=p3dlite_price_requests' ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )	
		  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {

			$delete_ids = array_map('intval',  $_POST['bulk-action']);

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_price_request( $id );

			}
			wp_redirect( admin_url( 'admin.php?page=p3dlite_price_requests' ) );
			exit;
		}


	}

}

class p3dlitePR_Plugin {

	// class instance
	static $instance;

	// price_request WP_List_Table object
	public $p3dliteprice_requests_obj;


	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );

		$this->screen_option();
	}


	public static function set_screen( $status, $option, $value ) {

		return $value;
	}

	public function plugin_menu() {


	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		$settings=p3dlite_get_option( 'p3dlite_settings' );
		?>
		<div class="wrap">
			<h2><?php esc_html_e('Price Requests', '3dprint-lite');?> </h2>

			<div id="poststuff p3dlite-poststuff">

				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable p3dlite-table">
							<form name="price_request_form" method="post">

								<?php
								$this->p3dliteprice_requests_obj->prepare_items();
								$this->p3dliteprice_requests_obj->display(); 
								?>

							</form>
							<div id="p3dlite-price-request-totals">
							<?php

								$estimated_total = 0;
								$set_total = 0;
								foreach ($this->p3dliteprice_requests_obj->items as $price_request) {
									$currency_rate = 1;
									if (isset($price_request['estimated_price_currency']) && strlen($price_request['estimated_price_currency'])) {
										$currency_rate = p3dlite_get_currency_rate($price_request['estimated_price_currency']);
									}

									$estimated_total+=$price_request['estimated_price_total']*$price_request['quantity']*$currency_rate;
									$set_total+=$price_request['price_total']*$price_request['quantity']*$currency_rate;
								}

								$estimated_total_html = p3dlite_format_price($estimated_total, $settings['currency'], $settings['currency_position']);
								$set_total_html = p3dlite_format_price($set_total, $settings['currency'], $settings['currency_position']);

								echo "<p>".esc_html__('Estimated Price Total', '3dprint-lite').": ".esc_html($estimated_total_html)." </p>";
								echo "<p>".esc_html__('Set Price Total' ,'3dprint-lite').": ".esc_html($set_total_html)." </p>";
							?>
							</div>
						</div>

					</div>
				</div>

				<br class="clear">
<?php
#print_r(self::$current_result);
?>

			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'p3dlitePriceRequests',
			'default' => 10,
			'option'  => 'p3dliteprice_requests_per_page'
		];

		add_screen_option( $option, $args );

		$this->p3dliteprice_requests_obj = new p3dlitePriceRequests_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
?>