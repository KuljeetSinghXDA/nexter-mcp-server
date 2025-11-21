<?php
/**
 * Nexter MCP REST API
 * 
 * Handles all REST API endpoints for MCP Server communication
 */

if (!defined('ABSPATH')) {
    exit;
}

class Nexter_MCP_REST_API {
    
    private static $instance = null;
    
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register all REST API routes
     */
    public function register_routes() {
        $namespace = 'nexter-mcp/v1';
        
        // 1. Get content with parsed blocks
        register_rest_route($namespace, '/content/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_content'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ]
        ]);
        
        // 2. Create new content
        register_rest_route($namespace, '/content/create', [
            'methods' => 'POST',
            'callback' => [$this, 'create_content'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'post_type' => [
                    'required' => true,
                    'enum' => ['post', 'page'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'blocks' => [
                    'required' => true,
                    'type' => 'array',
                    'validate_callback' => [$this, 'validate_blocks_array']
                ],
                'status' => [
                    'default' => 'draft',
                    'enum' => ['draft', 'pending'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'excerpt' => [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ],
                'categories' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer']
                ],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer']
                ]
            ]
        ]);
        
        // 3. Update existing content
        register_rest_route($namespace, '/content/(?P<id>\d+)/update', [
            'methods' => 'POST',
            'callback' => [$this, 'update_content'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ],
                'blocks' => [
                    'required' => true,
                    'type' => 'array'
                ],
                'title' => [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'create_revision' => [
                    'type' => 'boolean',
                    'default' => true
                ]
            ]
        ]);
        
        // 4. Search content
        register_rest_route($namespace, '/content/search', [
            'methods' => 'GET',
            'callback' => [$this, 'search_content'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'post_type' => [
                    'default' => 'post',
                    'enum' => ['post', 'page', 'any'],
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'limit' => [
                    'default' => 10,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // 5. Analyze blocks in content
        register_rest_route($namespace, '/content/(?P<id>\d+)/blocks', [
            'methods' => 'GET',
            'callback' => [$this, 'analyze_blocks'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // 6. Validate blocks
        register_rest_route($namespace, '/validate', [
            'methods' => 'POST',
            'callback' => [$this, 'validate_blocks'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'blocks' => [
                    'required' => true,
                    'type' => 'array'
                ],
                'strict' => [
                    'type' => 'boolean',
                    'default' => false
                ]
            ]
        ]);
        
        // 7. Get revisions
        register_rest_route($namespace, '/content/(?P<id>\d+)/revisions', [
            'methods' => 'GET',
            'callback' => [$this, 'get_revisions'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
    }
    
    /**
     * Permission check - user must be authenticated and have edit_posts capability
     */
    public function check_permission($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'not_authenticated',
                'Authentication required. Use WordPress Application Password.',
                ['status' => 401]
            );
        }
        
        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'insufficient_permissions',
                'User must have edit_posts capability',
                ['status' => 403]
            );
        }
        
        return true;
    }
    
    /**
     * Get post/page content with parsed blocks
     */
    public function get_content($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);
        
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', ['status' => 404]);
        }
        
        // Check if user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('forbidden', 'Cannot edit this post', ['status' => 403]);
        }
        
        // Parse Gutenberg blocks
        $blocks = parse_blocks($post->post_content);
        
        // Enhance blocks with metadata
        $enhanced_blocks = array_map(function($block) {
            $blockName = $block['blockName'] ?? '';
            return [
                'blockName' => $blockName,
                'attrs' => $block['attrs'],
                'innerHTML' => $block['innerHTML'],
                'innerBlocks' => $block['innerBlocks'],
                'innerContent' => $block['innerContent'],
                'isNexterBlock' => !empty($blockName) && strpos($blockName, 'tpgb/') === 0,
                'blockId' => isset($block['attrs']['block_id']) ? $block['attrs']['block_id'] : null
            ];
        }, $blocks);
        
        // Extract unique block types
        $block_types = array_unique(array_map(function($block) {
            return $block['blockName'];
        }, $blocks));
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'status' => $post->post_status,
                'type' => $post->post_type,
                'modified' => $post->post_modified,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'blocks' => $enhanced_blocks,
                'block_types' => array_values($block_types),
                'preview_url' => get_preview_post_link($post),
                'edit_url' => get_edit_post_link($post, 'raw')
            ]
        ]);
    }
    
    /**
     * Create new post/page
     */
    public function create_content($request) {
        $post_type = $request->get_param('post_type');
        $title = $request->get_param('title');
        $blocks = $request->get_param('blocks');
        $status = $request->get_param('status');
        $excerpt = $request->get_param('excerpt');
        $categories = $request->get_param('categories');
        $tags = $request->get_param('tags');

        // DEBUG: Log what we receive (only in WP_DEBUG mode)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Nexter MCP] Blocks received from Node.js:');
            error_log('[Nexter MCP]   Block count: ' . count($blocks));
            foreach ($blocks as $i => $block) {
                if (isset($block['blockName']) && $block['blockName'] === 'tpgb/tp-pro-paragraph') {
                    error_log("[Nexter MCP]   Block {$i} - " . $block['blockName']);
                    error_log('[Nexter MCP]     attrs: ' . json_encode($block['attrs']));
                    error_log('[Nexter MCP]     attrs.content exists: ' . (isset($block['attrs']['content']) ? 'YES' : 'NO'));
                    if (isset($block['attrs']['content'])) {
                        error_log('[Nexter MCP]     attrs.content value: ' . substr($block['attrs']['content'], 0, 50) . '...');
                    }
                }
            }
        }

        // Serialize blocks for initial post creation
        $content = serialize_blocks($blocks);

        // DEBUG: Log what serialize_blocks outputs (only in WP_DEBUG mode)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Nexter MCP] After serialize_blocks():');
            error_log('[Nexter MCP]   Content length: ' . strlen($content));
            error_log('[Nexter MCP]   Content preview: ' . substr($content, 0, 300));
        }
        
        // Prepare post data
        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => $post_type,
            'post_author' => get_current_user_id()
        ];
        
        if ($excerpt) {
            $post_data['post_excerpt'] = $excerpt;
        }
        
        // Create post
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return new WP_Error(
                'creation_failed',
                $post_id->get_error_message(),
                ['status' => 500]
            );
        }
        
        // ✅ WORDPRESS BEST PRACTICE: Modify blocks array BEFORE serialization
        // Append post_id to block_id in the array structure (NOT on HTML string)
        $blocks_with_post_id = $this->append_post_id_to_blocks_array($blocks, $post_id);
        $updated_content = serialize_blocks($blocks_with_post_id);
        
        // Update post with corrected block_ids
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $updated_content
        ]);
        
        // Set categories (posts only)
        if ($post_type === 'post' && !empty($categories) && is_array($categories)) {
            wp_set_post_categories($post_id, array_map('absint', $categories));
        }
        
        // Set tags (posts only)
        if ($post_type === 'post' && !empty($tags) && is_array($tags)) {
            wp_set_post_tags($post_id, array_map('absint', $tags));
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'title' => $title,
                'status' => $status,
                'edit_url' => get_edit_post_link($post_id, 'raw'),
                'preview_url' => get_preview_post_link($post_id),
                'message' => 'Content created successfully as ' . $status
            ]
        ]);
    }
    
    /**
     * Update existing content
     */
    public function update_content($request) {
        $post_id = $request->get_param('id');
        $blocks = $request->get_param('blocks');
        $title = $request->get_param('title');
        $create_revision = $request->get_param('create_revision');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', ['status' => 404]);
        }
        
        // Check edit permission
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('forbidden', 'Cannot edit this post', ['status' => 403]);
        }
        
        // ✅ WORDPRESS BEST PRACTICE: Ensure block_ids have post_id suffix
        // If blocks don't have post_id suffix yet, add it
        $blocks_with_post_id = $this->ensure_blocks_have_post_id($blocks, $post_id);
        
        // Serialize blocks
        $content = serialize_blocks($blocks_with_post_id);
        
        // Prepare update data
        $update_data = [
            'ID' => $post_id,
            'post_content' => $content
        ];
        
        if ($title) {
            $update_data['post_title'] = $title;
        }
        
        // Update post (WordPress automatically creates revision if enabled)
        $result = wp_update_post($update_data, true);
        
        if (is_wp_error($result)) {
            return new WP_Error(
                'update_failed',
                $result->get_error_message(),
                ['status' => 500]
            );
        }
        
        // Get latest revision
        $revisions = wp_get_post_revisions($post_id, ['posts_per_page' => 1]);
        $latest_revision = !empty($revisions) ? reset($revisions) : null;
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'revision_id' => $latest_revision ? $latest_revision->ID : null,
                'revision_date' => $latest_revision ? $latest_revision->post_modified : null,
                'preview_url' => get_preview_post_link($post_id),
                'edit_url' => get_edit_post_link($post_id, 'raw'),
                'message' => 'Content updated successfully. Revision created.'
            ]
        ]);
    }
    
    /**
     * Search posts/pages
     */
    public function search_content($request) {
        $query = $request->get_param('query');
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        
        // Prepare WP_Query args
        $args = [
            's' => $query,
            'post_type' => $post_type === 'any' ? ['post', 'page'] : $post_type,
            'posts_per_page' => min($limit, 50),
            'post_status' => ['publish', 'draft', 'pending'],
            'orderby' => 'relevance',
            'order' => 'DESC'
        ];
        
        $query_result = new WP_Query($args);
        
        // Format results
        $posts = array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'excerpt' => wp_trim_words($post->post_content, 30, '...'),
                'url' => get_permalink($post->ID),
                'edit_url' => get_edit_post_link($post->ID, 'raw')
            ];
        }, $query_result->posts);
        
        wp_reset_postdata();
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'query' => $query,
                'found' => $query_result->found_posts,
                'returned' => count($posts),
                'posts' => $posts
            ]
        ]);
    }
    
    /**
     * Analyze blocks in content
     */
    public function analyze_blocks($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);
        
        if (!$post) {
            return new WP_Error('not_found', 'Post not found', ['status' => 404]);
        }
        
        // Parse blocks
        $blocks = parse_blocks($post->post_content);
        
        // Identify Nexter blocks
        $nexter_blocks = array_filter($blocks, function($block) {
            return strpos($block['blockName'], 'tpgb/') === 0;
        });
        
        // Count blocks by type
        $block_inventory = [];
        foreach ($blocks as $block) {
            $type = $block['blockName'];
            if (!isset($block_inventory[$type])) {
                $block_inventory[$type] = 0;
            }
            $block_inventory[$type]++;
        }
        
        // Extract unique block types
        $block_types = array_unique(array_map(function($block) {
            return $block['blockName'];
        }, $blocks));
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'title' => $post->post_title,
                'total_blocks' => count($blocks),
                'nexter_blocks' => count($nexter_blocks),
                'block_types' => array_values($block_types),
                'block_inventory' => $block_inventory,
                'blocks_detail' => array_map(function($block) use ($blocks) {
                    return [
                        'blockName' => $block['blockName'],
                        'blockId' => isset($block['attrs']['block_id']) ? $block['attrs']['block_id'] : null,
                        'position' => array_search($block, $blocks, true),
                        'hasInnerBlocks' => !empty($block['innerBlocks'])
                    ];
                }, $nexter_blocks)
            ]
        ]);
    }
    
    /**
     * Validate blocks structure
     */
    public function validate_blocks($request) {
        $blocks = $request->get_param('blocks');
        $strict = $request->get_param('strict');
        
        $validator = new Nexter_MCP_Validator();
        $result = $validator->validate_block_tree($blocks, $strict);
        
        return rest_ensure_response($result);
    }
    
    /**
     * Get post revisions
     */
    public function get_revisions($request) {
        $post_id = $request->get_param('id');
        
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('forbidden', 'Cannot access revisions', ['status' => 403]);
        }
        
        $revisions = wp_get_post_revisions($post_id, [
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        $revision_data = array_map(function($rev) {
            return [
                'id' => $rev->ID,
                'date' => $rev->post_modified,
                'date_gmt' => $rev->post_modified_gmt,
                'author' => get_the_author_meta('display_name', $rev->post_author),
                'parent_id' => $rev->post_parent,
                'restore_url' => wp_nonce_url(
                    admin_url("revision.php?action=restore&revision={$rev->ID}"),
                    "restore-post_{$rev->post_parent}"
                )
            ];
        }, $revisions);
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'revision_count' => count($revision_data),
                'revisions' => array_values($revision_data)
            ]
        ]);
    }
    
    /**
     * Validate blocks array structure
     */
    public function validate_blocks_array($blocks, $request, $param) {
        if (!is_array($blocks)) {
            return new WP_Error('invalid_blocks', 'Blocks must be an array');
        }
        
        if (empty($blocks)) {
            return new WP_Error('empty_blocks', 'Blocks array cannot be empty');
        }
        
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                return new WP_Error('invalid_block', 'Each block must be an array/object');
            }
            
            if (!isset($block['blockName'])) {
                return new WP_Error('missing_blockname', 'Each block must have blockName');
            }
            
            if (!isset($block['attrs'])) {
                return new WP_Error('missing_attrs', 'Each block must have attrs object');
            }
        }
        
        return true;
    }
    
    /**
     * ✅ WORDPRESS BEST PRACTICE: Append post ID to block_id in array structure
     *
     * Modifies block array BEFORE serialize_blocks() - NOT after on HTML string.
     * This preserves all attributes and avoids JSON corruption.
     *
     * Nexter format: blockId_postId (e.g., 6574_255)
     *
     * @param array $blocks Array of block structures
     * @param int $post_id WordPress post ID
     * @return array Modified blocks array with updated block_ids
     */
    private function append_post_id_to_blocks_array($blocks, $post_id) {
        $modified_blocks = [];
        
        foreach ($blocks as $block) {
            // Ensure attrs array exists
            if (!isset($block['attrs'])) {
                $block['attrs'] = [];
            }
            
            // Update block_id if present and doesn't already have post_id suffix
            if (isset($block['attrs']['block_id'])) {
                $block_id = $block['attrs']['block_id'];
                
                // Only append if it's a 4-char hex without underscore (not already updated)
                if (preg_match('/^[a-f0-9]{4}$/', $block_id)) {
                    $block['attrs']['block_id'] = $block_id . '_' . $post_id;
                }
            }
            
            // Recursively process innerBlocks (for nested structures like accordions, tabs, containers)
            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $block['innerBlocks'] = $this->append_post_id_to_blocks_array(
                    $block['innerBlocks'],
                    $post_id
                );
            }
            
            $modified_blocks[] = $block;
        }
        
        return $modified_blocks;
    }
    
    /**
     * Ensure blocks have post_id suffix (for update operations)
     *
     * Similar to append_post_id_to_blocks_array but handles both cases:
     * - Blocks that already have post_id (keep as-is)
     * - Blocks that need post_id appended (add it)
     *
     * @param array $blocks Array of block structures
     * @param int $post_id WordPress post ID
     * @return array Modified blocks array
     */
    private function ensure_blocks_have_post_id($blocks, $post_id) {
        $modified_blocks = [];
        
        foreach ($blocks as $block) {
            // Ensure attrs array exists
            if (!isset($block['attrs'])) {
                $block['attrs'] = [];
            }
            
            // Check block_id and add post_id if needed
            if (isset($block['attrs']['block_id'])) {
                $block_id = $block['attrs']['block_id'];
                
                // If it's a 4-char hex without post_id, append it
                if (preg_match('/^[a-f0-9]{4}$/', $block_id)) {
                    $block['attrs']['block_id'] = $block_id . '_' . $post_id;
                }
                // If it has wrong post_id (e.g., from copied content), update it
                elseif (preg_match('/^[a-f0-9]{4}_\d+$/', $block_id)) {
                    // Extract the 4-char hex and append correct post_id
                    $hex_part = substr($block_id, 0, 4);
                    $block['attrs']['block_id'] = $hex_part . '_' . $post_id;
                }
            }
            
            // Recursively process innerBlocks
            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $block['innerBlocks'] = $this->ensure_blocks_have_post_id(
                    $block['innerBlocks'],
                    $post_id
                );
            }
            
            $modified_blocks[] = $block;
        }
        
        return $modified_blocks;
    }
}