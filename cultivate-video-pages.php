<?php
/**
 * Plugin Name: Cultivate Video Pages
 * Plugin URI:  https://cultivatewp.com/our-plugins/cultivate-video-pages/
 * Description: Build video-focused pages intended to provide a great video-watching experience for readers looking for your videos
 * Author:      CultivateWP
 * Author URI:  https://cultivatewp.com/
 * Version:     1.2.0
 * Text Domain: cultivate-video-pages
 *
 * Cultivate Video Pages is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Cultivate Video Pages is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Cultivate Video Pages. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'CULTIVATE_VIDEO_PAGES_VERSION' ) ) {
	define( 'CULTIVATE_VIDEO_PAGES_VERSION', '1.2.0' );
}

// Plugin Folder Path.
if ( ! defined( 'CULTIVATE_VIDEO_PAGES_PLUGIN_DIR' ) ) {
	define( 'CULTIVATE_VIDEO_PAGES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'CULTIVATE_VIDEO_PAGES_PLUGIN_URL' ) ) {
	define( 'CULTIVATE_VIDEO_PAGES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File.
if ( ! defined( 'CULTIVATE_VIDEO_PAGES_PLUGIN_FILE' ) ) {
	define( 'CULTIVATE_VIDEO_PAGES_PLUGIN_FILE', __FILE__ );
}

// We require PHP 5.6+ for the whole plugin to work.
if ( version_compare( phpversion(), '5.6', '<' ) ) {

	if ( ! function_exists( 'cultivate_video_pages_php56_notice' ) ) {
		/**
		 * Display the notice after deactivation.
		 *
		 * @since 1.0.0
		 */
		function cultivate_video_pages_php56_notice() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s - CultivateWP URL for recommended WordPress hosting. */
							__( 'Your site is running an <strong>insecure version</strong> of PHP that is no longer supported. Cultivate Video Pages requires PHP 5.6+. Please contact your web hosting provider to update your PHP version or switch to a <a href="%s" target="_blank" rel="noopener noreferrer">recommended WordPress hosting company</a>.<br /><br /><strong>Note:</strong> Cultivate Video Pages is disabled on your site until you fix the issue.', 'cultivate-recipe-videos' ),
							array(
								'a'      => array(
									'href'   => array(),
									'target' => array(),
									'rel'    => array(),
								),
								'strong' => array(),
							)
						),
						'https://www.billerickson.net/client-resources/hosting-recommendations/'
					);
					?>
				</p>
			</div>

			<?php
			// In case this is on plugin activation.
			if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
				unset( $_GET['activate'] );
			}
		}
	}
	add_action( 'admin_notices', 'cultivate_video_pages_php56_notice' );

	// Do not process the plugin code further.
	return;
}

// Define the class and the function.
require_once dirname( __FILE__ ) . '/includes/class-cultivate-video-pages.php';

/**
 * The function which returns the one Cultivate Video Pages instance.
 *
 * @since 1.0.0
 *
 * @return Cultivate_Video_Pages\Cultivate_Video_Pages
 */
function cultivate_video_pages() {
	return Cultivate_Video_Pages\Cultivate_Video_Pages::instance();
}
cultivate_video_pages();
