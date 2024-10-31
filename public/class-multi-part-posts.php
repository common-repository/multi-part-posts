<?php
/**
 * Multi Part Posts.
 *
 * @package   Multi_Part_Posts
 * @author    Nathan Marks <nmarks@nvisionsolutions.ca>
 * @license   GPL-2.0+
 * @link      http://www.nvisionsolutions.ca
 * @copyright 2014 Nathan Marks
 */

/**
 *
 * @package Multi_Part_Posts
 * @author  Nathan Marks <nmarks@nvisionsolutions.ca>
 */
class Multi_Part_Posts {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'multi-part-posts';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Enabled post types
	 * 
	 * @since 1.0.0
	 * 
	 * @var array array of post types
	 */
	private $post_types;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Enabled post types
		$this->post_types = apply_filters('multi_part_post_types',array('post'));

		// Add our table of contents
		add_filter('the_content',array($this,'display_multi_part'));
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Display the table of contents for multi part posts
	 * 
	 * @since 1.0.0
	 * @param string $content The Content
	 */
	public function display_multi_part($content) {
		global $post;

		if (!in_array($post->post_type, $this->post_types))
			return $content;

		$enable_multi_part = get_post_meta($post->ID,'enable_multi_part',true);
		$multi_part_json = get_post_meta($post->ID,'multi_part_data',true);

		if (empty($multi_part_json) || empty($enable_multi_part))
			return $content;

		if (apply_filters('display_before_post_multi_part',true))
			$content = ($this->multi_part_markup($multi_part_json)).$content;

		if (apply_filters('display_after_post_multi_part',false))
			$content = $content.($this->multi_part_markup($multi_part_json));

		// Return the content
		return $content;
	}

	/**
	 * Returns table of contents markup
	 */
	private function multi_part_markup($multi_part_json) {
		global $post;

		$multi_part_data = json_decode($multi_part_json);

		$get_multi_part_posts_args = array(
			'posts_per_page' => -1,
			'post_status' => 'any',
			'orderby' => 'post__in',
			'post__in' => $multi_part_data
		);
		$multi_part_posts = get_posts($get_multi_part_posts_args);

		$html = '<div class="multi_part_posts">';
			$html .= '<h5>'.__('Posts in this series',$this->plugin_slug).'</h5>';
			$html .= '<ol>';
				foreach ($multi_part_posts as $multi_part_post) {
					if ($multi_part_post->ID == $post->ID)
						$html .= '<li><strong>'.$multi_part_post->post_title.'</strong></li>';
					else
						$html .= '<li><a href="'.get_permalink($multi_part_post->ID).'" title="'.$multi_part_post->post_title.'">'.$multi_part_post->post_title.'</a></li>';
				}
			$html .= '</ol>';
		$html .= '</div>';

		return apply_filters('multi_part_markup',$html,$multi_part_posts);
	}

}
