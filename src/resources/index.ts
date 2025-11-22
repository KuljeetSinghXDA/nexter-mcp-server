/**
 * MCP Resources Registration
 *
 * Exposes block schemas as MCP resources for AI context
 * Supports progressive loading and common definitions
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { ListResourcesRequestSchema, ReadResourceRequestSchema } from '@modelcontextprotocol/sdk/types.js';
import { SchemaLoader } from '../services/schema-loader.js';
import { logger } from '../utils/logger.js';

export function registerResources(server: Server, schemaLoader: SchemaLoader) {

  // List all available resources
  server.setRequestHandler(ListResourcesRequestSchema, async () => {
    return {
      resources: [
        {
          uri: 'nexter://schemas/catalog',
          name: 'Block Catalog (New)',
          description: '84 blocks organized by category, complexity, keywords, and use cases',
          mimeType: 'application/json'
        },
        {
          uri: 'nexter://schemas/index',
          name: 'All Nexter Block Schemas Index (Legacy)',
          description: 'Master index of all available Nexter blocks - use catalog instead',
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
        },
        {
          uri: 'nexter://schemas/definitions',
          name: 'Common Definitions',
          description: 'Shared object definitions (typography, background, border, shadow, etc.)',
          mimeType: 'application/json'
        }
      ]
    };
  });

  // Handle resource reads
  server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
    const uri = request.params.uri;

    try {
      // New catalog resource
      if (uri === 'nexter://schemas/catalog') {
        const catalog = schemaLoader.getCatalog();
        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(catalog, null, 2)
          }]
        };
      }

      // Legacy index (backward compatible)
      if (uri === 'nexter://schemas/index') {
        const index = schemaLoader.getIndex();
        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(index, null, 2)
          }]
        };
      }

      // Categories
      if (uri === 'nexter://schemas/categories') {
        const categories = schemaLoader.getCategories();
        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(categories, null, 2)
          }]
        };
      }

      // Use cases
      if (uri === 'nexter://schemas/use-cases') {
        const useCases = schemaLoader.getUseCases();
        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(useCases, null, 2)
          }]
        };
      }

      // Common definitions
      if (uri === 'nexter://schemas/definitions') {
        const definitions = schemaLoader.getDefinitions();
        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(definitions, null, 2)
          }]
        };
      }

      // Specific definition (e.g., nexter://schemas/definition/typography)
      if (uri.startsWith('nexter://schemas/definition/')) {
        const defName = uri.replace('nexter://schemas/definition/', '');
        const definition = schemaLoader.getDefinition(defName);

        if (!definition) {
          throw new Error(`Definition not found: ${defName}`);
        }

        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(definition, null, 2)
          }]
        };
      }

      // Progressive block schema loading
      // Supports: nexter://schemas/block/{blockName}?levels=meta,core,styling
      if (uri.startsWith('nexter://schemas/block/')) {
        const [baseUri, queryString] = uri.split('?');
        const blockName = baseUri.replace('nexter://schemas/block/', '');

        // Parse query parameters for levels
        let levels: any[] = ['full'];
        let resolve$refs = true;

        if (queryString) {
          const params = new URLSearchParams(queryString);
          if (params.has('levels')) {
            levels = params.get('levels')!.split(',') as any[];
          }
          if (params.has('resolve')) {
            resolve$refs = params.get('resolve') === 'true';
          }
        }

        const schema = await schemaLoader.getBlockSchema(blockName, levels, resolve$refs);

        if (!schema) {
          throw new Error(`Schema not found: ${blockName}`);
        }

        return {
          contents: [{
            uri,
            mimeType: 'application/json',
            text: JSON.stringify(schema, null, 2)
          }]
        };
      }

      throw new Error(`Unknown resource URI: ${uri}`);
    } catch (error: any) {
      logger.error(`Resource read error for ${uri}:`, error);
      throw error;
    }
  });
}