<?php
/**
 * Block Parser Utilities
 * 
 * Helper functions for parsing and manipulating Gutenberg blocks
 */

if (!defined('ABSPATH')) {
    exit;
}

class Nexter_MCP_Block_Parser {
    
    /**
     * Find block by block_id in tree
     */
    public static function find_block_by_id($blocks, $block_id) {
        foreach ($blocks as $block) {
            if (isset($block['attrs']['block_id']) && $block['attrs']['block_id'] === $block_id) {
                return $block;
            }
            
            // Search in inner blocks
            if (isset($block['innerBlocks']) && is_array($block['innerBlocks']) && !empty($block['innerBlocks'])) {
                $found = self::find_block_by_id($block['innerBlocks'], $block_id);
                if ($found) {
                    return $found;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Count blocks by type
     */
    public static function count_blocks_by_type($blocks) {
        $counts = [];
        
        self::traverse_blocks($blocks, function($block) use (&$counts) {
            $type = $block['blockName'];
            if (!isset($counts[$type])) {
                $counts[$type] = 0;
            }
            $counts[$type]++;
        });
        
        return $counts;
    }
    
    /**
     * Get all Nexter blocks from tree
     */
    public static function get_nexter_blocks($blocks) {
        $nexter_blocks = [];
        
        self::traverse_blocks($blocks, function($block) use (&$nexter_blocks) {
            if (strpos($block['blockName'], 'tpgb/') === 0) {
                $nexter_blocks[] = $block;
            }
        });
        
        return $nexter_blocks;
    }
    
    /**
     * Traverse block tree with callback
     */
    private static function traverse_blocks($blocks, $callback) {
        foreach ($blocks as $block) {
            $callback($block);
            
            if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                self::traverse_blocks($block['innerBlocks'], $callback);
            }
        }
    }
    
    /**
     * Generate unique block ID
     *
     * Match Nexter MCP convention: 4-character lowercase hex
     * (base ID before WordPress appends _postId suffix)
     *
     * Uses cryptographically secure random_bytes() matching Node.js crypto.randomBytes(2)
     */
    public static function generate_block_id() {
        return bin2hex(random_bytes(2)); // Generates 4-char hex (same as Node.js)
    }
}
