You are an expert in WordPress, Gutenberg blocks, Nexter Blocks (free + Pro), PHP/JS plugin internals, schema design, and tool-building with MCP servers and AI agents.

## Background and current state

I already have an AI-powered CLI that talks to a custom MCP Server. This MCP Server is deployed on my Ubuntu server as a Dokploy-managed docker-compose app. Dokploy handles:

- Creating an isolated app network and attaching all services to it.
- Wiring Traefik to that network for routing, with labels/domains configured in Dokploy.
- Generating a .env file from environment variables defined via the Dokploy UI.
- Optionally randomizing resource names (services, volumes, networks, etc.) to avoid conflicts when “Randomize Compose” is enabled.

The MCP Server sits between the AI-powered CLI and my WordPress site.

My WordPress site uses Nexter Blocks, including Nexter Pro, in the Gutenberg editor. Check if  MCP Server is designed with these goals and constraints:

- It can generate new blog posts and pages, and modify existing posts/pages, with a primary focus on content built using Nexter Blocks in Gutenberg.
- All content creation and updates are saved as drafts by default (no automatic publishing) so I can review them first.
- Edits integrate cleanly with WordPress revisions, so I can revert changes when needed.
- When editing existing content, the system is designed to be non-destructive:
  - Preserve existing layout and block structure.
  - Avoid corrupting Nexter blocks or breaking layouts.
  - Make targeted adjustments (e.g., change text, tweak attributes, add/remove specific blocks) instead of rebuilding entire sections.
- For images, the system uses Nexter Image Block placeholders so that I can later upload and manage actual media through the WordPress media library.

## What I want you to do now is generate Nexter block schemas:

Your job now is to deeply understand  how the  Nexter plugin files which are in folders mentioned below + MCP Server + wordpress nexter plugin files setup works for Nexter Blocks.

Nexter has 90+ blocks across its free and Pro versions. To help the AI-powered CLI understand them, you are making schema-driven system in place:

- In this workspace, there are the Nexter plugin folders:
  - `the-plus-addons-for-block-editor`
  - `the-plus-addons-for-block-editor-pro`
- From these plugin files, you will generate machine-readable schema files for Nexter blocks. Generate them Schemas by referencing plugin files. Remember all schemas should be as if they were given to you, you should be able to do it all without breaking blocks, having pixel perfect layout and structure of blocks, able to make complex block with all settings set perfectly, being able to do targeted block modifications, modifying full page/post or specific sections as instructed by user.
- For this you will hand craft these individual schema files, not use a script which will degrade the quality and the purpose of making these schema will be lost. You can do them in batch as all at once wont be possible.
- These schema files will live in a `schema` folder. Typically:
  - There will be one schema per block.
  - When a block exists in both free and Pro, the Pro version is considered authoritative.
  - These schemas are intended for the MCP Server to give the AI-powered CLI detailed context about each block’s attributes and capabilities.