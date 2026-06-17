# Source Findings

These notes ground the `config-based-modularity` skill in the requested source material while keeping the skill itself language-agnostic.

## Public Sources

- Article: [Cross-Platform Modularity in PHP](https://dev.to/xedinunknown/cross-platform-modularity-in-php-30bo) by Anton Ukhanev.
- Simple reference implementation: [wp-oop/plugin-boilerplate](https://github.com/wp-oop/plugin-boilerplate).
- Container/service-provider primitives: [Dhii/containers](https://github.com/Dhii/containers).
- Service provider spec: [container-interop/service-provider](https://github.com/container-interop/service-provider/).

A larger, real-world private implementation is used as a complex reference. It is not named or linked here.

## Dev.to Article

The article "Cross-Platform Modularity in PHP" by Anton Ukhanev argues for modules that can run across host systems when a compatible loader composes them. The portable architectural ideas are:

- **Host/application ownership**: The application selects modules, determines load order, links to a larger platform when needed, and satisfies dependencies.
- **Inversion of control**: The application is "for" modules. Modules do not depend directly on one proprietary host; the host composes them and supplies the environment.
- **One source of truth**: A single composed container/configuration source exposes all resolved values.
- **Factories and extensions**: Factories define values and later factories for the same key replace earlier ones. Extensions apply on top of prior values and later extensions layer predictably.
- **Load order as architecture**: Resolution depends on the module/source order, so the order must be explicit and controlled.
- **Dependencies through missing keys**: A module can reference a key it does not provide; the host or another module supplies it.
- **Medium-agnostic configuration**: Values can come from service definitions, files, databases, settings, network sources, or other media if they are layered into one conceptual source.
- **Submodules**: A module can compose other modules, creating hierarchies while preserving the same composition model.

The article's PHP-specific APIs, DI container classes, and WordPress examples are implementation-specific. Do not treat them as required in the skill. The article also presents a setup/run lifecycle for modules, but this is an implementation detail of one reference. The essential idea is that a module is its configuration: a service provider exposing definitions and extensions. It does not need a separate lifecycle.

## Dhii Containers Reference

The `dhii/containers` package provides a public set of composable container/decorator primitives. These map to the skill's progressive layers:

**Primitive layer:**
- `ServiceProvider` -- minimal implementation: keyed factory map plus keyed extension map. This is the module's representation.
- `CompositeCachingServiceProvider` -- aggregates multiple providers; later factories override earlier ones; extensions for the same key compose in provider order. This is the core composition mechanism.
- `DelegatingContainer` -- invokes factories and extensions from a provider, passes a parent container for lookup delegation. Supports lazy resolution: values are produced on `get()`. Detects circular dependencies with readable dependency-path diagnostics.

**Service-management layer:**
- `AliasingContainer` -- maps outer keys to inner keys. Portable lesson: aliases for integration, compatibility, and scoping.
- `PrefixingContainer` / `DeprefixingContainer` -- adds or strips key prefixes for scoped module-local views.
- `PathContainer` -- resolves delimiter-separated path-like keys through nested containers.
- `SegmentingContainer` -- exposes flat delimiter-separated keys as nested container views.
- `MaskingContainer` -- selectively hides or exposes keys.

**Composition/bootstrap layer:**
- `CompositeContainer` -- ordered fallback lookup across multiple containers without merging. First match wins.
- `CachingContainer` -- ensures each key's value is resolved at most once.
- `ProxyContainer` -- forwards to an assignable inner container. Solves the bootstrapping chicken-egg problem: definitions need the final composed container, which does not exist until composition completes.
- `HierarchyContainer`, `DataStructureBasedFactory`, `DictionaryFactory` -- convert array/object hierarchies into navigable container trees.
- `MappingContainer` -- transforms values on the fly.
- `Dictionary` -- immutable map with container interface.

**Emergent helper (not a primitive):**
- `TaggingServiceProvider` -- scans factory docblocks for `@tag` annotations and generates collection services. Demonstrates the tagging/list-service pattern but is a convenience, not a required primitive.

## Simple Reference Implementation (wp-oop/plugin-boilerplate)

This repository demonstrates the minimal shape:

- The host entry point chooses modules, allows the host environment to alter the module list, bootstraps a composed container/configuration source, and injects platform values as keys.
- The bootstrap function composes module service providers with additional host containers. The additional sources can override or satisfy module-provided definitions.
- A module list file makes module selection explicit.
- Each module is represented by its service provider: a set of factories and extensions. A module does not do anything besides provide configuration.
- A composite module wrapper shows a module composed from submodules' service providers.
- Factory files show definitions derived from other keys. A demo module defines a default value that the main module overrides by deriving it from a host-provided key, demonstrating replacement via load order.
- Tests verify extension composition: a value defined by a factory is later modified by an extension.

Portable lesson: the core is keyed definitions, extensions, deterministic composition, host-provided keys, and one composed source. A module is its service provider. The WordPress hook calls and PHP class names are not the core pattern.

## Complex Reference Implementation

A larger, real-world implementation preserves the same primitives at scale and adds organizational conventions:

- The module list defines an explicit ordered list of over a dozen modules spanning concerns like internationalization, environment detection, API clients, filesystem, templating, shortcodes, settings, customization, geo-IP, and debugging.
- The host entry point adds platform state into the composed source: persisted options, debug flags, locale, site URL, database handles, upload paths, and other runtime values.
- The main factory file splits concerns into settings/cache/http/integration/override sub-files and uses aliases and constructors as helper conveniences.
- Extension files show add-ons extending existing values such as settings form fieldsets, settings keys, and API request metadata.
- Integration factory files bridge module-local keys across module boundaries without requiring modules to import each other's internals. For example, an integration file maps host-level keys to module-prefixed keys using aliases. Stable abstraction keys can be rebound later: a visitor-locale key starts as a site-locale alias and is later rebound to a geo-IP locale by a later integration mapping.
- List services plus extensions form integration buses: modules contribute path-mapping lists, settings fields, or SDK request data without depending directly on one another.
- An override factory file reserves an explicit place for replacement definitions, merged last for final precedence.
- A deprefixing container demonstrates scoped configuration: code can read/write a local unprefixed view while the underlying source stores prefixed keys. Tests verify get/set/has/unset through prefixed indirection.
- A lazy function service wrapper defers expensive resolution until invocation time, using the same definition primitive.
- Module-local package manifests act as dependency metadata. Each module declares its own external library dependencies; sibling module dependencies are expressed through referenced keys, not package-level requires.
- A debug module contributes settings fields, debug-flag-driven extensions, and diagnostic wiring as an ordinary service provider, without modifying original modules.
- Modules are purely configuration: each is represented by its service provider (factories + extensions). The host bridge is the only code that interacts with the proprietary platform runtime.
- Configuration splitting uses ordinary language files and map-merge patterns: local factories, settings, cache, HTTP, filesystem, integration, override, and extensions. The merge order within a provider determines internal precedence.

### Test Architecture

- A reusable abstract modular test case bootstraps service providers or modules, appends an extra provider containing test override factories/extensions, and asserts the final composed container. Tests do not require the real platform runtime.
- An abstract application test case boots the full module list plus the main application provider while overriding host/platform keys (debug flags, options, uploads, URLs, database handles) with test values.
- Module composition tests verify load order, later factory override, and extension application across multiple modules.
- App-level tests verify extensions, composed filesystem/path URL resolution, and aliased/integrated services.
- Debug/settings tests verify that a debug module contributes fields/keys through extensions without modifying original modules.
- Template/path resolver tests use virtual filesystems and path mappings to test abstract resource keys.
- Scoped-configuration tests verify read/write/unset behavior through prefixed indirection.
- Single-module tests boot one module with mocked boundary services, showing module encapsulation and explicit dependency satisfaction by keys.

Portable lesson: integration files, aliases, constructors, override groupings, list services, lazy wrappers, and scoped containers are emergent conveniences. They are useful once a system grows, but the core pattern remains keyed definitions plus deterministic composition with replacement and extension semantics. Testing should target composition semantics: order, overrides, extensions, bindings, scoped views, lazy/cached resolution, and dependency diagnostics.

## Comparing Simple And Complex Implementations

| Concept | Simple implementation | Complex implementation | Core or emergent? |
|---|---|---|---|
| Module list with explicit order | 1 module | 12+ modules | Core |
| Host-injected platform keys | 3 keys | 9+ keys (options, debug, locale, URL, DB, uploads, ...) | Core |
| Module = service provider (factories + extensions) | Yes | Yes | Core |
| Lazy resolution on access | Yes (via delegating container) | Yes (via delegating container + lazy function services) | Core |
| Factories per module | Single file | Split into settings/cache/http/integration/override files | Emergent (organizational) |
| Extensions per module | Single file | Split into multiple files | Emergent (organizational) |
| Aliases | 2 | 30+ across integration files | Emergent (convenience) |
| Binding/alias management across module namespaces | Minimal | Extensive; dedicated integration files per module | Emergent (governance) |
| Module-local dependency declarations | Implicit (referenced keys) | Implicit (referenced keys) + module-local package manifests | Emergent (packaging readiness) |
| List services / tag-like contributions | Not present | List services + extensions as contribution buses | Emergent (from primitives) |
| Constructors | None | A few | Emergent (convenience) |
| Lazy function services | None | Present | Emergent (convenience) |
| Integration files | None | Bridging module keys across boundaries | Emergent (organizational) |
| Override files | None | Reserved override file (mostly empty) | Emergent (organizational) |
| Scoped/deprefixing container | None | Deprefixing container for namespaced access | Emergent (convenience for large namespaces) |
| Module-local package manifests | Not present | Present for all modules | Emergent (packaging readiness) |
| Submodule composition | Composite module wrapper | Identical pattern | Core |
| Debug module | Not present | Ordinary module with extensions for diagnostics | Emergent (use case) |
| Configuration splitting | Single factory/extension files | Split by concern with deterministic merge order | Emergent (organizational) |
| Provider vs container composition | Both present in bootstrap | Both present in bootstrap | Core (complementary) |
| Bootstrap flow (provider compose, container compose, proxy wire, cache) | Present | Present (identical pattern) | Core |
| Platform runtime calls | Only in host bridge | Only in host bridge | Platform bridge, not core |
| Reusable modular test harness | Present | Present (more extensive) | Core (testing strategy) |

## Progressive Layer Map From References

| Layer | What it covers | Reference examples |
|---|---|---|
| Primitive | Keys, lazy definitions, extensions, deterministic order, override/extension semantics, one source of truth | `ServiceProvider`, factory/extension maps, `DelegatingContainer`, `CompositeCachingServiceProvider` order semantics |
| Service management | Path-like keys, module namespaces, dependency declarations as referenced keys, aliases/bindings in integration files, scoped prefix/deprefix views, list services, config splitting | Integration factory files, alias maps, `PrefixingContainer`/`DeprefixingContainer`, module package manifests, list-service definitions plus extensions |
| Composition/bootstrap | Composite providers, provider ordering, submodule aggregation, composite containers, caching, proxy/delegating lookup, path/scoping/masking/mapping views, external config sources, bootstrap wiring | `CompositeCachingServiceProvider`, `CompositeContainer`, `CachingContainer`, `ProxyContainer`, `PathContainer`, `MaskingContainer`, bootstrap.php flow |
| Use case | Debug modules, settings forms, filesystem path mappings, API client wiring, handler/pipeline composition, sandbox/debug config, testing strategy | Debug module extensions, settings field list services, path-mapping contribution buses, abstract modular test cases, single-module isolation tests |

## Generalization Boundaries

Generalize these ideas:

- Keyed lazy definitions that derive values from other keys.
- Extensions that decorate or layer values instead of replacing them.
- Host-owned load order and platform integration.
- Single composed source of truth across code, files, environment, persisted settings, and runtime state.
- Namespaced/scoped keys for large systems.
- Events represented as handler pipeline values under keys.
- Explicit binding/alias governance between module namespaces.
- Module-local dependency declarations as referenced keys.
- List services as emergent contribution buses.
- Debug modules as ordinary modules.
- Progressive layering: primitive, service management, composition/bootstrap, and use case.
- Testing composition semantics: order, overrides, extensions, bindings, lazy/cached behavior, scoped views, and dependency diagnostics.

Do not generalize these as required:

- PHP interfaces or service-provider APIs.
- WordPress hooks as the boundary mechanism.
- Composer package layout.
- A setup/run lifecycle separate from the service provider. A module is its configuration; it does not need its own execution phase.
- Specific helper classes such as aliases, constructors, deprefixing containers, tagging providers, or dictionary containers.
- Tagging as a primitive. The same problem is solved elegantly with list services and extensions.
- Feature flags as a substitute for module boundaries.
- Framework-specific lifecycle hooks, auto-discovery, or annotations.
