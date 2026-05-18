<?php
/**
 * Single post template.
 *
 * @package Lab_Notes
 */

get_header();

$current_post_slug = get_queried_object_id() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
$entry_command     = sprintf(
	'cat ~/entries/%s.md',
	$current_post_slug ? $current_post_slug : __( 'current', 'lab-notes' )
);
$related_command   = sprintf(
	'find ~/entries -type f -not -name %s',
	$current_post_slug ? $current_post_slug : __( 'current', 'lab-notes' )
);
?>
<main class="dashboard">
	<?php get_sidebar(); ?>

	<section class="main-grid">
		<article class="window" aria-labelledby="post-title">
			<div class="window-bar">
				<div class="window-title window-nav-title">
					<a class="back-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Index', 'lab-notes' ); ?></a>
					<span class="title-separator" aria-hidden="true">|</span>
					<span><?php echo esc_html( $entry_command ); ?></span>
				</div>
			</div>
			<div class="window-body">
				<div class="post-shell">
					<?php if ( have_posts() ) : ?>
						<?php
						while ( have_posts() ) :
							the_post();
							$entry_type = lab_notes_get_entry_type( get_the_ID() );
							?>
							<header class="post-header">
								<div class="post-meta">
									<span><?php echo esc_html( $entry_type['label'] ); ?></span>
									<span><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></span>
									<span><?php esc_html_e( 'Entry', 'lab-notes' ); ?></span>
								</div>
								<h1 class="post-title" id="post-title"><?php the_title(); ?></h1>
								<?php if ( has_excerpt() ) : ?>
									<p class="post-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
								<?php endif; ?>
							</header>

							<div class="post-body">
								<?php the_content(); ?>
							</div>
						<?php endwhile; ?>
					<?php else : ?>
						<header class="post-header">
							<div class="post-meta">
								<span><?php esc_html_e( 'Field Note', 'lab-notes' ); ?></span>
								<span>2026-05-17</span>
								<span><?php esc_html_e( '8 min', 'lab-notes' ); ?></span>
							</div>
							<h1 class="post-title" id="post-title"><?php esc_html_e( 'Building a calmer publishing workflow inside WordPress', 'lab-notes' ); ?></h1>
							<p class="post-dek"><?php esc_html_e( 'What changed after treating the editor like a workspace instead of a form: fewer decisions, cleaner drafts, and a better path from rough notes to finished posts.', 'lab-notes' ); ?></p>
						</header>

						<div class="post-body">
							<p><?php esc_html_e( 'The useful shift was small: stop asking the admin screen to be everything. I wanted the writing surface to feel closer to a working notebook, where structure appears when it helps and gets out of the way when it does not.', 'lab-notes' ); ?></p>
							<p><?php esc_html_e( 'That meant designing around the handful of states a post actually moves through: rough capture, shaped outline, checked examples, edited draft, and published reference. The interface became calmer once each state had an obvious place to live.', 'lab-notes' ); ?></p>
							<h2><?php esc_html_e( 'What Stayed', 'lab-notes' ); ?></h2>
							<p><?php esc_html_e( 'The core WordPress model still makes sense. Posts, categories, reusable blocks, and custom fields are enough for most of this. The theme should expose those pieces clearly instead of inventing a second editorial system on top.', 'lab-notes' ); ?></p>
							<h2><?php esc_html_e( 'What Changed', 'lab-notes' ); ?></h2>
							<ul>
								<li><?php esc_html_e( 'Post links lead with the title, not the surrounding card.', 'lab-notes' ); ?></li>
								<li><?php esc_html_e( 'Entry types use small visual markers instead of heavy badges.', 'lab-notes' ); ?></li>
								<li><?php esc_html_e( 'Metadata stays visible, but it does not compete with the writing.', 'lab-notes' ); ?></li>
							</ul>
							<p><?php esc_html_e( 'The next pass should make each content type feel deliberate without making the whole page noisy. Notes can stay light. Projects can show status. Skills can feel reusable. Longform can get a little more room.', 'lab-notes' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</article>

		<section class="window" aria-labelledby="related-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="related-title"><?php echo esc_html( $related_command ); ?></div>
			</div>
			<div class="window-body">
				<div class="related-list">
					<?php foreach ( array_slice( lab_notes_fallback_entries(), 0, 3 ) as $entry ) : ?>
						<article class="related-item">
							<div class="type-dot <?php echo esc_attr( $entry['type_class'] ); ?>" aria-hidden="true"></div>
							<div class="related-copy">
								<span class="related-meta"><?php echo esc_html( $entry['type'] ); ?></span>
								<h3><a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $entry['title'] ) ) ); ?>"><?php echo esc_html( $entry['title'] ); ?></a></h3>
								<p><?php echo esc_html( $entry['description'] ); ?></p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</section>
</main>
<?php
get_footer();
