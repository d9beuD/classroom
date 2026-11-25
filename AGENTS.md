# AGENTS.md

This is a Symfony project. Check `composer.json` for the exact Symfony/PHP version
in use, and read `symfony.lock` to see which recipes ran. Don't assume Doctrine,
Twig, API Platform, Messenger, or Lock are installed unless one of those says so.

## Project stack

Already decided, don't ask:

- Persistence: Doctrine ORM, with migrations and fixtures (`src/DataFixtures`).
- Interface: server-rendered Twig with Symfony Form. No API.
- Auth: SecurityBundle with `form_login`.
- Front end: AssetMapper + importmap (no Node bundler), Stimulus, Symfony UX
  Turbo, Twig Components and Live Components, Bootstrap 6 (alpha).

## Project conventions

- **Entities, no DTOs.** Forms, Live Components and services work on plain
  Doctrine entities directly. Don't add DTOs, mappers or request objects.
- **Enums where relevant.** A field with a fixed set of values is a backed PHP
  enum in `src/EntityEnum/`, mapped with `enumType:` on the column and
  `EnumType` in forms. Labels shown to users live on the enum (`label()`).
- **Doctrine lifecycle logic** goes in `src/EntityListener/` with
  `#[AsEntityListener]`; shared entity contracts in `src/EntityInterface/`.
- **Turbo gives the SPA feel.** Pages navigate and forms submit through Turbo
  Drive, so:
  - JS that sets up the page belongs in a Stimulus controller's `connect()`/
    `disconnect()`, never in `DOMContentLoaded` or inline scripts, which won't
    re-run after a Turbo visit.
  - A form submitted through Turbo must answer a failed submission with a 422
    (Symfony's `render()` with a submitted, invalid form already does) and a
    success with a redirect.
  - Use Turbo Frames / Streams for partial updates before writing custom fetch
    code.
- **TypeScript only.** New front-end code is TypeScript (`.ts`) under `assets/`,
  compiled by `sensiolabs/typescript-bundle` and typed by `tsconfig.json`
  (`strict`). The remaining `.js` files are recipe-generated; leave them. Add
  third-party packages with `bin/console importmap:require <pkg>`, not npm.
- **Icons are Lucide.** Use `<i data-lucide="icon-name"></i>` in Twig, or in
  chapter section content. `assets/controllers/bootstrap_controller.ts` renders
  every Lucide icon, so any name from https://lucide.dev/icons works with
  nothing to register.
- **Twig Components first.** A reusable piece of UI is a Twig Component
  (`src/Twig/Components/` + `templates/components/`), or a Live Component when
  it needs server round-trips, rather than an `include` or a macro.
- **User-facing text is hardcoded in French.** The app is single-language: no
  translation keys, no `trans` filter, no files in `translations/`.

## Adding features: Flex, not hand-wiring

Install new capabilities with `composer require <package>` (e.g. `symfony/lock`,
`symfony/messenger`, `orm-pack`) and let the Flex recipe register the bundle and
generate its config. Don't hand-edit `config/bundles.php` or hand-write a bundle's
base config; that's what the recipe is for. Don't skip a good-fit component just
because it isn't installed yet; installing it is one command.

## Conventions

Follow https://symfony.com/doc/current/best_practices.html to write idiomatic
Symfony:

- Use PHP attributes for framework metadata, and not only on controllers:
  `#[Route]`, `#[MapRequestPayload]`, `#[IsGranted]` on actions, `#[Assert\...]`
  on properties, `#[AsCommand]`, `#[AsEventListener]`, `#[AsMessageHandler]`, and
  `#[AsAlias]` / `#[AsTaggedItem]` / `#[Autoconfigure]` on services. No YAML or
  XML routing.
- Rely on autowiring and autoconfiguration. Type-hint constructor arguments and
  let the container resolve them. Where a type-hint can't express it, stay in the
  class with `#[Autowire]` (parameters, env vars, expressions) or `#[Target]` (one
  of several implementations of an interface). A YAML service definition is the
  last resort, not the first.
- Controllers extend `AbstractController`, stay thin, and delegate to services.
- Use the framework for what it already does: Form for server-rendered forms,
  Validator for validation, Serializer for JSON, Messenger for async work,
  Security (voters, authenticators) for access control, Twig `path()`/`url()`
  instead of hardcoded URLs.
- Before hand-writing infrastructure (locks, queues, caches, HTTP clients,
  mailers, schedulers) or reaching for a third-party library, check whether a
  Symfony component covers it. It usually does.

Three specifics worth spelling out, because they are easy to get wrong:

- Bind request data with `#[MapRequestPayload]` / `#[MapQueryString]` on action
  arguments, which wires up Serializer and Validator for you, instead of calling
  `json_decode()` or `SerializerInterface` by hand. If neither package is
  installed yet, `composer require` them rather than falling back to manual
  parsing.
- Use constructor property promotion, and `readonly` for value objects (this
  project doesn't use DTOs, see above).
- Use `symfony/lock` (`LockFactory`) for mutual exclusion. A hand-built flag or
  lock file looks fine in review and is usually wrong under concurrency.

## Everyday workflow

- The app runs in Docker only (no `symfony serve`). `make up` starts it on
  http://localhost:8000 (phpMyAdmin on :8080); `make init` builds and sets it up
  from scratch. Run every PHP command inside the `app` container:
  `docker compose exec app bin/console ...`, `docker compose exec app composer ...`.
  The `renderer` service (Shiki + Mermaid) is only reachable from `app`.
- When something fails, read `var/log/dev.log` and the web profiler
  (`/_profiler`) before changing code.
- If `maker-bundle` is installed, prefer `bin/console make:*` with every argument
  passed up front and `--no-interaction` where supported: makers prompt on a
  terminal by default, which hangs a non-interactive shell. If a maker still
  needs interactive input, hand-write the code instead.
- If Doctrine ORM is installed, schema changes go through migrations
  (`bin/console make:migration`, then `doctrine:migrations:migrate`), never
  `doctrine:schema:update` or hand-written SQL.
- `.env` is committed and holds defaults only. Real secrets belong in `.env.local`
  (git-ignored) or the secrets vault (`bin/console secrets:set`), read via
  `%env(...)%`.

## Testing

Functional/HTTP tests extend `WebTestCase`; service-level tests extend
`KernelTestCase`. Run `docker compose exec app bin/phpunit`.

Tests run against SQLite, not the MariaDB used in dev: `tests/bootstrap.php`
builds the schema from the Doctrine mapping (the migrations are MySQL-only) and
loads `src/DataFixtures/AppFixtures` on every run. So:

- A new entity or field gets fixture data in `AppFixtures`, and tests rely on
  that data instead of creating their own setup.
- Stick to portable DQL/QueryBuilder; no MySQL-only SQL or functions.
- Tests share one database and see each other's writes. A feature isn't done
until it has a test that exercises it the way a caller would, an HTTP request for
a controller or a service call for a service, not just "it didn't throw."

## Code style

Symfony's coding standard, the `@Symfony` php-cs-fixer ruleset (a PSR-12-derived
superset). Run `vendor/bin/php-cs-fixer fix` if `friendsofphp/php-cs-fixer` is
installed; it isn't part of the skeleton by default.

## Commits and pull requests

Commit messages and pull request titles follow
[Conventional Commits](https://www.conventionalcommits.org/): `feat: …`,
`fix(chapter): …`, `feat!: …` for a breaking change, `chore:`/`ci:`/`docs:`/
`refactor:`/`test:` for the rest. Release Please reads them to pick the next
version and write the changelog, and each release deploys to production; a
commit without a type is left out of both. A CI check rejects a pull request
whose title doesn't comply, and a squash merge uses that title as the commit
message.

## Discover, don't guess

Framework APIs change between versions and your training data may be stale. Look
things up in the project instead of relying on memory:

- `bin/console about`: versions, environment, paths.
- `bin/console debug:router`, `debug:container`, `debug:autowiring <name>`,
  `debug:config <bundle>`, `config:dump-reference <bundle>`: what exists and how
  it is configured.
- `bin/console lint:container`, plus `lint:twig templates/` and
  `lint:yaml config/` where those packages are installed: validate before running.
- Read the installed source and docblocks under `vendor/`.
- Docs: https://symfony.com/doc/current/ (switch to the version matching
  `composer.json` if it differs).
