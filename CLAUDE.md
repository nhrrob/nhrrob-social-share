# NHR Social Share: project notes

Slug `nhrrob-social-share` · display name "NHR Social Share" (working name) ·
menu label **Social Share** (Settings → Social Share; never "NHR" in the UI).

Version 0.3, public, prepared for WordPress.org (2026-11-24), not yet submitted.
The code came out of the
blog's site plugin (nhrrob/nazmulrobin_blog), which requires this plugin by
its release tags with Composer, so a release here reaches the live blog in its
next Composer update.

## Read first

- `.ai/PRD.md`: purpose, rules, what is built, what is left out and the
  identifiers. Check it before proposing a feature.
- `docs/DECISIONS.md` (one row per decision) and `docs/notes/YYYY-MM-DD.md`
  (one per working session): update both in the same pull request.
- `.ai/DESIGN.md` and `.ai/screens/`: the settings app and the editor panel.
- The open issues, in their numbered order. When asked to "continue", say in
  a few lines where each stands, then work through them from the top.

## Rules

1. **Nothing between the site and the network.** No relay service; the site
   uses its own LinkedIn app and keys, every other network through its own
   page.
2. **Nothing tracks the reader.** Share buttons are plain links: no script,
   pixel, cookie or count from a network.
3. **By hand where an API costs, forbids or breaks** (X, Facebook profile,
   Reddit, Medium): open the network's page filled in, copy what it cannot
   take, remember what was used.
4. **Small.** A network or feature is added when it is needed.
5. **GPL**, and complete on its own. Add-ons extend it only through the hooks
   below.
6. **Identifiers:** namespace `NHRRob\SocialShare\`, class
   `NHRRob_Social_Share`, constants `NHRROB_SOCIAL_SHARE_*`, option, meta and
   hook prefix `nhrrob_social_share_`, CSS and HTML id prefix
   `nhrrob-social-share-`, REST namespace `nhrrob-social-share/v1`, text domain
   `nhrrob-social-share`.
7. **Public repository.** Pricing, competitor research, positioning and plans
   for a paid version never go in this repo, its issues or its pull requests.
   They live in the private add-on repo's `docs/`.

## Layout

```
nhrrob-social-share.php   main class, constants, FEATURES (each class's init())
uninstall.php             deletes the options and post meta, per site
includes/
  Options.php      the settings option: buttons, previews, card colours, hand networks, Reddit targets, rotate
  Keys.php         every post meta key, in one place
  Admin.php        Settings → Social Share (mount point, boot data), the Posts list script
  Rest.php         GET/POST /settings, GET /results (manage_options)
  PostFields.php   register_post_meta for the editor panel, enqueues admin/build/editor.js
  Migration.php    copies the blog's old option and post meta once, never over new ones
  Buttons/         Buttons (under the content), Icons
  Preview/         Meta (Open Graph and X card tags), Card (the drawn PNG)
  Linkedin/        Api, Connect (OAuth), Settings (the connection option), Share, Image, Pattern, PostActions
  Hand/            Links (each network's page and what is copied), Layout, RowActions (Posts list), Urls ("Shared on" box)
  Stats/           Networks (Post Insights tiles), Visits (shares and visits per layout)
assets/            buttons.css and buttons.js (front end), hand.js (Posts list), fonts/ for the card
admin/src/         React: index.js + app.js + screens/ (Buttons, Previews, Linkedin, Hand, Results), editor.js (the editor panel)
admin/build/       compiled, committed
tests/             PHPUnit + WP_Mock
.github/workflows/php.yml      PHPCS, then per PHP version: syntax, unit tests, smoke test in a real WordPress
.github/workflows/plugin-check.yml  WordPress.org's Plugin Check on the files the zip holds
.github/workflows/deploy-on-pushing-a-new-tag.yml           a v<version> tag sends the release to WordPress.org (SVN) and makes the GitHub release
.github/workflows/deploy-readme-asset-on-pushing-on-trunk.yml  a push to trunk updates only the readme and the listing's images
.wordpress-org/    icon, banners and screenshots of the WordPress.org listing
docs/wporg/        their sources: icon.html, banner.html, render.mjs (Playwright)
```

## Hooks

- filter `nhrrob_social_share_auto_share` (bool, post): whether a post going live is shared on LinkedIn by itself; true only on a `production` environment.
- filter `nhrrob_social_share_unsaved_pattern` (pattern, time): the layout of a LinkedIn share saved without one (the blog sets its history).
- filter `nhrrob_social_share_card_label` (label, post): the bottom-left line of the card; the first category by default (the blog gives the series).
- filter `nhrrob_social_share_linkedin_redirect_uri` (url): the redirect URL registered in the LinkedIn app (the blog keeps its old one).
- filter `nhrrob_social_share_picture` (source, post): `featured` or `card`, the picture of a post that has a featured image (its shares and og:image). For an add-on.
- filter `nhrrob_social_share_text` (text, post, network): a share's text on `linkedin`, `facebook` or `x`, before the layout adds the link. For an add-on.
- filter `nhrrob_social_share_results` (results): the Results tables; an add-on appends to `networks` (optional `column`, `icon`). `Stats\Visits::rows()` takes its own keys for that. For an add-on.
- action `nhrrob_social_share_shared` (post_id, network): after a LinkedIn share, and each time a share-by-hand link is used. For an add-on.
- JS filter `nhrrob_social_share.screens` (screens, { ui, useToast }): more tabs on the settings screen, as { id, label, Screen }. For an add-on.
- CSS custom property `--nhrrob-social-share-accent`: the colour of the "Copy link" button.

## Conventions

The same as NHR Post Insights (nhrrob/nhrrob-post-insights): WordPress coding
standards, PHP 7.4 syntax, one class per job in `includes/` (PSR-4), hooks in
`init()` with named methods, escape on output and sanitize on input. The
settings screen is a React app built with `@wordpress/scripts` only, light and
dark, in the style of `.ai/DESIGN.md` there. PHPUnit with WP_Mock; PHPCS in
CI. After a change to `admin/src` run `npm run lint:js` and `npm run build`
and commit `admin/build`; after a new class run `composer dump-autoload
--no-dev` and commit `vendor/composer`.

## Releases

Releases are made by hand, as in NHR Post Insights; nothing is tagged on a
version change.

1. On a branch: raise `Version` in the header and `VERSION`, `Stable tag` and
   `package.json`'s `version`, and add a `= x.y.z =` section under
   `== Changelog ==` in `readme.txt`. Merge it into `main`.
2. Tag `main` and push the tag: `git tag v<version> && git push origin
   v<version>`. The tag push, not a GitHub release, runs
   `deploy-on-pushing-a-new-tag.yml`, which sends the version to WordPress.org
   with the `SVN_USERNAME` and `SVN_PASSWORD` secrets, and makes the GitHub
   release with that version's changelog; don't make one by hand. Until
   WordPress.org approves the plugin and the secrets are set, the deploy job
   fails; the GitHub release and the tag still work for the blog.
3. To change only the readme or the listing's images, push to `trunk`
   (`deploy-readme-asset-on-pushing-on-trunk.yml`). Bring `trunk` up to
   `main` after each release.

The blog requires the plugin by these tags with Composer, so a release
reaches it in Dependabot's next pull request.

## Branches and pull requests

Work on a branch, open a pull request into `main`. Follow
`.github/pull_request_template.md`. No AI tool attribution in descriptions or
commits. Commit and push only when asked.
