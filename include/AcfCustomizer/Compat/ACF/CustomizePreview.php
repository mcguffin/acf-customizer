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

		}
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


	public function enqueue_preview() {
		$core = Core\Core::instance();
		wp_enqueue_script(
			'acf-fieldgroup-preview',
			$core->get_asset_url( 'js/admin/customize-acf-fieldgroup-preview.js' ),
			array( 'jquery', 'wp-util', 'customize-preview', 'customize-selective-refresh' )
		);
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
			} else if ( $obj instanceof \WP_User ) {
				$loc['type'] = 'user';
				$loc['id'] = $obj->ID;
			}
		}
		return (object) $loc;
	}

}
