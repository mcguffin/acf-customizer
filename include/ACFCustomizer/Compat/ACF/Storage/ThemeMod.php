<?php

namespace ACFCustomizer\Compat\ACF\Storage;

class ThemeMod extends Storage {


	private $_converted_theme_mod = array();

	private $_converting_theme_mod = null;


	/**
	 *	Theme Mod storage
	 */
	protected function __construct() {

		add_filter( 'acf/pre_load_reference', array( $this, 'pre_load_reference' ), 10, 3 );
		add_filter( 'acf/pre_load_value', array( $this, 'pre_load_value' ), 10, 3 );

		parent::__construct();
	}


	/**
	 *	Handle theme mod values
	 *
	 *	@filter acf/pre_load_value
	 */
	public function pre_load_value( $value, $post_id, $field ) {
		if ( $this->has_setting_id( $post_id ) ) {
			$data_source = get_theme_mod( $post_id );
			//$data_source = $this->convert_theme_mod( $mod, $post_id );

			if ( isset( $data_source[ $field['key'] ] ) ) {
				$value = $data_source[ $field['key'] ];

				if ( $flex = acf()->fields->get_field_type( 'flexible_content' ) ) {
					remove_filter( 'acf/load_value/type=flexible_content', [$flex, 'load_value'], 10 );
				}
				$value = apply_filters( "acf/load_value/type={$field['type']}",		$value, $post_id, $field );
				$value = apply_filters( "acf/load_value/name={$field['_name']}",	$value, $post_id, $field );
				$value = apply_filters( "acf/load_value/key={$field['key']}",		$value, $post_id, $field );
				$value = apply_filters( "acf/load_value",							$value, $post_id, $field );

				if ( $flex ) {
					add_filter( 'acf/load_value/type=flexible_content', [ $flex, 'load_value' ], 10, 3 );
				}

				return $value;
			}
		}

		return $value;
	}


	/**
	 *	Convert values from deep array to flat postmeta.
	 *	Pretend to save ACF fields, catch the values and populate $this->_converted_theme_mod
	 *
	 *	@param array $changeset
	 *	@param string|int $post_id
	 */
	protected function convert_theme_mod( $changeset, $post_id = null ) {
		if ( is_null( $post_id ) || ! isset( $this->_converted_theme_mod[ $post_id ] ) ) {

			// store data here
			$this->_converting_theme_mod = array();

			add_filter( 'update_post_metadata', array( $this, 'convert_theme_mod_update_cb'), 10, 5 );
			acf_save_post( 1, $changeset );
			remove_filter( 'update_post_metadata', array( $this, 'convert_theme_mod_update_cb'), 10 );

			$mod = $this->_converting_theme_mod + array();
			$this->_converting_theme_mod = null;

			if ( is_null( $post_id ) ) {
				return $mod;
			} else {
				$this->_converted_theme_mod[ $post_id ] = $mod;
			}
		}
		return $this->_converted_theme_mod[ $post_id ];
	}


	/**
	 *	Store meta key and value in tmp var
	 *
	 *	@filter update_post_metadata
	 *	@return bool prevents wp from actually saving this in db
	 */
	public function convert_theme_mod_update_cb( $null, $object_id, $meta_key, $meta_value, $prev_value ) {
		$this->_converting_theme_mod[$meta_key] = $meta_value;
		return true;
	}

	/**
	 *	@filter acf/pre_load_reference
	 */
	public function pre_load_reference( $reference, $field_name, $post_id ) {
		if ( $this->has_setting_id( $post_id ) ) {

			$data_source = $this->convert_theme_mod( get_theme_mod( $post_id ), $post_id );

			if ( isset( $data_source[ '_' . $field_name ] ) ) {
				return $data_source[ '_' . $field_name ];
			}
		}
		return $reference;
	}

	/**
	 *	@inheritdoc
	 */
	public function persist( $post_id, $data ) {
		// saving is done by WP
		return false;
	}

}
