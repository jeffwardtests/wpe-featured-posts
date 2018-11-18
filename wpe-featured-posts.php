<?php
/**
 * @link              http://hello-jeff.com
 * @since             1.0.0
 * @package           WPEPFP
 *
 * @wordpress-plugin
 * Plugin Name:       WPEngine Featured Posts
 * Plugin URI:        https://www.wpengine.com
 * Description:       This plugin gives the abilty to create featured posts and access them via widget or api endpoint.
 * Version:           1.0.0
 * Author:            Jeff Ward
 * Author URI:        http://hello-jeff.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpe-fp
 * Domain Path:       /languages
 */

/////////////////////////////////////
# Do not call this file directly
/////////////////////////////////////
if( !defined( 'WPINC' ) ) die;

///////////////////////////
# Current plugin version
///////////////////////////
define( 'WPE_FP_VERSION', '1.0.0' );

///////////////////////////
# Activation events
///////////////////////////
function activate_wpe_fp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-fp-activation.php';
	WPE_FP_Activation::activate();
}

///////////////////////////
# Deactivation events
///////////////////////////
function deactivate_wpe_fp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-fp-deactivation.php';
	WPE_FP_Deactivation::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpe_fp' );
register_deactivation_hook( __FILE__, 'deactivate_wpe_fp' );

///////////////////////////////////
# Include the core plugin class
///////////////////////////////////
require plugin_dir_path( __FILE__ ) . 'includes/class-wpe-fp.php';

//////////////////////////////
# Initialize the plugin
//////////////////////////////
function run_wpe_fp() {
	$plugin = new WPE_FP();
	$plugin->run();
}
run_wpe_fp();
