<?php

global $acf_test_post_id;

printf( '<h3>Basic: %s</h3>', $acf_test_post_id );

?>
<dl>
	<?php

	foreach ( [ 'text', 'textarea', 'number', 'range', 'e-mail', 'url', 'ambigous_name' ] as $selector ) {
		?>
		<dt><?php
			do_action( 'acf_customizer_field', $selector, $acf_test_post_id );
			echo $selector;
		?></dt>
		<dd><?php the_field( $selector, $acf_test_post_id ) ?></dd>
		<?php
	}

	?>
</dl>
<hr />
<?php
