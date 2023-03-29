<?php
/**
 * Plugin Name: Add Script To Specific Page
 * Description: Adds a custom field to the editor for inserting HTML code, like JS or CSS scripts, into the header or footer of a specific page.
 * Version: 1.1
 * Author: trueqap
 * Author URI: https://github.com/trueqap/add-script-to-specific-page
 * Text Domain: add-script-to-specific-page
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class Add_Script_To_Specific_Page
 */
class Add_Script_To_Specific_Page
{
    /**
     * Add_Script_To_Specific_Page constructor.
     */
    public function __construct()
    {
        add_action('init', array( $this, 'astsp_load_textdomain' ));
        add_action('add_meta_boxes', array( $this, 'astsp_add_meta_box' ));
        add_action('save_post', array( $this, 'astsp_save_meta_data' ));
        add_action('wp_head', array( $this, 'astsp_insert_code_head' ));
        add_action('wp_footer', array( $this, 'astsp_insert_code_footer' ));
    }

    /**
     * Load plugin textdomain.
     */
    public function astsp_load_textdomain()
    {
        load_plugin_textdomain('add-script-to-specific-page', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Add meta box to post types.
     */
    public function astsp_add_meta_box()
    {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            add_meta_box(
                'astsp_meta_box',
                __('Code JS/CSS', 'add-script-to-specific-page'),
                array( $this, 'astsp_meta_box_callback' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }

    /**
     * Meta box callback.
     *
     * @param WP_Post $post The post object.
     */
    public function astsp_meta_box_callback($post)
    {
        wp_nonce_field('astsp_save_meta_data', 'astsp_meta_box_nonce');
        $code = get_post_meta($post->ID, '_astsp_html', true);
        $location = get_post_meta($post->ID, '_astsp_location', true);

        echo '<label style="display:block;" for="astsp_location">' . __('Code location', 'add-script-to-specific-page') . '</label>';
        echo '<select name="astsp_location" id="astsp_location">';
        echo '<option value="header"' . selected($location, 'header', false) . '>' . __('Header', 'add-script-to-specific-page') . '</option>';
        echo '<option value="footer"' . selected($location, 'footer', false) . '>' . __('Footer', 'add-script-to-specific-page') . '</option>';
        echo '</select>';
        echo '<div style="display:block; margin-top:1rem;"><label for="astsp_textarea">' . __('HTML code:', 'add-script-to-specific-page') . '</label>';
        echo '<textarea style="width:100%;" id="astsp_textarea" name="astsp_textarea">' . esc_textarea($code) . '</textarea></div>';
    }

    /**
     * Save meta data.
     *
     * @param int $post_id The post ID.
     */
    public function astsp_save_meta_data($post_id)
    {
        if (! isset($_POST['astsp_meta_box_nonce']) || ! wp_verify_nonce($_POST['astsp_meta_box_nonce'], 'astsp_save_meta_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $html_code = $_POST['astsp_textarea'];
        update_post_meta($post_id, '_astsp_html', $html_code);

        $location = $_POST['astsp_location'];
        update_post_meta($post_id, '_astsp_location', $location);
    }

    /**
     * Insert code into head.
     */
    public function astsp_insert_code_head()
    {
        $this->astsp_insert_code('header');
    }

    /**
     * Insert code into footer.
     */
    public function astsp_insert_code_footer()
    {
        $this->astsp_insert_code('footer');
    }

    /**
     * Insert code into the specified location.
     *
     * @param string $location The location to insert the code.
     */
    private function astsp_insert_code($location)
    {
        global $post;
        if (isset($post->ID)) {
            $code = get_post_meta($post->ID, '_astsp_html', true);
            $code_location = get_post_meta($post->ID, '_astsp_location', true);
            if (! empty($code) && $location === $code_location) {
                echo $code;
            }
        }
    }
}

new Add_Script_To_Specific_Page();
