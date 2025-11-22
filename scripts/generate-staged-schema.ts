/**
 * Automated Staged Schema Generator
 *
 * Generates staged schema files (meta, core, styling, examples, full)
 * for all Nexter blocks based on existing flat schemas
 */

import fs from 'fs';
import path from 'path';

interface BlockInfo {
  name: string;
  title: string;
  complexity: 'simple' | 'medium' | 'advanced' | 'expert';
  keywords: string[];
  useCases: string[];
  category: string;
}

/**
 * Main generator function for a single block
 */
async function generateStagedSchema(blockInfo: BlockInfo, schemasPath: string) {
  const blockPath = blockInfo.name.replace('tpgb/', '');
  const fullSchemaPath = path.join(schemasPath, `${blockPath}.json`);

  // Check if full schema exists
  if (!fs.existsSync(fullSchemaPath)) {
    console.warn(`⚠️  Schema not found for ${blockInfo.name}, skipping...`);
    return;
  }

  // Read existing full schema
  const fullSchema = JSON.parse(fs.readFileSync(fullSchemaPath, 'utf8'));

  // Create block directory
  const blockDir = path.join(schemasPath, 'blocks', blockPath);
  fs.mkdirSync(blockDir, { recursive: true });

  // 1. Generate meta.json
  const meta = generateMeta(fullSchema, blockInfo);

  // 2. Generate core.json
  const core = generateCore(fullSchema, blockInfo);

  // 3. Generate styling.json
  const styling = generateStyling(fullSchema, blockInfo);

  // 4. Generate examples.json
  const examples = generateExamples(fullSchema, blockInfo);

  // Write files
  fs.writeFileSync(path.join(blockDir, 'meta.json'), JSON.stringify(meta, null, 2));
  fs.writeFileSync(path.join(blockDir, 'core.json'), JSON.stringify(core, null, 2));
  fs.writeFileSync(path.join(blockDir, 'styling.json'), JSON.stringify(styling, null, 2));
  fs.writeFileSync(path.join(blockDir, 'examples.json'), JSON.stringify(examples, null, 2));

  // 5. Copy full.json
  fs.copyFileSync(fullSchemaPath, path.join(blockDir, 'full.json'));

  console.log(`✅ Generated staged schema for ${blockInfo.name}`);
}

/**
 * Generate meta.json - minimal metadata
 */
function generateMeta(fullSchema: any, blockInfo: BlockInfo): any {
  return {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    version: fullSchema.version || "1.0.0",
    title: fullSchema.title,
    category: blockInfo.category,
    source: fullSchema.source,
    complexity: blockInfo.complexity,
    description: fullSchema.description?.substring(0, 200) || "",
    keywords: blockInfo.keywords,
    useCases: blockInfo.useCases.slice(0, 3)
  };
}

/**
 * Generate core.json - content and structure attributes
 */
function generateCore(fullSchema: any, blockInfo: BlockInfo): any {
  const coreAttrs = extractCoreAttributes(fullSchema.attributes || {});

  return {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Core Attributes`,
    description: "Core content and structure attributes",
    attributes: coreAttrs,
    required: fullSchema.required || ["block_id"],
    editingGuidelines: fullSchema.editingGuidelines,
    usesContext: fullSchema.usesContext,
    supports: fullSchema.supports,
    innerBlocksAllowed: fullSchema.innerBlocksAllowed,
    parentBlockRequired: fullSchema.parentBlockRequired
  };
}

/**
 * Generate styling.json - visual styling attributes
 */
function generateStyling(fullSchema: any, blockInfo: BlockInfo): any {
  const stylingAttrs = extractStylingAttributes(fullSchema.attributes || {});

  return {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Styling Attributes`,
    description: "Visual styling, typography, effects, and positioning",
    attributes: stylingAttrs,
    editingGuidelines: {
      safeToModify: fullSchema.editingGuidelines?.safeToModify || [],
      requiresValidation: fullSchema.editingGuidelines?.requiresValidation || []
    },
    styleDependencies: fullSchema.styleDependencies || []
  };
}

/**
 * Generate examples.json - usage examples and patterns
 */
function generateExamples(fullSchema: any, blockInfo: BlockInfo): any {
  // Use existing examples if available, otherwise generate basic ones
  const examples = fullSchema.examples || generateDefaultExamples(fullSchema, blockInfo);

  return {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Usage Examples`,
    description: "Common usage patterns and example configurations",
    examples: examples,
    commonPatterns: generateCommonPatterns(fullSchema, blockInfo)
  };
}

/**
 * Extract core (content/structure) attributes
 */
function extractCoreAttributes(attrs: any): any {
  const coreAttrs: any = {};

  // Always include block_id
  if (attrs.block_id) {
    coreAttrs.block_id = attrs.block_id;
  }

  // Core content attribute patterns
  const corePatterns = [
    'block_id', 'title', 'text', 'content', 'heading', 'description',
    'btntext', 'buttontext', 'iconname', 'link', 'url', 'href',
    'anchor', 'isrptblock', 'contenttype', 'htmltag', 'tag',
    'columns', 'rows', 'items', 'slides', 'options', 'choices',
    'placeholder', 'label', 'value', 'name', 'id', 'class',
    'source', 'data', 'query', 'fields', 'meta', 'acf'
  ];

  for (const [key, value] of Object.entries(attrs)) {
    const keyLower = key.toLowerCase();

    // Include if matches core patterns or is not a styling attribute
    if (corePatterns.some(p => keyLower.includes(p)) || !isStylingAttribute(key)) {
      coreAttrs[key] = replaceWithRefs(value);
    }
  }

  return coreAttrs;
}

/**
 * Extract styling attributes
 */
function extractStylingAttributes(attrs: any): any {
  const stylingAttrs: any = {};

  for (const [key, value] of Object.entries(attrs)) {
    if (isStylingAttribute(key)) {
      stylingAttrs[key] = replaceWithRefs(value);
    }
  }

  return stylingAttrs;
}

/**
 * Check if attribute is styling-related
 */
function isStylingAttribute(key: string): boolean {
  const stylingPatterns = [
    'color', 'typo', 'typography', 'font', 'align', 'shadow',
    'border', 'radius', 'background', 'bg', 'padding', 'margin',
    'spacing', 'gap', 'size', 'width', 'height', 'position',
    'offset', 'top', 'left', 'right', 'bottom', 'transform',
    'animation', 'transition', 'hover', 'stroke', 'blend',
    'opacity', 'overflow', 'display', 'flex', 'grid', 'zindex',
    'cursor', 'appearance', 'visibility', 'clip'
  ];

  const keyLower = key.toLowerCase();
  return stylingPatterns.some(p => keyLower.includes(p));
}

/**
 * Replace known patterns with $ref
 */
function replaceWithRefs(attr: any): any {
  if (!attr || typeof attr !== 'object') {
    return attr;
  }

  if (Array.isArray(attr)) {
    return attr;
  }

  // Check for known pattern signatures in default values
  if (attr.default && typeof attr.default === 'object') {
    const def = attr.default;

    // Typography pattern
    if (def.openTypography !== undefined) {
      return { "$ref": "definitions://typography" };
    }

    // Background pattern
    if (def.openBg !== undefined || def.bgType !== undefined) {
      return { "$ref": "definitions://background" };
    }

    // Border pattern (all sides)
    if (def.openBorder !== undefined && (def.borderTop || def.borderBottom || def.borderLeft || def.borderRight)) {
      return { "$ref": "definitions://border" };
    }

    // Shadow pattern
    if (def.openShadow !== undefined || def.typeShadow !== undefined) {
      return { "$ref": "definitions://shadow" };
    }

    // Link pattern
    if (def.url !== undefined && def.target !== undefined && def.nofollow !== undefined) {
      return { "$ref": "definitions://link" };
    }

    // Image pattern
    if (def.url !== undefined && def.id !== undefined && (def.alt !== undefined || def.size !== undefined)) {
      return { "$ref": "definitions://image" };
    }

    // Icon pattern
    if (def.icon !== undefined && (def.iconLibrary !== undefined || def.iconType !== undefined)) {
      return { "$ref": "definitions://icon" };
    }

    // Responsive unit pattern
    if (def.md !== undefined && def.unit !== undefined) {
      return { "$ref": "definitions://responsive-unit" };
    }

    // Responsive spacing pattern
    if (def.md !== undefined && (def.top !== undefined || def.left !== undefined)) {
      return { "$ref": "definitions://responsive-spacing" };
    }

    // Scroll animation pattern
    if (def.animationType !== undefined || def.scrollAnimation !== undefined) {
      return { "$ref": "definitions://scroll-animation" };
    }

    // Hover animation pattern
    if (def.hoverEffect !== undefined || def.hoverAnimation !== undefined) {
      return { "$ref": "definitions://hover-animation" };
    }

    // Global position pattern
    if (def.position !== undefined && def.zIndex !== undefined) {
      return { "$ref": "definitions://responsive-position" };
    }
  }

  return attr;
}

/**
 * Generate default examples when none exist
 */
function generateDefaultExamples(fullSchema: any, blockInfo: BlockInfo): any[] {
  const examples: any[] = [];

  // Generate basic example
  const basicAttrs: any = { block_id: "a1b2" };

  // Add common content attributes
  if (fullSchema.attributes?.title) {
    basicAttrs.title = `Sample ${fullSchema.title}`;
  }
  if (fullSchema.attributes?.text) {
    basicAttrs.text = "Sample text content";
  }
  if (fullSchema.attributes?.content) {
    basicAttrs.content = "Sample content";
  }

  examples.push({
    name: `Basic ${fullSchema.title}`,
    description: `Simple ${fullSchema.title.toLowerCase()} with default settings`,
    attributes: basicAttrs,
    useCase: blockInfo.useCases[0] || "General use"
  });

  return examples;
}

/**
 * Generate common patterns section
 */
function generateCommonPatterns(fullSchema: any, blockInfo: BlockInfo): any {
  const patterns: any = {};

  // Generate default pattern based on block type
  if (blockInfo.complexity === 'simple') {
    patterns.default = {
      block_id: "example"
    };
  }

  return patterns;
}

/**
 * Load catalog and generate for all blocks
 */
async function generateAll() {
  const schemasPath = './schemas';
  const catalogPath = path.join(schemasPath, '_meta', 'catalog.json');

  if (!fs.existsSync(catalogPath)) {
    console.error('❌ Catalog not found at', catalogPath);
    process.exit(1);
  }

  const catalog = JSON.parse(fs.readFileSync(catalogPath, 'utf8'));

  console.log(`\n📦 Starting staged schema generation for ${catalog.totalBlocks} blocks\n`);

  let generated = 0;
  let skipped = 0;
  let errors = 0;

  // Iterate through all categories
  for (const [categoryName, categoryData] of Object.entries(catalog.categories)) {
    const category = categoryData as any;
    console.log(`\n📁 Category: ${categoryName} (${category.blocks.length} blocks)`);

    for (const block of category.blocks) {
      const blockInfo: BlockInfo = {
        name: block.name,
        title: block.title,
        complexity: block.complexity,
        keywords: block.keywords || [],
        useCases: block.useCases || [],
        category: categoryName
      };

      // Skip if already exists (tp-heading was done manually)
      const blockPath = block.name.replace('tpgb/', '');
      const blockDir = path.join(schemasPath, 'blocks', blockPath);

      if (fs.existsSync(blockDir)) {
        console.log(`  ⏭️  ${block.name} - already exists, skipping`);
        skipped++;
        continue;
      }

      try {
        await generateStagedSchema(blockInfo, schemasPath);
        generated++;
      } catch (error: any) {
        console.error(`  ❌ Failed for ${block.name}:`, error.message);
        errors++;
      }
    }
  }

  console.log(`\n✨ Generation complete!`);
  console.log(`   ✅ Generated: ${generated}`);
  console.log(`   ⏭️  Skipped: ${skipped}`);
  console.log(`   ❌ Errors: ${errors}`);
  console.log(`   📊 Total: ${generated + skipped + errors}/${catalog.totalBlocks}\n`);
}

// Run the generator
generateAll().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
