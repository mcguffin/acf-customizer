<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;


class ACF extends Core\PluginComponent {

	private $customizer_sections = array();

	protected function __construct() {


		// need to figure out which version ist lowest...
		if ( function_exists('acf') && version_compare( acf()->version, '5.6','>=' ) ) {

			global $wp_customize;

			if ( ! is_null( $wp_customize ) ) {
				// instantinate at 11
				add_action( 'plugins_loaded', array('ACFCustomizer\Compat\ACF\Customize','instance'), 11 );

			}

			require_once ACF_CUSTOMIZER_DIRECTORY . '/include/api/acf-functions.php';

			acf_register_location_rule( 'ACFCustomizer\Compat\ACF\Location\Customizer' );

		}
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
	 public function uninstall() {
		 // remove content and settings
	 }

	/**
 	 *	@inheritdoc
	 */
	public function upgrade( $new_version, $old_version ) {
	}

}
