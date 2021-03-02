<?php

namespace ACFCustomizer\Compat\ACF;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;



class CustomizePreview extends Core\Singleton {

	private $_changeset_data = null;

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		if ( is_customize_preview() && ! is_admin() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview' ) );

			add_action( 'wp_footer', array( $this, 'wp_foot' ), 2000 );

			add_action( 'acf_customizer_field' ,array( $this, 'partial_field' ), 10, 2 );
			add_action( 'acf_customizer_row', array( $this, 'partial_row' ), 10, 0 );
			add_action( 'acf_customizer_sub_field', array( $this, 'partial_subfield' ), 10, 1 );

		}
	}


	/**
	 *	@action acf_customizer_field
	 */
	public function partial_field( $selector, $post_id = null ) {
		if ( $path = $this->build_path( $selector, $post_id ) ) {
			echo $this->get_partial_button( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 *	@action acf_customizer_row
	 */
	public function partial_row( ) {
		if ( $path = $this->build_path( ) ) {
			echo $this->get_partial_button( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}


	/**
	 *	@action acf_customizer_sub_field
	 */
	public function partial_subfield( $selector ) {
		if ( $path = $this->build_path( $selector ) ) {
			echo $this->get_partial_button( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 *	Build path from
	 *
	 *	@param string $selector If null will build path based on ACF loop hierarchy. If not get path from field hierarchy.
	 *	@param string|int $post_id If null will guess current post id.
	 *	@return array
	 */
	private function build_path( $selector = null, $post_id = null ) {

		$path = array();
		$section_id = false;
		$post_id_info = false;

		$acf_loop = acf()->loop;
		$is_loop = $acf_loop->is_loop();

		$customize = Customize::instance();

		if ( is_null( $post_id ) ) {
			if ( $is_loop ) {
				$post_id = acf_get_loop( 'active', 'post_id' );
			} else {
				$post_id = acf_get_valid_post_id();
			}

		}

		$post_id_info = acf_get_post_id_info( $post_id );


		if ( ! is_null( $selector ) ) {

			if ( $is_loop ) {
				if ( $field = get_sub_field_object( $selector, $post_id ) ) {
					$path[] = $field['key'];
				}
			} else {
				if ( $field = get_field_object( $selector, $post_id ) ) {
					$path[] = $field['key'];
				}
			}
		}

		if ( $is_loop ) {
			$loops = $acf_loop->loops;

			// iterate inside out
			foreach ( array_reverse( array_keys( $loops ) ) as $loop_i ) {
				$loop = $loops[ $loop_i ];
				$field = $loop['field'];
				$path[] = $loop['i'];
				$path[] = $field['key'];
			}
		} else {
			$field = get_field_object( $selector, $post_id );
		}
		$field_group_key = $field['parent'];

		$path[] = $field_group_key; // field group

		if ( $post_id_info['type'] === 'option' ) {
			$section_id = $customize->get_section_id_by_fieldgroup_post_id( $field_group_key, $post_id );
		} else {
			$section_id = $customize->get_section_id_by_fieldgroup_storage( $field_group_key, $post_id_info['type'] );
		}

		if ( ! $section_id ) {
			return false;
		}

		$path[] = $section_id;

		return $path;
	}

	/**
	 *	@param array $path Path to customize control
	 */
	private function get_partial_button( $path ) {
		// pencil
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></svg>';

		// chain
		// $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M7.6,15.2l3.7-3.7c0.2,0.6,0.2,1.3,0,2s-0.5,1.3-1,1.8l-1.4,1.4c-0.7,0.6-1.6,1-2.7,1s-2-0.4-2.7-1.1c-0.8-0.8-1.1-1.7-1.1-2.7s0.4-2,1.1-2.7l1.4-1.5c0.5-0.5,1.1-0.8,1.8-1s1.3-0.2,2,0L5,12.4c-0.4,0.4-0.6,0.8-0.6,1.4c0,0.5,0.2,1,0.6,1.4s0.8,0.6,1.4,0.6S7.2,15.6,7.6,15.2z M8.3,13.2l4.8-4.8c0.2-0.2,0.3-0.4,0.3-0.7c0-0.3-0.1-0.5-0.3-0.7c-0.2-0.2-0.5-0.3-0.7-0.3c-0.3,0-0.5,0.1-0.7,0.3l-4.8,4.8c-0.2,0.2-0.3,0.4-0.3,0.7s0.1,0.5,0.3,0.7c0.2,0.2,0.4,0.3,0.7,0.3C7.9,13.4,8.1,13.3,8.3,13.2z M16.5,3.6c0.8,0.8,1.1,1.7,1.1,2.7c0,1.1-0.4,2-1.1,2.7l-1.4,1.4c-0.5,0.5-1.1,0.8-1.8,1s-1.3,0.2-2,0l2.4-2.3l0.7-0.7l0.7-0.7c0.4-0.4,0.6-0.8,0.6-1.4s-0.2-1-0.6-1.4s-0.8-0.6-1.4-0.6c-0.5,0-1,0.2-1.4,0.6l-0.7,0.7l-3,3C8.5,8,8.5,7.3,8.7,6.7s0.5-1.3,1-1.8l1.4-1.4c0.8-0.8,1.7-1.1,2.7-1.1S15.7,2.8,16.5,3.6z"/></svg>';

		// curly arrow left
		// $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M18.1,13.8c0-3.3-2.7-6-6-6c-0.6,0-2.3,0-3.6,0V3.5L1.9,10l6.4,6.4v-4.3c1.7,0,4.3,0,4.7,0c2.1,0,3.9,1.7,3.9,3.9c0,0.8-0.2,1.5-0.6,2.1C17.4,16.9,18.1,15.4,18.1,13.8z"/></svg>';

		$btn = sprintf('<button data-acf-customizer="%s" aria-label="%s" title="%s" class="customize-partial-edit-shortcut-button">%s</button>',
				esc_attr( json_encode($path) ),
				__( 'Click to edit this element.' ),
				__( 'Click to edit this element.' ),
				$svg
			);
		return sprintf( '<span class="customize-partial-edit-shortcut">%s</span>', $btn );
	}

	/**
	 *	@return array
	 */
	public function changeset_data( $manager ) {
		if ( ! is_null( $this->_changeset_data ) ) {
			return $this->_changeset_data;
		}

		$data = $manager->changeset_data();

		if ( ! isset( $_REQUEST['customized'] ) ) {
			return $data;
		}

		$customized = json_decode( wp_unslash( $_REQUEST['customized'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $customized ) ) {
			return $data;
		}

		foreach ( $customized as $key => $value ) {

			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = array(
					'value'	=> array(),
				);
			}
			$data[ $key ]['value'] = $value;
		}

		$this->_changeset_data = $data;

		return $this->_changeset_data;

	}

	/**
	 *	@action wp_enueue_scripts
	 */
	public function enqueue_preview() {

		$core = Core\Core::instance();

		$core->preview_css->enqueue();
		$core->preview_js->enqueue();

	}

	/**
	 *	@action wp_footer
	 */
	public function wp_foot() {

		?>
		<script type="text/javascript" id="acf-customize-context">
		var _acf_customize_context = <?php echo json_encode( array( 'current_location' => $this->get_context() ) ) ?>;
		</script>
		<?php
	}

	/**
	 *	@return
	 */
	public function get_context() {
		$obj = get_queried_object();
		$loc = array(
			'type'	=> null,
			'id'	=> null,
		);

		if (is_object($obj)) {
			if ( $obj instanceOf \WP_Post ) {
				$loc['type'] = 'post';
				$loc['id'] = $obj->ID;
			} else if ( $obj instanceOf \WP_Term ) {
				$loc['type'] = 'term';
				$loc['id'] = $obj->term_id;
			} else if ( $obj instanceOf \WP_Post_Type ) {
				$loc['type'] = 'post_type';
				$loc['id'] = $obj->name;
			}
		}
		return (object) $loc;
	}

}
