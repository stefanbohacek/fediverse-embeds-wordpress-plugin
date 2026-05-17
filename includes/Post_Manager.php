<?php

namespace FTF_Fediverse_Embeds;

use FTF_Fediverse_Embeds\Database;
use FTF_Fediverse_Embeds\Helpers;

if (is_admin() && !class_exists("WP_List_Table")) {
    require_once ABSPATH . "wp-admin/includes/class-wp-list-table.php";
}

class FTF_Posts_List_Table extends \WP_List_Table
{
    protected $db;

    function __construct($db)
    {
        parent::__construct([
            "singular" => "post",
            "plural"   => "posts",
            "ajax"     => false,
        ]);
        $this->db = $db;
    }

    function get_columns()
    {
        return [
            "cb"           => '<input type="checkbox">',
            "account"      => "Account",
            "content"      => "Content",
            "last_updated" => "Last Synced",
            "actions"      => "Actions",
        ];
    }

    function get_sortable_columns()
    {
        return [
            "last_updated" => ["last_updated", true],
        ];
    }

    protected function get_primary_column_name()
    {
        return "account";
    }

    function get_bulk_actions()
    {
        return ["delete" => "Delete"];
    }

    function column_cb($item)
    {
        return '<input type="checkbox" name="posts[]" value="' . esc_attr($item["instance"] . "|" . $item["post_id"]) . '">';
    }

    function column_account($item)
    {
        $post_data      = json_decode($item["post_data"], true);
        $account_url    = $post_data["account"]["url"] ?? ("https://" . $item["instance"]);
        $account_name   = $post_data["account"]["display_name"] ?? $post_data["account"]["username"] ?? "";
        $account_handle = $post_data["account"]["username"] ?? "";
        $avatar_url     = $post_data["account"]["avatar"] ?? "";

        if (!$account_handle) {
            return "&mdash;";
        }

        $html  = '<a href="' . esc_url($account_url) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;">';
        if ($avatar_url) {
            $html .= '<img src="' . esc_url($avatar_url) . '" width="32" height="32" style="border-radius:50%;flex-shrink:0;">';
        }
        $html .= esc_html($account_name ?: $account_handle);
        $html .= '</a>';
        $html .= '<br><small>@' . esc_html($account_handle . "@" . $item["instance"]) . '</small>';
        return $html;
    }

    function column_content($item)
    {
        $post_data    = json_decode($item["post_data"], true);
        $post_url     = $post_data["url"] ?? ("https://" . $item["instance"]);
        $content_html = $post_data["content"] ?? "";
        $content_text = wp_strip_all_tags($content_html);
        $excerpt      = mb_strimwidth($content_text, 0, 120, "...");
        $has_more     = mb_strlen($content_text) > 120;

        $html = "";
        if ($has_more) {
            $html .= '<details>';
            $html .= '<summary style="cursor:pointer"><em>' . esc_html($excerpt) . '</em></summary>';
            $html .= '<div style="margin-top:6px">' . wp_kses_post($content_html) . '</div>';
            $html .= '</details>';
        } else {
            $html .= esc_html($content_text ?: "—");
        }
        $html .= '<br><small><a href="' . esc_url($post_url) . '" target="_blank" rel="noopener noreferrer">View post</a></small>';
        return $html;
    }

    function column_last_updated($item)
    {
        return esc_html(date_i18n(get_option("date_format") . " " . get_option("time_format"), $item["last_updated"]));
    }

    function column_actions($item)
    {
        $refresh_url = wp_nonce_url(
            add_query_arg([
                "page"     => "ftf-fediverse-embeds-posts",
                "action"   => "refresh",
                "instance" => $item["instance"],
                "post_id"  => $item["post_id"],
            ], admin_url("admin.php")),
            "ftf_refresh_post"
        );

        $delete_url = wp_nonce_url(
            add_query_arg([
                "page"     => "ftf-fediverse-embeds-posts",
                "action"   => "delete",
                "instance" => $item["instance"],
                "post_id"  => $item["post_id"],
            ], admin_url("admin.php")),
            "ftf_delete_post"
        );

        $html  = '<a href="' . esc_url($refresh_url) . '" class="button button-small">Refresh</a>';
        $html .= '<a href="' . esc_url($delete_url) . '" class="button-link-delete" style="margin-left:6px" onclick="return confirm(\'' . esc_js("Remove this post from the database?") . '\')">Delete</a>';
        return $html;
    }

    function column_default($item, $column_name)
    {
        return "";
    }

    function prepare_items()
    {
        $per_page        = 20;
        $current_page    = $this->get_pagenum();
        $instance_filter = sanitize_text_field($_GET["instance_filter"] ?? "");

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
            $this->get_primary_column_name(),
        ];

        $this->items = $this->db->get_all_posts($current_page, $per_page, $instance_filter);
        $total_items = $this->db->get_post_count_filtered($instance_filter);

        $this->set_pagination_args([
            "total_items" => $total_items,
            "per_page"    => $per_page,
        ]);
    }
}

class Post_Manager
{
    protected $db;

    function __construct()
    {
        $this->db = new Database();
        add_action("admin_menu", array($this, "add_posts_page"));
        add_action("admin_init", array($this, "handle_actions"));
    }

    function add_posts_page()
    {
        add_submenu_page(
            "ftf-fediverse-embeds",
            "Manage Posts",
            "Manage Posts",
            "manage_options",
            "ftf-fediverse-embeds-posts",
            array($this, "render_posts_page")
        );
    }

    function handle_actions()
    {
        if (!current_user_can("manage_options")) {
            return;
        }

        $page = sanitize_text_field($_GET["page"] ?? "");
        if ($page !== "ftf-fediverse-embeds-posts") {
            return;
        }

        $action = sanitize_text_field($_GET["action"] ?? "");

        if ($action === "delete") {
            check_admin_referer("ftf_delete_post");
            $instance = sanitize_text_field($_GET["instance"] ?? "");
            $post_id  = absint($_GET["post_id"] ?? 0);

            if ($instance && $post_id) {
                $this->db->soft_delete_post($instance, $post_id);
            }

            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&deleted=1"));
            exit;
        }

        if ($action === "restore") {
            check_admin_referer("ftf_restore_post");
            $instance = sanitize_text_field($_GET["instance"] ?? "");
            $post_id  = absint($_GET["post_id"] ?? 0);

            if ($instance && $post_id) {
                $this->db->restore_post($instance, $post_id);
            }

            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&restored=1"));
            exit;
        }

        if ($action === "hard_delete") {
            check_admin_referer("ftf_hard_delete_post");
            $instance = sanitize_text_field($_GET["instance"] ?? "");
            $post_id  = absint($_GET["post_id"] ?? 0);

            if ($instance && $post_id) {
                $this->db->hard_delete_post($instance, $post_id);
            }

            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&hard_deleted=1"));
            exit;
        }

        if ($action === "refresh") {
            check_admin_referer("ftf_refresh_post");
            $instance = sanitize_text_field($_GET["instance"] ?? "");
            $post_id  = absint($_GET["post_id"] ?? 0);

            if ($instance && $post_id) {
                $this->refresh_post($instance, $post_id);
            }

            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&refreshed=1"));
            exit;
        }

        // WP_List_Table bulk actions use $_POST["action"] / $_POST["action2"]
        $bulk_action = sanitize_text_field($_POST["action"] ?? "");
        if ($bulk_action === "-1") {
            $bulk_action = sanitize_text_field($_POST["action2"] ?? "");
        }

        if ($bulk_action === "delete" && !empty($_POST["posts"])) {
            check_admin_referer("bulk-posts");
            $count = 0;

            foreach ((array) $_POST["posts"] as $post_key) {
                $parts = explode("|", sanitize_text_field($post_key));
                if (count($parts) === 2) {
                    $this->db->soft_delete_post($parts[0], absint($parts[1]));
                    $count++;
                }
            }

            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&deleted=" . (int) $count));
            exit;
        }

        if (isset($_POST["hard_delete_all_removed"])) {
            check_admin_referer("ftf_hard_delete_all_removed");
            $this->db->hard_delete_all_removed();
            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&hard_deleted_all=1"));
            exit;
        }

        if (isset($_POST["clear_media"])) {
            check_admin_referer("ftf_clear_media");
            $this->clear_media_directory();
            wp_redirect(admin_url("admin.php?page=ftf-fediverse-embeds-posts&media_cleared=1"));
            exit;
        }
    }

    protected function clear_media_directory()
    {
        $media_dir = dirname(plugin_dir_path(__FILE__)) . "/media";

        if (!is_dir($media_dir)) {
            return;
        }

        $files = glob(trailingslashit($media_dir) . "*");
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== ".htaccess") {
                    unlink($file);
                }
            }
        }

        delete_transient("ftf_dir_size_" . md5($media_dir));
    }

    protected function refresh_post($instance, $post_id)
    {
        global $wp_version;

        if (!filter_var($instance, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return;
        }

        $req_url = "https://" . $instance . "/api/v1/statuses/" . $post_id;

        if (!Helpers::is_safe_url($req_url)) {
            return;
        }

        $remote_response = wp_remote_get($req_url, array(
            "user-agent" => "FTF: Fediverse Embeds; WordPress/" . $wp_version . "; " . get_bloginfo("url"),
        ));

        if (is_wp_error($remote_response) || wp_remote_retrieve_response_code($remote_response) !== 200) {
            return;
        }

        $body = wp_remote_retrieve_body($remote_response);
        $data = json_decode($body, true);

        if (!empty($data) && !empty($data["id"]) && !empty($data["created_at"])) {
            $this->db->save_post($instance, $post_id, $body);
        }
    }

    function render_posts_page()
    {
        $instance_filter  = sanitize_text_field($_GET["instance_filter"] ?? "");
        $instances        = $this->db->get_instances();
        $removed_posts    = $this->db->get_removed_posts();
        $deleted          = intval($_GET["deleted"] ?? 0);
        $refreshed        = intval($_GET["refreshed"] ?? 0);
        $hard_deleted     = intval($_GET["hard_deleted"] ?? 0);
        $hard_deleted_all = intval($_GET["hard_deleted_all"] ?? 0);
        $restored         = intval($_GET["restored"] ?? 0);
        $media_cleared    = intval($_GET["media_cleared"] ?? 0);

        $table = new FTF_Posts_List_Table($this->db);
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h1>Fediverse Embeds &mdash; Manage Posts</h1>

            <?php if ($deleted): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($deleted === 1 ? "1 post removed." : "$deleted posts removed."); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($refreshed): ?>
            <div class="notice notice-success is-dismissible">
                <p>Post data refreshed.</p>
            </div>
            <?php endif; ?>

            <?php if ($hard_deleted): ?>
            <div class="notice notice-success is-dismissible">
                <p>Post permanently deleted.</p>
            </div>
            <?php endif; ?>

            <?php if ($hard_deleted_all): ?>
            <div class="notice notice-success is-dismissible">
                <p>All removed posts permanently deleted.</p>
            </div>
            <?php endif; ?>

            <?php if ($restored): ?>
            <div class="notice notice-success is-dismissible">
                <p>Post restored.</p>
            </div>
            <?php endif; ?>

            <?php if ($media_cleared): ?>
            <div class="notice notice-success is-dismissible">
                <p>Downloaded media files cleared.</p>
            </div>
            <?php endif; ?>

            <form method="get">
                <input type="hidden" name="page" value="ftf-fediverse-embeds-posts">
                <select name="instance_filter">
                    <option value="">All instances</option>
                    <?php foreach ($instances as $inst): ?>
                    <option value="<?php echo esc_attr($inst); ?>" <?php selected($instance_filter, $inst); ?>>
                        <?php echo esc_html($inst); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button("Filter", "secondary", "submit", false); ?>
                <?php if ($instance_filter): ?>
                <a class="button" href="<?php echo esc_url(admin_url("admin.php?page=ftf-fediverse-embeds-posts")); ?>">Clear</a>
                <?php endif; ?>
            </form>

            <form method="post">
                <input type="hidden" name="page" value="ftf-fediverse-embeds-posts">
                <?php $table->display(); ?>
            </form>

            <?php if (!empty($removed_posts)): ?>
            <h2>Removed Posts</h2>
            <p>These posts have been deleted and will not be re-fetched. If you have also removed them from any pages and articles, you can delete this information to free up some space.</p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Content</th>
                        <th>Last Synced</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($removed_posts as $post):
                        $post_data      = json_decode($post["post_data"], true);
                        $post_url       = $post_data["url"] ?? ("https://" . $post["instance"]);
                        $account_url    = $post_data["account"]["url"] ?? ("https://" . $post["instance"]);
                        $account_name   = $post_data["account"]["display_name"] ?? $post_data["account"]["username"] ?? "";
                        $account_handle = $post_data["account"]["username"] ?? "";
                        $avatar_url     = $post_data["account"]["avatar"] ?? "";
                        $last_updated   = date_i18n(get_option("date_format") . " " . get_option("time_format"), $post["last_updated"]);
                        $content_html   = $post_data["content"] ?? "";
                        $content_text   = wp_strip_all_tags($content_html);
                        $excerpt        = mb_strimwidth($content_text, 0, 120, "...");
                        $has_more       = mb_strlen($content_text) > 120;

                        $hard_delete_url = wp_nonce_url(
                            add_query_arg([
                                "page"     => "ftf-fediverse-embeds-posts",
                                "action"   => "hard_delete",
                                "instance" => $post["instance"],
                                "post_id"  => $post["post_id"],
                            ], admin_url("admin.php")),
                            "ftf_hard_delete_post"
                        );

                        $restore_url = wp_nonce_url(
                            add_query_arg([
                                "page"     => "ftf-fediverse-embeds-posts",
                                "action"   => "restore",
                                "instance" => $post["instance"],
                                "post_id"  => $post["post_id"],
                            ], admin_url("admin.php")),
                            "ftf_restore_post"
                        );
                    ?>
                    <tr style="opacity:0.65">
                        <td>
                            <?php if ($account_handle): ?>
                            <a href="<?php echo esc_url($account_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;">
                                <?php if ($avatar_url): ?>
                                <img src="<?php echo esc_url($avatar_url); ?>" width="32" height="32" style="border-radius:50%;flex-shrink:0;">
                                <?php endif; ?>
                                <?php echo esc_html($account_name ?: $account_handle); ?>
                            </a>
                            <br><small>@<?php echo esc_html($account_handle . "@" . $post["instance"]); ?></small>
                            <?php else: ?>
                            &mdash;
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($has_more): ?>
                            <details>
                                <summary style="cursor:pointer"><em><?php echo esc_html($excerpt); ?></em></summary>
                                <div style="margin-top:6px"><?php echo wp_kses_post($content_html); ?></div>
                            </details>
                            <?php else: ?>
                            <?php echo esc_html($content_text ?: "—"); ?>
                            <?php endif; ?>
                            <br><small><a href="<?php echo esc_url($post_url); ?>" target="_blank" rel="noopener noreferrer">View post</a></small>
                        </td>
                        <td><?php echo esc_html($last_updated); ?></td>
                        <td>
                            <a href="<?php echo esc_url($restore_url); ?>" class="button button-small">Restore</a>
                            <a href="<?php echo esc_url($hard_delete_url); ?>" class="button-link-delete" style="margin-left:6px" onclick="return confirm('<?php echo esc_js("Permanently delete this post?"); ?>')">Delete permanently</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br>
            <form method="post">
                <?php wp_nonce_field("ftf_hard_delete_all_removed"); ?>
                <input type="hidden" name="page" value="ftf-fediverse-embeds-posts">
                <button type="submit" name="hard_delete_all_removed" class="button-link-delete" onclick="return confirm('<?php echo esc_js("Permanently delete all removed posts?"); ?>')">
                    Delete all removed posts
                </button>
            </form>
            <?php endif; ?>

            <h2>Media Files</h2>
            <?php
            $media_dir      = dirname(plugin_dir_path(__FILE__)) . "/media";
            $media_dir_size = Helpers::get_directory_size($media_dir);
            $media_files    = array_filter(
                glob(trailingslashit($media_dir) . "*") ?: [],
                function ($f) { return is_file($f) && basename($f) !== ".htaccess"; }
            );
            ?>
            <p>
                Size of downloaded media files: <?php echo esc_html($media_dir_size); ?><br>
                <small>This information is cached for up to 5 minutes to improve performance.</small>
            </p>
            <?php if (!empty($media_files)): ?>
            <form method="post">
                <?php wp_nonce_field("ftf_clear_media"); ?>
                <input type="hidden" name="page" value="ftf-fediverse-embeds-posts">
                <button type="submit" name="clear_media" class="button-link-delete" onclick="return confirm('<?php echo esc_js("Delete all downloaded media files?"); ?>')">
                    Clear downloaded files
                </button>
            </form>
            <?php endif; ?>

        </div>
        <?php
    }
}
