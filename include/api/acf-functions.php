<?php



/**
 *	Get a ACFCustomizer instance
 *
 *	@return ACFCustomizer\Compat\ACF\Customize
 */
function acf_customize() {
	return ACFCustomizer\Compat\ACF\Customize::instance();
}

/**
 *	Get registered sections
 *
 *	@return array
 */
function acf_get_customizer_sections() {
	return ACFCustomizer\Compat\ACF\Customize::instance()->get_sections();
}


/**
 *	Get registered sections
 *
 *	@return array
 */
function acf_get_customizer_section( $section_id ) {
	$sections = ACFCustomizer\Compat\ACF\Customize::instance()->get_sections();
	if ( ! isset( $sections[ $section_id ] ) ) {
		return false;
	}
	return $sections[ $section_id ];
}


/**
 *	Add Panel
 *
 *	See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_panel/
 *
 *	@param $panel array(
 *		'id'				=> (string)
 *		'priority'			=> (int)
 *		'capability'		=> (string)
 *		'theme_supports'	=> (string | array)
 *		'title'				=> (string)
 *		'description'		=> (string)
 *		'active_callback'	=> (callable)
 *	)
 *	@return string Panel ID
 */
function acf_add_customizer_panel( $panel = '' ) {

	if ( 'init' !== current_action() && did_action('init') ) {
		_doing_it_wrong( __FUNCTION__, sprintf('%s must not be called after the init hook',__FUNCTION__), '0.0.1' );
	}

	return acf_customize()->add_panel( $panel );

}

/**
*	Add Section (child of panel)
*
 *	See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
 *
 *	@param $page array(
 *		'priority'				=> (int)
 *		'panel'					=> (string)
 *		'capability'			=> (string)
 *		'theme_supports'		=> (string | array)
 *		'title'					=> (string)
 *		'description'			=> (string)
 *		'active_callback'		=> (callable)
 *		'description_hidden'	=> (boolean)
 *		'storage_type'			=> (string)	option|theme_mod|post|term
 *		'post_id' 				=> (string)
 *	)
 *	@return string Section ID
 */
function acf_add_customizer_section( $section = '' ) {

	if ( 'init' !== current_action() && did_action('init') ) {
		_doing_it_wrong( __FUNCTION__, sprintf('%s must not be called after the init hook',__FUNCTION__), '0.0.1' );
	}

	return acf_customize()->add_section( $section );

}
