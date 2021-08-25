<?php

namespace ACFCustomizer\Compat\ACF\Storage;

use ACFCustomizer\ACF;

class Term extends Storage {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		parent::__construct();

		add_action( 'customize_update_term', [ $this, 'customize_update'], 10, 2 );

		if ( is_customize_preview() && ! is_admin() ) {

			add_action( 'template_redirect', [ $this, 'set_preview_context' ] );

			add_filter( 'acf/pre_load_value', [ $this, 'pre_load_value' ], 10, 3 );

		}
	}

	/**
	 *	@filter acf/pre_load_value
	 */
	public function pre_load_value( $value, $post_id, $field ) {

		if ( $this->is_context( $post_id, 'term' ) ) {
			return $this->get_changeset_value( $field, $value );
		}

		return $value;

	}

	/**
	 *	@action template_redirect
	 */
	public function set_preview_context() {

		if ( ($obj = get_queried_object()) && ( $obj instanceOf \WP_Term) ) {

			$this->context = (object) [
				'id'	=> $obj->term_id,
				'type'	=> 'term',
			];
		}
	}

	/**
	 *	Hooked into WP Action customize_update_{$type}
	 *
	 *	@param array $data Data to save
	 *	@param FieldgroupSetting $customize_setting
	 *
	 *	@action customize_update_term
	 */
	public function customize_update( $data, $customize_setting ) {
		$post_id = $this->get_context('id');

		$post_id = "term_{$post_id}";

		acf_save_post( $post_id, $data ); // works for options
	}

	/**
	 *	@return boolean
	 */
	public function permitted() {
		return current_user_can( 'edit_term', $this->get_context('id') );
	}

}
