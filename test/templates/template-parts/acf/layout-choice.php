<?php

$fields = [
	'select'		=> 'the_sub_field',
	'checkbox'		=> 'the_sub_field',
	'radio_button'	=> 'the_sub_field',
	'button_group'	=> 'the_sub_field',
	'true_false'	=> 'the_sub_field',
];

?>
<h4>ACF Choice</h4>
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
