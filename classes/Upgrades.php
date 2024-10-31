<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles processing of data migration & upgrade routines.
 *
 * @since 1.0.0
 */
class PUM_BuddyPress_Upgrades {

	/**
	 * @var self
	 */
	public static $instance;

	public $option_key = 'pum_buddypress_version_data';

	public $data = array();

	public static function init() {
		self::instance();
	}

	/**
	 * Gets everything going with a singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets up the Upgrades class instance.
	 */
	public function __construct() {
		// Update stored plugin version info.
		$this->update_plugin_version();

		add_action( 'pum_register_upgrades', array( $this, 'register_processes' ) );
	}

	/**
	 * Update version info.
	 */
	public function update_plugin_version() {
		$data = get_option( $this->option_key, array() );

		$this->data = wp_parse_args( $this->data, array(
			'version'         => PUM_BuddyPress::$VER,
			'upgraded_from'   => null,
			'initial_version' => PUM_BuddyPress::$VER,
			'installed_on'    => date( 'Y-m-d H:i:s' ),
		) );

		if ( version_compare( $this->data['version'], PUM_BuddyPress::$VER, '<' ) ) {
			// Allow processing of small core upgrades
			do_action( 'pum_update_buddypress_version', $this->data['version'] );

			// Save Upgraded From option
			$this->data['upgraded_from'] = $this->data['version'];
			$this->data['version']       = PUM_BuddyPress::$VER;

			// Reset popup asset cache on update.
			PUM_AssetCache::reset_cache();
		}

		if ( $data !== $this->data ) {
			update_option( $this->option_key, $this->data );
		}
	}

	/**
	 * @param PUM_Upgrade_Registry $registry
	 */
	public function register_processes( PUM_Upgrade_Registry $registry ) {

	}

}
