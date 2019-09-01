<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;


class ACF extends Core\Singleton implements Core\ComponentInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		// need to figure out which version ist lowest...


		global $wp_customize;

		$core = Core\Core::instance();

		if ( ! is_null( $wp_customize ) ) {
			// instantinate at 11
			add_action( 'plugins_loaded', array('ACFCustomizer\Compat\ACF\Customize','instance'), 11 );

		}

		require_once $core->get_plugin_dir() . '/include/api/acf-functions.php';

		add_action( 'acf/include_location_rules', array( $this, 'load_location_rule' ) );

	}

	/**
	 *	@action acf/include_location_rules
	 */
	public function load_location_rule() {
		acf_register_location_rule( 'ACFCustomizer\Compat\ACF\Location\Customizer' );
	}

	/**
	 *	@inheritdoc
	 */
	public function activate(){

	}

	/**
	 *	@inheritdoc
	 */
	public function deactivate(){

	}

	/**
	 *	@inheritdoc
	 */
	public static function uninstall() {
		// remove content and settings
	}

	/**
 	 *	@inheritdoc
	 */
	public function upgrade( $new_version, $old_version ) {
	}

}
