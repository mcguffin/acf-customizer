<?php
/**
 * Displays the footer widget area
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

global $acf_test_post_id;



?>

<div style="display:flex">
	<div>
	<?php
		$acf_test_post_id = 'acf_customize_mod_1';
		get_template_part( 'template-parts/acf/repeat-basic' );
		get_template_part( 'template-parts/acf/basic' );
		get_template_part( 'template-parts/acf/layouts' );

	?>
	</div>
	<div>
	<?php
		$acf_test_post_id = 'acf_customize_opt_1';
		get_template_part( 'template-parts/acf/repeat-basic' );
		get_template_part( 'template-parts/acf/basic' );
		get_template_part( 'template-parts/acf/layouts' );
	?>
	</div>
</div>

<?php

if ( is_active_sidebar( 'sidebar-1' ) ) : ?>

	<aside class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Footer', 'twentynineteen' ); ?>">
		<?php
		if ( is_active_sidebar( 'sidebar-1' ) ) {
			?>
					<div class="widget-column footer-widget-1">
					<?php dynamic_sidebar( 'sidebar-1' ); ?>
					</div>
				<?php
		}
		?>
	</aside><!-- .widget-area -->

<?php endif; ?>
