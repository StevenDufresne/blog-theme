<?php
/**
 * Category archive template.
 *
 * @package Lab_Notes
 */

get_header();

$category = get_queried_object();
$slug     = $category instanceof WP_Term ? $category->slug : 'entries';
?>
<main class="dashboard">
	<?php get_sidebar(); ?>

	<section class="main-grid">
		<section class="window wide note-feature" aria-labelledby="category-title">
			<div class="window-bar">
				<div class="window-title window-nav-title" id="category-title">
					<a class="back-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Index', 'lab-notes' ); ?></a>
					<span class="title-separator" aria-hidden="true">|</span>
					<span>cat ~/categories/<?php echo esc_html( $slug ); ?>.md</span>
				</div>
			</div>
			<div class="window-body feature-body">
				<div class="note-meta">
					<span><?php esc_html_e( 'Category', 'lab-notes' ); ?></span>
					<span><?php echo esc_html( single_cat_title( '', false ) ); ?></span>
				</div>
				<h2><?php echo esc_html( single_cat_title( '', false ) ); ?></h2>
				<?php if ( category_description() ) : ?>
					<?php echo wp_kses_post( category_description() ); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'A filtered view of entries filed under this category.', 'lab-notes' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="window wide" aria-labelledby="category-entries-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="category-entries-title">ls -t ~/entries --category=<?php echo esc_html( $slug ); ?></div>
			</div>
			<div class="window-body">
				<div class="article-list">
					<?php if ( have_posts() ) : ?>
						<?php
						while ( have_posts() ) :
							the_post();
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
					<?php else : ?>
						<div class="article-row">
							<div class="type-dot note" aria-hidden="true"></div>
							<div class="entry-copy">
								<span class="date"><?php esc_html_e( 'Empty', 'lab-notes' ); ?></span>
								<h3><?php esc_html_e( 'No entries in this category yet', 'lab-notes' ); ?></h3>
								<p><?php esc_html_e( 'Add a post to this category and it will show up here automatically.', 'lab-notes' ); ?></p>
							</div>
							<span class="count">--</span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</section>
</main>
<?php
get_footer();
