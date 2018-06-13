ACF-Customizer
==============

Bringing ACF Fields to the WordPress Customizer.

Minimum ACF version: 5.6

This plugin is still under heavy development.
Help is appreciated.


Todo:
-----
 - [ ] Validation
 - [ ] Sanitize callbacks
 - [ ] Fix for field types
	 - [ ] Content
		 - [ ] WYSIWYG
			 - Sometimes does not init
			 - Does not save
		 - [ ] Gallery
			 - Does not save
			 - No drag drop
			 - needs restyling
	 - [ ] Relational
		 - [ ] Post Object
			 - Doesn't init
		 - [ ] Page Link
			 - Doesn't init
		 - [ ] Relationship
			 - Doesn't init
		 - [ ] User
			 - Doesn't init
	 - [ ] jQuery
		 - [ ] Google Map
			 - Doesn't init
		 - [ ] Date Picker
			 - Doesn't init
		 - [ ] Date Time Picker
			 - Doesn't init
		 - [ ] Time Picker
			 - Doesn't init
		 - [ ] Color Picker
			 - Doesn't init
	 - [ ] Layout
		 - [ ] Message
		 - [ ] Accordion
		 - [ ] Tab
		 - [ ] Group
		 - [ ] Repeater
		 - [ ] Flexible Content
		 - [ ] Clone
 - [ ] Support more storage types:
	 - [ ] 'post' on single posts
	 - [ ] 'term' on term archives
	 - [ ] 'user' on user archives
 - [ ] Source documentation
 - [ ] Better help



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
