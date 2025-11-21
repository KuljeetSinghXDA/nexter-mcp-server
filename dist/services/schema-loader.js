/**
 * Block Schema Loader
 *
 * Loads and caches Nexter block schemas with intelligent filtering
 */
import fs from 'fs';
import path from 'path';
import { logger } from '../utils/logger.js';
export class SchemaLoader {
    cache = new Map();
    index = null;
    categories = null;
    useCases = null;
    schemasPath;
    constructor(schemasPath) {
        this.schemasPath = schemasPath;
        this.loadIndexes();
    }
    /**
     * Load master indexes
     */
    loadIndexes() {
        // Reset to safe defaults so callers always get a consistent shape
        this.index = {
            totalBlocks: 0,
            blocks: []
        };
        this.categories = {};
        this.useCases = {};
        try {
            // Load master index
            const indexPath = path.join(this.schemasPath, 'index.json');
            if (fs.existsSync(indexPath)) {
                this.index = JSON.parse(fs.readFileSync(indexPath, 'utf8'));
                logger.info(`📚 Loaded schema index: ${this.index.totalBlocks} blocks`);
            }
            // Load category index
            const categoriesPath = path.join(this.schemasPath, 'categories.json');
            if (fs.existsSync(categoriesPath)) {
                this.categories = JSON.parse(fs.readFileSync(categoriesPath, 'utf8'));
            }
            // Load use case patterns
            const useCasesPath = path.join(this.schemasPath, 'use-cases.json');
            if (fs.existsSync(useCasesPath)) {
                this.useCases = JSON.parse(fs.readFileSync(useCasesPath, 'utf8'));
            }
        }
        catch (error) {
            logger.error('Failed to load schema indexes:', error);
        }
    }
    /**
     * Get specific block schemas
     */
    async getSchemas(blockNames) {
        const schemas = [];
        for (const blockName of blockNames) {
            // Check cache first
            if (this.cache.has(blockName)) {
                schemas.push(this.cache.get(blockName));
                continue;
            }
            // Load from file
            const fileName = blockName.replace('tpgb/', '') + '.json';
            const filePath = path.join(this.schemasPath, fileName);
            if (fs.existsSync(filePath)) {
                try {
                    const schema = JSON.parse(fs.readFileSync(filePath, 'utf8'));
                    this.cache.set(blockName, schema);
                    schemas.push(schema);
                }
                catch (error) {
                    logger.error(`Failed to load schema for ${blockName}:`, error);
                }
            }
            else {
                logger.warn(`Schema not found: ${fileName}`);
            }
        }
        return schemas;
    }
    /**
     * Get schemas for a common use case
     */
    async getSchemasForUseCase(useCase) {
        if (!this.useCases || !this.useCases[useCase]) {
            logger.warn(`Unknown use case: ${useCase}`);
            return [];
        }
        const blockNames = this.useCases[useCase].map((b) => b.name);
        return this.getSchemas(blockNames);
    }
    /**
     * Get schemas by category
     */
    async getSchemasByCategory(category) {
        if (!this.categories || !this.categories[category]) {
            logger.warn(`Unknown category: ${category}`);
            return [];
        }
        const blockNames = this.categories[category].map((b) => b.name);
        return this.getSchemas(blockNames);
    }
    /**
     * Identify and load schemas from existing content
     */
    async identifySchemasFromContent(blocks) {
        const nexterBlocks = blocks
            .filter((b) => b.blockName?.startsWith('tpgb/'))
            .map((b) => b.blockName);
        // Get unique block types
        const uniqueBlocks = [...new Set(nexterBlocks)];
        return this.getSchemas(uniqueBlocks);
    }
    /**
     * Get all available block names
     */
    getAllBlockNames() {
        if (!this.index)
            return [];
        return this.index.blocks.map((b) => b.name);
    }
    /**
     * Get loaded schema count
     */
    getLoadedCount() {
        return this.cache.size;
    }
    /**
     * Get total available schemas
     */
    getTotalCount() {
        return this.index?.totalBlocks || 0;
    }
    /**
     * Reload all indexes and clear cache
     */
    async reload() {
        this.cache.clear();
        this.loadIndexes();
        logger.info('🔄 Schemas reloaded');
    }
    /**
     * Get master index
     */
    getIndex() {
        return this.index;
    }
    /**
     * Get categories index
     */
    getCategories() {
        return this.categories;
    }
    /**
     * Get use cases patterns
     */
    getUseCases() {
        return this.useCases;
    }
}
//# sourceMappingURL=schema-loader.js.map