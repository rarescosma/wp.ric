<?php

class ricMetabox {

	protected $args;
	protected $boxes = array();
	protected $sideboxes = array();

	private $ui;

	private $box_id = 0;

	function __construct( $args ) {
		// Parse arguments
		$defaults = array(
			'post_id' => 0,
			'post_type' => 'post'
		);

		$this->args = wp_parse_args( $args, $defaults );

		if( empty( $this->args['post_type'] ) and $this->args['post_id'] > 0 ) {
			$this->args['post_type'] = get_post_type( $this->args['post_id'] );
		}

		// Try to load and parse config file
		if( ! $this->parse_config() ) return;

		// Initialize ricUI
		$this->ui = ricUI::singleton();

		// Admin init: add metaboxes
		add_action( 'ric_admin_init', array( &$this, 'admin_init' ) );

		// Hook in to save meta data
		add_action( 'save_post', array( &$this, 'save_postdata' ), 1, 2 );
	}

	function parse_config() {
		$config_dir = ricUtil::slash( CONFIG_PATH . '/metaboxes' ) ;

		switch ( $this->args['post_type'] ) {
			case 'page':
				// Config file same as template file
				$config_file = get_post_meta( $this->args['post_id'], '_wp_page_template', true );
			break;
			default:
				$config_file = 'cpt-' . $this->args['post_type'] . '.php';
			break;
		}

		$config_file = $config_dir . $config_file;

		if( file_exists( $config_file ) and is_file( $config_file ) ) {
			// Include the config file
			include_once( $config_file );

			// And call the appropiate functions
			if( function_exists('metaboxes') )
				$this->boxes = metaboxes( $this->args['post_id'] );
			if( function_exists('sideboxes') )
				$this->sideboxes = sideboxes( $this->args['post_id'] );
		} else {
			return false;
		}

		return true;
	}

	function admin_init() {
		if ( function_exists( 'add_meta_box' ) ) {
			foreach ( array_keys( $this->boxes ) as $box_name ) {
				add_meta_box( sanitize_title( $box_name ), $box_name, array( &$this, '_box_content_hook' ), $this->args['post_type'], 'normal', 'high' );
			}

			foreach ( array_keys( $this->sideboxes ) as $box_name ) {
				add_meta_box( sanitize_title( $box_name ), $box_name, array( &$this, '_box_content_hook'), $this->args['post_type'], 'side', 'low' );
			}
		}
	}

	function _box_content_hook( $obj, $box ) {
		if( array_key_exists( $box['title'], $this->boxes ) ) {
			$page_boxes = $this->boxes[$box['title']];
		} elseif( array_key_exists( $box['title'], $this->sideboxes ) ) {
			$page_boxes = $this->sideboxes[$box['title']];
		}else{
			return;
		}

		foreach ( $page_boxes as $page_box ) {
			// The arguments
			$args = array(
				'type' => $page_box[0],
				'slug' => $page_box[1],
				'title' => $page_box[2],
				'desc' => $page_box[3],
				'extra' => $page_box[5]
			);

			$meta = $this->meta( $page_box[1] );

			$args['value'] = ( empty( $meta ) ) ? $page_box[4] : $meta;

			$body = $this->ui->machine( $args, false );

			switch( $args['type'] ) {
				case 'custom':
					$header = '';
					$footer = '';
				break;

				case 'hidden':
					$header = '';
					$footer = '';
				break;

				default: // all other input types
					$header = $this->field_header();
					$footer = $this->field_footer();
				break;
			}

			echo $header . $body . $footer;
		}
	}

	function field_header() {
		return '<div class="ric-ui-metabox">';
	}

	function field_footer() {
		return '</div>';
	}

	function save_postdata( $post_id, $post ) {
		if ( 'revision' == $post->post_type  ) {
			// don't store custom data twice
			return;
		}

		// Custom hook
		do_action( 'metabox_before_save_postdata', $post->ID );

		// The data is already in $boxes, but we need to flatten it out.
		$my_data = array();
		foreach( $this->boxes as $page_box ) {
			foreach( $page_box as $page_fields ) {
				$my_data[$page_fields[1]] = array(
					'type' => $page_fields[0],
					'value' => $_POST[$page_fields[1]]
				);
			}
		}
		// Add values of $my_data as custom fields
		// Let's cycle through the $my_data array!

		foreach( $my_data as $key => $data ) {
			extract( $data );

			switch( $type ) {
				case 'file':
					if( isset( $_FILES[$key] ) and ! empty( $_FILES[$key] ) ) {
						$file = $_FILES[$key];

						if( ! empty( $file['name'] ) ) {
							$args = array(
								'test_form' => false
							);

							$uploaded_file = wp_handle_upload( $file, $args );

							if( isset( $uploaded_file['file'] ) ) {
								// Upload successful; add as attachment
								$attach = array(
									'post_mime_type' => $uploaded_file['type'],
		                            'post_title' => array_pop( explode( '/', $uploaded_file['url'] ) ),
		                            'post_content' => '',
		                            'post_status' => 'inherit'
								);

								$attach_id = wp_insert_attachment( $attach, $uploaded_file['file'], $post->ID );

								if( $attach_id ) {
									update_post_meta( $post->ID, $key, $attach_id );
								}
							}
						}
					}
				break;

				default:
					// if $value is an array, make it a CSV (unlikely)
					$value = implode(',', (array)$value);

			 		if( get_post_meta( $post->ID, $key, FALSE ) ) {
			 			// Custom field has a value.
			 			update_post_meta( $post->ID, $key, $value );
			 		} else {
			 			// Custom field does not have a value.
			 			add_post_meta( $post->ID, $key, $value );
			 		}

			 		if( ! $value and $type != 'file' ) {
			 			// delete blanks
			 			delete_post_meta( $post->ID, $key );
			 		}
			 	break;
			}
		}

		// Custom hook
		do_action( 'metabox_after_save_postdata', $post->ID );
	}

	private function meta( $key ) {
		return get_post_meta( $this->args['post_id'], $key, true );
	}
}

