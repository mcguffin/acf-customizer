<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class FieldgroupSetting extends \WP_Customize_Setting {

	public $storage_type		= 'theme_mod'; // acf field group key in instances

	public $field_groups		= array(); // acf field group key in instances


	/**
	 *	@inheritdoc
	 */
	public function __construct( $manager, $id, $args = array() ) {

		parent::__construct( $manager, $id, $args );

		// bind events according to $storage_type
		if ( $this->storage_type !== 'theme_mod' ) {

			add_filter( 'acf_get_preview_value', array( $this, 'acf_get_preview_value' ), 10, 3 );

		}
	}

	/**
	 *	@filter acf_get_preview_value
	 */
	public function	acf_get_preview_value( $value, $post_id, $field ) {

		$preview = CustomizePreview::instance();

		$context = $preview->get_context();

		$info = acf_get_post_id_info( $post_id );

		// type is object to save...
		if ( in_array( $info['type'], array( 'post', 'term' ) ) ) {
			// ... object id okay? storage type matches type to save?
			if ( $info['id'] !== $context->id || $this->storage_type !== $info['type'] ) {
				return $value;
			}
		}

		$changeset_data = $preview->changeset_data( $this->manager );
		// must merge changeset data with request data...

		if ( ! isset( $changeset_data[ $this->id ] ) ) {
			return $value;
		}

		$mod = $this->convert_theme_mod( $changeset_data[ $this->id ]['value'] );


		if ( ! isset( $mod[ $field['name'] ] ) ) {
			return $value;
		}


		$value = $mod[ $field['name'] ];

		// Apply ACF filters.
		$value = apply_filters( "acf/load_value", $value, $post_id, $field );
		$value = apply_filters( "acf/load_value/type={$field['type']}", $value, $post_id, $field );
		$value = apply_filters( "acf/load_value/name={$field['_name']}", $value, $post_id, $field );
		$value = apply_filters( "acf/load_value/key={$field['key']}", $value, $post_id, $field );

		return $value;

	}


	/**
	 *	@inheritdoc
	 */
	public function validate( $value ) {

		// We have to use acf validation here, which is heavyly depending on the browser

		return true;

	}

	/**
	 *	@inheritdoc
	 */
	public function sanitize( $value ) {

		// should do all acf sanitizes
		if ( $this->storage_type === 'theme_mod' ) {
			$value = $this->convert_theme_mod( $value );
		}

		return $value;
	}


	/**
	 *	@inheritdoc
	 */
	protected function update( $value ) {
		// additional capability check
		if ( in_array( $this->storage_type, array( 'post', 'term' ) ) ) {

			$section = $this->manager->get_section( $this->id );
			$obj_id = $section->get_context('id');
			$obj_type = $section->get_context('type');

			if ( ! current_user_can( 'edit_' . $obj_type, $obj_id ) ) {
				return false;
			}
		}

		return parent::update( $value );
	}

	/**
	 *	@inheritdoc
	 */
	protected function set_root_value( $value ) {
		/*
		perform persistent store
		*/

//		$context = json_decode()
		$section = $this->manager->get_section( $this->id );
		$obj_id = $section->get_context('id');
		$obj_type = $section->get_context('type');

		if ( $this->storage_type === 'theme_mod' ) {

			parent::set_root_value( $value );

		} else if ( $this->storage_type === 'option' ) {
			// autoload prop?
			acf_save_post( $this->id, $value );

		} else if ( is_numeric( $obj_id ) && $this->storage_type === $obj_type ) {

			if ( $this->storage_type === 'post' ) {

				acf_save_post( $obj_id, $value );

			} else if ( $this->storage_type === 'term' ) {

				acf_save_post( "{$obj_type}_{$obj_id}", $value );

			}
		}



	}


	/**
	 *	@param array $mod
	 */
	protected function convert_theme_mod( $mod ) {
		$this->_converted_theme_mod = array();
		add_filter( 'update_post_metadata', array( $this, 'convert_theme_mod_update_cb'), 10, 5 );
		acf_save_post( 1, $mod );
		remove_filter( 'update_post_metadata', array( $this, 'convert_theme_mod_update_cb'), 10 );

		$return = $this->_converted_theme_mod + array();
		$this->_converted_theme_mod = null;
		return $return;
	}


	/**
	 *	Store meta key and value in tmp var
	 *
	 *	@filter update_post_metadata
	 *	@return bool prevents wp from actually saving this in db
	 */
	public function convert_theme_mod_update_cb( $null, $object_id, $meta_key, $meta_value, $prev_value ) {
		$this->_converted_theme_mod[$meta_key] = $meta_value;
		return true;
	}



	/**
	 *	@inheritdoc
	 */
	public function to_json() {
		parent::to_json();
		$this->json['storage_type'] = $this->storage_type;
	}

}
