# TYPO3 Playground

TYPO3 **14.3 LTS** (Composer mode) with a site package, a site set, file-based
backend layouts and a demo Extbase plugin. Built test-first (PHPUnit).

```
config/sites/playground/config.yaml   site configuration, activates set `playground/site`
packages/playground_site/             site package  (composer: playground/site-package)
packages/playground_demo/             demo plugin   (composer: playground/demo-plugin)
tests/Unit/                           PHPUnit tests for both packages + site config
```

## Site set `playground/site` (`packages/playground_site`)

`Configuration/Sets/Playground/`

| File | Purpose |
|---|---|
| `config.yaml` | Set name/label. Depends on `typo3/fluid-styled-content` and `playground/demo`, so activating the site set also activates the plugin's set. |
| `settings.definitions.yaml` / `settings.yaml` | Typed site settings `playground.site.name` and `playground.site.accentColor` (editable in the Sites module, available in Fluid as `{settings.playground.site.*}`). |
| `page.tsconfig` | Imports all `BackendLayouts/*.tsconfig`; sets `TCAdefaults.pages.backend_layout(_next_level)` so new pages and their subpages use `PlaygroundDefault`. |
| `setup.typoscript` | `PAGE` + `PAGEVIEW` and one `page-content` data processor. |

### Backend layouts and how they map to rendering

Three file-based layouts (`BackendLayouts/*.tsconfig`):

| Layout identifier | Columns (`colPos` → `identifier`) |
|---|---|
| `PlaygroundDefault` | 0 → `main` |
| `PlaygroundTwoColumn` | 0 → `main`, 1 → `sidebar` |
| `PlaygroundLanding` | 10 → `hero`, 0 → `main`, 20 → `footer` |

Two conventions connect the pieces:

1. **Layout identifier = template name.** `PAGEVIEW` renders
   `Resources/Private/PageView/Pages/<layout identifier>.fluid.html`
   (layouts in `Layouts/`, partials in `Partials/`). Pages with no layout
   selected resolve to `default`, hence the fallback `Pages/Default.fluid.html`.
2. **Column `identifier` = template variable.** A single `page-content` data
   processor (`as = content`) fetches every column that declares an
   `identifier` and exposes it as `{content.<identifier>}`; templates render it
   with `<f:render.contentArea contentArea="{content.main}" />`. No per-`colPos`
   TypoScript is needed.

**Adding a layout:** add `BackendLayouts/Foo.tsconfig` declaring
`mod.web_layout.BackendLayouts.PlaygroundFoo` with `colPos` + `identifier` per
column, add `Pages/PlaygroundFoo.fluid.html` rendering each identifier, and
extend the data providers in `tests/Unit/Site/BackendLayoutTest.php`.

## Demo plugin (`packages/playground_demo`)

Extbase plugin **Greeting** (`PlaygroundDemo` / `Greeting`, CType/signature
`playgrounddemo_greeting`) that outputs "Good morning, <name>!" depending on
the time of day.

- `GreetingService::greet(string $name, \DateTimeInterface $now)`: pure logic.
  Hours 5–11 morning, 12–17 afternoon, 18–21 evening, otherwise night; a blank
  name becomes `friend`.
- `GreetingController::showAction()` takes "now" from the Core `Context` date
  aspect and the name from `settings.name`. The action is registered
  **non-cacheable** (`ext_localconf.php`) because output depends on the time.
- `Configuration/FlexForms/Greeting.xml` lets editors override `settings.name`
  per plugin instance; registered in `Configuration/TCA/Overrides/tt_content.php`.
- Own site set `playground/demo` (`Configuration/Sets/Demo`) with view paths and
  the default `plugin.tx_playgrounddemo_greeting.settings.name = World`.

## Setup

```bash
composer install            # Composer plugins must be enabled (see below)
vendor/bin/typo3 setup      # interactive; pick SQLite for a zero-dependency start
php -S localhost:8080 -t public
```

Then create a page with id 1 (the site's `rootPageId`), choose one of the
"Playground:" backend layouts, and add the *Greeting (demo)* plugin.

## Tests

```bash
composer test               # = vendor/bin/phpunit -c phpunit.xml.dist
```

Unit tests cover the greeting logic and the structural contract of both
packages (set config, settings definitions, layouts/columns/templates,
plugin registration, FlexForm, site config). They use the plain Composer
autoloader as bootstrap, so no TYPO3 web root is needed.

**Not covered:** end-to-end rendering. The development container that produced
this repository ran Composer with plugins disabled, so TYPO3's web root and
system extensions were not linked and the site was never booted. Before relying
on the setup, run it once with plugins enabled and check the frontend; a
functional test (`typo3/testing-framework`) rendering a page per layout is the
natural next addition.
