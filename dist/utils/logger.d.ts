/**
 * Structured logging with Winston
 */
import winston from 'winston';
export declare const logger: winston.Logger;
export declare function logToolCall(toolName: string, args: any, result: any, duration: number): void;
export declare function logWordPressCall(endpoint: string, method: string, status: number, duration: number): void;
export declare function logError(context: string, error: Error | any, additionalInfo?: any): void;
export default logger;
//# sourceMappingURL=logger.d.ts.map