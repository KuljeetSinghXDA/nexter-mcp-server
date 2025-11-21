/**
 * Block Formatter Utility
 *
 * Formats blocks into complete Gutenberg structure before sending to WordPress
 */
/**
 * Format a single block to Gutenberg structure
 */
export declare function formatBlock(block: any, postId?: number): any;
/**
 * Format an array of blocks for WordPress
 * This adds all required Gutenberg fields that serialize_blocks() expects
 */
export declare function formatBlocksForWordPress(blocks: any[], postId?: number): any[];
/**
 * Validate that a block has minimum required structure
 */
export declare function validateBlockStructure(block: any): {
    valid: boolean;
    errors: string[];
};
/**
 * Pre-process blocks before sending to WordPress
 * - Adds missing Gutenberg fields
 * - Ensures proper block_id format
 * - Validates structure
 */
export declare function preprocessBlocks(blocks: any[]): {
    formatted: any[];
    warnings: string[];
    needsPostId: boolean;
};
//# sourceMappingURL=block-formatter.d.ts.map