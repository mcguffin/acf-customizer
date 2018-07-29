<?php

namespace ACFCustomizer\Compat\ACF\Location;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}


use ACFCustomizer\Core;
use ACFCustomizer\Compat\ACF as CompatACF;


class Customizer extends \acf_location {

	/*
	 *  __construct
	 *
	 *  This function will setup the class functionality
	 *
	 *  @type	function
	 *  @date	5/03/2014
	 *  @since	5.0.0
	 *
	 *  @param	n/a
	 *  @return	n/a
	 */

	function initialize() {

		// vars
		$this->name = 'customizer';
		$this->label = __("Customizer",'acf');
		$this->category = 'forms';

	}


	/*
	 *  rule_match
	 *
	 *  This function is used to match this location $rule to the current $screen
	 *
	 *  @type	function
	 *  @date	3/01/13
	 *  @since	3.5.7
	 *
	 *  @param	$match (boolean)
	 *  @param	$rule (array)
	 *  @return	$options (array)
	 */

	function rule_match( $result, $rule, $screen ) {

		// vars
		$customizer = acf_maybe_get( $screen, 'customizer' );


		// bail early if not widget
		if( !$customizer ) return false;

        // return
        return $this->compare( $customizer, $rule );

	}


	/*
	 *  rule_operators
	 *
	 *  This function returns the available values for this rule type
	 *
	 *  @type	function
	 *  @date	30/5/17
	 *  @since	5.6.0
	 *
	 *  @param	n/a
	 *  @return	(array)
	 */

	function rule_values( $choices, $rule ) {

		// global

		$choices = CompatACF\Customize::instance()->get_choices();

		return $choices;

	}


}
