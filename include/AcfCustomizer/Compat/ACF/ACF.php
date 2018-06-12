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
			require_once ACF_CUSTOMIZER_DIRECTORY . '/include/api/acf-functions.php';
			acf_register_location_rule( 'ACFCustomizer\Compat\ACF\Location\Customizer' );

			add_filter('acf/pre_load_value', array( $this, 'pre_load_value' ), 10, 3 );
//			Location\Customizer::instance();
		}
	}
	public function pre_load_value( $value, $post_id, $field ) {
		if ( 'theme_mod' === $post_id ) {
			$field = acf_get_valid_field( $field );
			// get fieldgroup.
			// get section
			// get_theme_mod( section + '_' + fieldgroup )


			// $field_group = $field;
			// while ( $field_group['parent'] ) {
			// 	$field_group =
			// }
			// var_dump();
		}
		return $value;
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
