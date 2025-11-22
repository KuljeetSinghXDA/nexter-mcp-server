# Scaling Guide: Converting Remaining 83 Blocks to Staged Schemas

## Current Status

✅ **Completed**: 1/84 blocks (tp-heading)
⏳ **Remaining**: 83 blocks
📦 **Total Files to Create**: 415 files (83 blocks × 5 files)

## Architecture Overview

Each block needs 5 files in `schemas/blocks/{block-name}/`:

1. **meta.json** (~200-300 bytes) - Basic metadata
2. **core.json** (~1-3 KB) - Core attributes with $ref
3. **styling.json** (~2-5 KB) - Styling attributes with $ref
4. **examples.json** (~3-6 KB) - Usage examples
5. **full.json** (existing schema) - Complete schema (copy from `schemas/{block}.json`)

## Batch Processing Strategy

### Recommended Batches (by complexity):

**Batch A: Simple Blocks (16 blocks, ~2-3 hours)**
- tp-empty-space, tp-blockquote, tp-social-icons
- tp-messagebox, tp-progress-bar, tp-expand
- All nxt-* form fields (10 simple fields)

**Batch B: Medium Complexity (40 blocks, ~8-10 hours)**
- Content blocks (tp-pro-paragraph, tp-adv-typo, tp-stylist-list)
- Marketing blocks (tp-button, tp-button-core, tp-advanced-buttons, tp-cta-banner)
- Interactive blocks (tp-accordion, tp-tabs-tours, tp-switcher)
- Media blocks (tp-image, tp-creative-image, tp-video, tp-audio-player)

**Batch C: Advanced Blocks (17 blocks, ~6-8 hours)**
- tp-pricing-table, tp-pricing-list, tp-testimonials
- tp-hotspot, tp-flipbox, tp-animated-service-boxes
- tp-timeline, tp-process-steps, tp-team-listing

**Batch D: Expert Blocks (11 blocks, ~5-7 hours)**
- tp-container, tp-row, tp-column (layout blocks)
- tp-repeater-block, tp-repeater-layout, tp-dynamic-heading (ACF)
- tp-popup-builder, tp-dynamic-device, tp-carousel-remote
- tp-form-block (parent block), tp-advanced-chart

**Total Estimated Time**: 21-28 hours for manual creation

## Automation Approach (Recommended)

### Step 1: Create Generator Script

Create `scripts/generate-staged-schema.ts`:

```typescript
import fs from 'fs';
import path from 'path';

interface BlockInfo {
  blockName: string;
  complexity: 'simple' | 'medium' | 'advanced' | 'expert';
}

async function generateStagedSchema(blockName: string) {
  const blockPath = blockName.replace('tpgb/', '');
  const fullSchemaPath = `./schemas/${blockPath}.json`;

  // Read existing full schema
  const fullSchema = JSON.parse(fs.readFileSync(fullSchemaPath, 'utf8'));

  // Create block directory
  const blockDir = `./schemas/blocks/${blockPath}`;
  fs.mkdirSync(blockDir, { recursive: true });

  // 1. Generate meta.json
  const meta = {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    version: fullSchema.version,
    title: fullSchema.title,
    category: fullSchema.category,
    source: fullSchema.source,
    complexity: determineComplexity(fullSchema),
    description: fullSchema.description?.substring(0, 150),
    keywords: extractKeywords(fullSchema),
    useCases: fullSchema.commonUseCases?.slice(0, 3) || []
  };

  // 2. Generate core.json (content + structure attributes)
  const core = {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Core Attributes`,
    description: "Core content and structure attributes",
    attributes: extractCoreAttributes(fullSchema.attributes),
    required: fullSchema.required || ["block_id"],
    editingGuidelines: fullSchema.editingGuidelines,
    usesContext: fullSchema.usesContext,
    supports: fullSchema.supports,
    innerBlocksAllowed: fullSchema.innerBlocksAllowed,
    parentBlockRequired: fullSchema.parentBlockRequired
  };

  // 3. Generate styling.json (visual attributes with $ref)
  const styling = {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Styling Attributes`,
    description: "Visual styling, typography, effects, and positioning",
    attributes: extractStylingAttributes(fullSchema.attributes),
    editingGuidelines: {
      safeToModify: fullSchema.editingGuidelines?.safeToModify || [],
      requiresValidation: fullSchema.editingGuidelines?.requiresValidation || []
    },
    styleDependencies: fullSchema.styleDependencies || []
  };

  // 4. Generate examples.json
  const examples = {
    "$schema": "http://json-schema.org/draft-07/schema#",
    blockName: fullSchema.blockName,
    title: `${fullSchema.title} - Usage Examples`,
    description: "Common usage patterns and example configurations",
    examples: fullSchema.examples || generateDefaultExamples(fullSchema),
    commonPatterns: generateCommonPatterns(fullSchema)
  };

  // 5. Copy full.json
  fs.copyFileSync(fullSchemaPath, path.join(blockDir, 'full.json'));

  // Write files
  fs.writeFileSync(path.join(blockDir, 'meta.json'), JSON.stringify(meta, null, 2));
  fs.writeFileSync(path.join(blockDir, 'core.json'), JSON.stringify(core, null, 2));
  fs.writeFileSync(path.join(blockDir, 'styling.json'), JSON.stringify(styling, null, 2));
  fs.writeFileSync(path.join(blockDir, 'examples.json'), JSON.stringify(examples, null, 2));

  console.log(`✅ Generated staged schema for ${blockName}`);
}

function extractCoreAttributes(attrs: any) {
  const coreAttrs: any = {};
  const coreKeys = ['block_id', 'title', 'text', 'content', 'btnText', 'iconName',
                    'link', 'url', 'anchor', 'isRptBlock', 'contentType'];

  for (const key of Object.keys(attrs)) {
    // Include core content attributes
    if (coreKeys.some(k => key.toLowerCase().includes(k.toLowerCase())) ||
        !isStylingAttribute(key)) {
      coreAttrs[key] = replaceWithRefs(attrs[key]);
    }
  }

  return coreAttrs;
}

function extractStylingAttributes(attrs: any) {
  const stylingAttrs: any = {};

  for (const key of Object.keys(attrs)) {
    if (isStylingAttribute(key)) {
      stylingAttrs[key] = replaceWithRefs(attrs[key]);
    }
  }

  return stylingAttrs;
}

function isStylingAttribute(key: string): boolean {
  const stylingPatterns = [
    'color', 'typo', 'typography', 'align', 'shadow', 'border', 'radius',
    'background', 'bg', 'padding', 'margin', 'spacing', 'size', 'width',
    'height', 'position', 'offset', 'transform', 'animation', 'hover',
    'stroke', 'blend', 'opacity', 'overflow'
  ];

  return stylingPatterns.some(p => key.toLowerCase().includes(p));
}

function replaceWithRefs(attr: any): any {
  // Replace known patterns with $ref
  if (attr.type === 'object' && attr.default) {
    // Typography pattern
    if (attr.default.openTypography !== undefined) {
      return { "$ref": "definitions://typography", ...attr };
    }
    // Background pattern
    if (attr.default.openBg !== undefined || attr.default.bgType !== undefined) {
      return { "$ref": "definitions://background", ...attr };
    }
    // Border pattern
    if (attr.default.openBorder !== undefined) {
      return { "$ref": "definitions://border", ...attr };
    }
    // Shadow pattern
    if (attr.default.openShadow !== undefined) {
      return { "$ref": "definitions://shadow", ...attr };
    }
    // Link pattern
    if (attr.default.url !== undefined && attr.default.target !== undefined) {
      return { "$ref": "definitions://link", ...attr };
    }
    // Responsive unit pattern
    if (attr.default.md !== undefined && attr.default.unit !== undefined) {
      return { "$ref": "definitions://responsive-unit", ...attr };
    }
  }

  return attr;
}

// Run for all blocks
async function generateAll() {
  const catalog = JSON.parse(fs.readFileSync('./schemas/_meta/catalog.json', 'utf8'));

  for (const category of Object.values(catalog.categories)) {
    for (const block of (category as any).blocks) {
      try {
        await generateStagedSchema(block.name);
      } catch (error) {
        console.error(`❌ Failed for ${block.name}:`, error);
      }
    }
  }
}

generateAll();
```

### Step 2: Run Generator

```bash
npm run build
node dist/scripts/generate-staged-schema.js
```

### Step 3: Manual Review & Enhancement

After generation, manually review and enhance:

1. **Verify $ref replacements** are accurate
2. **Add better examples** based on common use cases
3. **Enhance descriptions** for clarity
4. **Add commonPatterns** to examples.json

## Quality Checklist Per Block

- [ ] meta.json: Has accurate keywords and complexity level
- [ ] core.json: Contains all content/structure attributes
- [ ] core.json: Has correct $ref for link, image, icon objects
- [ ] styling.json: Contains all visual/styling attributes
- [ ] styling.json: Has $ref for typography, background, border, shadow
- [ ] examples.json: Has 3-6 real-world examples
- [ ] examples.json: Has commonPatterns section
- [ ] full.json: Is exact copy of original schema

## Testing Strategy

After each batch:

```bash
# Build TypeScript
npm run build

# Test schema loading
node -e "
const { SchemaLoader } = require('./dist/services/schema-loader.js');
const loader = new SchemaLoader('./schemas');

// Test progressive loading
loader.getBlockSchema('tpgb/tp-heading', ['meta']).then(meta => {
  console.log('Meta size:', JSON.stringify(meta).length, 'bytes');
});

loader.getBlockSchema('tpgb/tp-heading', ['meta', 'core']).then(core => {
  console.log('Core size:', JSON.stringify(core).length, 'bytes');
});

loader.getBlockSchema('tpgb/tp-heading', ['full'], true).then(full => {
  console.log('Full with $ref:', JSON.stringify(full).length, 'bytes');
});
"
```

## Expected Results

After completion:

- **420 files** in `schemas/blocks/` directory
- **99.7% duplication** eliminated from inline definitions
- **73% context reduction** for simple operations
- **Backward compatibility** maintained (full.json + legacy paths)

## Maintenance

### Adding New Blocks

1. Create full schema in `schemas/tp-new-block.json`
2. Add to `_meta/catalog.json`
3. Run generator script for that block
4. Manually review and enhance

### Updating Existing Blocks

1. Update `full.json` in block directory
2. Regenerate other 4 files using script
3. Manual review of changes

## Performance Targets

- **Catalog load**: < 100ms
- **Meta load**: < 10ms per block
- **Core load**: < 20ms per block
- **Full load with $ref**: < 50ms per block
- **Definition cache**: < 50ms initial load

## Next Steps

1. Decide: Manual creation vs. automated generation
2. If automated: Implement generator script
3. Run batches and test progressively
4. Update CLAUDE.md when complete
5. Consider generating API documentation

## Resources

- tp-heading example: `schemas/blocks/tp-heading/`
- Definitions: `schemas/_definitions/`
- Catalog: `schemas/_meta/catalog.json`
- Schema loader: `src/services/schema-loader.ts`
- MCP resources: `src/resources/index.ts`
