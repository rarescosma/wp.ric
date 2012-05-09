<?php

class ricCPT {
	private $post_data = array();
	private $meta = array();

	function __construct( $post, $blog_id = false ) {

		if( $blog_id ) {
			switch_to_blog( $blog_id );
		}

		if( is_numeric( $post ) ) {
			// We've been given a post ID. Let's do the assigning work
			$post_id = $post;

			// Asign the post data
			$post_data = get_post( $post_id );
			$this->post_data = $post_data;

		} elseif( is_object( $post ) ) {
			// We've been given a post object.
			$this->post_data = $post;

			// Asign the post ID
			$post_id = $post->ID;
		}

		if( $post_id ) {
			// Get the meta fields
			$meta = get_post_custom( $post_id );

			// Flatten the array
			foreach( $meta as $key => $value ) {
				$meta[$key] = $value[0];
			}

			// And asign it
			$this->meta = $meta;
		}

		$this->post_data->ID = $post_id;

		if( $blog_id ) {
			restore_current_blog();
		}
	}

	public function __get( $property ) {
		// This is a magic method that is called if we
		// access a property that does not exist.

		// Try the post data first
		if( array_key_exists( $property, $this->post_data ) ) {
			return $this->post_data->$property;
		}

		// Try the meta data next
		if( array_key_exists( $property, $this->meta ) ) {
			return $this->meta[$property];
		}

		return NULL;
	}

	public function __set( $name, $value ) {
		$this->post_data->$name = $value;
	}

	public function get_file( $args = array() ) {
		$defaults = array(
			'meta_key' => false
		);

		extract( wp_parse_args( $args, $defaults ) );

		if( ! $meta_key or empty( $meta_key ) )
			return false;

		if( $this->{$meta_key} ) {
			return array(
				'title' => get_the_title( $this->{$meta_key} ),
				'url' => wp_get_attachment_url( $this->{$meta_key} )
			);
		}

		return false;
	}

	static function register( $args = array() ) {
		$defaults = array(
			'slug' => 'product',
			'singular' => 'Product',
			'plural' => 'Products',
			'register_args' => array()
		);

		extract( wp_parse_args( $args, $defaults ) );

		$singular = __( $singular );
		$plural = __( $plural );

		$labels = array(
			'name' => $plural,
			'singular_name' => $singular,
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New' ) . ' ' . $singular,
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit' ) . ' ' . $singular,
			'new_item' => __( 'New' ) . ' ' . $singular,
			'view' => __( 'View' ) . ' ' . $singular,
			'view_item' => __( 'View' ) . ' ' . $singular,
			'search_items' => __( 'Search' ) . ' ' . $plural,
			'not_found' => __( 'No' ) . ' ' . $plural . ' ' . __( 'found' ),
			'not_found_in_trash' => __( "No ${plural} found in Trash" ),
			'parent' => __( 'Parent' ) . ' ' . $singular
		);

		$register_defaults = array(
			'labels' => $labels,
			'hierarchical' => true,
			'public' => true,
			'supports' => array ( 'title', 'editor' ),
			'rewrite' => array(
				'slug' => $slug,
				'with_front' => false
			)
		);

		$register_args = wp_parse_args( $register_args, $register_defaults );

		return register_post_type( $slug, $register_args );

	}

	static function objects_to_ids ( $posts = array() ) {
		$ids = array();

		foreach( $posts as $post ) {
			$ids[] = $post->ID;
		}

		return $ids;
	}

	static function ids_to_objects ( $ids = array(), $class ) {
		$objects = array();

		foreach( $ids as $id ) {
			$objects[] = new $class( $id );
		}

		return $objects;
	}

	static function by_postfields ( $args ) {
		$defaults = array(
			'post_type' => 'post',
			'order_by' => 'post_date',
			'order' => 'DESC',
			'blog_id' => false,
			'objects' => false,
			'fields' => array()
		);

		// Extract arguments for easier access
		extract( wp_parse_args( $args, $defaults ) );

		if( ! count( $fields ) ) return false;

		// Switch blogs if neccessary
		if( $blog_id ) switch_to_blog( $blog_id );

		$args = array(
			'numberposts' => -1,
			'post_type' => $post_type,
			'order_by' => $order_by,
			'order' => $order
		);

		$args = array_merge( $args, $fields );

		$posts = get_posts( $args ); // Got objects

		// Restore current blog if neccessary
		if( $blog_id ) restore_current_blog();

		$posts = self::objects_to_ids( $posts );

		if( $objects ) {
			$posts = self::ids_to_objects( $posts, $objects );
		}

		return $posts;
	}

	static function by_meta( $args ) {
		// Database abstraction
		global $wpdb;

		// Default arguments
		$defaults = array(
			'post_type' => 'post',
			'meta_key' => false,
			'meta_value' => false,
			'meta_conditions' => false,
			'order_by' => 'post_date',
			'order' => 'DESC',
			'blog_id' => false,
			'objects' => false
		);

		// Extract arguments for easier access
		extract(wp_parse_args($args, $defaults));

		if(($meta_key and $meta_value) or is_array($meta_conditions)) {
			// Single conditions take precedence
			if($meta_key and $meta_value) {
				$meta_conditions = array(
					$meta_key => $meta_value
				);
			}

			// Switch blogs if neccessary
			if($blog_id) switch_to_blog($blog_id);

			$posts = array();

			// Query part 0 - Select & Join
			$query = "SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
			WHERE $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_type = '$post_type'";

			// Query part 1 - Search meta fields
			foreach($meta_conditions as $key => $value) {
				$where_parts[] = "( $wpdb->postmeta.meta_key = '$key' AND $wpdb->postmeta.meta_value = '$value' )";
			}

			if(count($where_parts)) $query .= ' AND (' . implode(' OR ', $where_parts) . ') ';

			// Query last part - ORDER Statement
			$query .= " ORDER BY " . $wpdb->prefix . "posts.${order_by} ${order}";

			$results = $wpdb->get_results($query, ARRAY_A);

			// Parse the results
			foreach($results as $result){
				$posts[] = $result['ID'];
			}

			// Restore current blog if neccessary
			if($blog_id) restore_current_blog();

			if( $objects ) {
				$posts = self::ids_to_objects( $posts );
			}

			return $posts;
		}

		return false;
	}

	static function by_post_and_meta_fields($args) {
		// Database abstraction
		global $wpdb;

		// Default arguments
		$defaults = array(
			'post_type' => 'post',
			'post_fields_to_search' => false,
			'meta_fields_to_search' => false,
			'search_term' => false,
			'blog_id' => false
		);

		// Extract arguments for easier access
		extract(wp_parse_args($args, $defaults));

		if(($post_fields_to_search or $meta_fields_to_search) and $search_term) {

			// Switch blogs if neccessary
			if($blog_id) switch_to_blog($blog_id);

			$ids = array();

			// Query part 0 - SELECT statement
			$query = "SELECT DISTINCT $wpdb->posts.ID
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
			WHERE ($wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_type = '$post_type') AND (";

			// Query part 1 - Search post fields
			$where_parts = array();
			if(is_array($post_fields_to_search)) {
				foreach($post_fields_to_search as $field) {
					$where_parts[] = "$wpdb->posts.$field LIKE '%$search_term%'";
				}
			}

			// Query part 2 - Search meta fields
			if(is_array($meta_fields_to_search)) {
				foreach($meta_fields_to_search as $field) {
					$where_parts[] = "( $wpdb->postmeta.meta_key = '$field' AND $wpdb->postmeta.meta_value LIKE '%$search_term%' )";
				}
			}

			if(count($where_parts)) $query .= implode(' OR ', $where_parts);

			// Query last part - ORDER Statement
			$query .= ") ORDER BY $wpdb->posts.ID ASC";

			$results = $wpdb->get_results($query, ARRAY_A);

			// Parse the results
			foreach($results as $result){
				$ids[] = $result['ID'];
			}

			// Restore current blog if neccessary
			if($blog_id) restore_current_blog();

			return $ids;
		}

		return false;
	}

	static function by_meta_and_blog_id($args) {

		$defaults = array(
			'post_type' => 'post',
			'blog_id' => false,
			'meta_conditions' => false
		);

		extract(wp_parse_args($args, $defaults));

		global $wpdb;

		$table_prefix = $wpdb->get_blog_prefix($blog_id);

		// Query part 0 - SELECT statement
		$query = "SELECT DISTINCT {$table_prefix}posts.ID
			FROM {$table_prefix}posts
			LEFT JOIN {$table_prefix}postmeta ON ({$table_prefix}posts.ID = {$table_prefix}postmeta.post_id)
			WHERE ({$table_prefix}posts.post_status = 'publish'
			AND {$table_prefix}posts.post_type = '{$post_type}')";

		// Query part 1 - Search meta fields
		if(is_array($meta_conditions)) {
			foreach($meta_conditions as $key => $value) {
				$where_parts[] = "( {$table_prefix}postmeta.meta_key = '$key' AND {$table_prefix}postmeta.meta_value = '$value' )";
			}
		}

		if(count($where_parts)) $query .= ' AND (' . implode(' OR ', $where_parts) . ') ';

		// Query last part - ORDER Statement
		$query .= " ORDER BY {$table_prefix}posts.post_date DESC";

		$results = $wpdb->get_results($query, ARRAY_A);

		if($wpdb->num_rows > 0) {

			// Flatten the results
			foreach($results as $result){
				$ids[] = $result['ID'];
			}
			return $ids;
		} else {
			return false;
		}
	}
}