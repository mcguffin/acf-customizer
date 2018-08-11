ACF-Customizer
==============

Bringing ACF Fields to the WordPress Customizer.

Supported ACF versions: 5.6, 5.7

This plugin is still under heavy development.  
Help appreciated.


Todo:
-----
 - [ ] Test Fields
	 - [x] Repeater
	 - [x] Flexible Content
	 - [x] Tab
	 - [x] Accordion
	 - [ ] Group
 - [ ] Make theme mod defaults equal to acf field defaults
 - [ ] Sanitize callbacks?
 - [ ] MCE: make script loading conditional (performance!)
 - [ ] Source documentation
 - [ ] User documentation



Usage
-----
### Simple

```
$section_id = acf_add_customizer_section( 'A Section' )

the_field('field_selector', $section_id );
```

### Full
```
$panel_id = acf_add_customizer_panel( 'ACF Customizer' )


acf_add_customizer_section( array(

	'storage_type'			=> 'theme_mod',
		// Where to store the ACF field data.
		// Values can be `theme_mod`, `option`, `post` or `term`.
		// . Default: theme_mod.

	'post_id' 				=> 'a_section_theme_mod',
		// (string|int) post_id argument passed to get_field(). Default: sanitized title arg
		// Omit if you choose storage type `post` or `term`

	'priority'				=> 1000,
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'panel'					=> $panel_id,
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'capability'			=> 'administrator',
		// Argument passed to `WP_Customize_Manager::add_section()`, `WP_Customize_Manager::add_setting()`, `WP_Customize_Manager::add_control()`,
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_control/
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_setting/

	'theme_supports'		=> array(),
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_setting/

	'title'					=> 'A Section',
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'description'			=> 'You can edit some options here.',
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'active_callback'		=> 'is_front_page',
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'description_hidden'	=> false,
		// Argument passed to `WP_Customize_Manager::add_section()`
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/
) );


the_field( 'acf_field_selector', 'a_section_theme_mod' );

```
