<?php
/**
 * Lab Notes theme functions.
 *
 * @package Lab_Notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lab_notes_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_theme_support(
		'html5',
		array(
			'caption',
			'comment-form',
			'comment-list',
			'gallery',
			'navigation-widgets',
			'search-form',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'topics' => __( 'Topic Links', 'lab-notes' ),
		)
	);
}
add_action( 'after_setup_theme', 'lab_notes_setup' );

function lab_notes_scripts() {
	wp_enqueue_style(
		'lab-notes-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Noto+Sans+Mono:wght@400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'lab-notes-style',
		get_stylesheet_uri(),
		array( 'lab-notes-fonts' ),
		wp_get_theme()->get( 'Version' )
	);
	wp_add_inline_style( 'lab-notes-style', lab_notes_get_selected_style_css() );

	wp_enqueue_script(
		'lab-notes-availability',
		get_theme_file_uri( 'assets/js/availability.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'lab_notes_scripts' );

function lab_notes_block_editor_assets() {
	wp_enqueue_script(
		'lab-notes-editor-blocks',
		get_theme_file_uri( 'assets/js/editor-blocks.js' ),
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-server-side-render' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'lab_notes_block_editor_assets' );

function lab_notes_get_style_cookie_name() {
	return 'lab_notes_style';
}

function lab_notes_normalize_style_palette( $palette ) {
	$colors = array();

	foreach ( (array) $palette as $color ) {
		if ( empty( $color['slug'] ) || empty( $color['color'] ) ) {
			continue;
		}

		$colors[ sanitize_key( $color['slug'] ) ] = sanitize_hex_color( $color['color'] );
	}

	return array_filter( $colors );
}

function lab_notes_read_theme_json_file( $file ) {
	if ( ! is_readable( $file ) ) {
		return array();
	}

	$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	return is_array( $data ) ? $data : array();
}

function lab_notes_get_style_variations() {
	static $variations = null;

	if ( null !== $variations ) {
		return $variations;
	}

	$theme_json      = lab_notes_read_theme_json_file( get_theme_file_path( 'theme.json' ) );
	$default_palette = $theme_json['settings']['color']['palette'] ?? array();
	$variations      = array(
		'default' => array(
			'slug'   => 'default',
			'title'  => __( 'Default', 'lab-notes' ),
			'colors' => lab_notes_normalize_style_palette( $default_palette ),
		),
	);

	foreach ( glob( get_theme_file_path( 'styles/*.json' ) ) ?: array() as $file ) {
		$style_data = lab_notes_read_theme_json_file( $file );
		$slug       = sanitize_key( basename( $file, '.json' ) );
		$title      = $style_data['title'] ?? ucwords( str_replace( '-', ' ', $slug ) );
		$palette    = $style_data['settings']['color']['palette'] ?? array();

		$variations[ $slug ] = array(
			'slug'   => $slug,
			'title'  => $title,
			'colors' => lab_notes_normalize_style_palette( $palette ),
		);
	}

	uasort(
		$variations,
		static function ( $first, $second ) {
			if ( 'default' === $first['slug'] ) {
				return -1;
			}

			if ( 'default' === $second['slug'] ) {
				return 1;
			}

			return strcasecmp( $first['title'], $second['title'] );
		}
	);

	return $variations;
}

function lab_notes_get_selected_style_slug() {
	$variations = lab_notes_get_style_variations();
	$cookie     = lab_notes_get_style_cookie_name();
	$slug       = '';

	if ( isset( $_GET['lab_notes_style'] ) ) {
		$requested = sanitize_key( wp_unslash( $_GET['lab_notes_style'] ) );

		if ( 'default' === $requested || isset( $variations[ $requested ] ) ) {
			return $requested;
		}
	}

	if ( ! empty( $_SERVER['HTTP_COOKIE'] ) ) {
		foreach ( explode( ';', wp_unslash( $_SERVER['HTTP_COOKIE'] ) ) as $cookie_pair ) {
			$parts = explode( '=', trim( $cookie_pair ), 2 );

			if ( 2 === count( $parts ) && $cookie === $parts[0] ) {
				$slug = sanitize_key( rawurldecode( $parts[1] ) );
				break;
			}
		}
	}

	if ( ! $slug && ! empty( $_COOKIE[ $cookie ] ) ) {
		$slug = sanitize_key( wp_unslash( $_COOKIE[ $cookie ] ) );
	}

	return isset( $variations[ $slug ] ) ? $slug : 'default';
}

function lab_notes_handle_style_variation_request() {
	if ( ! isset( $_GET['lab_notes_style'] ) ) {
		return;
	}

	$requested  = sanitize_key( wp_unslash( $_GET['lab_notes_style'] ) );
	$variations = lab_notes_get_style_variations();
	$cookie     = lab_notes_get_style_cookie_name();
	$path       = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	$domain     = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

	if ( 'default' === $requested ) {
		setcookie( $cookie, '', time() - YEAR_IN_SECONDS, $path, $domain, is_ssl(), true );
		unset( $_COOKIE[ $cookie ] );
	} elseif ( isset( $variations[ $requested ] ) ) {
		setcookie( $cookie, $requested, time() + YEAR_IN_SECONDS, $path, $domain, is_ssl(), true );
		$_COOKIE[ $cookie ] = $requested;
	}
}
add_action( 'init', 'lab_notes_handle_style_variation_request' );

function lab_notes_is_light_color( $hex_color ) {
	$hex = ltrim( (string) $hex_color, '#' );

	if ( 6 !== strlen( $hex ) ) {
		return false;
	}

	$red   = hexdec( substr( $hex, 0, 2 ) );
	$green = hexdec( substr( $hex, 2, 2 ) );
	$blue  = hexdec( substr( $hex, 4, 2 ) );

	return ( ( $red * 299 ) + ( $green * 587 ) + ( $blue * 114 ) ) > 186000;
}

function lab_notes_get_selected_style_css() {
	$selected   = lab_notes_get_selected_style_slug();
	$variations = lab_notes_get_style_variations();

	if ( 'default' === $selected || empty( $variations[ $selected ]['colors'] ) ) {
		return '';
	}

	$colors       = $variations[ $selected ]['colors'];
	$declarations = array();

	foreach ( $colors as $slug => $color ) {
		$declarations[] = sprintf( '--wp--preset--color--%s: %s;', sanitize_key( $slug ), $color );
	}

	$color_scheme = lab_notes_is_light_color( $colors['background'] ?? '' ) ? 'light' : 'dark';

	return sprintf(
		":root { color-scheme: %s; %s }\nbody { background: var(--wp--preset--color--background); color: var(--wp--preset--color--text); }",
		$color_scheme,
		implode( ' ', $declarations )
	);
}

function lab_notes_get_style_switch_url( $style_slug ) {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$current_url = home_url( $request_uri );

	return add_query_arg( 'lab_notes_style', $style_slug, remove_query_arg( 'lab_notes_style', $current_url ) );
}

function lab_notes_daily_profile_image() {
	$image_dir   = get_theme_file_path( 'assets/profile-pic' );
	$image_paths = array();

	if ( ! is_dir( $image_dir ) ) {
		return '';
	}

	foreach ( new DirectoryIterator( $image_dir ) as $file ) {
		if ( $file->isDot() || ! $file->isFile() ) {
			continue;
		}

		$extension = strtolower( $file->getExtension() );

		if ( in_array( $extension, array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true ) ) {
			$image_paths[] = $file->getPathname();
		}
	}

	if ( empty( $image_paths ) ) {
		return '';
	}

	sort( $image_paths, SORT_NATURAL | SORT_FLAG_CASE );

	$today = (int) current_time( 'Ymd' );
	$index = $today % count( $image_paths );
	$file  = basename( $image_paths[ $index ] );

	return get_theme_file_uri( 'assets/profile-pic/' . $file );
}

function lab_notes_get_entry_type( $post_id ) {
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return array(
			'label' => __( 'Note', 'lab-notes' ),
			'class' => 'note',
		);
	}

	$category = $categories[0];
	$slug     = sanitize_html_class( $category->slug );
	$classes  = array(
		'note'     => 'note',
		'notes'    => 'note',
		'project'  => 'project',
		'projects' => 'project',
		'skill'    => 'skill',
		'skills'   => 'skill',
		'long'     => 'long',
		'longform' => 'long',
	);

	return array(
		'label' => $category->name,
		'class' => $classes[ $slug ] ?? 'note',
	);
}

function lab_notes_get_category_names( $post_id ) {
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return __( 'Entry', 'lab-notes' );
	}

	return implode(
		', ',
		wp_list_pluck( $categories, 'name' )
	);
}

function lab_notes_category_query( $slugs, $posts_per_page = 3 ) {
	foreach ( (array) $slugs as $slug ) {
		$category = get_category_by_slug( $slug );

		if ( ! $category ) {
			continue;
		}

		return new WP_Query(
			array(
				'cat'                 => $category->term_id,
				'posts_per_page'      => $posts_per_page,
				'ignore_sticky_posts' => true,
			)
		);
	}

	return new WP_Query(
		array(
			'post__in' => array( 0 ),
		)
	);
}

function lab_notes_current_post_slug() {
	$post_id = get_queried_object_id();

	return $post_id ? get_post_field( 'post_name', $post_id ) : '';
}

function lab_notes_command_target( $fallback = 'current' ) {
	$slug = lab_notes_current_post_slug();

	return $slug ? $slug : $fallback;
}

function lab_notes_remove_duplicate_feature_image( $content, $post_id ) {
	if ( ! has_post_thumbnail( $post_id ) ) {
		return $content;
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( ! $thumbnail_id ) {
		return $content;
	}

	$thumbnail_class = preg_quote( 'wp-image-' . $thumbnail_id, '/' );
	$pattern         = '/\s*(?:<figure\b[^>]*>\s*)?(?:<a\b[^>]*>\s*)?<img\b[^>]*\bclass=(["\'][^"\']*' . $thumbnail_class . '[^"\']*["\'])[^>]*>\s*(?:<\/a>\s*)?(?:<figcaption\b[^>]*>.*?<\/figcaption>\s*)?(?:<\/figure>\s*)?/is';
	$content         = preg_replace( $pattern, '', $content, 1 );

	return null === $content ? '' : $content;
}

function lab_notes_render_sidebar_block() {
	ob_start();
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

		<section class="stack-card" aria-label="<?php esc_attr_e( 'Style variations', 'lab-notes' ); ?>">
			<div class="stack-title"><?php esc_html_e( 'Style', 'lab-notes' ); ?></div>
			<nav class="style-list" aria-label="<?php esc_attr_e( 'Style variations', 'lab-notes' ); ?>">
				<?php
				$selected_style = lab_notes_get_selected_style_slug();
				foreach ( lab_notes_get_style_variations() as $style ) :
					$colors           = $style['colors'];
					$style_properties = sprintf(
						'--style-bg: %s; --style-text: %s; --style-accent: %s;',
						esc_attr( $colors['background'] ?? '#090909' ),
						esc_attr( $colors['text'] ?? '#eeeeea' ),
						esc_attr( $colors['accent'] ?? '#d7e82a' )
					);
					$is_selected      = $style['slug'] === $selected_style;
					?>
					<a
						class="style-link"
						href="<?php echo esc_url( lab_notes_get_style_switch_url( $style['slug'] ) ); ?>"
						style="<?php echo esc_attr( $style_properties ); ?>"
						<?php echo $is_selected ? 'aria-current="true"' : ''; ?>
					>
						<span class="style-swatch" aria-hidden="true"></span>
						<span class="style-name"><?php echo esc_html( $style['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</section>
	</aside>
	<?php

	return ob_get_clean();
}

function lab_notes_render_index_content_block() {
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

	$skills_query = lab_notes_category_query( array( 'skills', 'skill' ), 3 );

	ob_start();
	?>
	<section class="main-grid">
		<section class="window wide note-feature" aria-labelledby="feature-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="feature-title">cat ~/entries/latest.md</div>
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
						<span><?php esc_html_e( 'Empty', 'lab-notes' ); ?></span>
						<span>--</span>
						<span><?php esc_html_e( 'Entries', 'lab-notes' ); ?></span>
					</div>
					<h2><?php esc_html_e( 'No entries yet', 'lab-notes' ); ?></h2>
					<p><?php esc_html_e( 'Publish a post and it will appear here automatically.', 'lab-notes' ); ?></p>
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
						<div class="article-row">
							<div class="type-dot note" aria-hidden="true"></div>
							<div class="entry-copy">
								<span class="date"><?php esc_html_e( 'Empty', 'lab-notes' ); ?></span>
								<h3><?php esc_html_e( 'No entries yet', 'lab-notes' ); ?></h3>
								<p><?php esc_html_e( 'Publish a post and it will show up in this list.', 'lab-notes' ); ?></p>
							</div>
							<span class="count">--</span>
						</div>
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
	<?php

	return ob_get_clean();
}

function lab_notes_render_single_content_block() {
	$post_id       = get_queried_object_id();
	$current_post  = $post_id ? get_post( $post_id ) : null;
	$entry_command = sprintf( 'cat ~/entries/%s.md', lab_notes_command_target() );

	ob_start();
	?>
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
					<?php if ( $current_post instanceof WP_Post ) : ?>
						<?php
						setup_postdata( $current_post );
						$entry_type       = lab_notes_get_entry_type( $current_post->ID );
						$post_type_object = get_post_type_object( get_post_type( $current_post ) );
						$post_type_label  = $post_type_object ? $post_type_object->labels->singular_name : __( 'Entry', 'lab-notes' );
						?>
						<header class="post-header">
							<div class="post-meta">
								<span><?php echo esc_html( $entry_type['label'] ); ?></span>
								<span><?php echo esc_html( get_the_date( 'Y-m-d', $current_post ) ); ?></span>
								<span><?php echo esc_html( $post_type_label ); ?></span>
							</div>
							<h1 class="post-title" id="post-title"><?php echo esc_html( get_the_title( $current_post ) ); ?></h1>
							<?php if ( has_post_thumbnail( $current_post ) ) : ?>
								<figure class="post-feature-image">
									<?php echo get_the_post_thumbnail( $current_post, 'large' ); ?>
								</figure>
							<?php endif; ?>
							<?php if ( has_excerpt( $current_post ) ) : ?>
								<p class="post-dek"><?php echo esc_html( get_the_excerpt( $current_post ) ); ?></p>
							<?php endif; ?>
						</header>

						<div class="post-body">
							<?php echo lab_notes_remove_duplicate_feature_image( apply_filters( 'the_content', $current_post->post_content ), $current_post->ID ); ?>
						</div>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<header class="post-header">
							<div class="post-meta">
								<span><?php esc_html_e( 'Missing', 'lab-notes' ); ?></span>
								<span>--</span>
								<span><?php esc_html_e( 'Entry', 'lab-notes' ); ?></span>
							</div>
							<h1 class="post-title" id="post-title"><?php esc_html_e( 'No content found', 'lab-notes' ); ?></h1>
							<p class="post-dek"><?php esc_html_e( 'WordPress did not provide a post or page for this view.', 'lab-notes' ); ?></p>
						</header>
					<?php endif; ?>
				</div>
			</div>
		</article>

	</section>
	<?php

	return ob_get_clean();
}

function lab_notes_render_archive_content_block() {
	$queried_object = get_queried_object();
	$is_category    = $queried_object instanceof WP_Term && is_category();
	$slug           = $is_category ? $queried_object->slug : sanitize_title( get_the_archive_title() );
	$title          = $is_category ? single_cat_title( '', false ) : get_the_archive_title();

	ob_start();
	?>
	<section class="main-grid">
		<section class="window wide note-feature" aria-labelledby="archive-title">
			<div class="window-bar">
				<div class="window-title window-nav-title" id="archive-title">
					<a class="back-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Index', 'lab-notes' ); ?></a>
					<span class="title-separator" aria-hidden="true">|</span>
					<span>cat ~/categories/<?php echo esc_html( $slug ? $slug : 'entries' ); ?>.md</span>
				</div>
			</div>
			<div class="window-body feature-body">
				<div class="note-meta">
					<span><?php echo esc_html( $is_category ? __( 'Category', 'lab-notes' ) : __( 'Archive', 'lab-notes' ) ); ?></span>
					<span><?php echo esc_html( $title ); ?></span>
				</div>
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php if ( $is_category && category_description() ) : ?>
					<?php echo wp_kses_post( category_description() ); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'A filtered view of entries filed under this category.', 'lab-notes' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="window wide" aria-labelledby="archive-entries-title">
			<div class="window-bar">
				<div class="lights" aria-hidden="true"></div>
				<div class="window-title" id="archive-entries-title">ls -t ~/entries --category=<?php echo esc_html( $slug ? $slug : 'entries' ); ?></div>
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
								<h3><?php esc_html_e( 'No entries in this archive yet', 'lab-notes' ); ?></h3>
								<p><?php esc_html_e( 'Add a post here and it will show up automatically.', 'lab-notes' ); ?></p>
							</div>
							<span class="count">--</span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</section>
	<?php

	return ob_get_clean();
}

function lab_notes_register_dynamic_blocks() {
	register_block_type(
		'lab-notes/sidebar',
		array(
			'api_version'     => 3,
			'title'           => __( 'Lab Notes Sidebar', 'lab-notes' ),
			'category'        => 'theme',
			'render_callback' => 'lab_notes_render_sidebar_block',
		)
	);

	register_block_type(
		'lab-notes/index-content',
		array(
			'api_version'     => 3,
			'title'           => __( 'Lab Notes Index Content', 'lab-notes' ),
			'category'        => 'theme',
			'render_callback' => 'lab_notes_render_index_content_block',
		)
	);

	register_block_type(
		'lab-notes/single-content',
		array(
			'api_version'     => 3,
			'title'           => __( 'Lab Notes Single Content', 'lab-notes' ),
			'category'        => 'theme',
			'render_callback' => 'lab_notes_render_single_content_block',
		)
	);

	register_block_type(
		'lab-notes/archive-content',
		array(
			'api_version'     => 3,
			'title'           => __( 'Lab Notes Archive Content', 'lab-notes' ),
			'category'        => 'theme',
			'render_callback' => 'lab_notes_render_archive_content_block',
		)
	);
}
add_action( 'init', 'lab_notes_register_dynamic_blocks' );
