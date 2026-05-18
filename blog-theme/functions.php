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

function lab_notes_seed_categories() {
	if ( get_option( 'lab_notes_seeded_categories' ) ) {
		return;
	}

	$categories = array(
		'notes'     => __( 'Notes', 'lab-notes' ),
		'projects'  => __( 'Projects', 'lab-notes' ),
		'skills'    => __( 'Skills', 'lab-notes' ),
		'longform'  => __( 'Longform', 'lab-notes' ),
		'wordpress' => __( 'WordPress', 'lab-notes' ),
	);

	foreach ( $categories as $slug => $name ) {
		if ( ! get_category_by_slug( $slug ) ) {
			wp_insert_term(
				$name,
				'category',
				array(
					'slug'        => $slug,
					'description' => sprintf(
						/* translators: %s: category name */
						__( 'Entries filed under %s.', 'lab-notes' ),
						$name
					),
				)
			);
		}
	}

	update_option( 'lab_notes_seeded_categories', true );
}
add_action( 'init', 'lab_notes_seed_categories' );

function lab_notes_seed_demo_posts() {
	if ( get_option( 'lab_notes_seeded_demo_posts_v1' ) ) {
		return;
	}

	$posts = array(
		array(
			'title'    => __( 'Block-first article templates', 'lab-notes' ),
			'slug'     => 'block-first-article-templates',
			'category' => 'projects',
			'excerpt'  => __( 'A test set of reusable post structures for tutorials, field notes, and technical reviews.', 'lab-notes' ),
			'content'  => __( 'This project explores a small library of article patterns that can be reused without making every post feel templated.', 'lab-notes' ),
		),
		array(
			'title'    => __( 'Local notes to WordPress', 'lab-notes' ),
			'slug'     => 'local-notes-to-wordpress',
			'category' => 'projects',
			'excerpt'  => __( 'A lightweight path for turning rough Markdown notes into edited, publishable drafts.', 'lab-notes' ),
			'content'  => __( 'This project maps the handoff from local notes into WordPress so the publishing surface stays calm and predictable.', 'lab-notes' ),
		),
		array(
			'title'    => __( 'Editorial dashboard theme', 'lab-notes' ),
			'slug'     => 'editorial-dashboard-theme',
			'category' => 'projects',
			'excerpt'  => __( 'A modular theme direction that treats posts, skills, and projects as first-class objects.', 'lab-notes' ),
			'content'  => __( 'This theme prototype turns the index into a working surface for posts, categories, projects, and reusable skills.', 'lab-notes' ),
		),
		array(
			'title'    => __( 'Draft cleanup checklist', 'lab-notes' ),
			'slug'     => 'draft-cleanup-checklist',
			'category' => 'skills',
			'excerpt'  => __( 'Remove throat-clearing, check headings, tighten the first paragraph.', 'lab-notes' ),
			'content'  => __( 'A repeatable pass for making a rough technical post easier to scan before it moves into editing.', 'lab-notes' ),
		),
		array(
			'title'    => __( 'Reusable code block pattern', 'lab-notes' ),
			'slug'     => 'reusable-code-block-pattern',
			'category' => 'skills',
			'excerpt'  => __( 'Store language, filename, summary, and source link together.', 'lab-notes' ),
			'content'  => __( 'A compact pattern for keeping code examples readable, attributed, and easy to migrate between drafts.', 'lab-notes' ),
		),
		array(
			'title'    => __( 'Post idea intake', 'lab-notes' ),
			'slug'     => 'post-idea-intake',
			'category' => 'skills',
			'excerpt'  => __( 'Capture question, example, failure mode, and next test.', 'lab-notes' ),
			'content'  => __( 'A quick capture shape for turning loose observations into posts that have a clear reader handoff.', 'lab-notes' ),
		),
	);

	foreach ( $posts as $post ) {
		$existing = get_posts(
			array(
				'name'           => $post['slug'],
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( $existing ) {
			continue;
		}

		$category = get_category_by_slug( $post['category'] );
		$post_id  = wp_insert_post(
			array(
				'post_title'   => $post['title'],
				'post_name'    => $post['slug'],
				'post_excerpt' => $post['excerpt'],
				'post_content' => '<p>' . esc_html( $post['content'] ) . '</p>',
				'post_status'  => 'draft',
				'post_type'    => 'post',
			)
		);

		if ( ! is_wp_error( $post_id ) && $category ) {
			wp_set_post_categories( $post_id, array( $category->term_id ) );
		}
	}

	update_option( 'lab_notes_seeded_demo_posts_v1', true );
}
add_action( 'init', 'lab_notes_seed_demo_posts', 11 );

function lab_notes_fallback_topics() {
	return array(
		array( 'label' => __( 'Notes', 'lab-notes' ), 'slug' => 'notes' ),
		array( 'label' => __( 'Projects', 'lab-notes' ), 'slug' => 'projects' ),
		array( 'label' => __( 'Skills', 'lab-notes' ), 'slug' => 'skills' ),
		array( 'label' => __( 'Longform', 'lab-notes' ), 'slug' => 'longform' ),
		array( 'label' => __( 'WordPress', 'lab-notes' ), 'slug' => 'wordpress' ),
	);
}

function lab_notes_get_category_url( $slug ) {
	$category = get_category_by_slug( $slug );

	if ( $category ) {
		return get_category_link( $category );
	}

	return home_url( '/category/' . sanitize_title( $slug ) . '/' );
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

function lab_notes_fallback_skills() {
	return array(
		array(
			'title'       => __( 'Draft cleanup checklist', 'lab-notes' ),
			'description' => __( 'Remove throat-clearing, check headings, tighten the first paragraph.', 'lab-notes' ),
		),
		array(
			'title'       => __( 'Reusable code block pattern', 'lab-notes' ),
			'description' => __( 'Store language, filename, summary, and source link together.', 'lab-notes' ),
		),
		array(
			'title'       => __( 'Post idea intake', 'lab-notes' ),
			'description' => __( 'Capture question, example, failure mode, and next test.', 'lab-notes' ),
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

function lab_notes_visible_post_statuses() {
	return current_user_can( 'edit_posts' ) ? array( 'publish', 'draft' ) : 'publish';
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
				'post_status'         => lab_notes_visible_post_statuses(),
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

function lab_notes_include_drafts_for_editors( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$query->set( 'post_status', lab_notes_visible_post_statuses() );
}
add_action( 'pre_get_posts', 'lab_notes_include_drafts_for_editors' );
