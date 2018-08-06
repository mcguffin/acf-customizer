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
		if ( function_exists('acf') && version_compare( acf()->version, '5.6','>=' ) ) {
			Compat\ACF\ACF::instance();
		}
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

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.min' : '';

		if ( version_compare( acf()->version,'5.7','lt' ) ) {
			$control_src = "js/legacy/5.6/admin/customize-acf-fieldgroup-control{$suffix}.js";
		} else {
			$control_src = "js/admin/customize-acf-fieldgroup-control{$suffix}.js";
		}

		wp_register_script( 'jquery-serializejson', $this->get_asset_url( "js/jquery-serializejson{$suffix}.js" ), array( 'jquery' ) );

		wp_register_script(
			'acf-fieldgroup-control',
			$this->get_asset_url( $control_src ),
			array( 'jquery', 'jquery-serializejson', 'customize-controls' )
		);

		wp_register_script(
			'acf-fieldgroup-preview',
			$this->get_asset_url( "js/admin/customize-acf-fieldgroup-preview{$suffix}.js" ),
			array( 'jquery', 'wp-util', 'customize-preview', 'customize-selective-refresh' )
		);
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
