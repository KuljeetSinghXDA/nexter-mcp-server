<?php
/**
 * Block Validator
 * 
 * Validates Nexter block structure against WordPress and Nexter requirements
 */

if (!defined('ABSPATH')) {
    exit;
}

class Nexter_MCP_Validator {
    
    /**
     * Validate entire block tree
     */
    public function validate_block_tree($blocks, $strict = false) {
        $errors = [];
        $warnings = [];
        
        if (!is_array($blocks)) {
            return [
                'valid' => false,
                'errors' => ['Blocks must be an array'],
                'warnings' => []
            ];
        }
        
        // Collect all block IDs for duplicate check
        $block_ids = [];
        $this->collect_block_ids($blocks, $block_ids);
        
        // Check for duplicates
        $duplicates = $this->find_duplicates($block_ids);
        if (!empty($duplicates)) {
            $errors[] = 'Duplicate block_ids found: ' . implode(', ', $duplicates);
        }
        
        // Validate each block
        foreach ($blocks as $index => $block) {
            $block_result = $this->validate_block($block, $index);
            
            if (!empty($block_result['errors'])) {
                $errors = array_merge($errors, $block_result['errors']);
            }
            
            if (!empty($block_result['warnings'])) {
                $warnings = array_merge($warnings, $block_result['warnings']);
            }
        }
        
        // In strict mode, warnings count as errors
        $is_valid = empty($errors) && ($strict ? empty($warnings) : true);
        
        return [
            'valid' => $is_valid,
            'errors' => $errors,
            'warnings' => $warnings,
            'block_count' => count($blocks)
        ];
    }
    
    /**
     * Validate single block
     */
    private function validate_block($block, $index) {
        $errors = [];
        $warnings = [];
        
        // Check structure
        if (!isset($block['blockName'])) {
            $errors[] = "Block at index {$index}: missing blockName";
        }
        
        if (!isset($block['attrs'])) {
            $errors[] = "Block at index {$index}: missing attrs object";
        }
        
        // Check if block type is registered in WordPress
        $blockName = $block['blockName'] ?? '';
        if ($blockName && !WP_Block_Type_Registry::get_instance()->is_registered($blockName)) {
            $warnings[] = "Block {$blockName}: not registered in WordPress (may need plugin activation)";
        }
        
        // Validate Nexter blocks
        if (strpos($blockName, 'tpgb/') === 0) {
            $nexter_result = $this->validate_nexter_block($block);
            $errors = array_merge($errors, $nexter_result['errors']);
            $warnings = array_merge($warnings, $nexter_result['warnings']);
        }
        
        // Validate inner blocks recursively
        if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $inner_index => $inner_block) {
                $inner_result = $this->validate_block($inner_block, "{$index}.{$inner_index}");
                $errors = array_merge($errors, $inner_result['errors']);
                $warnings = array_merge($warnings, $inner_result['warnings']);
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validate Nexter-specific block requirements
     */
    private function validate_nexter_block($block) {
        $errors = [];
        $warnings = [];
        
        $attrs = $block['attrs'] ?? [];
        $blockName = $block['blockName'];
        
        // Check for block_id (critical for Nexter)
        if (!isset($attrs['block_id']) || empty($attrs['block_id'])) {
            $errors[] = "{$blockName}: missing required block_id attribute";
        }
        
        // Validate repeater fields
        if (isset($attrs['accordianList']) && is_array($attrs['accordianList'])) {
            foreach ($attrs['accordianList'] as $i => $item) {
                if (!isset($item['_key']) || empty($item['_key'])) {
                    $errors[] = "{$blockName}: accordianList item {$i} missing _key";
                }
            }
        }
        
        if (isset($attrs['testimonialList']) && is_array($attrs['testimonialList'])) {
            foreach ($attrs['testimonialList'] as $i => $item) {
                if (!isset($item['_key']) || empty($item['_key'])) {
                    $errors[] = "{$blockName}: testimonialList item {$i} missing _key";
                }
            }
        }
        
        if (isset($attrs['stylishList']) && is_array($attrs['stylishList'])) {
            foreach ($attrs['stylishList'] as $i => $item) {
                if (!isset($item['_key']) || empty($item['_key'])) {
                    $errors[] = "{$blockName}: stylishList item {$i} missing _key";
                }
            }
        }
        
        // Check for common issues
        if ($blockName === 'tpgb/tp-accordion') {
            if (isset($attrs['defaultAct']) && $attrs['defaultAct'] !== '') {
                $default_index = intval($attrs['defaultAct']);
                $item_count = isset($attrs['accordianList']) ? count($attrs['accordianList']) : 0;
                
                if ($default_index > $item_count) {
                    $warnings[] = "Accordion: defaultAct index {$default_index} exceeds item count {$item_count}";
                }
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Collect all block_ids from tree
     */
    private function collect_block_ids($blocks, &$ids) {
        foreach ($blocks as $block) {
            if (isset($block['attrs']['block_id'])) {
                $ids[] = $block['attrs']['block_id'];
            }
            
            if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $this->collect_block_ids($block['innerBlocks'], $ids);
            }
        }
    }
    
    /**
     * Find duplicate values in array
     */
    private function find_duplicates($array) {
        $counts = array_count_values($array);
        $duplicates = [];
        
        foreach ($counts as $value => $count) {
            if ($count > 1) {
                $duplicates[] = $value;
            }
        }
        
        return $duplicates;
    }
}