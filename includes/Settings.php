<?php

namespace FTF_Fediverse_Embeds;

use FTF_Fediverse_Embeds\Database;

class Settings
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();

        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_filter('plugin_action_links_fediverse-embeds/index.php', array($this, 'settings_page_link'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'settings_page_link'));
    }

    function add_settings_page()
    {
        add_options_page(
            'Settings for the Fediverse Embeds plugin',
            'Fediverse Embeds',
            'manage_options',
            'ftf-fediverse-embeds',
            array($this, 'render_settings_page')
        );
    }

    function settings_init()
    {
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_custom_styles', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_include_bootstrap_styles', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_theme', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_archival_mode', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_show_metrics', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_show_post_labels', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_deleted_posts', 'esc_attr');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_data_refresh_minutes', 'esc_attr');

        add_settings_section(
            'ftf_fediverse_embeds_settings',
            __('', 'wordpress'),
            array($this, 'render_settings_form'),
            'ftf_fediverse_embeds'
        );
    }

    function render_settings_page()
    { ?>
        <div class="wrap">
            <h1>Fediverse Embeds</h1>

            <form action='options.php' method='post'>
                <?php
                settings_fields('ftf_fediverse_embeds');
                do_settings_sections('ftf_fediverse_embeds');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    function render_settings_form()
    {
        /* Customization */
        $theme = get_option('ftf_fediverse_embeds_theme');
        $archival_mode = get_option('ftf_fediverse_embeds_archival_mode');
        $include_bootstrap_styles = get_option('ftf_fediverse_embeds_include_bootstrap_styles', 'on');
        $show_metrics = get_option('ftf_fediverse_embeds_show_metrics', 'on');
        $show_post_labels = get_option('ftf_fediverse_embeds_show_post_labels', 'on');
        $deleted_posts = get_option('ftf_fediverse_embeds_deleted_posts', 'keep');
        $custom_styles = get_option('ftf_fediverse_embeds_custom_styles');
        $data_refresh_minutes = get_option('ftf_fediverse_embeds_data_refresh_minutes');

        if (empty($data_refresh_minutes)) {
            $data_refresh_minutes = 60;
        }
    ?>

        <h3 id="about">About the plugin</h3>
        <p>
            Directly embed fediverse posts on your WordPress site to improve the vistors' experience.
        </p>
        <p>
            The plugin will work automatically with the default embed code from fediverse platforms that provide it, which can be added as an <a href="https://wordpress.com/support/wordpress-editor/blocks/custom-html-block/" target="_blank">HTML block</a>.
        </p>
        <p>
            Here's an example for Mastodon. (Click the images to zoom in.)
        </p>


        <table style="width:840px" class="form-table" role="presentation">
            <tbody>
                <tr>
                    <td style="vertical-align: top;">
                        <a alt="A screenshot of a Mastodon post showing how to access the embed code." href="<?php echo esc_html(plugin_dir_url(__FILE__)); ?>../images/instructions/mastodon-embed.png" target="_blank">
                            <img style="max-width: 100%" src="<?php echo esc_html(plugin_dir_url(__FILE__)); ?>../images/instructions/mastodon-embed.png">
                        </a>

                    </td>
                    <td style="vertical-align: top;">
                        <a alt="A screenshot of a custom HTML WordPress block containing the embed code." href="<?php echo esc_html(plugin_dir_url(__FILE__)); ?>../images/instructions/add-html-iframe.png" target="_blank">
                            <img style="max-width: 100%" src="<?php echo esc_html(plugin_dir_url(__FILE__)); ?>../images/instructions/add-html-iframe.png">
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>

        <p>Please <a href="https://stefanbohacek.com/contact/">visit my contact page</a> if you have any questions or suggestions.</p>

        <p>
            <a class="button" href="https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/" target="_blank">Learn more</a>
            <a class="button" href="https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin" target="_blank">View source</a>
        </p>
        <h3 id="settings-customization">Customization</h3>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-theme">Theme</label>
                    </th>
                    <td>
                        <select id="select-id" name="ftf_fediverse_embeds_theme" id="ftf-fediverse-embeds-theme">
                            <option value="automatic" <?php selected($theme, 'automatic', true); ?>>Automatic</option>
                            <option value="light" <?php selected($theme, 'light', true); ?>>Light</option>
                            <option value="dark" <?php selected($theme, 'dark', true); ?>>Dark</option>
                        </select>
                        <p class="description">
                            Use an automatic, or always light or always dark theme.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-data_refresh_minutes">Data refresh frequency (in minutes)</label>
                    </th>
                    <td>
                        <input id="ftf-fediverse-embeds-data_refresh_minutes" type="number" min="5" name="ftf_fediverse_embeds_data_refresh_minutes" value="<?php echo esc_html($data_refresh_minutes); ?>" placeholder="5">
                        <p class="description">
                            How often should the post data be refreshed?
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-show-metrics">Show post metrics</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($show_metrics, 'on'); ?> name="ftf_fediverse_embeds_show_metrics" id="ftf-fediverse-embeds-show-metrics">
                        <p class="description">
                            Show the number of likes, boosts, and replies each post received.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-show-post-labels">Show post labels</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($show_post_labels, 'on'); ?> name="ftf_fediverse_embeds_show_post_labels" id="ftf-fediverse-embeds-show-post-labels">
                        <ul class="description">
                            <li>
                                Optional:
                                <ul>
                                    <li>👑 Owner (instance admin)</li>
                                </ul>
                            </li>
                            <li>
                                Always shown:
                                <ul>
                                    <li>🤖 Bot</li>
                                    <li>📝 Edited post</li>
                                    <li>🗑️ Deleted post</li>
                                </ul>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-show-metrics">Deleted posts</label>
                    </th>
                    <td>
                        <select id="select-id" name="ftf_fediverse_embeds_deleted_posts" id="ftf-fediverse-embeds-deleted-posts">
                            <option value="keep" <?php selected($deleted_posts, 'keep', true); ?>>Mark as deleted</option>
                            <option value="redact" <?php selected($deleted_posts, 'redact', true); ?>>Remove username and profile image</option>
                            <option value="hide" <?php selected($deleted_posts, 'hide', true); ?>>Hide</option>
                            <!-- <option value="delete" <?php selected($deleted_posts, 'delete', true); ?>>Delete from database</option> -->
                        </select>
                        <p class="description">
                            How do you want to handle deleted posts?
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-include-bootstrap-styles">Load necessary Bootstrap styles</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($include_bootstrap_styles, 'on'); ?> name="ftf_fediverse_embeds_include_bootstrap_styles" id="ftf-fediverse-embeds-include-bootstrap-styles">
                        <p class="description">
                            If you use the full non-customized version of <a href="https://getbootstrap.com/" target="_blank">Bootstrap 5.3.0</a> on your site, you can uncheck this. Otherwise a slimmed-down version of the Bootstrap CSS library will be loaded and only applied to the embedded posts.
                        </p>
                    </td>
                </tr>
                <!--
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-custom_styles">Additional CSS</label>
                    </th>
                    <td>
                        <textarea
                            id="ftf-fediverse-embeds-custom_styles"
                            name="ftf_fediverse_embeds_custom_styles"
                            rows="4"
                            cols="50"
                            style="font-family: monospace;"
                        ><?php echo esc_html($custom_styles); ?></textarea>
                        <p class="description">
                            Add additional CSS styles. <a href="https://jigsaw.w3.org/css-validator/#validate_by_input" target="_blank">Use the CSS validator</a> to make sure your CSS is valid.
                        </p>                        
                    </td>
                </tr>
-->
                <!-- <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-archival-mode">Archival Mode (Experimental)</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($archival_mode, 'on'); ?> name="ftf_fediverse_embeds_archival_mode" id="ftf-fediverse-embeds-archival-mode">
                        <p class="description">
                            Save data and images in case the original post is deleted. This feature is currently under active development.
                        </p>
                    </td>
                </tr> -->
            </tbody>
        </table>

        <h3 id="fediverse-posts-stats">Stats</h3>
        <?php
        $post_count = $this->db->get_post_count();

        $media_dir = plugin_dir_path(__FILE__) . "../media";
        $media_dir_size = Helpers::get_directory_size($media_dir)
        ?>
        <ul>
            <li>Number of saved posts: <?php echo esc_html(number_format($post_count)); ?></li>
            <li>Size of downloaded media files: <?php echo esc_html($media_dir_size); ?>
                <code>(<?php echo $media_dir; ?>)</code>
            </li>
        </ul>
<?php }

    function settings_page_link($links)
    {
        $url = esc_url(add_query_arg(
            'page',
            'ftf-fediverse-embeds',
            get_admin_url() . 'admin.php'
        ));
        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }
}
