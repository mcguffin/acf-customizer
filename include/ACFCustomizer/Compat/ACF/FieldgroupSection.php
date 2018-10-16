<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class FieldgroupSection extends \WP_Customize_Section {

	private $context = null;

	public $storage_type		= 'theme_mod'; // acf field group key in instances

	/**
	 *	@inheritdoc
	 */
	public function __construct( $manager, $id, $args = array() ) {

		if ( $args['storage_type'] === 'theme_mod' ) {

			$cb = '';

		} else if ( $args['storage_type'] === 'option' ) {

			$cb = '';

		} else if ( $args['storage_type'] === 'post' ) {

			$cb = array( $this, 'is_post' );

		} else if ( $args['storage_type'] === 'term' ) {

			$cb = array( $this, 'is_term' );

		}

		$args = wp_parse_args( $args, array(
			'active_callback'	=> $cb,
		) );

		parent::__construct( $manager, $id, $args );

		if ( isset( $_REQUEST['acf_customize_context'] ) ) {
			$this->context = json_decode( wp_unslash( $_POST['acf_customize_context'] ) );
		} else {

		}

	}

	/**
	 *	@return mixed
	 */
	public function get_context( $which = null ) {
		if ( is_null( $this->context ) ) {
			return false;
		}
		if ( is_null( $which ) ) {
			return $this->context;
		}
		if ( ! isset( $this->context->$which ) ) {
			return false;
		}
		return $this->context->$which;
	}

	/**
	 *	@inheritdoc
	 */
	public function to_json() {
		$json = parent::json();
		$json['storage_type'] = $this->storage_type;
		return $json;
	}


	/**
	 *	@return bool
	 */
	public function is_post( $section ) {
		return is_singular();
	}

	/**
	 *	@return bool
	 */
	public function is_term( $section ) {
		return is_tag() || is_category() || is_tax();
	}



}
