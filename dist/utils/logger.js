/**
 * Structured logging with Winston
 */
import winston from 'winston';
import path from 'path';
import fs from 'fs';
// Ensure logs directory exists
const logsDir = './logs';
if (!fs.existsSync(logsDir)) {
    fs.mkdirSync(logsDir, { recursive: true });
}
const logLevel = process.env.LOG_LEVEL || 'info';
export const logger = winston.createLogger({
    level: logLevel,
    format: winston.format.combine(winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }), winston.format.errors({ stack: true }), winston.format.splat(), winston.format.json()),
    defaultMeta: { service: 'nexter-mcp-server' },
    transports: [
        // Error log
        new winston.transports.File({
            filename: path.join(logsDir, 'error.log'),
            level: 'error',
            maxsize: 5242880, // 5MB
            maxFiles: 5
        }),
        // Combined log
        new winston.transports.File({
            filename: path.join(logsDir, 'combined.log'),
            maxsize: 5242880, // 5MB
            maxFiles: 5
        })
    ]
});
// Console output for non-production
if (process.env.NODE_ENV !== 'production') {
    logger.add(new winston.transports.Console({
        format: winston.format.combine(winston.format.colorize(), winston.format.printf(({ level, message, timestamp, ...meta }) => {
            let msg = `${timestamp} [${level}]: ${message}`;
            if (Object.keys(meta).length > 0 && meta.service) {
                delete meta.service;
                if (Object.keys(meta).length > 0) {
                    msg += ` ${JSON.stringify(meta)}`;
                }
            }
            return msg;
        }))
    }));
}
// Helper functions for common logging patterns
export function logToolCall(toolName, args, result, duration) {
    logger.info('MCP Tool Call', {
        tool: toolName,
        args: sanitizeArgs(args),
        success: result.success,
        duration: `${duration}ms`
    });
}
export function logWordPressCall(endpoint, method, status, duration) {
    logger.info('WordPress API Call', {
        endpoint,
        method,
        status,
        duration: `${duration}ms`
    });
}
export function logError(context, error, additionalInfo) {
    logger.error(context, {
        error: error.message,
        stack: error.stack,
        ...additionalInfo
    });
}
// Remove sensitive data from logs
function sanitizeArgs(args, seen = new WeakSet()) {
    if (!args || typeof args !== 'object')
        return args;
    // Prevent circular references from causing stack overflow
    if (seen.has(args)) {
        return '[Circular Reference]';
    }
    seen.add(args);
    const sanitized = { ...args };
    const sensitiveKeys = ['password', 'token', 'secret', 'apiKey', 'api_key', 'WP_APP_PASSWORD'];
    for (const key of sensitiveKeys) {
        if (sanitized[key]) {
            sanitized[key] = '***REDACTED***';
        }
    }
    // Recursive sanitization for nested objects
    for (const [key, value] of Object.entries(sanitized)) {
        if (value && typeof value === 'object') {
            sanitized[key] = sanitizeArgs(value, seen);
        }
    }
    return sanitized;
}
export default logger;
//# sourceMappingURL=logger.js.map