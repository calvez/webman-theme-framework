<?php
/**
 * Updater class
 *
 * Based on @link https://github.com/unisphere/unisphere_notifier
 * by @author Joao Araujo
 *
 * @package     WebMan WordPress Theme Framework
 * @subpackage  Updater
 *
 * @since    1.0
 * @version  1.3
 */





/**
 * Updater class
 *
 * @since    1.0
 * @version  1.3
 *
 * Contents:
 *
 *  0) Init
 * 10) Links
 * 20) Page
 * 30) Get remote data
 */
final class {%= prefix_class %}_Theme_Framework_Updater {





	/**
	 * 0) Init
	 */

		private static $instance;



		/**
		 * Constructor
		 *
		 * @since    1.0
		 * @version  1.3
		 */
		private function __construct() {

			// Processing

				// Hooks

					// Actions

						// Admin menu

							add_action( 'admin_menu', array( $this, 'menu' ), 998 );

						// Toolbar

							add_action( 'admin_bar_menu', array( $this, 'toolbar' ), 998 );

		} // /__construct



		/**
		 * Initialization (get instance)
		 *
		 * @since    1.0
		 * @version  1.0
		 */
		public static function init() {

			// Processing

				if ( null === self::$instance ) {
					self::$instance = new self;
				}


			// Output

				return self::$instance;

		} // /init





	/**
	 * 10) Dashboard links
	 */

		/**
		 * Admin menu link
		 *
		 * @since    1.0
		 * @version  1.3
		 */
		public static function menu() {

			// Requirements check

				if (
						! is_super_admin()
						|| apply_filters( 'wmhook_{%= prefix_hook %}_tf_updater_menu_disable', false )
					) {
					return;
				}


			// Processing

				if ( function_exists( 'simplexml_load_string' ) ) {

					$xml = self::get_remote_xml_data( {%= prefix_constant %}_UPDATE_NOTIFIER_CACHE_INTERVAL );

					if (
							isset( $xml->latest )
							&& version_compare( $xml->latest, {%= prefix_constant %}_THEME_VERSION, '>' )
						) {

						add_theme_page(
							// page_title
							sprintf(
								esc_html_x( '%s Theme Updates', '%s stands for the theme name. Just copy it.', '{%= text_domain %}' ),
								wp_get_theme( '{%= theme_slug %}' )->get( 'Name' )
							),
							// menu_title
							esc_html_x( 'Theme Updates', 'Admin menu title.', '{%= text_domain %}' ) . ' <span class="update-plugins count-1"><span class="update-count">1</span></span>',
							// capability
							'switch_themes',
							// menu_slug
							'theme-update-notifier',
							// function
							'{%= prefix_class %}_Theme_Framework_Updater::page'
						);

					}

				}

		} // /menu



		/**
		 * Toolbar link
		 *
		 * @since    1.0
		 * @version  1.3
		 */
		public static function toolbar() {

			// Requirements check

				if (
						! is_super_admin()
						|| ! is_admin_bar_showing()
						|| apply_filters( 'wmhook_{%= prefix_hook %}_tf_updater_toolbar_disable', false )
					) {
					return;
				}


			// Processing

				if ( function_exists( 'simplexml_load_string' ) ) {

					global $wp_admin_bar;

					$xml = self::get_remote_xml_data( {%= prefix_constant %}_UPDATE_NOTIFIER_CACHE_INTERVAL );

					if (
							isset( $xml->latest )
							&& version_compare( $xml->latest, {%= prefix_constant %}_THEME_VERSION, '>' )
						) {

						if (
								! isset( $xml->noenvato )
								&& class_exists( 'Envato_WP_Toolkit' )
							) {
							$admin_url = network_admin_url( 'admin.php?page=envato-wordpress-toolkit' );
						} else {
							$admin_url = get_admin_url() . 'themes.php?page=theme-update-notifier';
						}

						$wp_admin_bar->add_menu( array(
								'id'    => 'update_notifier',
								'title' => sprintf( esc_html_x( '%s update', 'Admin bar notification link. %s: theme name.', '{%= text_domain %}' ), wp_get_theme( '{%= theme_slug %}' )->get( 'Name' ) ) . ' <span id="ab-updates">1</span>',
								'href'  => esc_url( $admin_url )
							) );

					}

				}

		} // /toolbar





	/**
	 * 20) Page
	 */

		/**
		 * Notifier page renderer
		 *
		 * @since    1.0
		 * @version  1.3
		 */
		public static function page() {

			// Pre

				$pre = apply_filters( 'wmhook_{%= prefix_hook %}_tf_updater_page_pre', false );

				if ( false !== $pre ) {
					return $pre;
				}


			// Requirements check

				if ( ! is_super_admin() ) {
					return;
				}


			// Helper variables

				$xml = self::get_remote_xml_data( {%= prefix_constant %}_UPDATE_NOTIFIER_CACHE_INTERVAL );


			// Processing

				/**
				 * No need for translations, english only.
				 */
				?>

				<div class="wrap update-notifier">

					<div id="icon-tools" class="icon32"></div>
					<h2><strong><?php echo wp_get_theme( '{%= theme_slug %}' )->get( 'Name' ); ?></strong> Theme Updates</h2>

					<br />

					<div id="message" class="error">

						<p><?php

						if ( isset( $xml->message ) && trim( $xml->message ) ) {
							echo '<strong>' . trim( $xml->message ) . '</strong><br />';
						}

						echo 'You have version ' . {%= prefix_constant %}_THEME_VERSION . ' installed. <strong>Update to version ' . trim( $xml->latest ) . ' now.</strong>';

						?></p>

					</div>

					<div id="instructions">

						<?php

						if ( isset( $xml->instructions ) && trim( $xml->instructions ) ) {

							echo trim( $xml->instructions );

						} else {

						?>

							<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/screenshot.png" alt="" class="theme-img" />

							<h3>Update Download and Instructions</h3>

							<p>First, please, re-download the new theme update from the source where you've originally obtained the theme.</p>

							<p>Use one of these options to update your theme:</p>

							<?php

							if ( isset( $xml->important ) ) {

								echo '<div class="important-note">' . $xml->important . '</div>';

							}

							?>

							<ul>

								<?php

								if ( class_exists( 'Envato_WP_Toolkit' ) && ! isset( $xml->noenvato ) ) {
									echo '<li><h4>Automatic theme update:</h4><a href="' . network_admin_url( 'admin.php?page=envato-wordpress-toolkit' ) . '" class="button button-primary button-hero">Update the Theme Automatically &raquo;</a></li>';
								}

								?>

								<li>

									<h4>Preferred, safer, quicker procedure:</h4>

									<ol>
										<li>Upload the theme installation ZIP file using FTP client to your server (into <code>YOUR_WORDPRESS_INSTALLATION/wp-content/themes/</code>).</li>
										<li>Using your FTP client, rename the old theme folder (for example from <code>{%= theme_slug %}</code> to <code>{%= theme_slug %}-bak</code>).</li>
										<li>When the old theme folder is renamed, unzip the theme installation zip file directly on the server (you might need to use a web-based FTP tool for this - hosting companies provides such tools).</li>
										<li>After checking whether the theme works fine, delete the renamed old theme folder from the server (the <code>{%= theme_slug %}-bak</code> folder in our case).</li>
									</ol>

								</li>

								<li>

									<h4>Easier, slower procedure:</h4>

									<ol>
										<li>Unzip the zipped theme file (you have just downloaded) on your computer.</li>
										<li>Upload the unzipped theme folder using FTP client to your server (into <code>YOUR_WORDPRESS_INSTALLATION/wp-content/themes/</code>) overwriting all the current theme files. Please note that if some files were removed from the theme in the new update, you will have to delete these files additionally from your server. For removed files please check the changelog on the right.</li>
									</ol>

								</li>

							</ul>

						<?php

						} // /Custom instructions check

						?>

					</div>

					<div id="changelog" class="note">

						<div class="icon32 icon32-posts-page" id="icon-edit-pages"><br /></div>
						<h2>Changelog</h2>

						<?php

						if ( isset( $xml->changelog ) ) {
							echo $xml->changelog;
						}

						?>

						<hr />

						<h3>Files changed:</h3>

						<code><?php

						if ( isset( $xml->changefiles ) ) {
							echo str_replace( ', ', '</code><br /><code>', $xml->changefiles );
						}

						?></code>

					</div>

				</div>

				<?php

		} // /page





	/**
	 * 30) Get remote data
	 */

		/**
		 * Remote XML file processing
		 *
		 * Get the remote XML file contents and return its data.
		 * Uses the cached version if available, inside the time interval defined.
		 *
		 * @since    1.0
		 * @version  1.3
		 *
		 * @param  int $interval
		 */
		public static function get_remote_xml_data( $interval ) {

			// Pre

				$pre = apply_filters( 'wmhook_{%= prefix_hook %}_tf_updater_get_remote_xml_data_pre', false, $interval );

				if ( false !== $pre ) {
					return $pre;
				}


			// Requirements check

				if ( ! is_super_admin() ) {
					return;
				}


			// Helper variables

				$db_cache_field              = '{%= prefix_var %}_notifier_cache_{%= theme_slug %}';
				$db_cache_field_last_updated = '{%= prefix_var %}_notifier_cache_{%= theme_slug %}_last_updated';
				$last                        = get_transient( $db_cache_field_last_updated );

				// Check the cache

					if (
							! $last
							|| ( time() - $last ) > absint( $interval )
						) {

						// Cache doesn't exist, or is old, so refresh it

							$response = wp_remote_get( esc_url( trailingslashit( wp_get_theme( '{%= theme_slug %}' )->get( 'AuthorURI' ) ) . 'updates/{%= theme_slug %}/{%= theme_slug %}-version.xml' ) );

							if ( is_wp_error( $response ) ) {

								$error = $response->get_error_message();

								$cache  = '<?xml version="1.0" encoding="UTF-8"?>';
								$cache .= '<notifier>';
									$cache .= '<latest>1.0</latest>';
									$cache .= '<message><![CDATA[<span style="font-size:125%;color:#f33">Something went wrong: ' . strip_tags( $error, '<a><span><strong>' ) . '</span>]]></message>';
									$cache .= '<changelog></changelog>';
									$cache .= '<changefiles></changefiles>';
								$cache .= '</notifier>';

							} else {

								$cache = $response['body'];

							}

						// If we've got good results, cache them

							if ( $cache ) {
								set_transient( $db_cache_field, $cache );
								set_transient( $db_cache_field_last_updated, time() );
							}

						// Read from the cache

							$notifier_data = get_transient( $db_cache_field );

					} else {

						// Cache is fresh enough, so read from it

							$notifier_data = get_transient( $db_cache_field );

					}

				/**
				 * Let's see if the $xml data was returned as we expected it to.
				 * If it wasn't, use the default 1.0 as the latest version so that we
				 * don't have problems when the remote server hosting the XML file is down
				 */
				if ( strpos( (string) $notifier_data, '<notifier>' ) === false ) {

					$notifier_data  = '<?xml version="1.0" encoding="UTF-8"?>';
					$notifier_data .= '<notifier>';
						$notifier_data .= '<latest>1.0</latest>';
						$notifier_data .= '<message></message>';
						$notifier_data .= '<changelog></changelog>';
						$notifier_data .= '<changefiles></changefiles>';
					$notifier_data .= '</notifier>';

				}

				// Load the remote XML data into a variable and return it

					$xml = simplexml_load_string( $notifier_data );


			// Output

				return $xml;

		} // /get_remote_xml_data





} // /{%= prefix_class %}_Theme_Framework_Updater
