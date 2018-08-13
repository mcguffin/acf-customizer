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
	 *	Field groups
	 */
	private $mce_init = array();

	/**
	 *	temporary var
	 */
	private $_converted_theme_mod = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'init', array( $this, 'init' ), 0xffffffff );

		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// must build hidden wp_editor AFTER customize_controls_print_styles
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'hidden_wp_editor' ), 1 );

		add_action( 'init', array( 'ACFCustomizer\Compat\ACF\CustomizePreview', 'instance') );

	}


	/**
	 *	Handle theme mod values
	 *
	 *	@filter acf/load_value
	 */
	public function acf_load_value( $value, $post_id, $field ) {

		if ( isset( $this->section_storage_types[ $post_id ] ) ) {
			$storage_type = $this->section_storage_types[ $post_id ];
			if ( $storage_type === 'theme_mod' ) {
				$data_source = get_theme_mod( $post_id );
				if ( isset( $data_source[ $field['name'] ] ) ) {
					return $data_source[ $field['name'] ];
				}
			}
		}
		return $value;
	}



	/**
	 *	Make sure all wp-editor scripts are loaded
	 *
	 *	@action customize_controls_print_footer_scripts
	 */
	public function hidden_wp_editor() {

		?>
			<div class="hidden">
				<?php wp_editor('','acf_content'); ?>
			</div>
		<?php

	}



	/**
	 *	@action init
	 */
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

				$this->sections[ $section_id ][ 'field_groups' ][] =  $field_group_key;

				$this->section_storage_types[ $section['post_id'] ] = $section['storage_type'];

			}
		}

		add_filter( 'acf/load_value', array( $this, 'acf_load_value' ), 10, 3 );

	}



	/**
	 *	@action customize_register
	 */
	public function customize_register( $wp_customize ) {

		$wp_customize->register_control_type( 'ACFCustomizer\Compat\ACF\FieldgroupControl' );

		foreach ( $this->panels as $panel_id => $panel ) {
			$wp_panel = $wp_customize->add_panel( $panel_id, $panel['args'] );
		}
		foreach ( $this->sections as $section_id => $section ) {
			$section['args']['field_groups'] = array_intersect_key(
				$this->field_groups,
				array_combine(
					array_values( $section['field_groups'] ),
					array_keys( $section['field_groups'] )
				)
			);

			$post_id = $section['post_id'];

			$wp_section = new FieldgroupSection( $wp_customize, $section_id, $section['args'] );

			$wp_customize->add_section( $wp_section );

			$section['setting_args']['field_groups'] = $section['field_groups'];


			$wp_setting = new FieldgroupSetting( $wp_customize, $section_id, $section['setting_args'] );

			$wp_customize->add_setting( $wp_setting );

			$wp_control = new FieldgroupControl( $wp_customize, $section_id, $section['control_args'] );

			$wp_customize->add_control( $wp_control );

		}
	}

	/**
	 *	@action customize_controls_enqueue_scripts
	 */
	public function enqueue_assets() {
		$core = Core\Core::instance();

		wp_enqueue_style( 'acf-fieldgroup-control' , $core->get_asset_url( '/css/admin/customize-acf-fieldgroup-control.css' ), array() );

		wp_enqueue_script( 'acf-fieldgroup-control' );

		wp_localize_script('acf-fieldgroup-control' , 'acf_fieldgroup_control' , array(
			'load_field_group_nonce'	=> wp_create_nonce('load-field-group'),
		) );
	}

	/**
	 *	@param array $args see function acf_add_customizer_panel
	 */
	public function add_panel( $args = '' ) {

		if ( empty( $args ) || empty( $args['title'] ) ) {
			return false;
		}

		if ( is_string( $args ) ) {
			$args = array(
				'title'	=> $args,
			);
		}


		$defaults = array(
			'id'				=> '',
			'priority'			=> 160, // acf field priority?
			'capability'		=> 'edit_theme_options',
			'theme_supports'	=> array(),
			'title'				=> '',
			'description'		=> '',
			'active_callback'	=> '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['type']	= 'default';

		if ( empty( $args['id'] ) ) {

			$id_base = sanitize_key( $args['title'] );

			$args['id'] = sanitize_key( $args['title'] );

			while ( isset( $this->sections[ $args['id'] ] ) ) {

				$args['id'] = sprintf( $id_base . '_%d', count( $this->sections ) + 1 );

			}

		}


		if ( empty( $args['id'] ) ) {
			$args['id'] = sprintf('acf_customizer_panel_%d', count( $this->panels ) + 1 );
		}

		$this->panels[ $args['id'] ] = array(
			'id'	=> $args['id'],
			'args'	=> $args,
		);
		return $args['id'];
	}

	/**
	 *	@param $args see function acf_add_customizer_section
	 */
	public function add_section( $args ) {
		if ( empty( $args ) || empty( $args['title'] ) ) {
			return false;
		}
		$_str_args = false;
		if ( is_string( $args ) ) {
			$_str_args = true;
			$args = array(
				'post_id'	=> sanitize_key( $args ),
				'title'		=> $args,
			);
		}

		$defaults = array(
			'priority'				=> 160,
			'panel'					=> null,
			'capability'			=> 'edit_theme_options',
			'theme_supports'		=> array(),
			'title'					=> '',
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
			'storage_type'			=> 'theme_mod',
			'description_hidden'	=> '',
			'active_callback'		=> '',
		);
		$section_args = array_intersect_key( $args, $section_defaults );

		$section_args['type']	= 'default';

		if ( empty( $args['post_id'] ) ) {
			$args['post_id'] = sanitize_key( $args['title'] );
		}

		$section_id = $args['post_id'];

		if ( ! $_str_args ) {
			// make unique $section_id if post_id was specified explicitely
			$i = 0;
			while ( isset( $this->sections[ $section_id ] ) ) {
				$section_id = sprintf('%s_%d', $args['post_id'], ++$i );
			}
		}


		$control_args = array(
			'capability'			=> $args['capability'],
			'theme_supports'		=> $args['theme_supports'],
			'default' 				=> '',
			'section'				=> $section_id,
			'storage_type'			=> $args['storage_type'],
		);

		$setting_args = array(
			'type'					=> $args['storage_type'] === 'theme_mod' ? 'theme_mod' : 'option', // theme_mod|option
			'setting'				=> $section_id,
			'capability'			=> $args['capability'],
			'theme_supports'		=> $args['theme_supports'],
			'default'				=> array(),
			'transport'				=> 'refresh', // 'postMessage|refresh'
			'sanitize_js_callback'	=> false,
			'dirty'					=> false,
			'storage_type'			=> $args['storage_type'],
		);

		$this->sections[ $section_id ] = array(
			'id'			=> $section_id,
			'post_id'		=> $args['post_id'],
			'storage_type'	=> $args['storage_type'],
			'field_groups'	=> array(),

			'args'			=> $section_args,
			'control_args'	=> $control_args,
			'setting_args'	=> $setting_args,
		);
		return $section_id;
	}

	/**
	 *	@return array
	 */
	public function get_sections() {
		return array_values( $this->sections );
	}

	/**
	 *	@return boolean
	 */
	public function validate( $validity, $value ) {

		return $validity;
	}

	/**
	 *	@return null|array
	 */
	public function get_section( $section_id ) {
		if ( isset( $this->sections[ $section_id ] ) ) {
			return $this->sections[ $section_id ];
		}
	}

	/**
	 *	@return array
	 */
	public function get_choices() {
		$choices = array();
		foreach ( $this->sections as $key => $element ) {
			$choices[$key] = $element['args']['title'];
		}

		return $choices;
	}

}
