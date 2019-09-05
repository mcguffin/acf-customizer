<?php

global $acf_test_post_id;


if ( have_rows( 'repeat_basic', $acf_test_post_id ) ) {

	printf( '<h3>Basic Repeat: %s</h3>', $acf_test_post_id );

	?>
	<table>
		<thead>

			<?php

			foreach ( [ 'text', 'textarea', 'number', 'range', 'e-mail', 'url' ] as $selector ) {
				?>
				<th><?php echo $selector; ?></th>
				<?php
			}
			?>
		</thead>
		<tbody>
			<?php


			while ( have_rows( 'repeat_basic', $acf_test_post_id ) ) {

				the_row();

				?>
				<tr>
					<?php

					foreach ( [ 'text', 'textarea', 'number', 'range', 'e-mail', 'url' ] as $selector ) {
						?>
						<td>
							<?php
								do_action( 'acf_customizer_sub_field', $selector );
								the_sub_field( $selector );
							?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
			}


			?>
		</tbody>
	</table>
	<?php
}
