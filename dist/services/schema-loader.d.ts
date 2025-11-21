/**
 * Block Schema Loader
 *
 * Loads and caches Nexter block schemas with intelligent filtering
 */
export interface BlockSchema {
    blockName: string;
    version: string;
    title: string;
    category: string;
    description: string;
    source: 'free' | 'pro';
    attributes: Record<string, any>;
    editingGuidelines: {
        safeToModify: string[];
        requiresValidation: string[];
        dangerous: string[];
    };
    styleDependencies: string[];
    commonUseCases: string[];
    relatedBlocks: string[];
    capabilities: string[];
    usesContext: string[];
    supports: Record<string, any>;
}
export declare class SchemaLoader {
    private cache;
    private index;
    private categories;
    private useCases;
    private schemasPath;
    constructor(schemasPath: string);
    /**
     * Load master indexes
     */
    private loadIndexes;
    /**
     * Get specific block schemas
     */
    getSchemas(blockNames: string[]): Promise<BlockSchema[]>;
    /**
     * Get schemas for a common use case
     */
    getSchemasForUseCase(useCase: string): Promise<BlockSchema[]>;
    /**
     * Get schemas by category
     */
    getSchemasByCategory(category: string): Promise<BlockSchema[]>;
    /**
     * Identify and load schemas from existing content
     */
    identifySchemasFromContent(blocks: any[]): Promise<BlockSchema[]>;
    /**
     * Get all available block names
     */
    getAllBlockNames(): string[];
    /**
     * Get loaded schema count
     */
    getLoadedCount(): number;
    /**
     * Get total available schemas
     */
    getTotalCount(): number;
    /**
     * Reload all indexes and clear cache
     */
    reload(): Promise<void>;
    /**
     * Get master index
     */
    getIndex(): any;
    /**
     * Get categories index
     */
    getCategories(): any;
    /**
     * Get use cases patterns
     */
    getUseCases(): any;
}
//# sourceMappingURL=schema-loader.d.ts.map