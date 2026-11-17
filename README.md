# NHR Social Share

Share each post where its readers are, from WordPress, without a service in
between and without tracking readers: share buttons, link previews with a
drawn card, LinkedIn from the site's own app, and X, Facebook, Reddit and
Medium filled in by hand, with three rotating layouts and their results.

Private repository, version 0.2. It grew out of the sharing code of the blog
at nazmulrobin.com/blog (nhrrob/nazmulrobin_blog), which now requires it by
its release tags with Composer. The scope and the decisions are in
[`.ai/PRD.md`](.ai/PRD.md), the screens in [`.ai/DESIGN.md`](.ai/DESIGN.md),
the project notes in [`CLAUDE.md`](CLAUDE.md), the open work in the issues.

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

Raise `Version` in the plugin header and `VERSION` in the class, `Stable tag`
in `readme.txt`, and add a `= x.y.z =` section under `== Changelog ==`.
Merging that into `main` runs `.github/workflows/release.yml`, which tags
`v<version>` and makes a GitHub release with that changelog section. The
tags and releases are the log of what each version contained; the blog
picks a new one up in Dependabot's next Composer pull request.
