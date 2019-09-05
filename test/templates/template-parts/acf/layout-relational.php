<?php

$fields = [
	'link'			=> 'the_sub_field',
	'post_object'	=> function($f){
		echo get_permalink(get_sub_field($f));
	},
	'page_link'		=> 'the_sub_field',
	'relationship'	=> function($f){
		foreach ( get_sub_field($f) as $post ) {
			echo get_permalink($post);
			echo '<br />';
		}

	},
	'taxonomy'		=> 'the_sub_field',
	'user'			=> function($f) {
		$u = get_sub_field($f);
		if ( is_numeric($u)) {
			$uid = $u;
		} else if ( is_object($u) ) {
			$uid = $u->ID;
		} else if ( is_array($u) ) {
			$uid = $u['ID'];
		}
		echo get_author_posts_url( $uid );
	},
];

?>
<h4>ACF Relational</h4>
<table class="acf-field">
	<?php

	foreach ( $fields as $field => $cb ) {
		?>
			<tr>
				<th><?php echo $field; ?></th>
				<td><?php
					do_action( 'acf_customizer_sub_field', $field );
					$cb($field);
				?></td>
			</tr>
		<?php
	}

	?>
</table>
<?php
