<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class Customize extends	Core\Singleton {
	private $panels = array();
	private $sections = array();
	private $field_groups = array();
	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function customize_register( $wp_customize ) {

		foreach ( $this->panels as $panel_id => $panel ) {
			$wp_panel = $wp_customize->add_panel( $panel_id, $panel['args'] );
		}
		foreach ( $this->sections as $section_id => $section ) {
			//continue;
			$wp_section = $wp_customize->add_section( $section_id, $section['args'] );

			$field_groups = acf_get_field_groups( array(
				'customizer' => $section_id,
			) );

			foreach( $field_groups as $field_group ) {
				$this->field_groups[ $field_group['key'] ] = new CustomizeFieldgroup( $wp_customize, $field_group, $section );
			}
		}


	}

	public function add_panel( $panel = '' ) {
		$panel_id = '';
		if ( is_string( $panel ) ) {
			$panel_id = $panel;
			$panel = array();
		}

		if ( isset( $panel['id'] ) ) {
			$panel_id = $panel['id'];
			unset($panel['id']);
		}

		if ( empty( $panel_id ) ) {
			$panel_id = sprintf('acf_customizer_panel_%d', count( $this->panels ) + 1 );
		}

		$panel_args = wp_parse_args( $panel, array(
			'priority'			=> 160, // acf field priority?
			'capability'		=> 'edit_theme_options',
			'theme_supports'	=> array(),
			'title'				=> __( 'ACF Panel', 'acf-customizer' ),
			'description'		=> '',
			'type'				=> 'default',
			'active_callback'	=> '',
		));

		$this->panels[ $panel_id ] = array(
			'id'	=> $panel_id,
			'args'	=> $panel_args,
		);
	}


	public function add_section( $section ) {

		$section_id = '';

		$storage_type = 'theme_mod';

		if ( is_string( $section ) ) {
			$section_id = $section;
			$section = array();
		}

		if ( isset( $section['id'] ) ) {
			$section_id = $section['id'];
			unset($section['id']);
		}

		if ( empty( $section_id ) ) {
			$section_id = sprintf('acf_customizer_section_%d', count( $this->sections ) + 1 );
		}

		if ( isset( $section['storage'] ) ) {
			$storage_type = $section['storage'];
			unset($section['storage'] );
		}



		$section_args = wp_parse_args( $section, array(
			'priority'				=> 160, // acf field priority?
			'panel'					=> null,
			'capability'			=> 'edit_theme_options',
			'theme_supports'		=> array(),
			'title'					=> __( 'ACF Section', 'acf-customizer' ),
			'description'			=> '',
			'type'					=> 'default',
			'active_callback'		=> '',
			'description_hidden'	=> false
		));

		$this->sections[ $section_id ] = array(
			'id'		=> $section_id,
			'args'		=> $section_args,
			'storage'	=> $storage_type,
		);

	}

	public function get_choices() {
		$choices = array();
		foreach ( $this->sections as $key => $element ) {
			$choices[$key] = sprintf( __('Section: %s','acf-customizer'), $element['args']['title']);
		}

		return $choices;
	}
}
