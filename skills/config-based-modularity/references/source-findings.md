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

Use the public `dhii/containers` README as the source of truth for the package's concrete container classes and examples: <https://github.com/Dhii/containers#readme>. The skill should not repeat that catalog.

Portable lessons to carry from this implementation:

- Known factory/extension maps are enough to represent a module's contribution.
- Provider composition and container composition are complementary: one composes definitions, the other composes lookup sources.
- Deterministic order is part of the architecture: later definitions replace earlier ones, while extensions layer predictably.
- Helpers such as aliases, path access, prefix/deprefix views, masking, mapping, caching, and tagging are conveniences built from the same underlying model, not additional primitives.
- Tests for this repository are useful evidence of the semantics: provider order, extension order, lookup delegation, lazy/cached resolution, aliases, path/scoped views, and error cases are all tested directly.

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

A larger, real-world implementation was used to distinguish core primitives from scale-driven conventions. It should remain anonymous in the skill.

Portable lessons from that implementation:

- Scale did not introduce new primitives. It introduced governance around names, bindings, integration layers, override layers, and tests.
- Cross-module relationships were expressed through keys, aliases/adapters, and extensions rather than package-level coupling between sibling modules.
- Stable abstraction keys could be rebound by later configuration, showing why order and immutability matter.
- List services plus extensions solved collection/tagging-style problems without requiring tagging as a primitive.
- Debug behavior was represented as ordinary definitions and extensions, not privileged access to internals.
- Configuration splitting was an organizational convention over maps with deterministic merge order.

### Test Architecture

Testing focused on composition semantics rather than platform behavior:

- Reusable harnesses bootstrapped selected providers/modules with test-only override factories and extensions.
- Tests asserted load order, later factory replacement, extension ordering, alias/binding resolution, scoped views, lazy/cached resolution, and circular/missing dependency diagnostics.
- Single-module tests satisfied outside dependencies through keys, preserving module encapsulation.

Portable lesson: integration files, aliases, constructors, override groupings, list services, lazy wrappers, and scoped containers are emergent conveniences. They are useful once a system grows, but the core pattern remains keyed definitions plus deterministic composition with replacement and extension semantics. Testing should target composition semantics: order, overrides, extensions, bindings, scoped views, lazy/cached resolution, and dependency diagnostics.

## Simple vs Complex Reference Lessons

- The simple reference shows the minimum viable shape: ordered modules, providers, host-provided keys, bootstrap composition, and extension tests.
- The complex reference shows that larger systems mainly need stronger naming, binding, integration, override, and testing discipline.
- The core did not change between them: module as provider, keyed definitions, extensions, deterministic order, host-owned composition, and one source of truth.

## Progressive Layer Map From References

- **Primitive:** keyed definitions, extensions, deterministic order, replacement/extension semantics, one source of truth.
- **Service management:** path-like names, dependency declarations as referenced keys, aliases/bindings, scoped views, list services, config splitting.
- **Composition/bootstrap:** provider composition, container/source composition, caching, lookup delegation, immutable final configuration.
- **Use case:** debug modules, settings/forms, path mapping, API wiring, handler pipelines, testing strategy.

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
