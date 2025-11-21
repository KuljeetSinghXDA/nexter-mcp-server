/**
 * Input Validator Utility
 * 
 * Validates block structure and attributes before sending to WordPress
 */

import { StructuredError } from '../types/errors.js';
import {
  missingBlockIdError,
  invalidBlockIdError,
  invalidBlockNameError,
  missingFieldError,
  validationError
} from './error-handler.js';

export interface ValidationResult {
  valid: boolean;
  errors: StructuredError[];
  warnings: string[];
}

/**
 * Validate block structure
 */
export function validateBlock(block: any): ValidationResult {
  const errors: StructuredError[] = [];
  const warnings: string[] = [];

  // Check blockName
  if (!block.blockName) {
    errors.push(missingFieldError('blockName', 'tpgb/*'));
  } else if (!isValidBlockName(block.blockName)) {
    errors.push(invalidBlockNameError(
      block.blockName,
      getSuggestedBlockNames(block.blockName)
    ));
  }

  // Check attrs
  if (!block.attrs) {
    errors.push(missingFieldError('attrs', '{}'));
  } else if (typeof block.attrs !== 'object') {
    errors.push(validationError(
      'attrs',
      'object',
      block.attrs,
      block.blockName
    ));
  }

  // Check block_id for Nexter blocks
  if (block.blockName?.startsWith('tpgb/')) {
    if (!block.attrs?.block_id) {
      errors.push(missingBlockIdError(block.blockName));
    } else if (!isValidBlockId(block.attrs.block_id)) {
      errors.push(invalidBlockIdError(block.attrs.block_id, block.blockName));
    }
  }

  // Validate innerBlocks recursively
  if (block.innerBlocks) {
    if (!Array.isArray(block.innerBlocks)) {
      errors.push(validationError(
        'innerBlocks',
        'array',
        block.innerBlocks,
        block.blockName
      ));
    } else {
      block.innerBlocks.forEach((innerBlock: any, index: number) => {
        const innerResult = validateBlock(innerBlock);
        if (!innerResult.valid) {
          // Prefix errors with innerBlock path
          innerResult.errors.forEach(error => {
            if (error.details?.field) {
              error.details.field = `innerBlocks[${index}].${error.details.field}`;
            }
            errors.push(error);
          });
        }
        warnings.push(...innerResult.warnings);
      });
    }
  }

  // Warnings for optional best practices
  if (block.blockName?.startsWith('tpgb/')) {
    if (!block.innerHTML) {
      warnings.push(`Block ${block.attrs?.block_id || 'unknown'} missing innerHTML - will be auto-generated`);
    }
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings
  };
}

/**
 * Validate array of blocks
 */
export function validateBlocks(blocks: any[]): ValidationResult {
  const allErrors: StructuredError[] = [];
  const allWarnings: string[] = [];

  if (!Array.isArray(blocks)) {
    return {
      valid: false,
      errors: [validationError('blocks', 'array', blocks)],
      warnings: []
    };
  }

  blocks.forEach((block, index) => {
    const result = validateBlock(block);
    
    // Prefix errors with block index
    result.errors.forEach(error => {
      if (error.details?.field) {
        error.details.field = `blocks[${index}].${error.details.field}`;
      } else {
        error.details = { field: `blocks[${index}]` };
      }
      allErrors.push(error);
    });
    
    allWarnings.push(...result.warnings);
  });

  // Check for duplicate block_ids
  const blockIds = blocks
    .filter(b => b.attrs?.block_id)
    .map(b => b.attrs.block_id);
  
  const duplicates = blockIds.filter((id, index) => blockIds.indexOf(id) !== index);
  
  if (duplicates.length > 0) {
    allWarnings.push(`Duplicate block_ids found: ${[...new Set(duplicates)].join(', ')}. Each block should have a unique ID.`);
  }

  return {
    valid: allErrors.length === 0,
    errors: allErrors,
    warnings: allWarnings
  };
}

/**
 * Check if block name is valid
 */
function isValidBlockName(blockName: string): boolean {
  // Nexter blocks must start with tpgb/
  if (blockName.startsWith('tpgb/')) {
    return /^tpgb\/[a-z0-9-]+$/.test(blockName);
  }
  
  // Core WordPress blocks are allowed
  if (blockName.startsWith('core/')) {
    return true;
  }
  
  // Other registered blocks (namespace/name format)
  return /^[a-z0-9-]+\/[a-z0-9-]+$/.test(blockName);
}

/**
 * Check if block_id is valid (4-char hex)
 */
function isValidBlockId(blockId: string): boolean {
  // Accept base format: abcd
  if (/^[a-f0-9]{4}$/.test(blockId)) {
    return true;
  }
  
  // Accept with post ID suffix: abcd_123
  if (/^[a-f0-9]{4}_\d+$/.test(blockId)) {
    return true;
  }
  
  return false;
}

/**
 * Get suggested block names based on input
 */
function getSuggestedBlockNames(input: string): string[] {
  const suggestions: string[] = [];
  
  // Common mappings
  const commonMappings: Record<string, string> = {
    'heading': 'tpgb/tp-heading',
    'paragraph': 'tpgb/tp-pro-paragraph',
    'button': 'tpgb/tp-button',
    'image': 'tpgb/tp-image',
    'container': 'tpgb/tp-container',
    'accordion': 'tpgb/tp-accordion',
    'tabs': 'tpgb/tp-tabs-tours'
  };
  
  // Check direct mapping
  const lower = input.toLowerCase();
  if (commonMappings[lower]) {
    suggestions.push(commonMappings[lower]);
  }
  
  // Check if it's close to a valid format
  if (input.startsWith('tp-') || input.startsWith('tpgb-')) {
    suggestions.push(`tpgb/tp-${input.replace(/^(tp-|tpgb-)/, '')}`);
  }
  
  // Add generic suggestions if no matches
  if (suggestions.length === 0) {
    suggestions.push(
      'tpgb/tp-heading',
      'tpgb/tp-pro-paragraph',
      'tpgb/tp-button'
    );
  }
  
  return suggestions;
}

/**
 * Validate attribute types against expected types
 */
export function validateAttributeType(
  value: any,
  expectedType: string,
  attributeName: string,
  blockName: string
): StructuredError | null {
  const actualType = Array.isArray(value) ? 'array' : typeof value;
  
  // Handle special cases
  if (expectedType === 'array' && !Array.isArray(value)) {
    return validationError(attributeName, 'array', value, blockName);
  }
  
  if (expectedType === 'object' && (actualType !== 'object' || Array.isArray(value))) {
    return validationError(attributeName, 'object', value, blockName);
  }
  
  if (expectedType !== 'array' && expectedType !== 'object' && actualType !== expectedType) {
    return validationError(attributeName, expectedType, value, blockName);
  }
  
  return null;
}

/**
 * Auto-fix common issues
 */
export function autoFixBlock(block: any): { fixed: any; changes: string[] } {
  const changes: string[] = [];
  const fixed = { ...block };
  
  // Auto-generate block_id if missing
  if (block.blockName?.startsWith('tpgb/') && !block.attrs?.block_id) {
    fixed.attrs = fixed.attrs || {};
    fixed.attrs.block_id = Math.random().toString(16).slice(2, 6);
    changes.push(`Generated block_id: ${fixed.attrs.block_id}`);
  }
  
  // Fix block name format
  if (block.blockName && !block.blockName.startsWith('tpgb/') && !block.blockName.includes('/')) {
    const suggestions = getSuggestedBlockNames(block.blockName);
    if (suggestions.length > 0) {
      fixed.blockName = suggestions[0];
      changes.push(`Changed blockName from "${block.blockName}" to "${fixed.blockName}"`);
    }
  }
  
  // Ensure attrs is an object
  if (!fixed.attrs || typeof fixed.attrs !== 'object') {
    fixed.attrs = {};
    changes.push('Initialized attrs as empty object');
  }
  
  // Ensure innerBlocks is an array
  if (fixed.innerBlocks && !Array.isArray(fixed.innerBlocks)) {
    fixed.innerBlocks = [];
    changes.push('Reset innerBlocks to empty array');
  }
  
  return { fixed, changes };
}

/**
 * Validate and auto-fix blocks
 */
export function validateAndFix(blocks: any[]): {
  valid: boolean;
  blocks: any[];
  errors: StructuredError[];
  warnings: string[];
  fixes_applied: string[];
} {
  const fixes_applied: string[] = [];
  const fixedBlocks = blocks.map((block, index) => {
    const { fixed, changes } = autoFixBlock(block);
    if (changes.length > 0) {
      fixes_applied.push(`Block ${index}: ${changes.join(', ')}`);
    }
    return fixed;
  });
  
  const validation = validateBlocks(fixedBlocks);
  
  return {
    valid: validation.valid,
    blocks: fixedBlocks,
    errors: validation.errors,
    warnings: validation.warnings,
    fixes_applied
  };
}
