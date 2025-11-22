You are an expert in WordPress, Gutenberg blocks, Nexter Blocks (free + Pro), PHP/JS plugin internals, schema design, and tool-building with MCP servers and AI agents.

## Background and current state

I already have an AI-powered CLI that talks to a custom MCP Server. This MCP Server is deployed on my Ubuntu server as a Dokploy-managed docker-compose app. Dokploy handles:

- Creating an isolated app network and attaching all services to it.
- Wiring Traefik to that network for routing, with labels/domains configured in Dokploy.
- Generating a .env file from environment variables defined via the Dokploy UI.
- Optionally randomizing resource names (services, volumes, networks, etc.) to avoid conflicts when "Randomize Compose" is enabled.

The MCP Server sits between the AI-powered CLI and my WordPress site.

My WordPress site uses Nexter Blocks, including Nexter Pro, in the Gutenberg editor. The MCP Server is designed with these goals and constraints:

- It can generate new blog posts and pages, and modify existing posts/pages, with a primary focus on content built using Nexter Blocks in Gutenberg.
- All content creation and updates are saved as drafts by default (no automatic publishing) so I can review them first.
- Edits integrate cleanly with WordPress revisions, so I can revert changes when needed.
- When editing existing content, the system is designed to be non-destructive:
  - Preserve existing layout and block structure.
  - Avoid corrupting Nexter blocks or breaking layouts.
  - Make targeted adjustments (e.g., change text, tweak attributes, add/remove specific blocks) instead of rebuilding entire sections.
- For images, the system uses Nexter Image Block placeholders so that I can later upload and manage actual media through the WordPress media library.

---

## Schema System: Current State (Phase 1 - Complete ✅)

### What We Have

**84 comprehensive block schemas** covering:
- **79 unique blocks** with block.json files (from Free + Pro plugins)
- **17 child blocks** (15 form fields + 2 repeater blocks)
- **5 PHP-only blocks** (essential layout blocks: tp-container, tp-row, tp-column, tp-container-inner, tp-countdown)

**Schema Coverage:**
- ✅ All blocks with block.json files have schemas
- ✅ All form field child blocks documented
- ✅ All repeater child blocks documented
- ✅ Essential PHP-only blocks included
- ✅ No legacy/deprecated blocks

**Current Schema Location:**
```
schemas/
├── index.json (empty placeholder)
├── categories.json (empty placeholder)
├── use-cases.json (empty placeholder)
├── tp-heading.json
├── tp-container.json
├── nxt-name-field.json
└── ... (81 more block schemas)
```

### The Problem with Current Schemas

**1. Too Large for AI Context**
- Average schema size: ~9KB
- Largest schemas: tp-container (34KB), tp-timeline (23KB)
- Total: 767KB for all 84 schemas
- AI loads full schema even for simple operations

**2. Massive Duplication**
- Typography object appears **292 times** across schemas
- Background, border, shadow objects repeated in every styled block
- Same complex structures copy-pasted everywhere
- Maintenance nightmare (change one thing → update 292 places)

**3. No Progressive Loading**
- AI can't "browse" blocks without loading full schemas
- Can't query by category or use case
- All-or-nothing approach

---

## Schema System: Staged Architecture (Phase 2 - Planned)

### The Solution: Intelligent Staged Schemas

**Core Principles:**
1. **Progressive disclosure** - Load only what you need, when you need it
2. **DRY (Don't Repeat Yourself)** - Extract common definitions once
3. **Smart references** - Use `$ref` to link to shared definitions
4. **Staged complexity** - From lightweight meta to full schemas

### New Schema Structure

```
schemas/
├── _meta/
│   └── catalog.json                    # 📋 All 84 blocks index + categories (~20KB)
│
├── _definitions/
│   ├── typography.json                 # 📝 Complete typography structure
│   ├── background.json                 # 🎨 Complete background structure
│   ├── border.json                     # 📐 Complete border structure
│   ├── shadow.json                     # 🌑 Complete shadow structure
│   ├── animation.json                  # ✨ Complete animation structure
│   ├── positioning.json                # 📍 Complete positioning structure
│   ├── responsive.json                 # 📱 Responsive patterns
│   └── common-objects.json             # 🔧 Other shared objects
│
└── blocks/
    ├── tp-heading/
    │   ├── meta.json                   # Stage 1: Quick reference (~200 bytes)
    │   ├── core.json                   # Stage 2: Essential attrs (~2KB)
    │   ├── styling.json                # Stage 3: Advanced styling (~3KB)
    │   ├── full.json                   # Stage 4: Complete schema (~9KB)
    │   └── examples.json               # Stage 5: Usage patterns (~1KB)
    │
    ├── tp-container/
    │   ├── meta.json
    │   ├── core.json
    │   ├── layout.json                 # Container-specific: layout options
    │   ├── effects.json                # Container-specific: animations/effects
    │   ├── styling.json
    │   ├── full.json
    │   └── examples.json
    │
    ├── nxt-name-field/
    │   ├── meta.json
    │   ├── core.json
    │   ├── validation.json             # Form-specific: validation rules
    │   ├── styling.json
    │   ├── full.json
    │   └── examples.json
    │
    └── ... (81 more block folders)
```

---

## Schema Stages Explained

### Stage 1: meta.json (~200 bytes)
**When to use:** Browsing blocks, quick lookups, category queries

```json
{
  "blockName": "tpgb/tp-heading",
  "title": "Heading",
  "category": "content",
  "source": "pro",
  "description": "Display customizable headings with typography controls",
  "icon": "heading",
  "keywords": ["title", "h1", "h2", "typography"],
  "complexity": "simple",
  "hasInnerBlocks": false,
  "parentRequired": false,
  "stages": ["meta", "core", "styling", "full", "examples"]
}
```

### Stage 2: core.json (~2KB)
**When to use:** Creating basic blocks, common edits

```json
{
  "attributes": {
    "block_id": {"type": "string", "required": true},
    "title": {"type": "string", "source": "html", "default": "Heading"},
    "titleTag": {"type": "string", "default": "h2", "enum": ["h1","h2","h3","h4","h5","h6"]},
    "alignment": {"$ref": "definitions://responsive.alignment"}
  },
  "editingGuidelines": {
    "safeToModify": ["title", "titleTag", "alignment"],
    "dangerous": ["block_id"]
  }
}
```

### Stage 3: styling.json (~3KB)
**When to use:** Applying styles, advanced customization

```json
{
  "attributes": {
    "titleTypo": {"$ref": "definitions://typography"},
    "titleColor": {"type": "string"},
    "hoverColor": {"type": "string"},
    "background": {"$ref": "definitions://background"},
    "border": {"$ref": "definitions://border"},
    "shadow": {"$ref": "definitions://shadow"}
  }
}
```

### Stage 4: full.json (Current schema)
**When to use:** Complete reference, complex scenarios, edge cases

Contains everything - backwards compatible with current schemas.

### Stage 5: examples.json (~1KB)
**When to use:** AI needs inspiration or patterns

```json
{
  "examples": [
    {
      "name": "Hero Heading",
      "description": "Large centered heading for hero sections",
      "useCase": "hero-section",
      "attributes": {
        "title": "Welcome to Our Site",
        "titleTag": "h1",
        "alignment": {"md": "center"},
        "titleTypo": {
          "openTypography": 1,
          "fontSize": {"md": "48", "sm": "36", "xs": "28", "unit": "px"},
          "fontWeight": "700"
        }
      }
    },
    {
      "name": "Section Title",
      "description": "Standard section heading",
      "useCase": "section-heading",
      "attributes": {
        "title": "About Us",
        "titleTag": "h2",
        "alignment": {"md": "left"}
      }
    }
  ],
  "commonPatterns": [
    {"pattern": "centered-hero", "attrs": {"alignment": {"md": "center"}, "titleTag": "h1"}},
    {"pattern": "section-title", "attrs": {"titleTag": "h2"}}
  ]
}
```

---

## Common Definitions (_definitions/)

### definitions://typography

**Full structure with all possible properties:**

```json
{
  "$id": "typography",
  "description": "Complete typography object for text styling",
  "triggerKeywords": ["font", "typography", "text style", "font size", "line height"],

  "structure": {
    "openTypography": {
      "type": "number",
      "default": 0,
      "description": "Enable typography (0=disabled, 1=enabled)"
    },
    "fontFamily": {
      "type": "string",
      "default": "",
      "description": "Font family name (e.g., 'Roboto', 'Arial')"
    },
    "fontWeight": {
      "type": "string",
      "default": "400",
      "enum": ["100", "200", "300", "400", "500", "600", "700", "800", "900"],
      "description": "Font weight (100=thin, 400=normal, 700=bold, 900=black)"
    },
    "fontSize": {
      "$ref": "responsive",
      "description": "Font size (responsive: md, sm, xs)"
    },
    "lineHeight": {
      "$ref": "responsive",
      "description": "Line height (responsive: md, sm, xs)"
    },
    "letterSpacing": {
      "$ref": "responsive",
      "description": "Letter spacing (responsive: md, sm, xs)"
    },
    "textTransform": {
      "type": "string",
      "default": "none",
      "enum": ["none", "uppercase", "lowercase", "capitalize"],
      "description": "Text transformation"
    },
    "fontStyle": {
      "type": "string",
      "default": "normal",
      "enum": ["normal", "italic"],
      "description": "Font style"
    },
    "textDecoration": {
      "type": "string",
      "default": "none",
      "enum": ["none", "underline", "line-through"],
      "description": "Text decoration"
    }
  },

  "examples": [
    {
      "description": "16px Roboto normal",
      "value": {
        "openTypography": 1,
        "fontFamily": "Roboto",
        "fontSize": {"md": "16", "unit": "px"}
      }
    },
    {
      "description": "48px bold hero heading",
      "value": {
        "openTypography": 1,
        "fontSize": {"md": "48", "sm": "36", "xs": "28", "unit": "px"},
        "fontWeight": "700",
        "lineHeight": {"md": "1.2", "unit": "em"}
      }
    }
  ]
}
```

### definitions://background

```json
{
  "$id": "background",
  "description": "Complete background object with color, gradient, image, video support",
  "triggerKeywords": ["background", "bg color", "gradient", "background image"],

  "structure": {
    "openBg": {"type": "number", "default": 0},
    "bgType": {"type": "string", "enum": ["color", "gradient", "image", "video"]},

    "bgDefaultColor": {"type": "string", "description": "Solid background color (when bgType=color)"},

    "bgGradient": {
      "gradientType": {"type": "string", "enum": ["linear", "radial"]},
      "gradientAngle": {"type": "number", "default": 90, "description": "0-360 degrees"},
      "gradientPosition": {"type": "string", "default": "center center"},
      "gradientColor": {
        "type": "array",
        "items": {
          "color": {"type": "string"},
          "position": {"type": "number", "description": "0-100 percentage"}
        }
      }
    },

    "bgImage": {
      "url": {"type": "string"},
      "Id": {"type": "string"},
      "position": {"type": "string", "default": "center center"},
      "attachment": {"type": "string", "enum": ["scroll", "fixed", "local"]},
      "repeat": {"type": "string", "enum": ["no-repeat", "repeat", "repeat-x", "repeat-y"]},
      "size": {"type": "string", "enum": ["auto", "cover", "contain"]}
    },

    "overlayBg": {"type": "number", "default": 0},
    "overlayBgColor": {"type": "string", "description": "Overlay color (rgba)"}
  },

  "examples": [
    {
      "description": "Simple color background",
      "value": {"openBg": 1, "bgType": "color", "bgDefaultColor": "#667eea"}
    },
    {
      "description": "Linear gradient purple to blue",
      "value": {
        "openBg": 1,
        "bgType": "gradient",
        "bgGradient": {
          "gradientType": "linear",
          "gradientAngle": 135,
          "gradientColor": [
            {"color": "#667eea", "position": 0},
            {"color": "#764ba2", "position": 100}
          ]
        }
      }
    }
  ]
}
```

### definitions://responsive

```json
{
  "$id": "responsive",
  "description": "Standard responsive value structure for breakpoints",

  "structure": {
    "md": {"type": "string", "description": "Desktop value (default breakpoint)"},
    "sm": {"type": "string", "description": "Tablet value"},
    "xs": {"type": "string", "description": "Mobile value"},
    "unit": {"type": "string", "enum": ["px", "em", "rem", "%", "vh", "vw"], "default": "px"}
  }
}
```

---

## Catalog Structure (_meta/catalog.json)

```json
{
  "version": "1.0.0",
  "totalBlocks": 84,
  "lastUpdated": "2025-11-21",
  "schemaVersion": "2.0.0",

  "blocks": [
    {
      "name": "tpgb/tp-heading",
      "title": "Heading",
      "category": "content",
      "source": "pro",
      "complexity": "simple",
      "keywords": ["title", "h1", "typography"],
      "path": "blocks/tp-heading",
      "stages": ["meta", "core", "styling", "full", "examples"]
    },
    {
      "name": "tpgb/tp-container",
      "title": "Container",
      "category": "layout",
      "source": "pro",
      "complexity": "advanced",
      "keywords": ["section", "wrapper", "layout"],
      "path": "blocks/tp-container",
      "stages": ["meta", "core", "layout", "effects", "styling", "full", "examples"]
    }
    // ... all 84 blocks
  ],

  "categories": {
    "content": {
      "description": "Text, headings, paragraphs, and content display blocks",
      "blocks": ["tpgb/tp-heading", "tpgb/tp-pro-paragraph", "tpgb/tp-heading-title", ...]
    },
    "layout": {
      "description": "Containers, rows, columns, and structural blocks",
      "blocks": ["tpgb/tp-container", "tpgb/tp-row", "tpgb/tp-column", ...]
    },
    "forms": {
      "description": "Form elements, inputs, and form-related blocks",
      "blocks": ["tpgb/tp-form-block", "tpgb/tp-form-name-field", "tpgb/tp-form-email-field", ...]
    },
    "interactive": {
      "description": "Tabs, accordions, toggles, and interactive elements",
      "blocks": ["tpgb/tp-accordion", "tpgb/tp-tabs-tours", "tpgb/tp-switcher", ...]
    },
    "media": {
      "description": "Images, videos, galleries, and media blocks",
      "blocks": ["tpgb/tp-image", "tpgb/tp-video", "tpgb/tp-creative-image", ...]
    },
    "marketing": {
      "description": "CTA, testimonials, pricing, and conversion blocks",
      "blocks": ["tpgb/tp-pricing-table", "tpgb/tp-testimonials", "tpgb/tp-cta-banner", ...]
    },
    "dynamic": {
      "description": "ACF repeaters, dynamic content, and data-driven blocks",
      "blocks": ["tpgb/tp-repeater-block", "tpgb/tp-dynamic-heading", ...]
    },
    "effects": {
      "description": "Animations, effects, and visual enhancements",
      "blocks": ["tpgb/tp-mouse-cursor", "tpgb/tp-scroll-navigation", "tpgb/tp-smooth-scroll", ...]
    }
  },

  "complexity": {
    "simple": ["tpgb/tp-heading", "tpgb/tp-pro-paragraph", "tpgb/tp-button-core", ...],
    "medium": ["tpgb/tp-accordion", "tpgb/tp-tabs-tours", "tpgb/tp-pricing-table", ...],
    "advanced": ["tpgb/tp-container", "tpgb/tp-timeline", "tpgb/tp-dynamic-device", ...],
    "expert": ["tpgb/tp-repeater-block", "tpgb/tp-form-block", ...]
  }
}
```

---

## Implementation Plan (Phase 2)

### Phase 2.1: Extract Common Definitions ⏳

**Goal:** Create `_definitions/` folder with all shared object structures

**Tasks:**
1. ✅ Analyze all 84 current schemas
2. ✅ Identify repeated patterns (typography, background, border, shadow, etc.)
3. ✅ Extract full structures from plugin block.json files
4. ✅ Create individual definition files:
   - `typography.json` (from 292 occurrences)
   - `background.json`
   - `border.json`
   - `shadow.json`
   - `animation.json`
   - `positioning.json`
   - `responsive.json`
   - `common-objects.json`
5. ✅ Document with examples and trigger keywords

**Deliverables:**
- `schemas/_definitions/` folder
- 8 comprehensive definition files
- Total size: ~40KB (vs 292 duplicates = ~2.3MB savings)

---

### Phase 2.2: Create Catalog & Metadata ⏳

**Goal:** Create `_meta/catalog.json` with all blocks organized

**Tasks:**
1. ✅ Generate catalog.json with all 84 blocks
2. ✅ Categorize blocks (content, layout, forms, interactive, media, marketing, dynamic, effects)
3. ✅ Add complexity levels (simple, medium, advanced, expert)
4. ✅ Add keywords for searchability
5. ✅ Map block relationships (parent/child, dependencies)

**Deliverables:**
- `schemas/_meta/catalog.json` (~20KB)

---

### Phase 2.3: Generate Staged Schemas ⏳

**Goal:** Create staged schemas in `schemas-temp/blocks/` for all 84 blocks

**For each of the 84 blocks:**

1. ✅ Create block folder: `blocks/{block-name}/`
2. ✅ Generate `meta.json` (~200 bytes)
   - Extract from current schema: blockName, title, category, description, etc.
3. ✅ Generate `core.json` (~2KB)
   - Extract 10-15 most essential attributes
   - Replace complex objects with `$ref` to definitions
4. ✅ Generate `styling.json` (~3KB)
   - Extract all styling-related attributes
   - Use `$ref` for typography, background, border, shadow
5. ✅ Generate block-specific files (if needed)
   - Containers: `layout.json`, `effects.json`
   - Forms: `validation.json`
   - Dynamic blocks: `dynamic.json`
6. ✅ Keep `full.json` (current schema, backwards compatible)
7. ✅ Generate `examples.json` (~1KB)
   - Create 3-5 real-world examples
   - Add common patterns

**Deliverables:**
- `schemas-temp/blocks/` with 84 block folders
- 420+ individual stage files (84 × 5 avg stages)
- Total size: ~450KB (vs current 767KB = 41% reduction)

---

### Phase 2.4: Update Schema Loader ⏳

**Goal:** Update `src/services/schema-loader.ts` to support staged loading

**Tasks:**
1. ✅ Add `$ref` resolution engine
   - Parse `definitions://typename` references
   - Load from `_definitions/` folder
   - Deep merge with parent object
2. ✅ Add progressive loading methods:
   - `getCatalog()` - Load catalog
   - `getBlockMeta(blockName)` - Load stage 1
   - `getBlockCore(blockName)` - Load stage 2 + resolve refs
   - `getBlockStyling(blockName)` - Load stage 3 + resolve refs
   - `getBlockFull(blockName)` - Load stage 4 (current behavior)
   - `getBlockExamples(blockName)` - Load stage 5
3. ✅ Add caching for definitions
4. ✅ Update MCP resources to expose stages
5. ✅ Backwards compatibility: `getSchemas()` still works (loads full.json)

**Deliverables:**
- Updated `schema-loader.ts`
- New methods for progressive loading
- $ref resolution engine

---

### Phase 2.5: Update MCP Tools ⏳

**Goal:** Update tool descriptions to use new staged loading

**Tasks:**
1. ✅ Update `create_block` tool:
   - Load `core.json` for basic creation
   - Include definition examples in description
2. ✅ Update `modify_block` tool:
   - Load appropriate stage based on modification type
3. ✅ Add `browse_blocks` tool:
   - Use catalog for discovery
4. ✅ Update MCP resources:
   - `nexter://schemas/catalog` - Browse all blocks
   - `nexter://schemas/block/{name}/meta` - Quick reference
   - `nexter://schemas/block/{name}/core` - Essential
   - `nexter://schemas/block/{name}/full` - Complete

**Deliverables:**
- Updated tool descriptions
- New browsing capabilities
- Better AI context efficiency

---

### Phase 2.6: Testing & Validation ⏳

**Goal:** Ensure everything works correctly

**Tasks:**
1. ✅ Test $ref resolution
2. ✅ Test progressive loading
3. ✅ Test backwards compatibility (getSchemas still works)
4. ✅ Validate all 84 blocks load correctly
5. ✅ Test MCP tools with new schemas
6. ✅ Measure context token reduction
7. ✅ Fix any issues

**Deliverables:**
- Validated system
- Performance metrics
- Bug fixes

---

### Phase 2.7: Migration & Cleanup ⏳

**Goal:** Replace old schemas with new structure

**Tasks:**
1. ✅ Backup current `schemas/` folder
2. ✅ Delete old `schemas/` folder
3. ✅ Rename `schemas-temp/` → `schemas/`
4. ✅ Update documentation
5. ✅ Commit and push to git

**Deliverables:**
- New schema structure in production
- Updated documentation
- Git history preserved

---

## Expected Benefits

### Performance Improvements

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| **Initial catalog load** | N/A | 20KB | New capability |
| **Creating simple block** | 9KB | 2.2KB | **75% reduction** |
| **Creating styled block** | 9KB | 5.2KB | **42% reduction** |
| **Full reference** | 9KB | 9KB | Same |
| **Browsing 10 blocks** | 90KB | 2KB | **97% reduction** |
| **Total duplication** | 292 typo defs | 1 typo def | **99.7% reduction** |
| **Context tokens (simple)** | ~3000 | ~800 | **73% reduction** |
| **Total schema size** | 767KB | 450KB | **41% reduction** |

### Developer Experience

- ✅ **Easier maintenance** - Update typography once, not 292 times
- ✅ **Better discovery** - Browse blocks by category, complexity, keywords
- ✅ **Faster AI** - Load only what's needed
- ✅ **Clear examples** - Real-world usage patterns
- ✅ **Backwards compatible** - Old code still works

### AI Experience

- ✅ **73% less context** for common operations
- ✅ **Progressive disclosure** - Learn incrementally
- ✅ **Smart references** - Understands complex objects deeply
- ✅ **Example-driven** - See patterns, not just specs
- ✅ **Faster responses** - Less data to process

---

## How AI Should Use New Schemas

### Scenario 1: Browsing Blocks

```typescript
// AI loads catalog (20KB, one time)
const catalog = await loadResource('nexter://schemas/catalog');

// AI can now:
// - Browse all 84 blocks
// - Filter by category ("show me all form blocks")
// - Filter by complexity ("show me simple blocks")
// - Search by keyword ("blocks with typography")
```

### Scenario 2: Creating Simple Block

```typescript
// Load only core attributes (2KB)
const core = await loadBlockStage('tpgb/tp-heading', 'core');

// AI creates basic heading
createBlock({
  blockName: 'tpgb/tp-heading',
  attrs: {
    block_id: 'a1b2',
    title: 'Welcome',
    titleTag: 'h1',
    alignment: {md: 'center'}
  }
});
```

### Scenario 3: Applying Advanced Styling

```typescript
// Load styling stage (3KB, includes $ref to definitions)
const styling = await loadBlockStage('tpgb/tp-heading', 'styling');

// AI sees: titleTypo: {"$ref": "definitions://typography"}
// AI loads definition (5KB, cached)
const typoDef = await loadDefinition('typography');

// AI now knows full typography structure
modifyBlock('a1b2', {
  titleTypo: {
    openTypography: 1,
    fontSize: {md: '48', sm: '36', xs: '28', unit: 'px'},
    fontWeight: '700'
  }
});
```

### Scenario 4: Learning from Examples

```typescript
// Load examples for inspiration
const examples = await loadBlockStage('tpgb/tp-heading', 'examples');

// AI sees:
// - "Hero Heading" example with 48px bold centered
// - "Section Title" example with h2 left-aligned
// - Common patterns for quick use

// AI can copy/adapt patterns
```

---

## Reference Resolution ($ref)

### How $ref Works

```json
// In core.json
{
  "alignment": {"$ref": "definitions://responsive.alignment"}
}

// Resolves to (from _definitions/responsive.json):
{
  "alignment": {
    "type": "string",
    "enum": ["left", "center", "right"],
    "responsive": true,
    "structure": {
      "md": "string - Desktop value",
      "sm": "string - Tablet value",
      "xs": "string - Mobile value"
    }
  }
}
```

### Reference Formats

- `definitions://typename` - Load from `_definitions/typename.json`
- `definitions://file.property` - Load property from definition file
- Schema loader automatically resolves and caches

---

## Schema Authoring Guidelines

### For Future Schema Updates

When updating or adding new blocks:

1. **Start with plugin block.json** - Source of truth
2. **Extract to appropriate stage:**
   - Essential attributes → `core.json`
   - Styling attributes → `styling.json`
   - Complex objects → Use `$ref` to definitions
3. **Use existing definitions** - Don't create duplicates
4. **Add examples** - Real-world usage in `examples.json`
5. **Update catalog** - Add to `_meta/catalog.json`
6. **Test $ref resolution** - Ensure references work

### Creating New Common Definitions

If you find a pattern repeated 3+ times:
1. Extract to `_definitions/new-pattern.json`
2. Document with full structure, description, examples
3. Replace in block schemas with `{"$ref": "definitions://new-pattern"}`
4. Update this documentation

---

## Current Status

### ✅ Phase 1: Complete
- 84 comprehensive block schemas generated
- All blocks with block.json files covered
- All child blocks documented
- PHP-only essential blocks included

### ✅ Phase 2: Complete
- ✅ Phase 2.1: Common definitions extraction (8 definition files)
- ✅ Phase 2.2: Catalog creation (84 blocks organized)
- ✅ Phase 2.3: Staged schema generation (83 blocks × 5 files = 415 files)
- ✅ Phase 2.4: Schema loader updates ($ref resolution, progressive loading)
- ✅ Phase 2.5: MCP resources update (progressive loading API)

**Phase 2 Achievement:**
- Automated generation using `scripts/generate-staged-schema.ts`
- 73% context reduction for simple operations (achieved via progressive loading)
- 99.7% duplication eliminated (292 typography instances → 1 definition)
- Backward compatible (full.json maintains complete schemas)
- Total size: 415 staged files vs 84 flat files
- Time saved: 21-28 hours via automation

### ⏳ Phase 3: Manual Enhancement Pass (In Progress)

**Goal:** Transform auto-generated basic schemas into production-ready, pixel-perfect documentation

**Why This Is Critical:**
The automated generator (Phase 2.3) created structurally correct but basic schemas. For **pixel-perfect layouts, complex blocks, and targeted modifications** without breaking anything, AI needs:

1. **Rich, Real-World Examples** - Not just basic attributes
2. **Visual Outcome Descriptions** - What settings actually look like
3. **Validation Rules & Warnings** - Prevent breaking configurations
4. **Complex Pattern Documentation** - Multi-step setups
5. **Relationship Context** - How blocks interact with each other

**The Problem with Current Auto-Generated Schemas:**
```json
// Current auto-generated example (too basic)
{
  "name": "Basic Accordion",
  "attributes": {
    "block_id": "a1b2",
    "title": "Sample Accordion"
  }
}
```

**What We Need for Production:**
```json
// Production-ready example (comprehensive)
{
  "name": "FAQ Section - Purple Theme with Icons",
  "description": "Multi-item accordion with FontAwesome icons, custom gradient, hover effects, How-To schema",
  "visualDescription": "Purple gradient background, white text, chevron-down icons that rotate on expand, smooth 300ms transitions",
  "complexity": "medium",
  "attributes": {
    "block_id": "abc123",
    "accordionList": [
      {
        "title": "What is your return policy?",
        "content": "<p>We offer 30-day returns...</p>",
        "defaultOpen": false
      },
      {
        "title": "How long does shipping take?",
        "content": "<p>Standard shipping: 5-7 business days...</p>",
        "defaultOpen": true
      }
    ],
    "iconLibrary": "fontAwesome",
    "iconName": "fa-chevron-down",
    "titleTypo": {
      "openTypography": 1,
      "fontSize": {"md": "18", "sm": "16", "unit": "px"},
      "fontWeight": "600",
      "textTransform": "uppercase"
    },
    "titleBgGradient": {
      "openBg": 1,
      "bgType": "gradient",
      "bgGradient": {
        "gradientType": "linear",
        "gradientAngle": 135,
        "gradientColor": [
          {"color": "#667eea", "position": 0},
          {"color": "#764ba2", "position": 100}
        ]
      }
    },
    "iconRotate": 1,
    "transition": {"md": "300", "unit": "ms"}
  },
  "useCase": "FAQ pages, product documentation, help centers, feature lists",
  "warnings": [
    "Don't set multiple items with defaultOpen:true in accordion mode (only first will open)",
    "iconName requires iconLibrary to be set first",
    "Max 50 accordion items recommended for performance",
    "Gradient backgrounds require openBg: 1 and bgType: 'gradient'"
  ],
  "validationRules": {
    "accordionList": "Required, minimum 1 item",
    "iconName": "Requires iconLibrary to be set",
    "titleTypo.fontSize.md": "Recommended 14-24px for readability"
  },
  "relatedPatterns": [
    "With search/filter functionality",
    "Nested accordions (advanced)",
    "With custom toggle icons",
    "Integrated with How-To schema for SEO"
  ],
  "commonMistakes": [
    "Forgetting to set openTypography: 1 before setting typography values",
    "Using px units for responsive designs (use rem/em instead)",
    "Not testing on mobile breakpoints (xs)"
  ]
}
```

---

### Phase 3: Enhancement Strategy

**Priority Batches (15-20 hours total):**

#### Batch 1: Critical Layout Blocks (3-4 hours) - **HIGHEST PRIORITY**
Without perfect layout blocks, everything breaks:
- `tp-container` - The foundation of all layouts
- `tp-row` - Row structure and responsive behavior
- `tp-column` - Column widths, gaps, responsive stacking
- `tp-container-inner` - Inner container context

**Enhanced Documentation For Each:**
- 10+ examples: Simple container, full-width hero, card grid, sidebar layout, etc.
- Responsive patterns: Mobile stacking, tablet adjustments, desktop expansion
- Z-index and stacking context rules
- Flex/grid interactions
- Visual descriptions: "Full-width hero with centered content, purple gradient, 100vh height"
- Common mistakes: Nested containers, missing column widths, broken responsive

#### Batch 2: Core Content Blocks (2-3 hours)
Most frequently used in every post/page:
- `tp-heading` - Already done manually ✅
- `tp-pro-paragraph` - Text styling, drop caps, columns
- `tp-button` / `tp-button-core` - CTA buttons with all states
- `tp-image` - Images with effects, lightbox, lazy load

#### Batch 3: Interactive Blocks (3-4 hours)
Complex state management:
- `tp-accordion` / `tp-accordion-inner` - Parent-child relationship critical
- `tp-tabs-tours` / `tp-tab-item` - Tab structure and behavior
- `tp-switcher` / `tp-switch-inner` - Toggle content patterns
- `tp-expand` - Read more/less functionality

#### Batch 4: Form Blocks (3-4 hours)
Complex validation and parent-child relationships:
- `tp-form-block` - Parent form container
- `nxt-name-field`, `nxt-email-field`, `nxt-phone-field` - Common fields
- `nxt-submit-button` - Form submission
- Field validation patterns and error states

#### Batch 5: Marketing & Media Blocks (3-4 hours)
High-impact visual blocks:
- `tp-pricing-table` - Pricing plans with features
- `tp-testimonials` - Customer reviews
- `tp-carousel` / `tp-anything-slide` - Image/content carousels
- `tp-video` - Video embeds with controls

#### Batch 6: Advanced Blocks (1-2 hours) - **AS NEEDED**
For power users:
- `tp-repeater-block` - ACF repeater integration
- `tp-timeline` / `tp-timeline-inner` - Timeline layouts
- `tp-google-map` - Map embeds with markers

---

### Phase 3: Per-Block Enhancement Checklist

**CRITICAL: Always Reference Plugin Source Files**

Before enhancing any block schema, you MUST:

1. **Locate the plugin source file:**
   - Blocks with `block.json`: `./the-plus-addons-for-block-editor-pro/classes/blocks/{block-name}/block.json`
   - PHP-only blocks (tp-container, tp-row, tp-column, tp-container-inner, tp-countdown): `./the-plus-addons-for-block-editor-pro/classes/blocks/{block-name}/index.php`

2. **Read the source file completely:**
   - For `block.json`: All attributes with types, defaults, enums
   - For PHP blocks: All `$attributes` variable extractions (lines like `$height = (!empty($attributes['height'])) ? $attributes['height'] : '';`)
   - Understand conditional logic, dependencies, default values

3. **Cross-reference with generated schema:**
   - Verify auto-generated `full.json` didn't miss attributes
   - Check for typos or incorrect defaults
   - Identify Pro-only attributes

4. **Use plugin source as ground truth:**
   - Plugin source = 100% accurate
   - Generated schemas = good structure, may have gaps
   - Your enhanced examples must match plugin behavior exactly

**Why This Is Critical:**
- Auto-generated schemas might miss attributes or have incorrect defaults
- Plugin source shows actual implementation behavior
- Conditional logic reveals dependencies (e.g., "if X then Y required")
- PHP code shows responsive cascade behavior (md → sm → xs fallback)

**Plugin File Locations:**
```
./the-plus-addons-for-block-editor-pro/classes/blocks/
├── tp-container/index.php (PHP-only, no block.json)
├── tp-row/index.php (PHP-only, no block.json)
├── tp-column/index.php (PHP-only, no block.json)
├── tp-container-inner/index.php (PHP-only, no block.json)
├── tp-heading-animation/block.json (Has block.json)
├── tp-accordion/block.json (Has block.json)
└── ... (79 more blocks with block.json)
```

---

For each block, enhance `examples.json` with:

**1. Examples Section (5-10 examples per block):**
- ✅ Name, description, visual description
- ✅ Complete working attributes (not minimal)
- ✅ Complexity level (simple/medium/advanced/expert)
- ✅ Use case context
- ✅ Responsive breakpoint values (md/sm/xs)
- ✅ Related blocks/patterns

**2. Validation Rules:**
- Required attributes
- Conditional requirements ("if X then Y required")
- Value constraints (min/max, enums, regex)
- Performance limits ("max 50 items")

**3. Warnings & Common Mistakes:**
- Breaking configurations
- Common typos/errors
- Performance pitfalls
- Responsive design gotchas

**4. Visual Outcome Descriptions:**
- What settings look like rendered
- Animation/transition descriptions
- Color/gradient visual descriptions
- Layout behavior descriptions

**5. Pattern Documentation:**
- Common combinations
- Advanced multi-block patterns
- Integration patterns (ACF, How-To schema, etc.)
- Responsive strategies

**6. Relationship Context:**
- Parent-child requirements
- Sibling block interactions
- Container context rules
- Z-index stacking rules

---

### Phase 3: Quality Metrics

Each enhanced block must achieve:

- ✅ **10+ comprehensive examples** (vs 1 basic auto-generated)
- ✅ **Visual descriptions** for all visual attributes
- ✅ **Validation rules** for all required/conditional attributes
- ✅ **5+ warnings** about common mistakes
- ✅ **Responsive patterns** documented (md/sm/xs)
- ✅ **Real use cases** from actual websites
- ✅ **Complex patterns** beyond basic usage

**Target Outcome:**
AI can create pixel-perfect, complex blocks with all settings configured correctly on first try, without breaking layouts or requiring human fixes.

---

---

## Notes for AI Assistants

When working with Nexter blocks:

1. **Always preserve block_id** - Never modify or regenerate
2. **Use schemas progressively** - Start with meta/core, expand as needed
3. **Reference definitions** - Don't inline complex objects
4. **Follow examples** - Real-world patterns are your friend
5. **Stay in stage** - Don't load full.json unless necessary
6. **Cache definitions** - Load typography once, reuse everywhere

The goal is **pixel-perfect layouts without breaking blocks** - these staged schemas give you the knowledge to do that efficiently.
