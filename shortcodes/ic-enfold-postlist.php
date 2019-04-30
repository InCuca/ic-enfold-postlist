<?php
error_log(class_exists( 'ic_enfold_postlist' ));

if ( !class_exists( 'ic_enfold_postlist' ) ) {
    class ic_enfold_postlist extends aviaShortcodeTemplate
	{
		function shortcode_insert_button()
		{
			// Configure shortcode
			$this->config['name']		= 'Post List';
			$this->config['icon']		= plugin_dir_url(__FILE__) . '../images/ic-template-icon.png';
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'ic-enfold-postlist';
			$this->config['tooltip'] 	= 'Lista de posts';
			$this->config['preview'] 	= false;
		}

		function popup_elements()
		{
			// Set admin popup elements
			$this->elements = array(
				array(	"name" 		=> __("Which categories should be used for the blog?", 'avia_framework' ),
							"desc" 		=> __("You can select multiple categories here. The Page will then show posts from only those categories.", 'avia_framework' ),
				            "id" 		=> "categories",
				            "type" 		=> "select",
	        				"multiple"	=> 6,
							"subtype" 	=> "cat"
				),
				array(	"name" 		=> __("Which categories should be evited?", 'avia_framework' ),
							"desc" 		=> __("You can select multiple categories here. The Page will ignore posts from only those categories.", 'avia_framework' ),
				            "id" 		=> "notCategories",
				            "type" 		=> "select",
	        				"multiple"	=> 6,
							"subtype" 	=> "cat"
				),
				array(
					"name" => "Quantidade",
					"desc" => "Quantidade de posts?",
					"id" => "qtd",
					"type" => "select",
					"std" 	=> "3",
					"subtype" => AviaHtmlHelper::number_array(1,10,1),
				),
			);
		}

		function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
		{
			global $avia_config;
			
			// Get options from admin popup
			$atts = shortcode_atts(array(
				'class'	=> $meta['el_class'],
				'custom_class' => '',
				'custom_markup' => $meta['custom_markup'],
				'qtd' => '3',
				'categories' => '',
				'notCategories' => '',
			), $atts, $this->config['shortcode']);

			/*
			 * Creates $class, $custom_class, $custom_markup, $message
			 */
			extract($atts);
			$custom_class = $custom_class?" $custom_class":"";

			$output = '<div class="post-loop' . $custom_class . '">';
			// WP_Query arguments
			$args = array(
				'posts_per_page'         => $atts['qtd'],
				'cat'                    => $atts['categories'],
				'category__not_in'       => $atts['notCategories'],
				'post__not_in' => empty($avia_config['posts_on_current_page'])? array() : $avia_config['posts_on_current_page'],
			);

			// The Query
			$query = new WP_Query( $args );

			// The Loop
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'medium' )[0];
					$cat = get_the_category()[0]->name;
					$cat_link = get_category_link(get_the_category()[0]->term_id);
					$cat_slug = get_the_category()[0]->slug;
					$titulo = get_the_title();
					$resumo = $this->excerpt(20);
					$link = get_permalink();
					$output .= '<div class="post-item">';
					$output .= '<a class="img-bg-link" href="'.$link.'" style="background-image: url('.$img.');"></a>';
					$output .= '<div class="content">';
					$output .= '<a href="'.$cat_link.'" class="'.$cat_slug.'">'. $cat .'</a>';
					$output .= '<h2><a href="'. $link .'">'. $titulo .'</a></h2>';
					$output .= '<p>'.$resumo.'</p>';
					$output .= '</div>';
					$output .= '</div>';
					$output .= '<hr>';

					$avia_config['posts_on_current_page'][] = get_the_ID();
				}

			} else {
				// no posts found
			}
			$output .= '</div>'; 
			return $output;
		}

		function extra_assets()
		{
			$plugin_dir = plugin_dir_url(__FILE__);
			wp_enqueue_style( 'ic-enfold-postlist' , $plugin_dir.'../css/ic-enfold-postlist.css' , array(), false );
		}

		function excerpt($limit) {
			$excerpt = explode(' ', get_the_excerpt(), $limit);
			if (count($excerpt)>=$limit) {
				array_pop($excerpt);
				$excerpt = implode(" ",$excerpt).'...';
			} else {
				$excerpt = implode(" ",$excerpt);
			}	
			$excerpt = preg_replace('`[[^]]*]`','',$excerpt);
			return $excerpt;
		}
	}
}