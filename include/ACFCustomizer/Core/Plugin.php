<?php
/**
 *	@package ACFCustomizer\Core
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFCustomizer\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

class Plugin extends Singleton implements ComponentInterface {

	/** @var string plugin main file */
	private $plugin_file;

	/** @var array metadata from plugin file */
	private $plugin_meta;

	/** @var string plugin components which might need upgrade */
	private static $components = array(
		'ACFCustomizer\Compat\ACF\ACF',
	);

	/**
	 *	@inheritdoc
	 */
	protected function __construct( $file ) {

		$this->plugin_file = $file;

		register_activation_hook( $this->get_plugin_file(), array( $this , 'activate' ) );
		register_deactivation_hook( $this->get_plugin_file(), array( $this , 'deactivate' ) );
		register_uninstall_hook( $this->get_plugin_file(), array( __CLASS__, 'uninstall' ) );

		add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );

		add_action( 'plugins_loaded' , array( $this , 'load_textdomain' ) );

		parent::__construct();
	}

	/**
	 *	@return string full plugin file path
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 *	@return string full plugin file path
	 */
	public function get_plugin_dir() {
		return plugin_dir_path( $this->get_plugin_file() );
	}

	/**
	 *	@return string full plugin url path
	 */
	public function get_plugin_url() {
		return plugin_dir_url( $this->get_plugin_file() );
	}



	/**
	 *	@inheritdoc
	 */
	public function get_asset_roots() {
		return [
			$this->get_plugin_dir() => $this->get_plugin_url(),
		];
	}


	/**
	 *	@return string plugin slug
	 */
	public function get_slug() {
		return basename( $this->get_plugin_dir() );
	}

	/**
	 *	@return string Path to the main plugin file from plugins directory
	 */
	public function get_wp_plugin() {
		return plugin_basename( $this->get_plugin_file() );
	}

	/**
	 *	@return string current plugin version
	 */
	public function version() {
		return $this->get_plugin_meta( 'Version' );
	}

	/**
	 *	@param string $which Which plugin meta to get. NUll
	 *	@return string|array plugin meta
	 */
	public function get_plugin_meta( $which = null ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( ! isset( $this->plugin_meta ) ) {
			$this->plugin_meta = get_plugin_data( $this->get_plugin_file() );
		}
		if ( isset( $this->plugin_meta[ $which ] ) ) {
			return $this->plugin_meta[ $which ];
		}
		return $this->plugin_meta;
	}


	/**
	 *	@action plugins_loaded
	 */
	public function maybe_upgrade() {
		// trigger upgrade
		$new_version = $this->version();
		$old_version = get_site_option( 'acf_customizer_version' );

		// call upgrade
		if ( version_compare($new_version, $old_version, '>' ) ) {

			$this->upgrade( $new_version, $old_version );

			update_site_option( 'acf_customizer_version', $new_version );

		}

	}

	/**
	 *	Load text domain
	 *
	 *  @action plugins_loaded
	 */
	public function load_textdomain() {
		$path = pathinfo( $this->get_wp_plugin(), PATHINFO_DIRNAME );
		load_plugin_textdomain( 'acf-customizer', false, $path . '/languages' );
	}



	/**
	 *	Fired on plugin activation
	 */
	public function activate() {

		$this->maybe_upgrade();

		foreach ( self::$components as $component ) {
			$comp = $component::instance();
			$comp->activate();
		}
	}


	/**
	 *	Fired on plugin updgrade
	 *
	 *	@param string $nev_version
	 *	@param string $old_version
	 *	@return array(
	 *		'success' => bool,
	 *		'messages' => array,
	 * )
	 */
	public function upgrade( $new_version, $old_version ) {

		$result = array(
			'success'	=> true,
			'messages'	=> [],
		);

		foreach ( self::$components as $component ) {
			$comp = $component::instance();
			$upgrade_result = $comp->upgrade( $new_version, $old_version );
			$result['success'] 		&= $upgrade_result['success'];
			$result['messages'][]	=  $upgrade_result['message'];
		}

		return $result;
	}

	/**
	 *	Fired on plugin deactivation
	 */
	public function deactivate() {
		foreach ( self::$components as $component ) {
			$comp = $component::instance();
			$comp->deactivate();
		}
	}

	/**
	 *	Fired on plugin deinstallation
	 */
	public static function uninstall() {
		foreach ( self::$components as $component ) {
			$comp = $component::instance();
			$comp->uninstall();
		}
	}

}
