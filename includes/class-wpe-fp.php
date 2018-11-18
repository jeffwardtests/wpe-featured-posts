<?php
/**
 * The file that defines the core plugin class
 *
 * @link       hi@hello-jeff.com
 * @since      1.0.0
 *
 * @package    WPE_FP
 * @subpackage WPE_FP/includes
 */
class WPE_FP {

	/////////////////////////
	# Protected constants
	/////////////////////////

	# Set the actions that's responsible for maintaining and registering all hooks for the plugin.
	protected $actions;

	# Set the settings for the plugin.
	protected $settings;

	# Set the unique identifier of this plugin.
	protected $plugin_name;

	# Set the current version of the plugin.
	protected $version;

	# Assets URL for this plugin.
	private $assets_url;

	# Assets directory for this plugin.
	private $assets_dir;

	//////////////////////////////
	# Constructor function
	//////////////////////////////

	public function __construct() {

		# Set the version number
		$this->version = (defined( 'WPE_FP_VERSION')) ? WPE_FP_VERSION : '1.0.0';

		# Set the plugin slug name
		$this->plugin_name = 'wpe-fp';

		# Set the assets folder
		$this->assets_url = (!empty($assets_url)) ? $assets_url : plugin_dir_url( __FILE__ );
		$this->assets_dir = (!empty($assets_dir)) ? $assets_dir : plugin_dir_path( __FILE__ );

		# Load dependencies
		$this->load_dependencies();

		# Define hooks
		$this->define_hooks();

		# Define admin hooks
		$this->define_admin_hooks();

	}

	//////////////////////////////
	# Load plugin dependencies
	//////////////////////////////

	/**
	 * Include the following files that make up the plugin:
	 *
	 * - WPE_FP_Actions. Orchestrates the hooks of the plugin.
	 * - WPE_FP_Admin. Defines all hooks for the admin area.
	 * - WPE_FP_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the plugin's actions which will be used to register hooks & filters
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		# Include core actions & filters class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpe-fp-actions.php';

		# Include admin actions & filters class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpe-fp-admin.php';

		# Create an Actions instance
		$this->actions = new WPE_FP_Actions();

	}

	//////////////////////////////
	# Define hooks
	//////////////////////////////

	/**
	 * Register all of the hooks related to the admin area functionality
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {

		# Action: init
		$this->actions->add_action( 'init', $this, 'wp_init' );

		# Action: REST api
		$this->actions->add_action( 'rest_api_init', $this, 'rest_api_init' );

		# Action: Register widget
		$this->actions->add_action( 'widgets_init', $this, 'widgets_init' );

		# Actions: Enqueue styles & scripts
		$this->actions->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
		$this->actions->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );

	}

	//////////////////////////////
	# Define admin hooks
	//////////////////////////////

	/**
	 * Register all of the hooks related to the admin area functionality
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		# Create an Admin instance
		$plugin_admin = new WPE_FP_Admin( $this->get_plugin_name(), $this->get_version() );

		# Action: admin init
		$this->actions->add_action( 'admin_init', $plugin_admin, 'admin_init' );

		# Action: admin menu
		$this->actions->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

		# Actions: Enqueue styles & scripts
		$this->actions->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->actions->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	///////////////////////////////////////////
	# Run the actions to execute all hooks
	///////////////////////////////////////////

	public function run() {
		$this->actions->run();
	}

	/////////////////////////////////
	# Get the name of the plugin
	/////////////////////////////////

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	//////////////////////////////////////////////////////
 	# Get the reference to the plugin's actions class
 	//////////////////////////////////////////////////////

	/**
	 * @since     1.0.0
	 * @return    WPE_FP_Actions    Orchestrates the hooks of the plugin.
	 */
	public function get_actions() {
		return $this->actions;
	}

	//////////////////////////////////////////
	# Get the version number of this plugin
	//////////////////////////////////////////

	public function get_version() {
		return $this->version;
	}

	/////////////////////////////////////
	# Action: Register styles
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

		wp_enqueue_style( $this->plugin_name, $this->assets_url . 'css/wpe-fp.css', array(), $this->version, 'all' );

	}

	/////////////////////////////////////
	# Action: Register scripts
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

		wp_enqueue_script( $this->plugin_name, $this->assets_url . 'js/wpe-fp.js', array( 'jquery' ), $this->version, false );

	}

	/////////////////////////////////////
	# Action: WP init
	/////////////////////////////////////

	 function wp_init() {

		 ///////////////////////////////////////////
		# Public Function: Get featured posts
		///////////////////////////////////////////
		if(!function_exists('wpe_fp_get_posts')){
			function wpe_fp_get_posts($args = array()){

				# Set default values
				$args['post_type'] = (!empty($args['post_type'])) ? $args['post_type'] : 'post';
				$args['post_status'] = (!empty($args['post_status'])) ? $args['post_status'] : 'publish';
				$args['posts_per_page'] = (!empty($args['per_page'])) ? $args['per_page'] : 5;

				# Add meta query
				$args['meta_query'] = array(
					array(
						'key' => '_wpe_featured_post',
						'type' => 'BINARY',
						'value' => '1',
						'compare' => '='
					 )
				);

				# Get the results
				$posts = get_posts( $args );
				wp_reset_query();
				return $posts;

			}
		}

	}

	/////////////////////////////////////
	# Action: Register widget
	/////////////////////////////////////

	 function widgets_init() {

	 		# Include widget class
	 		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpe-fp-widget.php';

			# Register the widget
			register_widget('wpe_fp_widget');

	 }

	 /////////////////////////////////////
	 # Action: REST api init
	 /////////////////////////////////////

	 /**
	  * Grab latest featured posts
	  *
	  * @param array $data Options for the function.
	  * @return string|null Post title for the latest,  * or null if none.
	 */

	 // Example enpoint: http://localhost/wpengine/wp-json/featured-posts/get/

	 public function rest_api_init() {
	   register_rest_route( 'featured-posts', 'get', array(
	       'methods' => 'GET',
	       'callback' => array(&$this, 'api_results'),
	       'args' => array(
	           'per_page' => array(
	               'validate_callback' => function( $param, $request, $key ) {
	                   return is_numeric( $param );
	               }
	           ),
	           'post_type' => array(
	               'validate_callback' => function( $param, $request, $key ) {
	                   return post_type_exists( $param );
	               }
	           ),
	       )
	 			/*
	 			,
	       'permission_callback' => function() {
	           return true;
	           return current_user_can( 'activate_plugins' );
	         }
	 				*/
	       )
	   );
	 }

	 /////////////////////////////////////
	 # Action: REST api results
	 /////////////////////////////////////
	 public function api_results() {

	 	# Get the parameters
	 	$post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'post';
	 	$per_page = (isset($_GET['per_page'])) ? sanitize_text_field($_GET['per_page']) : 5;
	 	$per_page = ($per_page > 5) ? 5 : $per_page;
	 	$orderby = (isset($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'post_date';
	 	$order = (isset($_GET['order'])) ? sanitize_text_field($_GET['order']) : 'DESC';

	 	# Set the date format
	 	$date_format = 'F jS, Y @ g:ia';

	 	# Setup arguments
	 	$query_args = array(
	 		'per_page' => $per_page,
	 		'post_type' => $post_type,
	 		'orderby' => $orderby,
	 		'order' => $order,
	 	);
	 	$posts = wpe_fp_get_posts($query_args);

	   # Get the posts
	   $post_data = array();

	 	# Loop through each post
	   foreach($posts as $post) {

	 		 # Get the thumbnail
	 		 $thumbnail = get_the_post_thumbnail( $post->ID, array(80, 80) );

	 		 # Get the author
	 		 $author = get_userdata($post->post_author);

	 		 # Parse the post data
	 		 $post_data[] = array(
	 			 'id' => $post->ID,
	 			 'title' => $post->post_title,
	 			 'author' => $author->display_name,
	 			 // 'author_id' => $post->post_author,
	 			 'content' => $post->post_content,
	 			 'published' => mysql2date($date_format, $post->post_date),
	 			 'thumbnail' => $thumbnail,
	 		 );

	 	}
	   wp_reset_postdata();

	   return rest_ensure_response( $post_data );

	 }

}
