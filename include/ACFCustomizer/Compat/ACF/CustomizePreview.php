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
			add_filter( 'acf/pre_load_value', array( $this, 'acf_pre_load_value' ), 10, 3 );

			add_action('acf_customizer_field' ,array( $this, 'partial_field' ), 10, 2 );
			add_action('acf_customizer_row', array( $this, 'partial_row' ), 10, 0 );
			add_action('acf_customizer_sub_field', array( $this, 'partial_subfield' ), 10, 1 );

		}
	}

	/**
	 *	@action acf_customizer_field
	 */
	public function partial_field( $selector, $post_id = null ) {
		if ( $path = $this->build_path( $selector, $post_id ) ) {
			echo $this->get_partial_button( $path );
		}
	}

	/**
	 *	@action acf_customizer_row
	 */
	public function partial_row( ) {
		if ( $path = $this->build_path( ) ) {
			echo $this->get_partial_button( $path );
		}
	}


	/**
	 *	@action acf_customizer_sub_field
	 */
	public function partial_subfield( $selector ) {
		if ( $path = $this->build_path( $selector ) ) {
			echo $this->get_partial_button( $path );
		}
	}

	/**
	 *	@param $selector
	 *	@param $post_id
	 */
	private function build_path( $selector = null, $post_id = null ) {

		$path = array();

		$acf_loop = acf()->loop;
		$is_loop = $acf_loop->is_loop();

		if ( is_null( $post_id ) ) {
			if ( $is_loop ) {
				$post_id = acf_get_loop( 'active', 'post_id' );
			} else {
				$post_id = acf_get_valid_post_id();
			}
		}
		if ( ! $post_id ) {
			return false;
		}

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
		}

		$path[] = $field['parent']; // add field group

		$path[] = $post_id;

		return $path;
	}

	private function get_partial_button( $path ) {
		// wp-inclues/js/customize-selective-refresh.js
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></svg>';

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

		$customized = json_decode( wp_unslash( $_REQUEST['customized'] ), true );

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
	 *	@filter acf/pre_load_value
	 */
	public function acf_pre_load_value( $value, $post_id, $field ) {

		return apply_filters( 'acf_get_preview_value', $value, $post_id, $field );

	}

	/**
	 *	@action wp_enueue_scripts
	 */
	public function enqueue_preview() {

		wp_enqueue_script( 'acf-fieldgroup-preview' );

		wp_enqueue_style( 'acf-fieldgroup-preview' );

	}

	public function wp_foot() {

		?>
		<script type="text/javascript">
		var _acf_customize_context = <?php echo json_encode( array( 'current_location' => $this->get_context() ) ) ?>;
		</script>
		<?php
	}

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
