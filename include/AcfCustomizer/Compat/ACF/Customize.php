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

		add_action( 'wp_ajax_load_customizer_field_group', array( $this, 'load_field_group' ) );

	}


	/**
	 *	Either previewed converted theme mod from request or actual theme mod from db
	 */
	private function get_current_theme_mod( $post_id ) {
		// if ( is_customize_preview() && isset( $_REQUEST['customized'] ) ) {
		// 	// can be theme mod or option...
		// 	$customized = json_decode( wp_unslash( $_REQUEST['customized'] ), true );
		// 	if ( isset( $customized[ $post_id ] ) ) {
		// 		/*
		// 		$data_source = $this->convert_theme_mod( $customized[ $post_id ] );
		// 		/*/
		// 		$data_source = $customized[ $post_id ];
		// 		//*/
		// 		if (  empty( $data_source ) ) {
		// 			return $data_source;
		// 		}
		// 	}
		// }
		return get_theme_mod( $post_id );
	}

	/**
	 *	Handle theme mod values
	 *
	 *	@filter acf/load_value
	 */
	public function acf_pre_load_value( $value, $post_id, $field ) {

		if ( isset( $this->section_storage_types[ $post_id ] ) ) {
			if ( $this->section_storage_types[ $post_id ] === 'theme_mod' ) {
				$data_source = $this->get_current_theme_mod( $post_id );
				if ( isset( $data_source[ $field['name'] ] ) ) {
					error_log('pre load value: '.$field['name'].' '.var_export($data_source[$field['name']],true));
					return $data_source[ $field['name'] ];
				}
			}
		}
		return $value;
	}

	/**
	 *	@filter pre_set_theme_mod_{$name}
	 */
	public function pre_set_theme_mod( $value, $old_value ) {
		$post_id = substr( current_action(), strlen('pre_set_theme_mod_') );//str_replace( , '',  );
		$value = $this->convert_theme_mod( $value );
		return $value;
	}



	public function convert_theme_mod( $mod ) {
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
//		error_log( "$option: ".var_export($value,true) );
		return true;
	}

	/**
	 *	@filter pre_update_option
	 */
	public function acf_save_option( $value, $option, $old_value ) {

		if ( isset( $this->section_storage_types[ $option ] ) ) {
			if ( $this->section_storage_types[ $option ] === 'option' ) {
				acf_save_post( $option, $value );
				// prevent posted data from being saved!
				return $old_value;
			}
			// else if post, term
		}

		return $value;
	}

	/**
	 *	@filter theme_mod_{$type}
	 */
	public function get_theme_mod( $mod ) {
		if ( is_customize_preview() && isset( $_REQUEST['customized'] ) ) {
			$post_id = substr( current_action(), strlen('theme_mod_') );
			if ( $new_mod = $this->get_currently_customizing( $post_id ) ) {
				return $this->convert_theme_mod( $new_mod );
			}
		}
		return $mod;
	}
	/**
	 *	Whether $theme_mod is currently being customized
	 *
	 *	@return bool
	 */
	private function get_currently_customizing( $theme_mod ) {
		if ( ! is_customize_preview() ) {
			return false;
		}
		if ( ! isset( $_REQUEST['customized'] ) ) {
			return false;
		}
		$customized = json_decode( wp_unslash( $_REQUEST['customized'] ), true );

		if ( ! isset( $customized[ $theme_mod ] ) ) {
			return false;
		}
		error_log('Customized Raw: '.var_export($_REQUEST['customized'],true));
		error_log('Customized: '.var_export($customized,true));
		return $customized[ $theme_mod ];
	}


	public function hidden_wp_editor() {

		?>
			<div class="hidden">
				<?php wp_editor('','acf_content'); ?>
			</div>
		<?php

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
		$section = $this->get_section( $_REQUEST['section_id'] );


		add_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10, 2 );


		ob_start();

		foreach ( $section['field_groups'] as $field_group_key ) {
			$field_group = acf_get_field_group( $field_group_key );
			acf_render_fields( acf_get_fields( $field_group ), $section['post_id'], 'div', $field_group['instruction_placement'] );
		}

		$mce_init = array();
		$html = ob_get_clean();


		// MCE: get editor settings
		ob_start();
		add_action( 'before_wp_tiny_mce', array( $this, 'catch_mce_init' ) );
		// admin_print_footer_scripts will trigger before_wp_tiny_mce at some point
		do_action('admin_print_footer_scripts');
		ob_end_clean();
		// END MCE

		wp_send_json_success( array(
			'html' => $html,
			'mce_init'	=> $this->mce_init,
		) );
	}

	public function catch_mce_init( $settings ) {
		$this->mce_init = $settings;
	}

	/**
	 *	@filter acf/pre_render_fields
	 */
	public function prepare_fields( $fields, $post_id ) {

		foreach ( array_keys( $fields ) as $i ) {

			// no prefix for numeric post_id
			$fields[$i]['prefix'] = '';//ntval( $post_id ) ? '' : $post_id;//$this->current_storage === 'theme_mod' ? 'acf' : '';

		}
		return $fields;
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

				if ( 'theme_mod' === $section['storage_type'] ) {

					add_filter( 'theme_mod_' . $section['post_id'], array( $this, 'get_theme_mod' ), 0xffffffff );
					add_filter( 'pre_set_theme_mod_' . $section['post_id'], array( $this, 'pre_set_theme_mod' ), 0xffffffff, 2 );
				}
			}
		}

		// save options in acf style
		if ( in_array( 'option', $this->section_storage_types ) ) {
			add_action( 'pre_update_option', array( $this, 'acf_save_option' ), 10, 3 );
		}

		//add_filter( 'acf/load_value', array( $this, 'acf_load_value'), 10, 3 );
		add_filter( 'acf/load_value', array( $this, 'acf_pre_load_value' ), 10, 3 );

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

			$wp_section = $wp_customize->add_section( $section['post_id'], $section['args'] );

			foreach( $section['field_groups'] as $field_group_key ) {

				new CustomizeFieldgroup( $wp_customize, $this->field_groups[ $field_group_key ], $section );

			}
		}
	}
	/**
	 *	@action customize_controls_enqueue_scripts
	 */
	public function enqueue_assets() {
		$core = Core\Core::instance();
//
		wp_enqueue_style( 'acf-fieldgroup-control' , $core->get_asset_url( '/css/admin/customize-acf-fieldgroup-control.css' ), array() );



		wp_enqueue_script(
			'acf-fieldgroup-control',
			$core->get_asset_url( 'js/admin/customize-acf-fieldgroup-control.js' ),
			array( 'jquery', 'jquery-serializejson', 'customize-controls' )
		);

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

		if ( empty( $args ) || empty($args['title']) ) {
			return false;
		}

		if ( is_string( $args ) ) {
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
			'active_callback'		=> '',
			'description_hidden'	=> '',
		);
		$section_args = array_intersect_key( $args, $section_defaults );

		$section_args['type']	= 'default';

		if ( empty( $args['post_id'] ) ) {
			$args['post_id'] = sanitize_key( $args['title'] );
		}


		$control_args = array(
			'capability'			=> $args['capability'],
			'theme_supports'		=> $args['theme_supports'],
			'default' 				=> '',//isset($this->acf_field_group['default_value']) ? $this->acf_field['default_value'] : '',
			'section'				=> $args['post_id'],
		);

		$setting_args = array(
			'type'					=> $args['storage_type'], // theme_mod|option
			'setting'				=> $args['post_id'],
			'capability'			=> $args['capability'],
			'theme_supports'		=> $args['theme_supports'],
			'default'				=> array(),
			'transport'				=> 'refresh', // 'postMessage|refresh'
			'sanitize_js_callback'	=> false,
			'dirty'					=> false,
		);

		$this->sections[ $args['post_id'] ] = array(
			'post_id'		=> $args['post_id'],
			'storage_type'	=> $args['storage_type'],
			'field_groups'	=> array(),

			'args'			=> $section_args,
			'control_args'	=> $control_args,
			'setting_args'	=> $setting_args,
		);
		return $args['post_id'];
	}


	public function validate( $validity, $value ) {

		return $validity;
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

}
