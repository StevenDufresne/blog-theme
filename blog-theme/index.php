<?php
/**
 * Main index template.
 *
 * @package Lab_Notes
 */

get_header();

$featured_query = new WP_Query(
	array(
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
	)
);

$entries_query = new WP_Query(
	array(
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
	)
);

$projects_query = lab_notes_category_query( array( 'projects', 'project' ), 3 );
$skills_query   = lab_notes_category_query( array( 'skills', 'skill' ), 3 );
$projects_url   = get_category_by_slug( 'projects' ) ? get_category_link( get_category_by_slug( 'projects' ) ) : home_url( '/' );
?>
<main class="dashboard" id="overview">
	<?php get_sidebar(); ?>

	<section class="main-grid">
		<section class="window wide note-feature" aria-labelledby="feature-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="feature-title">cat ~/entries/latest-field-report.md</div>
			</div>
			<div class="window-body feature-body">
				<?php if ( $featured_query->have_posts() ) : ?>
					<?php
					while ( $featured_query->have_posts() ) :
						$featured_query->the_post();
						?>
						<div class="note-meta">
							<span><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></span>
							<span><?php echo esc_html( lab_notes_get_category_names( get_the_ID() ) ); ?></span>
						</div>
						<h2><a class="feature-title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<div class="note-meta">
						<span><?php esc_html_e( 'Field Note', 'lab-notes' ); ?></span>
						<span>2026-05-17</span>
						<span><?php esc_html_e( 'Web Workflows', 'lab-notes' ); ?></span>
					</div>
					<h2><a class="feature-title-link" href="<?php echo esc_url( lab_notes_fallback_permalink() ); ?>"><?php esc_html_e( 'Building a calmer publishing workflow inside WordPress', 'lab-notes' ); ?></a></h2>
					<p><?php esc_html_e( 'What changed after treating the editor like a workspace instead of a form: fewer decisions, cleaner drafts, and a better path from rough notes to finished posts.', 'lab-notes' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="window wide" id="articles" aria-labelledby="articles-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="articles-title">ls -t ~/entries | head -n 6</div>
			</div>
			<div class="window-body">
				<div class="article-list">
					<?php if ( $entries_query->have_posts() ) : ?>
						<?php
						while ( $entries_query->have_posts() ) :
							$entries_query->the_post();
							$entry_type = lab_notes_get_entry_type( get_the_ID() );
							?>
							<div class="article-row">
								<div class="type-dot <?php echo esc_attr( $entry_type['class'] ); ?>" aria-hidden="true"></div>
								<div class="entry-copy">
									<span class="date"><?php echo esc_html( $entry_type['label'] ); ?></span>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p><?php echo esc_html( get_the_excerpt() ); ?></p>
								</div>
								<span class="count"><?php echo esc_html( get_the_date( 'm/d/Y' ) ); ?></span>
							</div>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<?php foreach ( lab_notes_fallback_entries() as $entry ) : ?>
							<div class="article-row">
								<div class="type-dot <?php echo esc_attr( $entry['type_class'] ); ?>" aria-hidden="true"></div>
								<div class="entry-copy">
									<span class="date"><?php echo esc_html( $entry['type'] ); ?></span>
									<h3><a href="<?php echo esc_url( lab_notes_fallback_permalink() ); ?>"><?php echo esc_html( $entry['title'] ); ?></a></h3>
									<p><?php echo esc_html( $entry['description'] ); ?></p>
								</div>
								<span class="count"><?php echo esc_html( $entry['date'] ); ?></span>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="window wide" id="projects" aria-label="<?php esc_attr_e( 'Projects', 'lab-notes' ); ?>">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<?php // <div class="window-title" id="projects-title">ls ~/github/projects</div> ?>
			</div>
			<div class="window-body">
				<div class="repo-grid">
					<?php if ( $projects_query->have_posts() ) : ?>
						<?php
						while ( $projects_query->have_posts() ) :
							$projects_query->the_post();
							?>
							<article class="repo">
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( get_the_excerpt() ); ?></p>
								<div class="repo-meta">
									<span class="mini-pill"><?php esc_html_e( 'Project', 'lab-notes' ); ?></span>
									<span class="mini-pill"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></span>
								</div>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<?php foreach ( lab_notes_fallback_projects() as $project ) : ?>
							<article class="repo">
								<h3><a href="<?php echo esc_url( $projects_url ); ?>"><?php echo esc_html( $project['title'] ); ?></a></h3>
								<p><?php echo esc_html( $project['description'] ); ?></p>
								<div class="repo-meta">
									<?php foreach ( $project['meta'] as $meta ) : ?>
										<span class="mini-pill"><?php echo esc_html( $meta ); ?></span>
									<?php endforeach; ?>
								</div>
							</article>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $skills_query->have_posts() ) : ?>
			<section class="window wide" aria-labelledby="skills-title">
				<div class="window-bar">
					<div class="lights" aria-hidden="true"></div>
					<div class="window-title" id="skills-title">ls ~/.claude/skills</div>
				</div>
				<div class="window-body">
					<div class="timeline">
						<?php
						while ( $skills_query->have_posts() ) :
							$skills_query->the_post();
							?>
							<article class="timeline-item">
								<div class="timeline-dot" aria-hidden="true"></div>
								<div>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p><?php echo esc_html( get_the_excerpt() ); ?></p>
								</div>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</section>
</main>
<?php
get_footer();
