<?php

$fields = [
	'image'		=> function($f){
		$img = get_sub_field($f);
		if ( is_array( $img ) ) {
			$img = $img['ID'];
		}
		if ( is_numeric( $img ) ) {
			echo wp_get_attachment_image($img);
		} else {
			printf( '<img src="%s" />', $img );
		}
	},
	'file'		=> function($f){
		$img = get_sub_field($f);
		if ( is_array( $img ) ) {
			$img = $img['ID'];
		}
		if ( is_numeric( $img ) ) {
			echo wp_get_attachment_link($img);
		} else {
			printf( '<a href="%s">Download</a>', $img );
		}

	},
	'wysiwyg'	=> 'the_sub_field',
	'oembed'	=> 'the_sub_field',
	'gallery'	=> function($f){
		$imgs = get_sub_field($f);
		foreach ( $imgs as $img ) {
			if ( is_array( $img ) ) {
				$img = $img['ID'];
			}
			if ( is_numeric( $img ) ) {
				echo wp_get_attachment_image($img,'thumbnail');
			} else {
				printf( '<img src="%s" />', $img );
			}
		}

	},
];

?>
<h4>ACF Content</h4>
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
