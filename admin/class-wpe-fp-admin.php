<?php
/**
 * Admin area functionality for this plugin.
 *
 * @package    WPE_FP
 * @subpackage WPE_FP/admin
 * @author     Jeff Ward <hi@hello-jeff.com>
 */
class WPE_FP_Admin {

	/////////////////////////
	# Protected constants
	/////////////////////////

	# ID of this plugin.
	private $plugin_name;

	# Version number of this plugin.
	private $version;

	# Assets URL for this plugin.
	private $assets_url;

	# Assets directory for this plugin.
	private $assets_dir;

	//////////////////////////////
	# Constructor function
	//////////////////////////////

	/**
	 * Initialize the class and set its properties.
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $assets_url = null, $assets_dir = null ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->assets_url = (!empty($assets_url)) ? $assets_url : plugin_dir_url( __FILE__ );
		$this->assets_dir = (!empty($assets_dir)) ? $assets_dir : plugin_dir_path( __FILE__ );

	}

	/////////////////////////////////////
	# Action: Register Admin styles
	/////////////////////////////////////

	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in WPE_FP_Actions as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPE_FP_Actions will then create the relationship
		 * between the defined hooks and the functions defined in this class.
	 	 *
	 	 * @since    1.0.0
		 */

		wp_enqueue_style( $this->plugin_name, $this->assets_url . 'css/wpe-fp-admin.css', array(), $this->version, 'all' );

	}

	/////////////////////////////////////
	# Action: Register Admin scripts
	/////////////////////////////////////

	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WPE_FP_Actions as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPE_FP_Actions will then create the relationship
		 * between the defined hooks and the functions defined in this class.
	 	 *
	 	 * @since    1.0.0
		 */

		wp_enqueue_script( $this->plugin_name, $this->assets_url . 'js/wpe-fp-admin.js', array( 'jquery' ), $this->version, false );

	}

	//////////////////////////////
	# Action: Admin Init
	//////////////////////////////

	public function admin_init() {

		$post_type = 'post';

		# Bulk Action: Add a new item into the Bulk Actions dropdown
		add_filter('bulk_actions-edit-'.$post_type, array(&$this, 'add_bulk_actions'));

		# Bulk Action: Handle Featured Post Bulk Actions
		add_filter('handle_bulk_actions-edit-'.$post_type, array(&$this, 'handle_bulk_actions'), 10, 3 );

		# Admin init: Featured Post Bulk Actions Message
		add_action( 'admin_notices', array(&$this, 'message_bulk_actions') );

		# Metabox: Register Featured posts metabox
		add_meta_box('wpe_featured_'.$post_type, 'Featured Post', array(&$this, 'metabox_form'), $post_type, 'side', 'low');

		# Metabox: Featured posts metabox save
		add_filter( 'save_'.$post_type, array(&$this, 'metabox_save') );

	}

	//////////////////////////////
	# Action: Admin Menu
	//////////////////////////////

	public function admin_menu() {

		# Featured posts submenu item
		add_submenu_page('edit.php', 'Featured Posts', 'Featured Posts', 'manage_options', 'edit.php?featured=1', '');

		# Filter: Highlight the submenu item when selected
		add_filter('parent_file', array(&$this, 'highlight_submenu_item') );

		# Filter: Add 'featured' to the post title
		add_filter('the_title', array(&$this, 'table_column_title'), 10, 2 );

		# Filter: Featured Posts results
		add_action( 'pre_get_posts', array(&$this, 'filter_post_results') );

		# Filter: Add Featured posts view count
		// add_filter( 'views_users', array(&$this, 'add_view_count') );
		add_filter( 'views_edit-post', array(&$this, 'add_view_count') );

	}

	///////////////////////////////////////////////
	# Admin init: Add Featured Post Bulk Actions
	///////////////////////////////////////////////

	public function add_bulk_actions( $bulk_actions ) {

		# Feature post
		$bulk_actions['feature_post'] = __( 'Add to featured posts', 'domain' );

		# Unfeature post
		$bulk_actions['unfeature_post'] = __( 'Remove from featured posts', 'domain' );

		# Return the new actions
		return $bulk_actions;

	}

	////////////////////////////////////////////////////
	# Admin init: Handle Featured Post Bulk Actions
	////////////////////////////////////////////////////

	public function handle_bulk_actions($redirect_to, $action, $ids) {

		# Validate actions
		if( !in_array($action, array('feature_post', 'unfeature_post') ) ) {
			return $redirect_to;
		}

		# Loop through actions
		switch($action) {

			# Feature post
			case 'feature':
			case 'feature_post':

				# Loop through and update ids
				foreach( $ids as $post_id ) {
					update_post_meta($post_id, '_wpe_featured_post', true);
				}

				break;

			# Unfeature post
			case 'unfeature':
			case 'unfeature_post':

				# Loop through and update ids
				foreach( $ids as $post_id ) {
					delete_post_meta($post_id, '_wpe_featured_post');
				}

				break;

		}

		# Set the redirect URL
		$redirect_to = add_query_arg('bulk_feature_post', count( $ids ), $redirect_to);
		return $redirect_to;

	}

	////////////////////////////////////////////////////
	# Admin init: Featured Post Bulk Actions Message
	////////////////////////////////////////////////////

	public function message_bulk_actions() {

		if(!empty( $_REQUEST['bulk_feature_post'] ) ) {

			# Count the number of affected items
			$post_count = intval( $_REQUEST['bulk_feature_post'] );

			# Display the message
			printf(
				'<div id="message" class="updated fade"><p>' .
				_n('%s post updated.', '%s posts updated.', $post_count)
				. '</p></div>',
				$post_count
			);

		}
	}

	////////////////////////////////////////////
	# Admin menu: Highlight the submenu item
	////////////////////////////////////////////

	// https://wpquestions.com/Highlight_submenu_page_if_selected_in_admin_menu/9425

	public function highlight_submenu_item( $parent_file ){
		global $submenu_file;
		if(isset($_GET['featured'])) {
			$submenu_file = 'edit.php?featured=1';
			$parent_file = 'edit.php';
		}
		return $parent_file;
	}

	//////////////////////////////////////////////////
	# Admin menu: Add 'featured' to the post title
	//////////////////////////////////////////////////
	public function table_column_title( $title, $post_id ) {

		# Verify page & if post is featured
		global $pagenow, $post_type;
		if( $pagenow == 'edit.php' && $post_type == 'post' ) {
			$check = get_post_meta($post_id, '_wpe_featured_post', true);
			return (!empty($check)) ? $title . ' &mdash; featured' : $title;
		}

		# Return default
		return $title;

	}

	//////////////////////////////////////////////////
	# Admin menu: Filter Featured Posts results
	//////////////////////////////////////////////////
	public function filter_post_results( $query ) {

		# Validate admin area
		if(!is_admin()) return false;

		# Validate featured
		if(!isset($_GET['featured'])) return false;

		# Append meta query
	  if( $query->is_main_query() ) {
	    $current_meta = $query->get('meta_query');
	    $meta_query = array(
	      'key' => '_wpe_featured_post',
	      'type' => 'BINARY',
	      'value' => '1',
	      'compare' => '='
	    );
	    $query->set('meta_query', array($meta_query));
	  }

	}

	//////////////////////////////////////////////////
	# Admin menu: Add Featured posts view count
	//////////////////////////////////////////////////
	public function add_view_count( $views ){

		# Count the number of featured posts
		global $wpdb;
		$count = $wpdb->get_var(" SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE 1=1 AND meta_key = '_wpe_featured_post' ");

		# Setup the link
		$bold = (isset($_GET['featured'])) ? 'style="font-weight: bold; color: #444;"' : '';
		$permalink = admin_url() . 'edit.php?featured=1';
		$views['featured'] = '<a href="'.$permalink.'"><span '.$bold.'>Featured</span> <span class="count">('.$count.')</span></a>';

		# Return the new view
		return $views;

	}

	/////////////////////////////////////
	# Metabox: Metabox callback form
	/////////////////////////////////////

	function metabox_form() {

		# Add a nonce field
		wp_nonce_field( basename( __FILE__ ), 'wpe_featured_post_nonce' );

		# Get the post meta
	  global $post;
	  $value = get_post_meta( $post->ID, '_wpe_featured_post', true );
		$checked = (!empty($value)) ? 'checked="checked"' : '';

		# Display the form field
	  ?>
		<label style="display: block; margin-bottom: 5px;">
			<span><?php _e('Feature this post?'); ?></span>
		</label>
		<label class="switch">
			<input type="checkbox" name="wpe_featured_post" value="1" <?php echo $checked; ?> />
		  <span class="slider round"></span>
		</label>

	  <?php

	}

	/////////////////////////////////////
	# Metabox: Metabox form save
	/////////////////////////////////////

	function metabox_save( $post_id ) {

		# Validate submission
	  if( !isset( $_POST['wpe_featured_post_nonce'] ) || !wp_verify_nonce( $_POST['wpe_featured_post_nonce'], basename(__FILE__) ) ) return $post_id;
	  if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

		# Set the meta value
		if(!empty($_POST['wpe_featured_post'])){

			update_post_meta( $post_id, '_wpe_featured_post', true );

		# Delete meta value
		} else {

			delete_post_meta( $post_id, '_wpe_featured_post' );

		}

	}

}
