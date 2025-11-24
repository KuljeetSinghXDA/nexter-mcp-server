/**
 * MCP Tools Registration
 *
 * Registers all tools available to AI agents
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { ListToolsRequestSchema, CallToolRequestSchema } from '@modelcontextprotocol/sdk/types.js';
import { z } from 'zod';
import { WordPressClient } from '../services/wordpress-client.js';
import { SchemaLoader } from '../services/schema-loader.js';
import { logger, logToolCall } from '../utils/logger.js';
import { preprocessBlocks } from '../utils/block-formatter.js';
import { validateBlocks, validateAndFix, validateBlocksWithSchemas } from '../utils/input-validator.js';

export function registerTools(
  server: Server,
  wpClient: WordPressClient,
  schemaLoader: SchemaLoader
) {
  
  // List all available tools
  server.setRequestHandler(ListToolsRequestSchema, async () => {
    return {
      tools: [
        {
          name: 'get_block_schemas',
          description: `Get Nexter block schemas for specific blocks, use cases, or categories. Use this to understand block structure before creating/editing content. Returns ONLY requested schemas to minimize context usage.

WHEN TO USE:
- Before creating content - understand available blocks
- When errors occur - verify attribute names and types
- For complex layouts - see examples of nested blocks
- To discover features - explore block capabilities

EXAMPLE 1 - Get specific blocks:
{
  "block_names": ["tpgb/tp-heading", "tpgb/tp-pro-paragraph"]
}

EXAMPLE 2 - Get blocks for a use case:
{
  "use_case": "hero-section"
}
Returns: heading, paragraph, button, container blocks

EXAMPLE 3 - Get blocks by category:
{
  "category": "content"
}
Returns: all content-related blocks`,
          inputSchema: {
            type: 'object',
            properties: {
              block_names: {
                type: 'array',
                items: {
                  type: 'string',
                  pattern: '^tpgb/[a-z0-9-]+$'
                },
                description: 'Specific block names to retrieve (e.g., ["tpgb/tp-accordion", "tpgb/tp-heading"])'
              },
              use_case: {
                type: 'string',
                enum: ['hero-section', 'testimonial-section', 'faq-section', 'pricing-section', 'cta-section', 'team-section', 'blog-post', 'landing-page'],
                description: 'Common content pattern to retrieve related blocks for'
              },
              category: {
                type: 'string',
                enum: ['content', 'layout', 'interactive', 'media', 'marketing', 'social', 'forms', 'navigation', 'advanced'],
                description: 'Block category to retrieve all blocks from'
              },
              levels: {
                type: 'array',
                items: {
                  type: 'string',
                  enum: ['meta', 'core', 'styling', 'examples', 'full']
                },
                default: ['full'],
                description: 'Schema levels to load. Default: ["full"]. Use ["core"] for basic attributes (2KB), ["core", "examples"] for patterns (3KB), ["full"] for everything (9KB). Progressive loading saves 75% context!'
              }
            }
          }
        },
        {
          name: 'create_content',
          description: `Create new WordPress post or page with Nexter blocks. Content is ALWAYS created as DRAFT for review. Returns post ID and preview URL.

EXAMPLE 1 - Simple heading and paragraph:
{
  "post_type": "post",
  "title": "My First Post",
  "blocks": [
    {
      "blockName": "tpgb/tp-heading",
      "attrs": {
        "block_id": "a3f2",
        "title": "Welcome",
        "tTag": "h1"
      }
    },
    {
      "blockName": "tpgb/tp-pro-paragraph",
      "attrs": {
        "block_id": "b7e1",
        "content": "This is the introduction."
      }
    }
  ]
}

EXAMPLE 2 - Nested accordion (FAQ):
{
  "post_type": "page",
  "title": "FAQ Page",
  "blocks": [
    {
      "blockName": "tpgb/tp-accordion",
      "attrs": { "block_id": "c4d9" },
      "innerBlocks": [
        {
          "blockName": "tpgb/tp-accordion-inner",
          "attrs": {
            "block_id": "d5e2",
            "accordionTitle": "What is this?",
            "description": "This is an answer."
          }
        }
      ]
    }
  ]
}

COMMON ERRORS:
❌ Missing block_id → Add 4-char hex ID to all blocks
❌ Wrong blockName format → Use "tpgb/..." format
❌ Content not appearing → Check attrs.content field
❌ Invalid attribute types → Check schema for correct types`,
          inputSchema: {
            type: 'object',
            properties: {
              post_type: {
                type: 'string',
                enum: ['post', 'page'],
                description: 'Type of content to create'
              },
              title: {
                type: 'string',
                minLength: 1,
                maxLength: 200,
                description: 'Post or page title (1-200 characters)'
              },
              blocks: {
                type: 'array',
                minItems: 1,
                description: 'Array of Gutenberg blocks. Each block must have blockName and attrs with block_id',
                items: {
                  type: 'object',
                  required: ['blockName', 'attrs'],
                  properties: {
                    blockName: {
                      type: 'string',
                      pattern: '^tpgb/[a-z0-9-]+$',
                      description: 'Block type (e.g., "tpgb/tp-heading")'
                    },
                    attrs: {
                      type: 'object',
                      required: ['block_id'],
                      properties: {
                        block_id: {
                          type: 'string',
                          pattern: '^[a-f0-9]{4}$',
                          description: '4-character hex ID (e.g., "a3f2")'
                        }
                      }
                    }
                  }
                }
              },
              excerpt: {
                type: 'string',
                maxLength: 500,
                description: 'Optional post excerpt (max 500 chars)'
              },
              categories: {
                type: 'array',
                items: { type: 'integer' },
                description: 'Category IDs (posts only)'
              },
              tags: {
                type: 'array',
                items: { type: 'integer' },
                description: 'Tag IDs (posts only)'
              }
            },
            required: ['post_type', 'title', 'blocks']
          }
        },
        {
          name: 'analyze_content',
          description: 'Analyze post/page to identify which Nexter blocks it uses and their configuration. ALWAYS use this BEFORE editing to understand existing structure.',
          inputSchema: {
            type: 'object',
            properties: {
              post_id: {
                type: 'integer',
                description: 'Post or page ID to analyze'
              },
              include_schemas: {
                type: 'boolean',
                default: true,
                description: 'Include block schemas for found blocks in response'
              }
            },
            required: ['post_id']
          }
        },
        {
          name: 'search_content',
          description: 'Search for posts or pages by query string. Returns matching content with IDs and basic info.',
          inputSchema: {
            type: 'object',
            properties: {
              query: {
                type: 'string',
                description: 'Search query string'
              },
              post_type: {
                type: 'string',
                enum: ['post', 'page', 'any'],
                default: 'post',
                description: 'Type of content to search'
              },
              limit: {
                type: 'integer',
                default: 10,
                minimum: 1,
                maximum: 50,
                description: 'Maximum number of results'
              }
            },
            required: ['query']
          }
        },
        {
          name: 'edit_content',
          description: 'Edit existing post/page with targeted operations. Supports attribute-level changes and block addition/removal. Creates WordPress revision automatically. ALWAYS load current content first.',
          inputSchema: {
            type: 'object',
            properties: {
              post_id: {
                type: 'integer',
                description: 'ID of post/page to edit'
              },
              operations: {
                type: 'array',
                description: 'Array of edit operations to perform',
                items: {
                  type: 'object',
                  properties: {
                    operation: {
                      type: 'string',
                      enum: ['modify_block', 'add_block', 'remove_block'],
                      description: 'Type of edit operation'
                    },
                    block_id: {
                      type: 'string',
                      description: 'Nexter block_id to target (for modify/remove)'
                    },
                    target_position: {
                      type: 'integer',
                      description: 'Position index for add_block (0-based, or use -1 for end)'
                    },
                    new_attrs: {
                      type: 'object',
                      description: 'New attribute values for modify_block'
                    },
                    new_block: {
                      type: 'object',
                      description: 'Complete block definition for add_block'
                    }
                  },
                  required: ['operation']
                }
              },
              title_update: {
                type: 'string',
                description: 'Optional: Update post title'
              }
            },
            required: ['post_id', 'operations']
          }
        },
        {
          name: 'browse_block_catalog',
          description: `Browse all 83 available Nexter blocks organized by category, complexity, or use case. Use this FIRST to discover blocks before requesting schemas.

WHEN TO USE:
- At the start of any task - discover what blocks exist
- To find blocks for a specific purpose ("I need a pricing block")
- To understand block relationships (parent-child blocks)
- To see block complexity before using

EXAMPLE 1 - Browse all blocks:
{ "view": "all" }
Returns: Complete catalog with 83 blocks grouped by 8 categories

EXAMPLE 2 - Filter by category:
{ "view": "category", "category": "content" }
Returns: All content blocks (heading, paragraph, typography, etc.)

EXAMPLE 3 - Filter by complexity:
{ "view": "complexity", "complexity": "simple" }
Returns: All simple blocks suitable for basic layouts

EXAMPLE 4 - Find child blocks:
{ "view": "children" }
Returns: All child blocks (accordion-inner, form fields, etc.) that require parent blocks

RETURNS:
- Catalog structure with block metadata
- Parent-child relationships clearly marked
- Complexity levels for each block
- Keywords and use cases for discovery`,
          inputSchema: {
            type: 'object',
            properties: {
              view: {
                type: 'string',
                enum: ['all', 'category', 'complexity', 'children', 'summary'],
                default: 'all',
                description: 'View type: "all" (full catalog), "category" (filter by category), "complexity" (filter by difficulty), "children" (only child blocks), "summary" (quick overview)'
              },
              category: {
                type: 'string',
                enum: ['content', 'layout', 'interactive', 'media', 'marketing', 'forms', 'navigation', 'effects'],
                description: 'Category filter (required if view=category)'
              },
              complexity: {
                type: 'string',
                enum: ['simple', 'medium', 'advanced', 'expert'],
                description: 'Complexity filter (required if view=complexity)'
              }
            }
          }
        },
        {
          name: 'validate_content',
          description: `Validate block structure before saving. Checks against Nexter schemas and WordPress requirements. Returns detailed errors with fix suggestions.

USE THIS TOOL:
- Before calling create_content or edit_content
- When receiving validation errors
- To understand what's wrong with block structure
- To get code examples for fixing issues

EXAMPLE 1 - Basic validation:
{
  "blocks": [
    {
      "blockName": "tpgb/tp-heading",
      "attrs": { "block_id": "a3f2", "title": "Test" }
    }
  ]
}
Returns: { valid: true, errors: [], warnings: [] }

EXAMPLE 2 - With auto-fix:
{
  "blocks": [
    {
      "blockName": "heading",
      "attrs": { "title": "Test" }
    }
  ],
  "auto_fix": true
}
Returns: Fixed blocks with proper format

RETURNS:
- valid: boolean - passes all checks
- errors: array - required fixes
- warnings: array - optional improvements
- fixes_applied: array - auto-corrections made`,
          inputSchema: {
            type: 'object',
            properties: {
              blocks: {
                type: 'array',
                minItems: 1,
                description: 'Block tree to validate'
              },
              strict: {
                type: 'boolean',
                default: false,
                description: 'Strict mode fails on warnings too'
              },
              auto_fix: {
                type: 'boolean',
                default: false,
                description: 'Automatically fix common issues (missing block_id, type conversions)'
              }
            },
            required: ['blocks']
          }
        }
      ]
    };
  });

  // Handle tool calls
  server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const name = request.params.name;
    const args = request.params.arguments || {};
    const startTime = Date.now();

    try {
      let result;

      switch (name) {
        case 'get_block_schemas':
          result = await handleGetBlockSchemas(args, schemaLoader);
          break;

        case 'create_content':
          result = await handleCreateContent(args, wpClient, schemaLoader);
          break;

        case 'analyze_content':
          result = await handleAnalyzeContent(args, wpClient, schemaLoader);
          break;

        case 'search_content':
          result = await handleSearchContent(args, wpClient);
          break;

        case 'edit_content':
          result = await handleEditContent(args, wpClient, schemaLoader);
          break;

        case 'browse_block_catalog':
          result = await handleBrowseCatalog(args, schemaLoader);
          break;

        case 'validate_content':
          result = await handleValidateContent(args, wpClient);
          break;

        default:
          result = { success: false, error: `Unknown tool: ${name}` };
      }

      const duration = Date.now() - startTime;
      logToolCall(name, args, result, duration);

      return {
        content: [{
          type: 'text',
          text: JSON.stringify(result, null, 2)
        }]
      };
    } catch (error: any) {
      const duration = Date.now() - startTime;
      const errorResult = {
        success: false,
        error: error.message,
        details: error.response?.data
      };

      logToolCall(name, args, errorResult, duration);

      return {
        content: [{
          type: 'text',
          text: JSON.stringify(errorResult, null, 2)
        }],
        isError: true
      };
    }
  });
}

// Export tool handlers for HTTP transport
export const toolHandlers = {
  get_block_schemas: handleGetBlockSchemas,
  create_content: handleCreateContent,
  analyze_content: handleAnalyzeContent,
  search_content: handleSearchContent,
  edit_content: handleEditContent,
  browse_block_catalog: handleBrowseCatalog,
  validate_content: handleValidateContent
};

// Export tool definitions for HTTP transport
export const toolDefinitions = [
  {
    name: 'get_block_schemas',
    description: 'Get Nexter block schemas for specific blocks, use cases, or categories. Use this to understand block structure before creating/editing content. Returns ONLY requested schemas to minimize context usage.',
    inputSchema: {
      type: 'object',
      properties: {
        block_names: {
          type: 'array',
          items: { type: 'string' },
          description: 'Specific block names to retrieve (e.g., ["tpgb/tp-accordion", "tpgb/tp-heading"])'
        },
        use_case: {
          type: 'string',
          enum: ['hero-section', 'testimonial-section', 'faq-section', 'pricing-section', 'cta-section', 'team-section', 'blog-post', 'landing-page'],
          description: 'Common content pattern to retrieve related blocks for'
        },
        category: {
          type: 'string',
          enum: ['content', 'layout', 'interactive', 'media', 'marketing', 'social', 'forms', 'navigation', 'advanced'],
          description: 'Block category to retrieve all blocks from'
        },
        levels: {
          type: 'array',
          items: {
            type: 'string',
            enum: ['meta', 'core', 'styling', 'examples', 'full']
          },
          default: ['full'],
          description: 'Schema levels to load (default: full). Progressive loading saves context.'
        }
      }
    }
  },
  {
    name: 'create_content',
    description: 'Create new WordPress post or page with Nexter blocks. Content is ALWAYS created as DRAFT for review. Returns post ID and preview URL.',
    inputSchema: {
      type: 'object',
      properties: {
        post_type: { type: 'string', enum: ['post', 'page'] },
        title: { type: 'string' },
        blocks: { type: 'array' },
        excerpt: { type: 'string' },
        categories: { type: 'array', items: { type: 'integer' } },
        tags: { type: 'array', items: { type: 'integer' } }
      },
      required: ['post_type', 'title', 'blocks']
    }
  },
  {
    name: 'analyze_content',
    description: 'Analyze post/page to identify which Nexter blocks it uses. ALWAYS use BEFORE editing.',
    inputSchema: {
      type: 'object',
      properties: {
        post_id: { type: 'integer' },
        include_schemas: { type: 'boolean', default: true }
      },
      required: ['post_id']
    }
  },
  {
    name: 'search_content',
    description: 'Search for posts or pages by query string.',
    inputSchema: {
      type: 'object',
      properties: {
        query: { type: 'string' },
        post_type: { type: 'string', enum: ['post', 'page', 'any'], default: 'post' },
        limit: { type: 'integer', default: 10 }
      },
      required: ['query']
    }
  },
  {
    name: 'edit_content',
    description: 'Edit existing post/page with targeted operations. Creates WordPress revision automatically.',
    inputSchema: {
      type: 'object',
      properties: {
        post_id: { type: 'integer' },
        operations: { type: 'array' },
        title_update: { type: 'string' }
      },
      required: ['post_id', 'operations']
    }
  },
  {
    name: 'browse_block_catalog',
    description: 'Browse all available Nexter blocks by category, complexity, or relationships.',
    inputSchema: {
      type: 'object',
      properties: {
        view: {
          type: 'string',
          enum: ['all', 'category', 'complexity', 'children', 'summary'],
          default: 'all'
        },
        category: { type: 'string' },
        complexity: { type: 'string' }
      }
    }
  },
  {
    name: 'validate_content',
    description: 'Validate block structure before saving.',
    inputSchema: {
      type: 'object',
      properties: {
        blocks: { type: 'array' },
        strict: { type: 'boolean', default: false }
      },
      required: ['blocks']
    }
  }
];

// Tool implementations

async function handleGetBlockSchemas(args: any, schemaLoader: SchemaLoader) {
  const levels = args.levels || ['full'];
  let schemas;

  if (args.block_names) {
    // Progressive loading support
    schemas = [];
    for (const blockName of args.block_names) {
      const schema = await schemaLoader.getBlockSchema(blockName, levels as any, true);
      if (schema) {
        schemas.push(schema);
      }
    }
  } else if (args.use_case) {
    schemas = await schemaLoader.getSchemasForUseCase(args.use_case);
  } else if (args.category) {
    schemas = await schemaLoader.getSchemasByCategory(args.category);
  } else {
    return {
      success: false,
      error: 'Must specify block_names, use_case, or category'
    };
  }

  return {
    success: true,
    schemas: schemas,
    count: schemas.length,
    levels_loaded: levels,
    context_saved: levels.includes('full') ? '0%' : levels.includes('core') ? '~75%' : '~50%',
    note: `Loaded ${schemas.length} block schema(s) at level(s): ${levels.join(', ')}. ${levels.includes('full') ? 'Full schemas loaded.' : 'Progressive loading active - use ["full"] if you need complete reference.'}`
  };
}

async function handleCreateContent(args: any, wpClient: WordPressClient, schemaLoader: SchemaLoader) {
  // Validate input schema
  const schema = z.object({
    post_type: z.enum(['post', 'page']),
    title: z.string().min(1).max(200),
    blocks: z.array(z.any()).min(1),
    excerpt: z.string().max(500).optional(),
    categories: z.array(z.number()).optional(),
    tags: z.array(z.number()).optional()
  });

  const validated = schema.safeParse(args);
  if (!validated.success) {
    return {
      status: 'error',
      error_type: 'validation_error',
      error_code: 'INVALID_INPUT',
      message: 'Input validation failed',
      details: validated.error.errors
    };
  }

  // Load schemas for all blocks (for schema-based validation)
  const blockNames = [
    ...new Set(
      getAllBlockNames(validated.data.blocks)
    )
  ];
  const loadedSchemas = await schemaLoader.getSchemas(blockNames);
  const schemaMap = new Map(loadedSchemas.map(s => [s.blockName, s]));

  // Validate block structure with schema-based validator
  const blockValidation = validateBlocksWithSchemas(validated.data.blocks, schemaMap);
  if (!blockValidation.valid) {
    return {
      status: 'error',
      error_type: 'validation_error',
      error_code: 'INVALID_BLOCK_STRUCTURE',
      message: 'Block validation failed against schemas. See errors for fix suggestions.',
      errors: blockValidation.errors,
      warnings: blockValidation.warnings,
      suggestion: 'Use validate_content tool with auto_fix: true to automatically fix common issues',
      validated_against_schemas: true
    };
  }

  // Pre-process blocks to add required Gutenberg fields
  const preprocessed = preprocessBlocks(validated.data.blocks);
  
  if (preprocessed.warnings.length > 0) {
    logger.warn('Block preprocessing warnings:', preprocessed.warnings);
  }

  // Validate blocks with WordPress
  const wpValidation = await wpClient.validateBlocks(preprocessed.formatted);
  if (!wpValidation.valid) {
    return {
      status: 'error',
      error_type: 'wordpress_error',
      error_code: 'WORDPRESS_VALIDATION_FAILED',
      message: 'WordPress validation failed',
      details: wpValidation.errors,
      warnings: [...blockValidation.warnings, ...preprocessed.warnings, ...(wpValidation.warnings || [])]
    };
  }

  // Create content (always as draft)
  try {
    const result = await wpClient.createContent({
      ...validated.data,
      blocks: preprocessed.formatted,
      status: 'draft'
    });

    return {
      status: 'success',
      post_id: result.data.post_id,
      title: validated.data.title,
      post_status: 'draft',
      preview_url: result.data.preview_url,
      edit_url: result.data.edit_url,
      warnings: [...blockValidation.warnings, ...preprocessed.warnings, ...(wpValidation.warnings || [])],
      message: 'Content created as DRAFT. Review in WordPress before publishing.'
    };
  } catch (error: any) {
    return {
      status: 'error',
      error_type: 'wordpress_error',
      error_code: 'CONTENT_CREATION_FAILED',
      message: error.message || 'Failed to create content',
      details: error.response?.data
    };
  }
}

async function handleAnalyzeContent(
  args: any,
  wpClient: WordPressClient,
  schemaLoader: SchemaLoader
) {
  const postId = args.post_id;
  const includeSchemas = args.include_schemas ?? true;

  // Get content
  const content = await wpClient.getContent(postId);

  // Identify Nexter blocks
  const nexterBlocks = content.data.blocks.filter((b: any) =>
    b.blockName?.startsWith('tpgb/')
  );

  const blockTypes = [...new Set(nexterBlocks.map((b: any) => b.blockName))];

  // Load schemas if requested
  let schemas: any[] = [];
  if (includeSchemas && blockTypes.length > 0) {
    schemas = await schemaLoader.getSchemas(blockTypes as string[]);
  }

  return {
    success: true,
    post_info: {
      id: content.data.post_id,
      title: content.data.title,
      status: content.data.status,
      type: content.data.type
    },
    block_summary: {
      total: content.data.blocks.length,
      nexter_blocks: nexterBlocks.length,
      types: blockTypes
    },
    blocks: nexterBlocks.map((b: any) => ({
      blockName: b.blockName,
      blockId: b.attrs?.block_id,
      position: content.data.blocks.indexOf(b),
      hasInnerBlocks: b.innerBlocks && b.innerBlocks.length > 0
    })),
    schemas: includeSchemas ? schemas : undefined
  };
}

async function handleSearchContent(args: any, wpClient: WordPressClient) {
  const query = args.query;
  const postType = args.post_type || 'post';
  const limit = args.limit || 10;

  const result = await wpClient.searchContent(query, postType, limit);

  return {
    success: true,
    found: result.data.found,
    results: result.data.posts,
    query,
    note: result.data.found === 0
      ? 'No posts found. Try different search terms.'
      : result.data.found === 1
      ? 'Single match found. You can proceed directly with this post_id.'
      : result.data.found <= 5
      ? 'Multiple matches found. Select one by post_id.'
      : 'Many matches found. Consider refining your search.'
  };
}

async function handleEditContent(
  args: any,
  wpClient: WordPressClient,
  _schemaLoader: SchemaLoader
) {
  const postId = args.post_id;
  const operations = args.operations;
  const titleUpdate = args.title_update;

  // Load current content
  const current = await wpClient.getContent(postId);
  let blocks = current.data.blocks;

  const changeLog: string[] = [];

  // Apply each operation
  for (const op of operations) {
    switch (op.operation) {
      case 'modify_block': {
        blocks = modifyBlockAttributes(blocks, op.block_id, op.new_attrs);
        changeLog.push(`Modified block ${op.block_id}`);
        break;
      }

      case 'add_block': {
        const position = op.target_position ?? blocks.length;
        blocks = insertBlock(blocks, position, op.new_block);
        changeLog.push(`Added ${op.new_block.blockName} at position ${position}`);
        break;
      }

      case 'remove_block': {
        blocks = removeBlock(blocks, op.block_id);
        changeLog.push(`Removed block ${op.block_id}`);
        break;
      }

      default:
        return {
          success: false,
          error: `Unknown operation: ${op.operation}`
        };
    }
  }

  // Validate modified block structure locally before preprocessing
  const blockValidation = validateBlocks(blocks);
  if (!blockValidation.valid) {
    return {
      success: false,
      error: 'Modified content failed local block validation',
      errors: blockValidation.errors,
      warnings: blockValidation.warnings,
      suggestion: 'Use validate_content tool with auto_fix: true to automatically fix common issues before editing'
    };
  }

  // Pre-process blocks to ensure Gutenberg/Nexter fidelity
  const preprocessed = preprocessBlocks(blocks);

  // Validate modified structure with WordPress using formatted blocks
  const validation = await wpClient.validateBlocks(preprocessed.formatted);
  if (!validation.valid) {
    return {
      success: false,
      error: 'Modified content failed validation',
      details: validation.errors,
      original_preserved: true
    };
  }

  // Update WordPress
  const result = await wpClient.updateContent(postId, {
    blocks: preprocessed.formatted,
    title: titleUpdate,
    create_revision: true
  });

  return {
    success: true,
    post_id: postId,
    changes_applied: changeLog,
    revision_id: result.data.revision_id,
    preview_url: result.data.preview_url,
    warnings: [
      ...blockValidation.warnings,
      ...preprocessed.warnings,
      ...(validation.warnings || [])
    ],
    message: 'Changes saved with new revision. Original preserved.'
  };
}

async function handleBrowseCatalog(args: any, schemaLoader: SchemaLoader) {
  const view = args.view || 'all';
  const catalog = schemaLoader.getCatalog();

  if (!catalog) {
    return {
      success: false,
      error: 'Block catalog not loaded'
    };
  }

  // Summary view - quick overview
  if (view === 'summary') {
    const categoryNames = Object.keys(catalog.categories || {});
    const summary: any = {
      totalBlocks: catalog.totalBlocks || 0,
      categories: {}
    };

    for (const cat of categoryNames) {
      const catData = catalog.categories[cat];
      summary.categories[cat] = {
        count: catData.blocks?.length || 0,
        description: catData.description
      };
    }

    return {
      success: true,
      view: 'summary',
      summary,
      note: 'Use view="category" to see blocks in a specific category, or view="all" for complete catalog'
    };
  }

  // Children view - only child blocks
  if (view === 'children') {
    const allBlocks: any[] = [];
    for (const catName of Object.keys(catalog.categories || {})) {
      allBlocks.push(...catalog.categories[catName].blocks);
    }

    const childBlocks = allBlocks.filter((b: any) => b.isChild === true);

    return {
      success: true,
      view: 'children',
      totalChildBlocks: childBlocks.length,
      blocks: childBlocks,
      note: 'These blocks REQUIRE a parent block. Check the "parent" field for required parent block name.',
      warning: 'Child blocks cannot be used standalone - they must be innerBlocks of their parent'
    };
  }

  // Category view - filter by specific category
  if (view === 'category') {
    if (!args.category) {
      return {
        success: false,
        error: 'category parameter required when view=category',
        availableCategories: Object.keys(catalog.categories || {})
      };
    }

    const categoryData = catalog.categories[args.category];
    if (!categoryData) {
      return {
        success: false,
        error: `Unknown category: ${args.category}`,
        availableCategories: Object.keys(catalog.categories || {})
      };
    }

    return {
      success: true,
      view: 'category',
      category: args.category,
      description: categoryData.description,
      count: categoryData.blocks.length,
      blocks: categoryData.blocks
    };
  }

  // Complexity view - filter by complexity level
  if (view === 'complexity') {
    if (!args.complexity) {
      return {
        success: false,
        error: 'complexity parameter required when view=complexity',
        availableLevels: ['simple', 'medium', 'advanced', 'expert']
      };
    }

    const allBlocks: any[] = [];
    for (const catName of Object.keys(catalog.categories || {})) {
      allBlocks.push(...catalog.categories[catName].blocks);
    }

    const filtered = allBlocks.filter((b: any) => b.complexity === args.complexity);

    return {
      success: true,
      view: 'complexity',
      complexity: args.complexity,
      count: filtered.length,
      blocks: filtered,
      note: `Showing ${filtered.length} ${args.complexity} blocks. ${args.complexity === 'simple' ? 'Great for getting started!' : args.complexity === 'expert' ? 'Advanced blocks requiring deep knowledge.' : ''}`
    };
  }

  // All view - complete catalog
  return {
    success: true,
    view: 'all',
    catalog,
    note: 'Complete catalog loaded. Use filters (view="category", view="complexity", view="children") to narrow down results.'
  };
}

async function handleValidateContent(args: any, _wpClient: WordPressClient) {
  const blocks = args.blocks;
  const strict = args.strict || false;
  const autoFix = args.auto_fix || false;

  // Use auto-fix if requested
  if (autoFix) {
    const result = validateAndFix(blocks);
    
    return {
      status: result.valid ? 'success' : 'error',
      valid: result.valid,
      blocks: result.blocks,
      errors: result.errors,
      warnings: result.warnings,
      fixes_applied: result.fixes_applied,
      message: result.valid
        ? `Validation passed. ${result.fixes_applied.length > 0 ? 'Auto-fixes applied.' : 'No fixes needed.'}`
        : 'Validation failed after auto-fix. See errors for details.'
    };
  }

  // Standard validation without auto-fix
  const validation = validateBlocks(blocks);

  // In strict mode, treat warnings as errors
  if (strict && validation.warnings.length > 0) {
    return {
      status: 'error',
      valid: false,
      errors: validation.errors,
      warnings: validation.warnings,
      message: 'Strict validation failed - warnings treated as errors',
      suggestion: 'Run with auto_fix: true to automatically fix issues'
    };
  }

  return {
    status: validation.valid ? 'success' : 'error',
    valid: validation.valid,
    errors: validation.errors,
    warnings: validation.warnings,
    message: validation.valid
      ? 'All blocks are valid'
      : 'Validation failed - see errors for fix suggestions',
    suggestion: validation.valid ? undefined : 'Each error includes actionable fix steps with code examples'
  };
}

// Helper functions

function modifyBlockAttributes(
  blocks: any[],
  blockId: string,
  newAttrs: any
): any[] {
  return blocks.map((block: any) => {
    if (block.attrs?.block_id === blockId) {
      return {
        ...block,
        attrs: {
          ...block.attrs,
          ...newAttrs,
          // NEVER modify these critical attributes
          block_id: block.attrs.block_id,
          className: block.attrs.className
        }
      };
    }

    if (block.innerBlocks && block.innerBlocks.length > 0) {
      return {
        ...block,
        innerBlocks: modifyBlockAttributes(block.innerBlocks, blockId, newAttrs)
      };
    }

    return block;
  });
}

function insertBlock(blocks: any[], position: number, newBlock: any): any[] {
  const result = [...blocks];
  
  // Handle negative position as "from end"
  const insertAt = position < 0 ? result.length : position;
  
  result.splice(insertAt, 0, newBlock);
  return result;
}

function removeBlock(blocks: any[], blockId: string): any[] {
  return blocks.filter((block: any) => {
    if (block.attrs?.block_id === blockId) {
      return false; // Remove this block
    }

    if (block.innerBlocks && block.innerBlocks.length > 0) {
      block.innerBlocks = removeBlock(block.innerBlocks, blockId);
    }

    return true;
  });
}

/**
 * Extract all unique block names from block tree (including innerBlocks)
 */
function getAllBlockNames(blocks: any[]): string[] {
  const names: string[] = [];

  const extract = (block: any): void => {
    if (block.blockName) {
      names.push(block.blockName);
    }
    if (block.innerBlocks && Array.isArray(block.innerBlocks)) {
      block.innerBlocks.forEach(extract);
    }
  };

  blocks.forEach(extract);
  return names;
}
