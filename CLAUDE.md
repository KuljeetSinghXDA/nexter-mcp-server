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

### Phase 3: Progress Tracker

**Approach:** Focused enhancement strategy (6-8 examples per block) for efficient coverage

#### ✅ Batch 1: Critical Layout Blocks (COMPLETE)
**Status:** 4 blocks, 43 examples, ~4,341 lines
**Commits:** 4 separate commits (bb0399a9, 3cb8b0c8, ef916219, + tp-container)

- ✅ `tp-container` - 10 examples (~770 lines) - Foundation layouts, hero sections, card grids
- ✅ `tp-row` - 12 examples (~1,072 lines) - Row structures, responsive stacking, flex layouts
- ✅ `tp-column` - 11 examples (~948 lines) - Column widths, gaps, responsive breakpoints
- ✅ `tp-testimonials` - 10 examples (~1,551 lines) - Customer reviews, carousel/grid layouts

**Achievement:** Critical layout foundation complete - AI can now create complex, responsive layouts without breaking structure

---

#### ✅ Batch 2: Core Content Blocks (COMPLETE)
**Status:** 3 blocks, 24 examples, ~2,081 lines
**Commits:** 3 separate commits (381de401, bd8f8157, 56657522)

- ✅ `tp-pro-paragraph` - 8 examples (~488 lines) - Multi-column text, drop caps, link styling
- ✅ `tp-button` - 8 examples (~977 lines) - Gradient CTAs, outline buttons, FancyBox popups, 23 style variants
- ✅ `tp-image` - 8 examples (~616 lines) - Responsive images, filters, captions, object-fit, lazy loading

**Achievement:** Core content blocks complete - AI can create rich text content, interactive buttons, and optimized images with all features

---

#### ✅ Batch 3: Interactive Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,062 lines
**Commits:** 4 separate commits (08984e00, c671778b, fa3d2f42, 80cca9c9)

- ✅ `tp-accordion` / `tp-accordion-inner` - 8 examples (~1,169 lines) - FAQs, How-To schema, expand/collapse all, gradient styling
- ✅ `tp-tabs-tours` / `tp-tab-item` - 8 examples (~646 lines) - Horizontal/vertical tabs, icons, swiper effects, responsive accordion
- ✅ `tp-switcher` / `tp-switch-inner` - 8 examples (~575 lines) - Pricing toggles, 4 style variations, template mode, carousel integration
- ✅ `tp-expand` - 8 examples (~672 lines) - Read more/less, gradient overlays, extra buttons, template content, accessibility

**Achievement:** Interactive blocks complete - AI can create accordions, tabs, content switchers, and expandable sections with complex parent-child relationships, state management, and all styling options

---

#### ✅ Batch 4: Form Blocks (COMPLETE)
**Status:** 5 blocks, 40 examples, ~3,965 lines
**Commits:** 5 separate commits (ebf3eeaf, e182d3ef, 0d57b90c, f51b598c, 242b8aa7)

- ✅ `tp-form-block` - 8 examples (~912 lines) - Parent container with form actions (Email, Mailchimp, ConvertKit, GetResponse, Discord, Slack, Webhook), layout types, email placeholders
- ✅ `nxt-name-field` - 8 examples (~532 lines) - Full/first/last name fields, FontAwesome/image icons, character validation, autocomplete (name, given-name, family-name)
- ✅ `nxt-email-field` - 8 examples (~625 lines) - Email validation, FontAwesome/image icons (note: ButtonImage attribute), inline buttons, Mailchimp integration, [nxt_email] placeholder
- ✅ `nxt-phone-field` - 8 examples (~708 lines) - Country dropdown with ISO codes (US, GB, CA, AU, IN, DE, FR), regex patterns, international E.164 format, countryWidth sizing (80-120px)
- ✅ `nxt-submit-button` - 8 examples (~620 lines) - Icon positioning (-1 before, 1 after), inline mode (isInline=true), button sizing (px vs %), alignment (left/center/right), descriptive labels

**Achievement:** Form blocks complete - AI can create complete forms with email/Mailchimp/etc integration, field validation, autocomplete, country dropdowns, inline newsletter layouts, and accessibility-compliant submit buttons with custom icons

---

#### ✅ Batch 5: Marketing & Media Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~2,873 lines
**Commits:** 3 separate commits (c57d64df, 9136f16b, 9ca37464)

- ✅ `tp-pricing-table` - 8 examples (~679 lines) - Three-tiered pricing, discount badges, ribbons (3 styles), feature lists with icons/tooltips, read more toggle, WYSIWYG/stylish content modes, annual discounts
- ✅ `tp-anything-carousel` - 8 examples (~580 lines) - Parent carousel with slide/fade transitions, remote control connection (carouselId), equal height system, overflow control, random order, template mode
- ✅ `tp-anything-slide` - 8 examples (~370 lines) - Child slide container with key matching, content layouts (hero, testimonial, product, blog, team), parent-child coordination
- ✅ `tp-video` - 8 examples (~544 lines) - YouTube/Vimeo/self-hosted embeds, custom thumbnails, animated play icons (pulse/rotating/drop_waves), popup lightbox, schema markup (SEO), styled borders/shadows

**Achievement:** Marketing & Media blocks complete - AI can create pricing tables with advanced features, carousels with full navigation control, and video embeds with custom thumbnails, animations, and SEO optimization

---

#### ✅ Batch 6: Advanced Blocks (COMPLETE)
**Status:** 4 blocks, 24 examples, ~1,958 lines
**Commits:** 4 separate commits (4d337a5a, 4d54a2f0, f9a64434, 7b56c0a8)

- ✅ `tp-repeater-block` - 8 examples (~550 lines) - ACF repeater integration, manual/ACF grids, 2-4 column layouts, post context (specific/current/options), gap control, template mode
- ✅ `tp-timeline` - 8 examples (~775 lines) - Company history, product roadmaps, event agendas, process steps, career timelines, project phases, minimal/advanced layouts, RContent array approach
- ✅ `tp-timeline-inner` - 8 examples (~575 lines) - Child block alternative to RContent, parent-child coordination, index/repetKey, rich nested content, migration guide between approaches
- ✅ `tp-google-map` - 8 examples (~608 lines) - Single/multiple locations, lat/long vs address geocoding, custom pins, styled maps, satellite/terrain views, overlay content, API setup guide

**Achievement:** Advanced blocks complete - AI can create ACF-powered grids, complex timelines with alternate layouts, Google Maps with full control. Completes all priority block documentation.

---

#### ✅ Batch 7: Typography & Display Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,589 lines
**Commits:** 4 separate commits (from previous session)

- ✅ `tp-adv-typo` - 8 examples - Advanced typography with text effects, gradients, responsive control
- ✅ `tp-blockquote` - 8 examples - Quote blocks with author attribution, citation styles
- ✅ `tp-stylist-list` - 8 examples - Custom styled lists with icons, numbering, layouts
- ✅ `tp-icon-box` - 8 examples - Icon-based content boxes with multiple layouts

**Achievement:** Typography and display blocks complete - AI can create advanced text styling, quotes, custom lists, and icon-based content boxes

---

#### ✅ Batch 8: Common UI Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,482 lines
**Commits:** 4 separate commits (18574033, c345c3fc, 76ecbacb, ee2389d5)

- ✅ `tp-heading` - 8 examples (~601 lines) - Semantic hierarchy (H1-H6), text stroke, shadow, blend modes, clickable headings, absolute positioning
- ✅ `tp-infobox` - 8 examples (~504 lines) - 3 layout styles, 4 icon types, carousel mode, pin badges
- ✅ `tp-social-icons` - 8 examples (~1,462 lines) - All 16 style variations, complete social suite, advanced tooltips, custom image icons
- ✅ `tp-progress-bar` - 8 examples (~915 lines) - progressbar and piechart layouts, gradient fills, custom symbols, icon/image support

**Achievement:** Common UI blocks complete - AI can create semantic headings with SEO, info boxes with multiple layouts, social media icons with all styles, and progress indicators (bars and pie charts)

---

#### ✅ Batch 9: Visual Effects & Counters (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,406 lines
**Commits:** 4 separate commits (50aeefdf, e9f30449, 6763e27a, cf804341)

- ✅ `tp-countdown` - 8 examples (~768 lines) - 3 countdown modes (normal datetime, scarcity timer, fake counter), 3 visual styles (boxes, flip, circular progress), 4 expiry actions, Pro looping
- ✅ `tp-number-counter` - 8 examples (~836 lines) - Animated count-up with numValue/startValue/numGap/timeDelay controls, K/M/B notation, commas, linked counters, 2 styles (horizontal/vertical)
- ✅ `tp-flipbox` - 8 examples (~720 lines) - Interactive 3D flip cards with innerBlocks architecture, horizontal/vertical flip directions, front/back sides, CTA buttons, hover activation
- ✅ `tp-before-after` - 8 examples (~1,082 lines) - Before/after image comparison sliders, horizontal/vertical orientations, 3 separator positions, hover/drag modes, two-tone separators, custom icons, styled labels

**Achievement:** Visual effects and counters complete - AI can create countdown timers for sales/events, animated number counters for stats, interactive flip boxes for reveals, and before/after sliders for comparisons

---

#### ✅ Batch 10: Content Display Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,492 lines
**Commits:** 4 separate commits (e5d609ba, 9f7a360f, 393f89b1, f49e0d47)

- ✅ `tp-heading-title` - 8 examples (~910 lines) - 9 style variants with separators/decorations, gradient text (titleGrad/titleStroke), text truncation (char/word modes), animation split types (chars/words/lines), extraTitle badges, responsive typography
- ✅ `tp-team-listing` - 8 examples (~1,082 lines) - Grid/carousel layouts, TeamMemberR array with _key, 4 social link types (Mail/Fb/Ws/Cus), TCateg filtering, 2-8 member examples from executive teams to support staff
- ✅ `tp-cta-banner` - 8 examples (~720 lines) - 8 styleType variants with unique layouts, 4 hoverStyle effects (vertical/horizontal/zoom/fade), bannerImage with WordPress imageSize, Title/subTitle/desc with HTML support, hover color states
- ✅ `tp-messagebox` - 8 examples (~780 lines) - Success/warning/error/info alerts, icon prefix/suffix positioning, dismissible functionality, msgArrow pointer, gradient backgrounds, box shadows, normal/hover states for title and description

**Achievement:** Content display blocks complete - AI can create advanced headings with 9 style variants and gradient text, team member showcases with social links, promotional CTA banners with hover effects, and notification boxes with all alert types and interactive states

---

#### ✅ Batch 11: Interactive & Advanced Features (COMPLETE)
**Status:** 4 blocks, 32 examples, ~5,250 lines
**Commits:** 4 separate commits (6d943b37, a4f646c8, a5c78c52, bade51d4)

- ✅ `tp-hotspot` - 8 examples (~1,412 lines) - 3 pin types (icon/image/text), Tippy.js tooltips with 12 placements, 4 wave effects, responsive positioning (TabRespo/MobRespo), number position values, icon/image/text indicators
- ✅ `tp-popup-builder` - 8 examples (~1,140 lines) - 8 trigger types (click/page load/scroll/exit intent/inactivity/page views/previous URL/external click), 4 open styles (popup/push/slide-along/corner-box), frequency limiting, time-based popups, 3 hamburger icon styles
- ✅ `tp-process-steps` - 8 examples (~1,545 lines) - 2 layout styles (horizontal/vertical), 3 indicator types (icon/image/text), 2 counter styles (number_normal/dc_custom_text), 4 separator types, carousel synchronization, special background effects (concentric circles), responsive layouts
- ✅ `tp-progress-tracker` - 8 examples (~1,153 lines) - 3 progress types (horizontal/vertical/circular), pin points with 2 styles (labels/dots), percentage display (2 styles), custom selector tracking, RTL support, responsive offsets for positioning

**Achievement:** Interactive & Advanced Features complete - AI can create interactive image hotspots with tooltips, complex modal popups with multiple trigger types, process workflow steps with carousel integration, and scroll progress trackers with pin point navigation

---

#### ✅ Batch 12: Remaining Form Fields (COMPLETE)
**Status:** 4 blocks, 32 examples, ~1,853 lines
**Commits:** 4 separate commits (a813de5f, 30042cdd, 7a23b168, b83a0940)

- ✅ `nxt-hidden-field` - 8 examples (~439 lines) - Form tracking with UTM parameters, referral codes, page context, timestamps, A/B test variants, user authentication, landing page attribution, 4 common patterns (Basic Tracking, Marketing Attribution, User Context, Journey Tracking), 4 integration examples (Mailchimp, GA, Salesforce, HubSpot)
- ✅ `nxt-security-field` - 8 examples (~466 lines) - Google reCAPTCHA v2/v3/invisible, hCaptcha (privacy-focused GDPR), Cloudflare Turnstile managed/non-interactive/invisible, multi-layer security (CAPTCHA + honeypot + rate limiting), provider comparison (reCAPTCHA vs hCaptcha vs Turnstile), test keys for all providers, backend verification example
- ✅ `nxt-time-field` - 8 examples (~421 lines) - Appointment time, event start time with help text, delivery time preferences, meeting time (50% width for multi-column), business hours input with opening/closing pairs, reservation time slots (40% width), flexible availability, inline time picker (70% + 30% submit button), browser-native time picker, 12/24-hour format support
- ✅ `nxt-date-field` - 8 examples (~527 lines) - Birth date with age verification (18+), appointment booking (future dates only, 30-day window), event date selection (specific range), travel check-in (50% for side-by-side with check-out), historical dates (past only), expiration dates (40% width), flexible date range, inline date picker (70% + 30% submit), dynamic date calculation examples, min/max date validation

**Achievement:** Remaining Form Fields complete - AI can create comprehensive forms with hidden fields for tracking/analytics, CAPTCHA security with all major providers, time/date pickers with validation and responsive layouts, completing the form block ecosystem

---

#### ✅ Batch 13: Additional Form Fields (COMPLETE)
**Status:** 4 blocks, 32 examples, ~2,523 lines
**Commits:** 4 separate commits (381014b3, 38c3ab58, b009f8a9, b24cb34e)

- ✅ `nxt-message-field` - 8 examples (~418 lines) - Textarea multi-line input with 2-10 line configurations, contact forms, feedback, support tickets, product reviews, cover letters, surveys, character limits, auto-height
- ✅ `nxt-url-field` - 8 examples (~492 lines) - URL validation with autocomplete, company websites, portfolios, social media profiles (LinkedIn, GitHub, YouTube), product landing pages, blog RSS feeds, FontAwesome/image icons, platform-specific brand colors, multi-column layouts
- ✅ `nxt-number-field` - 8 examples (~533 lines) - Integer validation with min/max ranges, product quantities (1-100), age verification (18+), phone last 4 digits, US ZIP codes (00501-99950), team size, monthly budgets, ratings (1-10 NPS), promo code uses, no decimals warning
- ✅ `nxt-checkbox-button` - 8 examples (~680 lines) - Multi-select checkboxes with fldOptions array, skills selection (vertical 6 options), newsletter topics (horizontal 4 options, rounded), Terms & Conditions (single required), e-commerce services with pricing (+$5, +$15), GDPR consent (granular 4 checkboxes), days of week (Mon-Sun horizontal), dietary restrictions (8 allergies/preferences), product filters (40% sidebar)

**Achievement:** Additional Form Fields complete - AI can create multi-line textareas with character limits, URL inputs with autocomplete and platform-specific icons, number fields with integer validation and range constraints, multi-select checkbox groups with vertical/horizontal layouts and GDPR-compliant consent forms

---

#### ✅ Batch 14: Final Form Fields (COMPLETE)
**Status:** 3 blocks, 24 examples, ~2,189 lines
**Commits:** 3 separate commits (a4d96915, 7afce1af, cd715444)

- ✅ `nxt-acceptance-button` - 8 examples (~548 lines) - Single acceptance checkbox with integrated link support for legal agreements, Terms & Conditions, Privacy Policy with link (purple theme, target='_blank'), GDPR data processing consent (responsive typography 14-12px), age verification (18+, red color scheme), newsletter opt-in (optional reqTgl: false, green), cookie consent (nofollow link, orange), community guidelines, Terms + Privacy combined workaround, comprehensive GDPR compliance notes (ePrivacy, CCPA), integration examples for audit trail logging, consent management, age verification with birth date, consent withdrawal mechanism
- ✅ `nxt-radio-button` - 8 examples (~672 lines) - Single-select radio button groups with fldOptions array, marketing attribution (How did you hear about us? 5 sources vertical with Other), service rating scale (horizontal 5 levels Poor-Excellent, numeric values 1-5), Yes/No binary choice (square bRadius, NPS context), gender selection (demographic with inclusive options, privacy guidelines), shipping method (e-commerce with pricing Standard $5/Express $15/Overnight $25), payment method (Credit Card/PayPal/Bank Transfer with conditional fields), T-Shirt size (horizontal 6 options XS-XXL with international sizing), preferred contact method (50% width multi-column responsive), comparison table with nxt-checkbox-button showing differences (single-select vs multi-select, unselect behavior, submission data format)
- ✅ `nxt-option-field` - 8 examples (~969 lines) - Dropdown select field with comprehensive use cases, country selector (15 countries, ISO codes, alphabetical, placeholder), US State Selection (50 states + DC, optional, help text), product category (8 e-commerce categories, Other option), time zone selection (12 major zones, GMT offsets, IANA identifiers for DST accuracy), subscription plan (4 pricing tiers, monthly/annual, savings percentage, trust signals), age range (9 brackets, 50% width, demographic, Prefer not to say), payment terms (B2B Net 30/60/90, 40% width, POD option), size selection (7 apparel sizes, responsive width 40/50/100%), dropdown vs radio comparison (when to use which, visibility trade-offs), 4 integration examples (PHP backend validation, JavaScript dependent dropdowns Country → State, dynamic pricing calculation, PHP DateTime timezone conversion with IANA)

**Achievement:** Final Form Fields complete - AI can create comprehensive legal consent checkboxes with GDPR compliance, single-select radio button groups for critical decisions, dropdown select fields for long option lists with international standards (ISO codes, IANA timezones), understand when to use acceptance vs checkbox vs radio vs dropdown, implement dependent dropdowns, and handle complex form validation with backend integration. **Form block ecosystem fully documented with all 15 form field types complete.**

---

#### ✅ Batch 15: Navigation & Interactive UI (COMPLETE)
**Status:** 4 blocks, 32 examples, ~2,075 lines
**Commits:** 4 separate commits (f1f9adbd, 87d80991, 94eaaf14, e188e63b)

- ✅ `tp-smooth-scroll` - 8 examples (~489 lines) - Page-level smooth scrolling extension with 13 easing functions (ease-out-cubic most natural, linear for precision), aniTime 500-6000ms (2500 default), stepSize 200-1000px, tMult touchpad sensitivity (3.5 optimal for MacBooks), smNav anchor link smoothing (#section navigation), infinite looping (experimental bottom→top wrap), custom viewport scrolling (.content-area selector), normalizeWheel cross-browser, prefers-reduced-motion accessibility, FPS performance monitoring, 8 easing guide (ease-in/out-quad/cubic/quart/quint)
- ✅ `tp-scroll-navigation` - 8 examples (~894 lines) - One-page navigation with dot/text indicators, scrollNavStyle (style-1 dots + icons vs style-2 text labels), scrollDirection (vertical left/right vs horizontal top/bottom), scrollNavRepeater array with _key/title/sectionId/icon/tooltip, **CRITICAL: sectionId must match HTML element IDs exactly** (case-sensitive, no # symbol), scrollSpeed 600-2000ms (1000ms sweet spot), activeOffset 50-200px, progressBar with position top/bottom, hideOnMobile for desktop-only, showTooltip with 12 placements (top/bottom/left/right), Intersection Observer API for efficient detection, HTML section setup guide, smooth scroll integration
- ✅ `tp-mobile-menu` - 8 examples (~338 lines) - Mobile navigation bars with mmStyle (style-1 single row vs style-2 split left/right), posType (fixed/absolute/relative), fixPosType (top/bottom), menu1Item/menu2Item arrays with _key/textVal/iconStore/linkUrl, extraToggle center FAB button (style-2 only), pinText badges (cart "3", notifications "New"), linkType (url/email/phone for mailto:/tel:), displayMode swiper for multi-page, pageIndicator with indiStyle (line/dot), scrollOffsetTgl auto hide/show, iconType (icon FontAwesome vs image custom), bottom navigation bars (5 icons), split menus with center action, e-commerce carts, floating action menus with slide-out templates, scroll-hiding menus
- ✅ `tp-circle-menu` - 8 examples (~354 lines) - Circular/radial menu layouts with layoutType (circle radial vs straight linear), cDirection (top-left/top-right/bottom-left/bottom-right), angleStart/angleEnd 0-360 degrees (90 quarter circle, 180 semi, 360 full), circleRadius in pixels, sDirection (left/right/top/bottom for straight), menuStyle (style-1 icons only vs style-2 icons + text labels), icnTrigger (hover/click), circleMenu array with _key/iconStore/title/linkType/linkUrl, iconType (icon FontAwesome vs image custom), overlayColorTgl background overlay (rgba), iconPos fixed for floating action buttons, social media circle menus, contact FAB menus, full circle layouts, semi-circle arches, image icon menus

**Achievement:** Navigation & Interactive UI complete - AI can create page-level smooth scrolling with customizable easing and touchpad optimization, one-page scroll navigation with active state tracking and progress bars, mobile navigation bars with badges and split layouts, and circular/radial menus with configurable angles and overlays. Critical patterns documented: sectionId must match HTML IDs exactly, easing functions for natural scrolling, touchpad multipliers for cross-platform support, fixed positioning strategies for navigation persistence.

---

#### ✅ Batch 16: Utility & Effects Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~2,094 lines
**Commits:** 4 separate commits (b41c2d21, a541c63d, c36e3fa0, c7efcb64)

- ✅ `tp-dark-mode` - 8 examples (~695 lines) - Theme switching toggles with 3 visual styles (style-1 classic toggle, style-2 with icons, style-3 icon-only FAB), dmPosition (relative/absolute/fixed), 4 fixed corners (left-top/bottom, right-top/bottom), saveCookies persistence (365-day cookie), matchOsTheme OS-aware dark mode (prefers-color-scheme CSS), beforeText/afterText labels, separate styling for light/dark states (switchLgtBG/switchDarkBG, dotLgtBG/dotDarkBG), gradient backgrounds, custom icons (sun/moon FontAwesome), darkIconEn for separate dark icon, responsive sizing, cookie management (GDPR notes), OS theme detection (macOS 10.14+, Windows 10+, iOS 13+), CSS custom properties integration, accessibility (prefers-reduced-motion), conflict warning (don't use both saveCookies + matchOsTheme)
- ✅ `tp-mouse-cursor` - 8 examples (~496 lines) - Custom cursor effects with 4 cursor types (mouse-cursor-icon predefined CSS cursors, mouse-follow-image custom images, mouse-follow-text hover labels, mouse-follow-circle animated circles), 3 circle styles (mc-cs1 solid, mc-cs2 SVG stroke outline, mc-cs3 dual circles with mix-blend-mode), cursorEffect (mc-body entire page vs mc-block specific section), crclMixBMode (difference/exclusion/multiply/screen for inversion effects), firstCircleSize/secondCircleSize for dual circles, mcClick click effects, listTagHover CSS selectors (a, button, .clickable), CSS transforms (scale/rotate/translate), circleZindex 9999, mcCursorSymbol 'none' to hide default, accessibility (touch devices don't support custom cursors), performance (requestAnimationFrame 60fps), 7 blend modes documented
- ✅ `tp-preloader` - 8 examples (~465 lines) - Page loading screens with 12 prebuilt animation styles + custom logo option, loaderTrigger (page-load default, button-click for forms, scroll experimental), progressType (bar horizontal 0-100%, percentage numerical counter), minDisplayTime 800-1500ms recommended (too short <500ms flickers, too long >3s frustrates), fadeOutDuration 400-600ms smooth transition, custom logo with customImage/imageSize, showText loadingText, backgroundColor overlay (solid or rgba), loaderColor brand colors, zIndex 999999 top-most, button-click requires triggerSelector CSS selector, all 12 styles documented (style-1 circular spinner, style-5 bouncing dots, style-8 circular progress, style-10 grid squares), accessibility (aria-live regions, role='status'), performance (preload custom images in <head>), timeout failsafe (10s max)
- ✅ `tp-draw-svg` - 8 examples (~438 lines) - Animated SVG vector graphics with stroke drawing effects, selectSvg (preBuild library icons vs custom upload SVG), drawType (delayed staggered paths, sync simultaneous, oneByOne sequential), duration in frames (60 = 1 second at 60fps, 30-200 typical range, 120 recommended), hoverDraw triggers (onScroll viewport IntersectionObserver, onHover mouse interaction, loop continuous animation), strokeColor outline, fillToggle + fillColor for fill colors, maxWidth responsive sizing (80-400px recommended), alignment (left/center/right), SVG optimization guide (SVGO tools, target <20KB, convert text to paths in Illustrator), frame calculation guide (30 frames = 0.5s fast, 120 frames = 2s smooth, 180 frames = 3s dramatic), accessibility (prefers-reduced-motion to disable animations), performance (avoid loop on multiple elements, keep paths <20 for performance)

**Achievement:** Utility & Effects Blocks complete - AI can create theme switching toggles with cookie persistence and OS matching, custom cursor effects with mix-blend modes and dual circles, page preloaders with 12 animation styles and progress tracking, and animated SVG drawings with stroke effects. Critical patterns documented: cookie vs OS theme conflicts, custom cursors don't work on touch devices, preloader timing recommendations (800-1500ms min display), SVG optimization requirements (true vector paths, not rasterized images), frame-based duration calculations for smooth 60fps animations.

---

#### ✅ Batch 17: Dynamic & Utility Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~1,687 lines
**Commits:** 4 separate commits (dca11667, dd2a955b, 45564ce7, 84fb0977)

- ✅ `tp-dynamic-heading` - 8 examples (~427 lines) - Dynamic HTML content display within ACF repeater templates, {{field_name}} placeholder syntax (case-sensitive), must be inside tpgb/tp-repeater-template parent block, receives context (tpgb/dynamicRepField, tpgb/dynamicPost, tpgb/dynamicSubFieldData, tpgb/dynamicRowIndex), textValue supports HTML tags (semantic <h3>, <time datetime>, <cite>, <blockquote>), alignment (left/center/right responsive), hColor/hvrHColor hover states, hTypo typography controls, multi-field combinations ({{job_title}} + {{department}}), WYSIWYG field warning (already outputs <p> tags, don't nest), FontAwesome icons require FA loaded, use cases: product grids, team member cards, testimonial sliders, event timelines, ACF field type support (Text, Textarea, WYSIWYG, Number, Date Picker), integration examples with tp-dynamic-image/tp-dynamic-button for complete cards
- ✅ `tp-design-tool` - 8 examples (~417 lines) - Grid overlay system for layout alignment verification, 2 modes (gs_default pre-configured vs gs_custom fully customizable), gridColumn 4-24 columns (12 standard Bootstrap), gridMaxWidth 960-1600px (1140px Bootstrap, 1280px Tailwind), gridBGcolor semi-transparent (#8072fc40 = 25% opacity recommended), alleySpace gutter spacing (10px tight, 20px compact, 30px standard, 40px spacious), gridOffset left/right edge offset, gridDirection LTR/RTL for mirrored layouts, gridOnFront editor-only vs frontend visible (CRITICAL: must be false before site launch!), CSS custom properties injection (--tp_grid_cont_max_width, --tp_grid_columns, etc.), responsive grid breakpoints (md/sm/xs), framework matching guide (Bootstrap 1140px, Tailwind 1280px, Foundation 1200px), color recommendations (purple default, blue professional, red high-contrast dark themes, yellow client preview warning), workflow integration (design phase → client review → development testing → pre-launch removal), opacity guide (40 hex = 25%, 50 = 31%, 60 = 38%, 80 = 50%)
- ✅ `tp-empty-space` - 8 examples (~406 lines) - Vertical spacing control with responsive heights, 2 modes (toggle: 'normal' px numbers vs 'global' custom units with strings), space responsive object {md/sm/xs}, normal mode uses numbers only (space: {md: 50}), global mode uses strings with units (space: {md: '3rem'}), supported units (px absolute, rem typography-relative respects user font size WCAG, em parent-relative, vh viewport height percentage, vw viewport width), responsive patterns (conservative 100-70-50, aggressive 120-60-30, proportional rem scaling, viewport-based vh), spacing scale (20px tight, 30px small, 40px form fields, 60px medium, 80px before headings, 120px section dividers), typography hierarchy (more space before heading than after, 3:1 or 4:1 ratio, 80px before + 20px after pattern), vh mobile URL bar bugs (use fixed px on mobile as fallback), use cases by context (paragraphs 20-30px, after headings 15-25px, before headings 60-100px, section dividers 80-150px, hero to content 10-15vh, form fields 30-50px), accessibility (rem respects user preferences, no ARIA needed purely visual), value format critical (normal mode NUMBERS, global mode STRINGS with units)
- ✅ `tp-button-core` - 8 examples (~437 lines) - Free version button block with core CTA functionality, btxt button text (HTML source), bLink {url, target, nofollow} link object (target '_blank' for external, nofollow for SEO control), bAlign left/center/right responsive alignment, icons with 2 types (biType: 'fontAwesome' class names vs 'image' custom images), bipos icon position ('' before text, '1' after text using CSS flexbox order), bispac icon spacing column-gap (8-12px optimal), biSize icon font-size or max-width, btColor/bthColor text colors normal/hover, btBg/bthBg background objects (openBg: 1 required, bgType: 'color' or 'gradient', bgGradient with linear/radial + gradientAngle + gradientColor array), bBord/bthBColor border normal/hover, brad border radius (5-8px modern, 25-50px pill/circular, equal all sides for circles), btPad padding responsive (standard 12-18 top/bottom, 25-45 left/right, reduce 20-30% mobile), btshadow box shadow (openShadow: 1), globalPosition static/relative/absolute/fixed, glohoriOffset right offset, gloverticalOffset bottom offset for FAB buttons (30px from edges), responsive padding patterns (desktop 18/45, tablet 14/35, mobile 12/25), attribute name differences from Pro version (btxt vs buttonText, bLink vs link, etc.), comparison Free vs Pro (Free: basic functionality 2 icon types, Pro: 23 styles 4 icon types FancyBox Lottie), use cases (primary CTAs, secondary outline buttons, icon buttons arrow/download, gradient backgrounds, external links, fixed FAB floating action buttons, responsive sizing), minimum touch target 44×44px WCAG 2.5.5

**Achievement:** Dynamic & Utility Blocks complete - AI can create ACF-powered dynamic HTML content within repeater templates with field placeholder syntax, grid overlay systems for precise layout alignment with framework matching and responsive configurations, vertical spacers with multiple unit types (px/rem/vh) and responsive patterns, and free version CTA buttons with icons, gradients, and positioning. Critical patterns documented: ACF field names are case-sensitive, grid overlays must be disabled before site launch, spacing modes require different value formats (numbers vs strings), button icon positioning uses CSS flexbox order values ('' or '1').

---

#### ✅ Batch 18: Button & Advanced Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~3,725 lines
**Commits:** 4 separate commits (64b4c9bd, d5c99776, a1491b4a, 516f7b5f)

- ✅ `tp-advanced-buttons` - 8 examples (~492 lines) - Pro buttons with 2 button types (cta, download), style variants (style-1 classic, style-6 extra text overlay, style-8 emoji reactions, style-9 multi-layer text, style-13 external link, download style-1/3/5), download progress indicators (style-5 with loading/success text states), emoji guide (common emoji pairings 💯→👏, 🔥→⭐, platform testing recommendations), file size recommendations (<1MB instant, 1-10MB consider progress, >10MB always use style-5 progress bar), accessibility (ariaLabel required, screen reader emoji support with aria-label), button types comparison (cta for actions, download for file downloads with progress tracking)
- ✅ `tp-carousel-remote` - 8 examples (~989 lines) - Remote control carousel sync with carouselId matching, 2 carousel types (parent tp-anything-carousel container, child tp-anything-slide slides), slideNav navigation types (nav normal pagination, both arrows+pagination, arrow only, none hidden), slideArrow arrow styles (6 prebuilt styles + custom images), dotStyle pagination (7 dot styles with active/inactive states), centerMode spotlight effect with centerPadding (0-200px reveals side slides), slidesToShow multi-slide display (1-5 slides), equalHeight uniform slide heights, randomOrder shuffle, connectionPattern remote controls trigger parent carousel navigation, carouselIdMatch critical for synchronization (case-sensitive unique IDs), integration examples (product showcase with thumbnail controls, testimonial carousel with custom navigation, full-screen hero with dot indicators)
- ✅ `tp-dynamic-device` - 8 examples (~916 lines) - Device mockups with 3 layouts (normal single device, carousel multi-devices), 4 device types (mobile iPhones/Android, tablet iPads, laptop MacBooks/Dell, desktop iMacs/Dell), 3 content types (image screenshots, iframe live websites, template WP content), hover scroll animation (rebHoverScroll with rebTranDur 2-5s for long screenshots), click effects (nothing, link clickable device, popup FancyBox gallery), custom device frames (deviceType: custom with imgTopOff/imgLeftOff positioning offsets measured in Photoshop/Figma), manual scrollbar styling (scrollBarWidth, thumbBG, trackBG Webkit-only), carousel mode (cConImg array, centerSlideEffect scale/shadow, showDots pagination with 7 dotsStyle variants), animated icon badges (iconConAni pulse/floating/tossing/rotating animations, iconConAniDuration 2-4s), FancyBox integration (LoopFancy, ArrowsFancy, ThumbsOption thumbnail strip), iframeTitle WCAG 2.4.2 required, offset guide per device (MacBook rebTopOffset: 3%, rebLeftOffset: 12%)
- ✅ `tp-pricing-list` - 8 examples (~1,328 lines) - Restaurant menus and pricing catalogs with 3 styles (style-1 simple list, style-2 flip cards, style-3 horizontal with dotted line), tagField pipe-separated tags (Tag1|Tag2|Tag3 splits into styled badges, 2-4 tags recommended), price field repurposable (price $12.99, time 9:00 AM, duration 45 min), style-2 flip cards (hoverEffect horizontal/vertical, imgShape circle/square/none, normalBG front card, hoverBG back card, 0.6s GPU-accelerated flip), style-3 dotted line (lineStyle dotted/dashed/solid separator, imgMinWidth/imgMaxWidth equal for consistent sizing 60-100px, imgRightSpace margin), maskImg SVG masks (custom shapes hexagon/star/blob, CSS mask-image Webkit browsers, IE11 shows rectangular fallback), imageSize WordPress sizes (thumbnail 150px, medium 300px, large 1024px, full original), tagSpace 6-8px spacing between tags, use cases (restaurant menus, salon services, coffee shops size-based pricing, event agendas with speaker photos, professional services packages, product catalogs)

**Achievement:** Button & Advanced Blocks complete - AI can create advanced Pro buttons with emoji reactions and download progress tracking, remote-controlled carousels with thumbnail synchronization and center mode spotlight effects, device mockups with hover scroll animations and FancyBox galleries for app/website showcases, and comprehensive pricing lists with flip card interactions and dotted line separators for restaurant menus and service catalogs. Critical patterns documented: carouselId must match exactly for remote control sync, iframeTitle required for accessibility, tagField uses pipe separator (not commas), style-2 flip cards need hoverEffect attribute, SVG masks browser support limitations, custom device offset measurements.

---

#### ✅ Batch 19: Gallery & Media Display Blocks (COMPLETE)
**Status:** 4 blocks, 32 examples, ~4,513 lines
**Commits:** 4 separate commits (72cb7e86, f5b1c141, ecb00404, 03ee3494)

- ✅ `tp-table-content` - 8 examples (~1,302 lines) - Auto-generated TOC from page headings, 5 visual styles (none, style-1 vertical line, style-2 border-left, style-3 dotted horizontal, style-4 animated width), heading selectors (H1-H6), smooth scroll with configurable duration (300-1000ms), fixed positioning for sticky sidebar, custom scrollbar (Webkit-only), active state tracking with Intersection Observer, ChildToggle for nested heading collapse, responsive collapse patterns (DefaultToggle.xs: false for mobile), contentSelector must match page container
- ✅ `tp-creative-image` - 8 examples (~824 lines) - Advanced image effects for portfolios and hero sections, 4 effect types (ScrollRevelImg color overlay wipe, ScrollParallax vertical movement 80-200px, ScrollImgEffect hover scroll 2-5s, showMaskImg SVG mask clipping), CSS filters (grayscale, blur, brightness, contrast, sepia), FancyBox lightbox integration with auto-grouping, combined effects (parallax + filters + hover states), performance considerations (GPU acceleration, image optimization <500KB), SVG masks browser compatibility (IE11 fallback), drop-shadow filter follows mask shape
- ✅ `tp-coupon-code` - 8 examples (~659 lines) - Interactive coupon code reveals for e-commerce and affiliate marketing, 4 coupon types (standard click-to-reveal, scratch HTML5 canvas gamification, slideOut fixed panel from screen edge, peel 3D transform corner reveal), 3 standard styles (style-1 dashed border, style-2 corner ribbon, style-3 minimal underlined), 2 action types (click inline reveal, popup modal overlay), interactive features (codecopyIcn Clipboard API, directionHint pulsing arrow, codeArrow), scratch-off mechanics (fillPercent 50-70%, touch/mouse drag, HTML5 canvas), masked links for affiliate marketing (dropdown with multiple retailer options), cookie persistence (saveCookie hides for returning visitors), Clipboard API requires HTTPS
- ✅ `tp-media-listing` - 8 examples (~1,528 lines) - Image and video galleries with 4 layouts (grid standard responsive, masonry Pinterest-style natural heights, metro Windows Metro mixed tile sizes, carousel Swiper horizontal scrolling), 4 visual styles (style-1 center overlay, style-2 bottom overlay, style-3 center overlay, style-4 content below with Learn More button), GalleryType (image with FancyBox lightbox, video YouTube/Vimeo/self-hosted embeds), category filtering with Isotope.js (TextCat "All" label, FilterHs style-1/2/3/4 hover effects), ImgRepeater array with _key/Rimg/Rtitle/RCaption/RCategory, video sources (VSource youtube/vimeo/self-hosted, YouTubeId 11-char, VimeoId numeric, RVideo self-hosted), metro patterns (MetroSty style-2 first item 2×2 large tile), carousel slideColumns responsive (md: 4, sm: 3, xs: 2), custom URLs (FCusURl: true enables Rurl per item), style-4 buttons (DisBtns4: true, Btns4txt custom text, BtnCr color), custom icons (RselImg icon/image, RIcon FontAwesome, ExtIcon size, ExtIconCr/ExtIconHCr colors), FancyBox advanced (thumbs.autoStart, buttons array, animationEffect zoom-in-out), performance (Isotope ~15KB, FancyBox, Swiper ~30KB total 50-80KB libraries), Bootstrap 12-column grid (columns md: 3 = 4 columns, md: 4 = 3 columns, md: 6 = 2 columns)

**Achievement:** Gallery & Media Display Blocks complete - AI can create auto-generated table of contents with active state tracking and smooth scrolling, advanced image effects with parallax and SVG masks for hero sections, interactive coupon code reveals with scratch-off gamification and affiliate link masking, and comprehensive image/video galleries with filtering, FancyBox lightboxes, and multiple layout options (grid, masonry, metro, carousel) for portfolios and media showcases

---

**Overall Phase 3 Progress:**
- ✅ **Completed:** 75 blocks, 587 examples, ~58,776 lines
- ✅ **Batches Complete:** Batch 1 (Layout), Batch 2 (Content), Batch 3 (Interactive), Batch 4 (Forms), Batch 5 (Marketing & Media), Batch 6 (Advanced), Batch 7 (Typography & Display), Batch 8 (Common UI), Batch 9 (Visual Effects & Counters), Batch 10 (Content Display), Batch 11 (Interactive & Advanced Features), Batch 12 (Remaining Form Fields), Batch 13 (Additional Form Fields), Batch 14 (Final Form Fields), Batch 15 (Navigation & Interactive UI), Batch 16 (Utility & Effects), Batch 17 (Dynamic & Utility), Batch 18 (Button & Advanced), Batch 19 (Gallery & Media Display)
- **Total Enhanced:** 75 / 84 total blocks (89.3% complete)
- **Remaining:** 9 blocks
- **Phase 3: IN PROGRESS** ⏳

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
