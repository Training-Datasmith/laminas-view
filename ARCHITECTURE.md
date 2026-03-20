# Architecture: laminas-view

## Purpose
A PHP view rendering layer supporting PHP templates, view models, template resolvers, view helpers, and context-aware output escaping. Used as the default view layer in laminas-mvc applications.

## Directory Structure
```
src/
  View.php                           # Top-level renderer coordinator
  Renderer/
    Php_Renderer.php                 # Renders PHP template files in isolated scope
    Template.php                     # Captures include() output
  Resolver/
    Template_Map_Resolver.php        # Resolves template names from a prebuilt name→path map
    Template_Path_Stack.php          # Resolves by searching directories in order
    Prefix_Path_Stack_Resolver.php   # Module-prefix-based path resolution
    Aggregate_Resolver.php           # Tries multiple resolvers in order
  Model/
    View_Model.php                   # Carries template name + variables; supports child models
  Helper/
    Escape_Html.php / Escape_Url.php / Escape_Css.php / Escape_Js.php / Escape_Html_Attr.php
    Head_Link.php / Head_Meta.php / Head_Script.php / Head_Style.php / Head_Title.php
    Partial.php / Partial_Loop.php
    Placeholder.php / Render_To_Placeholder.php
    Layout.php / Asset.php / Base_Path.php / Doctype.php / Cycle.php
    Gravatar_Image.php / Html_List.php / Html_Tag.php / Html_Attributes.php
    View_Model.php                   # Renders a child view model from within a template
  Helper_Plugin_Manager.php          # ServiceManager plugin manager for view helpers
  Console/
    Generate_Template_Map_Command.php   # Artisan-style command to pregenerate template maps
    Template_Map_Generator.php
  HTML/Tag.php / Html_Attributes_Set.php
  Exception/                         # Typed exceptions
  Factory/                           # laminas-servicemanager factories for DI wiring
  Config_Provider.php / Module.php
```

## Key Design Decisions
- **View model pattern** — `View_Model` is a plain value object (template name + variables); it has no rendering logic. This separates data preparation (controller) from rendering (Php_Renderer).
- **Child view models** — view models can nest child models (header, footer, sidebar). The renderer processes the tree from leaves to root, supporting layout composition without a separate two-pass mechanism.
- **Context-aware escaping** — five separate escape helpers (`EscapeHtml`, `EscapeUrl`, `EscapeCss`, `EscapeJs`, `EscapeHtmlAttr`) use `laminas-escaper` for context-specific encoding, preventing XSS.
- **Aggregate resolver** — template path resolution tries multiple resolvers in sequence (map → path stack → prefix stack), short-circuiting on first match. This allows module-local templates to override global ones.
- **Placeholder helpers** — `Head_Link`, `Head_Script`, `Head_Style`, and `Head_Title` buffer output into named placeholder containers, enabling views and layouts to inject assets in the right page section.

## Extension Points
- Implement `Renderer_Interface` to add a Twig, Blade, or other template engine.
- Implement `Resolver_Interface` to add a custom template resolution strategy.
- Register custom view helpers via `Helper_Plugin_Manager` configuration.

## Dependency Flow
```
Php_Renderer::render(View_Model)
  └─ Aggregate_Resolver::resolve($templateName) → file path
       └─ include($file) in isolated scope with $this = Php_Renderer
            └─ $this->helperName() → Helper_Plugin_Manager → view helper
  └─ process child view models recursively
       └─ inject into parent placeholder
```
