/**
 * Structured Error Types for NexterAI MCP Server
 * 
 * Provides actionable error responses with fix suggestions
 */

export type ErrorType = 
  | 'validation_error'
  | 'wordpress_error'
  | 'schema_error'
  | 'network_error'
  | 'authorization_error';

export type ErrorCode =
  // Block validation errors
  | 'INVALID_BLOCK_NAME'
  | 'MISSING_BLOCK_ID'
  | 'INVALID_BLOCK_ID_FORMAT'
  | 'TYPE_MISMATCH'
  | 'MISSING_REQUIRED_ATTR'
  | 'INVALID_INNER_BLOCKS'
  
  // WordPress errors
  | 'POST_NOT_FOUND'
  | 'PERMISSION_DENIED'
  | 'WORDPRESS_API_ERROR'
  | 'INVALID_POST_TYPE'
  
  // Schema errors
  | 'SCHEMA_NOT_FOUND'
  | 'SCHEMA_VALIDATION_FAILED'
  | 'INVALID_SCHEMA_FORMAT'
  
  // Network errors
  | 'CONNECTION_FAILED'
  | 'TIMEOUT'
  | 'RATE_LIMIT_EXCEEDED';

export interface FixStep {
  action: string;
  field?: string;
  current_value?: any;
  correct_value?: any;
  code_example?: string;
}

export interface Fix {
  description: string;
  severity: 'required' | 'recommended' | 'optional';
  automated: boolean;
  steps: FixStep[];
  example?: any;
}

export interface ErrorDetails {
  field?: string;
  expected?: string;
  received?: string;
  suggestions?: string[];
  block_name?: string;
  attribute?: string;
}

export interface StructuredError {
  status: 'error';
  error_type: ErrorType;
  error_code: ErrorCode;
  message: string;
  details?: ErrorDetails;
  fixes: Fix[];
  context?: {
    operation: string;
    input: any;
    failed_at: string;
  };
  related_errors?: StructuredError[];
  retry_after?: number;
  documentation_url?: string;
}

export interface SuccessResponse<T = any> {
  status: 'success';
  data: T;
  warnings?: string[];
  message?: string;
}

export type ToolResponse<T = any> = SuccessResponse<T> | StructuredError;