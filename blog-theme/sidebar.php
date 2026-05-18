<?php
/**
 * Theme sidebar.
 *
 * @package Lab_Notes
 */
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Profile and topics', 'lab-notes' ); ?>">
	<section class="profile">
		<div class="portrait" aria-hidden="true"></div>
		<h2><?php echo esc_html( get_theme_mod( 'lab_notes_profile_name', 'Steve D' ) ); ?></h2>
		<div class="handle">
			<div class="status-dot" aria-hidden="true"></div>
			<span id="availability-status"><?php esc_html_e( 'offline', 'lab-notes' ); ?></span>
		</div>
		<p class="profile-copy"><?php echo esc_html( get_theme_mod( 'lab_notes_profile_bio', __( 'Notes from building, breaking, testing, and refining tools for the web.', 'lab-notes' ) ) ); ?></p>
	</section>

	<section class="stack-card" aria-label="<?php esc_attr_e( 'Topics', 'lab-notes' ); ?>">
		<?php
		if ( has_nav_menu( 'topics' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'topics',
					'container'      => false,
					'menu_class'     => 'chips',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
		} else {
			echo '<div class="chips">';
			foreach ( lab_notes_fallback_topics() as $topic ) {
				printf(
					'<a class="chip" href="%s">%s</a>',
					esc_url( lab_notes_get_category_url( $topic['slug'] ) ),
					esc_html( $topic['label'] )
				);
			}
			echo '</div>';
		}
		?>
	</section>
</aside>
