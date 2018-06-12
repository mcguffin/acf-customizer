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

	protected $section_id			= '';


	public function content_template() {

		?>
			<div class="acf-fields" data-section-id="<?php echo $this->section_id; ?>">
				YadaYada!
			</div>
		<?php
	}

}
