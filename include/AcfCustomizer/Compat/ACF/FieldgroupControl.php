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
	public $type = 'acf'; // acf field group key in instances

	protected $storage = 'theme_mod';

	private $acf_field;

	public function set_field( $acf_field ) {
		$this->acf_field = $acf_field;
	}

	public function content_template() {
//		vaR_dump($this->acf_field);

		// load fields
		$field_group = acf_get_field_group( $this->type );

		$fields = acf_get_fields( $this->type );
		add_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10, 2 );
		?>
		<div class="acf-fields" data-field-group="<?php echo $this->type ?>">
			<#
				<?php echo $this->type ?> = {
					fields: <?php echo json_encode( $fields ); ?>
				}
				console.log(data);
			#>
			<?php
			// render
			acf_render_fields( $fields, $this->storage, 'div', $field_group['instruction_placement'] );
			?>
		</div>
		<?php
		remove_filter( 'acf/pre_render_fields', array( $this, 'prepare_fields' ), 10, 2 );
	}
	public function prepare_fields( $fields, $post_id ) {
		foreach ( array_keys( $fields ) as $i ) {
			$fields[$i]['prefix'] = $this->type;
		}
		return $fields;
	}
}
