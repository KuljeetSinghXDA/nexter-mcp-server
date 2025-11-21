/**
 * Input Validator Utility
 *
 * Validates block structure and attributes before sending to WordPress
 */
import { StructuredError } from '../types/errors.js';
export interface ValidationResult {
    valid: boolean;
    errors: StructuredError[];
    warnings: string[];
}
/**
 * Validate block structure
 */
export declare function validateBlock(block: any): ValidationResult;
/**
 * Validate array of blocks
 */
export declare function validateBlocks(blocks: any[]): ValidationResult;
/**
 * Validate attribute types against expected types
 */
export declare function validateAttributeType(value: any, expectedType: string, attributeName: string, blockName: string): StructuredError | null;
/**
 * Auto-fix common issues
 */
export declare function autoFixBlock(block: any): {
    fixed: any;
    changes: string[];
};
/**
 * Validate and auto-fix blocks
 */
export declare function validateAndFix(blocks: any[]): {
    valid: boolean;
    blocks: any[];
    errors: StructuredError[];
    warnings: string[];
    fixes_applied: string[];
};
//# sourceMappingURL=input-validator.d.ts.map