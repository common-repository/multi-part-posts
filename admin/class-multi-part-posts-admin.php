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
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package Multi_Part_Posts_Admin
 * @author  Nathan Marks <nmarks@nvisionsolutions.ca>
 */
class Multi_Part_Posts_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Enabled post types
	 * 
	 * @since 1.0.0
	 * 
	 * @var array array of post types
	 */
	private $post_types;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Multi_Part_Posts::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Enabled post types
		$this->post_types = apply_filters('multi_part_post_types',array('post'));

		// Add metabox to the post edit page
		add_action('add_meta_boxes',array($this,'add_multi_part_meta_box'),10,2);
		
		// Save our metabox data
		add_action('save_post',array($this,'save_multi_part_data'),10,2);

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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		if (in_array($screen->post_type, $this->post_types)) {

			wp_enqueue_style( $this->plugin_slug .'-chosen-styles', plugins_url( 'assets/css/chosen.min.css', __FILE__ ), array(), '1.1.0' );

			wp_enqueue_style( $this->plugin_slug .'-multi-part-styles', plugins_url( 'assets/css/multi-part.css', __FILE__ ), array(), Multi_Part_Posts::VERSION );
		
		}


	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_scripts() {


		$screen = get_current_screen();

		if (in_array($screen->post_type, $this->post_types)) {

			wp_enqueue_script('underscore');
			wp_enqueue_script('jquery-ui-sortable');

			wp_enqueue_script( 'chosen', plugins_url( 'assets/js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), '1.1.0' );

			wp_enqueue_script( $this->plugin_slug .'-meta-box', plugins_url( 'assets/js/multi-part.js', __FILE__ ), array( 'jquery' ), Multi_Part_Posts::VERSION );
		
		}

	}

	/**
	 * Add the metabox for controlling the multi part settings on posts
	 * First we check if the post type is allowed, then add the metabox
	 * 
	 * @since    1.0.0
	 * 
	 * @param string $post_type The Post Type
	 * @param object $post Post Object
	 */
	public function add_multi_part_meta_box($post_type,$post) {


		if (in_array($post_type, $this->post_types)) {
			add_meta_box(
				__( 'multi-part-post-setup', $this->plugin_slug ),
				__( 'Multi Part Post Setup', $this->plugin_slug ),
				array($this,'display_multi_part_meta_box'),
				$post_type,
				'advanced',
				'core'
			);
		}

	}

	/**
	 * Render the meta box
	 * 
	 * @since 1.0.0
	 * 
	 * 
	 */
	public function display_multi_part_meta_box($post,$metabox) {
		global $post;

		$exclude_ids = array(intval($post->ID));
		$multi_part_ids = array();

		$enable_multi_part = get_post_meta($post->ID,'enable_multi_part',true);
		$multi_part_json = get_post_meta($post->ID,'multi_part_data',true);

		/**
		 * If multi part is enabled, we should get the posts
		 */
		if (!empty($multi_part_json) && !empty($enable_multi_part)) {
			$multi_part_data = json_decode($multi_part_json);
			$multi_part_ids = $multi_part_data;
			$exclude_ids = array_unique(array_merge($exclude_ids,$multi_part_ids));

			$get_multi_part_posts_args = array(
				'posts_per_page' => -1,
				'post_type' => $post->post_type,
				'post_status' => 'any',
				'orderby' => 'post__in',
				'post__in' => $multi_part_ids
			);
			$multi_part_posts = get_posts($get_multi_part_posts_args);
		}

		// @TODO - replace this with more scalable solution
		$get_all_posts_args = array(
			'posts_per_page' => -1,
			'post_type' => $post->post_type,
			'post_status' => 'any',
			'post__not_in' => array($post->ID)
		);
		$all_posts = get_posts($get_all_posts_args);

		$id_json = json_encode($multi_part_ids);

		include_once( 'views/multi-part-meta-box.php' );
	}

	/**
	 * Save the multi part data
	 * 
	 * @since 1.0.0
	 * 
	 * @param int     $post_ID Post ID
	 * @param WP_Post $post    Post Object
	 */
	public function save_multi_part_data($post_ID,$post) {

		// No need to process if not an allowed post type
		if (!in_array($post->post_type, $this->post_types))
			return;

		/**
		 * Check if multi part is checked or unchecked
		 */
		if (isset($_REQUEST['enable_multi_part'])) {
			update_post_meta($post_ID,'enable_multi_part','enabled');
		} 
		else {
			delete_post_meta($post_ID,'enable_multi_part');
		}

		if (isset($_REQUEST['multi_part_data'])) {

			foreach ($_REQUEST as $key => $data) {
				if (!strstr($key, 'multi_part_data'))
					continue;

				$multi_part_json = $data;
				$multi_part_data = json_decode($multi_part_json);

				/**
				 * If there's current multi part data, verify they all still exist
				 * in the latest submission, if not remove the metadata
				 */
				$current_multi_part_json = get_post_meta($post->ID,'multi_part_data',true);

				if (!empty($current_multi_part_json)) {
					$current_multi_part_data = json_decode($current_multi_part_json);

					foreach ($current_multi_part_data as $current_multi_part_post_id) {
						// Does this work without intval? Forgot about PHPs loose typing
						// (my excuse for losing track of whether i've got ints or strings)
						// I blame postmeta
						// and beer.....
						if (!in_array($current_multi_part_post_id, $multi_part_data)) {
							delete_post_meta($current_multi_part_post_id,'multi_part_data');
							delete_post_meta($current_multi_part_post_id,'enable_multi_part');
						}
					}
				}

				/**
				 * If submission is empty, return.
				 */
				if (empty($multi_part_data))
					return;

				/**
				 * Loop through our selected posts and add the metadata
				 */
				foreach ($multi_part_data as $multi_part_post_id) {
					update_post_meta($multi_part_post_id,'multi_part_data',$multi_part_json);
					update_post_meta($multi_part_post_id,'enable_multi_part','enabled');
				}	
			}			

		}
	}

}
