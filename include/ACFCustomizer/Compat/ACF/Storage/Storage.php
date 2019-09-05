<?php

namespace ACFCustomizer\Compat\ACF\Storage;

use ACFCustomizer\Core;

abstract class Storage extends Core\Singleton {

	protected $context = null;

	public $manager = null;

	protected $setting_ids = array();


	/**
	 *	@param string $storage_type
	 *	@return Storage instance
	 */
	final public static function get( $storage_type ) {
		switch ( $storage_type ) {
			case 'theme_mod':
				return ThemeMod::instance();
			case 'option':
				return Option::instance();
			case 'post':
				return Post::instance();
			case 'term':
				return Term::instance();
		}
	}

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		$this->manager = $GLOBALS['wp_customize'];

		$this->set_context();
	}

	/**
	 *	register a post id
	 *
	 *	@param string $post_id
	 */
	public function register_setting_id( $setting_id ) {
		$this->setting_ids[ $setting_id ] = true;
	}


	/**
	 *	check if a post id is registered
	 *
	 *	@param string $post_id
	 *	@return boolean
	 */
	public function has_setting_id( $setting_id ) {

		return isset( $this->setting_ids[ $setting_id ] ) && $this->setting_ids[ $setting_id ];

	}

	/**
	 *
	 */
	protected function set_context() {
		$id = 0;
		$type = '';

		if ( isset( $_REQUEST['acf_customize_context'] ) ) {
			/*
			expected
			[
				id: <int>,
				type: term|post
			]
			*/
			if ( $context = json_decode( wp_unslash( $_REQUEST['acf_customize_context'] ), true ) ) {
				extract( $context );
				$type = in_array( $type, [ 'term', 'post' ] ) ? $type : '';
				$id = intval( $id );
			}
		}

		$this->context = (object) compact( 'id', 'type' );

	}

	/**
	 *	@param mixed $which NULL | 'id' | 'type'
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
	 *	@param string|int $post_id ACF Post ID
	 *	@param string $type post|term|
	 */
	public function is_context( $post_id, $type ) {

		$info = acf_get_post_id_info( $post_id );

		return $info['type'] === $type
			&& $this->get_context('type') === $type
			&& $this->get_context('id') === $info['id'];

		// // no term data requested
		// if ( $info['type'] !== $type ) {
		// 	return $value;
		// }
		// // not term page
		// if ( $this->get_context('type') !== 'term' ) {
		// 	return $value;
		// }
		// // not this term
		// if ( $this->get_context('id') !== $info['id'] ) {
		// 	return $value;
		// }

	}

	/**
	 *	Persistently save data
	 *
	 *	@param ACF\FieldgroupSection $section
	 *	@param array $data
	 *	@return boolean
	 */
	public function persist( $section, $data ) {
		// acf_save_post, ... whtever
		acf_save_post( $section->post_id, $data ); // works for options
		return true;
	}

	/**
	 *	@return boolean
	 */
	public function permitted() {
		return true;
	}


	/**
	 *	Get customized value from Changeset or Request
	 *
	 *	@param string $field_key ACF Field Key
	 *	@param string $fallback What to return if no changeset data found
	 *	@return mixed
	 */
	protected function get_changeset_value( $field_key, $fallback = null ) {

		$changeset_data = false;

		if ( isset( $_POST['customized'] ) ) {
			$changeset_data = json_decode( wp_unslash( $_POST['customized'] ), true );
		}

		if ( ! $changeset_data ) {
			$changeset_data = $this->manager->changeset_data();
			$changeset_data = array_map( [$this, '_flatten_value' ], $changeset_data );
		}

		foreach ( $changeset_data as $key => $data ) {

			if ( $this->has_setting_id( $key ) && isset( $data[ $field_key ] ) ) {
				return $data[ $field_key ];
			}
		}
		return $fallback;
	}

	private function _flatten_value( $el ) {
		if ( isset( $el['value'] ) ) {
			return $el['value'];
		}
		return $el;
	}

}
