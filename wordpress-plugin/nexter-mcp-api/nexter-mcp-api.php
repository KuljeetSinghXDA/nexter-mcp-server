<?php
/**
 * Plugin Name: Nexter MCP API
 * Plugin URI: https://github.com/yourusername/nexter-mcp-api
 * Description: REST API endpoints for AI-powered content management with Nexter Blocks. Enables MCP Server to create and modify posts/pages safely. v1.1.0 fixes critical content corruption bug.
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Text Domain: nexter-mcp-api
 * License: MIT
 *
 * Changelog:
 * 1.1.0 - CRITICAL FIX: Replaced regex-based block manipulation with WordPress native array methods.
 *         Fixes content corruption where block attributes were being lost. Uses parse_blocks/serialize_blocks properly.
 * 1.0.0 - Initial release
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin constants
define('NEXTER_MCP_VERSION', '1.1.0');
define('NEXTER_MCP_PATH', plugin_dir_path(__FILE__));
define('NEXTER_MCP_URL', plugin_dir_url(__FILE__));

// Load dependencies
require_once NEXTER_MCP_PATH . 'includes/class-rest-api.php';
require_once NEXTER_MCP_PATH . 'includes/class-block-parser.php';
require_once NEXTER_MCP_PATH . 'includes/class-validator.php';

// Initialize plugin
add_action('plugins_loaded', 'nexter_mcp_init');

function nexter_mcp_init() {
    // Check if Nexter Blocks is active
    if (!class_exists('Tpgb_Gutenberg_Loader') && !class_exists('TPGBP_Gutenberg_Pro_Loader')) {
        add_action('admin_notices', 'nexter_mcp_missing_nexter_notice');
        return;
    }
    
    // Initialize REST API
    Nexter_MCP_REST_API::init();
    
    // Log initialization
    error_log('Nexter MCP API v' . NEXTER_MCP_VERSION . ' initialized (Phase 1 Fix Applied) - Timestamp: ' . date('Y-m-d H:i:s'));
}

function nexter_mcp_missing_nexter_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong>Nexter MCP API</strong> requires 
            <strong>Nexter Blocks</strong> (free or Pro) to be installed and activated.
            Please install Nexter Blocks from WordPress.org or upload the Pro version.
        </p>
    </div>
    <?php
}

// Activation hook
register_activation_hook(__FILE__, 'nexter_mcp_activate');

function nexter_mcp_activate() {
    // Flush rewrite rules for REST API
    flush_rewrite_rules();
    
    // Log activation
    error_log('Nexter MCP API activated successfully');
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'nexter_mcp_deactivate');

function nexter_mcp_deactivate() {
    flush_rewrite_rules();
    error_log('Nexter MCP API deactivated');
}

// Add settings link on plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nexter_mcp_add_settings_link');

function nexter_mcp_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=nexter-mcp-api') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Add admin menu
add_action('admin_menu', 'nexter_mcp_add_admin_menu');

function nexter_mcp_add_admin_menu() {
    add_options_page(
        'Nexter MCP API Settings',
        'Nexter MCP API',
        'manage_options',
        'nexter-mcp-api',
        'nexter_mcp_settings_page'
    );
}

function nexter_mcp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Nexter MCP API Settings</h1>
        
        <div class="card">
            <h2>Connection Status</h2>
            <p>
                <strong>Plugin Version:</strong> <?php echo NEXTER_MCP_VERSION; ?><br>
                <strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?><br>
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Nexter Blocks:</strong> 
                <?php
                if (class_exists('TPGBP_Gutenberg_Pro_Loader')) {
                    echo '✅ Pro Active (v' . (defined('TPGBP_VERSION') ? TPGBP_VERSION : 'unknown') . ')';
                } elseif (class_exists('Tpgb_Gutenberg_Loader')) {
                    echo '✅ Free Active (v' . (defined('TPGB_VERSION') ? TPGB_VERSION : 'unknown') . ')';
                } else {
                    echo '❌ Not Installed';
                }
                ?>
            </p>
        </div>
        
        <div class="card">
            <h2>REST API Endpoints</h2>
            <p>Base URL: <code><?php echo rest_url('nexter-mcp/v1/'); ?></code></p>
            <ul>
                <li><code>GET /content/{id}</code> - Get post/page with parsed blocks</li>
                <li><code>POST /content/create</code> - Create new post/page</li>
                <li><code>POST /content/{id}/update</code> - Update existing content</li>
                <li><code>GET /content/search</code> - Search posts/pages</li>
                <li><code>GET /content/{id}/blocks</code> - Analyze blocks</li>
                <li><code>POST /validate</code> - Validate block structure</li>
                <li><code>GET /content/{id}/revisions</code> - Get revisions</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>Authentication</h2>
            <p>
                This plugin uses <strong>WordPress Application Passwords</strong> for authentication.
            </p>
            <ol>
                <li>Go to: <a href="<?php echo admin_url('profile.php#application-passwords-section'); ?>">Users → Your Profile → Application Passwords</a></li>
                <li>Enter name: "Nexter MCP Server"</li>
                <li>Click "Add New Application Password"</li>
                <li>Copy the generated password (format: xxxx xxxx xxxx xxxx)</li>
                <li>Add to your MCP Server .env file</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>Test Connection</h2>
            <p>Test REST API access with curl:</p>
            <pre style="background:#f5f5f5;padding:10px;overflow-x:auto;">curl -u "your-username:your-app-password" \
  <?php echo rest_url('wp/v2/users/me'); ?></pre>
        </div>
        
        <div class="card">
            <h2>ℹ️ Setup Complete</h2>
            <p><strong>This plugin is ready to use!</strong> No configuration needed.</p>
            <p>The REST API endpoints are now active and can be accessed by your MCP Server using WordPress Application Passwords for authentication.</p>
        </div>
    </div>
    <?php
}
