<?php

$fields = [
	'google_map'	=> function($f){
		?>
		<pre><?php var_dump( get_sub_field($f) ); ?></pre>
		<?php
	},
	'date_picker'	=> 'the_sub_field',
	'date_time_picker'	=> 'the_sub_field',
	'time_picker'	=> 'the_sub_field',
	'color_picker'	=> function($f){
		printf('<div style="border:1px solid #000;width:40px;height:40px;background-color:%s;"></div>', get_sub_field($f));
	},
];

?>
<h4>ACF jQuery</h4>
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
