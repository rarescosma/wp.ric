<?php

// 1. Menus

if ( function_exists( 'register_nav_menus')):
	register_nav_menus(
		array(
			'main_menu' => 'Main Menu',
			'footer_menu' => 'Footer Menu'
		)
	);
endif;

add_filter( 'nav_menu_css_class', 'add_parent_url_menu_class', 10, 2 );
add_filter( 'page_css_class', 'add_parent_url_menu_class', 10, 2 );

function add_parent_url_menu_class( $classes = array(), $item = false ) {
	// Get current URL
	$current_url = ricUtil::current_url();

	// Get homepage URL
	$homepage_url = trailingslashit( get_bloginfo( 'url' ) );

	// Exclude 404 and homepage
	if( is_404() or $item->url == $homepage_url ) return $classes;

	if ( strstr( $current_url, $item->url) or strstr( $current_url, get_permalink( $item->ID ) ) ) {
		// Add the 'parent_url' class
		$classes[] = 'parent_url';
	}

	return $classes;
}

// 2. Images
// Sizes
//add_image_size( '106x106', 106, 106, true );
add_image_size( '435x332', 435, 332, true );
add_image_size( '1000x332', 1000, 332, true );

if ( function_exists( 'add_theme_support')):
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 106, 106, true );
endif;

// 3. Widgets
// if ( function_exists( 'register_sidebar') ):
// 	register_sidebar(
// 		array(
// 			'name'=>'Bottom Teasers',
// 			'before_widget' => '<div class="%2$s my_widget_class">',
// 			'after_widget' => '</div>',
// 			'before_title' => '<h2>',
// 			'after_title' => '</h2>'
// 		)
// 	);
// 	global $my_widget_num;
// 	$my_widget_num = 0;
// endif;

// 4. Register Stylesheet files so they appear in the editor
add_editor_style( 'css/rte.css' );

function add_global_mce_styles( $styles ) {
	$my_styles = array(
		'Yellow' => 'yellow'
	);

	return $styles + $my_styles;
}

add_filter( 'ric_mce_styles', 'add_global_mce_styles', 10, 2 );


// Change Post labels
function change_post_menu_label() {
	global $menu;
	global $submenu;
	$menu[5][0] = 'News';
	$submenu['edit.php'][5][0] = 'News';
	$submenu['edit.php'][10][0] = 'Add News';
	$submenu['edit.php'][16][0] = 'News Tags';
	echo '';
}

function change_post_object_label() {
	global $wp_post_types;
	$labels = &$wp_post_types['post']->labels;
	$labels->name = 'News';
	$labels->singular_name = 'News';
	$labels->add_new = 'Add New News';
	$labels->add_new_item = 'Add New News';
	$labels->edit_item = 'Edit News';
	$labels->new_item = 'News';
	$labels->view_item = 'View News';
	$labels->search_items = 'Search News';
	$labels->not_found = 'No News found';
	$labels->not_found_in_trash = 'No News found in Trash';
}
add_action( 'init', 'change_post_object_label' );
add_action( 'admin_menu', 'change_post_menu_label' );

// Excerpt
add_post_type_support('page', 'excerpt');

function new_excerpt_length($length) {
	return 36;
}
add_filter('excerpt_length', 'new_excerpt_length');

function new_excerpt_more($more) {
	return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

function my_excerpt($excerpt_length = 55, $id = false, $echo = true) {

        $text = '';

              if($id) {
                    $the_post = & get_post( $my_id = $id );
                    $text = ($the_post->post_excerpt) ? $the_post->post_excerpt : $the_post->post_content;
              } else {
                    global $post;
                    $text = ($post->post_excerpt) ? $post->post_excerpt : get_the_content('');
        }

                    $text = strip_shortcodes( $text );
                    $text = apply_filters('the_content', $text);
                    $text = str_replace(']]>', ']]&gt;', $text);
              $text = strip_tags($text);

                    $excerpt_more = ' ' . '...';
                    $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
                    if ( count($words) > $excerpt_length ) {
                            array_pop($words);
                            $text = implode(' ', $words);
                            $text = $text . $excerpt_more;
                    } else {
                            $text = implode(' ', $words);
                    }
            if($echo)
      echo apply_filters('the_content', $text);
            else
            return $text;
}

function get_my_excerpt($excerpt_length = 55, $id = false, $echo = false) {
 return my_excerpt($excerpt_length, $id, $echo);
}

// Title
function the_titlesmall($before = '', $after = '', $echo = true, $length = false) { $title = get_the_title();

	if ( $length && is_numeric($length) ) {
		$title = substr( $title, 0, $length );
	}

	if ( strlen($title)> 0 ) {
		$title = apply_filters('the_titlesmall', $before . $title . $after, $before, $after);
		if ( $echo )
			echo $title;
		else
			return $title;
	}
}
?>