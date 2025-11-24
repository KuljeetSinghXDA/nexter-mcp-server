/**
 * Input Validator Utility
 *
 * Validates block structure and attributes before sending to WordPress
 */

import { randomBytes } from 'crypto';
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
    fixed.attrs.block_id = randomBytes(2).toString('hex');
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

/**
 * Validate block attributes against schema
 */
export function validateBlockAgainstSchema(
  block: any,
  schema: any
): ValidationResult {
  const errors: StructuredError[] = [];
  const warnings: string[] = [];

  if (!schema || !schema.attributes) {
    // No schema available - skip schema validation
    return { valid: true, errors: [], warnings: ['No schema available for validation'] };
  }

  const attrs = block.attrs || {};

  // Validate each attribute in schema
  for (const [attrName, attrSchema] of Object.entries(schema.attributes)) {
    const attrDef: any = attrSchema;
    const value = attrs[attrName];

    // Check if attribute is required
    if (attrDef.required && value === undefined) {
      errors.push(missingFieldError(
        attrName,
        attrDef.default !== undefined ? String(attrDef.default) : 'required'
      ));
      continue;
    }

    // Skip validation if value is undefined and not required
    if (value === undefined) {
      continue;
    }

    // Validate type
    if (attrDef.type) {
      const typeError = validateAttributeType(value, attrDef.type, attrName, block.blockName);
      if (typeError) {
        errors.push(typeError);
        continue; // Skip further validation if type is wrong
      }
    }

    // Validate enum values
    if (attrDef.enum && Array.isArray(attrDef.enum)) {
      if (!attrDef.enum.includes(value)) {
        errors.push(validationError(
          attrName,
          `one of: ${attrDef.enum.slice(0, 5).join(', ')}${attrDef.enum.length > 5 ? '...' : ''}`,
          value,
          block.blockName
        ));
      }
    }

    // Validate string patterns
    if (attrDef.pattern && typeof value === 'string') {
      const regex = new RegExp(attrDef.pattern);
      if (!regex.test(value)) {
        errors.push(validationError(
          attrName,
          `matching pattern: ${attrDef.pattern}`,
          value,
          block.blockName
        ));
      }
    }

    // Validate min/max for numbers
    if (typeof value === 'number') {
      if (attrDef.minimum !== undefined && value < attrDef.minimum) {
        warnings.push(`${block.blockName}: ${attrName} is ${value}, below minimum of ${attrDef.minimum}`);
      }
      if (attrDef.maximum !== undefined && value > attrDef.maximum) {
        warnings.push(`${block.blockName}: ${attrName} is ${value}, above maximum of ${attrDef.maximum}`);
      }
    }

    // Validate string length
    if (typeof value === 'string') {
      if (attrDef.minLength !== undefined && value.length < attrDef.minLength) {
        warnings.push(`${block.blockName}: ${attrName} length is ${value.length}, below minimum of ${attrDef.minLength}`);
      }
      if (attrDef.maxLength !== undefined && value.length > attrDef.maxLength) {
        warnings.push(`${block.blockName}: ${attrName} length is ${value.length}, above maximum of ${attrDef.maxLength}`);
      }
    }
  }

  // Check for unknown attributes (not in schema)
  for (const attrName of Object.keys(attrs)) {
    if (attrName === 'block_id' || attrName === 'className') {
      continue; // Skip special attributes
    }
    if (!schema.attributes[attrName]) {
      warnings.push(`${block.blockName}: Unknown attribute "${attrName}" not in schema (may be deprecated or custom)`);
    }
  }

  return {
    valid: errors.length === 0,
    errors,
    warnings
  };
}

/**
 * Validate blocks with their schemas
 */
export function validateBlocksWithSchemas(
  blocks: any[],
  schemas: Map<string, any>
): ValidationResult {
  const allErrors: StructuredError[] = [];
  const allWarnings: string[] = [];

  // First do basic structural validation
  const basicValidation = validateBlocks(blocks);
  allErrors.push(...basicValidation.errors);
  allWarnings.push(...basicValidation.warnings);

  // Then validate against schemas
  const validateBlockWithSchema = (block: any, path: string = ''): void => {
    const schema = schemas.get(block.blockName);

    if (schema) {
      const schemaValidation = validateBlockAgainstSchema(block, schema);

      // Add path prefix to errors
      schemaValidation.errors.forEach(error => {
        if (error.details?.field && path) {
          error.details.field = `${path}.${error.details.field}`;
        } else if (path) {
          error.details = { field: path };
        }
        allErrors.push(error);
      });

      // Add path prefix to warnings
      schemaValidation.warnings.forEach(warning => {
        allWarnings.push(path ? `${path}: ${warning}` : warning);
      });
    }

    // Recursively validate innerBlocks
    if (block.innerBlocks && Array.isArray(block.innerBlocks)) {
      block.innerBlocks.forEach((innerBlock: any, index: number) => {
        const innerPath = path ? `${path}.innerBlocks[${index}]` : `innerBlocks[${index}]`;
        validateBlockWithSchema(innerBlock, innerPath);
      });
    }
  };

  blocks.forEach((block, index) => {
    validateBlockWithSchema(block, `blocks[${index}]`);
  });

  return {
    valid: allErrors.length === 0,
    errors: allErrors,
    warnings: allWarnings
  };
}
