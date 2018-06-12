<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class Customize extends	Core\Singleton {
	/**
	 *	registered panels
	 */
	private $panels = array();

	/**
	 *	registered sections
	 */
	private $sections = array();

	/**
	 *	Mapping $post_id <=> storage type
	 */
	private $section_storage_types = array();

	/**
	 *	Field groups
	 */
	private $field_groups = array();

	/**
	 *	Field group classnames
	 *	Used in JS for registering customize-control constructors
	 */
	private $field_group_types = array();

	/**
	 *	?
	 */
	private $section_field_groups = array();

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ), 0xffffffff );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_load_customizer_field_group', array( $this, 'load_field_group' ) );
	}

	/**
	 *	Handle custimizer preview
	 *
	 *	@filter acf/load_value
	 */
	public function acf_load_value( $value, $post_id, $field ) {

		if ( isset( $this->section_storage_types[ $post_id ] ) ) {
			$data_source = array();
			// sanitize!!!
			if ( isset( $_REQUEST['customized'] ) ) {
				$customized = json_decode( wp_unslash( $_REQUEST['customized'] ), true );
				if ( isset( $customized[ $post_id ] ) ) {
					$data_source = $this->convert_theme_mod( $customized[ $post_id ] );
				}
			} else if ( $this->section_storage_types[ $post_id ] === 'theme_mod' ) {
				$data_source = get_theme_mod( $post_id );
			}
			if ( isset( $data_source[ $field['name'] ] ) ) {
				$value = $data_source[ $field['name'] ];
			}
		}
		return $value;
	}

	/**
	 *	@filter pre_update_option
	 */
	public function acf_save_option( $value, $option, $old_value ) {

		if ( isset( $this->section_storage_types[ $option ] ) && $this->section_storage_types[ $option ] === 'option' ) {
			acf_save_post( $option, $value );
			return $old_value;
		}

		return $value;
	}
	/**
	 *	@filter pre_set_theme_mod_{$type}
	 */
	public function acf_save_theme_mod( $value, $old_value ) {
return $value;
		return $this->convert_theme_mod( $value );

	}

	/**
	 *	@filter theme_mod_{$type}
	 */
	public function convert_theme_mod( $mod ) {
		foreach ( array_keys($mod) as $field_key ) {
			// need field group here!
			$field = acf_get_field( $field_key );
			// save like in options|postmeta|whereever|...
			$mod[ $field['name'] ] = $mod[ $field_key ];
			$mod[ '_' . $field['name'] ] = $field_key;
			unset( $mod[ $field_key ] );
		}
		return $mod;
	}
	/**
	 *	@action wp_ajax_load_customizer_field_group
	 */
	public function load_field_group() {
		// check nonce
		if ( ! wp_verify_nonce($_REQUEST['_nonce'],'load-field-group') ) {
			wp_send_json_error( array(
				'message' => __( 'Bad Nonce.', 'acf-customizer' ),
			) );
		}

		$field_group = acf_get_field_group( $_REQUEST['field_group_key'] );

		$post_id = $_REQUEST['post_id'];

		add_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10, 2 );

		ob_start();
		acf_render_fields( acf_get_fields( $field_group ), $post_id, 'div', $field_group['instruction_placement'] );
		$html = ob_get_clean();

		remove_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10 );

		wp_send_json_success( array(
			'html' => $html,
		) );
	}

	/**
	 *	@filter acf/pre_render_fields
	 */
	public function prepare_fields( $fields, $post_id ) {

		foreach ( array_keys( $fields ) as $i ) {

//			$fields[$i] = acf_get_field( $fields[$i]['key'], $post_id );

			// no prefix for numeric post_id
			$fields[$i]['prefix'] = '';//ntval( $post_id ) ? '' : $post_id;//$this->current_storage === 'theme_mod' ? 'acf' : '';

		}
		return $fields;
	}


	public function init() {


		foreach ( $this->sections as $section_id => $section ) {
			// post_id <=> storage_type
			$this->section_storage_types[ $section['post_id'] ] = $section['setting_args']['type'];

			//continue;
			$field_groups = acf_get_field_groups( array(
				'customizer' => $section_id,
			) );

			foreach( $field_groups as $field_group ) {

				$field_group_key = $field_group['key'];

				if ( ! isset( $this->field_groups[ $field_group_key ] ) ) {
					$this->field_groups[ $field_group_key ] = $field_group;
				}

				$control_class = $this->get_control_class( $field_group_key, $section, false );

				$this->field_group_types[] = $control_class;
				$this->sections[ $section_id ][ 'field_groups' ][] =  $field_group_key;

				$this->section_storage_types[ $section['post_id'] ] = $section['storage_type'];

				if ( 'theme_mod' === $section['storage_type'] ) {

					add_filter( 'theme_mod_' . $section['post_id'], array( $this, 'convert_theme_mod' ), 0xffffffff );

				}
			}
		}

		// save options in acf style
		if ( in_array( 'option', $this->section_storage_types ) ) {
			add_action( 'pre_update_option', array( $this, 'acf_save_option' ), 10, 3 );
		}

		add_filter( 'acf/load_value', array( $this, 'acf_load_value'), 10, 3 );

	}


	public function enqueue_scripts() {

		$core = Core\Core::instance();
		wp_enqueue_style( 'acf-fieldgroup-control' , $core->get_asset_url( '/css/admin/customize-acf-fieldgroup-control.css' ) );

		wp_enqueue_script(
			'acf-fieldgroup-control',
			$core->get_asset_url( 'js/admin/customize-acf-fieldgroup-control.js' ),
			array( 'jquery', 'jquery-serializejson', 'customize-controls' )
		);

		wp_localize_script('acf-fieldgroup-control' , 'acf_fieldgroup_control' , array(
			'field_group_types'			=> $this->field_group_types,
			'load_field_group_nonce'	=> wp_create_nonce('load-field-group'),
		) );
	}


	/**
	 *	@action customize_register
	 */
	public function customize_register( $wp_customize ) {

		foreach ( $this->panels as $panel_id => $panel ) {
			$wp_panel = $wp_customize->add_panel( $panel_id, $panel['args'] );
		}
		foreach ( $this->sections as $section_id => $section ) {

			$wp_section = $wp_customize->add_section( $section_id, $section['args'] );

			foreach( $section['field_groups'] as $field_group_key ) {

				new CustomizeFieldgroup( $wp_customize, $this->field_groups[ $field_group_key ], $section, $this->get_control_class( $field_group_key, $section ) );

			}
		}
	}


	/**
	 *
	 */
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
			'active_callback'	=> '',
		));

		$panel_args = wp_parse_args( array(
			'type'				=> 'default',
		), $panel );

		$this->panels[ $panel_id ] = array(
			'id'	=> $panel_id,
			'args'	=> $panel_args,
		);
	}


	public function add_section( $args ) {

		if ( is_string( $args ) ) {
			$args = array(
				'id'	=> $args,
			);
		}


		$defaults = array(
			'id'					=> '',
			'priority'				=> 160,
			'panel'					=> null,
			'capability'			=> 'edit_theme_options',
			'theme_supports'		=> array(),
			'title'					=> __( 'ACF Section', 'acf-customizer' ),
			'description'			=> '',
			'active_callback'		=> '',
			'description_hidden'	=> false,
			'storage_type'			=> 'theme_mod',
			'post_id' 				=> '',
		);

		$args = wp_parse_args( $args, $defaults );

		$section_defaults = array(
			'priority'				=> '',
			'panel'					=> '',
			'capability'			=> '',
			'theme_supports'		=> '',
			'title'					=> '',
			'description'			=> '',
			'active_callback'		=> '',
			'description_hidden'	=> '',
		);
		$section_args = array_intersect_key( $args, $section_defaults );

		$section_args['type']	= 'default';

		if ( empty( $args['id'] ) ) {
			$args['id'] = sprintf('acf_customizer_section_%d', count( $this->sections ) + 1 );
		}

		if ( empty( $args['post_id'] ) ) {
			$args['post_id'] = $args['id'];
		}


		$control_args = array(
			'capability'			=> $args['capability'],
			'theme_supports'		=> $args['theme_supports'],
			'default' 				=> '',//isset($this->acf_field_group['default_value']) ? $this->acf_field['default_value'] : '',
			'transport'				=> 'refresh', // 'postMessage|refresh'
//			'validate_callback'		=> array( $this, 'validate' ),	// acf validate function
//			'sanitize_callback'		=> array( $this, 'sanitize' ),
			'sanitize_js_callback'	=> false,
			'dirty'					=> false,
			'section'				=> $args['id'],
		);

		$setting_args = array(
			'type'			=> $args['storage_type'], // theme_mod|option
			'setting'		=> $args['post_id'],
			'capability'	=> $args['capability'],
			'section'		=> $args['id'],
		);

		$this->sections[ $args['id'] ] = array(
			'id'			=> $args['id'],
			'post_id'		=> $args['post_id'],
			'storage_type'	=> $args['storage_type'],
			'field_groups'	=> array(),

			'args'			=> $section_args,
			'control_args'	=> $control_args,
			'setting_args'	=> $setting_args,
		);
		return $args['id'];
	}

	public function get_section( $section_id ) {
		if ( isset( $this->sections[ $section_id ] ) ) {
			return $this->sections[ $section_id ];
		}
	}

	public function get_choices() {
		$choices = array();
		foreach ( $this->sections as $key => $element ) {
			$choices[$key] = $element['args']['title'];
		}

		return $choices;
	}

	private function get_control_class( $field_group_key, $section, $generate = true ) {
//		$key = $this->acf_field_group['key'];

		$post_id = $section['post_id'];
		$class = str_replace( '-', '_', sanitize_key( $field_group_key . '_' . $post_id ) );

		if ( ! $generate || class_exists( $class ) ) {
			return $class;
		}

		$code = "class {$class} extends ACFCustomizer\Compat\ACF\FieldgroupControl {
			public \$type = '{$class}';
			protected \$field_group_key = '{$field_group_key}';
			protected \$post_id = '{$post_id}';
		}";
		eval($code);

		return $class;
	}


}