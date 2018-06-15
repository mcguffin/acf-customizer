<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class FieldgroupControl extends \WP_Customize_Control {

	/**
	 * Customize control type.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $type 				= 'acf_customizer'; // acf field group key in instances

	public $storage_type		= 'theme_mod'; // acf field group key in instances


	public function __construct( $manager, $id, $args = array() ) {

		parent::__construct( $manager, $id, $args );

		add_action( "wp_ajax_load_customizer_field_groups_{$id}", array( $this, 'load_field_groups' ) );

	}

	/**
	 *	@action wp_ajax_load_customizer_field_groups_{$this->id}
	 */
	public function load_field_groups() {

		// check nonce
		if ( ! wp_verify_nonce($_REQUEST['_nonce'],'load-field-group') ) {
			wp_send_json_error( array(
				'message' => __( 'Bad Nonce.', 'acf-customizer' ),
			) );
		}

		$section = $this->manager->get_section( $this->section );

		$setting = $this->manager->get_setting( $this->setting );

		if ( 'post' === $this->storage_type ) {

			$post_id = $section->get_context('id');

		} else if ( 'term' === $this->storage_type ) {

			$post_id = 'term_' . $section->get_context('id');

		} else if ( 'user' === $this->storage_type ) {

			$post_id = 'user_' . $section->get_context('id');

		} else { // option, theme_mod

			$post_id = $this->id;

		}

		add_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10, 2 );

		ob_start();

		foreach ( $this->setting->field_groups as $field_group_key ) {
			$field_group = acf_get_field_group( $field_group_key );
			acf_render_fields( acf_get_fields( $field_group ), $post_id, 'div', $field_group['instruction_placement'] );
		}

		$mce_init = array();

		if ( ! $section->get_context('id') && in_array( $section->storage_type, array( 'post', 'term', 'user' ) ) ) {
			ob_end_clean();
			$html = '';
		} else {
			$html = ob_get_clean();
		}


		// MCE: get editor settings
		ob_start();
		add_action( 'before_wp_tiny_mce', array( $this, 'catch_mce_init' ) );
		// admin_print_footer_scripts will trigger before_wp_tiny_mce at some point
		do_action('admin_print_footer_scripts');
		ob_end_clean();
		// END MCE

		wp_send_json_success( array(
			'html'		=> $html,
			'mce_init'	=> $this->mce_init,
		) );
	}

	/**
	 *	@action before_wp_tiny_mce
	 */
	public function catch_mce_init( $settings ) {
		$this->mce_init = $settings;
	}


	/**
	 *	@filter acf/pre_render_fields
	 */
	public function prepare_fields( $fields, $post_id ) {

		foreach ( array_keys($fields) as $i ) {
			$fields[$i]['prefix'] = $this->id;
		}

		return $fields;
	}

	/**
	 *	@inheritdoc
	 */
	protected function render_content() {
		return;
	}

	/**
	 *	@inheritdoc
	 */
	public function content_template() {

		?>
			<div class="acf-fields" data-storage-type="{{{ data.storage_type }}}"></div>
		<?php
	}

	/**
	 *	@inheritdoc
	 */
	public function json() {
		$json = parent::json();
		$json['storage_type'] = $this->storage_type;
		return $json;
	}
}
