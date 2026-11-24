# NHR Social Share

Share each post where its readers are, from WordPress, without a service in
between and without tracking readers: share buttons, link previews with a
drawn card, LinkedIn from the site's own app, and X, Facebook, Reddit and
Medium filled in by hand, with three rotating layouts and their results.

Version 0.3, prepared for WordPress.org. It grew out of the sharing code of the blog
at nazmulrobin.com/blog (nhrrob/nazmulrobin_blog), which now requires it by
its release tags with Composer. The scope and the decisions are in
[`.ai/PRD.md`](.ai/PRD.md), the screens in [`.ai/DESIGN.md`](.ai/DESIGN.md),
the project notes in [`CLAUDE.md`](CLAUDE.md), the decisions in
[`docs/DECISIONS.md`](docs/DECISIONS.md), the open work in the issues.

## Development

```
composer install            # dev tools: PHPUnit, WP_Mock, PHPCS
npm install
npm run build               # admin/build: the settings app and the editor panel
npm run lint:js
composer phpcs
composer test:unit
```

`admin/build` and the production autoloader in `vendor/` are committed: a
site that installs the plugin from git (as the blog does with Composer) runs
it without a build. Before committing a change to `composer.json` or a new
class, run `composer dump-autoload --no-dev`.

## Releases

By hand: raise the version, merge into `main`, then push the tag
`v<version>`. The tag push sends it to WordPress.org and makes the GitHub
release. A push to `trunk` updates only the readme and the listing's images. The steps are
in [`CLAUDE.md`](CLAUDE.md#releases).

The listing's icon and banners are drawn from `docs/wporg/icon.html` and
`banner.html`: `node docs/wporg/render.mjs` (needs Playwright) writes them
into `.wordpress-org/`.
