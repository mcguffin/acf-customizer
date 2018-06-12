ACF-Customizer
==============

Bringing ACF Fields to the WordPress Customizer.

Todo:
-----
 - [ ] Test with field types
	 - [ ] Basic
		 - [ ] Text
		 - [ ] Text Area
		 - [ ] Number
		 - [ ] Range
		 - [ ] Email
		 - [ ] URL
	 - [ ] Content
		 - [ ] Image
		 - [ ] File
		 - [ ] WYSIWYG
		 - [ ] oEmbed
		 - [ ] Gallery
	 - [ ] Choice
		 - [ ] Select
		 - [ ] Checkbox
		 - [ ] Radio Button
		 - [ ] Button Group
		 - [ ] True / False
	 - [ ] Relational
		 - [ ] Link
		 - [ ] Post Object
		 - [ ] Page Link
		 - [ ] Relationship
		 - [ ] Taxonomy
		 - [ ] User
	 - [ ] jQuery
		 - [ ] Google Map
		 - [ ] Date Picker
		 - [ ] Date Time Picker
		 - [ ] Time Picker
		 - [ ] Color Picker
	 - [ ] Layout
		 - [ ] Message
		 - [ ] Accordion
		 - [ ] Tab
		 - [ ] Group
		 - [ ] Repeater
		 - [ ] Flexible Content
		 - [ ] Clone
 - [ ] More storage types:
	 - [ ] 'post' on single posts
	 - [ ] 'term' on term archives
	 - [ ] 'user' on user archives



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
