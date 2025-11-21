/**
 * Error Handler Utility
 *
 * Creates structured errors with actionable fix suggestions
 */
import { StructuredError, ErrorCode, ErrorDetails } from '../types/errors.js';
/**
 * Create a structured error with fixes
 */
export declare function createError(errorCode: ErrorCode, message: string, details?: ErrorDetails, context?: any): StructuredError;
/**
 * Validation error helper
 */
export declare function validationError(field: string, expected: string, received: any, blockName?: string): StructuredError;
/**
 * Missing field error helper
 */
export declare function missingFieldError(field: string, expected?: string): StructuredError;
/**
 * Invalid block name error helper
 */
export declare function invalidBlockNameError(received: string, suggestions?: string[]): StructuredError;
/**
 * Missing block_id error helper
 */
export declare function missingBlockIdError(blockName: string): StructuredError;
/**
 * Invalid block_id format error helper
 */
export declare function invalidBlockIdError(blockId: string, blockName?: string): StructuredError;
/**
 * WordPress API error helper
 */
export declare function wordpressApiError(message: string, statusCode?: number, details?: any): StructuredError;
//# sourceMappingURL=error-handler.d.ts.map