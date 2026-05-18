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

	wp_enqueue_script(
		'lab-notes-availability',
		get_theme_file_uri( 'assets/js/availability.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'lab_notes_scripts' );

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

function lab_notes_fallback_entries() {
	return array(
		array(
			'type'        => __( 'Note', 'lab-notes' ),
			'type_class'  => 'note',
			'title'       => __( 'What I changed after a week of writing in blocks', 'lab-notes' ),
			'description' => __( 'A short field note on simplifying drafts, reusable sections, and the limits of block patterns.', 'lab-notes' ),
			'date'        => '05/17/2026',
		),
		array(
			'type'        => __( 'Project', 'lab-notes' ),
			'type_class'  => 'project',
			'title'       => __( 'Testing a no-dashboard WordPress author flow', 'lab-notes' ),
			'description' => __( 'A prototype for moving from local notes to publishable entries with fewer admin screens.', 'lab-notes' ),
			'date'        => '05/12/2026',
		),
		array(
			'type'        => __( 'Skill', 'lab-notes' ),
			'type_class'  => 'skill',
			'title'       => __( 'A tiny checklist for publishing technical posts', 'lab-notes' ),
			'description' => __( 'A reusable review pass for examples, assumptions, screenshots, and reader handoff.', 'lab-notes' ),
			'date'        => '05/09/2026',
		),
		array(
			'type'        => __( 'Longform', 'lab-notes' ),
			'type_class'  => 'long',
			'title'       => __( 'The difference between a website and a working surface', 'lab-notes' ),
			'description' => __( 'A longer essay about interfaces that support ongoing work instead of one-time visits.', 'lab-notes' ),
			'date'        => '05/01/2026',
		),
	);
}

function lab_notes_fallback_projects() {
	return array(
		array(
			'title'       => __( 'Block-first article templates', 'lab-notes' ),
			'description' => __( 'A test set of reusable post structures for tutorials, field notes, and technical reviews.', 'lab-notes' ),
			'meta'        => array( __( 'Project', 'lab-notes' ), __( 'Active', 'lab-notes' ) ),
		),
		array(
			'title'       => __( 'Local notes to WordPress', 'lab-notes' ),
			'description' => __( 'A lightweight path for turning rough Markdown notes into edited, publishable drafts.', 'lab-notes' ),
			'meta'        => array( __( 'Prototype', 'lab-notes' ), __( 'Drafting', 'lab-notes' ) ),
		),
		array(
			'title'       => __( 'Editorial dashboard theme', 'lab-notes' ),
			'description' => __( 'A modular theme direction that treats posts, skills, and projects as first-class objects.', 'lab-notes' ),
			'meta'        => array( __( 'Theme', 'lab-notes' ), __( 'Designing', 'lab-notes' ) ),
		),
	);
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
		return __( 'Web Workflows', 'lab-notes' );
	}

	return implode(
		', ',
		wp_list_pluck( $categories, 'name' )
	);
}

function lab_notes_fallback_permalink() {
	return home_url( '/?p=1' );
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
