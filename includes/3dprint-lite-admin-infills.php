<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class p3dliteInfills_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {


		parent::__construct( [
			'singular' => esc_html__( 'Infill', '3dprint-lite' ), //singular name of the listed records
			'plural'   => esc_html__( 'Infills', '3dprint-lite' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	public static function apply_filter($sql) {
		global $wpdb;

		$where_str = ' where 1=1 ';
		if (isset($_REQUEST['infill_text']) && strlen($_REQUEST['infill_text'])>0) {
			$infill_text = $wpdb->esc_like( sanitize_text_field( wp_unslash(trim($_REQUEST['infill_text']))));
			$where_str .= " and ( name like '%$infill_text%' OR description like '%$infill_text%' ) ";
		}


		$sql.=$where_str;

		return $sql;
	}

	/**
	 * Retrieve p3dinfills data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_p3dinfills( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

        	$sql = "select * from ".$wpdb->prefix."p3dlite_infills ";

		$sql = self::apply_filter($sql);


		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . $this->validate_column( esc_sql($_REQUEST['orderby'] ));
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . $this->validate_order( esc_sql($_REQUEST['order'] ) ): ' ASC';
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
	 * Delete a infill record.
	 *
	 * @param int $id infill ID
	 */
	public static function delete_infill( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}p3dlite_infills",
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

        	$sql = "select count(*) from ".$wpdb->prefix."p3dlite_infills ";
		$sql = self::apply_filter($sql);


		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no infill data is available */
	public function no_items() {
		esc_html_e( 'No infills avaliable.', '3dprint-lite' );
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


		switch ( $column_name ) {
			case 'color':
				return '<div class="group-color-sample" style="background-color:'.esc_attr($item[ $column_name ]).';"></div>';
			break;
			case 'status':
				if ((int)$item[ $column_name ] == 1) return '<span class="p3dlite-active">'.esc_html__('Active', '3dprint-lite').'</span>';
				if ((int)$item[ $column_name ] == 0) return '<span class="p3dlite-inactive">'.esc_html__('Inactive', '3dprint-lite').'</span>';
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
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_infill' );
		$edit_nonce = wp_create_nonce( 'sp_edit_infill' );
		$clone_nonce = wp_create_nonce( 'sp_clone_infill' );

		$title = '<strong>' . esc_html($item['name']) . '</strong>';

		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&infill=%s&_wpnonce=%s">'.esc_html__("Edit", '3dprint-lite').'</a>', 'p3dlite_infills', 'edit', absint( $item['id'] ), $edit_nonce ),
			'clone' => sprintf( '<a href="?page=%s&action=%s&infill=%s&_wpnonce=%s">'.esc_html__("Clone", '3dprint-lite').'</a>', 'p3dlite_infills', 'clone', absint( $item['id'] ), $clone_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&infill=%s&_wpnonce=%s">'.esc_html__("Delete", '3dprint-lite').'</a>', 'p3dlite_infills', 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
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
			'name'    => esc_html__( 'Name', '3dprint-lite' ),
			'infill'    => esc_html__( 'Value', '3dprint-lite' ),
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
			'name' => array( 'name', false ),
			'infill' => array( 'infill', false ),
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
			'bulk-activate' => 'Activate',
			'bulk-deactivate' => 'Deactivate',
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

		$items = self::get_p3dinfills( $per_page, $current_page );

		$this->items = $items;
	}

	public function process_bulk_action() {
		global $wpdb;

		if ( 'edit' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = sanitize_key( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_edit_infill' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				wp_redirect( admin_url( 'admin.php?page=p3dlite_infills&action=edit&infill_id='.(int)$_GET['infill'] ) );
				exit;
			}

		}

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = sanitize_key( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_infill' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_infill( absint( $_GET['infill'] ) );

				wp_redirect( admin_url( 'admin.php?page=p3dlite_infills' ) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )	
		  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {

			$delete_ids = array_map('intval',  $_POST['bulk-action']);

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_infill( $id );

			}
			wp_redirect( admin_url( 'admin.php?page=p3dlite_infills' ) );
			exit;
		}
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-activate' ) 
		  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-activate' ) ) {
			$activate_ids = array_map('intval',  $_POST['bulk-action']);
			foreach ($activate_ids as $id) {
				$wpdb->update($wpdb->prefix.'p3dlite_infills', array('status'=>1), array('id'=>$id));
			}
			wp_redirect( admin_url( 'admin.php?page=p3dlite_infills' ) );
		}

		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-deactivate' ) 
		  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-deactivate' ) ) {
			$deactivate_ids = array_map('intval',  $_POST['bulk-action']);

			foreach ($deactivate_ids as $id) {
				$wpdb->update($wpdb->prefix.'p3dlite_infills', array('status'=>0), array('id'=>$id));

			}
			wp_redirect( admin_url( 'admin.php?page=p3dlite_infills' ) );
		}
	}

}

class p3dliteI_Plugin {

	// class instance
	static $instance;

	// infill WP_List_Table object
	public $p3dinfills_obj;

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

		?>
		<div class="wrap">
			<h2><?php esc_html_e('Infills', '3dprint-lite');?> </h2>

			<div id="poststuff p3d-lite-poststuff">
				<button class="button-secondary" type="button" onclick="location.href='<?php echo esc_url(admin_url( 'admin.php?page=p3dlite_infills&action=add' ));?>'"><b><?php esc_html_e('Add Infill', '3dprint-lite');?></b></button>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable p3d-lite-table">
							<form name="infill_form" method="post">
								<?php esc_html_e('Search in name or description', '3dprint-lite');?>: <input name="infill_text" value="<?php if(isset($_REQUEST['infill_text'])) echo esc_attr(esc_html($_REQUEST['infill_text']));?>">&nbsp;
								<input type="submit" value="<?php esc_html_e('Search', '3dprint-lite');?>">
								<?php
								$this->p3dinfills_obj->prepare_items();
								$this->p3dinfills_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
				<button class="button-secondary" type="button" onclick="location.href='<?php echo esc_url(admin_url( 'admin.php?page=p3dlite_infills&action=add' ));?>'"><b><?php esc_html_e('Add Infill', '3dprint-lite');?></b></button>
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
			'label'   => 'p3dInfills',
			'default' => 10,
			'option'  => 'p3dinfills_per_page'
		];

		add_screen_option( $option, $args );

		$this->p3dinfills_obj = new p3dliteInfills_List();
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