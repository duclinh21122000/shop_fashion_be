<?php 

    /*----------------------------

    Theme Setup

    ------------------------------*/

    define ('thelight_home' , esc_url( home_url( '/' ) ));
    define ('thelight_uri' , get_template_directory_uri());
    define ('thelight_dir' , get_template_directory());
    define ('thelight_inc', thelight_uri . '/inc');

    /*----------------------------

	Basic

	------------------------------*/

    function thelight_theme_setup() {

		load_theme_textdomain( 'nsc' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

        register_nav_menus( array (
			'main'    => __( 'Header - Main Menu', 'thelight' ),
		    'navigation-footer' => __( 'Footer - Navigation Menu', 'thelight' ),
		));

        add_theme_support( 'html5', array(
            'format',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

    }

    add_action( 'after_setup_theme', 'thelight_theme_setup' );

    /*----------------------------

	Use old editor

	------------------------------*/

    add_filter('use_block_editor_for_post', '__return_false');

    /*----------------------------

	Disable Admin Toolbar

	------------------------------*/

    add_filter( 'show_admin_bar', '__return_false' );

    /*----------------------------

	Insert JS & CSS

	------------------------------*/

    function thelight_css_js() {

        wp_enqueue_style( 'fontawesome', thelight_uri . '/assets/css/fontawesome/css/all.min.css', array(), '' );
        wp_enqueue_style( 'slick', thelight_uri . '/assets/libs/slick/slick.css', array(), '' );
        wp_enqueue_style( 'slick-theme', thelight_uri . '/assets/libs/slick/slick-theme.css', array(), '' );
        wp_enqueue_style( 'boostrap-style', thelight_uri . '/assets/css/boostrap.css', array(), '' );
        wp_enqueue_style( 'theme-style', thelight_uri . '/assets/css/theme.css', array(), '' );
        wp_enqueue_style( 'style', thelight_uri . '/style.css', array(), '' );

        wp_enqueue_script('jquery-js', thelight_uri . '/assets/js/jquery.min.js', '', '', true);
        wp_enqueue_script('slick-js', thelight_uri . '/assets/libs/slick/slick.min.js', '', '', true);
        wp_enqueue_script('thelightjs', thelight_uri . '/assets/js/main.js', '', '', true);

    }

    add_action( 'wp_enqueue_scripts', 'thelight_css_js' );

    /*----------------------------

	Allow webp

	------------------------------*/

    function webp_upload_mimes($existing_mimes) {
        $existing_mimes['webp'] = 'image/webp';
        return $existing_mimes;
    }

    add_filter('mime_types', 'webp_upload_mimes');

    /*----------------------------

    Allow SVG backend

    ------------------------------*/

    add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
        $filetype = wp_check_filetype( $filename, $mimes );
        return [
            'ext'             => $filetype['ext'],
            'type'            => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        ];
    }, 10, 4 );

    function cc_mime_types( $mimes ){
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    add_filter( 'upload_mimes', 'cc_mime_types' );

    function fix_svg() {
        echo '<style type="text/css">
            .attachment-266x266, .thumbnail img {
                width: 100% !important;
                height: auto !important;
            }
        </style>';
    }

    add_action( 'admin_head', 'fix_svg' );

    // Option page
    if( function_exists('acf_add_options_page') ) {
        acf_add_options_page(array(
            'page_title' 	=> 'Trang quản trị',
            'menu_title'	=> 'Trang quản trị',
            'menu_slug' 	=> 'theme-general-settings',
            'capability'	=> 'edit_posts',
            'redirect'		=> false
        ));
    }

    // Limit the character length in excerpt
    function custom_excerpt_length( $length ) {
        return 21;
    }

    add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

    // Change 3 dot in excerpt
    function new_excerpt_more( $more ) {
        return '...';
    }

    add_filter('excerpt_more', 'new_excerpt_more');

    /*
    * Set post views count using post meta
    */

    function setPostViews($postID) {
        $countKey = 'post_views_count';
        $count = get_post_meta($postID, $countKey, true);
        if($count==''){
            $count = 0;
            delete_post_meta($postID, $countKey);
            add_post_meta($postID, $countKey, '0');
        }else{
            $count++;
            update_post_meta($postID, $countKey, $count);
        }
    }

    // custom category
    function get_template_for_category( $template ) {

        if ( basename( $template ) === 'category.php' ) { // No custom template for this specific term, let's find it's parent
            // get the current term, e.g. red
            $term = get_queried_object();
    
            // check for template file for the page category
            $slug_template = locate_template( "category-{$term->slug}.php" );
            if ( $slug_template ) return $slug_template;
    
            // if the page category doesn't have a template, then start checking back through the parent levels to find a template for a parent slug
            $term_to_check = $term;
            while ( $term_to_check ->parent ) {
                // get the parent of the this level's parent
                $term_to_check = get_category( $term_to_check->parent );
    
                if ( ! $term_to_check || is_wp_error( $term_to_check ) )
                    break; // No valid parent found
    
                // Use locate_template to check if a template exists for this categories slug
                $slug_template = locate_template( "category-{$term_to_check->slug}.php" );
                // if we find a template then return it. Otherwise the loop will check for this level's parent
                if ( $slug_template ) return $slug_template;
            }
        }
    
        return $template;
    }
    add_filter( 'category_template', 'get_template_for_category' );

    // custom single for category
    add_filter('single_template', 'check_for_category_single_template');
    function check_for_category_single_template( $t )
    {
    foreach( (array) get_the_category() as $cat ) 
    { 
        if ( file_exists(get_stylesheet_directory() . "/single-category-{$cat->slug}.php") ) return get_stylesheet_directory() . "/single-category-{$cat->slug}.php"; 
        if($cat->parent)
        {
        $cat = get_the_category_by_ID( $cat->parent );
        if ( file_exists(get_stylesheet_directory() . "/single-category-{$cat->slug}.php") ) return get_stylesheet_directory() . "/single-category-{$cat->slug}.php";
        }
    } 
    return $t;
    }

    // Remove category:
    add_filter( 'get_the_archive_title', function ($title) {    
        if ( is_category() ) {    
                $title = single_cat_title( '', false );    
            } elseif ( is_tag() ) {    
                $title = single_tag_title( '', false );    
            } elseif ( is_author() ) {    
                $title = '<span class="vcard">' . get_the_author() . '</span>' ;    
            } elseif ( is_tax() ) { //for custom post types
                $title = sprintf( __( '%1$s' ), single_term_title( '', false ) );
            } elseif (is_post_type_archive()) {
                $title = post_type_archive_title( '', false );
            }
        return $title;    
    });

    // Breadcrumb
    function the_breadcrumb() {
        $delimiter = '»';
        $home = 'Trang chủ';
        $before = '<span class="current">'; 
        $after = '</span>';
        if ( !is_home() && !is_front_page() || is_paged() ) {
            echo '<div id="crumbs">';
            global $post;
            $homeLink = get_bloginfo('url');
            echo '<a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
            if ( is_category() ) {
                global $wp_query;
                $cat_obj = $wp_query->get_queried_object();
                $thisCat = $cat_obj->term_id;
                $thisCat = get_category($thisCat);
                $parentCat = get_category($thisCat->parent);
                if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
                echo $before . single_cat_title('', false) . $after;
            } elseif ( is_day() ) {
                echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
                echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
                echo $before . get_the_time('d') . $after;
            } elseif ( is_month() ) {
                echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
                echo $before . get_the_time('F') . $after;
            } elseif ( is_year() ) {
                echo $before . get_the_time('Y') . $after;
            } elseif ( is_single() && !is_attachment() ) {
                if ( get_post_type() != 'post' ) {
                    $post_type = get_post_type_object(get_post_type());
                    $slug = $post_type->rewrite;
                    echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a> ' . $delimiter . ' ';
                    echo $before . get_the_title() . $after;
                } else {
                    $cat = get_the_category(); $cat = $cat[0];
                    echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                    echo $before . get_the_title() . $after;
                }
            } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
                $post_type = get_post_type_object(get_post_type());
                echo $before . $post_type->labels->singular_name . $after;
            } elseif ( is_attachment() ) {
                $parent = get_post($post->post_parent);
                $cat = get_the_category($parent->ID); $cat = $cat[0];
                echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
                echo $before . get_the_title() . $after;
            } elseif ( is_page() && !$post->post_parent ) {
                echo $before . get_the_title() . $after;
            } elseif ( is_page() && $post->post_parent ) {
                $parent_id = $post->post_parent;
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);
                    $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
                echo $before . get_the_title() . $after;
            } elseif ( is_search() ) {
                echo $before . 'Search results for "' . get_search_query() . '"' . $after;
            } elseif ( is_tag() ) {
                echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
            } elseif ( is_author() ) {
                global $author;
                echo $before . 'Articles posted by ' . $userdata->display_name . $after;
            } elseif ( is_404() ) {
                echo $before . 'Error 404' . $after;
            }
            if ( get_query_var('paged') ) {
                if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
                echo __('Page') . ' ' . get_query_var('paged');
                if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
            }
            echo '</div>';
        }
    }

