<?php
/**
 * Slider
 * custom post type loop for Orbit slider by ZURB
 *
 * @package Reactor
 * @author Anthony Wilhelm (@awshout / anthonywilhelm.com)
 * @since 1.0.0
 * @param $args Optional. Override defaults.
 * @license GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 */

function reactor_slider( $args = '' ) {

	$defaults = array(
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'category'       => '',
		'field'          => 'id',
		'posts_per_page' => -1,
		'slider_id'      => '',
		'data_options'   => array(),
		'echo'           => true
	);
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'reactor_slider_args', $args );

	// customizer can return a value of -1
	if ( -1 == $args['category'] ) {
		 $args['category'] = '';
	}

	$slider_id = ( $args['slider_id'] ) ? ' id="' . $args['slider_id'] . '"' : '';

	// start the slider loop
	$query_args = array(
		'post_type'      => 'slide',
		'orderby'        => $args['orderby'],
		'order'          => $args['order'],
		'posts_per_page' => $args['posts_per_page']
	 );

	// if specific category is passed to args use tax_query
	if ( $args['category'] ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'slide-category',
				'field'    => $args['field'],
				'terms'    => $args['category'],
				'operator' => 'IN'
			 )
		 );
	}

	global $slider_query;
	$slider_query = new WP_Query( $query_args );
	$output = ''; $slide = ''; $caption = '';

	// add data-options to slider
	if ( $args['data_options'] && is_array( $args['data_options'] ) ) {
		$options_array = array();
		foreach ( $args['data_options'] as $key => $value ) {
			$options_array[] = $key . ':' . $value;
		}
		$options = implode( '; ', $options_array );
		$data_options = ' data-options="' . $options . '"';
	} else {
		$data_options = '';
	}

	if ( $slider_query->have_posts() ) :
		$output .= '<div class="preloader"></div>';
        $output .= '<ul' . $slider_id . ' data-orbit' . $data_options . '>';

        while ( $slider_query->have_posts() ) : $slider_query->the_post();
		    $post_id = get_the_ID();

			$output .= '<li>';

			// if slide post has a thumbnail use that as the slide
            if ( has_post_thumbnail() ) {
                $img_id = get_post_thumbnail_id( $post_id );
                $img_url = wp_get_attachment_url( $img_id );
                $alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );

            	$output .= '<img src="' . $img_url . '" alt="' . $alt . '" />';

			// else use the content of the post for the slide
            } else {
                $output .= '<div class="slide-content">';
                $output .= '<div class="row"><div class="large-12 small-12 large-centered small-centered columns">';
            	$output .= get_the_content();
            	$output .= '</div></div></div>';
            }

			// if slide post has a url set it up
			$slide_url = get_post_meta( $post_id, '_slide_url', true );
			$show_title = get_post_meta( $post_id, '_slide_title', true );

			if ( $show_title ) {
				if ( $slide_url ) {
					$slide_title = '<h4 class="slide-title"><a href="' . $slide_url . '">' . get_the_title() . '</a></h4>';
				} else {
					$slide_title = '<h4 class="slide-title">' . get_the_title() . '</h4>';
				}
			} else {
				$slide_title = '';
			}

			// if slide post has excerpt use it for the caption
			if ( has_excerpt() ) {
				$output .= '<div class="orbit-caption hide-for-small">';
				$output .= $slide_title;
				$output .= '<p class="slide-excerpt">' . get_the_excerpt() . '</p>';
				$output .= '</div>';
			}
			elseif ( !has_excerpt() && $show_title ) {
				$output .= '<div class="orbit-caption hide-for-small">';
				$output .= $slide_title;
				$output .= '</div>';
			}

			$output .= '</li>';

    	endwhile;
		$output .= '</ul>';

		if ( $args['echo'] ) {
			echo apply_filters('reactor_slider', $output, $args);
		} else {
			return apply_filters('reactor_slider', $output, $args);
		}

    endif;
	wp_reset_postdata();
}