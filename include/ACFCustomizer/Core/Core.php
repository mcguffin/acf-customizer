<?php
/**
 *	@package ACFCustomizer\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFCustomizer\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}
use ACFCustomizer\Asset;
use ACFCustomizer\Compat;

class Core extends Plugin implements CoreInterface {


	public $control_css = null;

	public $control_js = null;

	public $preview_css = null;

	public $preview_js = null;

	private $is_loaded = false; 

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {
		add_action( 'plugins_loaded' , array( $this , 'init_compat' ), 0 ); // must run before hook acf/include_location_rules (acf as regular plugin)
		add_action( 'init' , array( $this , 'init_compat' ), 0 ); // must run before hook acf/include_location_rules (acf bundled in theme)
		add_action( 'init' , array( $this , 'init_assets' ) );

		$args = func_get_args();
		parent::__construct( ...$args );
	}

	/**
	 *	Load Compatibility classes
	 *
	 *  @action plugins_loaded
	 */
	public function init_compat() {
		if ( function_exists('acf') && version_compare( acf()->version, '5.6','>=' ) && $this->is_loaded === false) {
			$this->is_loaded = true; //Enshure single instance
			Compat\ACF\ACF::instance();
		}
	}

	/**
	 *	Init hook.
	 *
	 *  @action init
	 */
	public function init_assets() {

		$serialize_handle = Asset\Asset::get( 'js/jquery-serializejson.js' )
			->deps('jquery')
			->register()
			->handle;

		$this->control_js = Asset\Asset::get( 'js/admin/customize-acf-fieldgroup-control.js' )
			->deps( [ 'jquery', $serialize_handle, 'customize-controls' ] )
			->register();

		$this->preview_js = Asset\Asset::get( 'js/admin/customize-acf-fieldgroup-preview.js' )
			->deps( [ 'jquery', 'wp-util', 'customize-preview', 'customize-selective-refresh' ] )
			->register();

		$this->control_css = Asset\Asset::get( 'css/admin/customize-acf-fieldgroup-control.css' )
			->register();

		$this->preview_css = Asset\Asset::get( 'css/admin/customize-acf-fieldgroup-preview.css' )
			->deps( 'customize-preview' )
			->register();


	}


}
