<?php

namespace ACFCustomizer\Compat\ACF\Storage;

class Option extends Storage {



	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		parent::__construct();

		if ( is_customize_preview() && ! is_admin() ) {

			add_filter( 'acf/pre_load_value', [ $this, 'pre_load_value' ], 10, 3 );

		}
	}


	/**
	 *	@filter acf/pre_load_value
	 */
	public function pre_load_value( $value, $post_id, $field ) {

		if ( $this->has_setting_id( $post_id ) ) {
			return $this->get_changeset_value( $field['key'], $value, $post_id );
		}

		return $value;

	}


	/**
	 *	@return boolean
	 */
	public function permitted() {
		return current_user_can('manage_options');
	}

}
