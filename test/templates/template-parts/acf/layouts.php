<div class="acf-layouts">
<?php

global $acf_test_post_id;

printf( '<h3>Layout: %s</h3>', $acf_test_post_id );

while ( have_rows('layout', $acf_test_post_id ) ) {
	the_row();
	do_action( 'acf_customizer_row' );
	get_template_part('template-parts/acf/layout', get_row_layout() );
}

?>
</div>
