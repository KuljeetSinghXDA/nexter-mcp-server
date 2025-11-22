/**
 * Block Schema Loader
 *
 * Loads and caches Nexter block schemas with intelligent filtering
 * Supports progressive loading and $ref resolution
 */

import fs from 'fs';
import path from 'path';
import { logger } from '../utils/logger.js';

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

export type SchemaLevel = 'meta' | 'core' | 'styling' | 'examples' | 'full';

export class SchemaLoader {
  private cache: Map<string, BlockSchema> = new Map();
  private stagedCache: Map<string, Map<SchemaLevel, any>> = new Map();
  private definitionsCache: Map<string, any> = new Map();
  private index: any = null;
  private catalog: any = null;
  private categories: any = null;
  private useCases: any = null;
  private schemasPath: string;

  constructor(schemasPath: string) {
    this.schemasPath = schemasPath;
    this.loadIndexes();
    this.loadDefinitions();
  }

  /**
   * Load master indexes and catalog
   */
  private loadIndexes() {
    // Reset to safe defaults so callers always get a consistent shape
    this.index = {
      totalBlocks: 0,
      blocks: []
    };
    this.categories = {};
    this.useCases = {};
    this.catalog = null;

    try {
      // Load new catalog (priority)
      const catalogPath = path.join(this.schemasPath, '_meta', 'catalog.json');
      if (fs.existsSync(catalogPath)) {
        this.catalog = JSON.parse(fs.readFileSync(catalogPath, 'utf8'));
        logger.info(`📚 Loaded block catalog: ${this.catalog.totalBlocks} blocks`);
      }

      // Load legacy master index (fallback)
      const indexPath = path.join(this.schemasPath, 'index.json');
      if (fs.existsSync(indexPath)) {
        this.index = JSON.parse(fs.readFileSync(indexPath, 'utf8'));
        if (!this.catalog) {
          logger.info(`📚 Loaded schema index: ${this.index.totalBlocks} blocks`);
        }
      }

      // Load legacy category index (fallback)
      const categoriesPath = path.join(this.schemasPath, 'categories.json');
      if (fs.existsSync(categoriesPath)) {
        this.categories = JSON.parse(fs.readFileSync(categoriesPath, 'utf8'));
      }

      // Load legacy use case patterns (fallback)
      const useCasesPath = path.join(this.schemasPath, 'use-cases.json');
      if (fs.existsSync(useCasesPath)) {
        this.useCases = JSON.parse(fs.readFileSync(useCasesPath, 'utf8'));
      }
    } catch (error: any) {
      logger.error('Failed to load schema indexes:', error);
    }
  }

  /**
   * Load all common definitions for $ref resolution
   */
  private loadDefinitions() {
    try {
      const definitionsPath = path.join(this.schemasPath, '_definitions');
      if (!fs.existsSync(definitionsPath)) {
        logger.warn('No _definitions directory found, $ref resolution unavailable');
        return;
      }

      const files = fs.readdirSync(definitionsPath).filter(f => f.endsWith('.json'));

      for (const file of files) {
        const filePath = path.join(definitionsPath, file);
        const defName = file.replace('.json', '');
        try {
          const definition = JSON.parse(fs.readFileSync(filePath, 'utf8'));
          this.definitionsCache.set(defName, definition);
        } catch (error: any) {
          logger.error(`Failed to load definition ${defName}:`, error);
        }
      }

      logger.info(`📦 Loaded ${this.definitionsCache.size} common definitions`);
    } catch (error: any) {
      logger.error('Failed to load definitions:', error);
    }
  }

  /**
   * Resolve $ref references in schema objects
   */
  private resolve$ref(obj: any, depth: number = 0): any {
    if (depth > 10) {
      logger.warn('Max $ref resolution depth reached, possible circular reference');
      return obj;
    }

    if (!obj || typeof obj !== 'object') {
      return obj;
    }

    if (Array.isArray(obj)) {
      return obj.map(item => this.resolve$ref(item, depth + 1));
    }

    // Handle $ref property
    if (obj.$ref && typeof obj.$ref === 'string') {
      const refPath = obj.$ref;

      // Handle definitions:// protocol
      if (refPath.startsWith('definitions://')) {
        const defName = refPath.replace('definitions://', '');

        // Try to find in loaded definitions
        for (const [name, definition] of this.definitionsCache.entries()) {
          if (definition.$id === refPath) {
            // Return the structure or definition, removing $id
            const resolved = { ...definition };
            delete resolved.$id;
            delete resolved.$schema;
            return this.resolve$ref(resolved.structure || resolved, depth + 1);
          }

          // Check nested definitions (e.g., responsive.json has multiple definitions)
          if (definition.definitions && definition.definitions[defName]) {
            const resolved = { ...definition.definitions[defName] };
            delete resolved.$id;
            return this.resolve$ref(resolved.structure || resolved, depth + 1);
          }
        }

        logger.warn(`Could not resolve $ref: ${refPath}`);
        return { $ref: refPath }; // Keep unresolved reference
      }

      return obj; // Unknown $ref format
    }

    // Recursively resolve nested objects
    const resolved: any = {};
    for (const [key, value] of Object.entries(obj)) {
      resolved[key] = this.resolve$ref(value, depth + 1);
    }
    return resolved;
  }

  /**
   * Get block schema with progressive loading
   * Supports staged loading (meta, core, styling, examples) or full schema
   */
  async getBlockSchema(
    blockName: string,
    levels: SchemaLevel[] = ['full'],
    resolve$refs: boolean = true
  ): Promise<any | null> {
    const blockPath = blockName.replace('tpgb/', '');

    // Try new staged structure first (blocks/{block-name}/)
    const stagedDir = path.join(this.schemasPath, 'blocks', blockPath);

    if (fs.existsSync(stagedDir)) {
      return this.loadStagedSchema(blockName, stagedDir, levels, resolve$refs);
    }

    // Fallback to legacy flat structure ({block-name}.json)
    const legacyPath = path.join(this.schemasPath, `${blockPath}.json`);
    if (fs.existsSync(legacyPath)) {
      try {
        const schema = JSON.parse(fs.readFileSync(legacyPath, 'utf8'));
        return resolve$refs ? this.resolve$ref(schema) : schema;
      } catch (error: any) {
        logger.error(`Failed to load schema for ${blockName}:`, error);
        return null;
      }
    }

    logger.warn(`Schema not found: ${blockName}`);
    return null;
  }

  /**
   * Load staged schema files and merge based on requested levels
   */
  private async loadStagedSchema(
    blockName: string,
    dir: string,
    levels: SchemaLevel[],
    resolve$refs: boolean
  ): Promise<any> {
    const levelFiles: Record<SchemaLevel, string> = {
      meta: 'meta.json',
      core: 'core.json',
      styling: 'styling.json',
      examples: 'examples.json',
      full: 'full.json'
    };

    // If 'full' is requested, just load that
    if (levels.includes('full')) {
      const fullPath = path.join(dir, 'full.json');
      if (fs.existsSync(fullPath)) {
        const schema = JSON.parse(fs.readFileSync(fullPath, 'utf8'));
        return resolve$refs ? this.resolve$ref(schema) : schema;
      }
    }

    // Load and merge requested levels
    let merged: any = {};

    for (const level of levels) {
      const filePath = path.join(dir, levelFiles[level]);
      if (fs.existsSync(filePath)) {
        try {
          const levelData = JSON.parse(fs.readFileSync(filePath, 'utf8'));

          // Merge attributes from each level
          if (levelData.attributes) {
            merged.attributes = { ...merged.attributes, ...levelData.attributes };
          }

          // Merge other properties (shallow merge)
          merged = { ...merged, ...levelData };
        } catch (error: any) {
          logger.error(`Failed to load ${level} for ${blockName}:`, error);
        }
      }
    }

    return resolve$refs ? this.resolve$ref(merged) : merged;
  }

  /**
   * Get specific block schemas (backward compatible)
   */
  async getSchemas(blockNames: string[]): Promise<BlockSchema[]> {
    const schemas: BlockSchema[] = [];

    for (const blockName of blockNames) {
      // Check cache first
      if (this.cache.has(blockName)) {
        schemas.push(this.cache.get(blockName)!);
        continue;
      }

      // Try new progressive loading (full schema)
      const schema = await this.getBlockSchema(blockName, ['full'], true);
      if (schema) {
        this.cache.set(blockName, schema);
        schemas.push(schema);
        continue;
      }

      // Legacy fallback (already tried in getBlockSchema)
      logger.warn(`Schema not found: ${blockName}`);
    }

    return schemas;
  }

  /**
   * Get schemas for a common use case
   */
  async getSchemasForUseCase(useCase: string): Promise<BlockSchema[]> {
    if (!this.useCases || !this.useCases[useCase]) {
      logger.warn(`Unknown use case: ${useCase}`);
      return [];
    }

    const blockNames = this.useCases[useCase].map((b: any) => b.name);
    return this.getSchemas(blockNames);
  }

  /**
   * Get schemas by category
   */
  async getSchemasByCategory(category: string): Promise<BlockSchema[]> {
    if (!this.categories || !this.categories[category]) {
      logger.warn(`Unknown category: ${category}`);
      return [];
    }

    const blockNames = this.categories[category].map((b: any) => b.name);
    return this.getSchemas(blockNames);
  }

  /**
   * Identify and load schemas from existing content
   */
  async identifySchemasFromContent(blocks: any[]): Promise<BlockSchema[]> {
    const nexterBlocks = blocks
      .filter((b: any) => b.blockName?.startsWith('tpgb/'))
      .map((b: any) => b.blockName!);

    // Get unique block types
    const uniqueBlocks = [...new Set(nexterBlocks)];

    return this.getSchemas(uniqueBlocks);
  }

  /**
   * Get all available block names
   */
  getAllBlockNames(): string[] {
    if (!this.index) return [];
    return this.index.blocks.map((b: any) => b.name);
  }

  /**
   * Get loaded schema count
   */
  getLoadedCount(): number {
    return this.cache.size;
  }

  /**
   * Get total available schemas
   */
  getTotalCount(): number {
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
   * Get master index (returns catalog if available, falls back to legacy index)
   */
  getIndex() {
    return this.catalog || this.index;
  }

  /**
   * Get block catalog (new format)
   */
  getCatalog() {
    return this.catalog;
  }

  /**
   * Get categories (from catalog or legacy)
   */
  getCategories() {
    if (this.catalog && this.catalog.categories) {
      return this.catalog.categories;
    }
    return this.categories;
  }

  /**
   * Get use cases patterns (from catalog or legacy)
   */
  getUseCases() {
    if (this.catalog && this.catalog.commonUseCases) {
      return this.catalog.commonUseCases;
    }
    return this.useCases;
  }

  /**
   * Get common definition by name
   */
  getDefinition(name: string): any | null {
    return this.definitionsCache.get(name) || null;
  }

  /**
   * Get all loaded definitions
   */
  getDefinitions(): Record<string, any> {
    const defs: Record<string, any> = {};
    for (const [name, def] of this.definitionsCache.entries()) {
      defs[name] = def;
    }
    return defs;
  }
}
