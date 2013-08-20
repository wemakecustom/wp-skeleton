<?php

function wp_install_defaults($user_id) {
    global $wpdb, $wp_rewrite, $current_site, $table_prefix, $user_ID;

    $user_ID = $user_id;

    function add_default_options() {
        $options = apply_filters('default_install_options', array(
            'widget_search'          => array( 2 => array( 'title' => '' ), '_multiwidget' => 1 ),
            'widget_recent-posts'    => array( 2 => array( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ),
            'widget_recent-comments' => array( 2 => array( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ),
            'widget_archives'        => array( 2 => array( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ),
            'widget_categories'      => array( 2 => array( 'title' => '', 'count' => 0, 'hierarchical' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ),
            'widget_meta'            => array( 2 => array( 'title' => '' ), '_multiwidget' => 1 ),
            'sidebars_widgets'       => array('wp_inactive_widgets' => array(), 'sidebar-1' => array( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', ), 'sidebar-2' => array(), 'sidebar-3' => array(), 'sidebar-4' => array(), 'sidebar-5' => array(), 'array_version' => 3 ),
            'avatar_default'         => 'identicon',
            'permalink_structure'    => '/%year%/%monthnum%/%postname%/',
            'timezone_string'        => ini_get('date.timezone'),
        ));

        foreach ($options as $option => $value) {
            update_option($option, $value);
        }
    }

    function add_default_category() {
        global $wpdb;

        // Default category
        $cat_name = __('Uncategorized');
        /* translators: Default category slug */
        $cat_slug = sanitize_title(_x('Uncategorized', 'Default category slug'));

        if ( global_terms_enabled() ) {
            $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_ID FROM {$wpdb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
            if ( $cat_id == null ) {
                $wpdb->insert( $wpdb->sitecategories, array('cat_ID' => 0, 'cat_name' => $cat_name, 'category_nicename' => $cat_slug, 'last_updated' => current_time('mysql', true)) );
                $cat_id = $wpdb->insert_id;
            }
            update_option('default_category', $cat_id);
        } else {
            $cat_id = 1;
        }

        $wpdb->insert( $wpdb->terms, array('term_id' => $cat_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0) );
        $wpdb->insert( $wpdb->term_taxonomy, array('term_id' => $cat_id, 'taxonomy' => 'category', 'description' => '', 'parent' => 0, 'count' => 1));
        $cat_tt_id = $wpdb->insert_id;

        return array(
            'id' => $cat_id,
            'slug' => $cat_slug,
            'name' => $cat_name,
            'tt_id' => $cat_tt_id,
        );
    }

    function add_default_link_category() {
        global $wpdb;

        // Default link category
        $cat_name = __('Blogroll');
        /* translators: Default link category slug */
        $cat_slug = sanitize_title(_x('Blogroll', 'Default link category slug'));

        if ( global_terms_enabled() ) {
            $blogroll_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_ID FROM {$wpdb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
            if ( $blogroll_id == null ) {
                $wpdb->insert( $wpdb->sitecategories, array('cat_ID' => 0, 'cat_name' => $cat_name, 'category_nicename' => $cat_slug, 'last_updated' => current_time('mysql', true)) );
                $blogroll_id = $wpdb->insert_id;
            }
            update_option('default_link_category', $blogroll_id);
        } else {
            $blogroll_id = 2;
        }

        $wpdb->insert( $wpdb->terms, array('term_id' => $blogroll_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0) );
        $wpdb->insert( $wpdb->term_taxonomy, array('term_id' => $blogroll_id, 'taxonomy' => 'link_category', 'description' => '', 'parent' => 0, 'count' => 7));
        $blogroll_tt_id = $wpdb->insert_id;

        return array(
            'id' => $blogroll_id,
            'slug' => $cat_slug,
            'name' => $cat_name,
            'tt_id' => $blogroll_tt_id,
        );
    }

    function add_default_links($category) {
        global $wpdb;

        $default_links = array();
        $default_links[] = array(   'link_url' => __( 'http://www.madeicitte.ca/' ),
                                    'link_name' => __( 'Made Icitte' ),
                                    'link_rss' => '',
                                    'link_notes' =>'');

        $default_links[] = array(   'link_url' => __( 'http://codex.wordpress.org/' ),
                                    'link_name' => __( 'Documentation' ),
                                    'link_rss' => '',
                                    'link_notes' => '');

        $default_links[] = array(   'link_url' => __( 'http://wordpress.org/news/' ),
                                    'link_name' => __( 'WordPress Blog' ),
                                    'link_rss' => __( 'http://wordpress.org/news/feed/' ),
                                    'link_notes' => '');

        $default_links[] = array(   'link_url' => __( 'http://wordpress.org/support/' ),
                                    'link_name' => _x( 'Support Forums', 'default link' ),
                                    'link_rss' => '',
                                    'link_notes' =>'');

        $default_links[] = array(   'link_url' => 'http://wordpress.org/extend/plugins/',
                                    'link_name' => _x( 'Plugins', 'Default link to wordpress.org/extend/plugins/' ),
                                    'link_rss' => '',
                                    'link_notes' =>'');

        $default_links[] = array(   'link_url' => 'http://wordpress.org/extend/themes/',
                                    'link_name' => _x( 'Themes', 'Default link to wordpress.org/extend/themes/' ),
                                    'link_rss' => '',
                                    'link_notes' =>'');

        $default_links[] = array(   'link_url' => __( 'http://wordpress.org/support/forum/requests-and-feedback' ),
                                    'link_name' => __( 'Feedback' ),
                                    'link_rss' => '',
                                    'link_notes' =>'');

        foreach ( $default_links as $link ) {
            $wpdb->insert( $wpdb->links, $link);
            $wpdb->insert( $wpdb->term_relationships, array('term_taxonomy_id' => $category['tt_id'], 'object_id' => $wpdb->insert_id) );
        }
    }

    function get_lorem(array $options = array()) {
        $options = array_merge(array(
            'p' => 7, // number of paragraphs
            'l' => 'short', // paragraph length: short, medium, long, verylong
            'd' => 1, // Add <b> and <i> tags
            'a' => 1, // Add <a>
            'co' => 0, // Add <code> and <pre>
            'ul' => 1, // Add <ul>
            'ol' => 1, // Add <ol>
            'dl' => 0, // Add <dl>
            'bq' => 1, // Add <blockquote>
            'h' => 1, // Add <h1> through <h6>
            'ac' => 0, // Everything in ALL CAPS
            'pr' => 1, // Remove certain words like 'sex' or 'homo'
        ), $options);
        $lorem_url = 'http://loripsum.net/generate.php?' . http_build_query($options);

        $lorem = file_get_contents($lorem_url);
        $lorem = str_replace("href='http://loripsum.net/' target='_blank'", 'href="#"', $lorem);
        $lorem = preg_replace('/<(\/?)h1>/', '<$1b>', $lorem);
        $lorem = preg_replace('/<(\/?)mark>/', '', $lorem);
        $lorem = preg_replace('/<\/p>/s', "</p>\n<!--more-->", $lorem, 1);
        return $lorem;
    }

    function insert_post($user_id, $id, $title, $body, $type = 'post', $comments = 0) {
        global $wpdb;
        $now     = date('Y-m-d H:i:s');
        $now_gmt = gmdate('Y-m-d H:i:s');
        $guid    = get_option('home') . '/?p=' . $id;

        $wpdb->insert($wpdb->posts, apply_filters('wp_insert_post_data', array(
            'post_author'           => $user_id,
            'post_date'             => $now,
            'post_date_gmt'         => $now_gmt,
            'post_content'          => apply_filters('content_save_pre', $body),
            'post_excerpt'          => '',
            'post_title'            => apply_filters('title_save_pre', $title),
            'post_name'             => apply_filters('name_save_pre', sanitize_title($title)),
            'post_modified'         => $now,
            'post_modified_gmt'     => $now_gmt,
            'guid'                  => $guid,
            'comment_count'         => $comments,
            'comment_status'        => get_option('default_ping_status'),
            'post_type'             => $type,
            'to_ping'               => '',
            'pinged'                => '',
            'post_content_filtered' => ''
        )));

        $post_id = $wpdb->insert_id;

        for ($i=0; $i < $comments; $i++) { 
            $comment = get_lorem(array('p' => 2, 'h' => 0, 'bq' => 0));
            insert_comment($post_id, $comment);
        }

        return $post_id;
    }

    function insert_comment($post_id, $comment) {
        global $wpdb;
        $author  = __('Mr WordPress');
        $url     = '#';
        $now     = date('Y-m-d H:i:s');
        $now_gmt = gmdate('Y-m-d H:i:s');

        $wpdb->insert($wpdb->comments, apply_filters('preprocess_comment', array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $author,
            'comment_author_email' => substr(md5(microtime()), 0, 10) . '@example.com',
            'comment_author_url'   => $url,
            'comment_date'         => $now,
            'comment_date_gmt'     => $now_gmt,
            'comment_content'      => apply_filters('pre_comment_content', $comment),
        )));

        $comment_id = $wpdb->insert_id;
        return $comment_id;
    }

    function add_default_posts($user_id) {
        global $wpdb;

        for ($i=1; $i <= 10; $i++) {
            $title = 'Post ' . $i;
            $body = get_lorem();
            $comments = rand(0, 1) == 0 ? 0 : rand(1, 5); // 50% chance of having 1-5 comments
            $post_id = insert_post($user_id, $i, $title, $body, 'post', $comments);
        }

        insert_post($user_id, 11, __('About'), get_lorem(), 'page');
        insert_post($user_id, 12, __('Portfolio'), get_lorem(), 'page');
        insert_post($user_id, 13, __('Contact us'), get_lorem(), 'page');
    }

    add_default_options();
    add_default_category();
    add_default_links(add_default_link_category());
    add_default_posts($user_id);

    if ( ! is_multisite() )
        update_user_meta( $user_id, 'show_welcome_panel', 1 );
    elseif ( ! is_super_admin( $user_id ) && ! metadata_exists( 'user', $user_id, 'show_welcome_panel' ) )
        update_user_meta( $user_id, 'show_welcome_panel', 2 );

    if ( is_multisite() ) {
        // Flush rules to pick up the new page.
        $wp_rewrite->init();
        $wp_rewrite->flush_rules();

        $user = new WP_User($user_id);
        $wpdb->update( $wpdb->options, array('option_value' => $user->user_email), array('option_name' => 'admin_email') );

        // Remove all perms except for the login user.
        $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'user_level') );
        $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'capabilities') );

        // Delete any caps that snuck into the previously active blog. (Hardcoded to blog 1 for now.) TODO: Get previous_blog_id.
        if ( !is_super_admin( $user_id ) && $user_id != 1 )
            $wpdb->delete( $wpdb->usermeta, array( 'user_id' => $user_id , 'meta_key' => $wpdb->base_prefix.'1_capabilities' ) );
    }

    do_action('default_install');
}
