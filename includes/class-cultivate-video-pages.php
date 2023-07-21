<?php
/**
 * Main class
 *
 * @package      CULTIVATE_VIDEO_PAGES
 * @author       CultivateWP
 * @since        1.0.0
 * @license      GPL-2.0+
**/

namespace Cultivate_Video_Pages;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

final class Cultivate_Video_Pages {

	/**
	 * Instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Nonce Value
	 *
	 * @since 1.0.0
	 */
	private $nonce = 'cultivate_video_pages_nonce';


	/**
	 * Cultivate Video Pages Instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Cultivate_Video_Pages
	 */
	public static function instance() {

		if( ! isset( self::$instance ) && ! ( self::$instance instanceof Cultivate_Video_Pages ) ) {
			self::$instance = new Cultivate_Video_Pages();
			self::$instance->load_textdomain();
			self::$instance->install();
			self::$instance->includes();

			add_action( 'init', [ self::$instance, 'register_post_type' ] );
			add_action( 'add_meta_boxes', [ self::$instance, 'metabox_register' ] );
			add_action( 'save_post', [ self::$instance, 'metabox_save' ],  1, 2  );
			add_action( 'pre_get_posts', [ self::$instance, 'video_query' ] );
			add_filter( 'body_class', [ self::$instance, 'body_classes' ], 30 );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'style' ] );
			add_action( 'tha_header_after', [ self::$instance, 'display_video' ], 30 );
			add_action( 'genesis_after_header', [ self::$instance, 'display_video' ], 30 );
			add_action( 'cultivate_video_pages_display_video', [ self::$instance, 'display_video' ] );
			add_filter( 'the_content', [ self::$instance, 'display_video_fallback'], 1 );
			add_action( 'tha_entry_content_after', [ self::$instance, 'original_post_message' ], 5 );
			add_action( 'genesis_entry_content', [ self::$instance, 'original_post_message' ], 12 );
			add_action( 'cultivate_video_pages_message', [ self::$instance, 'original_post_message' ] );

			add_action( 'cultivate_video_pages_install', [ self::$instance, 'register_post_type' ] );
			add_action( 'cultivate_video_pages_install', 'flush_rewrite_rules' );

			global $wp_embed;
			add_filter( 'cultivate_video_pages/output', array( $wp_embed, 'autoembed'     ), 8 );
			add_filter( 'cultivate_video_pages/output', 'do_shortcode' );

		}
		return self::$instance;
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since 1.0.0
	 * @todo generate pot file
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'cultivate-video-pages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Install procedure.
	 *
	 * @since 1.0.0
	 */
	public function install() {

		// When activated, run install.
		register_activation_hook(
			CULTIVATE_VIDEO_PAGES_PLUGIN_FILE,
			function() {

				do_action( 'cultivate_video_pages_install' );

				// Set current version, to be referenced in future updates.
				update_option( 'cultivate_video_pages_version', CULTIVATE_VIDEO_PAGES_VERSION );
			}
		);
	}

	/**
	 * Load includes.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		if( is_admin() ) {
			require CULTIVATE_VIDEO_PAGES_PLUGIN_DIR . 'includes/updater/plugin-update-checker.php';

			$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
				'https://github.com/cultivatewp/cultivate-video-pages/',
				CULTIVATE_VIDEO_PAGES_PLUGIN_FILE, //Full path to the main plugin file or functions.php.
				'cultivate-video-pages'
			);

			$myUpdateChecker->setBranch('master');
		}
	}

	/**
	 * Register post type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => 'Videos',
			'singular_name'      => 'Video',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Video',
			'edit_item'          => 'Edit Video',
  			'new_item'           => 'New Video',
  			'view_item'          => 'View Video',
  			'search_items'       => 'Search Videos',
  			'not_found'          => 'No Videos found',
  			'not_found_in_trash' => 'No Videos found in Trash',
  			'parent_item_colon'  => 'Parent Video:',
  			'menu_name'          => 'Video Pages',
  		);

  		$args = array(
  			'labels'              => $labels,
  			'hierarchical'        => false,
  			'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
  			'public'              => true,
  			'show_ui'             => true,
  			'show_in_menu'        => true,
  			'show_in_rest'        => true,
  			'show_in_nav_menus'   => true,
  			'publicly_queryable'  => true,
  			'exclude_from_search' => false,
  			'has_archive'         => true,
  			'query_var'           => true,
  			'can_export'          => true,
  			'rewrite'             => array( 'slug' => 'videos', 'with_front' => false ),
  			'menu_icon'           => 'dashicons-video-alt3'
  		);

		$args = apply_filters( 'cultivate_video_pages/post_type_args', $args );
		register_post_type( 'video', $args );
	}

	/**
	 * Register Metabox
	 */
	 public function metabox_register() {
		 add_meta_box( 'cultivate-video-pages', 'Recipe Video', [ self::$instance, 'metabox_render' ], 'video', 'side', 'high' );
	 }

	/**
	 * Render Metabox
	 */
	public function metabox_render() {

		// Security nonce
		wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );


		echo '<div class="cultivate-video-pages-setting">';
		echo '<p><label for="cwp_video_embed">Video Embed Code</label><br /><textarea rows="4" class="widefat" type="text" name="cwp_video_embed">' . get_post_meta( get_the_ID(), 'cwp_video_embed', true ) . '</textarea></p>';
		echo '<p><label for="cwp_recipe_url">Recipe Post URL</label><br /><input class="widefat" type="text" name="cwp_recipe_url" value="' . esc_html( get_post_meta( get_the_ID(), 'cwp_recipe_url', true ) ) . '" /></p>';
		echo '</div>';

	}

	/**
	 * Save Metabox
	 *
	 * @since 1.0.0
	 */
	function metabox_save( $post_id, $post ) {

		if( ! $this->user_can_save( $post_id, $this->nonce ) )
			return;

		update_post_meta( $post_id, 'cwp_recipe_url', esc_html( $_POST[ 'cwp_recipe_url' ] ) );
		update_post_meta( $post_id, 'cwp_video_embed', $_POST[ 'cwp_video_embed' ] );
	}

	/**
	 * User can save metabox
	 *
	 * @since 1.0.0
	 */
	function user_can_save( $post_id, $nonce ) {

		// Security check
		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) ) {
			return false;
		}

		// Bail out if running an autosave, ajax, cron.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		}

		// Bail out if the user doesn't have the correct permissions to edit the post
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		// Good to go!
		return true;
	}

	/**
	 * Video Query
	 */
	public function video_query( $query ) {
		if ( $query->is_main_query() && ! is_admin() && $query->is_post_type_archive( 'video' ) ) {
			$query->set( 'orderby', 'post_date title' );
		}
	}

	/**
	 * Body Classes
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Body Classes
	 * @return array
	 */
	 public function body_classes( $classes ) {
		 if ( is_singular( 'video' ) ) {
			 $new_classes = [ 'adthrive-video-autoplay', 'dedicated-video-page' ];
			 $new_classes = apply_filters( 'cultivate_video_pages/body_classes', $new_classes );
			 $classes = array_merge( $classes, $new_classes );
		 }
		 return $classes;
	 }

	 /**
	  * Style
	  */
	 public function style() {
		 if ( ! is_singular( 'video' ) ) {
			 return;
		 }

		 if ( apply_filters( 'cultivate_video_pages/disable_css', false ) ) {
			 return;
		 }

		 wp_register_style( 'cwp-video', false );
		 wp_add_inline_style( 'cwp-video', '.cwp-video > .wrap iframe { aspect-ratio: 16 / 9; width: 100%; height: 100% }' );
		 wp_enqueue_style( 'cwp-video' );

	 }

	 /**
	  * Display Video
	  *
	  * @since 1.0.0
	  */
	  public function display_video() {
		remove_filter( 'the_content', [ self::$instance, 'display_video_fallback' ], 1 );

		if ( ! is_singular( 'video' ) ) {
			  return;
		  }

		  $video = get_post_meta( get_the_ID(), 'cwp_video_embed', true );
		  if ( empty( $video ) ) {
			  return;
		  }

		  $class = apply_filters( 'cultivate_video_pages/video_class', 'cwp-video' );
		  echo '<div class="' . esc_attr( $class ) . '"><div class="wrap">' . apply_filters( 'cultivate_video_pages/output', $video ) . '</div></div>';
	  }

	  /**
	   * Display Video Fallback
	   */
	  public function display_video_fallback( $content ) {
		if ( ! is_singular( 'video' ) ) {
			return $content;
		}

		$video = get_post_meta( get_the_ID(), 'cwp_video_embed', true );
		if ( empty( $video ) ) {
			return $content;
		}

		$class = apply_filters( 'cultivate_video_pages/video_class', 'cwp-video' );
		$video = '<div class="' . esc_attr( $class ) . '">' . apply_filters( 'cultivate_video_pages/output', $video ) . '</div>';

		$content = $video . $content;

		// Original Post Link
		$url = get_post_meta( get_the_ID(), 'cwp_recipe_url', true );
		if ( empty( $url ) ) {
			return $content;
		}
		
		$id = url_to_postid( $url );
		$title = ! empty( $id ) ? get_the_title( $id ) : get_the_title();

		$message = apply_filters(
			'cultivate_video_pages/original_post_message',
			'<p><em>This video originally appeared on <a href="%s">%s</a>.</em></p>'
		);

		if ( empty( $message ) ) {
			return $content;
		}

		$message = sprintf( $message, esc_url( $url ), esc_html( $title ) );		
		return $content . $message;

	  }

	  /**
	   * Original Post Link
	   *
	   * @since 1.0.0
	   */
	  public function original_post_message() {
		  if ( ! is_singular( 'video' ) ) {
			  return;
		  }

		  $url = get_post_meta( get_the_ID(), 'cwp_recipe_url', true );
		  if ( empty( $url ) ) {
			  return;
		  }
		  
		  $id = url_to_postid( $url );
		  $title = ! empty( $id ) ? get_the_title( $id ) : get_the_title();

		  $message = apply_filters(
			  'cultivate_video_pages/original_post_message',
			  '<p><em>This video originally appeared on <a href="%s">%s</a>.</em></p>'
		  );

		  if ( empty( $message ) ) {
			  return;
		  }

		  printf( $message, esc_url( $url ), esc_html( $title ) );
	  }

}
