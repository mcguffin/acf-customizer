<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class FieldgroupSetting extends \WP_Customize_Setting {

	public $storage_type		= 'theme_mod'; // acf field group key in instances

	public $field_groups		= array(); // acf field group key in instances

	public $post_id = '';

	// private $_converted_theme_mod = array();
	//
	// private $_converting_theme_mod = null;
	//
	private $storage = null;

	/**
	 *	@inheritdoc
	 */
	public function __construct( $manager, $id, $args = array() ) {

		parent::__construct( $manager, $id, $args );

		$this->storage = Storage\Storage::get( $this->storage_type );

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
		// if ( $this->storage_type === 'theme_mod' ) {
		// 	$value = $this->convert_theme_mod( $value );
		// }

		return $value;
	}


	/**
	 *	@inheritdoc
	 */
	protected function update( $value ) {
		// additional capability check
		if ( in_array( $this->storage_type, array( 'post', 'term' ) ) ) {
			if ( ! $this->storage->permitted() ) {
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
		$obj_id = $section->get_context('id'); // theme_mod? opt?

		if ( false === $this->storage->persist( $section, $value ) ) {
			parent::set_root_value( $value );
		}
	}



	/**
	 *	@inheritdoc
	 */
	public function to_json() {
		parent::to_json();
		$this->json['storage_type'] = $this->storage_type;
	}

}
