# Agent Guidance

This repository is `dhii/containers`, a PHP library of interoperable container utilities. Use `README.md` as the source of truth for package purpose, supported container types, and examples.

## Project Context

- For architecture involving service providers, keyed definitions, composition, overrides, extensions, scoped configuration, or modularity, use the local skill at `.agents/skills/config-based-modularity`.
- The installable source for that skill is `skills/config-based-modularity`; `.agents/skills/config-based-modularity` is a symlink for local agent discovery.
- Do not duplicate conceptual explanations from `README.md` or the skill. Refer to them and keep changes focused.

## Development Checks

Use the existing CI workflow as the authority for checks: `.github/workflows/ci.yml`.

Relevant local commands are:

```bash
composer validate
vendor/bin/phpunit
./vendor/bin/phpcs -s --runtime-set ignore_warnings_on_exit 1
vendor/bin/psalm
```

## Change Guidance

- Preserve PHP 7.4 compatibility unless the project metadata changes.
- Keep source changes aligned with the existing style rules in `phpcs.xml.dist` and type expectations in `psalm.xml.dist`.
- When changing behavior, add or update tests under the appropriate suite from `phpunit.xml.dist`.
- Avoid committing local opencode plan files under `.opencode/plans/`.
