<?php

/**
 * W3 Total Cache Admin plugin
 */
if (!defined('W3TC')) {
    die();
}

define('W3TC_PLUGIN_TOTALCACHE_REGEXP_COOKIEDOMAIN', '~define\s*\(\s*[\'"]COOKIE_DOMAIN[\'"]\s*,.*?\)~is');

w3_require_once(W3TC_INC_DIR . '/functions/http.php');
w3_require_once(W3TC_INC_DIR . '/functions/rule.php');
w3_require_once(W3TC_LIB_W3_DIR . '/Plugin.php');

/**
 * Class W3_Plugin_TotalCacheAdmin
 */
class W3_Plugin_TotalCacheAdmin extends W3_Plugin {
    /**
     * Current page
     *
     * @var string
     */
    var $_page = 'w3tc_dashboard';

    /**
     * Notes
     *
     * @var array
     */
    var $_notes = array();

    /**
     * Errors
     *
     * @var array
     */
    var $_errors = array();

    /**
     * Rule-related errors about modifications in .htaccess
     *
     * @var array
     */
    var $_rule_errors = array();

    /**
     * Rule-related errors about modification of root config file
     * @var array
     */
    var $_rule_errors_root = array();

    /**
     * Link for hiding of root rules notification
     *
     * @var string
     */
    var $_rule_errors_root_hide = '';

    /**
     * If missing folder error
     * @var string
     */
    var $_use_ftp_form = false;

    /**
     * Link for auto-installing of rules
     *
     * @var string
     */
    var $_rule_errors_autoinstall = '';

    /**
     * Link for hiding of rules notification
     *
     * @var string
     */
    var $_rule_errors_hide = '';

    /**
     * Show support reminder flag
     *
     * @var boolean
     */
    var $_support_reminder = false;

    /**
     * Used in PHPMailer init function
     *
     * @var string
     */
    var $_phpmailer_sender = '';

    /**
     * Array of request types
     *
     * @var array
     */
    var $_request_types;

    /**
     * Array of request groups
     *
     * @var array
     */
    var $_request_groups = array(
        'Free Support' => array(
            'bug_report',
            'new_feature'
        ),
        'Premium Services (per site pricing)' => array(
            'email_support',
            'phone_support',
            'plugin_config',
            'theme_config',
            'linux_config'
        )
    );

    /**
     * Request price list
     *
     * @var array
     */
    var $_request_prices = array(
        'email_support' => 75,
        'phone_support' => 150,
        'plugin_config' => 100,
        'theme_config' => 150,
        'linux_config' => 200
    );

    /**
     * Admin configuration
     *
     * @var W3_ConfigAdmin
     */
    var $_config_admin;

    /**
     * Master configuration
     *
     * @var W3_Config
     */
    var $_config_master;

    /**
     * @var string WordPress FTP form
     */
    var $_ftp_form;

    var $_disable_cache_write_notification = false;
    var $_disable_add_in_files_notification = false;
    var $_disable_minify_error_notification = false;
    var $_disable_file_operation_notification = false;

    /**
     * Runs plugin
     *
     * @return void
     */
    function run() {
        $this->_request_types = array(
            'bug_report' => __('Submit a Bug Report', 'w3-total-cache'),
            'new_feature' => __('Suggest a New Feature', 'w3-total-cache'),
            'email_support' => __('Less than 15 Minute Email Support Response (M-F 9AM - 5PM EDT): $75 USD', 'w3-total-cache'),
            'phone_support' => __('Less than 15 Minute Phone Support Response (M-F 9AM - 5PM EDT): $150 USD', 'w3-total-cache'),
            'plugin_config' => __('Professional Plugin Configuration: Starting @ $100 USD', 'w3-total-cache'),
            'theme_config' => __('Theme Performance Optimization & Plugin Configuration: Starting @ $150 USD', 'w3-total-cache'),
            'linux_config' => __('Linux Server Optimization & Plugin Configuration: Starting @ $200 USD', 'w3-total-cache')
        );

        $this->_config_admin = w3_instance('W3_ConfigAdmin');

        add_action('admin_init', array(
            &$this,
            'admin_init'
        ));

        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_enqueue_scripts'));

        add_action('admin_head', array(
            &$this,
            'admin_head'
        ));
        
         // Trigger a config cache refresh when adding 'home'
        add_action('add_option_home', array(
             &$this,
             'refresh_config_cache',
        ));

        // Trigger a config cache refresh when updating 'home'
        add_action('update_option_home', array(
            &$this,
            'refresh_config_cache',
        ));

        if (is_network_admin()) {
            add_action('network_admin_menu', array(
                    &$this,
                    'admin_menu'
            ));
        } else {
            add_action('admin_menu', array(
                    &$this,
                    'admin_menu'
            ));
        }

        add_filter('contextual_help_list', array(
            &$this,
            'contextual_help_list'
        ));

        add_filter('plugin_action_links_' . W3TC_FILE, array(
            &$this,
            'plugin_action_links'
        ));

        add_filter('favorite_actions', array(
            &$this,
            'favorite_actions'
        ));

        add_action('in_plugin_update_message-' . W3TC_FILE, array(
            &$this,
            'in_plugin_update_message'
        ));

        if ($this->_config->get_boolean('pgcache.enabled') || $this->_config->get_boolean('minify.enabled')) {
            add_filter('pre_update_option_active_plugins', array(
                &$this,
                'pre_update_option_active_plugins'
            ));
        }

        if ($this->_config->get_boolean('cdn.enabled') && w3_can_cdn_purge($this->_config->get_string('cdn.engine'))) {
            add_filter('media_row_actions', array(
                &$this,
                'media_row_actions'
            ), 0, 2);
        }

        if ($this->_config->get_boolean('pgcache.enabled') || $this->_config->get_boolean('varnish.enabled') ||
            ($this->_config->get_boolean('cdn.enabled') && $this->_config->get_boolean('cdncache.enabled'))) {
            add_filter('post_row_actions', array(
                &$this,
                'post_row_actions'
            ), 0, 2);

            add_filter('page_row_actions', array(
                &$this,
                'page_row_actions'
            ), 0, 2);

            add_action('post_submitbox_start', array(
                &$this,
                'post_submitbox_start'
            ));
        }
    }

    /**
     * Load action
     *
     * @return void
     */
    function load() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $this->_page = W3_Request::get_string('page');

        switch (true) {
            case ($this->_page == 'w3tc_dashboard'):
            case ($this->_page == 'w3tc_general'):
            case ($this->_page == 'w3tc_pgcache'):
            case ($this->_page == 'w3tc_minify'):
            case ($this->_page == 'w3tc_dbcache'):
            case ($this->_page == 'w3tc_objectcache'):
            case ($this->_page == 'w3tc_fragmentcache'):
            case ($this->_page == 'w3tc_browsercache'):
            case ($this->_page == 'w3tc_mobile'):
            case ($this->_page == 'w3tc_referrer'):
            case ($this->_page == 'w3tc_cdn'):
            case ($this->_page == 'w3tc_monitoring'):
            case ($this->_page == 'w3tc_install'):
            case ($this->_page == 'w3tc_faq'):
            case ($this->_page == 'w3tc_about'):
            case ($this->_page == 'w3tc_support'):
                break;

            default:
                $this->_page = 'w3tc_dashboard';
        }

        $this->_support_reminder = ($this->_config->get_boolean('notes.support_us') && $this->_config_admin->get_integer('common.install') < (time() - W3TC_SUPPORT_US_TIMEOUT) && $this->_config->get_string('common.support') == '' && !$this->_config->get_boolean('common.tweeted'));

        /**
         * Run plugin action
         */
        $action = false;

        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, 'w3tc_') === 0) {
                $action = 'action_' . substr($key, 5);
                break;
            }
        }

        if ($action && method_exists($this, $action)) {
            if (strpos($action, 'view') !== false)
                if (!wp_verify_nonce(W3_Request::get_string('_wpnonce'), 'w3tc'))
                    wp_nonce_ays('w3tc');
            else
                check_admin_referer('w3tc');

            try {
                call_user_func(array(
                    &$this,
                    $action
                ));
            } catch (Exception $e) {
                $this->redirect_with_custom_messages(array(), array($e->getMessage()));
            }

            exit();
        }
    }

    /**
     * Admin init
     *
     * @return void
     */
    function admin_init() {
        if (function_exists('ats_register_plugin')) {
            // plugin registration
            ats_register_plugin('w3-total-cache', W3TC_FILE);

            // enable tickets module
            ats_enable_tickets('w3-total-cache',
                array(
                    'custom_fields' => array(
                        __('SSH / FTP host', 'w3-total-cache'),
                        __('SSH / FTP login', 'w3-total-cache'),
                        __('SSH / FTP password', 'w3-total-cache')
                    )
                )
            );
        }

        // special handling for deactivation link, it's plugins.php file
        if (W3_Request::get_string('action') == 'w3tc_deactivate_plugin')
            $this->action_deactivate_plugin();
    }

    function admin_enqueue_scripts() {
        wp_register_style('w3tc-options', plugins_url('pub/css/options.css', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_style('w3tc-lightbox', plugins_url('pub/css/lightbox.css', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_style('w3tc-widget', plugins_url('pub/css/widget.css', W3TC_FILE), array(), W3TC_VERSION);

        wp_register_script('w3tc-metadata', plugins_url('pub/js/metadata.js', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_script('w3tc-options', plugins_url('pub/js/options.js', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_script('w3tc-lightbox', plugins_url('pub/js/lightbox.js', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_script('w3tc-widget', plugins_url('pub/js/widget.js', W3TC_FILE), array(), W3TC_VERSION);
        wp_register_script('jquery-masonry', plugins_url('pub/js/jquery.masonry.min.js', W3TC_FILE), array('jquery'), W3TC_VERSION);
    }

// Define icon styles for the custom post type
    function admin_head() {

        if (isset($_GET['page']) && $_GET['page'] == 'w3tc_dashboard'): ?>
<script type="text/javascript">
    jQuery(function() {
        jQuery('#normal-sortables').masonry({
            itemSelector : '.postbox'
        });
    });
</script>
            <?php
        endif;
        ?>
    <style type="text/css" media="screen">
        #toplevel_page_w3tc_dashboard .wp-menu-image {
            background: url(<?php echo plugins_url('w3-total-cache/pub/img/w3tc-sprite.png')?>) no-repeat 0 -32px !important;
        }
        #toplevel_page_w3tc_dashboard:hover .wp-menu-image,
        #toplevel_page_w3tc_dashboard.wp-has-current-submenu .wp-menu-image {
            background-position:0 0 !important;
        }
        #icon-edit.icon32-posts-casestudy {
            background: url(<?php echo plugins_url('w3-total-cache/pub/img/w3tc-sprite.png') ?>) no-repeat;
        }
        /**
        * HiDPI Displays
        */
        @media print,
        (-o-min-device-pixel-ratio: 5/4),
        (-webkit-min-device-pixel-ratio: 1.25),
        (min-resolution: 120dpi) {
            
            #toplevel_page_w3tc_dashboard .wp-menu-image {
                background-image: url(<?php echo plugins_url('w3-total-cache/pub/img/w3tc-sprite-retina.png')?>) !important;
                background-size: 30px 64px !important;
            }
            #toplevel_page_w3tc_dashboard:hover .wp-menu-image,
            #toplevel_page_w3tc_dashboard.wp-has-current-submenu .wp-menu-image {
                background-position:0px 0px !important;
            }
            #icon-edit.icon32-posts-casestudy {
                background-image: url(<?php echo plugins_url('w3-total-cache/pub/img/w3tc-sprite-retina.png') ?>) !important;
                background-size: 30px 64px !important;
            }
        }
    </style>

    <?php }

    /**
     * Admin menu
     *
     * @return void
     */
    function admin_menu() {
        $pages = array(
            'w3tc_dashboard' => array(
                __('Dashboard', 'w3-total-cache'),
                __('Dashboard', 'w3-total-cache'),
                'network_show' => true
            ),
            'w3tc_general' => array(
                __('General Settings', 'w3-total-cache'),
                __('General Settings', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_pgcache' => array(
                __('Page Cache', 'w3-total-cache'),
                __('Page Cache', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_minify' => array(
                __('Minify', 'w3-total-cache'),
                __('Minify', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_dbcache' => array(
                __('Database Cache', 'w3-total-cache'),
                __('Database Cache', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_objectcache' => array(
                __('Object Cache', 'w3-total-cache'),
                __('Object Cache', 'w3-total-cache'),
                'network_show' => false
            )
        );
        if (w3_is_pro() || w3_is_enterprise()) {
            $pages['w3tc_fragmentcache'] = array(
                __('Fragment Cache', 'w3-total-cache'),
                __('Fragment Cache', 'w3-total-cache'),
                'network_show' => false
            );
        }
        $pages = array_merge($pages, array(
            'w3tc_browsercache' => array(
                __('Browser Cache', 'w3-total-cache'),
                __('Browser Cache', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_mobile' => array(
                __('User Agent Groups', 'w3-total-cache'),
                __('User Agent Groups', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_referrer' => array(
                __('Referrer Groups', 'w3-total-cache'),
                __('Referrer Groups', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_cdn' => array(
                __('Content Delivery Network', 'w3-total-cache'),
                __('<acronym title="Content Delivery Network">CDN</acronym>', 'w3-total-cache'),
                'network_show' => $this->_config->get_boolean('cdn.enabled')
            ),
            'w3tc_monitoring' => array(
                __('Monitoring', 'w3-total-cache'),
                __('Monitoring', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_faq' => array(
                __('FAQ', 'w3-total-cache'),
                __('FAQ', 'w3-total-cache'),
                'network_show' => true
            ),
            'w3tc_support' => array(
                __('Support', 'w3-total-cache'),
                __('<span style="color: red;">Support</span>', 'w3-total-cache'),
                'network_show' => true
            ),
            'w3tc_install' => array(
                __('Install', 'w3-total-cache'),
                __('Install', 'w3-total-cache'),
                'network_show' => false
            ),
            'w3tc_about' => array(
                __('About', 'w3-total-cache'),
                __('About', 'w3-total-cache'),
                'network_show' => true
            )
        ));

        add_menu_page(__('Performance', 'w3-total-cache'), __('Performance', 'w3-total-cache'), 'manage_options', 'w3tc_dashboard', '', 'div');

        $submenu_pages = array();

        foreach ($pages as $slug => $titles) {
            if (($this->_config_admin->get_boolean('common.visible_by_master_only') && $titles['network_show']) ||
                    (!$this->_config_admin->get_boolean('common.visible_by_master_only') ||
                        (is_super_admin() && (!w3_force_master() || is_network_admin())))
                ) {
                $submenu_pages[] = add_submenu_page('w3tc_dashboard', $titles[0] . ' | W3 Total Cache', $titles[1], 'manage_options', $slug, array(
                    &$this,
                    'options'
                ));
            }
        }

        if (current_user_can('manage_options')) {
            /**
             * Only admin can modify W3TC settings
             */
            foreach ($submenu_pages as $submenu_page) {
                add_action('load-' . $submenu_page, array(
                    &$this,
                    'load'
                ));

                add_action('admin_print_styles-' . $submenu_page, array(
                    &$this,
                    'admin_print_styles'
                ));

                add_action('admin_print_scripts-' . $submenu_page, array(
                    &$this,
                    'admin_print_scripts'
                ));
            }

            global $pagenow;
            if ($pagenow == 'plugins.php') {
                add_action('admin_print_scripts', array($this, 'load_plugins_page_js'));
                add_action('admin_print_styles', array($this, 'print_plugins_page_css'));
            }
            /**
             * Only admin can see W3TC notices and errors
             */
            add_action('admin_notices', array(
                &$this,
                'admin_notices'
            ));
            add_action('network_admin_notices', array(
                &$this,
                'admin_notices'
            ));
        }
    }
    
    /**
     * add_option_home and update_option_home hook
     * We trigger a config cache refresh, to make sure we always have the latest value of 'home' in 
     * the config cache.
     * 
     * @return void
     **/
    function refresh_config_cache() {
        $this->_config->refresh_cache();
    }

    /**
     * Print styles
     *
     * @return void
     */
    function admin_print_styles() {
        wp_enqueue_style('w3tc-options');
        wp_enqueue_style('w3tc-lightbox');
    }

    /**
     * Print scripts
     *
     * @return void
     */
    function admin_print_scripts() {
        wp_enqueue_script('w3tc-metadata');
        wp_enqueue_script('w3tc-options');
        wp_enqueue_script('w3tc-lightbox');

        switch ($this->_page) {
            case 'w3tc_minify':
            case 'w3tc_mobile':
            case 'w3tc_referrer':
            case 'w3tc_cdn':
                wp_enqueue_script('jquery-ui-sortable');
                break;
        }
        if($this->_page=='w3tc_cdn')
            wp_enqueue_script('jquery-ui-dialog');
        if($this->_page=='w3tc_dashboard')
            wp_enqueue_script('jquery-masonry');
    }


    function load_plugins_page_js() {
        wp_enqueue_script('w3tc-options');
    }

    function print_plugins_page_css() {
        echo "<style type=\"text/css\">.w3tc-missing-files ul {
                margin-left: 20px;
                list-style-type: disc;
              }
              #w3tc {
              padding: 0;
              }
              #w3tc span {
    font-size: 0.6em;
    font-style: normal;
    text-shadow: none;
}
ul.w3tc-incomp-plugins, ul.w3-bullet-list {
    list-style: disc outside;
    margin-left: 17px;
    margin-top: 0;
    margin-bottom: 0;
}
ul.w3tc-incomp-plugins li div{
    width: 170px;
    display: inline-block;
}
              </style>";
    }

    /**
     * Contextual help list filter
     *
     * @param string $list
     * @return string
     */
    function contextual_help_list($list) {
        w3_require_once(W3TC_INC_FUNCTIONS_DIR . '/other.php');
        $faq = w3_parse_faq();

        if (isset($faq['Usage'])) {
            $columns = array_chunk($faq['Usage'], ceil(count($faq['Usage']) / 3));

            ob_start();
            include W3TC_INC_DIR . '/options/common/help.php';
            $help = ob_get_contents();
            ob_end_clean();

            $hook = get_plugin_page_hookname($this->_page, 'w3tc_dashboard');

            $list[$hook] = $help;
        }

        return $list;
    }

    /**
     * Plugin action links filter
     *
     * @param array $links
     * @return array
     */
    function plugin_action_links($links) {
        array_unshift($links, 
            '<a class="edit" href="admin.php?page=w3tc_general">Settings</a>');

        w3_require_once(W3TC_INC_DIR . '/functions/rule.php');
        if (!is_writable(WP_CONTENT_DIR) || 
            !is_writable(w3_get_browsercache_rules_cache_path())) {
            $delete_link = '<a href="' . 
                wp_nonce_url(admin_url('plugins.php?action=w3tc_deactivate_plugin'), 'w3tc') .
                '">Uninstall</a>';
            array_unshift($links, $delete_link);
        }

        return $links;
    }

    /**
     * favorite_actions filter
     *
     * @param array $actions
     * @return array
     */
    function favorite_actions($actions) {
        $actions[wp_nonce_url(admin_url('admin.php?page=w3tc_dashboard&amp;w3tc_flush_all'), 'w3tc')] = array(
            __('Empty Caches', 'w3-total-cache'),
            'manage_options'
        );

        return $actions;
    }

    /**
     * Active plugins pre update option filter
     *
     * @param string $new_value
     * @return string
     */
    function pre_update_option_active_plugins($new_value) {
        $old_value = (array) get_option('active_plugins');

        if ($new_value !== $old_value && in_array(W3TC_FILE, (array) $new_value) && in_array(W3TC_FILE, (array) $old_value)) {
                $this->_config->set('notes.plugins_updated', true);
                try {
                    $this->_config->save();
                } catch(Exception $ex) {}
        }

        return $new_value;
    }

    /**
     * Show plugin changes
     *
     * @return void
     */
    function in_plugin_update_message() {
        $response = w3_http_get(W3TC_README_URL);

        if (!is_wp_error($response) && $response['response']['code'] == 200) {
            $matches = null;
            $regexp = '~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*' . preg_quote(W3TC_VERSION) . '\s*=|$)~Uis';

            if (preg_match($regexp, $response['body'], $matches)) {
                $changelog = (array) preg_split('~[\r\n]+~', trim($matches[1]));

                echo '<div style="color: #f00;">' . __('Take a minute to update, here\'s why:', 'w3-total-cache') . '</div><div style="font-weight: normal;">';
                $ul = false;

                foreach ($changelog as $index => $line) {
                    if (preg_match('~^\s*\*\s*~', $line)) {
                        if (!$ul) {
                            echo '<ul style="list-style: disc; margin-left: 20px;">';
                            $ul = true;
                        }
                        $line = preg_replace('~^\s*\*\s*~', '', htmlspecialchars($line));
                        echo '<li style="width: 50%; margin: 0; float: left; ' . ($index % 2 == 0 ? 'clear: left;' : '') . '">' . $line . '</li>';
                    } else {
                        if ($ul) {
                            echo '</ul><div style="clear: left;"></div>';
                            $ul = false;
                        }
                        echo '<p style="margin: 5px 0;">' . htmlspecialchars($line) . '</p>';
                    }
                }

                if ($ul) {
                    echo '</ul><div style="clear: left;"></div>';
                }

                echo '</div>';
            }
        }
    }

    /**
     * media_row_actions filter
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    function media_row_actions($actions, $post) {
        $actions = array_merge($actions, array(
            'cdn_purge' => sprintf('<a href="%s">' . __('Purge from CDN', 'w3-total-cache') . '</a>', wp_nonce_url(sprintf('admin.php?page=w3tc_dashboard&w3tc_cdn_purge_attachment&attachment_id=%d', $post->ID), 'w3tc'))
        ));

        return $actions;
    }

    /**
     * post_row_actions filter
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    function post_row_actions($actions, $post) {
        if (current_user_can('manage_options'))
            $actions = array_merge($actions, array(
                'pgcache_purge' => sprintf('<a href="%s">' . __('Purge from cache', 'w3-total-cache') . '</a>', wp_nonce_url(sprintf('admin.php?page=w3tc_dashboard&w3tc_pgcache_purge_post&post_id=%d', $post->ID), 'w3tc'))
            ));

        return $actions;
    }

    /**
     * page_row_actions filter
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    function page_row_actions($actions, $post) {
        if (current_user_can('manage_options')) {
            $actions = array_merge($actions, array(
                'pgcache_purge' => sprintf('<a href="%s">' . __('Purge from cache', 'w3-total-cache') . '</a>', wp_nonce_url(sprintf('admin.php?page=w3tc_dashboard&w3tc_pgcache_purge_page&post_id=%d', $post->ID), 'w3tc'))
            ));
        }
        return $actions;
    }

    /**
     * Display Purge from cache on Page/Post post.php.
     */
    function post_submitbox_start() {
        if (current_user_can('manage_options'))  {
            global $post;
            echo '<div>', sprintf('<a href="%s">' . __('Purge from cache', 'w3-total-cache') . '</a>', wp_nonce_url(sprintf('admin.php?page=w3tc_dashboard&w3tc_pgcache_purge_page&post_id=%d', $post->ID), 'w3tc')), '</div>';
        }
    }

    /**
     * Admin notices action
     *
     * @return void
     */
    function admin_notices() {
        $cookie_domain = $this->get_cookie_domain();

        $error_messages = array(
            'fancy_permalinks_disabled_pgcache' => sprintf(__('Fancy permalinks are disabled. Please %s it first, then re-attempt to enabling enhanced disk mode.', 'w3-total-cache'), $this->button_link('enable', 'options-permalink.php')),
            'fancy_permalinks_disabled_browsercache' => sprintf(__('Fancy permalinks are disabled. Please %s it first, then re-attempt to enabling the \'Do not process 404 errors for static objects with WordPress\'.', 'w3-total-cache'), $this->button_link('enable', 'options-permalink.php')),
            'support_request_type' => __('Please select request type.', 'w3-total-cache'),
            'support_request_url' => __('Please enter the address of the site in the site <acronym title="Uniform Resource Locator">URL</acronym> field.', 'w3-total-cache'),
            'support_request_name' => __('Please enter your name in the Name field', 'w3-total-cache'),
            'support_request_email' => __('Please enter valid email address in the E-Mail field.', 'w3-total-cache'),
            'support_request_phone' => __('Please enter your phone in the phone field.', 'w3-total-cache'),
            'support_request_subject' => __('Please enter subject in the subject field.', 'w3-total-cache'),
            'support_request_description' => __('Please describe the issue in the issue description field.', 'w3-total-cache'),
            'support_request_wp_login' => __('Please enter an administrator login. Create a temporary one just for this support case if needed.', 'w3-total-cache'),
            'support_request_wp_password' => __('Please enter WP Admin password, be sure it\'s spelled correctly.', 'w3-total-cache'),
            'support_request_ftp_host' => __('Please enter <acronym title="Secure Shell">SSH</acronym> or <acronym title="File Transfer Protocol">FTP</acronym> host for the site.', 'w3-total-cache'),
            'support_request_ftp_login' => __('Please enter <acronym title="Secure Shell">SSH</acronym> or <acronym title="File Transfer Protocol">FTP</acronym> login for the server. Create a temporary one just for this support case if needed.', 'w3-total-cache'),
            'support_request_ftp_password' => __('Please enter <acronym title="Secure Shell">SSH</acronym> or <acronym title="File Transfer Protocol">FTP</acronym> password for the <acronym title="File Transfer Protocol">FTP</acronym> account.', 'w3-total-cache'),
            'support_request' => __('Unable to send the support request.', 'w3-total-cache'),
            'config_import_no_file' => __('Please select config file.', 'w3-total-cache'),
            'config_import_upload' => __('Unable to upload config file.', 'w3-total-cache'),
            'config_import_import' => __('Configuration file could not be imported.', 'w3-total-cache'),
            'config_reset' => sprintf(__('Default settings could not be restored. Please run <strong>chmod 777 %s</strong> to make the configuration file write-able, then try again.', 'w3-total-cache'), W3TC_CONFIG_DIR),
            'cdn_purge_attachment' => __('Unable to purge attachment.', 'w3-total-cache'),
            'pgcache_purge_post' => __('Unable to purge post.', 'w3-total-cache'),
            'pgcache_purge_page' => __('Unable to purge page.', 'w3-total-cache'),
            'enable_cookie_domain' => sprintf(__('<strong>%swp-config.php</strong> could not be written, please edit config and add:<br /><strong style="color:#f00;">define(\'COOKIE_DOMAIN\', \'%s\');</strong> before <strong style="color:#f00;">require_once(ABSPATH . \'wp-settings.php\');</strong>.', 'w3-total-cache'), ABSPATH, addslashes($cookie_domain)),
            'disable_cookie_domain' => sprintf(__('<strong>%swp-config.php</strong> could not be written, please edit config and add:<br /><strong style="color:#f00;">define(\'COOKIE_DOMAIN\', false);</strong> before <strong style="color:#f00;">require_once(ABSPATH . \'wp-settings.php\');</strong>.', 'w3-total-cache'), ABSPATH),
            'cloudflare_api_request' => __('Unable to make CloudFlare API request.', 'w3-total-cache'),
        );

        $note_messages = array(
            'config_save' => __('Plugin configuration successfully updated.', 'w3-total-cache'),
            'flush_all' => __('All caches successfully emptied.', 'w3-total-cache'),
            'flush_all_except_cf' => __('All caches except CloudFlare successfully emptied.', 'w3-total-cache'),
            'flush_memcached' => __('Memcached cache(s) successfully emptied.', 'w3-total-cache'),
            'flush_opcode' => __('Opcode cache(s) successfully emptied.', 'w3-total-cache'),
            'flush_apc_system' => __('APC system cache successfully emptied', 'w3-total-cache'),
            'flush_file' => __('Disk cache(s) successfully emptied.', 'w3-total-cache'),
            'flush_pgcache' => __('Page cache successfully emptied.', 'w3-total-cache'),
            'flush_dbcache' => __('Database cache successfully emptied.', 'w3-total-cache'),
            'flush_objectcache' => __('Object cache successfully emptied.', 'w3-total-cache'),
            'flush_fragmentcache' => __('Fragment cache successfully emptied.', 'w3-total-cache'),
            'flush_minify' => __('Minify cache successfully emptied.', 'w3-total-cache'),
            'flush_browser_cache' => __('Media Query string has been successfully updated.', 'w3-total-cache'),
            'flush_varnish' => __('Varnish servers successfully purged.', 'w3-total-cache'),
            'flush_cdn' => __('CDN was successfully purged.', 'w3-total-cache'),
            'support_request' => __('The support request has been successfully sent.', 'w3-total-cache'),
            'config_import' => __('Settings successfully imported.', 'w3-total-cache'),
            'config_reset' => __('Settings successfully restored.', 'w3-total-cache'),
            'preview_enable' => __('Preview mode was successfully enabled', 'w3-total-cache'),
            'preview_disable' => __('Preview mode was successfully disabled', 'w3-total-cache'),
            'preview_deploy' => __('Preview settings successfully deployed. Preview mode remains enabled until it\'s disabled. Continue testing new settings or disable preview mode if done.', 'w3-total-cache'),
            'cdn_purge_attachment' => __('Attachment successfully purged.', 'w3-total-cache'),
            'pgcache_purge_post' => __('Post successfully purged.', 'w3-total-cache'),
            'pgcache_purge_page' => __('Page successfully purged.', 'w3-total-cache'),
            'new_relic_save' => __('New relic settings have been updated.', 'w3-total-cache')
        );

        $errors = array();
        $notes = array();
        $environment_error_present = false;

        // print errors which happened during current request execution
        foreach ($this->_errors as $error)
            $errors[] = $error;

        // print errors happened during last request execution,
        // when we decided to redirect with error message instead of
        // printing it directly (to avoid reexecution on refresh)
        $message_id = W3_Request::get_string('w3tc_message');
        if ($message_id) {
            $v = get_transient('w3tc_message.' . $message_id);
            set_transient('w3tc_message.' . $message_id, null);

            if (isset($v['errors']) && is_array($v['errors'])) {
                foreach ($v['errors'] as $error) {
                    if (isset($error_messages[$error]))
                        $errors[] = $error_messages[$error];
                    else
                        $errors[] = $error;
                }
            }
            if (isset($v['notes']) && is_array($v['notes'])) {
                foreach ($v['notes'] as $note) {
                    if (isset($note_messages[$note]))
                        $notes[] = $note_messages[$note];
                    else
                        $notes[] = $note;
                }
            }
        }

        /*
         * Filesystem environment fix, if needed
         */
        try {
            global $pagenow;
            if ($pagenow == 'plugins.php' || 
                    substr(W3_Request::get_string('page'), 0, 5) == 'w3tc_') {
                $environment = w3_instance('W3_AdminEnvironment');
                $environment->fix_in_wpadmin($this->_config);

                if (isset($_REQUEST['upgrade'])) 
                    $notes[] = __('Required files and directories have been automatically created', 'w3-total-cache');
            }
        } catch (SelfTestExceptions $exs) {
            $r = w3_parse_selftest_exceptions($exs);

            foreach ($r['before_errors'] as $e)
                $errors[] = $e;

            if (strlen($r['required_changes']) > 0) {
                $changes_style = 'border: 1px solid black; ' .
                    'background: white; ' .
                    'margin: 10px 30px 10px 30px; ' . 
                    'padding: 10px; display: none';
                $ftp_style = 'border: 1px solid black; background: white; ' .
                    'margin: 10px 30px 10px 30px; ' . 
                    'padding: 10px; display: none';
                $ftp_form = str_replace('class="wrap"', '', 
                    $exs->credentials_form());
                $ftp_form = str_replace('<form ', '<form name="w3tc_ftp_form" ', 
                    $ftp_form);
                $ftp_form = str_replace('<fieldset>', '', $ftp_form);
                $ftp_form = str_replace('</fieldset>', '', $ftp_form);
                $ftp_form = str_replace('id="upgrade" class="button"', 
                    'id="upgrade" class="button w3tc-button-save"', $ftp_form);

                $error = '<strong>W3 Total Cache Error:</strong> ' .
                    'Files and directories could not be automatically ' .
                    'created to complete the installation. ' .
                    '<table>' .
                    '<tr>' .
                    '<td>Please execute commands manually</td>' .
                    '<td>' . 
                    $this->button('View required changes', '', 
                        'w3tc-show-required-changes') . 
                    '</td>' .
                    '</tr>' .
                    '<tr>' .
                    '<td>or use FTP form to allow ' . 
                    '<strong>W3 Total Cache</strong> make it automatically.' . 
                    '</td>' .
                    '<td>' . 
                    $this->button('Update via FTP', '', 'w3tc-show-ftp-form') . 
                    '</td>' .
                    '</tr></table>'.

                    '<div class="w3tc-required-changes" style="' . 
                    $changes_style . '">' . $r['required_changes'] . '</div>' .
                    '<div class="w3tc-ftp-form" style="' . $ftp_style . '">' . 
                    $ftp_form . '</div>';

                $environment_error_present = true;
                $errors[] = $error;
            }

            foreach ($r['later_errors'] as $e)
                $errors[] = $e;
        }

        /**
        * CloudFlare notifications
         * @var $w3_cloudflare W3_CloudFlare
        */
        $w3_cloudflare = w3_instance('W3_CloudFlare');
        if ($error = $w3_cloudflare->check_lasterror()) {
            $this->_errors[] = $error;
        }

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $error = W3_Request::get_string('w3tc_error');
        $note = W3_Request::get_string('w3tc_note');

        /**
         * Handle messages from reqeust
         */
        if ($error == 'cloudflare_api_request' && $w3_cloudflare->get_fault_signaled()) {
            // dont complain twice on cloudflare
        }
        elseif (isset($error_messages[$error])) {
            $errors[] = $error_messages[$error];
        }

        if (isset($note_messages[$note])) {
            $notes[] = $note_messages[$note];
        }

        /**
         * CDN notifications
         */
        if ($this->_config->get_boolean('cdn.enabled') && !w3_is_cdn_mirror($this->_config->get_string('cdn.engine'))) {
            /**
             * Show notification after theme change
             */
            if ($this->_config->get_boolean('notes.theme_changed')) {
                $notes[] = sprintf(__('The active theme has changed, please %s now to ensure proper operation. %s', 'w3-total-cache'), $this->button_popup(__('upload active theme files', 'w3-total-cache'), 'cdn_export', 'cdn_export_type=theme'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'theme_changed'));
            }

            /**
             * Show notification after WP upgrade
             */
            if ($this->_config->get_boolean('notes.wp_upgraded')) {
                $notes[] = sprintf(__('Upgraded WordPress? Please %s files now to ensure proper operation. %s', 'w3-total-cache'), $this->button_popup('upload wp-includes', 'cdn_export', 'cdn_export_type=includes'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'wp_upgraded'));
            }

            /**
             * Show notification after CDN enable
             */
            if ($this->_config->get_boolean('notes.cdn_upload') || $this->_config->get_boolean('notes.cdn_reupload')) {
                $cdn_upload_buttons = array();

                if ($this->_config->get_boolean('cdn.includes.enable')) {
                    $cdn_upload_buttons[] = $this->button_popup('wp-includes', 'cdn_export', 'cdn_export_type=includes');
                }

                if ($this->_config->get_boolean('cdn.theme.enable')) {
                    $cdn_upload_buttons[] = $this->button_popup('theme files', 'cdn_export', 'cdn_export_type=theme');
                }

                if ($this->_config->get_boolean('minify.enabled') && $this->_config->get_boolean('cdn.minify.enable') &&
                    !$this->_config->get_boolean('minify.auto')) {
                    $cdn_upload_buttons[] = $this->button_popup('minify files', 'cdn_export', 'cdn_export_type=minify');
                }

                if ($this->_config->get_boolean('cdn.custom.enable')) {
                    $cdn_upload_buttons[] = $this->button_popup('custom files', 'cdn_export', 'cdn_export_type=custom');
                }

                if ($this->_config->get_boolean('notes.cdn_upload')) {
                    $notes[] = sprintf(__('Make sure to %s and upload the %s, files to the <acronym title="Content Delivery Network">CDN</acronym> to ensure proper operation. %s', 'w3-total-cache'), $this->button_popup('export the media library', 'cdn_export_library'), implode(', ', $cdn_upload_buttons), $this->button_hide_note('Hide this message', 'cdn_upload'));
                }

                if ($this->_config->get_boolean('notes.cdn_reupload')) {
                    $notes[] = sprintf(__('Settings that affect Browser Cache settings for files hosted by the CDN have been changed. To apply the new settings %s and %s. %s', 'w3-total-cache'), $this->button_popup(__('export the media library', 'w3-total-cache'), 'cdn_export_library'), implode(', ', $cdn_upload_buttons), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'cdn_reupload'));
                }
            }

            /**
             * Show notification if upload queue is not empty
             */
            if (!$this->is_queue_empty()) {
                $errors[] = sprintf(__('The %s has unresolved errors. Empty the queue to restore normal operation.', 'w3-total-cache'), $this->button_popup(__('unsuccessful transfer queue', 'w3-total-cache'), 'cdn_queue'));
            }
        }

        /**
         * Show notification after plugin activate/deactivate
         */
        if ($this->_config->get_boolean('notes.plugins_updated')) {
            $texts = array();

            if ($this->_config->get_boolean('pgcache.enabled')) {
                $texts[] = $this->button_link(__('empty the page cache', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_flush_pgcache', $this->_page), 'w3tc'));
            }

            if ($this->_config->get_boolean('minify.enabled')) {
                $texts[] = sprintf(__('check the %s to maintain the desired user experience', 'w3-total-cache'), $this->button_hide_note(__('minify settings', 'w3-total-cache'), 'plugins_updated', 'admin.php?page=w3tc_minify'));
            }

            if (count($texts)) {
                $notes[] = sprintf(__('One or more plugins have been activated or deactivated, please %s. %s', 'w3-total-cache'), implode(__(' and ', 'w3-total-cache'), $texts), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'plugins_updated'));
            }
        }

        /**
         * Show notification when page cache needs to be emptied
         */
        if ($this->_config->get_boolean('pgcache.enabled') && $this->_config->get('notes.need_empty_pgcache') && !$this->_config->is_preview()) {
            $notes[] = sprintf('The setting change(s) made either invalidate the cached data or modify the behavior of the site. %s now to provide a consistent user experience.', $this->button_link('Empty the page cache', wp_nonce_url(sprintf('admin.php?page=%s&w3tc_flush_pgcache', $this->_page), 'w3tc')));
        }

        /**
         * Show notification when object cache needs to be emptied
         */
        if ($this->_config->get_boolean('objectcache.enabled') && $this->_config->get('notes.need_empty_objectcache') && !$this->_config->is_preview()) {
            $notes[] = sprintf(__('The setting change(s) made either invalidate the cached data or modify the behavior of the site. %s now to provide a consistent user experience.', 'w3-total-cache'), $this->button_link(__('Empty the object cache', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_flush_objectcache', $this->_page), 'w3tc')));
        }

        /**
         * Minify notifications
         */
        if ($this->_config->get_boolean('minify.enabled')) {
            /**
             * Minify error occured
             */
            if ($this->_config_admin->get_boolean('notes.minify_error')) {
                $errors[] = sprintf(__('Recently an error occurred while creating the CSS / JS minify cache: %s. %s', 'w3-total-cache'), $this->_config_admin->get_string('minify.error.last'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'minify_error', '', true));
            }

            /**
             * Show notification when minify needs to be emptied
             */
            if ($this->_config->get_boolean('notes.need_empty_minify') && !$this->_config->is_preview()) {
                $notes[] = sprintf(__('The setting change(s) made either invalidate the cached data or modify the behavior of the site. %s now to provide a consistent user experience.', 'w3-total-cache'), $this->button_link(__('Empty the minify cache', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_flush_minify', $this->_page), 'w3tc')));
            }
        }

        if ($this->_config->get_boolean('newrelic.enabled')) {
            /**
             * @var $nerser W3_NewRelicService
             */
            $nerser = w3_instance('W3_NewRelicService');

            try {
                $pl = $nerser->get_frontend_response_time();

                if ($pl>0.3 && $this->_config_admin->get_boolean('notes.new_relic_page_load_notification')) {
                    $nr_recommends = array();
                    if (!$this->_config->get_boolean('pgcache.enabled'))
                        $nr_recommends[] = __('Page Cache', 'w3-total-cache');
                    if (!$this->_config->get_boolean('minify.enabled'))
                        $nr_recommends[] = __('Minify', 'w3-total-cache');
                    if (!$this->_config->get_boolean('cdn.enabled'))
                        $nr_recommends[] = __('CDN', 'w3-total-cache');
                    if (!$this->_config->get_boolean('browsercache.enabled'))
                        $nr_recommends[] = __('Browser Cache and use compression', 'w3-total-cache');
                    if ($nr_recommends) {
                        $message =  sprintf(__('Application monitoring has detected that your page load time is
                                                       higher than 300ms. It is recommended that you enable the following
                                                       features: %s %s', 'w3-total-cache')
                                                       , implode(', ', $nr_recommends)
                                                       , $this->button_hide_note('Hide this message', 'new_relic_page_load_notification', '', true)
                                                       );
                        $notes[] = $message;
                    }
                }
            }catch(Exception $ex){}
        }
        /**
         * Show notification if user can remove old w3tc folders
         */
        if ($this->_config_admin->get_boolean('notes.remove_w3tc')) {
            w3_require_once(W3TC_INC_DIR . '/functions/update.php');
            $folders = w3_find_old_folders();
            $folders = array_map('basename', $folders);
            $notes[] = sprintf(__('The directory w3tc can be deleted. %s: %s. However, <em>do not remove the w3tc-config directory</em>. %s', 'w3-total-cache')
                                , WP_CONTENT_DIR, implode(', ',$folders)
                                , $this->button_hide_note('Hide this message', 'remove_w3tc', '', true));
        }

        /**
         * Show messages
         */
        foreach ($notes as $note) {
            echo sprintf('<div class="updated fade"><p>%s</p></div>', $note);
        }

        foreach ($errors as $error) {
            echo sprintf('<div class="error"><p>%s</p></div>', $error);
        }
    }

    /**
     * Options page
     *
     * @return void
     */
    function options() {
        $remove_results = array();
        $w3tc_error = array();
        w3_require_once(W3TC_INC_DIR . '/functions/activation.php');

        $preview = $this->_config->is_preview();
        if (w3_is_network() && !$this->is_master()) {
            $this->_config_master = new W3_Config(true);
        }
        else
            $this->_config_master = $this->_config;

        /**
         * Check for page cache availability
         */
        $wp_config_edit = false;

        /**
         * Check memcached
         */
        $memcaches_errors = array();

        if ($this->_config->get_boolean('pgcache.enabled') && $this->_config->get_string('pgcache.engine') == 'memcached') {
            $pgcache_memcached_servers = $this->_config->get_array('pgcache.memcached.servers');

            if (!$this->is_memcache_available($pgcache_memcached_servers)) {
                $memcaches_errors[] = sprintf(__('Page Cache: %s.', 'w3-total-cache'), implode(', ', $pgcache_memcached_servers));
            }
        }

        if ($this->_config->get_boolean('minify.enabled') && $this->_config->get_string('minify.engine') == 'memcached') {
            $minify_memcached_servers = $this->_config->get_array('minify.memcached.servers');

            if (!$this->is_memcache_available($minify_memcached_servers)) {
                $memcaches_errors[] = sprintf(__('Minify: %s.', 'w3-total-cache'), implode(', ', $minify_memcached_servers));
            }
        }

        if ($this->_config->get_boolean('dbcache.enabled') && $this->_config->get_string('dbcache.engine') == 'memcached') {
            $dbcache_memcached_servers = $this->_config->get_array('dbcache.memcached.servers');

            if (!$this->is_memcache_available($dbcache_memcached_servers)) {
                $memcaches_errors[] = sprintf(__('Database Cache: %s.', 'w3-total-cache'), implode(', ', $dbcache_memcached_servers));
            }
        }

        if ($this->_config->get_boolean('objectcache.enabled') && $this->_config->get_string('objectcache.engine') == 'memcached') {
            $objectcache_memcached_servers = $this->_config->get_array('objectcache.memcached.servers');

            if (!$this->is_memcache_available($objectcache_memcached_servers)) {
                $memcaches_errors[] = sprintf(__('Object Cache: %s.', 'w3-total-cache'), implode(', ', $objectcache_memcached_servers));
            }
        }

        if (count($memcaches_errors)) {
            $memcache_error = __('The following memcached servers are not responding or not running:</p><ul>', 'w3-total-cache');

            foreach ($memcaches_errors as $memcaches_error) {
                $memcache_error .= '<li>' . $memcaches_error . '</li>';
            }

            $memcache_error .= __('</ul><p>This message will automatically disappear once the issue is resolved.', 'w3-total-cache');

            $this->_errors[] = $memcache_error;
        }

        /**
         * Check CURL extension
         */
        if ($this->_config->get_boolean('notes.no_curl') && $this->_config->get_boolean('cdn.enabled') && !function_exists('curl_init')) {
            $this->_notes[] = sprintf(__('The <strong>CURL PHP</strong> extension is not available. Please install it to enable S3 or CloudFront functionality. %s', 'w3-total-cache'), $this->button_hide_note('Hide this message', 'no_curl'));
        }

        /**
         * Check Zlib extension
         */
        if ($this->_config->get_boolean('notes.no_zlib') && !function_exists('gzencode')) {
            $this->_notes[] = sprintf(__('Unfortunately the PHP installation is incomplete, the <strong>zlib module is missing</strong>. This is a core PHP module. Notify the server administrator. %s', 'w3-total-cache'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'no_zlib'));
        }

        /**
         * Check if Zlib output compression is enabled
         */
        if ($this->_config->get_boolean('notes.zlib_output_compression') && w3_zlib_output_compression()) {
            $this->_notes[] = sprintf(__('Either the PHP configuration, web server configuration or a script in the WordPress installation has <strong>zlib.output_compression</strong> enabled.<br />Please locate and disable this setting to ensure proper HTTP compression behavior. %s', 'w3-total-cache'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'zlib_output_compression'));
        }

        /**
         * Check wp-content permissions
         */
        if (!W3TC_WIN && $this->_config->get_boolean('notes.wp_content_perms')) {
            w3_require_once( W3TC_INC_DIR . '/functions/file.php');
            $wp_content_mode = w3_get_file_permissions(WP_CONTENT_DIR);

            if ($wp_content_mode > 0755) {
                $this->_notes[] = sprintf(__('<strong>%s</strong> is write-able. When finished installing the plugin,
                                        change the permissions back to the default: <strong>chmod 755 %s</strong>.
                                        Permissions are currently %s. %s', 'w3-total-cache')
                                        , WP_CONTENT_DIR
                                        , WP_CONTENT_DIR
                                        , base_convert(w3_get_file_permissions(WP_CONTENT_DIR), 10, 8)
                                        , $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'wp_content_perms'));
            }
        }

        /**
         * Check wp-content permissions
         */
        if (!W3TC_WIN && $this->_config->get_boolean('notes.wp_content_changed_perms')) {
            $perm = get_transient('w3tc_prev_permission');
            $current_perm = w3_get_file_permissions(WP_CONTENT_DIR);
            if ($perm && $perm != base_convert($current_perm, 10, 8) && ($current_perm > 0755 || $perm < base_convert($current_perm, 10, 8))) {
                $this->_notes[] = sprintf(__('<strong>%s</strong> permissions were changed during the setup process.
                                        Permissions are currently %s.<br />To restore permissions run
                                        <strong>chmod %s %s</strong>. %s', 'w3-total-cache')
                                        , WP_CONTENT_DIR
                                        , base_convert($current_perm, 10, 8)
                                        , $perm
                                        , WP_CONTENT_DIR
                                        , $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'wp_content_changed_perms'));
            }
        }

        /**
         * Check permalinks
         */
        if ($this->_config->get_boolean('notes.no_permalink_rules') && (($this->_config->get_boolean('pgcache.enabled') && $this->_config->get_string('pgcache.engine') == 'file_generic') || ($this->_config->get_boolean('browsercache.enabled') && $this->_config->get_boolean('browsercache.no404wp'))) && !w3_is_permalink_rules()) {
            $this->_errors[] = sprintf(__('The required directives for fancy permalinks could not be detected, please confirm they are available: <a href="http://codex.wordpress.org/Using_Permalinks#Creating_and_editing_.28.htaccess.29">Creating and editing</a> %s', 'w3-total-cache'), $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'no_permalink_rules'));
        }

        /**
         * CDN
         */

        if ($this->_config->get_boolean('cdn.enabled')) {
            /**
             * Check upload settings
             */
            $upload_info = w3_upload_info();

            if (!$upload_info) {
                $upload_path = get_option('upload_path');
                $upload_path = trim($upload_path);

                if (empty($upload_path)) {
                    $upload_path = WP_CONTENT_DIR . '/uploads';

                    $this->_errors[] = sprintf(__('The uploads directory is not available. Default WordPress directories will be created: <strong>%s</strong>.', 'w3-total-cache'), $upload_path);
                }

                if (!w3_is_multisite()) {
                    $this->_errors[] = sprintf(__('The uploads path found in the database (%s) is inconsistent with the actual path. Please manually adjust the upload path either in miscellaneous settings or if not using a custom path %s automatically to resolve the issue.', 'w3-total-cache'), $upload_path, $this->button_link(__('update the path', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_update_upload_path', $this->_page), 'w3tc')));
                }
            }

            /**
             * Check CDN settings
             */
            $cdn_engine = $this->_config->get_string('cdn.engine');
            $error = '';
            switch (true) {
                case ($cdn_engine == 'ftp' && !count($this->_config->get_array('cdn.ftp.domain'))):
                    $this->_errors[] = __('A configuration issue prevents <acronym title="Content Delivery Network">CDN</acronym> from working: 
                                            The <strong>"Replace default hostname with"</strong> 
                                            field cannot be empty. Enter <acronym 
                                            title="Content Delivery Network">CDN</acronym> 
                                            provider hostname <a href="?page=w3tc_cdn#configuration">here</a>. 
                                            <em>(This is the hostname used in order to view objects 
                                            in a browser.)</em>', 'w3-total-cache');
                    break;

                case ($cdn_engine == 's3' && ($this->_config->get_string('cdn.s3.key') == '' || $this->_config->get_string('cdn.s3.secret') == '' || $this->_config->get_string('cdn.s3.bucket') == '')):
                    $error = __('The <strong>"Access key", "Secret key" and "Bucket"</strong> fields cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'cf' && ($this->_config->get_string('cdn.cf.key') == '' || $this->_config->get_string('cdn.cf.secret') == '' || $this->_config->get_string('cdn.cf.bucket') == '' || ($this->_config->get_string('cdn.cf.id') == '' && !count($this->_config->get_array('cdn.cf.cname'))))):
                    $error = __('The <strong>"Access key", "Secret key", "Bucket" and "Replace default hostname with"</strong> fields cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'cf2' && ($this->_config->get_string('cdn.cf2.key') == '' || $this->_config->get_string('cdn.cf2.secret') == '' || ($this->_config->get_string('cdn.cf2.id') == '' && !count($this->_config->get_array('cdn.cf2.cname'))))):
                    $error = __('The <strong>"Access key", "Secret key" and "Replace default hostname with"</strong> fields cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'rscf' && ($this->_config->get_string('cdn.rscf.user') == '' || $this->_config->get_string('cdn.rscf.key') == '' || $this->_config->get_string('cdn.rscf.container') == '' || !count($this->_config->get_array('cdn.rscf.cname')))):
                    $error = __('The <strong>"Username", "API key", "Container" and "Replace default hostname with"</strong> fields cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'azure' && ($this->_config->get_string('cdn.azure.user') == '' || $this->_config->get_string('cdn.azure.key') == '' || $this->_config->get_string('cdn.azure.container') == '')):
                    $error = __('The <strong>"Account name", "Account key" and "Container"</strong> fields cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'mirror' && !count($this->_config->get_array('cdn.mirror.domain'))):
                    $error = __('The <strong>"Replace default hostname with"</strong> field cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'netdna'):
                    $fields = array();
                    if ($this->_config->get_string('cdn.netdna.authorization_key') == '')
                        $fields[] = '"' . __('Authorization key', 'w3-total-cache') . '"';

                    if (!count($this->_config->get_array('cdn.netdna.domain')))
                        $fields[] = '"' . __('Replace default hostname with', 'w3-total-cache') . '"';

                    if ($fields) {
                        $error = sprintf(__('The <strong>%s</strong> field cannot be empty.', 'w3-total-cache'),
                            implode(__(' and ', 'w3-total-cache'), $fields));
                    }

                    if ($this->_config->get_string('cdn.netdna.authorization_key') != '' &&
                        sizeof(explode('+', $this->_config->get_string('cdn.netdna.authorization_key'))) != 3)
                        $error .= __('The <strong>"Authorization key"</strong> is not correct.', 'w3-total-cache');
                    elseif ($this->_config->get_integer('cdn.netdna.zone_id', 0) <= 0)
                        $error .= __('You need to select / create a pull zone.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'maxcdn'):
                    $fields = array();
                    if ($this->_config->get_string('cdn.maxcdn.authorization_key') == '')
                        $fields[] = '"' . __('Authorization key', 'w3-total-cache') . '"';

                    if (!count($this->_config->get_array('cdn.maxcdn.domain')))
                        $fields[] = '"' . __('Replace default hostname with', 'w3-total-cache') . '"';

                    if ($fields) {
                        $error = sprintf(__('The <strong>%s</strong> field cannot be empty.', 'w3-total-cache'),
                            implode(__(' and ', 'w3-total-cache'), $fields));
                    }

                    if ($this->_config->get_string('cdn.maxcdn.authorization_key') != '' &&
                        sizeof(explode('+', $this->_config->get_string('cdn.maxcdn.authorization_key'))) != 3)
                        $error .= __('The <strong>"Authorization key"</strong> is not correct.', 'w3-total-cache');
                    elseif ($this->_config->get_integer('cdn.maxcdn.zone_id', 0) <= 0)
                        $error .= __('You need to select / create a pull zone.', 'w3-total-cache');

                    break;

                case ($cdn_engine == 'cotendo' && !count($this->_config->get_array('cdn.cotendo.domain'))):
                    $error = __('The <strong>"Replace default hostname with"</strong> field cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'edgecast' && !count($this->_config->get_array('cdn.edgecast.domain'))):
                    $error = __('The <strong>"Replace default hostname with"</strong> field cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'att' && !count($this->_config->get_array('cdn.att.domain'))):
                    $error = __('The <strong>"Replace default hostname with"</strong> field cannot be empty.', 'w3-total-cache');
                    break;

                case ($cdn_engine == 'akamai' && !count($this->_config->get_array('cdn.akamai.domain'))):
                    $error = 'The <strong>"Replace default hostname with"</strong> field cannot be empty.';
                    break;
            }

            if ($error) {
                $this->_errors[] = __('A configuration issue prevents <acronym title="Content Delivery Network">CDN</acronym> from working: ', 'w3-total-cache') . $error . __(' <a href="?page=w3tc_cdn#configuration">Specify it here</a>.', 'w3-total-cache');
            }
        }


        /**
         * Preview mode
         */
        if ($this->_config->is_preview()) {
            $this->_notes[] = sprintf(__('Preview mode is active: Changed settings will not take effect until preview mode is %s or %s. %s any changed settings (without deploying), or make additional changes.', 'w3-total-cache'), $this->button_link(__('deploy', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_preview_deploy', $this->_page), 'w3tc')), $this->button_link(__('disable', 'w3-total-cache'), wp_nonce_url(sprintf('admin.php?page=%s&w3tc_preview_disable', $this->_page), 'w3tc')), $this->button_link(__('Preview', 'w3-total-cache'), w3_get_home_url() . '/?w3tc_preview=1', true));
        }

        /**
         *
         */

        if ($this->_config->get_boolean('notes.root_rules') && count($this->_rule_errors_root) > 0) {
            $this->_rule_errors_root_hide = $this->button_hide_note(__('Hide this message', 'w3-total-cache'), 'root_rules');
        } else {
            $this->_rule_errors_root = array();
        }

        $this->_disable_file_operation_notification = $this->_disable_add_in_files_notification || $this->_disable_cache_write_notification;

        if (!$this->_disable_file_operation_notification && isset($file_operation_exception) && $file_operation_exception) {
            $tech_message = '<ul>';
            $core_rules_perms = '';
            if (w3_get_file_permissions(w3_get_wp_config_path()) != 0644)
                $core_config_perms = sprintf(__('File permissions are <strong>%s</strong>, however they should be
					<strong>644</strong>.', 'w3-total-cache')
                    , base_convert(w3_get_file_permissions(w3_get_wp_config_path()), 10, 8)
                );
            else
                $core_config_perms = sprintf(__('File permissions are <strong>%s</strong>', 'w3-total-cache'), base_convert(w3_get_file_permissions(w3_get_wp_config_path()), 10, 8));

            if (w3_get_file_permissions(w3_get_pgcache_rules_core_path()) != 0644)
                $core_rules_perms = sprintf(__('File permissions are <strong>%s</strong>, however they should be
											<strong>644</strong>.', 'w3-total-cache')
                                            , base_convert(w3_get_file_permissions(w3_get_pgcache_rules_core_path()), 10, 8)
                                            );
            else
                $core_rules_perms = sprintf(__('File permissions are <strong>%s</strong>', 'w3-total-cache'), base_convert(w3_get_file_permissions(w3_get_pgcache_rules_core_path()), 10, 8));

            $wp_content_perms = '';
            if (w3_get_file_permissions(WP_CONTENT_DIR) != 0755)
                $wp_content_perms = sprintf(__('Directory permissions are <strong>%s</strong>, however they should be
											<strong>755</strong>.', 'w3-total-cache')
                                            , base_convert(w3_get_file_permissions(WP_CONTENT_DIR), 10, 8)
                                            );
            $tech_message .= '<li>' . sprintf(__('File: <strong>%s</strong> %s File owner: %s', 'w3-total-cache')
                    ,w3_get_wp_config_path()
                    ,$core_config_perms
                    , w3_get_file_owner(w3_get_wp_config_path())) .
                    '</li>' ;

            $tech_message .= '<li>' . sprintf(__('File: <strong>%s</strong> %s File owner: %s', 'w3-total-cache')
                                                ,w3_get_pgcache_rules_core_path()
                                                ,$core_rules_perms
                                                , w3_get_file_owner(w3_get_pgcache_rules_core_path())) .
                                 '</li>' ;

            $tech_message .= '<li>' . sprintf(__('Directory: <strong>%s</strong> %s File owner: %s', 'w3-total-cache')
                                            , WP_CONTENT_DIR
                                            , $wp_content_perms
                                            , w3_get_file_owner(WP_CONTENT_DIR)) .
                             '</li>' ;

            $tech_message .= '<li>' . sprintf(__('Owner of current file: %s', 'w3-total-cache'), w3_get_file_owner()) .
                             '</li>' ;
            if (!(w3_get_file_owner() == w3_get_file_owner(w3_get_pgcache_rules_core_path()) &&
                w3_get_file_owner() == w3_get_file_owner(WP_CONTENT_DIR)))
                $tech_message .= __('<li>The files and directories have different ownership, they should have the same ownership.
								 </li>', 'w3-total-cache');
            $tech_message .= '</ul>';
            $tech_message = '<div class="w3tc-technical-info" style="display:none">' . $tech_message . '</div>';
            $w3tc_error[] = sprintf(__('<strong>W3 Total Cache Error:</strong> The plugin tried to edit, %s, but failed.
								Files and directories cannot be modified. Please review your
								<a target="_blank" href="http://codex.wordpress.org/Changing_File_Permissions">
								file permissions</a>. A common cause is %s and %s having different ownership or permissions.
								%s %s', 'w3-total-cache')
                                , $wp_config_edit ? w3_get_wp_config_path() :  w3_get_pgcache_rules_core_path()
                                , $wp_config_edit ? basename(w3_get_wp_config_path()) :  basename(w3_get_pgcache_rules_core_path())
                                , WP_CONTENT_DIR
                                , $this->button(__('View technical information', 'w3-total-cache'), '', 'w3tc-show-technical-info')
                                ,$tech_message);
        }

        /**
         * Remove functions results
         */
        if ($remove_results) {
            foreach ($remove_results as $result) {
                $this->_errors = array_merge($this->_errors, $result['errors']);
                if (!isset($this->_ftp_form) && isset($result['ftp_form'])) {
                    $extra_ftp_message = __('Please enter FTP details <a href="#ftp_upload_form">below</a> to remove the disabled modules. ', 'w3-total-cache');
                    $this->_ftp_form = $result['ftp_form'];
                    $this->_use_ftp_form = true;
                }
            }
            if (isset($extra_ftp_message))
                $this->_errors[] = $extra_ftp_message;
        }

        foreach ($w3tc_error as $error)
            array_unshift($this->_errors, $error);

        if (isset($this->_ftp_form))
            $this->_use_ftp_form = true;

        /*
         * Hidden pages
         */
        if (isset($_REQUEST['w3tc_dbcluster_config']))
            $this->dbcluster_config();

        /**
         * Show tab
         */
        switch ($this->_page) {
            case 'w3tc_dashboard':
                $this->options_dashboard();
                break;

            case 'w3tc_general':
                $this->options_general();
                break;

            case 'w3tc_pgcache':
                $this->options_pgcache();
                break;

            case 'w3tc_minify':
                $this->options_minify();
                break;

            case 'w3tc_dbcache':
                $this->options_dbcache();
                break;

            case 'w3tc_objectcache':
                $this->options_objectcache();
                break;

            case 'w3tc_fragmentcache':
                $this->options_fragmentcache();
                break;

            case 'w3tc_browsercache':
                $this->options_browsercache();
                break;

            case 'w3tc_mobile':
                $this->options_mobile();
                break;

            case 'w3tc_referrer':
                $this->options_referrer();
                break;

            case 'w3tc_cdn':
                $this->options_cdn();
                break;

            case 'w3tc_monitoring':
                $this->options_monitoring();
                break;

            case 'w3tc_faq':
                $this->options_faq();
                break;

            case 'w3tc_support':
                $this->options_support();
                break;

            case 'w3tc_install':
                $this->options_install();
                break;

            case 'w3tc_about':
                $this->options_about();
                break;
        }
    }

    /**
     * Dashboard tab
     */
    function options_dashboard() {
        w3_require_once(W3TC_INC_DIR . '/functions/widgets.php');
        /**
         * @var $module_status W3_ModuleStatus
         */
        $module_status = w3_instance('W3_ModuleStatus');
        w3tc_dashboard_setup();
        global $current_user;
        $config_master = $this->_config_master;

        $browsercache_enabled = $module_status->is_enabled('browsercache');
        $cloudflare_enabled = $module_status->is_enabled('cloudflare');

        $enabled = $module_status->plugin_is_enabled();

        $can_empty_memcache = $module_status->can_empty_memcache();

        $can_empty_opcode = $module_status->can_empty_opcode();

        $can_empty_apc_system = $module_status->can_empty_apc_system();

        $can_empty_file = $module_status->can_empty_file();

        $can_empty_varnish = $module_status->can_empty_varnish();

        $cdn_enabled = $module_status->is_enabled('cdn');
        $cdn_mirror_purge = w3_cdn_can_purge_all($module_status->get_module_engine('cdn'));

        if ($cloudflare_enabled && $this->_config->get_string('cloudflare.email') && $this->_config->get_string('cloudflare.key')) {
            $can_empty_cloudflare = true;
        } else {
            $can_empty_cloudflare = false;
        }

        // Required for Update Media Query String button
        $browsercache_update_media_qs = ($this->_config->get_boolean('browsercache.cssjs.replace') || $this->_config->get_boolean('browsercache.other.replace'));

        include W3TC_INC_DIR . '/options/dashboard.php';
    }

    /**
     * General tab
     *
     * @return void
     */
    function options_general() {
        global $current_user;
        $config_master = $this->_config_master;
        /**
         * @var $modules W3_ModuleStatus
         */
        $modules = w3_instance('W3_ModuleStatus');

        $pgcache_enabled = $modules->is_enabled('pgcache');
        $dbcache_enabled = $modules->is_enabled('dbcache');
        $objectcache_enabled = $modules->is_enabled('objectcache');
        $browsercache_enabled = $modules->is_enabled('browsercache');
        $minify_enabled = $modules->is_enabled('minify');
        $cdn_enabled = $modules->is_enabled('cdn');
        $cloudflare_enabled = $modules->is_enabled('cloudflare');
        $varnish_enabled = $modules->is_enabled('varnish');
        $fragmentcache_enabled = $modules->is_enabled('fragmentcache');

        $enabled = $modules->plugin_is_enabled();
        $enabled_checkbox = $modules->all_modules_enabled();

        $check_rules = w3_can_check_rules();
        $check_apc = function_exists('apc_store');
        $check_eaccelerator = function_exists('eaccelerator_put');
        $check_xcache = function_exists('xcache_set');
        $check_wincache = function_exists('wincache_ucache_set');
        $check_curl = function_exists('curl_init');
        $check_memcached = class_exists('Memcache');
        $check_ftp = function_exists('ftp_connect');
        $check_tidy = class_exists('tidy');

        $disc_enhanced_enabled = !(! $check_rules || (!$this->is_master() && w3_is_network() && $config_master->get_string('pgcache.engine') != 'file_generic'));

        $can_empty_file = $modules->can_empty_file();

        $can_empty_varnish = $modules->can_empty_varnish();

        $cdn_mirror_purge = w3_cdn_can_purge_all($modules->get_module_engine('cdn'));

        $cloudflare_signup_email = '';
        $cloudflare_signup_user = '';

        if (is_a($current_user, 'WP_User')) {
            if ($current_user->user_email) {
                $cloudflare_signup_email = $current_user->user_email;
            }

            if ($current_user->user_login && $current_user->user_login != 'admin') {
                $cloudflare_signup_user = $current_user->user_login;
            }
        }

        /**
         * @var $w3_cloudflare W3_CloudFlare
         */
        $w3_cloudflare = w3_instance('W3_CloudFlare');
        $cf_options = $w3_cloudflare->get_options();
        $cloudflare_seclvls = $cf_options['sec_lvl'];
        $cloudflare_devmodes = $cf_options['dev_mode'];

        $cloudflare_rocket_loaders = $cf_options['async'];
        $cloudflare_minifications = $cf_options['minify'];

        $cloudflare_seclvl = 'med';
        $cloudflare_devmode_expire = 0;
        $cloudflare_devmode = 0;
        $cloudflare_rocket_loader = 0;
        $cloudflare_minify = 0;

        if ($cloudflare_enabled && $this->_config->get_string('cloudflare.email') && $this->_config->get_string('cloudflare.key')) {
            $settings = $w3_cloudflare->get_settings();
            $cloudflare_seclvl = $settings['sec_lvl'];
            $cloudflare_devmode_expire = $settings['devmode'];
            $cloudflare_rocket_loader = $settings['async'];
            $cloudflare_devmode = ($cloudflare_devmode_expire ? 1 : 0);
            $cloudflare_minify = $settings['minify'];
            $can_empty_cloudflare = true;
        } else {
            $can_empty_cloudflare = false;
        }

        $file_nfs = ($this->_config->get_boolean('pgcache.file.nfs') || $this->_config->get_boolean('minify.file.nfs'));
        $file_locking = ($this->_config->get_boolean('dbcache.file.locking') || $this->_config->get_boolean('objectcache.file.locking') || $this->_config->get_boolean('pgcache.file.locking') || $this->_config->get_boolean('minify.file.locking'));

        w3_require_once(W3TC_LIB_NEWRELIC_DIR . '/NewRelicWrapper.php');
        $newrelic_conf_appname = NewRelicWrapper::get_wordpress_appname($this->_config, $this->_config_master,false);
        $newrelic_applications = array();
        $nerser = w3_instance('W3_NewRelicService');

        $new_relic_installed = $nerser->module_is_enabled();
        $new_relic_running = true;
        if ($this->_config->get_boolean('newrelic.enabled')) {

            $new_relic_configured = $this->_config->get_string('newrelic.api_key') &&
                                    $this->_config->get_string('newrelic.account_id');

            $newrelic_prefix = '';
            if ($new_relic_configured) {
                if (w3_is_network())
                    $newrelic_prefix = $this->_config->get_string('newrelic.appname_prefix');

                try {
                    $newrelic_applications = $nerser->get_applications();
                }catch(Exception $ex) {
                }
                $newrelic_application = $this->_config->get_string('newrelic.application_id');

            }
        }
        include W3TC_INC_DIR . '/options/general.php';
    }

    /**
     * Database cluster config editor
     *
     * @return void
     */
    function dbcluster_config() {
        if (w3_is_dbcluster())
            $content = @file_get_contents(W3TC_FILE_DB_CLUSTER_CONFIG);
        else
            $content = @file_get_contents(W3TC_DIR . '/ini/dbcluster-config-sample.php');

        include W3TC_INC_OPTIONS_DIR . '/enterprise/dbcluster-config.php';
    }

    /**
     * Page cache tab
     *
     * @return void
     */
    function options_pgcache() {
        global $wp_rewrite;

        $feeds = $wp_rewrite->feeds;

        $feed_key = array_search('feed', $feeds);

        if ($feed_key !== false) {
            unset($feeds[$feed_key]);
        }

        $default_feed = get_default_feed();
        $pgcache_enabled = $this->_config->get_boolean('pgcache.enabled');
        $permalink_structure = get_option('permalink_structure');

        $varnish_enabled = $this->_config->get_boolean('varnish.enabled');
        $cdn_mirror_purge_enabled = w3_is_cdn_mirror($this->_config->get_string('cdn.engine')) &&
                            $this->_config->get_string('cdn.engine') != 'mirror' &&
                            $this->_config->get_boolean('cdncache.enabled');
        $disable_check_domain = (w3_is_multisite() && w3_force_master());
        include W3TC_INC_DIR . '/options/pgcache.php';
    }

    /**
     * Minify tab
     *
     * @return void
     */
    function options_minify() {
        $minify_enabled = $this->_config->get_boolean('minify.enabled');

        $minify_rewrite_disabled = (w3_is_network() && !$this->is_master() && !$this->_config_master->get_boolean('minify.rewrite'));
        $themes = $this->get_themes();
        $templates = array();

        $current_theme = w3tc_get_current_theme_name();
        $current_theme_key = '';

        foreach ($themes as $theme_key => $theme_name) {
            if ($theme_name == $current_theme) {
                $current_theme_key = $theme_key;
            }

            $templates[$theme_key] = $this->get_theme_templates($theme_name);
        }

        $css_imports_values = array(
            '' => 'None',
            'bubble' => 'Bubble',
            'process' => 'Process',
        );

        $auto = $this->_config->get_boolean('minify.auto');

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $js_theme = W3_Request::get_string('js_theme', $current_theme_key);
        $js_groups = $this->_config->get_array('minify.js.groups');

        $css_theme = W3_Request::get_string('css_theme', $current_theme_key);
        $css_groups = $this->_config->get_array('minify.css.groups');

        $js_engine = $this->_config->get_string('minify.js.engine');
        $css_engine = $this->_config->get_string('minify.css.engine');
        $html_engine = $this->_config->get_string('minify.html.engine');

        $css_imports = $this->_config->get_string('minify.css.imports');

        // Required for Update Media Query String button
        $browsercache_enabled = $this->_config->get_boolean('browsercache.enabled');
        $browsercache_update_media_qs = ($this->_config->get_boolean('browsercache.cssjs.replace'));

        include W3TC_INC_DIR . '/options/minify.php';
    }

    /**
     * Database cache tab
     *
     * @return void
     */
    function options_dbcache() {
        $dbcache_enabled = $this->_config->get_boolean('dbcache.enabled');

        include W3TC_INC_DIR . '/options/dbcache.php';
    }

    /**
     * Objects cache tab
     *
     * @return void
     */
    function options_objectcache() {
        $objectcache_enabled = $this->_config->get_boolean('objectcache.enabled');

        include W3TC_INC_DIR . '/options/objectcache.php';
    }

    /**
     * Fragment cache tab
     *
     * @return void
     */
    function options_fragmentcache() {
        $fragmentcache_enabled = $this->_config->get_boolean('fragmentcache.enabled');
        $w3_plugin_fragmentcache = w3_instance('W3_Pro_Plugin_FragmentCache');

        $registered_groups = $w3_plugin_fragmentcache->get_registered_fragment_groups();
        $registered_global_groups = $w3_plugin_fragmentcache->get_registered_global_fragment_groups();
        include W3TC_INC_DIR . '/options/pro/fragmentcache.php';
    }

    /**
     * Objects cache tab
     *
     * @return void
     */
    function options_browsercache() {
        $browsercache_enabled = $this->_config->get_boolean('browsercache.enabled');
        $browsercache_last_modified = ($this->_config->get_boolean('browsercache.cssjs.last_modified') && $this->_config->get_boolean('browsercache.html.last_modified') && $this->_config->get_boolean('browsercache.other.last_modified'));
        $browsercache_expires = ($this->_config->get_boolean('browsercache.cssjs.expires') && $this->_config->get_boolean('browsercache.html.expires') && $this->_config->get_boolean('browsercache.other.expires'));
        $browsercache_cache_control = ($this->_config->get_boolean('browsercache.cssjs.cache.control') && $this->_config->get_boolean('browsercache.html.cache.control') && $this->_config->get_boolean('browsercache.other.cache.control'));
        $browsercache_etag = ($this->_config->get_boolean('browsercache.cssjs.etag') && $this->_config->get_boolean('browsercache.html.etag') && $this->_config->get_boolean('browsercache.other.etag'));
        $browsercache_w3tc = ($this->_config->get_boolean('browsercache.cssjs.w3tc') && $this->_config->get_boolean('browsercache.html.w3tc') && $this->_config->get_boolean('browsercache.other.w3tc'));
        $browsercache_compression = ($this->_config->get_boolean('browsercache.cssjs.compression') && $this->_config->get_boolean('browsercache.html.compression') && $this->_config->get_boolean('browsercache.other.compression'));
        $browsercache_replace = ($this->_config->get_boolean('browsercache.cssjs.replace') && $this->_config->get_boolean('browsercache.other.replace'));
        $browsercache_update_media_qs = ($this->_config->get_boolean('browsercache.cssjs.replace') || $this->_config->get_boolean('browsercache.other.replace'));
        $browsercache_nocookies =
            ($this->_config->get_boolean('browsercache.cssjs.nocookies') &&
            $this->_config->get_boolean('browsercache.other.nocookies'));

        include W3TC_INC_DIR . '/options/browsercache.php';
    }

    /**
     * Mobile tab
     *
     * @return void
     */
    function options_mobile() {
        $groups = $this->_config->get_array('mobile.rgroups');

        $w3_mobile = w3_instance('W3_Mobile');
        $themes = $w3_mobile->get_themes();

        include W3TC_INC_DIR . '/options/mobile.php';
    }

    /**
     * Referrer tab
     *
     * @return void
     */
    function options_referrer() {
        $groups = $this->_config->get_array('referrer.rgroups');

        $w3_referrer = w3_instance('W3_Referrer');

        $themes = $w3_referrer->get_themes();

        include W3TC_INC_DIR . '/options/referrer.php';
    }

    /**
     * CDN tab
     *
     * @return void
     */
    function options_cdn() {
        $cdn_enabled = $this->_config->get_boolean('cdn.enabled');
        $cdn_engine = $this->_config->get_string('cdn.engine');
        $cdn_mirror = w3_is_cdn_mirror($cdn_engine);
        $cdn_mirror_purge_all = w3_cdn_can_purge_all($cdn_engine);
        $cdn_common = w3_instance('W3_Plugin_CdnCommon');

        $cdn = $cdn_common->get_cdn();
        $cdn_supports_header = $cdn->headers_support() == W3TC_CDN_HEADER_MIRRORING;
        $cdn_supports_full_page_mirroring = $cdn->supports_full_page_mirroring();
        $minify_enabled = ($this->_config->get_boolean('minify.enabled') && $this->_config->get_boolean('minify.rewrite') && (!$this->_config->get_boolean('minify.auto') || w3_is_cdn_mirror($this->_config->get_string('cdn.engine'))));

        $cookie_domain = $this->get_cookie_domain();
        $set_cookie_domain = $this->is_cookie_domain_enabled();

        // Required for Update Media Query String button
        $browsercache_enabled = $this->_config->get_boolean('browsercache.enabled');
        $browsercache_update_media_qs = ($this->_config->get_boolean('browsercache.cssjs.replace') || $this->_config->get_boolean('browsercache.other.replace'));
        if (in_array($cdn_engine, array('netdna', 'maxcdn'))) {
            $authorization_key = $this->_config->get_string("cdn.$cdn_engine.authorization_key");
            $zone_id = $this->_config->get_integer("cdn.$cdn_engine.zone_id");
            $alias = $consumerkey = $consumersecret = '';

            if ($authorization_key) {
                $keys = explode('+', $authorization_key);
                if (sizeof($keys) == 3) {
                    list($alias, $consumerkey, $consumersecret) =  $keys;
                }
            }

            $authorized = $authorization_key != '' && $alias && $consumerkey && $consumersecret;
            $have_zone = $zone_id != 0;
            if ($authorized) {
                w3_require_once(W3TC_LIB_NETDNA_DIR . '/NetDNA.php');

                $api = new NetDNA($alias, $consumerkey, $consumersecret);
                $pull_zones = $api->get_zones_by_url(w3_get_home_url());
            }
        }
        include W3TC_INC_DIR . '/options/cdn.php';
    }

    /**
     * New Relic tab
     */
    function options_monitoring() {
        $applications = array();
        $dashboard = '';
        /**
         * @var $nerser W3_NewRelicService
         */
        $nerser = w3_instance('W3_NewRelicService');
        $new_relic_configured = $this->_config->get_string('newrelic.account_id') &&
                                $this->_config->get_string('newrelic.api_key') &&
                                $this->_config->get_string('newrelic.application_id');
        $view_application = $this->_config->get_string('newrelic.application_id');
        $new_relic_enabled = $this->_config->get_boolean('newrelic.enabled');
        $verify_running = $nerser->verify_running();
        $application_settings = array();
        if (!is_array($verify_running)) {
            try {
                $application_settings = $nerser->get_application_settings();
            }catch(Exception $ex) {
                $application_settings = array();
            }
        }
        if ($view_metric = W3_Request::get_boolean('view_metric', false)) {
            $metric_names = $nerser->get_metric_names(W3_Request::get_string('regex', ''));
        }
        include W3TC_INC_DIR . '/options/new_relic.php';
    }

    /**
     * FAQ tab
     *
     * @return void
     */
    function options_faq() {
        w3_require_once(W3TC_INC_FUNCTIONS_DIR . '/other.php');
        $faq = w3_parse_faq();

        include W3TC_INC_DIR . '/options/faq.php';
    }

    /**
     * Support tab
     *
     * @return void
     */
    function options_support() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $request_type = W3_Request::get_string('request_type');
        $payment = W3_Request::get_boolean('payment');

        include W3TC_INC_DIR . '/options/support.php';
    }

    /**
     * Install tab
     *
     * @return void
     */
    function options_install() {
        $rewrite_rules_descriptors = array();

        if (w3_can_check_rules()) {
            $e = w3_instance('W3_AdminEnvironment');
            $rewrite_rules_descriptors = $e->get_required_rules($this->_config);
        }

        include W3TC_INC_DIR . '/options/install.php';
    }

    /**
     * About tab
     *
     * @return void
     */
    function options_about() {
        include W3TC_INC_DIR . '/options/about.php';
    }

    /**
     * Flush all caches action
     *
     * @return void
     */
    function action_flush_all() {
        $this->flush_all();

        $this->redirect(array(
            'w3tc_note' => 'flush_all'
        ), true);
    }

    /**
     * Flsuh all caches except CloudFlare
     */
    function action_flush_all_except_cf() {
        $this->flush_all(false);

        $this->redirect(array(
            'w3tc_note' => 'flush_all_except_cf'
        ), true);
    }

    /**
     * Flush memcache cache action
     *
     * @return void
     */
    function action_flush_memcached() {
        $this->flush_memcached();

        $this->redirect(array(
            'w3tc_note' => 'flush_memcached'
        ), true);
    }

    /**
     * Flush opcode caches action
     *
     * @return void
     */
    function action_flush_opcode() {
        $this->flush_opcode();

        $this->redirect(array(
            'w3tc_note' => 'flush_opcode'
        ), true);
    }

    /**
     * Flush opcode caches action
     *
     * @return void
     */
    function action_flush_apc_system() {
        $this->flush_apc_system();

        $this->redirect(array(
            'w3tc_note' => 'flush_apc_system'
        ), true);
    }

    /**
     * Flush file caches action
     *
     * @return void
     */
    function action_flush_file() {
        $this->flush_file();

        $this->redirect(array(
            'w3tc_note' => 'flush_file'
        ), true);
    }

    /**
     * Flush page cache action
     *
     * @return void
     */
    function action_flush_pgcache() {
        $this->flush_pgcache();

        $this->_config->set('notes.need_empty_pgcache', false);
        $this->_config->set('notes.plugins_updated', false);

        $this->_config->save();

        $this->redirect(array(
            'w3tc_note' => 'flush_pgcache'
        ), true);
    }

    /**
     * Flush database cache action
     *
     * @return void
     */
    function action_flush_dbcache() {
        $this->flush_dbcache();

        $this->redirect(array(
            'w3tc_note' => 'flush_dbcache'
        ), true);
    }

    /**
     * Flush object cache action
     *
     * @return void
     */
    function action_flush_objectcache() {
        $this->flush_objectcache();

        $this->_config->set('notes.need_empty_objectcache', false);

        $this->_config->save();

        $this->redirect(array(
            'w3tc_note' => 'flush_objectcache'
        ), true);
    }


    /**
     * Flush fragment cache action
     *
     * @return void
     */
    function action_flush_fragmentcache() {
        $this->flush_fragmentcache();

        $this->_config->set('notes.need_empty_fragmentcache', false);

        $this->_config->save();

        $this->redirect(array(
            'w3tc_note' => 'flush_fragmentcache'
        ), true);
    }

    /**
     * Flush minify action
     *
     * @return void
     */
    function action_flush_minify() {
        $this->flush_minify();

        $this->_config->set('notes.need_empty_minify', false);

        $this->_config->save();

        $this->redirect(array(
            'w3tc_note' => 'flush_minify'
        ), true);
    }

    /**
     * Flush browser cache action
     *
     * @return void
     */
    function action_flush_browser_cache() {
        $this->flush_browser_cache();

        $this->redirect(array(
            'w3tc_note' => 'flush_browser_cache'
			), true);
    }

    /*
	 * Flush varnish cache
     */
    function action_flush_varnish() {
        $this->flush_varnish();

        $this->redirect(array(
            'w3tc_note' => 'flush_varnish'
        ), true);
    }

    /*
	 * Flush CDN mirror
     */
    function action_flush_cdn() {
        $this->flush_cdn();

        $this->redirect(array(
            'w3tc_note' => 'flush_cdn'
        ), true);
    }

    /**
     * Deactivates plugin
     **/
    function action_deactivate_plugin() {
        try {
            $environment = w3_instance('W3_AdminEnvironment');
            $environment->fix_after_deactivation();
            deactivate_plugins(plugin_basename(W3TC_FILE));
        } catch (SelfTestExceptions $exs) {
            $r = w3_parse_selftest_exceptions($exs);

            foreach ($r['before_errors'] as $e)
                $this->_errors[] = $e;

            if (strlen($r['required_changes']) > 0) {
                $changes_style = 'border: 1px solid black; ' .
                    'background: white; ' .
                    'margin: 10px 30px 10px 30px; ' . 
                    'padding: 10px; display: none';
                $ftp_style = 'border: 1px solid black; background: white; ' .
                    'margin: 10px 30px 10px 30px; ' . 
                    'padding: 10px; display: none';
                $ftp_form = str_replace('class="wrap"', '', 
                    $exs->credentials_form());
                $ftp_form = str_replace('<fieldset>', '', $ftp_form);
                $ftp_form = str_replace('</fieldset>', '', $ftp_form);
                $ftp_form = str_replace('id="upgrade" class="button"', 
                    'id="upgrade" class="button w3tc-button-save"', $ftp_form);
				$error = sprintf( __('<strong>W3 Total Cache Error:</strong>
		                    Files and directories could not be automatically
		                    deleted.
		                    <table>
		                    <tr>
		                    <td>Please execute commands manually</td>
		                    <td>
								%s
		                    </td>
		                    </tr>
		                    <tr>
		                    <td>or use FTP form to allow 
		                    <strong>W3 Total Cache</strong> make it automatically.
		                    </td>
		                    <td>
								%s
		                    </td>
		                    </tr></table>', 'w3-total-cache'), 
							$this->button(__('View required changes', 'w3-total-cache'), '', 'w3tc-show-required-changes'), 
							$this->button(__('Update via FTP', 'w3-total-cache'), '', 'w3tc-show-ftp-form')
						) . '<div class="w3tc-required-changes" style="' . 
                		$changes_style . '">' . $r['required_changes'] . '</div>' .
                		'<div class="w3tc-ftp-form" style="' . $ftp_style . '">' . 
                		$ftp_form . '</div>';

                $this->_errors[] = $error;
                echo 'set _errors';
            }
        }
    }

    /**
     * Import config action
     *
     * @return void
     */
    function action_config_import() {
        $error = '';

        @$config = new W3_Config();

        if (!isset($_FILES['config_file']['error']) || $_FILES['config_file']['error'] == UPLOAD_ERR_NO_FILE) {
            $error = 'config_import_no_file';
        } elseif ($_FILES['config_file']['error'] != UPLOAD_ERR_OK) {
            $error = 'config_import_upload';
        } else {
            ob_start();
            $imported = $config->import($_FILES['config_file']['tmp_name']);
            ob_end_clean();

            if (!$imported) {
                $error = 'config_import_import';
            }
        }

        if ($error) {
            $this->redirect(array(
                'w3tc_error' => $error
            ), true);
        }

        $this->config_save($config, $this->_config_admin);
        $this->redirect(array(
            'w3tc_note' => 'config_import'
        ), true);
    }

    /**
     * Export config action
     *
     * @return void
     */
    function action_config_export() {
        @header(sprintf(__('Content-Disposition: attachment; filename=%s.php', 'w3-total-cache'), w3_get_blog_id()));
        echo $this->_config->export();
        die();
    }

    /**
     * Reset config action
     *
     * @return void
     */
    function action_config_reset() {
        @$config = new W3_Config();
        $config->set_defaults();
        $this->config_save($config, $this->_config_admin);
        $this->redirect(array(
            'w3tc_note' => 'config_reset'
        ), true);
    }

    /**
     * Save preview option
     *
     * @return void
     */
    function action_preview_enable() {
        $this->_config->preview_production_copy(-1);
        $this->_config_admin->set('previewmode.enabled', true);
        $this->_config_admin->save();
        $this->redirect(array(
            'w3tc_note' => 'preview_enable'
        ));
    }

    /**
     * Save preview option
     *
     * @return void
     */
    function action_preview_disable() {
        $this->_config->preview_production_copy(1, true);
        $this->_config_admin->set('previewmode.enabled', false);
        $this->_config_admin->save();
        $this->redirect(array(
            'w3tc_note' => 'preview_disable'
        ));
    }

    /**
     * Deploy preview settings action
     *
     * @return void
     */
    function action_preview_deploy() {
        $this->_config->preview_production_copy(1);
        $this->flush_all();

        $this->redirect(array(
            'w3tc_note' => 'preview_deploy'
        ));
    }

    /**
     * Support select action
     *
     * @return void
     */
    function action_support_select() {
        include W3TC_INC_DIR . '/options/support/select.php';
    }

    /**
     * Support payment action
     *
     * @return void
     */
    function action_support_payment() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $request_type = W3_Request::get_string('request_type');

        if (!isset($this->_request_types[$request_type])) {
            $request_type = 'bug_report';
        }

        $request_id = date('YmdHi');
        $return_url = admin_url('admin.php?page=w3tc_support&request_type=' . $request_type . '&payment=1&request_id=' . $request_id);
        $cancel_url = admin_url('admin.php?page=w3tc_dashboard');

        include W3TC_INC_DIR . '/options/support/payment.php';
    }

    /**
     * Support form action
     *
     * @return void
     */
    function action_support_form() {
        global $current_user;

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $name = '';
        $email = '';
        $request_type = W3_Request::get_string('request_type');

        if (!isset($this->_request_types[$request_type])) {
            $request_type = 'bug_report';
        }

        if (is_a($current_user, 'WP_User')) {
            if ($current_user->first_name) {
                $name = $current_user->first_name;
            }

            if ($current_user->last_name) {
                $name .= ($name != '' ? ' ' : '') . $current_user->last_name;
            }

            if ($name == 'admin') {
                $name = '';
            }

            if ($current_user->user_email) {
                $email = $current_user->user_email;
            }
        }

        $theme = w3tc_get_current_theme();
        $template_files = (isset($theme['Template Files']) ? (array) $theme['Template Files'] : array());

        $ajax = W3_Request::get_boolean('ajax');
        $request_id = W3_Request::get_string('request_id', date('YmdHi'));
        $payment = W3_Request::get_boolean('payment');
        $url = W3_Request::get_string('url', w3_get_domain_url());
        $name = W3_Request::get_string('name', $name);
        $email = W3_Request::get_string('email', $email);
        $twitter = W3_Request::get_string('twitter');
        $phone = W3_Request::get_string('phone');
        $subject = W3_Request::get_string('subject');
        $description = W3_Request::get_string('description');
        $templates = W3_Request::get_array('templates');
        $forum_url = W3_Request::get_string('forum_url');
        $wp_login = W3_Request::get_string('wp_login');
        $wp_password = W3_Request::get_string('wp_password');
        $ftp_host = W3_Request::get_string('ftp_host');
        $ftp_login = W3_Request::get_string('ftp_login');
        $ftp_password = W3_Request::get_string('ftp_password');
        $subscribe_releases = W3_Request::get_string('subscribe_releases');
        $subscribe_customer = W3_Request::get_string('subscribe_customer');

        include W3TC_INC_DIR . '/options/support/form.php';
    }

    /**
     * Send support request action
     *
     * @return void
     */
    function action_support_request() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $request_type = W3_Request::get_string('request_type');
        $payment = W3_Request::get_boolean('payment');
        $request_id = W3_Request::get_string('request_id');
        $url = W3_Request::get_string('url');
        $name = W3_Request::get_string('name');
        $email = W3_Request::get_string('email');
        $twitter = W3_Request::get_string('twitter');
        $phone = W3_Request::get_string('phone');
        $subject = W3_Request::get_string('subject');
        $description = W3_Request::get_string('description');
        $templates = W3_Request::get_array('templates');
        $forum_url = W3_Request::get_string('forum_url');
        $wp_login = W3_Request::get_string('wp_login');
        $wp_password = W3_Request::get_string('wp_password');
        $ftp_host = W3_Request::get_string('ftp_host');
        $ftp_login = W3_Request::get_string('ftp_login');
        $ftp_password = W3_Request::get_string('ftp_password');
        $subscribe_releases = W3_Request::get_string('subscribe_releases');
        $subscribe_customer = W3_Request::get_string('subscribe_customer');

        $params = array(
            'request_type' => $request_type,
            'payment' => $payment,
            'url' => $url,
            'name' => $name,
            'email' => $email,
            'twitter' => $twitter,
            'phone' => $phone,
            'subject' => $subject,
            'description' => $description,
            'forum_url' => $forum_url,
            'wp_login' => $wp_login,
            'wp_password' => $wp_password,
            'ftp_host' => $ftp_host,
            'ftp_login' => $ftp_login,
            'ftp_password' => $ftp_password,
            'subscribe_releases' => $subscribe_releases,
            'subscribe_customer' => $subscribe_customer
        );

        $post = $params;
        foreach ($templates as $template_index => $template) {
            $template_key = sprintf('templates[%d]', $template_index);
            $params[$template_key] = $template;
        }

        if (!isset($this->_request_types[$request_type])) {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_type'
            )));
        }

        $required = array(
            'bug_report' => 'url,name,email,subject,description',
            'new_feature' => 'url,name,email,subject,description',
            'email_support' => 'url,name,email,subject,description',
            'phone_support' => 'url,name,email,subject,description,phone',
            'plugin_config' => 'url,name,email,subject,description,wp_login,wp_password',
            'theme_config' => 'url,name,email,subject,description,wp_login,wp_password,ftp_host,ftp_login,ftp_password',
            'linux_config' => 'url,name,email,subject,description,wp_login,wp_password,ftp_host,ftp_login,ftp_password'
        );

        if (strstr($required[$request_type], 'url') !== false && $url == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_url'
            )));
        }

        if (strstr($required[$request_type], 'name') !== false && $name == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_name'
            )));
        }

        if (strstr($required[$request_type], 'email') !== false && !preg_match('~^[a-z0-9_\-\.]+@[a-z0-9-\.]+\.[a-z]{2,5}$~', $email)) {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_email'
            )));
        }

        if (strstr($required[$request_type], 'phone') !== false && !preg_match('~^[0-9\-\.\ \(\)\+]+$~', $phone)) {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_phone'
            )));
        }

        if (strstr($required[$request_type], 'subject') !== false && $subject == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_subject'
            )));
        }

        if (strstr($required[$request_type], 'description') !== false && $description == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_description'
            )));
        }

        if (strstr($required[$request_type], 'wp_login') !== false && $wp_login == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_wp_login'
            )));
        }

        if (strstr($required[$request_type], 'wp_password') !== false && $wp_password == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_wp_password'
            )));
        }

        if (strstr($required[$request_type], 'ftp_host') !== false && $ftp_host == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_ftp_host'
            )));
        }

        if (strstr($required[$request_type], 'ftp_login') !== false && $ftp_login == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_ftp_login'
            )));
        }

        if (strstr($required[$request_type], 'ftp_password') !== false && $ftp_password == '') {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => 'support_request_ftp_password'
            )));
        }

        /**
         * Add attachments
         */
        $attachments = array();

        $attach_files = array(
            /**
             * Attach WP config file
             */
            w3_get_wp_config_path(),

            /**
             * Attach minify file
             */
            w3_cache_blog_dir('log') . '/minify.log',

            /**
             * Attach .htaccess files
             */
            w3_get_pgcache_rules_core_path(),
            w3_get_pgcache_rules_cache_path(),
            w3_get_browsercache_rules_cache_path(),
            w3_get_browsercache_rules_no404wp_path(),
            w3_get_minify_rules_core_path(),
            w3_get_minify_rules_cache_path()
        );

        /**
         * Attach config files
         */
        if ($handle = opendir(W3TC_CONFIG_DIR)) {
            while (($entry = @readdir($handle)) !== false) {
                if ($entry == '.' || $entry == '..' || $entry == 'index.html')
                    continue;

                $attachments[] = W3TC_CONFIG_DIR . '/' . $entry;
            }
            closedir($handle);
        }


        foreach ($attach_files as $attach_file) {
            if ($attach_file && file_exists($attach_file) && !in_array($attach_file, $attachments)) {
                $attachments[] = $attach_file;
            }
        }

        /**
         * Attach server info
         */
        $server_info = print_r($this->get_server_info(), true);
        $server_info = str_replace("\n", "\r\n", $server_info);

        $server_info_path = W3TC_CACHE_TMP_DIR . '/server_info.txt';

        if (@file_put_contents($server_info_path, $server_info)) {
            $attachments[] = $server_info_path;
        }

        /**
         * Attach phpinfo
         */
        ob_start();
        phpinfo();
        $php_info = ob_get_contents();
        ob_end_clean();

        $php_info_path = W3TC_CACHE_TMP_DIR . '/php_info.html';

        if (@file_put_contents($php_info_path, $php_info)) {
            $attachments[] = $php_info_path;
        }

        /**
         * Attach self-test
         */
        ob_start();
        $this->action_self_test();
        $self_test = ob_get_contents();
        ob_end_clean();

        $self_test_path = W3TC_CACHE_TMP_DIR . '/self_test.html';

        if (@file_put_contents($self_test_path, $self_test)) {
            $attachments[] = $self_test_path;
        }

        /**
         * Attach templates
         */
        foreach ($templates as $template) {
            if (!empty($template)) {
                $attachments[] = $template;
            }
        }

        /**
         * Attach other files
         */
        if (!empty($_FILES['files'])) {
            $files = (array) $_FILES['files'];
            for ($i = 0, $l = count($files); $i < $l; $i++) {
                if (isset($files['tmp_name'][$i]) && isset($files['name'][$i]) && isset($files['error'][$i]) && $files['error'][$i] == UPLOAD_ERR_OK) {
                    $path = W3TC_CACHE_TMP_DIR . '/' . $files['name'][$i];
                    if (@move_uploaded_file($files['tmp_name'][$i], $path)) {
                        $attachments[] = $path;
                    }
                }
            }
        }

        $data = array();

        if (!empty($wp_login) && !empty($wp_password)) {
            $data['WP Admin login'] = $wp_login;
            $data['WP Admin password'] = $wp_password;
        }

        if (!empty($ftp_host) && !empty($ftp_login) && !empty($ftp_password)) {
            $data['SSH / FTP host'] = $ftp_host;
            $data['SSH / FTP login'] = $ftp_login;
            $data['SSH / FTP password'] = $ftp_password;
        }

        /**
         * Store request data for future access
         */
        if (count($data)) {
            $hash = md5(microtime());
            $request_data = get_option('w3tc_request_data', array());
            $request_data[$hash] = $data;

            update_option('w3tc_request_data', $request_data);

            $request_data_url = sprintf('%s/w3tc_request_data/%s', w3_get_home_url(), $hash);
        } else {
            $request_data_url = null;
        }

        $nonce =  wp_create_nonce('w3tc_support_request');
        if (is_network_admin()) {
            update_site_option('w3tc_support_request', $nonce);
        } else {
            update_option('w3tc_support_request', $nonce);
        }
        $post['file_access'] = WP_PLUGIN_URL . '/' . dirname(W3TC_FILE) . '/pub/files.php';
        $post['nonce'] = $nonce;
        $post['request_data_url'] = $request_data_url;
        $post['ip'] = $_SERVER['REMOTE_ADDR'];
        $post['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $post['version'] = W3TC_VERSION;
        $post['plugin'] = 'W3 Total Cache';
        $post['request_id'] = $request_id;

        $unset = array('wp_login', 'wp_password', 'ftp_host', 'ftp_login', 'ftp_password');

        foreach($unset as $key)
            unset($post[$key]);

        foreach ($attachments as $attachment) {
            if (is_network_admin())
                update_site_option('attachment_'. md5($attachment), $attachment);
            else
                update_option('attachment_'. md5($attachment), $attachment);
        }
        $post = array_merge($post, array('files' => $attachments));

        if (defined('W3_SUPPORT_DEBUG') && W3_SUPPORT_DEBUG) {
            $data = sprintf("[%s] Post support request\n" ,date('r'));
            foreach ($post as $key => $value)
                $data .= sprintf("%s => %s\n" ,$key, is_array($value) ? implode(',' , $value) : $value);
            $filename = w3_cache_blog_dir('log') . '/support.log';
            if (!is_dir(dirname($filename)))
                w3_mkdir_from(dirname($filename), W3TC_CACHE_DIR);

            @file_put_contents($filename, $data, FILE_APPEND);
        }

        $response = wp_remote_post(W3TC_SUPPORT_REQUEST_URL, array('body' => $post, 'timeout' => $this->_config->get_integer('timelimit.email_send')));

        if (defined('W3_SUPPORT_DEBUG') && W3_SUPPORT_DEBUG) {
            $filename = w3_cache_blog_dir('log') . '/support.log';
            $data = sprintf("[%s] Post response %s %s\n" ,date('r'), $response['response']['code'], $response['body']);
            @file_put_contents($filename, $data, FILE_APPEND);
        }

        if (!is_wp_error($response))
            $result = $response['response']['code'] == 200 && $response['body'] == 'Ok';
        else
            $result = false;
        /**
         * Remove temporary files
         */
        foreach ($attachments as $attachment) {
            if (strstr($attachment, W3TC_CACHE_TMP_DIR) !== false) {
                @unlink($attachment);
            }
            if (is_network_admin())
                delete_site_option('attachment_'. md5($attachment));
            else
                delete_option('attachment_'. md5($attachment));
        }

        if (is_network_admin())
            delete_site_option('w3tc_support_request');
        else
            delete_option('w3tc_support_request');

        if ($result) {
            $this->redirect(array(
                'tab' => 'general',
                'w3tc_note' => 'support_request'
            ));
        } else {
            $this->redirect(array_merge($params, array(
                'request_type' => $request_type,
                'w3tc_error' => 'support_request'
            )));
        }
    }

    /**
     * CDN queue action
     *
     * @return void
     */
    function action_cdn_queue() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');
        $cdn_queue_action = W3_Request::get_string('cdn_queue_action');
        $cdn_queue_tab = W3_Request::get_string('cdn_queue_tab');

        $notes = array();

        switch ($cdn_queue_tab) {
            case 'upload':
            case 'delete':
            case 'purge':
                break;

            default:
                $cdn_queue_tab = 'upload';
        }

        switch ($cdn_queue_action) {
            case 'delete':
                $cdn_queue_id = W3_Request::get_integer('cdn_queue_id');
                if (!empty($cdn_queue_id)) {
                    $w3_plugin_cdn->queue_delete($cdn_queue_id);
                    $notes[] = __('File successfully deleted from the queue.', 'w3-total-cache');
                }
                break;

            case 'empty':
                $cdn_queue_type = W3_Request::get_integer('cdn_queue_type');
                if (!empty($cdn_queue_type)) {
                    $w3_plugin_cdn->queue_empty($cdn_queue_type);
                    $notes[] = __('Queue successfully emptied.', 'w3-total-cache');
                }
                break;

            case 'process':
                $w3_plugin_cdn_normal = w3_instance('W3_Plugin_Cdn');
                $n = $w3_plugin_cdn_normal->cron_queue_process();
                $notes[] = sprintf(__('Number of processed queue items: %d', 'w3-total-cache'), $n);
                break;
        }

        $nonce = wp_create_nonce('w3tc');
        $queue = $w3_plugin_cdn->queue_get();
        $title = __('Unsuccessful file transfer queue.', 'w3-total-cache');

        include W3TC_INC_DIR . '/popup/cdn_queue.php';
    }

    /**
     * CDN export library action
     *
     * @return void
     */
    function action_cdn_export_library() {
        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        $total = $w3_plugin_cdn->get_attachments_count();
        $title = __('Media Library export', 'w3-total-cache');

        include W3TC_INC_DIR . '/popup/cdn_export_library.php';
    }

    /**
     * CDN export library process
     *
     * @return void
     */
    function action_cdn_export_library_process() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        $limit = W3_Request::get_integer('limit');
        $offset = W3_Request::get_integer('offset');

        $count = null;
        $total = null;
        $results = array();

        @$w3_plugin_cdn->export_library($limit, $offset, $count, $total, $results);

        $response = array(
            'limit' => $limit,
            'offset' => $offset,
            'count' => $count,
            'total' => $total,
            'results' => $results
        );

        echo json_encode($response);
    }

    /**
     * CDN import library action
     *
     * @return void
     */
    function action_cdn_import_library() {
        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');
        $w3_plugin_cdncommon = w3_instance('W3_Plugin_CdnCommon');

        $cdn = $w3_plugin_cdncommon->get_cdn();

        $total = $w3_plugin_cdn->get_import_posts_count();
        $cdn_host = $cdn->get_domain();

        $title = __('Media Library import', 'w3-total-cache');

        include W3TC_INC_DIR . '/popup/cdn_import_library.php';
    }

    /**
     * CDN import library process
     *
     * @return void
     */
    function action_cdn_import_library_process() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        $limit = W3_Request::get_integer('limit');
        $offset = W3_Request::get_integer('offset');

        $count = null;
        $total = null;
        $results = array();

        @$w3_plugin_cdn->import_library($limit, $offset, $count, $total, $results);

        $response = array(
            'limit' => $limit,
            'offset' => $offset,
            'count' => $count,
            'total' => $total,
            'results' => $results
        );

        echo json_encode($response);
    }

    /**
     * CDN rename domain action
     *
     * @return void
     */
    function action_cdn_rename_domain() {
        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        $total = $w3_plugin_cdn->get_rename_posts_count();

        $title = __('Modify attachment URLs', 'w3-total-cache');

        include W3TC_INC_DIR . '/popup/cdn_rename_domain.php';
    }

    /**
     * CDN rename domain process
     *
     * @return void
     */
    function action_cdn_rename_domain_process() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        $limit = W3_Request::get_integer('limit');
        $offset = W3_Request::get_integer('offset');
        $names = W3_Request::get_array('names');

        $count = null;
        $total = null;
        $results = array();

        @$w3_plugin_cdn->rename_domain($names, $limit, $offset, $count, $total, $results);

        $response = array(
            'limit' => $limit,
            'offset' => $offset,
            'count' => $count,
            'total' => $total,
            'results' => $results
        );

        echo json_encode($response);
    }

    /**
     * CDN export action
     *
     * @return void
     */
    function action_cdn_export() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdn = w3_instance('W3_Plugin_Cdn');

        $cdn_export_type = W3_Request::get_string('cdn_export_type', 'custom');

        switch ($cdn_export_type) {
            case 'includes':
                $title = __('Includes files export', 'w3-total-cache');
                $files = $w3_plugin_cdn->get_files_includes();
                break;

            case 'theme':
                $title = __('Theme files export', 'w3-total-cache');
                $files = $w3_plugin_cdn->get_files_theme();
                break;

            case 'minify':
                $title = __('Minify files export', 'w3-total-cache');
                $files = $w3_plugin_cdn->get_files_minify();
                break;

            default:
            case 'custom':
                $title = __('Custom files export', 'w3-total-cache');
                $files = $w3_plugin_cdn->get_files_custom();
                break;
        }

        include W3TC_INC_DIR . '/popup/cdn_export_file.php';
    }

    /**
     * CDN export process
     *
     * @return void
     */
    function action_cdn_export_process() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $w3_plugin_cdncommon = w3_instance('W3_Plugin_CdnCommon');

        $files = W3_Request::get_array('files');

        $upload = array();
        $results = array();

        foreach ($files as $file) {
            $local_path = $w3_plugin_cdncommon->docroot_filename_to_absolute_path($file);
            $remote_path = $w3_plugin_cdncommon->uri_to_cdn_uri($w3_plugin_cdncommon->docroot_filename_to_uri($file));
            $upload[] = $w3_plugin_cdncommon->build_file_descriptor($local_path, $remote_path);
        }

        $w3_plugin_cdncommon->upload($upload, false, $results);

        $response = array(
            'results' => $results
        );

        echo json_encode($response);
    }

    /**
     * CDN purge action
     *
     * @return void
     */
    function action_cdn_purge() {
        $title = __('Content Delivery Network (CDN): Purge Tool', 'w3-total-cache');
        $results = array();

        include W3TC_INC_DIR . '/popup/cdn_purge.php';
    }

    /**
     * CDN purge post action
     *
     * @return void
     */
    function action_cdn_purge_post() {
        $title = __('Content Delivery Network (CDN): Purge Tool', 'w3-total-cache');
        $results = array();

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $files = W3_Request::get_array('files');

        $purge = array();

        $w3_plugin_cdncommon = w3_instance('W3_Plugin_CdnCommon');

        foreach ($files as $file) {
            $local_path = $w3_plugin_cdncommon->docroot_filename_to_absolute_path($file);
            $remote_path = $w3_plugin_cdncommon->uri_to_cdn_uri($w3_plugin_cdncommon->docroot_filename_to_uri($file));

            $purge[] = $w3_plugin_cdncommon->build_file_descriptor($local_path, $remote_path);
        }

        if (count($purge)) {
            $w3_plugin_cdncommon->purge($purge, false, $results);
        } else {
            $errors[] = __('Empty files list.', 'w3-total-cache');
        }

        include W3TC_INC_DIR . '/popup/cdn_purge.php';
    }

    /**
     * CDN Purge Post
     *
     * @return void
     */
    function action_cdn_purge_attachment() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $results = array();
        $attachment_id = W3_Request::get_integer('attachment_id');

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        if ($w3_plugin_cdn->purge_attachment($attachment_id, $results)) {
            $this->redirect(array(
                'w3tc_note' => 'cdn_purge_attachment'
            ), true);
        } else {
            $this->redirect(array(
                'w3tc_error' => 'cdn_purge_attachment'
            ), true);
        }
    }

    /**
     * CDN Test action
     *
     * @return void
     */
    function action_cdn_test() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');
        w3_require_once(W3TC_LIB_W3_DIR . '/Cdn.php');

        $engine = W3_Request::get_string('engine');
        $config = W3_Request::get_array('config');

        $config = array_merge($config, array(
            'debug' => false
        ));

        if (w3_is_cdn_engine($engine)) {
            $result = true;
        } else {
            $result = false;
            $error = __('Incorrect engine.', 'w3-total-cache');
        }

        if ($result) {
            $w3_cdn = W3_Cdn::instance($engine, $config);
            $error = null;

            @set_time_limit($this->_config->get_integer('timelimit.cdn_test'));

            if ($w3_cdn->test($error)) {
                $result = true;
                $error = __('Test passed', 'w3-total-cache');
            } else {
                $result = false;
                $error = sprintf(__('Error: %s', 'w3-total-cache'), $error);
            }
        }

        $response = array(
            'result' => $result,
            'error' => $error
        );

        echo json_encode($response);
    }

    /**
     * Save dbcluster config action
     *
     * @return void
     */
    function action_dbcluster_config_save() {
        $params = array('page' => 'w3tc_general');

        if (!file_put_contents(W3TC_FILE_DB_CLUSTER_CONFIG,
                 stripslashes($_REQUEST['newcontent']))) {
            w3_require_once(W3TC_INC_DIR . '/functions/activation.php');
            try {
                w3_throw_on_write_error(W3TC_FILE_DB_CLUSTER_CONFIG);
            } catch (Exception $e) {
                $error = $e->getMessage();
	    	    $this->redirect_with_custom_messages($params, array($error));
            }
        }

        $this->redirect_with_custom_messages($params, null,
            array(__('Database Cluster configuration file has been successfully saved', 'w3-total-cache')));
    }

    /**
     * Create container action
     *
     * @return void
     */
    function action_cdn_create_container() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');
        w3_require_once(W3TC_LIB_W3_DIR . '/Cdn.php');

        $engine = W3_Request::get_string('engine');
        $config = W3_Request::get_array('config');

        $config = array_merge($config, array(
            'debug' => false
        ));

        $result = false;
        $error = __('Incorrect type.', 'w3-total-cache');
        $container_id = '';

        switch ($engine) {
            case 's3':
            case 'cf':
            case 'cf2':
            case 'rscf':
            case 'azure':
                $result = true;
                break;
        }

        if ($result) {
            $w3_cdn = W3_Cdn::instance($engine, $config);

            @set_time_limit($this->_config->get_integer('timelimit.cdn_container_create'));

            if ($w3_cdn->create_container($container_id, $error)) {
                $result = true;
                $error = __('Created successfully.', 'w3-total-cache');
            } else {
                $result = false;
                $error = sprintf(__('Error: %s', 'w3-total-cache'), $error);
            }
        }

        $response = array(
            'result' => $result,
            'error' => $error,
            'container_id' => $container_id
        );

        echo json_encode($response);
    }

    /**
     * S3 bucket location lightbox
     *
     * @return void
     */
    function action_cdn_s3_bucket_location() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $type = W3_Request::get_string('type', 's3');

        $locations = array(
            '' => 'US (Default)',
            'us-west-1' => __('US-West (Northern California)', 'w3-total-cache'),
            'EU' => 'Europe',
            'ap-southeast-1' => __('AP-SouthEast (Singapore)', 'w3-total-cache'),
        );

        include W3TC_INC_DIR . '/lightbox/cdn_s3_bucket_location.php';
    }

    /**
     * CDN OAuth redirect popup
     *
     * @return void
     */
    function action_cdn_oauth() {
        require_once W3TC_LIB_W3_DIR . '/Request.php';
        include W3TC_LIB_OAUTH_DIR . '/OAuthService.php';
        $type = W3_Request::get_string('type');

        $oauthClient = OAuthService::get_oauth_client($type);
        $oauthClient->authorize();
    }

    /**
     * Get CDN OAuth access and retrieve API Credentials
     *
     * @return void
     */
    function action_cdn_oauth_access(){
        require_once W3TC_LIB_W3_DIR . '/Request.php';
        include W3TC_LIB_OAUTH_DIR . '/OAuthService.php';

        $type = W3_Request::get_string('type');

        $oauthClient = OAuthService::get_oauth_client($type);
        $oauthClient->print_javascript();
    }


    function action_create_netdna_maxcdn_pull_zone() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');
        $type = W3_Request::get_string('type');
        $name = W3_Request::get_string('name');
        $label = W3_Request::get_string('label');
        w3_require_once(W3TC_LIB_NETDNA_DIR . '/NetDNA.php');
        $cdn_engine= $this->_config->get_string('cdn.engine');

        $authorization_key = $this->_config->get_string("cdn.$cdn_engine.authorization_key");
        $alias = $consumerkey = $consumersecret = '';

        if ($authorization_key) {
            $keys = explode('+', $authorization_key);
            if (sizeof($keys) == 3) {
                list($alias, $consumerkey, $consumersecret) =  $keys;
            }
        }
        $api = new NetDNA($alias, $consumerkey, $consumersecret);
        $url = w3_get_home_url();
        $zone = array();
        $zone['name'] = $name;
        $zone['label'] = $label;
        $zone['url'] = $url;
        try {
            $response = $api->create_pull_zone($zone);
            try {
                if (!$this->_config->get_array("cdn.$cdn_engine.domain")) {
                    $this->_config->set("cdn.$cdn_engine.domain", array("$name.$alias.netdna-cdn.com"));
                    $this->_config->save();
                }
            } catch (Exception $ex) {}
            echo json_encode(array('status' => 'success','message' => 'Pull Zone created.', 'temporary_url' => "$name.$alias.netdna-cdn.com", 'data' => $response));
        } catch (Exception $ex) {
            echo json_encode(array('status' => 'error','message' => $ex->getMessage()));
        }
    }

    function action_create_netdna_maxcdn_pull_zone_form() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $type = W3_Request::get_string('type', 'maxcdn');
        include W3TC_INC_DIR . '/lightbox/create_netdna_maxcdn_pull_zone.php';
    }

    /**
     * Test memcached
     *
     * @return void
     */
    function action_test_memcached() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $servers = W3_Request::get_array('servers');

        if ($this->is_memcache_available($servers)) {
            $result = true;
            $error = __('Test passed.', 'w3-total-cache');
        } else {
            $result = false;
            $error = __('Test failed.', 'w3-total-cache');
        }

        $response = array(
            'result' => $result,
            'error' => $error
        );

        echo json_encode($response);
    }

    /**
     * Test minifier action
     *
     * @return void
     */
    function action_test_minifier() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $engine = W3_Request::get_string('engine');
        $path_java = W3_Request::get_string('path_java');
        $path_jar = W3_Request::get_string('path_jar');

        $result = false;
        $error = '';

        if (!$path_java) {
            $error = __('Empty JAVA executable path.', 'w3-total-cache');
        } elseif (!$path_jar) {
            $error = __('Empty JAR file path.', 'w3-total-cache');
        } else {
            switch ($engine) {
                case 'yuijs':
                    w3_require_once(W3TC_LIB_MINIFY_DIR . '/Minify/YUICompressor.php');

                    Minify_YUICompressor::setPathJava($path_java);
                    Minify_YUICompressor::setPathJar($path_jar);

                    $result = Minify_YUICompressor::testJs($error);
                    break;

                case 'yuicss':
                    w3_require_once(W3TC_LIB_MINIFY_DIR . '/Minify/YUICompressor.php');

                    Minify_YUICompressor::setPathJava($path_java);
                    Minify_YUICompressor::setPathJar($path_jar);

                    $result = Minify_YUICompressor::testCss($error);
                    break;

                case 'ccjs':
                    w3_require_once(W3TC_LIB_MINIFY_DIR . '/Minify/ClosureCompiler.php');

                    Minify_ClosureCompiler::setPathJava($path_java);
                    Minify_ClosureCompiler::setPathJar($path_jar);

                    $result = Minify_ClosureCompiler::test($error);
                    break;

                default:
                    $error = __('Invalid engine.', 'w3-total-cache');
                    break;
            }
        }

        $response = array(
            'result' => $result,
            'error' => $error
        );

        echo json_encode($response);
    }

    /**
     * Hide note action
     *
     * @return void
     */
    function action_hide_note() {
        $note = W3_Request::get_string('note');
        $admin = W3_Request::get_boolean('admin');
        $setting = sprintf('notes.%s', $note);
        if ($admin) {
            $this->_config_admin->set($setting, false);
            $this->_config_admin->save();
        } else {
            $this->_config->set($setting, false);
            $this->_config->save();
        }
        $this->redirect(array(), true);
    }

    /**
     * Update upload path action
     *
     * @return void
     */
    function action_update_upload_path() {
        update_option('upload_path', '');

        $this->redirect();
    }

    /**
     * Options save action
     *
     * @return void
     */
    function action_save_options() {
        /**
         * Redirect params
         */
        $params = array();

        /**
         * Store error message regarding permalink not enabled
         */
        $redirect_permalink_error = '';

        /**
         * Read config
         * We should use new instance of WP_Config object here
         */
        $config = new W3_Config();
        if (!$this->_config->own_config_exists()) {
            $config->import_legacy_config();
        }

        $this->read_request($config);

        $config_admin = new W3_ConfigAdmin();
        $this->read_request($config_admin);

        if ($this->_page == 'w3tc_dashboard') {
            if (W3_Request::get_boolean('maxcdn')) {
                $config->set('cdn.enabled', true);
                $config->set('cdn.engine', 'maxcdn');
            }
        }

        /**
         * General tab
         */
        if ($this->_page == 'w3tc_general') {
            $file_nfs = W3_Request::get_boolean('file_nfs');
            $file_locking = W3_Request::get_boolean('file_locking');

            $config->set('pgcache.file.nfs', $file_nfs);
            $config->set('minify.file.nfs', $file_nfs);

            $config->set('dbcache.file.locking', $file_locking);
            $config->set('objectcache.file.locking', $file_locking);
            $config->set('pgcache.file.locking', $file_locking);
            $config->set('minify.file.locking', $file_locking);

            if ($this->is_network_and_master()) {
                if (($this->_config->get_boolean('common.force_master') !==
                    $config->get_boolean('common.force_master')) ||
                    //Blogs cache is wrong so empty it.
                    (!w3_force_master() && $this->_config->get_boolean('common.force_master')
                        && $config->get_boolean('common.force_master')) ||
                    (w3_force_master() && !$this->_config->get_boolean('common.force_master')
                        && !$config->get_boolean('common.force_master'))) {
                    @unlink(W3TC_CACHE_BLOGMAP_FILENAME);
                    $blogmap_dir = dirname(W3TC_CACHE_BLOGMAP_FILENAME) . '/' .
                                   basename(W3TC_CACHE_BLOGMAP_FILENAME, '.php') . '/';
                    if (@is_dir($blogmap_dir))
                        w3_rmdir($blogmap_dir);
                }
                if ($config->get_boolean('common.force_master'))
                    $config_admin->set('common.visible_by_master_only', true);
            }

            /**
             * Check permalinks for page cache
             */
            if ($config->get_boolean('pgcache.enabled') && $config->get_string('pgcache.engine') == 'file_generic'
                && !get_option('permalink_structure')) {
                $config->set('pgcache.enabled', false);
                $redirect_permalink_error = 'fancy_permalinks_disabled_pgcache';
            }

            $w3_cloudflare = w3_instance('W3_CloudFlare');
            $w3_cloudflare->reset_settings_cache();
            if ($config->get_boolean('cloudflare.enabled') && $w3_cloudflare->minify_enabled() && $config->get_boolean('minify.enabled')) {
                $config->set('minify.enabled',false);
            }

            /**
             * Get New Relic application id
             */
            if ($config->get_boolean('newrelic.enabled')) {
                $method = W3_Request::get_string('application_id_method');
                $newrelic_prefix = '';
                if (w3_is_network() && w3_get_blog_id() != 0)
                    $newrelic_prefix = $this->_config->get_string('newrelic.appname_prefix');
                if (($newrelic_api_key = $config->get_string('newrelic.api_key')) && !$config->get_string('newrelic.account_id')) {
                    $nerser = w3_instance('W3_NewRelicService');
                    $account_id = $nerser->get_account_id($newrelic_api_key);
                    $config->set('newrelic.account_id', $account_id);
                }

                if ($method == 'dropdown' && $config->get_string('newrelic.application_id')) {
                    $application_id = $config->get_string('newrelic.application_id');
                    if ($config->get_string('newrelic.api_key') && $config->get_string('newrelic.account_id')) {
                        w3_require_once(W3TC_LIB_W3_DIR .'/NewRelicService.php');
                        $nerser = new W3_NewRelicService($config->get_string('newrelic.api_key'),
                                                         $config->get_string('newrelic.account_id'));
                        $appname = $nerser->get_application_name($application_id);
                        $config->set('newrelic.appname', $appname);
                    }
                } else if ($method == 'manual' && $config->get_string('newrelic.appname')) {
                    if ($newrelic_prefix != '' && strpos($config->get_string('newrelic.appname'), $newrelic_prefix) === false) {
                        $application_name = $newrelic_prefix . $config->get_string('newrelic.appname');
                        $config->set('newrelic.appname', $application_name);
                    } else
                        $application_name = $config->get_string('newrelic.appname');

                    if ($config->get_string('newrelic.api_key') && $config->get_string('newrelic.account_id') ) {
                        w3_require_once(W3TC_LIB_W3_DIR .'/NewRelicService.php');
                        $nerser = new W3_NewRelicService($config->get_string('newrelic.api_key'),
                                                         $config->get_string('newrelic.account_id'));
                        $application_id = $nerser->get_application_id($application_name);
                        if ($application_id)
                            $config->set('newrelic.application_id', $application_id);
                    }
                }
            }
        }

        /**
         * Minify tab
         */
        if ($this->_page == 'w3tc_minify' && !$this->_config->get_boolean('minify.auto')) {
            $js_groups = array();
            $css_groups = array();

            $js_files = W3_Request::get_array('js_files');
            $css_files = W3_Request::get_array('css_files');

            foreach ($js_files as $theme => $templates) {
                foreach ($templates as $template => $locations) {
                    foreach ((array) $locations as $location => $types) {
                        foreach ((array) $types as $type => $files) {
                            foreach ((array) $files as $file) {
                                if (!empty($file)) {
                                    $js_groups[$theme][$template][$location]['files'][] = w3_normalize_file_minify($file);
                                }
                            }
                        }
                    }
                }
            }

            foreach ($css_files as $theme => $templates) {
                foreach ($templates as $template => $locations) {
                    foreach ((array) $locations as $location => $files) {
                        foreach ((array) $files as $file) {
                            if (!empty($file)) {
                                $css_groups[$theme][$template][$location]['files'][] = w3_normalize_file_minify($file);
                            }
                        }
                    }
                }
            }

            $config->set('minify.js.groups', $js_groups);
            $config->set('minify.css.groups', $css_groups);

            $js_theme = W3_Request::get_string('js_theme');
            $css_theme = W3_Request::get_string('css_theme');

            $params = array_merge($params, array(
                'js_theme' => $js_theme,
                'css_theme' => $css_theme
            ));
        }
        if ($this->_page == 'w3tc_minify') {
            if ($config->get_integer('minify.auto.filename_length') > 246) {
                $config->set('minify.auto.filename_length', 246);
            }
        }
        delete_transient('w3tc_minify_tested_filename_length');

        /**
         * Browser Cache tab
         */
        if ($this->_page == 'w3tc_browsercache') {
            if ($config->get_boolean('browsercache.enabled') && $config->get_boolean('browsercache.no404wp') && !get_option('permalink_structure')) {
                $config->set('browsercache.no404wp', false);
                $redirect_permalink_error = 'fancy_permalinks_disabled_browsercache';
            }
            $config->set('browsercache.timestamp', time());
        }

        /**
         * Mobile tab
         */
        if ($this->_page == 'w3tc_mobile') {
            $groups = W3_Request::get_array('mobile_groups');

            $mobile_groups = array();
            $cached_mobile_groups = array();

            foreach ($groups as $group => $group_config) {
                $group = strtolower($group);
                $group = preg_replace('~[^0-9a-z_]+~', '_', $group);
                $group = trim($group, '_');

                if ($group) {
                    $theme = (isset($group_config['theme']) ? trim($group_config['theme']) : 'default');
                    $enabled = (isset($group_config['enabled']) ? (boolean) $group_config['enabled'] : true);
                    $redirect = (isset($group_config['redirect']) ? trim($group_config['redirect']) : '');
                    $agents = (isset($group_config['agents']) ? explode("\r\n", trim($group_config['agents'])) : array());

                    $mobile_groups[$group] = array(
                        'theme' => $theme,
                        'enabled' => $enabled,
                        'redirect' => $redirect,
                        'agents' => $agents
                    );

                    $cached_mobile_groups[$group] = $agents;
                }
            }

            /**
             * Allow plugins modify WPSC mobile groups
             */
            $cached_mobile_groups = apply_filters('cached_mobile_groups', $cached_mobile_groups);

            /**
             * Merge existent and delete removed groups
             */
            foreach ($mobile_groups as $group => $group_config) {
                if (isset($cached_mobile_groups[$group])) {
                    $mobile_groups[$group]['agents'] = (array) $cached_mobile_groups[$group];
                } else {
                    unset($mobile_groups[$group]);
                }
            }

            /**
             * Add new groups
             */
            foreach ($cached_mobile_groups as $group => $agents) {
                if (!isset($mobile_groups[$group])) {
                    $mobile_groups[$group] = array(
                        'theme' => '',
                        'enabled' => true,
                        'redirect' => '',
                        'agents' => $agents
                    );
                }
            }

            /**
             * Allow plugins modify W3TC mobile groups
             */
            $mobile_groups = apply_filters('w3tc_mobile_groups', $mobile_groups);

            /**
             * Sanitize mobile groups
             */
            foreach ($mobile_groups as $group => $group_config) {
                $mobile_groups[$group] = array_merge(array(
                    'theme' => '',
                    'enabled' => true,
                    'redirect' => '',
                    'agents' => array()
                ), $group_config);

                $mobile_groups[$group]['agents'] = array_unique($mobile_groups[$group]['agents']);
                $mobile_groups[$group]['agents'] = array_map('strtolower', $mobile_groups[$group]['agents']);
                sort($mobile_groups[$group]['agents']);
            }
            $enable_mobile = false;
            foreach ($mobile_groups as $group => $group_config) {
                if ($group_config['enabled']) {
                    $enable_mobile = true;
                    break;
                }
            }
            $config->set('mobile.enabled', $enable_mobile);
            $config->set('mobile.rgroups', $mobile_groups);
        }

        /**
         * Referrer tab
         */
        if ($this->_page == 'w3tc_referrer') {
            $groups = W3_Request::get_array('referrer_groups');

            $referrer_groups = array();

            foreach ($groups as $group => $group_config) {
                $group = strtolower($group);
                $group = preg_replace('~[^0-9a-z_]+~', '_', $group);
                $group = trim($group, '_');

                if ($group) {
                    $theme = (isset($group_config['theme']) ? trim($group_config['theme']) : 'default');
                    $enabled = (isset($group_config['enabled']) ? (boolean) $group_config['enabled'] : true);
                    $redirect = (isset($group_config['redirect']) ? trim($group_config['redirect']) : '');
                    $referrers = (isset($group_config['referrers']) ? explode("\r\n", trim($group_config['referrers'])) : array());

                    $referrer_groups[$group] = array(
                        'theme' => $theme,
                        'enabled' => $enabled,
                        'redirect' => $redirect,
                        'referrers' => $referrers
                    );
                }
            }

            /**
             * Allow plugins modify W3TC referrer groups
             */
            $referrer_groups = apply_filters('w3tc_referrer_groups', $referrer_groups);

            /**
             * Sanitize mobile groups
             */
            foreach ($referrer_groups as $group => $group_config) {
                $referrer_groups[$group] = array_merge(array(
                    'theme' => '',
                    'enabled' => true,
                    'redirect' => '',
                    'referrers' => array()
                ), $group_config);

                $referrer_groups[$group]['referrers'] = array_unique($referrer_groups[$group]['referrers']);
                $referrer_groups[$group]['referrers'] = array_map('strtolower', $referrer_groups[$group]['referrers']);
                sort($referrer_groups[$group]['referrers']);
            }

            $enable_referrer = false;
            foreach ($referrer_groups as $group => $group_config) {
                if ($group_config['enabled']) {
                    $enable_referrer = true;
                    break;
                }
            }
            $config->set('referrer.enabled', $enable_referrer);

            $config->set('referrer.rgroups', $referrer_groups);
        }

        /**
         * CDN tab
         */
        if ($this->_page == 'w3tc_cdn') {
            $cdn_cnames = W3_Request::get_array('cdn_cnames');
            $cdn_domains = array();

            foreach ($cdn_cnames as $cdn_cname) {
                $cdn_cname = trim($cdn_cname);

                /**
                 * Auto expand wildcard domain to 10 subdomains
                 */
                $matches = null;

                if (preg_match('~^\*\.(.*)$~', $cdn_cname, $matches)) {
                    $cdn_domains = array();

                    for ($i = 1; $i <= 10; $i++) {
                        $cdn_domains[] = sprintf('cdn%d.%s', $i, $matches[1]);
                    }

                    break;
                }

                if ($cdn_cname) {
                    $cdn_domains[] = $cdn_cname;
                }
            }

            switch ($this->_config->get_string('cdn.engine')) {
                case 'ftp':
                    $config->set('cdn.ftp.domain', $cdn_domains);
                    break;

                case 's3':
                    $config->set('cdn.s3.cname', $cdn_domains);
                    break;

                case 'cf':
                    $config->set('cdn.cf.cname', $cdn_domains);
                    break;

                case 'cf2':
                    $config->set('cdn.cf2.cname', $cdn_domains);
                    break;

                case 'rscf':
                    $config->set('cdn.rscf.cname', $cdn_domains);
                    break;

                case 'azure':
                    $config->set('cdn.azure.cname', $cdn_domains);
                    break;
                case 'mirror':
                    $config->set('cdn.mirror.domain', $cdn_domains);
                    break;

                case 'maxcdn':
                    $config->set('cdn.maxcdn.domain', $cdn_domains);
                    break;

                case 'netdna':
                    $config->set('cdn.netdna.domain', $cdn_domains);
                    break;

                case 'cotendo':
                    $config->set('cdn.cotendo.domain', $cdn_domains);
                    break;

                case 'edgecast':
                    $config->set('cdn.edgecast.domain', $cdn_domains);
                    break;

                case 'att':
                    $config->set('cdn.att.domain', $cdn_domains);
                    break;
                
                case 'akamai':
                    $config->set('cdn.akamai.domain', $cdn_domains);
                    break;
            }
        }

        $this->config_save($config, $config_admin);
            
        switch ($this->_page) {
            case 'w3tc_cdn':
                /**
                 * Handle Set Cookie Domain
                 */
                $set_cookie_domain_old = W3_Request::get_boolean('set_cookie_domain_old');
                $set_cookie_domain_new = W3_Request::get_boolean('set_cookie_domain_new');

                if ($set_cookie_domain_old != $set_cookie_domain_new) {
                    if ($set_cookie_domain_new) {
                        if (!$this->enable_cookie_domain()) {
                            $this->redirect(array_merge($params, array(
                                'w3tc_error' => 'enable_cookie_domain'
                            )));
                        }
                    } else {
                        if (!$this->disable_cookie_domain()) {
                            $this->redirect(array_merge($params, array(
                                'w3tc_error' => 'disable_cookie_domain'
                            )));
                        }
                    }
                }
                break;

            case 'w3tc_general':
                /**
                 * Handle CloudFlare changes
                 */
                if ($this->_config->get_boolean('cloudflare.enabled') &&
                    ((w3_get_blog_id() == 0) ||
                     (w3_get_blog_id() != 0 && !$this->is_sealed('cloudflare'))
                    )) {
                    /**
                     * @var $w3_cloudflare W3_CloudFlare
                     */
                    $w3_cloudflare = w3_instance('W3_CloudFlare');
                    W3_CloudFlare::clear_last_error('');
                    $cf_values = W3_Request::get_as_array('cloudflare_');
                    if (!$w3_cloudflare->save_settings($cf_values)) {
                        $this->redirect(array_merge($params, array(
                            'w3tc_error' => 'cloudflare_api_request'
                        )));
                    }
                }
                break;
        }

        $this->_notes[] = 'config_save';

        if ($redirect_permalink_error) {
            $this->redirect(array(
                'w3tc_error' => $redirect_permalink_error,
                'w3tc_note' => 'config_save'
            ));
        }

        $this->redirect_with_custom_messages($params);
    }

    function action_save_new_relic() {
        if ($this->_config->get_boolean('newrelic.enabled')) {
            /**
             * @var $nerser W3_NewRelicService
             */
            $nerser = w3_instance('W3_NewRelicService');
            $application = W3_Request::get_array('application');
            $application['alerts_enabled'] = $application['alerts_enabled'] == 1 ? 'true' : 'false';
            $application['rum_enabled'] = $application['rum_enabled'] == 1 ? 'true' : 'false';
            $result=$nerser->update_application_settings($application);
            $this->redirect(array(
                'w3tc_note' => 'new_relic_save'
            ), true);
        }
    }

    /**
     * Save support us action
     *
     * @return void
     */
    function action_save_support_us() {
        $support = W3_Request::get_string('support');
        $tweeted = W3_Request::get_boolean('tweeted');

        $this->_config->set('common.support', $support);
        $this->_config->set('common.tweeted', $tweeted);

        $this->_config->save();

        w3_instance('W3_AdminLinks')->link_update($this->_config);

        $this->redirect(array(
            'w3tc_note' => 'config_save'
        ));
    }

    /**
     * PgCache purge post
     *
     * @return void
     */
    function action_pgcache_purge_post() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $post_id = W3_Request::get_integer('post_id');
        do_action('w3tc_purge_from_pgcache', $post_id);

        $this->redirect(array(
                'w3tc_note' => 'pgcache_purge_post'
            ), true);
    }

    /**
     * PgCache purge page
     *
     * @return void
     */
    function action_pgcache_purge_page() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $post_id = W3_Request::get_integer('post_id');
        do_action('w3tc_purge_from_pgcache', $post_id);

        $this->redirect(array(
                'w3tc_note' => 'pgcache_purge_page'
            ), true);
    }

    /**
     * Minify recommendations action
     *
     * @return void
     */
    function action_minify_recommendations() {
        $themes = $this->get_themes();

        $current_theme = w3tc_get_current_theme_name();
        $current_theme_key = array_search($current_theme, $themes);

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $theme_key = W3_Request::get_string('theme_key', $current_theme_key);
        $theme_name = (isset($themes[$theme_key]) ? $themes[$theme_key] : $current_theme);

        $templates = $this->get_theme_templates($theme_name);
        $recommendations = $this->get_theme_recommendations($theme_name);

        list ($js_groups, $css_groups) = $recommendations;

        $minify_js_groups = $this->_config->get_array('minify.js.groups');
        $minify_css_groups = $this->_config->get_array('minify.css.groups');

        $checked_js = array();
        $checked_css = array();

        $locations_js = array();

        if (isset($minify_js_groups[$theme_key])) {
            foreach ((array) $minify_js_groups[$theme_key] as $template => $locations) {
                foreach ((array) $locations as $location => $config) {
                    if (isset($config['files'])) {
                        foreach ((array) $config['files'] as $file) {
                            if (!isset($js_groups[$template]) || !in_array($file, $js_groups[$template])) {
                                $js_groups[$template][] = $file;
                            }

                            $checked_js[$template][$file] = true;
                            $locations_js[$template][$file] = $location;
                        }
                    }
                }
            }
        }

        if (isset($minify_css_groups[$theme_key])) {
            foreach ((array) $minify_css_groups[$theme_key] as $template => $locations) {
                foreach ((array) $locations as $location => $config) {
                    if (isset($config['files'])) {
                        foreach ((array) $config['files'] as $file) {
                            if (!isset($css_groups[$template]) || !in_array($file, $css_groups[$template])) {
                                $css_groups[$template][] = $file;
                            }

                            $checked_css[$template][$file] = true;
                        }
                    }
                }
            }
        }

        include W3TC_INC_DIR . '/lightbox/minify_recommendations.php';
    }

    /**
     * Self test action
     */
    function action_self_test() {
        include W3TC_INC_DIR . '/lightbox/self_test.php';
    }

    /**
     * Support Us action
     *
     * @return void
     */
    function action_support_us() {
        $supports = $this->get_supports();

        include W3TC_INC_DIR . '/lightbox/support_us.php';
    }

    /**
     * Page Speed results action
     *
     * @return void
     */
    function action_pagespeed_results() {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');
        w3_require_once(W3TC_LIB_W3_DIR . '/PageSpeed.php');

        $force = W3_Request::get_boolean('force');
        $title = 'Google Page Speed';

        $w3_pagespeed = new W3_PageSpeed();
        $results = $w3_pagespeed->analyze(w3_get_home_url(), $force);

        if ($force) {
            $this->redirect(array(
                'w3tc_pagespeed_results' => 1,
                '_wpnonce' => wp_create_nonce('w3tc')
            ));
        }

        include W3TC_INC_DIR . '/popup/pagespeed_results.php';
    }

    /**
     * New Relic tab
     */
    function action_view_new_relic_app() {
        $nerser = w3_instance('W3_NewRelicService');
        $view_application = W3_Request::get_integer('view_application', 0);
        $dashboard = '';
        if ($view_application)
            $dashboard = $nerser->get_dashboard($view_application);
        echo $dashboard;
    }

    /**
     * Save config, can't decline save process. (difference from action_save)
     *
     * Do some actions on config keys update
     * Used in several places such as:
     *
     * 1. common config save
     * 2. import settings
     *
     * @param W3_Config $new_config
     * @param W3_Config $new_config_admin
     * @return bool
     * @throws Exception
     */
    function config_save($new_config, $new_config_admin) {
        $master_config = ($new_config->is_master() ? $new_config : new W3_Config(true));

        if ($master_config->get_integer('common.instance_id', 0) == 0) {
            $master_config->set('common.instance_id', mt_rand());
            if (!$new_config->is_master())
                $master_config->save();
        }

        $old_config = new W3_Config();
        $browsercache_dependencies = array();

        if ($new_config->get_boolean('browsercache.enabled')) {
            $browsercache_dependencies = array_merge($browsercache_dependencies, array(
                'browsercache.cssjs.replace',
                'browsercache.html.replace',
                'browsercache.other.replace'
            ));

            if ($new_config->get_boolean('browsercache.cssjs.replace')) {
                $browsercache_dependencies = array_merge($browsercache_dependencies, array(
                    'browsercache.cssjs.compression',
                    'browsercache.cssjs.expires',
                    'browsercache.cssjs.lifetime',
                    'browsercache.cssjs.cache.control',
                    'browsercache.cssjs.cache.policy',
                    'browsercache.cssjs.etag',
                    'browsercache.cssjs.w3tc'
                ));
            }

            if ($new_config->get_boolean('browsercache.html.replace')) {
                $browsercache_dependencies = array_merge($browsercache_dependencies, array(
                    'browsercache.html.compression',
                    'browsercache.html.expires',
                    'browsercache.html.lifetime',
                    'browsercache.html.cache.control',
                    'browsercache.html.cache.policy',
                    'browsercache.html.etag',
                    'browsercache.html.w3tc'
                ));
            }

            if ($new_config->get_boolean('browsercache.other.replace')) {
                $browsercache_dependencies = array_merge($browsercache_dependencies, array(
                    'browsercache.other.compression',
                    'browsercache.other.expires',
                    'browsercache.other.lifetime',
                    'browsercache.other.cache.control',
                    'browsercache.other.cache.policy',
                    'browsercache.other.etag',
                    'browsercache.other.w3tc'
                ));
            }
        }

        /**
         * Show need empty page cache notification
         */
        if ($new_config->get_boolean('pgcache.enabled')) {

            $pgcache_dependencies = array_merge($browsercache_dependencies, array(
                'pgcache.debug',
                'dbcache.enabled',
                'objectcache.enabled',
                'minify.enabled',
                'cdn.enabled',
                'mobile.enabled',
                'referrer.enabled'
            ));

            if ($new_config->get_boolean('dbcache.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'dbcache.debug'
                ));
            }

            if ($new_config->get_boolean('objectcache.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'objectcache.debug'
                ));
            }

            if ($new_config->get_boolean('minify.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'minify.auto',
                    'minify.debug',
                    'minify.rewrite',
                    'minify.html.enable',
                    'minify.html.engine',
                    'minify.html.inline.css',
                    'minify.html.inline.js',
                    'minify.html.strip.crlf',
                    'minify.html.comments.ignore',
                    'minify.css.enable',
                    'minify.css.engine',
                    'minify.css.groups',
                    'minify.js.enable',
                    'minify.js.engine',
                    'minify.js.groups',
                    'minify.htmltidy.options.clean',
                    'minify.htmltidy.options.hide-comments',
                    'minify.htmltidy.options.wrap',
                    'minify.reject.logged',
                    'minify.reject.ua',
                    'minify.reject.uri'
                ));
            }

            if ($new_config->get_boolean('cdn.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'cdn.debug',
                    'cdn.engine',
                    'cdn.uploads.enable',
                    'cdn.includes.enable',
                    'cdn.includes.files',
                    'cdn.theme.enable',
                    'cdn.theme.files',
                    'cdn.minify.enable',
                    'cdn.custom.enable',
                    'cdn.custom.files',
                    'cdn.ftp.domain',
                    'cdn.ftp.ssl',
                    'cdn.s3.cname',
                    'cdn.s3.ssl',
                    'cdn.cf.cname',
                    'cdn.cf.ssl',
                    'cdn.cf2.cname',
                    'cdn.cf2.ssl',
                    'cdn.rscf.cname',
                    'cdn.rscf.ssl',
                    'cdn.azure.cname',
                    'cdn.azure.ssl',
                    'cdn.mirror.domain',
                    'cdn.mirror.ssl',
                    'cdn.netdna.domain',
                    'cdn.netdna.ssl',
                    'cdn.cotendo.domain',
                    'cdn.cotendo.ssl',
                    'cdn.edgecast.domain',
                    'cdn.edgecast.ssl',
                    'cdn.att.domain',
                    'cdn.att.ssl',
                    'cdn.reject.logged_roles',
                    'cdn.reject.roles',
                    'cdn.reject.ua',
                    'cdn.reject.uri',
                    'cdn.reject.files'
                ));
            }

            if ($new_config->get_boolean('mobile.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'mobile.rgroups'
                ));
            }

            if ($new_config->get_boolean('referrer.enabled')) {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'referrer.rgroups'
                ));
            }


            if ($new_config->get_boolean('browsercache.enabled') &&
                $new_config->get_string('pgcache.engine') == 'file_generic') {
                $pgcache_dependencies = array_merge($pgcache_dependencies, array(
                    'browsercache.html.last_modified',
                    'browsercache.other.last_modified'
                ));
            }

            $old_pgcache_dependencies_values = array();
            $new_pgcache_dependencies_values = array();

            foreach ($pgcache_dependencies as $pgcache_dependency) {
                $old_pgcache_dependencies_values[] = $old_config->get($pgcache_dependency);
                $new_pgcache_dependencies_values[] = $new_config->get($pgcache_dependency);
            }

            if (serialize($old_pgcache_dependencies_values) != serialize($new_pgcache_dependencies_values)) {
                $new_config->set('notes.need_empty_pgcache', true);
            }
        }

        /**
         * Show need empty minify notification
         */
        if ($new_config->get_boolean('minify.enabled') && (($new_config->get_boolean('minify.css.enable') && ($new_config->get_boolean('minify.auto') || count($new_config->get_array('minify.css.groups')))) || ($new_config->get_boolean('minify.js.enable') && ($new_config->get_boolean('minify.auto') || count($new_config->get_array('minify.js.groups')))))) {
            $minify_dependencies = array_merge($browsercache_dependencies, array(
                'minify.auto',
                'minify.debug',
                'minify.options',
                'minify.symlinks',
                'minify.css.enable',
                'minify.js.enable',
                'cdn.enabled'
            ));

            if ($new_config->get_boolean('minify.css.enable') && ($new_config->get_boolean('minify.auto') || count($new_config->get_array('minify.css.groups')))) {
                $minify_dependencies = array_merge($minify_dependencies, array(
                    'minify.css.engine',
                    'minify.css.combine',
                    'minify.css.strip.comments',
                    'minify.css.strip.crlf',
                    'minify.css.imports',
                    'minify.css.groups',
                    'minify.yuicss.path.java',
                    'minify.yuicss.path.jar',
                    'minify.yuicss.options.line-break',
                    'minify.csstidy.options.remove_bslash',
                    'minify.csstidy.options.compress_colors',
                    'minify.csstidy.options.compress_font-weight',
                    'minify.csstidy.options.lowercase_s',
                    'minify.csstidy.options.optimise_shorthands',
                    'minify.csstidy.options.remove_last_;',
                    'minify.csstidy.options.case_properties',
                    'minify.csstidy.options.sort_properties',
                    'minify.csstidy.options.sort_selectors',
                    'minify.csstidy.options.merge_selectors',
                    'minify.csstidy.options.discard_invalid_properties',
                    'minify.csstidy.options.css_level',
                    'minify.csstidy.options.preserve_css',
                    'minify.csstidy.options.timestamp',
                    'minify.csstidy.options.template'
                ));
            }

            if ($new_config->get_boolean('minify.js.enable') && ($new_config->get_boolean('minify.auto') || count($new_config->get_array('minify.js.groups')))) {
                $minify_dependencies = array_merge($minify_dependencies, array(
                    'minify.js.engine',
                    'minify.js.combine.header',
                    'minify.js.combine.body',
                    'minify.js.combine.footer',
                    'minify.js.strip.comments',
                    'minify.js.strip.crlf',
                    'minify.js.groups',
                    'minify.yuijs.path.java',
                    'minify.yuijs.path.jar',
                    'minify.yuijs.options.line-break',
                    'minify.yuijs.options.nomunge',
                    'minify.yuijs.options.preserve-semi',
                    'minify.yuijs.options.disable-optimizations',
                    'minify.ccjs.path.java',
                    'minify.ccjs.path.jar',
                    'minify.ccjs.options.compilation_level',
                    'minify.ccjs.options.formatting'
                ));
            }

            if ($new_config->get_boolean('cdn.enabled')) {
                $minify_dependencies = array_merge($minify_dependencies, array(
                    'cdn.engine'
                ));
            }

            $old_minify_dependencies_values = array();
            $new_minify_dependencies_values = array();

            foreach ($minify_dependencies as $minify_dependency) {
                $old_minify_dependencies_values[] = $old_config->get($minify_dependency);
                $new_minify_dependencies_values[] = $new_config->get($minify_dependency);
            }

            if (serialize($old_minify_dependencies_values) != serialize($new_minify_dependencies_values)) {
                $new_config->set('notes.need_empty_minify', true);
            }
        }

        if ($new_config->get_boolean('cdn.enabled') && !w3_is_cdn_mirror($new_config->get_string('cdn.engine'))) {
            /**
             * Show notification when CDN enabled
             */
            if (!$old_config->get_boolean('cdn.enabled')) {
                $new_config->set('notes.cdn_upload', true);
            }

            /**
             * Show notification when Browser Cache settings changes
             */
            $cdn_dependencies = array(
                'browsercache.enabled'
            );

            if ($new_config->get_boolean('cdn.enabled')) {
                $cdn_dependencies = array(
                    'browsercache.cssjs.compression',
                    'browsercache.cssjs.expires',
                    'browsercache.cssjs.lifetime',
                    'browsercache.cssjs.cache.control',
                    'browsercache.cssjs.cache.policy',
                    'browsercache.cssjs.etag',
                    'browsercache.cssjs.w3tc',
                    'browsercache.html.compression',
                    'browsercache.html.expires',
                    'browsercache.html.lifetime',
                    'browsercache.html.cache.control',
                    'browsercache.html.cache.policy',
                    'browsercache.html.etag',
                    'browsercache.html.w3tc',
                    'browsercache.other.compression',
                    'browsercache.other.expires',
                    'browsercache.other.lifetime',
                    'browsercache.other.cache.control',
                    'browsercache.other.cache.policy',
                    'browsercache.other.etag',
                    'browsercache.other.w3tc'
                );
            }

            $old_cdn_dependencies_values = array();
            $new_cdn_dependencies_values = array();

            foreach ($cdn_dependencies as $cdn_dependency) {
                $old_cdn_dependencies_values[] = $old_config->get($cdn_dependency);
                $new_cdn_dependencies_values[] = $new_config->get($cdn_dependency);
            }

            if (serialize($old_cdn_dependencies_values) != serialize($new_cdn_dependencies_values)) {
                $new_config->set('notes.cdn_reupload', true);
            }
        }

        /**
         * Show need empty object cache notification
         */
        if ($this->_config->get_boolean('objectcache.enabled')) {
            $objectcache_dependencies = array(
                'objectcache.groups.global',
                'objectcache.groups.nonpersistent'
            );

            $old_objectcache_dependencies_values = array();
            $new_objectcache_dependencies_values = array();

            foreach ($objectcache_dependencies as $objectcache_dependency) {
                $old_objectcache_dependencies_values[] = $old_config->get($objectcache_dependency);
                $new_objectcache_dependencies_values[] = $new_config->get($objectcache_dependency);
            }

            if (serialize($old_objectcache_dependencies_values) != serialize($new_objectcache_dependencies_values)) {
                $new_config->set('notes.need_empty_objectcache', true);
            }
        }

        if ($this->_config->get_boolean('newrelic.enabled')) {
            if ($this->_config->get_boolean('pgcache.enabled')) {
                if (w3_is_network() && $this->_config->get_boolean('common.force_master')) {
                    $new_config->set('pgcache.late_init', true);
                }
            }
        }
        /**
         * Save config
         */
        try {
            $new_config_admin->save();
            $new_config->save();
        } catch (Exception $ex) {
            // try to fix environment, it potentially can be fixed silently
            // dont show error here, it will be called again later 
            // in admin_notices
            try {
                $environment = w3_instance('W3_AdminEnvironment');
                $environment->fix_in_wpadmin($new_config);
            } catch (Exception $ex) {
            }

            // retry save process and complain now on failure
            try {
                $new_config_admin->save();
                $new_config->save();
            } catch (Exception $ex) {
                throw new Exception(
                    '<strong>Can\'t change configuration</strong>: ' .
                    $ex->getMessage());
            }
        }

        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnAdmin');

        /**
         * Empty caches on engine change or cache enable/disable
         */
        if ($old_config->get_string('pgcache.engine') != $new_config->get_string('pgcache.engine') || $old_config->get_string('pgcache.enabled') != $new_config->get_string('pgcache.enabled')) {
            $this->flush_pgcache();
        }

        if ($old_config->get_string('dbcache.engine') != $new_config->get_string('dbcache.engine') || $old_config->get_string('dbcache.enabled') != $new_config->get_string('dbcache.enabled')) {
            $this->flush_dbcache();
        }

        if ($old_config->get_string('objectcache.engine') != $new_config->get_string('objectcache.engine') || $old_config->get_string('objectcache.enabled') != $new_config->get_string('objectcache.enabled')) {
            $this->flush_objectcache();
        }

        if ($old_config->get_string('minify.engine') != $new_config->get_string('minify.engine') || $old_config->get_string('minify.enabled') != $new_config->get_string('minify.enabled')) {
            $this->flush_minify();
        }

        /**
         * Update CloudFront CNAMEs
         */
        $update_cf_cnames = false;

        if ($new_config->get_boolean('cdn.enabled') && in_array($new_config->get_string('cdn.engine'), array('cf', 'cf2'))) {
            if ($new_config->get_string('cdn.engine') == 'cf') {
                $old_cnames = $old_config->get_array('cdn.cf.cname');
                $new_cnames = $new_config->get_array('cdn.cf.cname');
            } else {
                $old_cnames = $old_config->get_array('cdn.cf2.cname');
                $new_cnames = $new_config->get_array('cdn.cf2.cname');
            }

            if (count($old_cnames) != count($new_cnames) || count(array_diff($old_cnames, $new_cnames))) {
                $update_cf_cnames = true;
            }
        }

        /**
         * Refresh config
         */
        $this->_config->load();

        /**
         * React to config changes
         */
        $environment = w3_instance('W3_AdminEnvironment');
        $environment->fix_on_event($new_config, 'config_change', $old_config);
            
        /**
         * Update support us option
         */
        w3_instance('W3_AdminLinks')->link_update($this->_config);

        /**
         * Auto upload minify files to CDN
         */
        if ($new_config->get_boolean('minify.enabled') && $new_config->get_boolean('minify.upload') && $new_config->get_boolean('cdn.enabled') && !w3_is_cdn_mirror($new_config->get_string('cdn.engine'))) {
            $this->cdn_upload_minify();
        }

        /**
         * Auto upload browsercache files to CDN
         */
        if ($new_config->get_boolean('cdn.enabled') && $new_config->get_string('cdn.engine') == 'ftp') {
            $this->cdn_delete_browsercache();
            $this->cdn_upload_browsercache();
        }

        /**
         * Update CloudFront CNAMEs
         */
        if ($update_cf_cnames) {
            $error = null;
            $w3_plugin_cdn->update_cnames($error);
        }

        return true;
    }

    /**
     * Flush specified cache
     *
     * @param string $type
     * @return void
     */
    function flush($type) {
        
        if ($this->_config->get_string('pgcache.engine') == $type && $this->_config->get_boolean('pgcache.enabled')) {
            $this->_config->set('notes.need_empty_pgcache', false);
            $this->_config->set('notes.plugins_updated', false);
            $this->_config->save();
            $this->flush_pgcache();
        }

        if ($this->_config->get_string('dbcache.engine') == $type && $this->_config->get_boolean('dbcache.enabled')) {
            $this->flush_dbcache();
        }

        if ($this->_config->get_string('objectcache.engine') == $type && $this->_config->get_boolean('objectcache.enabled')) {
            $this->flush_objectcache();
        }

        if ($this->_config->get_string('fragmentcache.engine') == $type && $this->_config->get_boolean('fragmentcache.enabled')) {
            $this->flush_fragmentcache();
        }

        if ($this->_config->get_string('minify.engine') == $type && $this->_config->get_boolean('minify.enabled')) {
            $this->_config->set('notes.need_empty_minify', false);
            $this->_config->save();
            $this->flush_minify();
        }
    }

    /**
     * Flush memcached cache
     *
     * @return void
     */
    function flush_memcached() {
        $this->flush('memcached');
    }

    /**
     * Flush APC cache
     *
     * @return void
     */
    function flush_opcode() {
        $this->flush('apc');
        $this->flush('eaccelerator');
        $this->flush('xcache');
        $this->flush('wincache');
    }

    /**
     * Flush APC system cache
     */
    function flush_apc_system() {
        $cacheflush = w3_instance('W3_CacheFlush');
        $cacheflush->apc_system_flush();
    }

    /**
     * Flush file cache
     *
     * @return void
     */
    function flush_file() {
        $this->flush('file');
        $this->flush('file_generic');
    }

    /**
     * Flush all cache
     *
     * @param bool $flush_cf
     * @return void
     */
    function flush_all($flush_cf = true) {
        $this->flush_memcached();
        $this->flush_opcode();
        $this->flush_file();
        $this->flush_browser_cache();
        if ($this->_config->get_boolean('varnish.enabled'))
            $this->flush_varnish();
        if ($flush_cf && $this->_config->get_boolean('cloudflare.enabled'))
            $this->flush_cloudflare();
    }

    /**
     * Flush page cache
     *
     * @return void
     */
    function flush_pgcache() {
        $flusher = w3_instance('W3_CacheFlush');
        $flusher->pgcache_flush();
    }

    /**
     * Flush database cache
     *
     * @return void
     */
    function flush_dbcache() {
        $flusher = w3_instance('W3_CacheFlush');
        $flusher->dbcache_flush();
    }

    /**
     * Flush object cache
     *
     * @return void
     */
    function flush_objectcache() {
        $flusher = w3_instance('W3_CacheFlush');
        $flusher->objectcache_flush();
    }

    /**
     * Flush fragment cache
     */
    function flush_fragmentcache() {
        $flusher = w3_instance('W3_CacheFlush');
        $flusher->fragmentcache_flush();
    }

    /**
     * Flush minify cache
     *
     * @return void
     */
    function flush_minify() {
        $w3_minify = w3_instance('W3_Minify');
        $w3_minify->flush();
    }

    /**
     * Flush browsers cache
     */
    function flush_browser_cache() {
        if ($this->_config->get_boolean('browsercache.enabled')) {
            $this->_config->set('browsercache.timestamp', time());

            $this->_config->save();
        }
    }
	
    /**
     * Flush varnish cache
     */
    function flush_varnish() {
        $cacheflush = w3_instance('W3_CacheFlush');
        $cacheflush->varnish_flush();
    }

    /**
     * Flush CDN mirror
     */
    function flush_cdn() {
        $cacheflush = w3_instance('W3_CacheFlush');
        $cacheflush->cdncache_purge();
    }

    /**
     * Purge the CloudFlare cache
     * @return void
     */
    function flush_cloudflare() {
        $response = null;

        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $email = $this->_config->get_string('email');
        $key = $this->_config->get_string('key');
        $zone = $this->_config->get_string('zone');


        if ($email && $key && $zone) {
            $config = array(
                'email' => $email,
                'key' => $key,
                'zone' => $zone
            );

            w3_require_once(W3TC_LIB_W3_DIR . '/CloudFlare.php');
            @$w3_cloudflare = new W3_CloudFlare($config);
            $w3_cloudflare->purge();
        }
    }

    /**
     * Returns array of theme groups
     *
     * @param string $theme_name
     * @return array
     */
    function get_theme_files($theme_name) {
        $patterns = array(
            '404',
            'search',
            'taxonomy(-.*)?',
            'front-page',
            'home',
            'index',
            '(image|video|text|audio|application).*',
            'attachment',
            'single(-.*)?',
            'page(-.*)?',
            'category(-.*)?',
            'tag(-.*)?',
            'author(-.*)?',
            'date',
            'archive',
            'comments-popup',
            'paged'
        );

        $templates = array();
        $theme = w3tc_get_theme($theme_name);

        if ($theme && isset($theme['Template Files'])) {
            $template_files = (array) $theme['Template Files'];

            foreach ($template_files as $template_file) {
                /**
                 * Check file name
                 */
                $template = basename($template_file, '.php');

                foreach ($patterns as $pattern) {
                    $regexp = '~^' . $pattern . '$~';

                    if (preg_match($regexp, $template)) {
                        $templates[] = $template_file;
                        continue 2;
                    }
                }

                /**
                 * Check get_header function call
                 */
                $template_content = @file_get_contents($template_file);

                if ($template_content && preg_match('~\s*get_header[0-9_]*\s*\(~', $template_content)) {
                    $templates[] = $template_file;
                }
            }

            sort($templates);
            reset($templates);
        }

        return $templates;
    }

    /**
     * Returns minify groups
     *
     * @param string $theme_name
     * @return array
     */
    function get_theme_templates($theme_name) {
        $groups = array(
            'default' => __('All Templates', 'w3-total-cache')
        );

        $templates = $this->get_theme_files($theme_name);

        foreach ($templates as $template) {
            $basename = basename($template, '.php');

            $groups[$basename] = ucfirst($basename);
        }

        return $groups;
    }

    /**
     * Returns array of detected URLs for theme templates
     *
     * @param string $theme_name
     * @return array
     */
    function get_theme_urls($theme_name) {
        $urls = array();
        $theme = w3tc_get_theme($theme_name);

        if ($theme && isset($theme['Template Files'])) {
            $front_page_template = false;

            if (get_option('show_on_front') == 'page') {
                $front_page_id = get_option('page_on_front');

                if ($front_page_id) {
                    $front_page_template_file = get_post_meta($front_page_id, '_wp_page_template', true);

                    if ($front_page_template_file) {
                        $front_page_template = basename($front_page_template_file, '.php');
                    }
                }
            }

            $home_url = w3_get_home_url();
            $template_files = (array) $theme['Template Files'];

            $mime_types = get_allowed_mime_types();
            $custom_mime_types = array();

            foreach ($mime_types as $mime_type) {
                list ($type1, $type2) = explode('/', $mime_type);
                $custom_mime_types = array_merge($custom_mime_types, array(
                    $type1,
                    $type2,
                    $type1 . '_' . $type2
                ));
            }

            foreach ($template_files as $template_file) {
                $link = false;
                $template = basename($template_file, '.php');

                /**
                 * Check common templates
                 */
                switch (true) {
                    /**
                     * Handle home.php or index.php or front-page.php
                     */
                    case (!$front_page_template && $template == 'home'):
                    case (!$front_page_template && $template == 'index'):
                    case (!$front_page_template && $template == 'front-page'):

                        /**
                         * Handle custom home page
                         */
                    case ($template == $front_page_template):
                        $link = $home_url . '/';
                        break;

                    /**
                     * Handle 404.php
                     */
                    case ($template == '404'):
                        $permalink = get_option('permalink_structure');
                        if ($permalink) {
                            $link = sprintf('%s/%s/', $home_url, '404_test');
                        } else {
                            $link = sprintf('%s/?p=%d', $home_url, 999999999);
                        }
                        break;

                    /**
                     * Handle search.php
                     */
                    case ($template == 'search'):
                        $link = sprintf('%s/?s=%s', $home_url, 'search_test');
                        break;

                    /**
                     * Handle date.php or archive.php
                     */
                    case ($template == 'date'):
                    case ($template == 'archive'):
                        $posts = get_posts(array(
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if (is_array($posts) && count($posts)) {
                            $time = strtotime($posts[0]->post_date);
                            $link = get_day_link(date('Y', $time), date('m', $time), date('d', $time));
                        }
                        break;

                    /**
                     * Handle author.php
                     */
                    case ($template == 'author'):
                        $author_id = false;
                        if (function_exists('get_users')) {
                            $users = get_users();
                            if (is_array($users) && count($users)) {
                                $user = current($users);
                                $author_id = $user->ID;
                            }
                        } else {
                            $author_ids = get_author_user_ids();
                            if (is_array($author_ids) && count($author_ids)) {
                                $author_id = $author_ids[0];
                            }
                        }
                        if ($author_id) {
                            $link = get_author_posts_url($author_id);
                        }
                        break;

                    /**
                     * Handle category.php
                     */
                    case ($template == 'category'):
                        $category_ids = get_all_category_ids();
                        if (is_array($category_ids) && count($category_ids)) {
                            $link = get_category_link($category_ids[0]);
                        }
                        break;

                    /**
                     * Handle tag.php
                     */
                    case ($template == 'tag'):
                        $term_ids = get_terms('post_tag', 'fields=ids');
                        if (is_array($term_ids) && count($term_ids)) {
                            $link = get_term_link($term_ids[0], 'post_tag');
                        }
                        break;

                    /**
                     * Handle taxonomy.php
                     */
                    case ($template == 'taxonomy'):
                        $taxonomy = '';
                        if (isset($GLOBALS['wp_taxonomies']) && is_array($GLOBALS['wp_taxonomies'])) {
                            foreach ($GLOBALS['wp_taxonomies'] as $wp_taxonomy) {
                                if (!in_array($wp_taxonomy->name, array(
                                    'category',
                                    'post_tag',
                                    'link_category'
                                ))) {
                                    $taxonomy = $wp_taxonomy->name;
                                    break;
                                }
                            }
                        }
                        if ($taxonomy) {
                            $terms = get_terms($taxonomy, array(
                                'number' => 1
                            ));
                            if (is_array($terms) && count($terms)) {
                                $link = get_term_link($terms[0], $taxonomy);
                            }
                        }
                        break;

                    /**
                     * Handle attachment.php
                     */
                    case ($template == 'attachment'):
                        $attachments = get_posts(array(
                            'post_type' => 'attachment',
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if (is_array($attachments) && count($attachments)) {
                            $link = get_attachment_link($attachments[0]->ID);
                        }
                        break;

                    /**
                     * Handle single.php
                     */
                    case ($template == 'single'):
                        $posts = get_posts(array(
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if (is_array($posts) && count($posts)) {
                            $link = get_permalink($posts[0]->ID);
                        }
                        break;

                    /**
                     * Handle page.php
                     */
                    case ($template == 'page'):
                        $pages_ids = get_all_page_ids();
                        if (is_array($pages_ids) && count($pages_ids)) {
                            $link = get_page_link($pages_ids[0]);
                        }
                        break;

                    /**
                     * Handle comments-popup.php
                     */
                    case ($template == 'comments-popup'):
                        $posts = get_posts(array(
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if (is_array($posts) && count($posts)) {
                            $link = sprintf('%s/?comments_popup=%d', $home_url, $posts[0]->ID);
                        }
                        break;

                    /**
                     * Handle paged.php
                     */
                    case ($template == 'paged'):
                        global $wp_rewrite;
                        if ($wp_rewrite->using_permalinks()) {
                            $link = sprintf('%s/page/%d/', $home_url, 1);
                        } else {
                            $link = sprintf('%s/?paged=%d', 1);
                        }
                        break;

                    /**
                     * Handle author-id.php or author-nicename.php
                     */
                    case preg_match('~^author-(.+)$~', $template, $matches):
                        if (is_numeric($matches[1])) {
                            $link = get_author_posts_url($matches[1]);
                        } else {
                            $link = get_author_posts_url(null, $matches[1]);
                        }
                        break;

                    /**
                     * Handle category-id.php or category-slug.php
                     */
                    case preg_match('~^category-(.+)$~', $template, $matches):
                        if (is_numeric($matches[1])) {
                            $link = get_category_link($matches[1]);
                        } else {
                            $term = get_term_by('slug', $matches[1], 'category');
                            if (is_object($term)) {
                                $link = get_category_link($term->term_id);
                            }
                        }
                        break;

                    /**
                     * Handle tag-id.php or tag-slug.php
                     */
                    case preg_match('~^tag-(.+)$~', $template, $matches):
                        if (is_numeric($matches[1])) {
                            $link = get_tag_link($matches[1]);
                        } else {
                            $term = get_term_by('slug', $matches[1], 'post_tag');
                            if (is_object($term)) {
                                $link = get_tag_link($term->term_id);
                            }
                        }
                        break;

                    /**
                     * Handle taxonomy-taxonomy-term.php
                     */
                    case preg_match('~^taxonomy-(.+)-(.+)$~', $template, $matches):
                        $link = get_term_link($matches[2], $matches[1]);
                        break;

                    /**
                     * Handle taxonomy-taxonomy.php
                     */
                    case preg_match('~^taxonomy-(.+)$~', $template, $matches):
                        $terms = get_terms($matches[1], array(
                            'number' => 1
                        ));
                        if (is_array($terms) && count($terms)) {
                            $link = get_term_link($terms[0], $matches[1]);
                        }
                        break;

                    /**
                     * Handle MIME_type.php
                     */
                    case in_array($template, $custom_mime_types):
                        $posts = get_posts(array(
                            'post_mime_type' => '%' . $template . '%',
                            'post_type' => 'attachment',
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));
                        if (is_array($posts) && count($posts)) {
                            $link = get_permalink($posts[0]->ID);
                        }
                        break;

                    /**
                     * Handle single-posttype.php
                     */
                    case preg_match('~^single-(.+)$~', $template, $matches):
                        $posts = get_posts(array(
                            'post_type' => $matches[1],
                            'numberposts' => 1,
                            'orderby' => 'rand'
                        ));

                        if (is_array($posts) && count($posts)) {
                            $link = get_permalink($posts[0]->ID);
                        }
                        break;

                    /**
                     * Handle page-id.php or page-slug.php
                     */
                    case preg_match('~^page-(.+)$~', $template, $matches):
                        if (is_numeric($matches[1])) {
                            $link = get_permalink($matches[1]);
                        } else {
                            $posts = get_posts(array(
                                'pagename' => $matches[1],
                                'post_type' => 'page',
                                'numberposts' => 1
                            ));

                            if (is_array($posts) && count($posts)) {
                                $link = get_permalink($posts[0]->ID);
                            }
                        }
                        break;

                    /**
                     * Try to handle custom template
                     */
                    default:
                        $posts = get_posts(array(
                            'pagename' => $template,
                            'post_type' => 'page',
                            'numberposts' => 1
                        ));

                        if (is_array($posts) && count($posts)) {
                            $link = get_permalink($posts[0]->ID);
                        }
                        break;
                }

                if ($link && !is_wp_error($link)) {
                    $urls[$template] = $link;
                }
            }
        }

        return $urls;
    }

    /**
     * Returns theme recommendations
     *
     * @param string $theme_name
     * @return array
     */
    function get_theme_recommendations($theme_name) {
        $urls = $this->get_theme_urls($theme_name);

        $js_groups = array();
        $css_groups = array();

        @set_time_limit($this->_config->get_integer('timelimit.minify_recommendations'));

        foreach ($urls as $template => $url) {
            /**
             * Append theme identifier
             */
            $url .= (strstr($url, '?') !== false ? '&' : '?') . 'w3tc_theme=' . urlencode($theme_name);

            /**
             * If preview mode enabled append w3tc_preview
             */
            if ($this->_config->is_preview()) {
                $url .= '&w3tc_preview=1';
            }

            /**
             * Get page contents
             */
            $response = w3_http_get($url);

            if (!is_wp_error($response) && ($response['response']['code'] == 200 || ($response['response']['code'] == 404 && $template == '404'))) {
                $js_files = $this->get_recommendations_js($response['body']);
                $css_files = $this->get_recommendations_css($response['body']);

                $js_groups[$template] = $js_files;
                $css_groups[$template] = $css_files;
            }
        }

        $js_groups = $this->get_theme_recommendations_by_groups($js_groups);
        $css_groups = $this->get_theme_recommendations_by_groups($css_groups);

        $recommendations = array(
            $js_groups,
            $css_groups
        );

        return $recommendations;
    }

    /**
     * Find common files and place them into default group
     *
     * @param array $groups
     * @return array
     */
    function get_theme_recommendations_by_groups($groups) {
        /**
         * First calculate file usage count
         */
        $all_files = array();

        foreach ($groups as $template => $files) {
            foreach ($files as $file) {
                if (!isset($all_files[$file])) {
                    $all_files[$file] = 0;
                }

                $all_files[$file]++;
            }
        }

        /**
         * Determine default group files
         */
        $default_files = array();
        $count = count($groups);

        foreach ($all_files as $all_file => $all_file_count) {
            /**
             * If file usage count == groups count then file is common
             */
            if ($count == $all_file_count) {
                $default_files[] = $all_file;

                /**
                 * If common file found unset it from all groups
                 */
                foreach ($groups as $template => $files) {
                    foreach ($files as $index => $file) {
                        if ($file == $all_file) {
                            array_splice($groups[$template], $index, 1);
                            if (!count($groups[$template])) {
                                unset($groups[$template]);
                            }
                            break;
                        }
                    }
                }
            }
        }

        /**
         * If there are common files append add them into default group
         */
        if (count($default_files)) {
            $new_groups = array();
            $new_groups['default'] = $default_files;

            foreach ($groups as $template => $files) {
                $new_groups[$template] = $files;
            }

            $groups = $new_groups;
        }

        /**
         * Unset empty templates
         */
        foreach ($groups as $template => $files) {
            if (!count($files)) {
                unset($groups[$template]);
            }
        }

        return $groups;
    }

    /**
     * Parse content and return JS recommendations
     *
     * @param string $content
     * @return array
     */
    function get_recommendations_js(&$content) {
        w3_require_once(W3TC_INC_DIR . '/functions/extract.php');

        $files = w3_extract_js($content);

        $files = array_map('w3_normalize_file_minify', $files);
        $files = array_unique($files);
        $ignore_files = $this->_config->get_array('minify.reject.files.js');
        $files = array_diff($files, $ignore_files);
        return $files;
    }

    /**
     * Parse content and return CSS recommendations
     *
     * @param string $content
     * @return array
     */
    function get_recommendations_css(&$content) {
        w3_require_once(W3TC_INC_DIR . '/functions/extract.php');

        $files = w3_extract_css($content);

        $files = array_map('w3_normalize_file_minify', $files);
        $files = array_unique($files);
        $ignore_files = $this->_config->get_array('minify.reject.files.css');
        $files = array_diff($files, $ignore_files);

        return $files;
    }

    /**
     * Returns button html
     *
     * @param string $text
     * @param string $onclick
     * @param string $class
     * @return string
     */
    function button($text, $onclick = '', $class = '') {
        return sprintf('<input type="button" class="button %s" value="%s" onclick="%s" />', htmlspecialchars($class), htmlspecialchars($text), htmlspecialchars($onclick));
    }

    /**
     * Returns button link html
     *
     * @param string $text
     * @param string $url
     * @param boolean $new_window
     * @return string
     */
    function button_link($text, $url, $new_window = false) {
        $url = str_replace('&amp;', '&', $url);

        if ($new_window) {
            $onclick = sprintf('window.open(\'%s\');', addslashes($url));
        } else {
            $onclick = sprintf('document.location.href=\'%s\';', addslashes($url));
        }

        return $this->button($text, $onclick);
    }

    /**
     * Returns hide note button html
     *
     * @param string $text
     * @param string $note
     * @param string $redirect
     * @param boolean $admin if to use config admin
     * @return string
     */
    function button_hide_note($text, $note, $redirect = '', $admin = false) {
        $url = sprintf('admin.php?page=%s&w3tc_hide_note&note=%s', $this->_page, $note);

        if ($admin)
            $url .= '&admin=1';

        if ($redirect != '') {
            $url .= '&redirect=' . urlencode($redirect);
        }

        $url = wp_nonce_url($url, 'w3tc');

        return $this->button_link($text, $url);
    }

    /**
     * Returns popup button html
     *
     * @param string $text
     * @param string $action
     * @param string $params
     * @param integer $width
     * @param integer $height
     * @return string
     */
    function button_popup($text, $action, $params = '', $width = 800, $height = 600) {
        $url = wp_nonce_url(sprintf('admin.php?page=w3tc_dashboard&w3tc_%s%s', $action, ($params != '' ? '&' . $params : '')), 'w3tc');
        $url = str_replace('&amp;', '&', $url);

        $onclick = sprintf('window.open(\'%s\', \'%s\', \'width=%d,height=%d,status=no,toolbar=no,menubar=no,scrollbars=yes\');', $url, $action, $width, $height);

        return $this->button($text, $onclick);
    }

    /**
     * Returns postbox header
     *
     * @param string $title
     * @param string $class
	 * @param string $id
     * @return string
     */
    function postbox_header($title, $class = '', $id = '') {
		if ( !empty( $id ) ) {
			$id = ' id="' . esc_attr( $id ) . '"';
		}
        return '<div' . $id . ' class="postbox ' . $class . '"><div class="handlediv" title="' . __('Click to toggle', 'w3-total-cache') . '"><br /></div><h3 class="hndle"><span>' . $title . '</span></h3><div class="inside">';
    }

    /**
     * Returns postbox footer
     *
     * @return string
     */
    function postbox_footer() {
        return '</div></div>';
    }

    /**
     * Returns nonce field HTML
     *
     * @param string $action
     * @param string $name
     * @param bool $referer
     * @param bool $echo
     * @return string
     */
    function nonce_field($action = -1, $name = '_wpnonce', $referer = true) {
        $name = esc_attr($name);
        $return = '<input type="hidden" name="' . $name . '" value="' . wp_create_nonce($action) . '" />';

        if ($referer) {
            $return .= wp_referer_field(false);
        }

        return $return;
    }

    /**
     * Check if memcache is available
     *
     * @param array $servers
     * @return boolean
     */
    function is_memcache_available($servers) {
        static $results = array();

        $key = md5(implode('', $servers));

        if (!isset($results[$key])) {
            w3_require_once(W3TC_LIB_W3_DIR . '/Cache/Memcached.php');

            @$memcached = new W3_Cache_Memcached(array(
                'servers' => $servers,
                'persistant' => false
            ));

            $test_string = sprintf('test_' . md5(time()));
            $test_value = array('content' => $test_string);
            $memcached->set($test_string, $test_value, 60);
            $test_value = $memcached->get($test_string);
            $results[$key] = ( $test_value['content'] == $test_string);
        }

        return $results[$key];
    }

    /**
     * Returns cookie domain
     *
     * @return string
     */
    function get_cookie_domain() {
        $site_url = get_option('siteurl');
        $parse_url = @parse_url($site_url);

        if ($parse_url && !empty($parse_url['host'])) {
            return $parse_url['host'];
        }

        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Checks COOKIE_DOMAIN definition existence
     *
     * @param string $content
     * @return int
     */
    function is_cookie_domain_define($content) {
        return preg_match(W3TC_PLUGIN_TOTALCACHE_REGEXP_COOKIEDOMAIN, $content);
    }

    /**
     * Checks if COOKIE_DOMAIN is enabled
     *
     * @return bool
     */
    function is_cookie_domain_enabled() {
        $cookie_domain = $this->get_cookie_domain();

        return (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN == $cookie_domain);
    }

    /**
     * Enables COOKIE_DOMAIN
     *
     * @return bool
     */
    function enable_cookie_domain() {
        $config_path = w3_get_wp_config_path();
        $config_data = @file_get_contents($config_path);

        if ($config_data === false) {
            return false;
        }

        $cookie_domain = $this->get_cookie_domain();

        if ($this->is_cookie_domain_define($config_data)) {
            $new_config_data = preg_replace(W3TC_PLUGIN_TOTALCACHE_REGEXP_COOKIEDOMAIN, "define('COOKIE_DOMAIN', '" . addslashes($cookie_domain) . "')", $config_data, 1);
        } else {
            $new_config_data = preg_replace('~<\?(php)?~', "\\0\r\ndefine('COOKIE_DOMAIN', '" . addslashes($cookie_domain) . "'); // " . __('Added by W3 Total Cache', 'w3-total-cache') . "\r\n", $config_data, 1);
        }

        if ($new_config_data != $config_data) {
            if (!@file_put_contents($config_path, $new_config_data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Disables COOKIE_DOMAIN
     *
     * @return bool
     */
    function disable_cookie_domain() {
        $config_path = w3_get_wp_config_path();
        $config_data = @file_get_contents($config_path);

        if ($config_data === false) {
            return false;
        }

        if ($this->is_cookie_domain_define($config_data)) {
            $new_config_data = preg_replace(W3TC_PLUGIN_TOTALCACHE_REGEXP_COOKIEDOMAIN, "define('COOKIE_DOMAIN', false)", $config_data, 1);

            if ($new_config_data != $config_data) {
                if (!@file_put_contents($config_path, $new_config_data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Uploads minify files to CDN
     *
     * @return void
     */
    function cdn_upload_minify() {
        $w3_plugin_cdn = w3_instance('W3_Plugin_Cdn');
        $w3_plugin_cdncommon = w3_instance('W3_Plugin_CdnCommon');

        $files = $w3_plugin_cdn->get_files_minify();

        $upload = array();
        $results = array();

        foreach ($files as $file) {
            $upload[] = $w3_plugin_cdncommon->build_file_descriptor($w3_plugin_cdncommon->docroot_filename_to_absolute_path($file),
                $w3_plugin_cdncommon->uri_to_cdn_uri($w3_plugin_cdncommon->docroot_filename_to_uri($file)));
        }

        $w3_plugin_cdncommon->upload($upload, true, $results);
    }

    /**
     * Uploads Browser Cache .htaccess to FTP
     *
     * @return void
     */
    function cdn_upload_browsercache() {
        $w3_plugin_cdncommon = w3_instance('W3_Plugin_CdnCommon');
        $w3_plugin_cdnadmin = w3_instance('W3_Plugin_CdnAdmin');

        $ce = w3_instance('W3_CdnAdminEnvironment');
        $rules = $ce->rules_generate_for_ftp($this->_config);

        if ($this->_config->get_boolean('browsercache.enabled')) {
            $be = w3_instance('W3_BrowserCacheAdminEnvironment');
            $rules .= $be->rules_cache_generate_for_ftp($this->_config);
        }

        $cdn_path = w3_get_cdn_rules_path();
        $tmp_path = W3TC_CACHE_TMP_DIR . '/' . $cdn_path;

        if (@file_put_contents($tmp_path, $rules)) {
            $results = array();
            $upload = array($w3_plugin_cdncommon->build_file_descriptor($tmp_path, $cdn_path));

            $w3_plugin_cdncommon->upload($upload, true, $results);
        }
    }

    /**
     * Deletes Browser Cache .htaccess from FTP
     *
     * @return void
     */
    function cdn_delete_browsercache() {
        $w3_plugin_cdn = w3_instance('W3_Plugin_CdnCommon');

        $cdn_path = w3_get_cdn_rules_path();
        $tmp_path = W3TC_CACHE_TMP_DIR . '/' . $cdn_path;

        $results = array();
        $delete = array(
            $w3_plugin_cdn->build_file_descriptor($tmp_path, $cdn_path)
        );

        $w3_plugin_cdn->delete($delete, false, $results);
    }

    /**
     * PHPMailer init function
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    function phpmailer_init(&$phpmailer) {
        $phpmailer->Sender = $this->_phpmailer_sender;
    }

    /**
     * Returns themes array
     *
     * @return array
     */
    function get_themes() {
        $themes = array();
        $wp_themes = w3tc_get_themes();

        foreach ($wp_themes as $wp_theme) {
            $theme_key = w3_get_theme_key($wp_theme['Theme Root'], $wp_theme['Template'], $wp_theme['Stylesheet']);
            $themes[$theme_key] = $wp_theme['Name'];
        }

        return $themes;
    }


    /**
     * Returns server info
     *
     * @return array
     */
    function get_server_info() {
        global $wp_version, $wp_db_version, $wpdb;

        $wordpress_plugins = get_plugins();
        $wordpress_plugins_active = array();

        foreach ($wordpress_plugins as $wordpress_plugin_file => $wordpress_plugin) {
            if (is_plugin_active($wordpress_plugin_file)) {
                $wordpress_plugins_active[$wordpress_plugin_file] = $wordpress_plugin;
            }
        }

        $mysql_version = $wpdb->get_var('SELECT VERSION()');
        $mysql_variables_result = (array) $wpdb->get_results('SHOW VARIABLES', ARRAY_N);
        $mysql_variables = array();

        foreach ($mysql_variables_result as $mysql_variables_row) {
            $mysql_variables[$mysql_variables_row[0]] = $mysql_variables_row[1];
        }

        $server_info = array(
            'w3tc' => array(
                'version' => W3TC_VERSION,
                'server' => (!empty($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown'),
                'dir' => W3TC_DIR,
                'cache_dir' => W3TC_CACHE_DIR,
                'blog_id' => w3_get_blog_id(),
                'document_root' => w3_get_document_root(),
                'home_root' => w3_get_home_root(),
                'site_root' => w3_get_site_root(),
                'base_path' => w3_get_base_path(),
                'home_path' => w3_get_home_path(),
                'site_path' => w3_get_site_path()
            ),
            'wp' => array(
                'version' => $wp_version,
                'db_version' => $wp_db_version,
                'abspath' => ABSPATH,
                'home' => get_option('home'),
                'siteurl' => get_option('siteurl'),
                'email' => get_option('admin_email'),
                'upload_info' => (array) w3_upload_info(),
                'theme' => w3tc_get_current_theme(),
                'wp_cache' => ((defined('WP_CACHE') && WP_CACHE) ? 'true' : 'false'),
                'plugins' => $wordpress_plugins_active
            ),
            'mysql' => array(
                'version' => $mysql_version,
                'variables' => $mysql_variables
            )
        );

        return $server_info;
    }

    /**
     * Returns list of support types
     *
     * @return array
     */
    function get_supports() {
        $supports = array(
            'footer' => 'page footer'
        );

        $link_categories = get_terms('link_category', array(
            'hide_empty' => 0
        ));

        foreach ($link_categories as $link_category) {
            $supports['link_category_' . $link_category->term_id] = strtolower($link_category->name);
        }

        return $supports;
    }

    /**
     * Returns true if upload queue is empty
     *
     * @return boolean
     */
    function is_queue_empty() {
        global $wpdb;

        $sql = sprintf('SELECT COUNT(*) FROM %s', $wpdb->prefix . W3TC_CDN_TABLE_QUEUE);
        $result = $wpdb->get_var($sql);

        return ($result == 0);
    }

    /**
     * Redirect function
     *
     * @param array $params
     * @param boolean $check_referrer
     * @return void
     */
    function redirect($params = array(), $check_referrer = false) {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');

        $url = W3_Request::get_string('redirect');

        if ($url == '') {
            if ($check_referrer && !empty($_SERVER['HTTP_REFERER'])) {
                $url = $_SERVER['HTTP_REFERER'];
            } else {
                $url = 'admin.php';
                $params = array_merge(array(
                    'page' => $this->_page
                ), $params);
            }
        }

        w3_redirect($url, $params);
    }

    /**
     * Redirect function to current admin page with errors and messages specified
     *
     * @param array $params
     * @param array $errors
     * @param array $notes
     * @return void
     */
    function redirect_with_custom_messages($params, $errors = null, $notes = null) {
        if (is_null($errors))
            $errors = $this->_errors;
        if (is_null($notes))
            $notes = $this->_notes;
        
        if (empty($errors) && $this->_single_system_item($notes)) {
            $this->redirect(array_merge($params, array(
                'w3tc_note' => $notes[0])));
            return;
        }
        if ($this->_single_system_item($errors) && empty($notes)) {
            $this->redirect(array_merge($params, array(
                'w3tc_error' => $errors[0])));
            return;
        }
                    
        $message_id = uniqid();
    	set_transient('w3tc_message.' . $message_id, 
                array('errors' => $errors, 'notes' => $notes), 600);

    	$this->redirect(array_merge($params, array(
                'w3tc_message' => $message_id)));
    }
    
    /*
     * Checks if contains single message item
     * 
     * @param $a array
     * @return boolean
     */
    function _single_system_item($a) {
        if (!is_array($a) || count($a) != 1)
            return false;
        
        $pos = strpos($a[0], ' ');
        if ($pos === false)
            return true;
        
        return false;
    }
    
    /**
     * Reads config from request
     *
     * @param W3_Config $config
     */
    function read_request($config) {
        w3_require_once(W3TC_LIB_W3_DIR . '/Request.php');
        $request = W3_Request::get_request();

        foreach ($request as $request_key => $request_value) {
            if  (is_array($request_value))
                array_map('stripslashes_deep', $request_value);
            else
                $request_value = stripslashes($request_value);
            if (strpos($request_key, 'memcached_servers'))
                $request_value = explode(',', $request_value);
            $config->set($request_key, $request_value);
        }
    }



    /**
     * UI helper methods, called from the view
     */

    /**
     * Returns true if we edit master config
     *
     * @return boolean
     */
    private function is_master() {
        return $this->_config->is_master();
    }

    /**
     * Returns true if we edit master config and we have network WP
     *
     * @return boolean
     */
    private function is_network_and_master() {
        return is_network_admin();
    }

    /**
     * Returns true if config section is sealed
     * @param string $section
     * @return boolean
     */
    private function is_sealed($section) {
        if ($this->is_master())
            return false;

        if (w3_is_network() && !$this->is_master() && w3_force_master())
            return true;

        // browsercache settings change rules, so not available in child settings
        if ($section == 'browsercache')
            return true;

        if ($section == 'minify' && !$this->_config_master->get_boolean('minify.enabled'))
            return true;

        return $this->_config_admin->get_boolean($section . '.configuration_sealed');
    }

    /**
     * Prints checkbox with config option value
     *
     * @param string $option_id
     * @param bool $disabled
     * @param string $class_prefix
     * @param bool $label
     */
    private function checkbox($option_id, $disabled = false, $class_prefix = '', $label = true) {
        $section = substr($option_id, 0, strpos($option_id, '.'));

        $disabled = $disabled || $this->is_sealed($section);

        if (!$disabled)
            echo '<input type="hidden" name="' . $option_id . '" value="0" />';

        $name = str_replace('.', '_', $option_id);

        if ($label)
            echo '<label>';
        echo '<input class="'.$class_prefix.'enabled" type="checkbox" id="' . $name .
            '" name="' . $option_id . '" value="1" ';
        checked($this->_config->get_boolean($option_id), true);

        if ($disabled)
            echo 'disabled="disabled" ';

        echo ' />';
    }

    /**
     * Prints a radio button and if config value matches value
     * @param string $option_id config id
     * @param $value
     * @param bool $disabled
     * @param string $class_prefix
     */
    private function radio($option_id, $value, $disabled = false, $class_prefix = ''){
        $section = substr($option_id, 0, strpos($option_id, '.'));

        if(is_bool($value))
            $rValue = $value?'1':'0';
        else
            $rValue = $value;
        $disabled = $disabled || $this->is_sealed($section);

        $name = str_replace('.', '_', $option_id);

        echo '<label>';
        echo '<input class="'.$class_prefix.'enabled" type="radio" id="' . $name .
            '" name="' . $option_id . '" value="',$rValue,'" ';
        checked($this->_config->get_boolean($option_id), $value);

        if ($disabled)
            echo 'disabled="disabled" ';

        echo ' />';
    }

    /**
     * Prints checkbox for debug option
     *
     * @param string $option_id
     */
    private function checkbox_debug($option_id) {
        $section = substr($option_id, 0, strrpos($option_id, '.'));
        $section_enabled = $this->_config->get_boolean($section . '.enabled');
        $disabled = $this->is_sealed($section) || !$section_enabled;
        
        if (!$disabled)
            echo '<input type="hidden" name="' . $option_id . '" value="0" />';

        echo '<label>';
        echo '<input class="enabled" type="checkbox" name="' . $option_id .
            '" value="1" ';
        checked($this->_config->get_boolean($option_id) && $section_enabled, true);

        if ($disabled)
            echo 'disabled="disabled" ';

        echo ' />';
    }

    private function sealing_disabled($section) {
        if ($this->is_sealed($section))
            echo 'disabled="disabled" ';
    }

    /**
     * Prints checkbox with admin config option value
     *
     * @param string $option_id
	 * @param boolean $disabled
     */
    private function checkbox_admin($option_id, $disabled = false) {
        if (!$disabled)
            $disabled = $this->_config->get_boolean('common.force_master');
        $checked = $this->_config_admin->get_boolean($option_id) || $disabled;
        if (!$disabled)
            echo '<input type="hidden" name="' . $option_id . '" value="0" />';
        
        echo '<label>';
        $id = str_replace('.', '_', $option_id);
        $class = $disabled ? 'disabled' : 'enabled';
        echo '<input id="' . $id . '" class="' . $class . '" type="checkbox" name="' . $option_id .
            '" value="1"';
        checked($checked, true);
        if ($disabled)
            echo ' disabled="disabled"';

        echo ' />';
    }
}
