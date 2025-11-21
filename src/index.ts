/**
 * Nexter MCP Server - Main Entry Point
 * 
 * Provides MCP tools for AI-powered WordPress content management
 * with Nexter Blocks (90+ blocks supported)
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import express, { Request, Response } from 'express';
import compression from 'compression';
import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';
import { registerTools, toolHandlers, toolDefinitions } from './tools/index.js';
import { registerResources } from './resources/index.js';
import { WordPressClient } from './services/wordpress-client.js';
import { SchemaLoader } from './services/schema-loader.js';
import { logger } from './utils/logger.js';

// Load environment variables
dotenv.config();

// Validate required environment variables
const requiredEnvVars = ['WORDPRESS_URL', 'WP_USERNAME', 'WP_APP_PASSWORD'];
for (const envVar of requiredEnvVars) {
  if (!process.env[envVar]) {
    console.error(`❌ Missing required environment variable: ${envVar}`);
    process.exit(1);
  }
}

// Initialize services
const wpClient = new WordPressClient();
const schemaLoader = new SchemaLoader('./schemas');

// Create MCP server
const mcpServer = new Server(
  {
    name: 'nexter-mcp-server',
    version: '1.0.0'
  },
  {
    capabilities: {
      tools: {},
      resources: {}
    }
  }
);

// Register tools and resources
registerTools(mcpServer, wpClient, schemaLoader);
registerResources(mcpServer, schemaLoader);

// Transport mode
const transportMode = process.env.MCP_TRANSPORT || 'http';
const port = parseInt(process.env.PORT || '3000', 10);

async function startServer() {
  if (transportMode === 'stdio') {
    // Stdio transport (for local CLI usage)
    const transport = new StdioServerTransport();
    await mcpServer.connect(transport);
    logger.info('MCP Server running on stdio transport');
    
  } else {
    // HTTP transport (for remote access)
    const app = express();
    
    // Trust only Traefik (1 proxy hop) for X-Forwarded-For headers
    // More secure than 'true' - only trusts the immediate proxy (Traefik)
    app.set('trust proxy', 1);
    
    // Middleware
    app.use(express.json({ limit: '10mb' }));
    app.use(compression());
    
    // Rate limiting
    const limiter = rateLimit({
      windowMs: 15 * 60 * 1000, // 15 minutes
      max: 100, // Limit each IP to 100 requests per windowMs
      message: 'Too many requests from this IP, please try again later',
      standardHeaders: true,
      legacyHeaders: false,
    });
    
    app.use('/mcp', limiter);
    
    // Health check endpoint
    app.get('/health', (req: Request, res: Response) => {
      res.json({
        status: 'healthy',
        version: '1.0.0',
        uptime: process.uptime(),
        wordpress: {
          url: process.env.WORDPRESS_URL,
          connected: wpClient.isConnected()
        },
        schemas: {
          loaded: schemaLoader.getLoadedCount(),
          path: './schemas'
        }
      });
    });
    
    // Test connection endpoint
    app.post('/test-connection', async (req: Request, res: Response) => {
      try {
        await wpClient.testConnection();
        res.json({ success: true, message: 'WordPress connection successful' });
      } catch (error: any) {
        res.status(500).json({ 
          success: false, 
          error: error.message 
        });
      }
    });
    
    // GET /mcp - Return 405 Method Not Allowed (MCP spec compliance)
    app.get('/mcp', (req: Request, res: Response) => {
      res.setHeader('Allow', 'POST');
      res.status(405).send('GET /mcp is not supported. This server only supports POST /mcp for MCP protocol.');
    });
    
    // POST /mcp - Main MCP endpoint (HTTP transport)
    app.post('/mcp', async (req: Request, res: Response) => {
      try {
        const request = req.body;
        logger.info('MCP request received:', request?.method);
        
        // Validate JSON-RPC structure
        if (!request || !request.jsonrpc || request.jsonrpc !== '2.0') {
          return res.status(400).json({
            jsonrpc: '2.0',
            error: { code: -32600, message: 'Invalid Request' },
            id: request?.id ?? 0
          });
        }
        
        // Route based on method
        let result: any;
        
        // Handle notifications (methods that don't expect a response)
        if (request.method && request.method.startsWith('notifications/')) {
          logger.info('Notification received:', request.method);
          // Don't send a response for notifications per JSON-RPC 2.0 spec
          return res.status(204).end();
        }
        
        if (request.method === 'initialize') {
          result = {
            protocolVersion: '2024-11-05',
            capabilities: {
              tools: {},
              resources: {}
            },
            serverInfo: {
              name: 'nexter-mcp-server',
              version: '1.0.0'
            }
          };
        } else if (request.method === 'tools/list') {
          result = { tools: toolDefinitions };
        } else if (request.method === 'resources/list') {
          // Expose schema-related resources over HTTP transport
          result = {
            resources: [
              {
                uri: 'nexter://schemas/index',
                name: 'All Nexter Block Schemas Index',
                description: 'Master index of all available Nexter blocks with categories and use cases',
                mimeType: 'application/json'
              },
              {
                uri: 'nexter://schemas/categories',
                name: 'Blocks by Category',
                description: 'All blocks organized by category (content, layout, interactive, etc.)',
                mimeType: 'application/json'
              },
              {
                uri: 'nexter://schemas/use-cases',
                name: 'Common Block Patterns',
                description: 'Pre-defined block combinations for common use cases (hero, FAQ, pricing, etc.)',
                mimeType: 'application/json'
              }
            ]
          };
        } else if (request.method === 'resources/read') {
          const uri = request.params?.uri;

          if (!uri) {
            return res.status(400).json({
              jsonrpc: '2.0',
              error: { code: -32602, message: 'Missing required param: uri' },
              id: request.id ?? 0
            });
          }

          if (uri === 'nexter://schemas/index') {
            const index = schemaLoader.getIndex();
            result = {
              contents: [{
                uri,
                mimeType: 'application/json',
                text: JSON.stringify(index, null, 2)
              }]
            };
          } else if (uri === 'nexter://schemas/categories') {
            const categories = schemaLoader.getCategories();
            result = {
              contents: [{
                uri,
                mimeType: 'application/json',
                text: JSON.stringify(categories, null, 2)
              }]
            };
          } else if (uri === 'nexter://schemas/use-cases') {
            const useCases = schemaLoader.getUseCases();
            result = {
              contents: [{
                uri,
                mimeType: 'application/json',
                text: JSON.stringify(useCases, null, 2)
              }]
            };
          } else if (uri.startsWith('nexter://schemas/block/')) {
            const blockName = uri.replace('nexter://schemas/block/', '');
            const schemas = await schemaLoader.getSchemas([blockName]);

            if (schemas.length === 0) {
              return res.status(404).json({
                jsonrpc: '2.0',
                error: { code: -32602, message: `Schema not found: ${blockName}` },
                id: request.id ?? 0
              });
            }

            result = {
              contents: [{
                uri,
                mimeType: 'application/json',
                text: JSON.stringify(schemas[0], null, 2)
              }]
            };
          } else {
            return res.status(400).json({
              jsonrpc: '2.0',
              error: { code: -32601, message: `Unknown resource URI: ${uri}` },
              id: request.id ?? 0
            });
          }
        } else if (request.method === 'resources/templates/list') {
          result = { resourceTemplates: [] };
        } else if (request.method === 'prompts/list') {
          result = { prompts: [] };
        } else if (request.method === 'tools/call') {
          // Call the actual tool handler
          const toolName = request.params?.name;
          const toolArgs = request.params?.arguments || {};
          
          const handler = (toolHandlers as any)[toolName];
          if (!handler) {
            return res.status(400).json({
              jsonrpc: '2.0',
              error: { code: -32601, message: `Tool not found: ${toolName}` },
              id: request.id ?? 0
            });
          }
          
          // Execute tool with appropriate dependencies
          let toolResult;
          if (toolName === 'get_block_schemas') {
            toolResult = await handler(toolArgs, schemaLoader);
          } else if (toolName === 'analyze_content' || toolName === 'edit_content') {
            toolResult = await handler(toolArgs, wpClient, schemaLoader);
          } else {
            toolResult = await handler(toolArgs, wpClient);
          }
          
          result = {
            content: [{
              type: 'text',
              text: JSON.stringify(toolResult, null, 2)
            }]
          };
        } else {
          return res.status(400).json({
            jsonrpc: '2.0',
            error: { code: -32601, message: `Method not found: ${request.method}` },
            id: request.id ?? 0
          });
        }
        
        res.json({
          jsonrpc: '2.0',
          result,
          id: request.id ?? 0
        });
        
        logger.info('MCP request completed:', request.method);
      } catch (error: any) {
        logger.error('MCP request error:', error);
        res.status(500).json({
          jsonrpc: '2.0',
          error: { code: -32603, message: error.message || 'Internal server error' },
          id: req.body?.id ?? 0
        });
      }
    });
    
    // Start HTTP server
    app.listen(port, () => {
      logger.info(`🚀 MCP Server listening on port ${port}`);
      logger.info(`📡 Transport mode: ${transportMode}`);
      logger.info(`🌐 WordPress URL: ${process.env.WORDPRESS_URL}`);
      logger.info(`📚 Schemas path: ./schemas`);
      logger.info(`✅ Health check: http://localhost:${port}/health`);
      logger.info(`🔌 MCP HTTP endpoint: POST http://localhost:${port}/mcp`);
    });
  }
}

// Error handling
process.on('unhandledRejection', (reason, promise) => {
  logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

process.on('uncaughtException', (error) => {
  logger.error('Uncaught Exception:', error);
  process.exit(1);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM received, shutting down gracefully');
  process.exit(0);
});

process.on('SIGINT', () => {
  logger.info('SIGINT received, shutting down gracefully');
  process.exit(0);
});

// Start the server
startServer().catch((error) => {
  logger.error('Failed to start server:', error);
  process.exit(1);
});
