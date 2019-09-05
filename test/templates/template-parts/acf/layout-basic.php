<?php

$fields = [
	'text'		=> 'the_sub_field',
	'textarea'	=> 'the_sub_field',
	'number'	=> 'the_sub_field',
	'range'		=> 'the_sub_field',
	'e-mail'	=> 'the_sub_field',
	'url'		=> 'the_sub_field',
];

?>
<h4>ACF Basic</h4>
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
