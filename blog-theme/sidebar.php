<?php
/**
 * Theme sidebar.
 *
 * @package Lab_Notes
 */
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Profile and topics', 'lab-notes' ); ?>">
	<section class="profile">
		<?php $profile_image = lab_notes_daily_profile_image(); ?>
		<div class="portrait" aria-hidden="true">
			<?php if ( $profile_image ) : ?>
				<img src="<?php echo esc_url( $profile_image ); ?>" alt="">
			<?php endif; ?>
		</div>
		<h2><?php echo esc_html( get_theme_mod( 'lab_notes_profile_name', 'Steve D' ) ); ?></h2>
		<div class="handle">
			<div class="status-dot" aria-hidden="true"></div>
			<span id="availability-status"><?php esc_html_e( 'offline', 'lab-notes' ); ?></span>
		</div>
		<p class="profile-copy"><?php echo esc_html( get_theme_mod( 'lab_notes_profile_bio', __( 'Notes from building, breaking, testing, and refining tools for the web.', 'lab-notes' ) ) ); ?></p>
	</section>

	<section class="stack-card" aria-label="<?php esc_attr_e( 'Topics', 'lab-notes' ); ?>">
		<?php
		$categories = get_categories(
			array(
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		echo '<nav class="path-list" aria-label="' . esc_attr__( 'Categories', 'lab-notes' ) . '">';
		printf(
			'<a class="path-link" href="%s">%s</a>',
			esc_url( home_url( '/' ) ),
			esc_html__( '~/home', 'lab-notes' )
		);
		if ( $categories ) {
			foreach ( $categories as $category ) {
				printf(
					'<a class="path-link" href="%s">~/%s</a>',
					esc_url( get_category_link( $category ) ),
					esc_html( $category->slug )
				);
			}
		} else {
			printf(
				'<a class="path-link" href="%s">%s</a>',
				esc_url( home_url( '/' ) ),
				esc_html__( '~/entries', 'lab-notes' )
			);
		}
		echo '</nav>';
		?>
	</section>
</aside>
