<?php
/**
 * Plugin Name:	Reusable Blocks Extended
 * Plugin URI:	https://jeanbaptisteaudras.com/en/2019/09/reusable-block-extended-a-cool-wordpress-plugin-to-extend-gutenberg-reusable-block-feature/
 * Description:	Extend Gutenberg Reusable Blocks feature with a complete admin panel, widgets, shortcodes and PHP functions.
 * Version:		0.9
 * Author:		audrasjb
 * Author URI:	https://jeanbaptisteaudras.com/en
 * License:		GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	reusable-blocks-extended
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function reblex_admin_init() {
	/* Polylang compatibility */
	if ( function_exists( 'pll__' ) ) {
		add_action( 'pre_get_posts', 'reblex_reusable_menu_polylang_all_langs', 10, 2 );
	}
}
add_action( 'admin_init', 'reblex_admin_init' );

if ( is_admin() ) {
	add_action( 'registered_post_type', 'reblex_reusable_menu_display', 10, 2 );
	add_filter( 'manage_wp_block_posts_columns', 'reblex_reusable_screen_add_column' );
	add_action( 'manage_wp_block_posts_custom_column' , 'reblex_reusable_screen_fill_column', 1000, 2);
	add_action( 'admin_enqueue_scripts', 'reblex_reusable_screen_enqueues' );

	// Force Block editor for Reusable Blocks even when Classic editor plugin is activated
	add_filter( 'use_block_editor_for_post', 'reblex_enable_gutenberg_post', 1000, 2 );
	add_filter( 'use_block_editor_for_post_type', 'reblex_enable_gutenberg_post_type', 1000, 2 );
}

/**
 * reblex_enable_gutenberg_post function.
 * Force block editor for reusable block post type
 * 
 * @param bool $can_edit
 * @param WP_Post $post
 */
function reblex_enable_gutenberg_post( $use_block_editor, $post ) {
	if ( empty( $post->ID ) ) return $use_block_editor;
	if ( 'wp_block' === get_post_type( $post->ID ) ) return true;
	return $use_block_editor;
}

/**
 * reblex_enable_gutenberg_post_type function.
 * Force block editor for reusable block post type
 * @param bool $can_edit
 * @param bool $post_type
 */
function reblex_enable_gutenberg_post_type( $use_block_editor, $post_type ) {
	if ( 'wp_block' === $post_type ) return true;
	return $use_block_editor;
}


/**
 * reblex_reusable_screen_enqueues function.
 * 
 * @param mixed $hook
 * @return void
 */
function reblex_reusable_screen_enqueues( $hook ) {
	wp_enqueue_script( 'wp-embed' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'reusable-block-extended', plugin_dir_url( __FILE__ ) . 'js/reusable-blocks-extended.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_enqueue_style( 'reusable-blocks-extended', plugin_dir_url( __FILE__ ) . 'css/reusable-blocks-extended.css', 'wp-jquery-ui-dialog', __FILE__ );
}
/**
 * reblex_reusable_menu_display function.
 * 
 * @param mixed $type
 * @param mixed $args
 * @return void
 */
function reblex_reusable_menu_display( $type, $args ) {
	if ( 'wp_block' !== $type ) { return; }
	$args->show_in_menu = true;
	$args->_builtin = false;
	$args->labels->name = esc_html__( 'Blocks', 'reusable-blocks-extended' );
	$args->labels->menu_name = esc_html__( 'Blocks', 'reusable-blocks-extended' );
	$args->menu_icon = 'dashicons-screenoptions';
	$args->menu_position = 58;
}
/**
 * reblex_reusable_menu_polylang_all_langs function.
 * Polylang compatibility.
 * 
 * @param mixed $query
 * @return void
 */
function reblex_reusable_menu_polylang_all_langs( $query ) {
	$screen = get_current_screen();
	if ( is_object( $screen ) ) {
		if ( $screen->post_type == 'wp_block' ) {
			$query->set( 'lang', '' );
			$query->set( 'tax_query', '' );
		}
	}
	return $query;
}

/**
 * reblex_reusable_screen_block_pattern_registration function.
 * 
 */
function reblex_reusable_screen_block_pattern_registration() {
	$screen = get_current_screen();
 
	if ( 'wp_block' !== $screen->post_type ) {
		return;
	}

	if ( isset( $_GET['create_pattern'] ) && intval( $_GET['create_pattern'] ) > 0 ) :
		update_post_meta( intval( $_GET['create_pattern'] ), 'transformed_into_pattern', true );
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Reusable block successfully converted to a new block pattern. It is now available in the block editor.', 'reusable-blocks-extended' ); ?></p>
		</div>
	<?php
	endif;

	if ( isset( $_GET['delete_pattern'] ) && intval( $_GET['delete_pattern'] ) > 0 ) :
		update_post_meta( intval( $_GET['delete_pattern'] ), 'transformed_into_pattern', false );
		?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'Block pattern successfully deleted.', 'reusable-blocks-extended' ); ?></p>
			</div>
	<?php
	endif;
}
add_action( 'admin_notices', 'reblex_reusable_screen_block_pattern_registration' );

/**
 * reblex_reusable_screen_add_column function.
 * 
 * @param mixed $columns
 * @return $columns
 */
function reblex_reusable_screen_add_column( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => esc_html__( 'Block title', 'reusable-blocks-extended' ),
		'reblex-reusable-instances' => esc_html__( 'Used in', 'reusable-blocks-extended' ),
		'reblex-reusable-preview' => esc_html__( 'Usage', 'reusable-blocks-extended' ),
		'reblex-reusable-conversion' => esc_html__( 'Pattern conversion', 'reusable-blocks-extended' ),
		'reblex-date-modified' => esc_html__( 'Last modified', 'reusable-blocks-extended' )
	);
	return $columns;
}

/**
 * reblex_reusable_screen_fill_column function.
 * 
 * @param mixed $column
 * @param mixed $ID
 * @return void
 */
function reblex_reusable_screen_fill_column( $column, $ID ) {
	global $post;
	switch( $column ) {

		case 'reblex-reusable-instances' :

			global $wpdb;
			$tag = '<!-- wp:block {"ref":' . $ID . '}';
			$search = '%' . $wpdb->esc_like($tag) . '%';
			$instances = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_content LIKE '$search' and post_type NOT IN ('revision', 'attachment', 'nav_menu_item')" );
			$count_instances = count( $instances );
			echo '<p class="reblex_instance_label">';
			if ( $count_instances > 0 ) {
				echo '<strong>';
				echo sprintf( 
					_n( 'Used in %s post:', 'Used in %s posts:', $count_instances, 'reusable-blocks-extended' ),
					$count_instances
				);
				echo '</strong>';
			} else {
				esc_html_e( 'Not used yet.', 'reusable-blocks-extended' );
			}
			echo '</p>';
			if ( $instances ) {
				$count = 0;
				$more_items_class = '';
				foreach( $instances as $instance ){
					if ( $count === 5 ) {
						$more_items_class = 'more_items_class';
						?>
						<button type="button" class="button button-secondary button-small reblex_button_more" data-toggle="<?php esc_html_e( 'Fold up', 'reusable-blocks-extended' ) ?>">
							<?php
							$more_instances = $count_instances - $count;
							echo sprintf(
								esc_html__( 'Show %s more instance(s)', 'reusable-blocks-extended' ),
								$more_instances
							);
							?>
						</button>
						<?php
					} 
					echo '<p class="reblex_instance_paragraph ' . $more_items_class . '"><a href="' . get_edit_post_link( $instance->ID ) . '">' . $instance->post_title . ' [' . get_post_type( $instance->ID ) . ']</a></p>';
					$count++;
				}
			}
			break;

		case 'reblex-reusable-preview' :

			echo '<p>' . esc_html__( 'Shortcode:', 'reusable-blocks-extended' ) . ' <code>[reblex id=\'' . $ID . '\']</code></p>';
			echo '<p>' . esc_html__( 'PHP function:', 'reusable-blocks-extended' ) . ' <code>reblex_display_block(' . $ID . ')</code></p>';

			reblex_display_modal( $ID );

			break;

		case 'reblex-reusable-conversion' :

			if ( true === reblex_check_wordpress_version_55() ) {
				if ( true == get_post_meta( $ID, 'transformed_into_pattern', true ) ) {
					echo '<p>' . esc_html__( 'Converted to a block pattern.', 'reusable-blocks-extended' ) . '</p>';
					echo '<p class="reblex-delete-pattern"><a href="' . admin_url( 'edit.php?post_type=wp_block&delete_pattern=' . $ID ) . '">' . esc_html__( 'Delete existing pattern', 'reusable-blocks-extended' ) . '</a></p>';
				} else {
					echo '<a class="button button-primary" href="' . admin_url( 'edit.php?post_type=wp_block&create_pattern=' . $ID ) . '">' . esc_html__( 'Convert to block pattern', 'reusable-blocks-extended' ) . '</a>';
				}
			}

			break;

		case 'reblex-date-modified' :

			$d = get_date_from_gmt( $post->post_modified, 'Y-m-d H:i:s' );
			echo sprintf(
				/* translators: %1$s: Date the block was last modified %2$s Time the block was last modified %3$s Author */
				esc_html__( '%1$s at %2$s', 'reusable-blocks-extended' ),
				date_i18n( get_option('date_format'), strtotime( $d ) ),
				date_i18n( get_option('time_format'), strtotime( $d ) )
			);
			if ( get_post_meta( $ID, '_edit_last', true ) ) {
				$last_user = get_userdata( get_post_meta( $ID, '_edit_last', true ) );
				echo ' ' . esc_html__( 'by', 'reusable-blocks-extended' ) . ' ' . $last_user->display_name;
			}
			break;

		default :
			break;
	}
}
/**
 * reblex_reusable_save_registered_styles function.
 * 
 */
function reblex_reusable_save_registered_styles() {
	if ( false === get_transient( 'reblex_reusable_registered_stylesheets' ) ) {
		global $wp_styles;
		$results = array();
		foreach( $wp_styles->queue as $style ) {
			$results[] =  $wp_styles->registered[$style]->src;
		}
		set_transient( 'reblex_reusable_registered_stylesheets', json_encode( $results ), DAY_IN_SECONDS );
	}
}
add_action( 'wp_head', 'reblex_reusable_save_registered_styles' );
/**
 * reblex_reusable_get_registered_styles function.
 * 
 * @return Stylesheets registered into `reblex_reusable_registered_stylesheets` transient.
 */
function reblex_reusable_get_registered_styles() {
	$results = false;
	if ( get_transient( 'reblex_reusable_registered_stylesheets' ) ) {
		$results = json_decode( get_transient( 'reblex_reusable_registered_stylesheets' ) );
	}
	return $results;
}

/**
 * reblex_display_modal function.
 * 
 * @param mixed $ID
 * @return Modal preview for Reusable Blocks screen.
 */
function reblex_display_modal( $ID ) {
	$styles = reblex_reusable_get_registered_styles();
	if ( $styles ) {
		$link_tags = '<style>';
		$i = 1;
		foreach ( $styles as $style ) {
			if ( strpos( $style, site_url() ) === 0 ) {
				$link_tags .= '@import url(\'' . $style . '\');';
			} else {
				$link_tags .= '@import url(\'' . site_url( $style ) . '\');';
			}
			$i++;
		}
		$link_tags .= '</style>';
		$content_post = get_post( $ID );
		$content = $content_post->post_content;
		$block_list = parse_blocks( $content );
		?>
		<button type="button" class="button button-secondary button-small reblex_button" id="reblex_button_<?php echo $ID; ?>" data-target="#reblex_div_<?php echo $ID; ?>">
			<?php esc_html_e( 'Preview', 'reusable-blocks-extended' ); ?>
		</button>
		<div id="reblex_div_<?php echo $ID; ?>" class="reblex_modal" data-title="<?php echo esc_attr__( 'Block preview:', 'reusable-blocks-extended' ) . ' ' . esc_attr( get_the_title( $ID ) ); ?>">
			<span>
				<em>
					<?php esc_html_e( 'Note: this feature is experimental.', 'reusable-blocks-extended' ); ?>
					<br />
					<?php esc_html_e( 'The exact rendering may depends on the real block context.', 'reusable-blocks-extended' ); ?>
				</em>
			</span>
			<?php
			if ( $block_list ) {
				$block_names = array();
				foreach ( $block_list as $block ) {
					if ( $block['blockName'] ) {
						$block_names[] = substr( strrchr( $block['blockName'], '/' ), 1 );
					}
				}	
				echo '<p>' . esc_html__( 'Contains:', 'reusable-blocks-extended' ) . ' ' . implode( ', ', $block_names ) . '</p>';
			}
			?>
			<iframe id="reblex_iframe_<?php echo $ID; ?>">
				<?php echo $link_tags; ?>
				<?php echo apply_filters( 'the_content', $content ); ?>
			</iframe>
		</div>
		<?php

	} else {
		esc_html_e( 'No preview available. Please visit your front-end and come back.', 'reusable-blocks-extended' );
	}
}
/**
 * reblex_get_block function.
 * 
 * @param mixed $id The ID of the reusable block.
 * @return $content The content of the block.
 */
function reblex_get_block( $id ) {
	$content_post = get_post( $id );
	$content = $content_post->post_content;
	return $content;
}
/**
 * reblex_display_block function.
 * 
 * @param mixed $id The ID of the reusable block.
 */
function reblex_display_block( $id ) {
	echo apply_filters( 'the_content', reblex_get_block( $id ) );
}
/**
 * reblex_shortcode function.
 * 
 * @param mixed $atts
 *			  id The ID of the reusable block.
 * @return $content The content of the block.
 */
function reblex_shortcode( $atts ){
	extract(shortcode_atts(
		array(
			'id' => ''
	), $atts));
	$content = apply_filters( 'the_content', reblex_get_block( $id ) );
	return $content;
}
add_shortcode( 'reblex', 'reblex_shortcode' );

/**
 * Reblex_Widget class.
 * 
 * @extends WP_Widget
 */
class Reblex_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'reblex-widget',
			'Reusable block'
		);
		add_action(
			'widgets_init',
			function() {
				register_widget( 'Reblex_Widget' );
			}
		);
	}
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo '<h3>' . esc_html( $instance['title'] ) . '</h3>';
		}
		$block_id = '';
		if ( isset( $instance['block_id'] ) ) {
			$block_id = $instance['block_id'];
		}
		echo apply_filters( 'the_content', reblex_get_block( $block_id ) );
		echo $args['after_widget'];
	}
 
	public function form( $instance ) {
		$title	= ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$block_id = ( ! empty( $instance['block_id'] ) ) ? $instance['block_id'] : '';
		$args = array(
			'post_type' => 'wp_block',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		);
		$query_reusable = new WP_Query( $args );
		?>

		<?php if ( $query_reusable->have_posts() ) : ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Optional widget title', 'reusable-blocks-extended' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'block_id' ) ); ?>"><?php echo esc_attr( 'Select reusable block', 'reusable-blocks-extended' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'block_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'block_id' ) ); ?>">
			<?php while ( $query_reusable->have_posts() ) : $query_reusable->the_post(); ?>
				<option value="<?php echo get_the_ID(); ?>" <?php selected( $block_id, get_the_ID() ); ?>>
					<?php the_title() ?> - (<?php echo sprintf( esc_html__( 'ID: %s', 'reusable-blocks-extended' ), get_the_ID() ); ?>)
				</option>
			<?php endwhile; ?>
			</select>
		</p>
		<?php else : ?>
			<p><?php esc_html_e( 'You don’t have any reusable block yet.', 'reusable-blocks-extended' ); ?></p>
		<?php endif; wp_reset_postdata(); ?>

		<?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']	= ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['block_id'] = ( ! empty( $new_instance['block_id'] ) ) ? $new_instance['block_id'] : '';
		return $instance;
	}
}
$reblex_widget = new Reblex_Widget();

/**
 * custom_glance_items function.
 * 
 * @access public
 * @param array $items (default: array())
 * @return void
 */
function reblex_add_reusables_to_dashboard( $items = array() ) {
 
	$num_posts = wp_count_posts( 'wp_block' );
	if( $num_posts ) {
	   
		$published = intval( $num_posts->publish );
		$post_type = get_post_type_object( 'wp_block' );
		$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'reusable-blocks-extended' );
		$text = sprintf( $text, number_format_i18n( $published ) );
			 
		if ( current_user_can( $post_type->cap->edit_posts ) ) {
			$items[] = sprintf( '<a class="wp_block-count" href="edit.php?post_type=wp_block">%s</a>', $text ) . "\n";
		} else {
			$items[] = sprintf( '<span class="wp_block-count">%s</span>', $text ) . "\n";
		}
	}
	return $items;
}
add_filter( 'dashboard_glance_items', 'reblex_add_reusables_to_dashboard', 10, 1 );

/**
 * custom_glance_items function. Register user generated patterns.
 */
function reblex_register_block_patterns() {
	global $pagenow;

	if ( ! function_exists( 'register_block_type' ) || ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	if ( 'media-new.php' === $pagenow || 'async-upload.php' === $pagenow ) {
		return;
	}

	register_block_pattern_category(
		'converted',
		array(
			'label' => __( 'Converted from reusable blocks', 'reusable-blocks-extended' ),
		)
	);

	$args = array(
		'post_type'      => 'wp_block',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'   => 'transformed_into_pattern',
				'value' => 1,
			)
		)
	);
	$query_patterns = new WP_Query( $args );
	if ( $query_patterns->have_posts() ) {
		while ( $query_patterns->have_posts() ) {
			$query_patterns->the_post();

			$pattern_id      = get_the_ID();
			$pattern_title   = get_the_title();
			$pattern_slug    = get_post_field( 'post_name', $pattern_id );
			$pattern_content = get_the_content();

			register_block_pattern(
				'reblex/' . $pattern_slug,
				array(
					'title'      => $pattern_title,
					'content'    => $pattern_content,
					'categories' => array( 'converted' ),
				)
			);
		}
	}
	wp_reset_postdata();
}
add_action( 'admin_init', 'reblex_register_block_patterns' );

function reblex_check_wordpress_version_55() {
	if ( function_exists( 'wp_is_auto_update_enabled_for_type' ) ) {
		return true;
	} else {
		return false;
	}
}
