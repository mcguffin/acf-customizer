<?php

namespace ACFCustomizer\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

use ACFCustomizer\Compat;

class Core extends Plugin {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'plugins_loaded' , array( $this , 'load_textdomain' ) );
		add_action( 'plugins_loaded' , array( $this , 'init_compat' ) );
		add_action( 'init' , array( $this , 'init' ) );

		parent::__construct();
	}

	/**
	 *	Init Compat Classes
	 *
	 *  @action plugins_loaded
	 */
	public function init_compat() {
		Compat\ACF\ACF::instance();
	}



	/**
	 *	Load text domain
	 *
	 *  @action plugins_loaded
	 */
	public function load_textdomain() {
		$path = pathinfo( dirname( ACF_CUSTOMIZER_FILE ), PATHINFO_FILENAME );
		load_plugin_textdomain( 'acf-customizer' , false, $path . '/languages' );
	}

	/**
	 *	Init hook.
	 *
	 *  @action init
	 */
	public function init() {
		wp_register_script( 'jquery-serializejson', $this->get_asset_url( 'js/jquery-serializejson.js' ), array( 'jquery' ) );
	}

	/**
	 *	Get asset url for this plugin
	 *
	 *	@param	string	$asset	URL part relative to plugin class
	 *	@return wp_enqueue_editor
	 */
	public function get_asset_url( $asset ) {
		return plugins_url( $asset, ACF_CUSTOMIZER_FILE );
	}



}
