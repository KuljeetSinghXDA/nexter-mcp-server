/**
 * Block Formatter Utility
 *
 * Formats blocks into complete Gutenberg structure before sending to WordPress
 */
/**
 * Generate 4-character hex ID matching Nexter's format
 */
function generateBlockId() {
    return Math.random().toString(16).substring(2, 6);
}
/**
 * Generate innerHTML for Nexter blocks based on their attributes
 * This is critical - Gutenberg editor expects innerHTML to contain the rendered HTML
 */
function generateInnerHTML(blockName, attrs) {
    // Handle tpgb/tp-heading
    if (blockName === 'tpgb/tp-heading') {
        const tag = attrs.tTag || 'h2';
        const title = attrs.title || '';
        const blockId = attrs.block_id || '';
        return `<${tag} class="wp-block-tpgb-tp-heading tp-core-heading tpgb-block-${blockId}">${title}</${tag}>`;
    }
    // Handle tpgb/tp-pro-paragraph
    if (blockName === 'tpgb/tp-pro-paragraph') {
        const blockId = attrs.block_id || '';
        const content = attrs.content || '';
        const showTitle = attrs.Showtitle !== false;
        const title = attrs.title || '';
        const titleTag = attrs.titleTag || 'h3';
        const descTag = attrs.descTag || 'div'; // Changed default to 'div' to match Nexter behavior
        let html = `<div class="tpgb-pro-paragraph tpgb-block-${blockId}">`;
        if (showTitle && title) {
            html += `<${titleTag} class="pro-heading-inner">${title}</${titleTag}>`;
        }
        if (content) {
            // CRITICAL FIX: Must wrap content in descTag inside pro-paragraph-inner
            // This matches Nexter's actual innerHTML structure
            html += `<div class="pro-paragraph-inner"><${descTag}>${content}</${descTag}></div>`;
        }
        html += `</div>`;
        return html;
    }
    // Handle tpgb/tp-image
    if (blockName === 'tpgb/tp-image') {
        const blockId = attrs.block_id || '';
        const url = attrs.url || '';
        const alt = attrs.alt || '';
        const caption = attrs.caption || '';
        let html = `<div class="wp-block-tpgb-tp-image tpgb-image tpgb-block-${blockId}">`;
        html += `<figure class="tpgb-figure">`;
        if (url) {
            html += `<img src="${url}" alt="${alt}" class="tpgb-img-inner" />`;
        }
        if (caption) {
            html += `<figcaption>${caption}</figcaption>`;
        }
        html += `</figure></div>`;
        return html;
    }
    // Handle tpgb/tp-button
    if (blockName === 'tpgb/tp-button') {
        const blockId = attrs.block_id || '';
        const text = attrs.buttonText || attrs.text || 'Button';
        const url = attrs.buttonLink?.url || '#';
        return `<div class="tpgb-adv-button tpgb-block-${blockId}"><a class="button-link-wrap" href="${url}">${text}</a></div>`;
    }
    // For other Nexter blocks, return minimal structure
    if (blockName.startsWith('tpgb/')) {
        const blockId = attrs.block_id || '';
        const blockClass = blockName.replace('tpgb/', '');
        return `<div class="tpgb-${blockClass} tpgb-block-${blockId}"></div>`;
    }
    return '';
}
/**
 * Format a single block to Gutenberg structure
 */
export function formatBlock(block, postId) {
    // Ensure block has required fields
    if (!block.blockName) {
        throw new Error('Block must have blockName');
    }
    if (!block.attrs) {
        throw new Error('Block must have attrs');
    }
    // Generate or ensure block_id exists in proper format
    if (!block.attrs.block_id) {
        // Generate new ID in Nexter format: 4char_postId
        const hexId = generateBlockId();
        block.attrs.block_id = postId ? `${hexId}_${postId}` : hexId;
    }
    else if (postId && !block.attrs.block_id.includes('_')) {
        // Append postId if not already there
        block.attrs.block_id = `${block.attrs.block_id}_${postId}`;
    }
    // Format inner blocks recursively if they exist
    const innerBlocks = block.innerBlocks
        ? block.innerBlocks.map((inner) => formatBlock(inner, postId))
        : [];
    // Generate innerHTML if not provided (critical for Nexter blocks!)
    const innerHTML = block.innerHTML || generateInnerHTML(block.blockName, block.attrs);
    // Build complete Gutenberg block structure
    return {
        blockName: block.blockName,
        attrs: block.attrs,
        innerBlocks: innerBlocks,
        innerHTML: innerHTML,
        innerContent: block.innerContent || (innerBlocks.length > 0 ? innerBlocks.map(() => null) : [innerHTML])
    };
}
/**
 * Format an array of blocks for WordPress
 * This adds all required Gutenberg fields that serialize_blocks() expects
 */
export function formatBlocksForWordPress(blocks, postId) {
    return blocks.map(block => formatBlock(block, postId));
}
/**
 * Validate that a block has minimum required structure
 */
export function validateBlockStructure(block) {
    const errors = [];
    if (!block.blockName) {
        errors.push('Missing required field: blockName');
    }
    if (!block.attrs || typeof block.attrs !== 'object') {
        errors.push('Missing or invalid field: attrs (must be object)');
    }
    // Check for Nexter blocks
    if (block.blockName?.startsWith('tpgb/')) {
        if (!block.attrs?.block_id) {
            errors.push('Nexter blocks must have block_id attribute');
        }
    }
    return {
        valid: errors.length === 0,
        errors
    };
}
/**
 * Pre-process blocks before sending to WordPress
 * - Adds missing Gutenberg fields
 * - Ensures proper block_id format
 * - Validates structure
 */
export function preprocessBlocks(blocks) {
    const warnings = [];
    let needsPostId = false;
    // Validate all blocks first
    blocks.forEach((block, index) => {
        const validation = validateBlockStructure(block);
        if (!validation.valid) {
            warnings.push(`Block at index ${index}: ${validation.errors.join(', ')}`);
        }
        // Check if any block needs a post ID for proper block_id
        if (block.blockName?.startsWith('tpgb/') && block.attrs?.block_id && !block.attrs.block_id.includes('_')) {
            needsPostId = true;
        }
    });
    // Format blocks (postId will be added after creation in WordPress plugin)
    const formatted = blocks.map(block => {
        const attrs = {
            ...block.attrs,
            // Ensure block_id exists for Nexter blocks
            block_id: block.attrs?.block_id || (block.blockName?.startsWith('tpgb/') ? generateBlockId() : undefined)
        };
        // Generate innerHTML if not provided (this reads from attrs.content)
        const innerHTML = block.innerHTML || generateInnerHTML(block.blockName, attrs);
        // CRITICAL: Keep ALL attributes - WordPress serialize_blocks() needs them!
        // The innerHTML is only for Gutenberg editor preview, not the actual content rendering
        const finalAttrs = { ...attrs };
        return {
            blockName: block.blockName,
            attrs: finalAttrs,
            innerBlocks: block.innerBlocks || [],
            innerHTML: innerHTML,
            innerContent: block.innerContent || [innerHTML]
        };
    });
    return {
        formatted,
        warnings,
        needsPostId
    };
}
//# sourceMappingURL=block-formatter.js.map