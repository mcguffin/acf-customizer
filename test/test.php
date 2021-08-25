<?php

namespace ACFCustomizer;

class PluginTest {

	private $current_json_save_path = null;

	public function __construct() {
		add_filter( 'acf/settings/load_json', [ $this, 'load_json' ] );

		add_filter( 'acf/settings/save_json', [ $this, 'save_json' ] );

		add_action( 'acf/delete_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/trash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/untrash_field_group', [ $this, 'mutate_field_group' ], 9 );
		add_action( 'acf/update_field_group', [ $this, 'mutate_field_group' ], 9 );

		add_action( 'init', [ $this, 'init' ] );

//		add_filter( 'template_include', [ $this, 'template_include' ], 99 );
		add_filter('stylesheet_directory',function($dir){
			return dirname(__FILE__).'/templates';
		});

		add_filter('acf/fields/google_map/api', function($api){
			$api['key'] = get_option('google_maps_api_key');
			return $api;
		});
		add_filter( 'the_content', [ $this, 'the_content' ] );

	}

	public function the_content( $content ) {
		$content = '';
		$content .= '<pre>';
		$show = [
			['text','acf_customize_opt_1'],
			['some_color','acf_customize_opt_1'],
			['ambigous_name','acf_customize_opt_1'],
			['text','acf_customize_opt_2'],
			['some_color','acf_customize_opt_2'],
			['ambigous_name','acf_customize_opt_2'],
			['ambigous_name',get_the_ID()],
			['text',get_the_ID()],
		];
		$mods = [
			'acf_customize_mod_1',
		];
		foreach ( $show as $field ) {
			@list( $field_name, $post_id ) = $field;
			$content .= "get_field({$field_name} {$post_id}):\n";
			$content .= var_export(get_field($field_name,$post_id),true)."\n";
			$content .= "\n";
		}
		foreach ( $mods as $mod ) {
			$content .= "get_theme_mod({$mod}):\n";
			$content .= var_export(get_theme_mod($mod),true)."\n";
			$content .= "\n";
		}
		$content .= '</pre>';

		get_template_part('template-parts/acf/repeat-basic');

		return $content;
	}

	// public function template_include( $template ) {
	// 	$file = str_replace( get_stylesheet_directory(), '', $template);
	// 	$test_template = dirname(__FILE__) . '/templates' . $file;
	// 	vaR_dump($test_template);
	// 	if ( file_exists( $test_template ) ) {
	// 		return $test_template;
	// 	}
	// 	return $template;
	// }

	/**
	 *	@action init
	 */
	public function init( $paths ) {

		$mod_id = acf_add_customizer_panel([
			'id'				=> 'acf_cust_mod',
			'priority'			=> 1000,
			'capability'		=> 'manage_options',
			//'theme_supports'	=> '',
			'title'				=> 'Save As Theme Mod',
			'description'		=> '',
			//'active_callback'	=> '',
		]);

		acf_add_customizer_section([
			'priority'				=> 10,
			'panel'					=> $mod_id,
			//'capability'			=> '',
			//'theme_supports'		=> '',
			'title'					=> 'Theme Mod #1',
			'description'			=> '',
			//'active_callback'		=> '',
			'description_hidden'	=> true,
			'storage_type'			=> 'theme_mod',
			'post_id'				=> 'acf_customize_mod_1',
		]);

		$opt_id = acf_add_customizer_panel([
			'id'				=> 'acf_cust_opt',
			'priority'			=> 1010,
			'capability'		=> 'manage_options',
			//'theme_supports'	=> '',
			'title'				=> 'Save in Options',
			'description'		=> '',
			//'active_callback'	=> '',
		]);

		acf_add_customizer_section([
			'priority'				=> 10,
			'panel'					=> $opt_id,
			//'capability'			=> '',
			//'theme_supports'		=> '',
			'title'					=> 'Options #1',
			'description'			=> '',
			//'active_callback'		=> '',
			'description_hidden'	=> true,
			'storage_type'			=> 'option',
			'post_id'				=> 'acf_customize_opt_1',
		]);
		acf_add_customizer_section([
			'priority'				=> 10,
			'panel'					=> $opt_id,
			//'capability'			=> '',
			//'theme_supports'		=> '',
			'title'					=> 'Options #2',
			'description'			=> '',
			//'active_callback'		=> '',
			'description_hidden'	=> true,
			'storage_type'			=> 'option',
			'post_id'				=> 'acf_customize_opt_2',
		]);

		$post_id = acf_add_customizer_panel([
			'id'				=> 'acf_cust_post',
			'priority'			=> 1020,
			'capability'		=> 'manage_options',
			//'theme_supports'	=> '',
			'title'				=> 'Save in Post',
			'description'		=> '',
			//'active_callback'	=> '',
		]);

		acf_add_customizer_section([
			'priority'				=> 10,
			'panel'					=> $post_id,
			//'capability'			=> '',
			//'theme_supports'		=> '',
			'title'					=> 'Post #1',
			'description'			=> '',
			//'active_callback'		=> '',
			'description_hidden'	=> true,
			'storage_type'			=> 'post',
		]);


		$term_id = acf_add_customizer_panel([
			'id'				=> 'acf_cust_term',
			'priority'			=> 1020,
			'capability'		=> 'manage_options',
			//'theme_supports'	=> '',
			'title'				=> 'Save in Term',
			'description'		=> '',
			//'active_callback'	=> '',
		]);

		acf_add_customizer_section([
			'priority'				=> 10,
			'panel'					=> $term_id,
			//'capability'			=> '',
			//'theme_supports'		=> '',
			'title'					=> 'Term #1',
			'description'			=> '',
			//'active_callback'		=> '',
			'description_hidden'	=> true,
			'storage_type'			=> 'term',
		]);


		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( array(
				'page_title'	=> 'ACF Customizer Options',
				'description'	=> 'You are testing the ACF Custmizer Plugin.',
				'post_id'		=> 'acf_customize_opt_1',
				'icon_url'		=> 'dashicons-admin-customizer',
				'autoload'		=> false,
				'parent_slug'	=> 'themes.php',
			) );
		}



	}

	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function load_json( $paths ) {
		$paths[] = dirname(__FILE__).'/acf-json';
		return $paths;
	}

	/**
	 *	@filter 'acf/settings/save_json'
	 */
	public function save_json( $path ) {
		if ( ! is_null( $this->current_json_save_path ) ) {
			return $this->current_json_save_path;
		}
		return $path;
	}

	/**
	 *	Figure out where to save ACF JSON
	 *
	 *	@action 'acf/update_field_group'
	 */
	public function mutate_field_group( $field_group ) {
		// default

		if ( strpos( $field_group['key'], 'group_acf_customizer_' ) === false ) {
			$this->current_json_save_path = null;
			return;
		}
		$this->current_json_save_path = dirname(__FILE__).'/acf-json';

	}
}

new PluginTest();
