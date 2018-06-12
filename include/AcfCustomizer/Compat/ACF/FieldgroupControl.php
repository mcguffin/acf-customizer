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
	public $type 				= ''; // acf field group key in instances

	protected $post_id			= '';

	protected $field_group_key	= '';


	public function content_template() {

		?>
			<div class="acf-fields" data-field-group-key="<?php echo $this->field_group_key; ?>" data-post-id="<?php echo $this->post_id; ?>">
				YadaYada!
			</div>
		<?php
	}

}
