/**
 * MCP Resources Registration
 *
 * Exposes block schemas as MCP resources for AI context
 */
import { ListResourcesRequestSchema, ReadResourceRequestSchema } from '@modelcontextprotocol/sdk/types.js';
import { logger } from '../utils/logger.js';
export function registerResources(server, schemaLoader) {
    // List all available resources
    server.setRequestHandler(ListResourcesRequestSchema, async () => {
        return {
            resources: [
                {
                    uri: 'nexter://schemas/index',
                    name: 'All Nexter Block Schemas Index',
                    description: 'Master index of all 90+ available Nexter blocks with categories and use cases',
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
    });
    // Handle resource reads
    server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
        const uri = request.params.uri;
        try {
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
            // Handle specific block schema requests
            if (uri.startsWith('nexter://schemas/block/')) {
                const blockName = uri.replace('nexter://schemas/block/', '');
                const schemas = await schemaLoader.getSchemas([blockName]);
                if (schemas.length === 0) {
                    throw new Error(`Schema not found: ${blockName}`);
                }
                return {
                    contents: [{
                            uri,
                            mimeType: 'application/json',
                            text: JSON.stringify(schemas[0], null, 2)
                        }]
                };
            }
            throw new Error(`Unknown resource URI: ${uri}`);
        }
        catch (error) {
            logger.error(`Resource read error for ${uri}:`, error);
            throw error;
        }
    });
}
//# sourceMappingURL=index.js.map