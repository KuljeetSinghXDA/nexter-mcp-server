/**
 * MCP Tools Registration
 *
 * Registers all tools available to AI agents
 */
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { WordPressClient } from '../services/wordpress-client.js';
import { SchemaLoader } from '../services/schema-loader.js';
import { StructuredError } from '../types/errors.js';
export declare function registerTools(server: Server, wpClient: WordPressClient, schemaLoader: SchemaLoader): void;
export declare const toolHandlers: {
    get_block_schemas: typeof handleGetBlockSchemas;
    create_content: typeof handleCreateContent;
    analyze_content: typeof handleAnalyzeContent;
    search_content: typeof handleSearchContent;
    edit_content: typeof handleEditContent;
    validate_content: typeof handleValidateContent;
};
export declare const toolDefinitions: ({
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            block_names: {
                type: string;
                items: {
                    type: string;
                };
                description: string;
            };
            use_case: {
                type: string;
                enum: string[];
                description: string;
            };
            category: {
                type: string;
                enum: string[];
                description: string;
            };
            post_type?: undefined;
            title?: undefined;
            blocks?: undefined;
            excerpt?: undefined;
            categories?: undefined;
            tags?: undefined;
            post_id?: undefined;
            include_schemas?: undefined;
            query?: undefined;
            limit?: undefined;
            operations?: undefined;
            title_update?: undefined;
            strict?: undefined;
        };
        required?: undefined;
    };
} | {
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            post_type: {
                type: string;
                enum: string[];
                default?: undefined;
            };
            title: {
                type: string;
            };
            blocks: {
                type: string;
            };
            excerpt: {
                type: string;
            };
            categories: {
                type: string;
                items: {
                    type: string;
                };
            };
            tags: {
                type: string;
                items: {
                    type: string;
                };
            };
            block_names?: undefined;
            use_case?: undefined;
            category?: undefined;
            post_id?: undefined;
            include_schemas?: undefined;
            query?: undefined;
            limit?: undefined;
            operations?: undefined;
            title_update?: undefined;
            strict?: undefined;
        };
        required: string[];
    };
} | {
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            post_id: {
                type: string;
            };
            include_schemas: {
                type: string;
                default: boolean;
            };
            block_names?: undefined;
            use_case?: undefined;
            category?: undefined;
            post_type?: undefined;
            title?: undefined;
            blocks?: undefined;
            excerpt?: undefined;
            categories?: undefined;
            tags?: undefined;
            query?: undefined;
            limit?: undefined;
            operations?: undefined;
            title_update?: undefined;
            strict?: undefined;
        };
        required: string[];
    };
} | {
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            query: {
                type: string;
            };
            post_type: {
                type: string;
                enum: string[];
                default: string;
            };
            limit: {
                type: string;
                default: number;
            };
            block_names?: undefined;
            use_case?: undefined;
            category?: undefined;
            title?: undefined;
            blocks?: undefined;
            excerpt?: undefined;
            categories?: undefined;
            tags?: undefined;
            post_id?: undefined;
            include_schemas?: undefined;
            operations?: undefined;
            title_update?: undefined;
            strict?: undefined;
        };
        required: string[];
    };
} | {
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            post_id: {
                type: string;
            };
            operations: {
                type: string;
            };
            title_update: {
                type: string;
            };
            block_names?: undefined;
            use_case?: undefined;
            category?: undefined;
            post_type?: undefined;
            title?: undefined;
            blocks?: undefined;
            excerpt?: undefined;
            categories?: undefined;
            tags?: undefined;
            include_schemas?: undefined;
            query?: undefined;
            limit?: undefined;
            strict?: undefined;
        };
        required: string[];
    };
} | {
    name: string;
    description: string;
    inputSchema: {
        type: string;
        properties: {
            blocks: {
                type: string;
            };
            strict: {
                type: string;
                default: boolean;
            };
            block_names?: undefined;
            use_case?: undefined;
            category?: undefined;
            post_type?: undefined;
            title?: undefined;
            excerpt?: undefined;
            categories?: undefined;
            tags?: undefined;
            post_id?: undefined;
            include_schemas?: undefined;
            query?: undefined;
            limit?: undefined;
            operations?: undefined;
            title_update?: undefined;
        };
        required: string[];
    };
})[];
declare function handleGetBlockSchemas(args: any, schemaLoader: SchemaLoader): Promise<{
    success: boolean;
    error: string;
    schemas?: undefined;
    count?: undefined;
    note?: undefined;
} | {
    success: boolean;
    schemas: import("../services/schema-loader.js").BlockSchema[];
    count: number;
    note: string;
    error?: undefined;
}>;
declare function handleCreateContent(args: any, wpClient: WordPressClient): Promise<{
    status: string;
    error_type: string;
    error_code: string;
    message: string;
    errors: StructuredError[];
    warnings: string[];
    suggestion: string;
    details?: undefined;
    post_id?: undefined;
    title?: undefined;
    post_status?: undefined;
    preview_url?: undefined;
    edit_url?: undefined;
} | {
    status: string;
    error_type: string;
    error_code: string;
    message: string;
    details: any;
    warnings: any[];
    errors?: undefined;
    suggestion?: undefined;
    post_id?: undefined;
    title?: undefined;
    post_status?: undefined;
    preview_url?: undefined;
    edit_url?: undefined;
} | {
    status: string;
    post_id: any;
    title: string;
    post_status: string;
    preview_url: any;
    edit_url: any;
    warnings: any[];
    message: string;
    error_type?: undefined;
    error_code?: undefined;
    errors?: undefined;
    suggestion?: undefined;
    details?: undefined;
} | {
    status: string;
    error_type: string;
    error_code: string;
    message: any;
    details: any;
    errors?: undefined;
    warnings?: undefined;
    suggestion?: undefined;
    post_id?: undefined;
    title?: undefined;
    post_status?: undefined;
    preview_url?: undefined;
    edit_url?: undefined;
}>;
declare function handleAnalyzeContent(args: any, wpClient: WordPressClient, schemaLoader: SchemaLoader): Promise<{
    success: boolean;
    post_info: {
        id: any;
        title: any;
        status: any;
        type: any;
    };
    block_summary: {
        total: any;
        nexter_blocks: any;
        types: unknown[];
    };
    blocks: any;
    schemas: any[] | undefined;
}>;
declare function handleSearchContent(args: any, wpClient: WordPressClient): Promise<{
    success: boolean;
    found: any;
    results: any;
    query: any;
    note: string;
}>;
declare function handleEditContent(args: any, wpClient: WordPressClient, schemaLoader: SchemaLoader): Promise<{
    success: boolean;
    error: string;
    errors?: undefined;
    warnings?: undefined;
    suggestion?: undefined;
    details?: undefined;
    original_preserved?: undefined;
    post_id?: undefined;
    changes_applied?: undefined;
    revision_id?: undefined;
    preview_url?: undefined;
    message?: undefined;
} | {
    success: boolean;
    error: string;
    errors: StructuredError[];
    warnings: string[];
    suggestion: string;
    details?: undefined;
    original_preserved?: undefined;
    post_id?: undefined;
    changes_applied?: undefined;
    revision_id?: undefined;
    preview_url?: undefined;
    message?: undefined;
} | {
    success: boolean;
    error: string;
    details: any;
    original_preserved: boolean;
    errors?: undefined;
    warnings?: undefined;
    suggestion?: undefined;
    post_id?: undefined;
    changes_applied?: undefined;
    revision_id?: undefined;
    preview_url?: undefined;
    message?: undefined;
} | {
    success: boolean;
    post_id: any;
    changes_applied: string[];
    revision_id: any;
    preview_url: any;
    warnings: any[];
    message: string;
    error?: undefined;
    errors?: undefined;
    suggestion?: undefined;
    details?: undefined;
    original_preserved?: undefined;
}>;
declare function handleValidateContent(args: any, wpClient: WordPressClient): Promise<{
    status: string;
    valid: boolean;
    blocks: any[];
    errors: StructuredError[];
    warnings: string[];
    fixes_applied: string[];
    message: string;
    suggestion?: undefined;
} | {
    status: string;
    valid: boolean;
    errors: StructuredError[];
    warnings: string[];
    message: string;
    suggestion: string | undefined;
    blocks?: undefined;
    fixes_applied?: undefined;
}>;
export {};
//# sourceMappingURL=index.d.ts.map