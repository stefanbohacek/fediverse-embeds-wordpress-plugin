<?php

namespace FTF_Fediverse_Embeds;

class Settings
{
    function __construct()
    {
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_menu'), 10);
        add_action('admin_menu', array($this, 'add_advanced_submenu'), 30);
        add_action('admin_post_ftf_reset_allowlists', array($this, 'reset_allowlists'));
        add_filter('plugin_action_links_fediverse-embeds/index.php', array($this, 'settings_page_link'));
    }

    function add_menu()
    {
        add_menu_page(
            'Fediverse Embeds',
            'Fediverse Embeds',
            'manage_options',
            'ftf-fediverse-embeds',
            array($this, 'render_about_page'),
            'dashicons-excerpt-view',
            80
        );

        add_submenu_page(
            'ftf-fediverse-embeds',
            'About',
            'About',
            'manage_options',
            'ftf-fediverse-embeds',
            array($this, 'render_about_page')
        );

        add_submenu_page(
            'ftf-fediverse-embeds',
            'Settings',
            'Settings',
            'manage_options',
            'ftf-fediverse-embeds-settings',
            array($this, 'render_settings_page')
        );
    }

    function add_advanced_submenu()
    {
        if (defined('FTF_SHOW_ADVANCED_SETTINGS') && \FTF_SHOW_ADVANCED_SETTINGS) {
            add_submenu_page(
                'ftf-fediverse-embeds',
                'Advanced',
                'Advanced',
                'manage_options',
                'ftf-fediverse-embeds-advanced',
                array($this, 'render_advanced_page')
            );
        }
    }

    function settings_init()
    {
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_custom_styles', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_include_bootstrap_styles', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_theme', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_archival_mode', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_show_metrics', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_show_post_labels', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_deleted_posts', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_data_refresh_enabled', 'sanitize_text_field');
        register_setting('ftf_fediverse_embeds', 'ftf_fediverse_embeds_data_refresh_minutes', 'absint');

        add_settings_section(
            'ftf_fediverse_embeds_settings',
            '',
            array($this, 'render_settings_form'),
            'ftf_fediverse_embeds'
        );

        register_setting('ftf_fediverse_embeds_advanced', 'ftf_fediverse_embeds_allowed_domains', 'sanitize_textarea_field');
        register_setting('ftf_fediverse_embeds_advanced', 'ftf_fediverse_embeds_allowed_suffixes', 'sanitize_textarea_field');

        add_settings_section(
            'ftf_fediverse_embeds_advanced_settings',
            '',
            array($this, 'render_advanced_form'),
            'ftf_fediverse_embeds_advanced'
        );
    }

    function render_about_page()
    { ?>
        <div class="wrap">
            <h1>Fediverse Embeds &rsaquo; About the Plugin</h1>

            <p>
                This plugin lets you embed fediverse posts on your WordPress site while improving your site's performance and your visitors' experience.
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
                            <a alt="A screenshot of a Mastodon post showing how to access the embed code." href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>../images/instructions/mastodon-embed.png" target="_blank">
                                <img style="max-width: 100%" src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>../images/instructions/mastodon-embed.png">
                            </a>
                        </td>
                        <td style="vertical-align: top;">
                            <a alt="A screenshot of a custom HTML WordPress block containing the embed code." href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>../images/instructions/add-html-iframe.png" target="_blank">
                                <img style="max-width: 100%" src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>../images/instructions/add-html-iframe.png">
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p>Please <a href="https://stefanbohacek.com/contact/">visit my contact page</a> if you have any questions or suggestions.</p>

            <p>
                <a class="button button-primary" href="https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/" target="_blank">Learn more</a>
                <a class="button" href="https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin" target="_blank">View source</a>
            </p>
        </div>
    <?php }

    function render_settings_page()
    { ?>
        <div class="wrap">
            <h1>Fediverse Embeds &rsaquo; Settings</h1>

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
        $theme = get_option('ftf_fediverse_embeds_theme');
        $archival_mode = get_option('ftf_fediverse_embeds_archival_mode');
        $include_bootstrap_styles = get_option('ftf_fediverse_embeds_include_bootstrap_styles', 'on');
        $show_metrics = get_option('ftf_fediverse_embeds_show_metrics', 'on');
        $show_post_labels = get_option('ftf_fediverse_embeds_show_post_labels', 'on');
        $deleted_posts = get_option('ftf_fediverse_embeds_deleted_posts', 'keep');
        $custom_styles = get_option('ftf_fediverse_embeds_custom_styles');
        $data_refresh_enabled = get_option('ftf_fediverse_embeds_data_refresh_enabled', 'on');
        $data_refresh_minutes = get_option('ftf_fediverse_embeds_data_refresh_minutes');

        if (empty($data_refresh_minutes)) {
            $data_refresh_minutes = 60;
        }
    ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-theme">Theme</label>
                    </th>
                    <td>
                        <select name="ftf_fediverse_embeds_theme" id="ftf-fediverse-embeds-theme">
                            <option value="automatic" <?php selected($theme, 'automatic', true); ?>>Automatic</option>
                            <option value="light" <?php selected($theme, 'light', true); ?>>Light</option>
                            <option value="dark" <?php selected($theme, 'dark', true); ?>>Dark</option>
                        </select>
                        <p class="description">
                            Use an automatic, always light, or always dark theme.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-data_refresh_enabled">Enable data refresh</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($data_refresh_enabled, 'on'); ?> name="ftf_fediverse_embeds_data_refresh_enabled" id="ftf-fediverse-embeds-data_refresh_enabled">
                        <p class="description">
                            Periodically refresh embedded posts, including the number of likes and boosts.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-data_refresh_minutes">Data refresh frequency</label>
                    </th>
                    <td>
                        <input id="ftf-fediverse-embeds-data_refresh_minutes" type="number" min="5" name="ftf_fediverse_embeds_data_refresh_minutes" value="<?php echo esc_html($data_refresh_minutes); ?>" placeholder="5">
                        <p class="description">
                            How often (in minutes) should the post data be refreshed?
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
                        <label for="ftf-fediverse-embeds-deleted-posts">Deleted posts</label>
                    </th>
                    <td>
                        <select name="ftf_fediverse_embeds_deleted_posts" id="ftf-fediverse-embeds-deleted-posts">
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

<?php }

    function render_advanced_page()
    {
        $reset = isset($_GET['reset']) && $_GET['reset'] === '1';
        $saved_domains = get_option('ftf_fediverse_embeds_allowed_domains');
        $saved_suffixes = get_option('ftf_fediverse_embeds_allowed_suffixes');
        $domains_empty = $saved_domains !== false && trim($saved_domains) === '';
        $suffixes_empty = $saved_suffixes !== false && trim($saved_suffixes) === '';
        ?>
        <div class="wrap">
            <h1>Fediverse Embeds &rsaquo; Advanced</h1>

            <?php if ($reset): ?>
            <div class="notice notice-success is-dismissible">
                <p>Allowlists have been reset to defaults.</p>
            </div>
            <?php endif; ?>

            <?php if ($domains_empty || $suffixes_empty): ?>
            <div class="notice notice-info">
                <p>
                    <?php if ($domains_empty && $suffixes_empty): ?>
                        <strong>Note:</strong> Both allowlists are empty. Only media from automatically detected fediverse servers will be loaded; CDN and shared media hosting domains will be blocked.
                    <?php elseif ($domains_empty): ?>
                        <strong>Note:</strong> The allowed domains list is empty. Only suffixes and automatically detected fediverse servers will be used to allow media.
                    <?php else: ?>
                        <strong>Note:</strong> The allowed suffixes list is empty. Only exact domains and automatically detected fediverse servers will be used to allow media.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="notice notice-warning">
                <p><strong>Warning:</strong> Incorrect changes to these settings can introduce security vulnerabilities or break media loading. Only modify these settings if you know what you're doing.</p>
            </div>

            <form action='options.php' method='post'>
                <?php
                settings_fields('ftf_fediverse_embeds_advanced');
                do_settings_sections('ftf_fediverse_embeds_advanced');
                submit_button('Save Changes');
                ?>
            </form>

            <hr>

            <h2>Reset to Defaults</h2>
            <p>Restore the built-in default allowlists. Unlike saving empty fields (which blocks all CDN domains), this re-enables the default list of trusted domains and suffixes.</p>
            <form method='post' action='<?php echo esc_url(admin_url('admin-post.php')); ?>'>
                <input type='hidden' name='action' value='ftf_reset_allowlists'>
                <?php wp_nonce_field('ftf_reset_allowlists'); ?>
                <?php submit_button('Reset to Defaults', 'secondary', 'submit', false); ?>
            </form>
        </div>
        <?php
    }

    function render_advanced_form()
    {
        $default_domains = implode("\n", Helpers::get_default_allowed_domains());
        $default_suffixes = implode("\n", Helpers::get_default_allowed_suffixes());

        $allowed_domains = get_option('ftf_fediverse_embeds_allowed_domains', $default_domains);
        $allowed_suffixes = get_option('ftf_fediverse_embeds_allowed_suffixes', $default_suffixes);
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-allowed-domains">Allowed domains</label>
                    </th>
                    <td>
                        <textarea
                            id="ftf-fediverse-embeds-allowed-domains"
                            name="ftf_fediverse_embeds_allowed_domains"
                            rows="8"
                            cols="50"
                            style="font-family: monospace;"
                        ><?php echo esc_textarea($allowed_domains); ?></textarea>
                        <p class="description">
                            One domain per line, without protocol or path (e.g. <code>cdn.example.com</code>). These domains are always allowed to serve media.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-fediverse-embeds-allowed-suffixes">Allowed domain suffixes</label>
                    </th>
                    <td>
                        <textarea
                            id="ftf-fediverse-embeds-allowed-suffixes"
                            name="ftf_fediverse_embeds_allowed_suffixes"
                            rows="4"
                            cols="50"
                            style="font-family: monospace;"
                        ><?php echo esc_textarea($allowed_suffixes); ?></textarea>
                        <p class="description">
                            One suffix per line, starting with a dot (e.g. <code>.example.com</code>). Any domain ending with these suffixes is allowed to serve media.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    function reset_allowlists()
    {
        check_admin_referer('ftf_reset_allowlists');
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'fediverse-embeds'));
        }
        delete_option('ftf_fediverse_embeds_allowed_domains');
        delete_option('ftf_fediverse_embeds_allowed_suffixes');
        wp_redirect(add_query_arg(
            array('page' => 'ftf-fediverse-embeds-advanced', 'reset' => '1'),
            admin_url('admin.php')
        ));
        exit();
    }

    function settings_page_link($links)
    {
        $url = esc_url(add_query_arg(
            'page',
            'ftf-fediverse-embeds-settings',
            get_admin_url() . 'admin.php'
        ));
        $settings_link = "<a href='$url'>" . __('Settings', 'fediverse-embeds') . '</a>';
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }
}
