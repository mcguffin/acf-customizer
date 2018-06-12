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

	public function __construct( $manager, $acf_field_group, $section, $control_class ) {
		$this->acf_field_group = $acf_field_group;
		$this->section = $section;
		$this->initialize( $manager, $control_class );

	}
	private function initialize( $manager, $control_class ) {

		$manager->register_control_type( $control_class );

		$post_id = $this->section['post_id'];

		$setting_args = wp_parse_args( $this->section['setting_args'], array(
			'validate_callback'		=> array( $this, 'validate' ),	// acf validate function
			'sanitize_callback'		=> array( $this, 'sanitize' ),
		));
		$control_args = $this->section['control_args'];

		$manager->add_setting( $post_id, $setting_args );

		$this->control = new $control_class( $manager, $post_id, $control_args );

		$manager->add_control( $this->control );

	}

	public function validate( $value ) {
		return $value;
	}

	public function sanitize( $value ) {
		return $value;
	}

}
