<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class CustomizeFieldgroup {
	private $acf_field_group;
	private $section;
	private $control;

	public function __construct( $manager, $acf_field_group, $section ) {
		$this->acf_field_group = $acf_field_group;
		$this->section = $section;
		$this->initialize( $manager );
	}
	private function initialize( $manager ) {
		$id = $this->acf_field_group['key'];

		$control_class = $this->get_control_class();

		$manager->register_control_type( $control_class );

		$manager->add_setting( $id, array(
			'type'					=> $this->section['storage'], // theme_mod|option
			'capability'			=> $this->section['args']['capability'],
			'theme_supports'		=> array(),
			'default' 				=> '',//isset($this->acf_field_group['default_value']) ? $this->acf_field['default_value'] : '',
			'transport'				=> 'refresh', // 'postMessage|refresh'
			'validate_callback'		=> array( $this, 'validate' ),	// acf validate function
			'sanitize_callback'		=> array( $this, 'sanitize' ),
			'sanitize_js_callback'	=> false,
			'dirty'					=> false,
		) );

		$this->control = new $control_class( $manager, $id, array(
			'setting'		=> $id,
			'capability'	=> $this->section['args']['capability'],
			'section'		=> $this->section['id'],
		) );
		$this->control->set_field( $this );

		// <TEST#
		$manager->add_setting( 'fg_test_set', array(
			'type'		=> 'theme_mod',
			//'transport'	=> 'postMessage', // 'postMessage|refresh'
			'default'	=> '',
		));
		$manager->add_control( 'fg_test_set', array(
			'type'		=> 'text',
			'setting'	=> 'fg_test_set',
			'capability'	=> $this->section['args']['capability'],
			'section'	=> $this->section['id'],
			'priority'	=> 1,
		) );
		// #TEST>

		$manager->add_control( $this->control );

	}

	private function get_control_class() {
		$type = $this->acf_field_group['key'];
		$storage = isset($this->section['post_id']) ? $this->section['post_id'] : 'theme_mod';
		$class = sanitize_key($type . '_' . str_replace( '-', '_', $storage ) );
		$code = "class {$class} extends ACFCustomizer\Compat\ACF\FieldgroupControl {
			public \$type = '{$type}';
			protected \$storage = '{$storage}';
		}";
		eval($code);
		return $class;
	}

	public function get_acf_field() {
		return $this->acf_field;
	}

	public function validate( $value ) {
		return $value;
	}

	public function sanitize( $value ) {
		return $value;
	}

}
