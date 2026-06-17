---
name: config-based-modularity
description: Design, explain, review, and refactor language-agnostic config-based modular architectures based on keyed definitions, deterministic composition, overrides, extensions, scoped configuration, and host-owned platform integration. Use this skill whenever the user asks about modularity, plugin systems, add-ons, module loading, service providers, dependency injection, configuration-driven architecture, extensibility, event or hook pipelines, middleware-style handler layering, reusable cross-platform modules, separating host/platform policy from reusable module configuration, binding or aliasing services between modules, comparing framework DI/providers to portable modularity, feature flags versus modular composition, debug or diagnostic modules, testing composition semantics, or how complex systems emerge from simple config primitives.
---

# Config-Based Modularity

Use this skill to help users design, explain, review, or refactor systems where modules contribute behavior through composed configuration rather than by mutating global state or depending directly on a host runtime.

The pattern is inspired by Anton Ukhanev's article "Cross-Platform Modularity in PHP" and the associated reference implementations. Treat those sources as inspiration, not as the target interface: keep recommendations language-agnostic and avoid coupling the advice to PHP, WordPress, Composer, PSR, or any specific dependency injection container.

For deeper source-grounded notes, read `references/source-findings.md` when you need more context about what came from the article and implementations.

## Core Idea

A module is its configuration. It is represented by its service provider: a set of keyed definitions and extensions. A module does not do anything besides provide configuration. It has no separate setup, run, or lifecycle phase. All behavior it contributes -- services, handlers, commands, routes, adapters, UI contributions, pipelines, policies, runtime objects -- is expressed as values under keys.

In a real sense, the configuration code and its keys constitute the application layer. The keys and the types of values they resolve to define the application's semantics. Implementation details live inside definitions; the configuration structure is the architecture.

Most of the powerful features of this architecture arise from a handful of primitives: keyed lazy definitions, extensions, deterministic composition order, override semantics, and one composed source of truth. Integration layers, debug modules, override files, handler pipelines, feature selection, tagging/contribution buses, scoped views, and submodules all emerge from recombining these primitives rather than adding separate extension mechanisms.

A module's configuration consists of:

- Keyed definitions that create values lazily: each definition is a function or recipe that receives a container/registry or a declared list of dependency keys and produces a value when accessed.
- Extensions that layer behavior onto an existing key without replacing its original definition.
- Optional dependency metadata or manifests that explain which keys the module provides and which keys it expects another module or the host application to provide.

The host application owns module selection, load order, composition, dependency satisfaction, and integration with the external runtime or platform. A single composed registry, container, or configuration source becomes the source of truth that modules read from.

Once composed, the configuration must not change. Definitions and extensions are collected during composition, and the resulting source of truth is immutable for the lifetime of the application process. In environments with long-lived processes (e.g., an async server), changes require restarting or explicitly reloading the configuration -- not mutating it in place. This immutability is what makes resolution predictable and cacheable.

This inverts the usual plugin model. Instead of modules being written for one host and calling host-specific APIs directly, the host adapts platform concerns into keys and composes modules around those keys.

## Vocabulary

- **Module**: A reusable unit represented by its service provider. It contributes definitions, extensions, and metadata. It does not have its own execution lifecycle.
- **Host application**: The system that selects modules, orders them, composes their contributions, supplies missing dependencies, and bridges to the runtime.
- **Registry/container/configuration source**: The composed lookup surface for keys. It may resolve values from code, files, environment, persisted settings, database rows, remote config, or user input.
- **Key**: A stable identifier for a value. Keys need naming discipline and should usually be namespaced or scoped. In practice, keys are often path-like strings.
- **Definition/factory**: A recipe for producing a value associated with a key. Most definitions are lazy: they receive a container/registry or declared dependencies and produce a value when first accessed. However, some values -- particularly scalars, constants, and test fixtures -- can be stored directly in memory without lazy resolution.
- **Extension/decorator/layer**: A contribution that receives or wraps a previously resolved value and returns a modified value.
- **Override/replacement**: A later definition for the same key that replaces an earlier definition.
- **Binding**: An explicit connection between a required key and a provided key/value, often across module namespaces. Bindings are typically expressed as aliases or adapter definitions in a host-owned integration layer.
- **Alias map**: A deliberate set of key-to-key mappings for integration, compatibility, migration, or scoping.
- **Adapter/bridge**: Host-owned code that maps platform APIs, runtime state, or proprietary events into keys and values.
- **Handler pipeline**: A composed value, often under one key, that represents an event, hook, middleware chain, command pipeline, or request flow.
- **Scoped configuration**: A restricted or prefix-adjusted view of the composed source. This is a general-purpose technique for slicing configuration segments -- useful for module-local keys, environment sections, settings categories, or any structured subset -- while preserving one source of truth.
- **List service**: A value whose purpose is to collect many contributed services/values under one key. This is an emergent pattern built from definitions and extensions, and is a good language-agnostic replacement for tag-based collection systems.
- **Provider**: A definition/extension source (the module's contribution). Avoid implying framework-specific lifecycle or boot methods.
- **Debug module**: An ordinary module whose definitions and extensions add diagnostics, tracing, sandbox config, or inspection without modifying original module code.

## Core Primitives

The primitive layer is deliberately small:

- Keyed lazy definitions.
- Extensions that layer on prior values.
- Deterministic composition order of definition sources.
- Override semantics: later definitions for the same key replace earlier ones.
- Extension semantics: extensions for the same key compose in source order.
- One composed source of truth.

Everything else -- namespacing, aliases, bindings, list services, tagging, integration files, override files, scoped views, composite containers, caching, bootstrap processes, debug modules, and handler pipelines -- is built from these primitives. Do not promote conveniences to primitive status.

## Progressive Layers

The architecture forms a minimal stack of emergent layers. Each layer is explainable in terms of the layers below it.

1. **Primitive layer**: Keys, lazy definitions that receive a container/registry or declared dependencies, extensions, deterministic source order, override semantics, and one composed source of truth.

2. **Service-management layer**: Namespaced/path-like keys, module-local dependency declarations as referenced keys, binding/alias maps between module models, scoped views, list services and tag-like contribution buses, integration seams, and configuration splitting into local/integration/override/extension files.

3. **Composition/bootstrap layer**: Service providers as maps of factories and extensions, composite providers, provider ordering, submodule provider aggregation, composite containers, caching/lazy resolution, proxy/delegating lookup, path/scoped/masked/mapped config views, external configuration sources, and the host bootstrap process that assembles the final source of truth.

4. **Use-case layer**: Debug modules, feature selection, handler pipelines, environment-specific wiring, sandbox credentials, support/issue-tracker integration, framework adaptation, and testing strategy.

Warn against treating helper classes, file naming conventions, or framework facilities as new primitives. If a behavior can be expressed as a key plus definition/extension/binding, it does not need a new mechanism.

## Why Few Primitives Are Enough

Consider what emerges from just keyed lazy definitions, extensions, and deterministic load order:

- **Overrides**: A later source defines the same key. The last definition wins.
- **Integration bindings**: An alias or adapter definition maps one module's key to another module's key.
- **Debug wrappers**: An extension wraps an existing service with tracing, logging, or sandbox credentials.
- **Environment-specific wiring**: The host adds a config source for environment or persisted settings that overrides code defaults.
- **Feature composition**: The host chooses which modules to load. Including or excluding a module adds or removes its definitions/extensions.
- **Event pipelines**: A key holds a handler pipeline; modules contribute handlers through extensions.
- **List services / tagging**: A key holds a list; modules append to the list through extensions. A tag helper is just a convenience for generating this pattern.
- **Submodules**: A module composes other modules' providers into its own provider.
- **Scoped views**: A container wrapper strips or adds prefixes so module-local keys remain clean.

Before adding a new extension mechanism, check whether a key plus definition/extension/binding already expresses the behavior.

## Useful Analogies

**Virtual filesystem (VFS) analogy.** Keys are like filesystem paths: they identify resources in a hierarchy. Namespaces are like directories. Values are lazy-loaded on access, like programs resolved from `$PATH`. Aliases resemble symlinks. Composed configuration sources resemble a VFS -- a virtual filesystem that layers multiple mounts into one unified namespace. Precedence resembles mount order or overlay priority. The application sees one coherent tree; the backing sources can be code, files, databases, environment, or remote config.

**Virtualization analogy.** Modules target a virtual configuration surface: they depend on abstract keys, not on a specific host runtime. The host maps that virtual surface to the real platform, like a hypervisor mapping virtual hardware to physical resources. Composition decides which concrete implementation backs each key.

These analogies are explanatory. They do not imply literal filesystem storage or virtualization APIs.

## Design Workflow

1. **Identify the host boundary.**
   Define what the host owns: module discovery/selection, load order, configuration source layering, runtime startup, platform bridges, persisted settings, secrets, and policy decisions.

2. **Name the composed source of truth.**
   Decide what conceptual registry/container/configuration source modules will read from. Avoid multiple competing sources for the same concern unless they are deliberately layered into one resolution model.

3. **Model module contributions as keys.**
   For each behavior, ask what value is being contributed: a handler, route, command, adapter, policy, UI section, client, repository, pipeline, or data value. Give it a stable key.

4. **Separate definitions from extensions.**
   Use definitions for values that create or replace a key. Use extensions when another module or host layer should decorate, append to, merge with, validate, wrap, or otherwise build on the previous value.

5. **Make load order explicit.**
   Document which sources are loaded first and which are loaded later. Later definitions should have clear replacement behavior. Later extensions should have clear layering order.

6. **Represent dependencies as key references.**
   A module can depend on keys it does not provide. The host or other modules satisfy those keys. This keeps modules portable and makes missing dependencies visible.

7. **Manage service names, local dependencies, bindings, and aliases explicitly.**
   Document which keys each module provides and requires. Bind module-local keys to host/global keys through shared contracts, aliases, adapters, or host-owned integration layers. Treat referenced keys as the module's dependency declarations. In the future, tooling -- CLI inspectors, MCP servers, static analyzers, and AI assistants -- can automate dependency graph inspection, suggest or generate bindings based on type analysis, and detect unsatisfied or circular dependencies. Today, AI can suggest mappings from manifests and code, but mappings must be human-reviewable, testable, and diagnosable. Avoid accidental alias chains, circular bindings, and ambiguous ownership.

8. **Bridge external platforms through keys.**
   Do not make reusable modules call proprietary host APIs directly. The host should expose platform values, callbacks, request contexts, event emitters, settings, database handles, or filesystem abstractions through keys.

9. **Add scoped/namespaced views where needed.**
   In larger systems, use key prefixes, namespaces, or scoped views so module internals remain clear without losing access to the composed source of truth.

10. **Test resolution behavior.**
    Test load order, overrides, extension chains, missing dependencies, bindings/aliases, lazy/cached resolution, persisted/user configuration, platform bridges, and namespaced access. Debugging this architecture depends on being able to explain why a key resolved to a value.

## Design Checklist

- [ ] Modules can be understood as definition and extension sources (service providers) with no lifecycle of their own.
- [ ] The host, not reusable modules, decides module selection and order.
- [ ] There is one composed source of truth for resolved values.
- [ ] Overrides and extensions have distinct, documented semantics.
- [ ] Load order is deterministic and visible.
- [ ] Missing dependencies are expressed as key references, not hidden globals.
- [ ] Platform integration is adapted by the host into keys.
- [ ] Keys are namespaced or scoped enough to avoid collisions, with clear path/namespace ownership per module.
- [ ] Binding/alias maps between modules are explicit, documented, and diagnosable.
- [ ] Definitions are lazy: values are resolved on access, not at composition time (scalars and constants excepted).
- [ ] The composed configuration is immutable once loaded. Changes require restarting or reloading, not in-place mutation.
- [ ] List services or tag-like contribution buses are used where multiple modules need to contribute to a collection.
- [ ] Debug/diagnostic behavior is modeled as ordinary module definitions and extensions.
- [ ] Feature selection happens at composition time (module set), not through scattered runtime flag checks.
- [ ] The design can explain how an add-on changes base behavior without reaching into internals.
- [ ] Tests cover replacement, extension layering, binding resolution, configuration source precedence, lazy/cached behavior, and missing-key failures.

## Review Checklist

When reviewing an architecture, look for these risks first:

- **Hidden dependencies**: modules read globals, singleton state, environment variables, or platform APIs directly.
- **Implicit load order**: behavior changes based on filesystem order, registration timing, or incidental import order.
- **Blurred semantics**: the system cannot distinguish replacing a value from extending it.
- **Multiple sources of truth**: settings, code definitions, runtime state, and user config compete without a clear resolution model.
- **Module-to-module internal coupling**: one module imports another module's internals instead of depending on a documented key.
- **Host leakage**: reusable modules contain proprietary runtime calls instead of consuming host-provided adapters.
- **Ungoverned string keys**: keys collide, drift, or lack ownership conventions.
- **Overly granular definitions**: every tiny expression becomes a key, making resolution hard to reason about.
- **Lifecycle confusion**: modules are given setup/run/boot hooks, blurring the line between providing configuration and executing behavior. A module should be its service provider, not an execution unit.
- **Ambiguous bindings**: cross-module aliases lack clear ownership, create circular references, or produce long redirect chains that are hard to diagnose.
- **Magic provider conventions**: framework-specific auto-discovery, annotation scanning, or lifecycle boot methods hide load order, dependencies, and key ownership.
- **Feature-flag sprawl**: runtime flag checks are scattered through code instead of modeling feature selection as module composition.
- **Debug tools that bypass composition**: diagnostic instrumentation reads internals or globals instead of using composed diagnostic keys and extensions.

## Events And Hooks

Do not treat events or hooks as the foundation of the architecture. Model an event as a key whose value is a handler pipeline.

For example:

```text
key: orders.on_submitted.pipeline
definition: base pipeline with validation and persistence handlers
extension: fraud module appends risk-scoring handler
extension: notifications module appends email handler
extension: audit module wraps pipeline with audit logging
host bridge: runtime subscribes this pipeline to the platform's actual order-submitted event
```

This keeps event participation portable. A reusable module contributes handlers or pipeline layers. The host decides how a real platform event invokes the composed pipeline.

## Replacement And Extension

The two core operations on a key are:

- **Replace**: a later source provides the value for a key instead of an earlier source.
- **Extend**: a later source builds on the previous value, such as wrapping, appending, merging, filtering, or decorating.

Everything else is a use of these two primitives:

- **Aliasing** is a definition (replacement or new key) whose value is looked up from another key.
- **Adapting** is a definition that transforms host-specific or external behavior into a portable value under a key.
- **Deriving** is a definition that computes a value from other keys without changing those dependencies.

Use precise vocabulary in explanations. Distinguish replacement from extension. Aliasing, adapting, and deriving are useful patterns, but they are applications of the same definition primitive, not separate core operations.

## Binding And Alias Management

In systems with multiple modules, keys that connect modules need explicit governance.

A binding connects a key that a consumer expects to a key that a provider supplies. Common patterns:

```text
reporting.db             -> db.primary.connection
orders.payment_client    -> payments.primary.client
shortcode.template_factory -> app.templating.path_template_factory
sdk.auth_token           -> settings.api_key
```

Bindings typically live in host-owned or clearly named integration layers, not inside reusable modules. The host or an integration layer owns each binding and is responsible for keeping it correct when modules change.

A stable abstraction key can be rebound later. For example, `app.visitor_locale` may start as an alias to `app.site_locale` and later be rebound to `geoip.request_locale` by loading an integration module that overrides the alias.

Diagnostics should answer: who provided this key? Who extended it? Which alias redirected it? Which source won? Without these diagnostics, alias chains become a debugging hazard.

### Future: Tooling And Automated Binding

Because the dependency graph is fully expressed through keys and types, significant automation is possible:

- **Dependency graph inspection.** CLI tools or MCP servers can inspect a module set and visualize which keys each module provides, requires, extends, and overrides.
- **Automated binding via type analysis.** When a module's definition declares that it produces a value of a certain type, and a consumer's definition declares that it requires that type, tooling can suggest or generate the alias/binding automatically -- a form of build-time autowiring. Since the dependency graph is immutable once loaded, this does not need to happen at compile time; it can happen at composition time.
- **Unsatisfied dependency detection.** Static analysis can identify keys that are referenced but never defined, suggest packages or modules that satisfy them, and detect circular dependency chains.
- **Documentation generation.** Provided/required key manifests and binding maps can be generated automatically from the configuration structure.

Today, some of this is done with human guidance or AI assistance. The architecture's explicit key-based dependency model makes it a natural fit for progressive automation.

## Service Management Layer

Above the primitives is the service-management layer. These patterns are built from the primitives, not new foundations.

**Namespacing and path-like keys.** Use structured key names to express ownership: `app/checkout/pipeline`, `billing/invoice/create`, `host/db/primary`. Namespaces function like directories: they partition the key space and make ownership visible.

**Module-local dependency declarations.** A module's dependencies are the keys it references but does not define. Documenting provided and required keys per module keeps ownership clear and enables tooling to detect missing or conflicting bindings.

**Integration files and override files.** Split configuration using ordinary language/runtime mechanisms: local definitions, integration/binding definitions, override definitions, and extension definitions. Integration files are binding/adaptation layers between module namespaces. Override files are explicit final replacement layers. This is an organizational convenience built from the same keyed-definition primitive.

**Configuration splitting.** A large provider can be split into multiple files or sections merged in a deterministic order: local factories, settings/config defaults, integration bindings, and final overrides. The merge order determines precedence. No new primitive is needed; ordinary map merging plus the same key-override semantics suffices.

### Common Helper Patterns

Helpers are reusable definition shapes built from the service-definition primitive. They do more than save keystrokes: they provide structure and semantics that benefit humans reading the code, AI tools reasoning about it, and deterministic analysis tools inspecting the dependency graph. A well-chosen helper makes intent explicit and statically analyzable. Helpers should reduce repetition without hiding ordering, dependencies, or key ownership.

- **Alias**: A definition whose value is looked up from another key. Useful for binding, compatibility, and scoping.
- **Constructor/class factory**: A definition that instantiates a class with declared dependency keys. A convenience for the common case of "create object X with dependencies Y, Z."
- **Literal value**: A definition that returns a fixed value. Useful for constants, defaults, and host-injected config.
- **Lazy function service**: A definition that returns a callable which defers resolution of an expensive dependency until invocation time. Useful for breaking circular or conditional resolution chains.
- **Service list**: A definition whose value is a list of other resolved services by key. Extensions can append to the list. This is a language-agnostic replacement for tag-based collection systems.
- **Adapter/mapper**: A definition that transforms or filters a value from another key.

**Tagging as an emergent pattern.** Collecting many contributions under one key does not require a tagging primitive. Define a list service and let modules extend it:

```text
definition:  settings.fields = list_service([settings.field.api_key])
extension:   settings.fields += [settings.field.debug_mode]       (from debug module)
extension:   settings.fields += [settings.field.branding]          (from customizer module)

consumer:    settings.form reads settings.fields and renders them all
```

A tag helper can generate this config automatically, but the underlying mechanism is just a definition plus extensions. Test the consumer, not the tagging machinery.

## Composition And Bootstrap Layer

### Provider Composition

A service provider bundles keyed definitions and extensions. Composing providers means merging their definition and extension maps:

- **Definitions**: later providers override earlier providers for the same key.
- **Extensions**: extensions for the same key compose in provider order; each receives the previous value.

Composite providers aggregate multiple providers into one. Submodule providers can be aggregated into a parent module's provider. The composition is cacheable since it only needs to run once.

### Container And Configuration Composition

Containers compose resolved or externally supplied configuration sources. This is complementary to provider composition:

- **Composite containers** look up a key across multiple containers in order, returning the first match. This layers code-defined services with database config, environment, persisted settings, or remote sources without physically merging them.
- **Caching containers** wrap another container and ensure each key's value is resolved at most once.
- **Proxy/delegating lookup** solves bootstrapping: definitions need to see the final composed source of truth, but it does not exist yet when definitions are being declared. A proxy container is wired after composition completes, so definitions resolve against the full application container.
- **Path containers** allow hierarchical path-like access to nested config.
- **Prefix/deprefix views** slice a configuration segment by adding or stripping key prefixes. This is a general-purpose way to isolate or expose a subset of the key space -- useful for scoping settings, environment sections, or any structured config -- not limited to module boundaries.
- **Masking/mapping containers** filter, expose, or transform keys on the fly.

### Bootstrap Pattern

A generic bootstrap process:

1. Host decides the module set and explicit order.
2. Host collects each module's service provider.
3. Host composes providers into one provider (override/extension order is now locked in).
4. Host creates a module-service container over that provider.
5. Host composes external containers/config sources before or around the module container: host overrides, persisted settings, environment, database, remote config, platform adapters.
6. Host wires lookup delegation so definitions see the final composed source of truth (proxy/forward reference pattern).
7. Host adds caching so definitions run on first access and are reused thereafter.
8. Host bridges platform runtime events/entrypoints to resolved services or handler pipelines.

Modules only provide service providers. The host owns every step of this process.

## Testing The Composition

Tests should target composition semantics, not just happy-path runtime behavior. The architecture's correctness depends on resolution order, key ownership, and lazy behavior.

**Provider order test.** Module A defines `service`, module B overrides it, module C extends it. Assert the final value reflects B's definition decorated by C's extension.

**Binding test.** Consumer module expects `reporting.db`. Host binds it to `db.primary.connection`. Assert successful resolution. Remove the binding and assert a missing-key or not-found error.

**Namespacing/scoping test.** Module reads local `settings.api_key`. Storage uses `app/settings/api_key`. Assert get/has through a scoped/deprefixed view. Assert writes through the scoped view land in the correct prefixed storage.

**List-service/tagging test.** Multiple modules contribute handlers, fields, or path mappings to a list service via definitions and extensions. Assert a consumer receives the collected list in deterministic order.

**Lazy/caching test.** Assert factory invocation count: a definition should run once on first access and return the cached value on subsequent accesses.

**Bootstrap test.** Compose the full application with a test-only override provider/source that satisfies host/platform keys (e.g., debug flags, database handles, file paths). Assert final resolved services match expectations without booting the real platform runtime.

**Circular/conflicting dependency test.** Assert readable resolution-chain diagnostics when a circular dependency is detected or when conflicting bindings exist.

**Recommended harness.** Build a reusable test helper that bootstraps providers/modules with extra test factories/extensions appended as a final override source. This lets each test control the exact composition without requiring the real host environment.

## Conceptual Example

```text
Host selected sources, in order:
1. core module definitions
2. billing module definitions and extensions
3. notifications module definitions and extensions
4. debug module extensions (if loaded)
5. tenant persisted settings
6. host overrides

core definitions:
  app.checkout.pipeline = pipeline([validate_cart, price_order])
  app.payment.client = factory(keys: [host.payment.gateway_config])
  app.db.primary = factory(keys: [host.db.config])

billing definitions:
  billing.invoice.create = factory(keys: [app.payment.client, billing.tax_policy])
  billing.settings.fields = list_service([billing.field.tax_id])

billing extensions:
  app.checkout.pipeline += append_handler(billing.invoice.create)

notifications extensions:
  app.checkout.pipeline += append_handler(notifications.send_receipt)

integration bindings (host-owned):
  reporting.db           -> app.db.primary
  billing.tax_policy     -> tenant.billing.tax_policy
  sdk.auth_token         -> settings.api_key

debug module definitions and extensions (when loaded):
  app.checkout.pipeline  += trace_wrapper(previous)                       (extension: wraps pipeline with tracing)
  app.payment.client     = sandbox_client(keys: [debug.sandbox_config])   (override: replaces with sandbox implementation)
  billing.settings.fields += [debug.field.trace_toggle]                   (extension: adds debug settings field)

tenant settings:
  tenant.billing.tax_policy = persisted_value(...)
  settings.api_key = persisted_value(...)

host overrides:
  app.payment.client = factory(keys: [host.test_gateway_config])
```

The checkout behavior is not hardcoded into the core module. It emerges from deterministic composition. The host can switch payment adapters, satisfy tax policy from persisted settings, load or skip the debug module, and decide extension order. Every module remains encapsulated.

## Debug Modules

A debug module is an ordinary service provider. It does not need special privileges or access to module internals. It contributes definitions and extensions like any other module:

- Wrap services or pipelines with tracing, logging, or timing extensions.
- Override credentials or endpoint keys with sandbox/test values.
- Add diagnostic commands, routes, panels, or health-check endpoints as definitions.
- Connect to external issue trackers or support desks by contributing adapter definitions.
- Expose resolution-chain inspection by extending introspection keys if the host provides them.
- Contribute debug-specific settings fields via list-service extensions.

Because it uses the same override/extension primitives, the debug module can alter behavior comprehensively -- wrapping any service, replacing any configuration value, adding any diagnostics -- without touching a single line of original module code. This is the power of config-based modularity applied to the development workflow itself.

## Feature Flags Are Not Modularity

Feature flags can be values under keys, and they participate in the composed source of truth like any other configuration. But the philosophy is opposite:

- **Config-based modularity**: choose the module set and composed behavior once, at build/composition time. Including or excluding a module adds or removes its definitions, extensions, and bindings. The check happens once.
- **Feature flags**: scatter `if flag` checks through production code. Each check is a runtime branch that can span any volume of code and any number of modules.

A debug module is a clear example of the difference. Instead of littering production code with `if (debug)` checks for logging, sandbox credentials, alternative endpoints, and diagnostic panels, a debug module uses the simple yet powerful override/extension primitives to replace or wrap the relevant services. Every module remains clean. Every behavior change is an explicit composition decision.

Flags can still participate as key values for parameterization, A/B testing, or gradual rollouts. But they should not substitute for module boundaries, dependency declarations, load order, extension semantics, or host-owned integration.

## Relationship To Framework DI And Providers

Many modern frameworks offer similar mechanics: providers, bindings, aliases, decorators, tags, scopes. Config-based modularity shares the same conceptual roots but differs in emphasis:

**Similarities.** Both use keyed service definitions, extensions/decorators, and a composed registry. Both support aliases and scoped lookups.

**Differences and pitfalls in typical framework DI.** Frameworks often add complexity that weakens the primitives:

- Implicit/magic auto-discovery creates hidden load order and makes override behavior unpredictable.
- Proprietary APIs and annotations leak into reusable modules, coupling them to one framework.
- Lifecycle/boot methods in providers execute side effects, blurring the line between configuration and execution. In this pattern, modules are pure config.
- Service-locator usage hides dependencies behind runtime lookups instead of declaring them as keys.
- Ambiguous override semantics make it unclear which definition wins when multiple providers define the same key.
- Multiple competing containers or registries undermine the single source of truth.
- Framework-specific tagging, scoping, or decorator systems become proprietary primitives instead of emergent patterns.

**Control inversion.** In this pattern, modules provide configuration and the host composes, satisfies, and bridges. Modules do not command the host runtime. Many framework systems invert this: modules register with the framework and call framework APIs directly, making them non-portable.

## Common Patterns And Nuances

- **Submodules**: A module can be composed from other modules or definition sources. This is useful for packaging but should not hide load order.
- **Module-local metadata**: Keep ownership, provided keys, required keys, and optional integrations close to the module so it can move into a separate package later.
- **Configuration layering**: Code defaults, files, environment, persisted settings, remote config, and user input can all participate if they resolve through the same source of truth.
- **Scoped configuration**: Large systems benefit from namespaced keys and scoped views that add or strip prefixes. This is a general-purpose way to slice configuration segments, useful for module-local perspectives, environment sections, settings categories, or any structured subset of the key space.
- **Integration groupings**: Files or sections named "integration", "override", or similar are organizational conveniences, not core primitives.
- **No module lifecycle**: A module is its service provider. It does not need a separate setup, boot, or run phase. The host application is responsible for using the composed configuration to drive execution.

## Failure Modes And Anti-Patterns

- Hardcoded globals and service locators that bypass the composed source.
- Proprietary platform calls inside reusable modules.
- Hidden dependency discovery through runtime side effects.
- Implicit load order or undocumented precedence.
- Modules mutating other modules' internals instead of replacing or extending keys.
- Treating event systems as the portability boundary.
- Mistaking helper conventions, file names, or framework APIs for the core pattern.
- Unbounded key proliferation without naming rules, ownership, or diagnostics.
- Configuration sprawl where debugging a key's resolution chain is impractical.
- Tests that verify only happy-path startup and not override/extension behavior.
- Feature-flag sprawl masquerading as modular architecture.
- DI provider boot methods executing side effects instead of providing definitions.
- Auto-discovery or filesystem scanning creating implicit order.
- Unmanaged aliases/bindings obscuring ownership.
- Debug modules using privileged internals instead of composed diagnostic keys.
- Mutating the composed configuration after loading, breaking resolution predictability and cache correctness.

## Tradeoffs

Use this pattern deliberately. It buys portability, host control, add-on extensibility, and predictable composition, but it adds indirection. Teams need naming discipline, resolution diagnostics, binding maps, and tests around composition. Developers may try to give modules their own execution lifecycle instead of keeping them as pure configuration providers; resist this unless there is a clear reason. Analogies such as filesystem paths and virtualization help teach the model, but teams still need concrete naming and governance rules.

## Adapting To Different Ecosystems

- In web frameworks, model routes, middleware, controllers, templates, auth policies, clients, and settings as keys.
- In desktop or mobile apps, model screens, commands, feature flags, storage adapters, background tasks, and platform permissions as keys.
- In event-driven systems, model topics or event names as keys whose values are composed handler pipelines.
- In CLI tools, model commands, option schemas, output renderers, and execution pipelines as keys.
- In data systems, model connectors, transforms, validation rules, sinks, and orchestration steps as keys.

Keep reusable modules dependent on conceptual keys and host-provided adapters. Let the host translate those keys into the ecosystem's concrete runtime.

## When Not To Use This Pattern

- The system is small, has no real add-on boundary, and direct composition is clearer.
- Startup and configuration resolution must be extremely transparent and the team cannot invest in diagnostics.
- The domain requires strict static wiring with little runtime variation.
- The organization cannot maintain key naming rules, load-order policy, or tests.
- The architecture would turn straightforward function calls into excessive indirection without a portability or extensibility payoff.

## Public Implementation References

These are source inspirations and reference implementations, not the prescribed language-agnostic interface:

- Article: [Cross-Platform Modularity in PHP](https://dev.to/xedinunknown/cross-platform-modularity-in-php-30bo) by Anton Ukhanev.
- Simple reference implementation: [wp-oop/plugin-boilerplate](https://github.com/wp-oop/plugin-boilerplate).
- Container/service-provider primitives: [Dhii/containers](https://github.com/Dhii/containers).
- Service provider spec: [container-interop/service-provider](https://github.com/container-interop/service-provider/).
