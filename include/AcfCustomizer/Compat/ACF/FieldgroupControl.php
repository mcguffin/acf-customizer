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
	public $type 				= 'acf_customizer'; // acf field group key in instances

	/**
	 *	@inheritdoc
	 */
	protected function render_content() {
		return;
	}

	/**
	 *	@inheritdoc
	 */
	public function content_template() {

		?>
			<div class="acf-fields"></div>
		<?php
	}

}
