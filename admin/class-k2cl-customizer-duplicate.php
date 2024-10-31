<?php

/**
 * Duplicate customizer
 *
 * @link              http://k2-service.com/shop/product-customizer/
 * @author            K2-Service <plugins@k2-service.com>
 * @version           1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Admin_Duplicate_Customizer')) :

    /**
     * WC_Admin_Duplicate_Customizer Class
     */
    class WC_Admin_Duplicate_Customizer
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('admin_action_duplicate_customizer', [$this, 'clone_customizer_action']);
            add_filter('post_row_actions', [$this, 'show_clone_link'], 10, 2);
            add_filter('page_row_actions', [$this, 'show_clone_link'], 10, 2);
            add_action('post_submitbox_start', [$this, 'clone_button']);
        }

        /**
         * Show the "Duplicate" link in admin configurations list
         *
         * @param  array   $actions
         * @param  WP_Post $post Post object
         *
         * @return array
         */
        public function show_clone_link($actions, $post)
        {
            if (!current_user_can(apply_filters('woocommerce_duplicate_customizer_capability', 'manage_woocommerce'))) {
                return $actions;
            }

            if ($post->post_type != 'k2cl_customizer') {
                return $actions;
            }

            $actions['duplicate'] = '<a href="' .
                wp_nonce_url(admin_url('edit.php?post_type=k2cl_customizer&action=duplicate_customizer&amp;post=' .
                    $post->ID), 'woocommerce-duplicate-customizer_' . $post->ID) .
                '" title="' . __('Make a clone from this customizer', 'k2cl_customizer')
                . '" rel="permalink">' . __('Clone', 'k2cl_customizer') . '</a>';

            return $actions;
        }

        /**
         * Show the clone button
         */
        public function clone_button()
        {
            global $post;

            if (!current_user_can(apply_filters('woocommerce_duplicate_customizer_capability-config_capability', 'manage_woocommerce'))) {
                return;
            }

            if (!is_object($post)) {
                return;
            }

            if ($post->post_type != 'k2cl_customizer') {
                return;
            }

            if (isset($_GET['post'])) {
                $notifyUrl = wp_nonce_url(admin_url("edit.php?post_type=k2cl_customizer&action=duplicate_customizer&post=" .
                    absint($_GET['post'])), 'woocommerce-clone-customizer_' . $_GET['post']);
                ?>
                <div id="clone-action">
                    <a class="clone"
                       href="<?php echo esc_url($notifyUrl); ?>"><?php _e('Copy to a new draft', 'k2cl_customizer'); ?></a>
                </div>
                <?php
            }
        }

        /**
         * Clone customizer action.
         */
        public function clone_customizer_action()
        {

            if (empty($_REQUEST['post'])) {
                wp_die(__('No customizers to duplicate has been supplied!', 'k2cl_customizer'));
            }

            $id = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : '';
            check_admin_referer('woocommerce-duplicate-customizer_' . $id);

            $post = $this->get_customizer_to_clone($id);
            if (!empty($post)) {
                $new_id = $this->clone_customizer($post);

                do_action('woocommerce_duplicate_customizer', $new_id, $post);

                wp_redirect(admin_url('post.php?action=edit&post=' . $new_id));
                exit;
            } else {
                wp_die(__('Customizer clone creation failed, could not find original customizer:', 'k2cl_customizer') . ' ' . $id);
            }
        }

        /**
         * Function to create the clone of the customizer.
         *
         * @param mixed  $post
         * @param int    $parent      (default: 0)
         * @param string $post_status (default: '')
         *
         * @return int
         */
        public function clone_customizer($post, $parent = 0, $post_status = '')
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            $new_post_author = wp_get_current_user();
            $new_post_date = current_time('mysql');
            $new_post_date_gmt = get_gmt_from_date($new_post_date);

            if ($parent > 0) {
                $post_parent = $parent;
                $post_status = $post_status ? $post_status : 'publish';
                $suffix = '';
            } else {
                $post_parent = $post->post_parent;
                $post_status = $post_status ? $post_status : 'draft';
                $suffix = ' ' . __('(Clone) ', 'k2cl_customizer');
            }

            $wpdb->insert(
                $wpdb->posts,
                [
                    'post_author'           => $new_post_author->ID,
                    'post_date'             => $new_post_date,
                    'post_date_gmt'         => $new_post_date_gmt,
                    'post_content'          => $post->post_content,
                    'post_content_filtered' => $post->post_content_filtered,
                    'post_title'            => $post->post_title . $suffix,
                    'post_excerpt'          => $post->post_excerpt,
                    'post_status'           => $post_status,
                    'post_type'             => $post->post_type,
                    'comment_status'        => $post->comment_status,
                    'ping_status'           => $post->ping_status,
                    'post_password'         => $post->post_password,
                    'to_ping'               => $post->to_ping,
                    'pinged'                => $post->pinged,
                    'post_modified'         => $new_post_date,
                    'post_modified_gmt'     => $new_post_date_gmt,
                    'post_parent'           => $post_parent,
                    'menu_order'            => $post->menu_order,
                    'post_mime_type'        => $post->post_mime_type
                ]
            );

            $new_post_id = $wpdb->insert_id;

            // Clone the taxonomies
            $this->clone_customizer_taxonomies($post->ID, $new_post_id, $post->post_type);

            // Clone the meta information
            $this->clone_customizer_meta($post->ID, $new_post_id);

            return $new_post_id;
        }

        /**
         * Get a customizer from the database to clone
         *
         * @param mixed $id
         *
         * @return WP_Post|bool
         */
        private function get_customizer_to_clone($id)
        {
            global $wpdb;

            $id = absint($id);

            if (!$id) {
                return false;
            }

            $post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");

            if (isset($post->post_type) && $post->post_type == "revision") {
                $id = $post->post_parent;
                $post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");
            }

            return $post[0];
        }

        /**
         * Clone the taxonomies of a post to another post
         *
         * @param mixed $id
         * @param mixed $new_id
         * @param mixed $post_type
         */
        private function clone_customizer_taxonomies($id, $new_id, $post_type)
        {

            $taxonomies = get_object_taxonomies($post_type);

            foreach ($taxonomies as $taxonomy) {

                $post_terms = wp_get_object_terms($id, $taxonomy);
                $post_terms_count = sizeof($post_terms);

                for ($i = 0; $i < $post_terms_count; $i++) {
                    wp_set_object_terms($new_id, $post_terms[$i]->slug, $taxonomy, true);
                }
            }
        }

        /**
         * Clone the meta information of a post to another post
         *
         * @param mixed $id
         * @param mixed $new_id
         */
        private function clone_customizer_meta($id, $new_id)
        {
            global $wpdb;

            $post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d AND meta_key NOT IN ( 'total_sales' );", absint($id)));

            if (count($post_meta_infos) != 0) {

                $sql_query_sel = [];
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

                foreach ($post_meta_infos as $meta_info) {
                    $sql_query_sel[] = $wpdb->prepare("SELECT %d, %s, %s", $new_id, $meta_info->meta_key, $meta_info->meta_value);
                }

                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }
        }
    }
endif;

return new WC_Admin_Duplicate_Customizer();
