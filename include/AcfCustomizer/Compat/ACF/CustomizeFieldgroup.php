<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class CustomizeFieldgroup {

	public $id;

	private $acf_field_group;

	private $section;

	private $control;

	public function __construct( $manager, $acf_field_group, $section ) {
		$this->acf_field_group = $acf_field_group;
		$this->section = $section;
		$this->initialize( $manager );

	}
	private function initialize( $manager ) {

		$post_id = $this->section['post_id'];

		$setting_args = wp_parse_args( $this->section['setting_args'], array(
			'validate_callback'		=> array( $this, 'validate' ),	// acf validate function
			'sanitize_callback'		=> array( $this, 'sanitize' ),
		));
		$control_args = $this->section['control_args'];

		$manager->add_setting( $post_id, $setting_args );

		$this->control = new FieldgroupControl( $manager, $post_id, $control_args );

		$manager->add_control( $this->control );

	}

	public function validate( $validity, $value ) {

		// We have to use acf validation here, which is heavyly depending on the browser

		return $validity;

		acf_reset_validation_errors();
		acf_validate_values($value);

		if ( $errs = acf_get_validation_errors() )

		foreach ( $errs as $err ) {
			$validity->add( $err['input'], $err['message'] );
		}

		return $validity;
	}

	public function sanitize( $value ) {
		return $value;
	}

}
