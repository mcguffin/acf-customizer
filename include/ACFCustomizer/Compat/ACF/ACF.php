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

		global $wp_customize;

		$core = Core\Core::instance();

		require_once $core->get_plugin_dir() . '/include/api/acf-functions.php';

		add_action( 'acf/include_location_rules', array( $this, 'load_location_rule' ) );
		add_filter( 'acf/decode_post_id', [ $this, 'decode_post_id' ], 10, 2 );

	}

	/**
	 *	@filter acf/decode_post_id
	 */
	public function decode_post_id( $decoded_post_id, $post_id ) {
		if ( 'option' === $decoded_post_id['type'] && ( $storage_type = Customize::instance()->get_storage_type_by_post_id( $post_id ) ) ) {
			// storage type by post id
			$decoded_post_id['type'] = $storage_type;
		}
		return $decoded_post_id;
	}

	/**
	 *	Return proper field key for field in context of $post_id.
	 *
	 *	@param array $field Minimum ACF field [ 'name' => 'some-name' ]
	 *	@param string|int $post_id
	 *	@return string a proper field key if applicable, field name otherwise
	 */
	public function ensure_field_key( $field, $post_id ) {
		if ( ! empty( $field['key'] ) ) {
			return $field['key'];
		}
		global $wpdb;
		$local_fields = acf_get_local_store( 'fields' )->query(array(
			'name' => $field['name']
		));

		$candidates = array_keys( $local_fields );
		$candidates = array_merge( $candidates, $wpdb->get_col( $wpdb->prepare(
			"SELECT post_name FROM $wpdb->posts WHERE post_excerpt = %s",
			$field['name']
		)));
		$candidates = array_unique( $candidates );

		if ( 1 === count( $candidates ) ) {
			return $candidates[0];
		}
		$acf = ACF::instance();
		// ambigous ...

		foreach ( $candidates as $candidate_key ) {
			$candidate_field = acf_get_field( $candidate_key );
			$is_handling = $acf->acf_field_is_handling_post_id( $candidate_field, $post_id );
			if ( $is_handling ) {
				return $candidate_key;
			}
			// check if $candidate_field is responsible for handling $post_id
		}

		return $field['name'];
	}

	/**
	 *	returns whether an ACF field is handling data for specified post_id
	 *
	 *	@param array $acf_field
	 *	@param int|string $post_id
	 *	@return boolean
	 */
	public function acf_field_is_handling_post_id( $acf_field, $post_id ) {

		$decoded_post_id = acf_decode_post_id( $post_id );
		if ( ! in_array( $decoded_post_id['type'], [ 'post', 'term', 'option', 'theme_mod' ] ) ) {
			return false;
		}

		$field_group = $this->get_acf_field_root( $acf_field );
		if ( ! $field_group ) {
			return false;
		}
		$locations = $field_group['location'];
		$locations = array_filter( $locations, [ $this, 'filter_customizer_locations'] );

		foreach ( $locations as $location ) {
			foreach ( $location as $loc ) {

				if ( $section = acf_get_customizer_section( $loc['value'] ) ) {
					if ( $section['storage_type'] !== 'option' && $section['storage_type'] === $decoded_post_id['type'] ) {
						return true;
					}
					if ( $section['storage_type'] === 'option' && $section['storage_type'] === $decoded_post_id['type'] && $section['post_id'] === $post_id ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 *	array_filter callback
	 *	@param array $location
	 *	@return boolean
	 */
	private function filter_customizer_locations( $location ) {
		foreach ( $location as $loc ) {
			if ( $loc[ 'param' ] === 'customizer' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *	Get root field group for field
	 *
	 *	@param Array $acf_field
	 *	@return Array acf field group
	 */
	public function get_acf_field_root( $acf_field ) {
		while ( $acf_field ) {
			if ( 0 === strpos( $acf_field['parent'], 'group_' ) ) {
				return acf_get_field_group( $acf_field['parent'] );
			}
			$acf_field = acf_get_field( $acf_field['parent'] );
		}
		return false;
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
		return ['success' => true, 'message' => '' ];
	}

}
