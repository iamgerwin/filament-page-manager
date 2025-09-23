# Changelog

All notable changes to `filament-page-manager` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2024-09-24

### Fixed
- **Type Safety Improvements**
  - Fixed all PHPStan level 8 static analysis errors
  - Added comprehensive PHPDoc annotations to all models, methods, and properties
  - Resolved Filament v4 component type compatibility issues
  - Fixed PHP 8 union type declarations for navigation properties
  - Corrected generic Collection type parameters throughout codebase
  - Added proper type hints for array values and return types

- **Filament Compatibility**
  - Updated deprecated table action methods (`actions()` to `recordActions()`, `bulkActions()` to `toolbarActions()`)
  - Fixed Form vs Schema parameter type mismatches in resources
  - Corrected component namespace imports for Filament v4 structure
  - Resolved duplicate import statements

- **Model Enhancements**
  - Added `@property` annotations for all database columns and relationships
  - Fixed `descendants()` return type to use `Illuminate\Support\Collection`
  - Improved `generateUniqueSlug()` method to handle null and empty slug arrays
  - Enhanced type checking in `duplicate()` method

- **Template System**
  - Updated template contract and abstract class with proper type annotations
  - Fixed SEO field generation with correct component types
  - Improved field cloning for locale-specific content
  - Enhanced translatable field wrapper methods

- **Test Suite**
  - Fixed test template class references
  - Corrected pages structure test expectations
  - Updated mock data to match new type requirements
  - All 27 tests now passing with 97 assertions

## [1.0.0] - 2024-09-24

### 🎉 Initial Release

#### Added
- **Page Management System**
  - Hierarchical page structure with parent-child relationships
  - Draft and publish workflow for content control
  - Page duplication with automatic slug generation
  - Drag-and-drop sorting with customizable order
  - Breadcrumb generation for navigation
  - Path and URL generation with configurable prefixes/suffixes

- **Template System**
  - Flexible template-based content architecture
  - Abstract template classes for pages and regions
  - Dynamic form field generation based on templates
  - Template-specific path suffixes
  - Artisan command for quick template scaffolding
  - Support for unique template instances

- **Region Management**
  - Reusable content blocks across multiple pages
  - Template-based region configuration
  - Unique naming enforcement for regions
  - Region duplication capabilities

- **Multilingual Support**
  - Complete translation system with HasTranslations trait
  - JSON-based storage for translated content
  - Locale-specific slug generation
  - Fallback locale support
  - Translation management for all content fields

- **SEO Optimization**
  - Built-in SEO field configuration
  - Meta title and description management
  - Open Graph image support
  - Keywords management
  - Configurable SEO field requirements

- **Filament Integration**
  - Full Filament v4 compatibility
  - Comprehensive admin resources for pages and regions
  - Advanced table features with filtering and sorting
  - Bulk actions for content management
  - Custom navigation configuration
  - Reorderable table rows

- **Developer Experience**
  - Global helper functions for easy access
  - Facade pattern implementation
  - Comprehensive configuration options
  - Cache management with tag support
  - Model and resource customization
  - Extensive PHPDoc annotations

- **Testing & Quality**
  - Complete Pest test suite with unit and feature tests
  - PHPStan level 8 static analysis
  - PSR-12 coding standards compliance
  - GitHub Actions CI/CD pipeline
  - Automated dependency updates with Dependabot

#### Technical Stack
- PHP 8.3+ with modern language features
- Laravel 11.0+ framework
- Filament 4.0+ admin panel
- Spatie Laravel Package Tools for package development

#### Documentation
- Comprehensive README with installation guide
- Usage examples and code snippets
- API documentation with method signatures
- Configuration reference
- Template creation guide
