<?php
/**
 * Plugin Name: Popup Maker - BuddyPress Integration
 * Plugin URI: https://wppopupmaker.com/works-with/buddypress/
 * Description: Adds integrated functionality between Popup Maker & BuddyPress
 * Author: WP Popup Maker
 * Version: 1.0.0
 * Author URI: https://wppopupmaker.com/
 * Text Domain: popup-maker-buddypress-integration
 * GitLab Plugin URI: https://github.com/PopupMaker/BuddyPress-Integration
 * GitHub Branch:     master
 *
 * @author       WP Popup Maker
 * @copyright    Copyright (c) 2018, WP Popup Maker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $autoloaders
 *
 * @return array
 */
function pum_buddypress_autoloader( $autoloaders = array() ) {
	return array_merge( $autoloaders, array(
		array(
			'prefix' => 'PUM_BuddyPress_',
			'dir'    => dirname( __FILE__ ) . '/classes/',
		),
	) );
}

add_filter( 'pum_autoloaders', 'pum_buddypress_autoloader' );

/**
 * Class PUM_BuddyPress
 */
class PUM_BuddyPress {

	/**
	 * @var string
	 */
	public static $NAME = 'BuddyPress Integration';

	/**
	 * Here temporarily until Popup Maker v1.8 goes out with the new activation class.
	 *
	 * @var bool
	 */
	public static $ID = false;

	/**
	 * @var bool
	 */
	public static $WP_REPO = 'https://wordpress.org/plugins/popup-maker-buddypress-integration/';

	/**
	 * @var string
	 */
	public static $VER = '1.0.0';

	/**
	 * @var string Required Version of Popup Maker
	 */
	public static $REQUIRED_CORE_VER = '1.7.0';

	/**
	 * @var string
	 */
	public static $URL = '';

	/**
	 * @var string
	 */
	public static $DIR = '';

	/**
	 * @var string
	 */
	public static $FILE = '';

	public static $INTEGRATION_NAME = 'BuddyPress';
	public static $INTEGRATION_SLUG = 'buddypress';

	public static $INTEGRATION_PATH = '';

	public static $REQUIRED_INTEGRATION_VER = false;

	/**
	 * @var         PUM_BuddyPress $instance The one true PUM_BuddyPress
	 */
	private static $instance;

	/**
	 * Get active instance
	 *
	 * @return      object self::$instance The one true PUM_BuddyPress
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
			self::$instance->setup_constants();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Setup plugin constants
	 */
	public function setup_constants() {
		self::$DIR  = plugin_dir_path( __FILE__ );
		self::$URL  = plugins_url( '/', __FILE__ );
		self::$FILE = __FILE__;
	}

	/**
	 * Internationalization
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'popup-maker-buddypress-integration' );
	}

	/**
	 * Include necessary files
	 */
	private function includes() {
	}

	/**
	 * Initialize everything
	 */
	private function init() {
		if ( $this->integration_status() != 'active' ) {
			// Display notice
			add_action( 'admin_notices', array( $this, 'missing_popmake_notice' ) );

			return;
		}

		// Unhook core BuddyPress integration code if hooked.
		remove_action( 'init', 'PUM_BuddyPress_Integration::init' );

		PUM_BuddyPress_Conditions::init();
	}

	/**
	 * @return string
	 */
	private function integration_status() {
		static $status;

		if ( ! isset( $status ) ) {
			$plugins = get_plugins();

			$integration_plugin_installed = false;

			// Is Popup Maker installed?
			foreach ( $plugins as $plugin_path => $plugin ) {
				if ( $plugin['Name'] == self::$INTEGRATION_NAME ) {
					$integration_plugin_installed = true;
					self::$INTEGRATION_PATH       = $plugin_path;
					break;
				}
			}

			if ( $integration_plugin_installed && ! class_exists( 'BuddyPress' ) ) {
				$status = 'not_activated';
//			} elseif ( $integration_plugin_installed && isset( self::$REQUIRED_INTEGRATION_VER ) && version_compare( Popup_Maker::$VER, $this->required_core_version, '<' ) ) {
//				$status = 'not_updated';
			} elseif ( ! $integration_plugin_installed ) {
				$status = 'not_installed';
			} else {
				$status = 'active';
			}
		}

		return $status;

	}

	/**
	 * Display notice if Popup Maker isn't installed
	 */
	public function missing_popmake_notice() {
		switch ( $this->integration_status() ) {
			case 'not_activated':
				$url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . self::$INTEGRATION_PATH ), 'activate-plugin_' . self::$INTEGRATION_PATH ) );
				$link = '<a href="' . $url . '">' . __( 'activate it' ) . '</a>';
				echo '<div class="error"><p>' . sprintf( __( 'The plugin "Popup Maker - %s" requires %s! Please %s to continue!' ), self::$NAME, '<strong>' . self::$INTEGRATION_NAME . '</strong>', $link ) . '</p></div>';

				break;
//			case 'not_updated':
//				$url  = esc_url( wp_nonce_url( admin_url( 'update.php?action=upgrade-plugin&plugin=' . self::$INTEGRATION_PATH ), 'upgrade-plugin_' . self::$INTEGRATION_PATH ) );
//				$link = '<a href="' . $url . '">' . __( 'update it' ) . '</a>';
//				echo '<div class="error"><p>' . self::$NAME . sprintf( __( ' requires Popup Maker v%s or higher! Please %s to continue!' ), $this->required_core_version, $link ) . '</p></div>';
//
//				break;
			case 'not_installed':
				$url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . self::$INTEGRATION_SLUG ), 'install-plugin_' . self::$INTEGRATION_SLUG ) );
				$link = '<a href="' . $url . '">' . __( 'install it' ) . '</a>';
				echo '<div class="error"><p>' . sprintf( __( 'The plugin "Popup Maker - %s" requires %s! Please %s to continue!' ), self::$NAME, '<strong>' . self::$INTEGRATION_NAME . '</strong>', $link ) . '</p></div>';

				break;
			case 'active':
			default:
				return;
		}
	}

}

/**
 * Get the ball rolling. Fire up the correct version.
 */
function pum_buddypress_init() {
	if ( ! class_exists( 'PUM_Extension_Activator' ) ) {
		require_once 'includes/pum-sdk/class-pum-extension-activator.php';
	}

	$activator = new PUM_Extension_Activator( 'PUM_BuddyPress' );
	$activator->run();
}

add_action( 'plugins_loaded', 'pum_buddypress_init', 11 );

if ( ! class_exists( 'PUM_BuddyPress_Activator' ) ) {
	require_once 'classes/Activator.php';
}
register_activation_hook( __FILE__, 'PUM_BuddyPress_Activator::activate' );

if ( ! class_exists( 'PUM_BuddyPress_Deactivator' ) ) {
	require_once 'classes/Deactivator.php';
}
register_deactivation_hook( __FILE__, 'PUM_BuddyPress_Deactivator::deactivate' );
