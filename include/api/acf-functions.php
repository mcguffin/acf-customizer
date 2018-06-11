<?php


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
 *		'type'				=> (string)
 *		'active_callback'	=> (callable)
 *	)
 */
function acf_add_customizer_panel( $panel = '' ) {

	ACFCustomizer\Compat\ACF\Customize::instance()->add_panel( $panel );

}

/**
*	Add Section (child of panel)
*
 *	See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
 *
 *	@param $page array(
 *		'id'					=> (string)
 *		'priority'				=> (int)
 *		'panel'					=> (string)
 *		'capability'			=> (string)
 *		'theme_supports'		=> (string | array)
 *		'title'					=> (string)
 *		'description'			=> (string)
 *		'type'					=> (string)
 *		'active_callback'		=> (callable)
 *		'description_hidden'	=> (boolean)
 *		'post_id' 				=> (string) provide a string to save as option, leave empty to save as theme_mod
 *	)
 */
function acf_add_customizer_section( $section = '' ) {

	ACFCustomizer\Compat\ACF\Customize::instance()->add_section( $section );

}
