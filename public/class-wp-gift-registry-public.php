<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://dreiqbik.de
 * @since      1.0.0
 *
 * @package    WP_Gift_Registry
 * @subpackage WP_Gift_Registry/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Gift_Registry
 * @subpackage WP_Gift_Registry/public
 * @author     Moritz Bappert <mb@dreiqbik.de>
 */

class WP_Gift_Registry_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in WPGiftRegistry_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WPGiftRegistry_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */


        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-gift-registry-public.css', array(), $this->version, 'all' );

        // new styles
        wp_enqueue_style( $this->plugin_name . '-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in WPGiftRegistry_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WPGiftRegistry_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-gift-registry-public.js', array( 'jquery' ), $this->version, true );

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( $this->plugin_name, 'variablesOld', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'updateGiftAvailabiltyNonce' => wp_create_nonce( 'gift-availability-81991' )
        ) );



        // new scripts
        wp_enqueue_script( $this->plugin_name . '-vendor', plugin_dir_url( __FILE__ ) . 'js/vendor/vendor.min.js', array( 'jquery' ), $this->version, true );

        wp_enqueue_script( $this->plugin_name . '-main', plugin_dir_url( __FILE__ ) . 'js/main.min.js', array( 'jquery' ), $this->version, true );

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( $this->plugin_name . '-main', 'variables', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'update_gift_availabilty_nonce' => wp_create_nonce( 'gift-availability-81991' )
        ) );
    }

    /**
     * Register the [wishlist] shortcode
     *
     * @since    1.0.0
     */
    public function create_wishlist_shortcode() {

        add_shortcode('wishlist', function( $atts = [] ) {

            // set attribute defaults
            $atts = shortcode_atts(
                array(
                    'id' => false, // false as default if no id parameter set
                ),
                $atts
            );

            ob_start();

            if ( $atts['id'] !== false ) {
                $currency = get_option('wpgr_settings')['currency_symbol'];
                $currency_placement = get_option('wpgr_settings')['currency_symbol_placement'];

                if ( $atts['id'] !== 'all' ) {
                    $wishlist = get_post_meta($atts['id'], 'wpgr_wishlist', true);

                    if ( !empty( $wishlist ) ) {

                        $wishlist_id = $atts['id'];
                        require( plugin_dir_path( __FILE__ ) . '/../templates/wishlist--single.php' );
                    }

                } else {

                    $all_wishlists = get_posts(array(
                        'fields'          => 'ids',
                        'posts_per_page'  => -1,
                        'post_type' => 'wpgr_wishlist'
                    ));

                    if ( !empty($all_wishlists) ) {
                        echo "<ul class='wpgr_wishlists'>";

                        foreach ( $all_wishlists as $wishlist_id ) {
                            $wishlist = get_post_meta($wishlist_id, 'wpgr_wishlist', true);
                            if ( !empty( $wishlist ) ) {

                                echo "<li><a href='" . get_permalink($wishlist_id) . "'>" . get_the_title($wishlist_id) . "</a></li>";

                                // echo "<h2>" . get_the_title($wishlist_id) . "</h2>";
                                // require( plugin_dir_path( __FILE__ ) . '/../templates/wishlist--single.php' );
                            }
                        }

                        echo "</ul>";
                    }
                }

            } else {
                // fallback for old plugin versions
                $currency = get_option('wishlist_settings')['currency_symbol'];
                $currency_placement = get_option('wishlist_settings')['currency_symbol_placement'];
                $wishlist = get_option('wishlist')['wishlist_group'];

                if ( !empty( $wishlist ) ) {
                    require( plugin_dir_path( __FILE__ ) . '/../templates/wishlist--single-old.php' );
                }
            }

            $output = ob_get_clean();
            return $output;

        });
    }


    /**
     * Integrate our wishlist into the page content of single wishlist pages
     * @since 1.4.4
     */
    public function filter_wishlist_content( $content ) {
        if ( is_singular('wpgr_wishlist') && !post_password_required() ) {
            $content = do_shortcode('[wishlist id="' . $GLOBALS['post']->ID . '"]');
        }
        return $content;
    }


    /**
     * Get the number of parts of a gift that are already reserved
     *
     * @since    1.0.0
     */
    public function get_reserved_parts( $wishlist_id, $gift_id ) {

        $reserved_gifts = get_post_meta($wishlist_id, 'wpgr_reserved_gifts', true);
        $gift = isset($reserved_gifts[$gift_id]) ? $reserved_gifts[$gift_id] : [];

        if ( isset($gift['gift_parts_reserved']) ) {
            return $gift['gift_parts_reserved'];
        }

        return 0;
    }


    /**
     * Updates the gift availability (called through AJAX)
     * @since    1.0.0
     */
    public function update_gift_availability() {

        $nonce = $_POST['nonce'];
        if ( !wp_verify_nonce( $nonce, 'gift-availability-81991' ) ) {
            die ( 'Busted!');
        }

        if ( $_POST['version'] == 'new' ) {
            // update new custom post type wishlists

            $wishlist_id 			= sanitize_key( $_POST['wishlist_id'] );
            $gift_id 				= sanitize_key( $_POST['gift_id'] );
            $gift_availability 		= wp_kses_data( $_POST['gift_availability'] );
            $gift_has_parts 		= wp_kses_data( $_POST['gift_has_parts'] );
            $gift_parts_reserved 	= sanitize_key( $_POST['gift_parts_reserved'] );
            $gift_reserver			= wp_kses_data( $_POST['gift_reserver'] );
            $gift_reserver_email	= is_email( $_POST['gift_reserver_email'] ) ? $_POST['gift_reserver_email'] : '';
            $gift_reserver_message	= wp_kses_data( $_POST['gift_reserver_message'] );

            $wishlist = get_post_meta($wishlist_id, 'wpgr_wishlist', true);
            $wishlist = !empty($wishlist) ? $wishlist : [];
            $to_be_updated = array_search($gift_id, array_column($wishlist, 'gift_id'));

            if ( $gift_has_parts == 'true' ) {
                $gift_availability = ((static::get_reserved_parts($wishlist_id, $gift_id) + $gift_parts_reserved != $wishlist[$to_be_updated]['gift_parts_total']) ? 'true' : 'false');
            }

            $wishlist[$to_be_updated]['gift_availability'] = $gift_availability;
            update_post_meta($wishlist_id, 'wpgr_wishlist', $wishlist);

            $reserved_gifts = get_post_meta($wishlist_id, 'wpgr_reserved_gifts', true);
            $reserved_gifts = !empty($reserved_gifts) ? $reserved_gifts : [];
            $reserved_parts = isset($reserved_gifts[$gift_id]['gift_parts_reserved']) ? $reserved_gifts[$gift_id]['gift_parts_reserved'] : 0;

            $reserved_gifts[$gift_id]['gift_id'] = $gift_id;
            $reserved_gifts[$gift_id]['gift_title'] = $wishlist[$to_be_updated]['gift_title'];
            $reserved_gifts[$gift_id]['gift_parts_reserved'] = $reserved_parts + $gift_parts_reserved;
            $reserved_gifts[$gift_id]['gift_parts_total'] = $wishlist[$to_be_updated]['gift_parts_total'];

            $reserved_gifts[$gift_id]['gift_reservations'][] = [
                'gift_reserver' 		=> $gift_reserver,
                'gift_parts'			=> $gift_parts_reserved,
                'gift_reserver_email' 	=> $gift_reserver_email,
                'gift_reserver_message' => $gift_reserver_message,
                'gift_reservation_date'	=> date('YmdHis'),
            ];
            update_post_meta($wishlist_id, 'wpgr_reserved_gifts', $reserved_gifts);

        } else {
            // update old wishlist type (managed through options page)

            $item_name = sanitize_title( $_POST['itemName'] );
            $item_availability = wp_kses_data( $_POST['availability'] );
            $options_array = get_option('wishlist');
            $wishlist = $options_array['wishlist_group'];

            foreach($wishlist as $gift_key => $gift) {
                if ( $gift['gift_title'] === $item_name ) {
                    $wishlist[$gift_key]['gift_availability'] = $item_availability;
                }
            }

            $options_array['wishlist_group'] = $wishlist;

            update_option('wishlist', $options_array);
        }

        die();
    }

    /**
     * Transforms Links into Affiliate Links
     * @since    1.0.0
     */
    public function transform_to_affiliate_link( $link ) {
        $link = htmlspecialchars( $link ); // This is the original unmodified link that is entered by the user.
        return $link;
    }

    function wpgr_encode_url($string) {
        $replacements = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $entities = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }

}
