/**
 * Error Handler Utility
 * 
 * Creates structured errors with actionable fix suggestions
 */

import {
  StructuredError,
  Fix,
  ErrorCode,
  ErrorType,
  ErrorDetails
} from '../types/errors.js';

/**
 * Create a structured error with fixes
 */
export function createError(
  errorCode: ErrorCode,
  message: string,
  details?: ErrorDetails,
  context?: any
): StructuredError {
  const fixes = getFixesForError(errorCode, details);
  const errorType = getErrorType(errorCode);

  return {
    status: 'error',
    error_type: errorType,
    error_code: errorCode,
    message,
    details,
    fixes,
    context,
    documentation_url: `https://github.com/nexterai/mcp-server/docs/errors#${errorCode.toLowerCase()}`
  };
}

/**
 * Get error type from error code
 */
function getErrorType(errorCode: ErrorCode): ErrorType {
  if (errorCode.startsWith('INVALID_') || errorCode.startsWith('MISSING_') || errorCode.startsWith('TYPE_')) {
    return 'validation_error';
  }
  if (errorCode.includes('WORDPRESS') || errorCode.includes('POST') || errorCode.includes('PERMISSION')) {
    return 'wordpress_error';
  }
  if (errorCode.includes('SCHEMA')) {
    return 'schema_error';
  }
  if (errorCode.includes('CONNECTION') || errorCode.includes('TIMEOUT') || errorCode.includes('RATE_LIMIT')) {
    return 'network_error';
  }
  if (errorCode.includes('AUTHORIZATION') || errorCode.includes('PERMISSION')) {
    return 'authorization_error';
  }
  return 'validation_error';
}

/**
 * Get fix suggestions for specific error codes
 */
function getFixesForError(errorCode: ErrorCode, details?: ErrorDetails): Fix[] {
  switch (errorCode) {
    case 'MISSING_BLOCK_ID':
      return [{
        description: 'Add a unique 4-character hex block_id to the block',
        severity: 'required',
        automated: true,
        steps: [{
          action: 'Add block_id to attrs',
          field: 'attrs.block_id',
          correct_value: 'GENERATE_NEW',
          code_example: `
// Generate a unique block_id
import { randomBytes } from 'crypto';
block.attrs.block_id = randomBytes(2).toString('hex');

// Example result: "a3f2"
`
        }],
        example: {
          blockName: 'tpgb/tp-heading',
          attrs: {
            block_id: 'a3f2',
            title: 'My Heading'
          }
        }
      }];

    case 'INVALID_BLOCK_ID_FORMAT':
      return [{
        description: 'Use correct block_id format: 4-character hex (a-f, 0-9)',
        severity: 'required',
        automated: true,
        steps: [{
          action: 'Fix block_id format',
          field: 'attrs.block_id',
          current_value: details?.received,
          correct_value: 'Use 4-char hex like "a3f2"',
          code_example: `
// ❌ Wrong
block.attrs.block_id = "abc";        // Too short
block.attrs.block_id = "12345";      // Too long
block.attrs.block_id = "wxyz";       // Invalid chars

// ✅ Correct
block.attrs.block_id = "a3f2";       // 4-char hex
block.attrs.block_id = "b7e1";       // Lowercase a-f, 0-9
`
        }]
      }];

    case 'INVALID_BLOCK_NAME':
      return [{
        description: 'Use correct block name format starting with "tpgb/"',
        severity: 'required',
        automated: true,
        steps: [{
          action: 'Change blockName to valid format',
          field: 'blockName',
          current_value: details?.received,
          correct_value: details?.expected || 'tpgb/*',
          code_example: `
// ❌ Wrong
{ blockName: "heading" }
{ blockName: "tp-heading" }
{ blockName: "tpgb-heading" }

// ✅ Correct
{ blockName: "tpgb/tp-heading" }
{ blockName: "tpgb/tp-button" }
{ blockName: "tpgb/tp-pro-paragraph" }
`
        }],
        example: {
          blockName: 'tpgb/tp-heading',
          attrs: {
            block_id: 'a3f2',
            title: 'My Heading'
          }
        }
      }];

    case 'TYPE_MISMATCH':
      return [{
        description: `Convert ${details?.attribute || 'attribute'} from ${details?.received} to ${details?.expected}`,
        severity: 'required',
        automated: true,
        steps: [{
          action: 'Cast to correct type',
          field: details?.field,
          current_value: details?.received,
          correct_value: details?.expected,
          code_example: getTypeCastExample(details?.received, details?.expected)
        }]
      }];

    case 'MISSING_REQUIRED_ATTR':
      return [{
        description: `Add required attribute: ${details?.field}`,
        severity: 'required',
        automated: false,
        steps: [{
          action: 'Add missing required attribute',
          field: details?.field,
          code_example: `
// Add the required attribute to your block
block.attrs.${details?.field} = ${details?.expected || '...'};
`
        }]
      }];

    case 'INVALID_INNER_BLOCKS':
      return [{
        description: 'Fix innerBlocks structure',
        severity: 'required',
        automated: false,
        steps: [{
          action: 'Ensure innerBlocks is an array of valid blocks',
          field: 'innerBlocks',
          code_example: `
// ✅ Correct innerBlocks structure
{
  blockName: "tpgb/tp-container",
  attrs: { block_id: "a3f2" },
  innerBlocks: [
    {
      blockName: "tpgb/tp-heading",
      attrs: { block_id: "b7e1", title: "Hello" }
    }
  ]
}
`
        }]
      }];

    case 'POST_NOT_FOUND':
      return [{
        description: 'The specified post ID does not exist',
        severity: 'required',
        automated: false,
        steps: [{
          action: 'Use search_content tool to find correct post ID',
          code_example: `
// Search for the post first
{
  "tool": "search_content",
  "arguments": {
    "query": "your search term",
    "post_type": "post"
  }
}
`
        }]
      }];

    case 'SCHEMA_NOT_FOUND':
      return [{
        description: 'Block schema not found - block may not be registered',
        severity: 'recommended',
        automated: false,
        steps: [{
          action: 'Check available blocks using get_block_schemas',
          code_example: `
// Get available blocks by category
{
  "tool": "get_block_schemas",
  "arguments": {
    "category": "content"
  }
}
`
        }],
        example: {
          suggested_alternative: details?.suggestions?.[0]
        }
      }];

    case 'WORDPRESS_API_ERROR':
      return [{
        description: 'WordPress REST API returned an error',
        severity: 'required',
        automated: false,
        steps: [{
          action: 'Check WordPress connection and credentials',
          code_example: `
// Verify environment variables are set:
WORDPRESS_URL=https://your-site.com
WORDPRESS_USERNAME=admin
WORDPRESS_APP_PASSWORD=xxxx xxxx xxxx xxxx
`
        }]
      }];

    default:
      return [{
        description: 'An error occurred',
        severity: 'required',
        automated: false,
        steps: [{
          action: 'Check the error details and try again'
        }]
      }];
  }
}

/**
 * Get type casting example
 */
function getTypeCastExample(receivedType?: string, expectedType?: string): string {
  if (expectedType === 'string' && receivedType === 'number') {
    return `
// ❌ Wrong - number without unit
{ fontSize: 32 }

// ✅ Correct - string with unit
{ fontSize: "32px" }
{ fontSize: "2rem" }
{ fontSize: "150%" }
`;
  }

  if (expectedType === 'number' && receivedType === 'string') {
    return `
// ❌ Wrong - string when number expected
{ count: "5" }

// ✅ Correct - actual number
{ count: 5 }
`;
  }

  if (expectedType === 'boolean' && receivedType === 'string') {
    return `
// ❌ Wrong - string representation
{ enabled: "true" }

// ✅ Correct - boolean value
{ enabled: true }
`;
  }

  if (expectedType === 'array') {
    return `
// ❌ Wrong - single value
{ items: "item1" }

// ✅ Correct - array of values
{ items: ["item1", "item2"] }
`;
  }

  return `
// Convert from ${receivedType} to ${expectedType}
// Check the schema for the correct format
`;
}

/**
 * Validation error helper
 */
export function validationError(
  field: string,
  expected: string,
  received: any,
  blockName?: string
): StructuredError {
  return createError(
    'TYPE_MISMATCH',
    `Type mismatch for ${field}: expected ${expected}, received ${typeof received}`,
    {
      field,
      expected,
      received: typeof received,
      block_name: blockName,
      attribute: field
    }
  );
}

/**
 * Missing field error helper
 */
export function missingFieldError(
  field: string,
  expected?: string
): StructuredError {
  return createError(
    'MISSING_REQUIRED_ATTR',
    `Missing required field: ${field}`,
    {
      field,
      expected,
      suggestions: [`Add ${field} to your block attributes`]
    }
  );
}

/**
 * Invalid block name error helper
 */
export function invalidBlockNameError(
  received: string,
  suggestions?: string[]
): StructuredError {
  return createError(
    'INVALID_BLOCK_NAME',
    `Invalid block name format: "${received}". Must start with "tpgb/"`,
    {
      received,
      expected: 'tpgb/*',
      suggestions: suggestions || [
        'Use format: tpgb/tp-heading',
        'Use format: tpgb/tp-button',
        'Use format: tpgb/tp-pro-paragraph'
      ]
    }
  );
}

/**
 * Missing block_id error helper
 */
export function missingBlockIdError(blockName: string): StructuredError {
  return createError(
    'MISSING_BLOCK_ID',
    `Block "${blockName}" is missing required block_id attribute`,
    {
      field: 'attrs.block_id',
      block_name: blockName,
      expected: '4-character hex ID (e.g., "a3f2")',
      suggestions: [
        'Generate with: require("crypto").randomBytes(2).toString("hex")'
      ]
    }
  );
}

/**
 * Invalid block_id format error helper
 */
export function invalidBlockIdError(
  blockId: string,
  blockName?: string
): StructuredError {
  return createError(
    'INVALID_BLOCK_ID_FORMAT',
    `Invalid block_id format: "${blockId}". Must be 4-character hex (a-f, 0-9)`,
    {
      field: 'attrs.block_id',
      block_name: blockName,
      received: blockId,
      expected: '4-character hex (e.g., "a3f2", "b7e1")',
      suggestions: [
        'Use only characters: a-f and 0-9',
        'Must be exactly 4 characters',
        'Example: "a3f2", "b7e1", "c4d9"'
      ]
    }
  );
}

/**
 * WordPress API error helper
 */
export function wordpressApiError(
  message: string,
  statusCode?: number,
  details?: any
): StructuredError {
  return createError(
    'WORDPRESS_API_ERROR',
    message,
    {
      ...details,
      received: statusCode?.toString()
    },
    {
      operation: 'WordPress API call',
      failed_at: new Date().toISOString()
    }
  );
}
