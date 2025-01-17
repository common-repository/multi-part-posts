<?php
/**
 * Multi part Posts.
 *
 * @package   Multi_Part_Posts
 * @author    Nathan Marks <nmarks@nvisionsolutions.ca>
 * @license   GPL-2.0+
 * @link      http://www.nvisionsolutions.ca
 * @copyright 2014 Nathan Marks
 *
 * @wordpress-plugin
 * Plugin Name:       Multi Part Posts
 * Plugin URI:        http://www.nvisionsolutions.ca
 * Description:       Easily add a table of contents to your multi part posts. Automated syncing cross-posts.
 * Version:           1.0.0
 * Author:            Nathan Marks
 * Author URI:        http://www.nvisionsolutions.ca
 * Text Domain:       multi-part-posts
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nathanmarks/wordpress-multi-part-posts
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-multi-part-posts.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Multi_Part_Posts', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Multi_Part_Posts', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Multi_Part_Posts', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-multi-part-posts-admin.php' );
	add_action( 'plugins_loaded', array( 'Multi_Part_Posts_Admin', 'get_instance' ) );

}
