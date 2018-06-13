ACF-Customizer
==============

Bringing ACF Fields to the WordPress Customizer.

Minimum ACF version: 5.6

This plugin is still under heavy development.
Help appreciated.


Todo:
-----
 - [x] Validation
 - [ ] Test Fields
	 - [ ] Repeater
		 - [ ] Doesn't display values if storage is theme_mod > flatten data, like in options table
		 - [ ] Test rows loop with theme_mod data
		 - [ ] Maybe: auto-convert row layout to block
	 - [ ] Layout
 - [ ] Make theme mod defaults equal to acf field defaults
 - [ ] Sanitize callbacks
 - [ ] Support more storage types:
	 - [ ] 'post' on single posts
	 - [ ] 'term' on term archives
	 - [ ] 'user' on user archives
 - [ ] MCE: make script loading conditional (performance)
 - [ ] Source documentation
 - [ ] User documentation



Usage
-----
### Simple

```
$section_id = acf_add_customizer_section( 'A Section' )

// > assign some ACF field groups to A Section

the_field('field_selector', $section_id );
```

### Full
```
$panel_id = acf_add_customizer_panel( 'ACF Customizer' )


acf_add_customizer_section( array(

	'priority'				=> 1000,
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'panel'					=> $panel_id,
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'capability'			=> 'administrator'
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'theme_supports'		=> array(),
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'title'					=> 'A Section'
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'description'			=> 'You can edit some options here.',
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'active_callback'		=> 'is_front_page',
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'description_hidden'	=> false,
		// See https://developer.wordpress.org/reference/classes/wp_customize_manager/add_section/

	'storage_type'			=> 'theme_mod',							
		// theme_mod or option. Default: theme_mod.

	'post_id' 				=> 'a_section_theme_mod',				
		// (string|int) post_id argument passed to get_field(). Default: sanitized title arg
) );

the_field( 'field_selector', 'a_section_theme_mod' );
```
