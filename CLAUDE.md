# NHR Social Share: project notes

Slug `nhrrob-social-share` · display name "NHR Social Share" (working name) ·
menu label **Social Share** (Settings → Social Share; never "NHR" in the UI).

Private repository, version 0.1. The code came out of the blog's site plugin
(nhrrob/nazmulrobin_blog), which now requires this plugin by its release tags
with Composer, so a release here reaches the live blog in its next Composer
update. A paid add-on, `nhrrob-social-share-pro`, comes later, sold through
the store (nhrrob/nazmulrobin_store). Its plan, decisions and to-do are in
[nhrrob/nhrrob-social-share-pro](https://github.com/nhrrob/nhrrob-social-share-pro)
(`docs/`); until it is built the rotating layouts and their results stay in
the free plugin.

## Read first

- `.ai/PRD.md`: purpose, rules, the free and Pro lists, what is left out, the
  identifiers and the decisions. Check it before proposing a feature.
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
5. **GPL.** Pro never stops working when a licence ends, and nothing is paid
   that other plugins give away free.
6. **Identifiers:** namespace `NHRRob\SocialShare\`, class
   `NHRRob_Social_Share`, constants `NHRROB_SOCIAL_SHARE_*`, option, meta and
   hook prefix `nhrrob_social_share_`, CSS and HTML id prefix
   `nhrrob-social-share-`, REST namespace `nhrrob-social-share/v1`, text domain
   `nhrrob-social-share`.

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
.github/workflows/release.yml  tag v<version> and a release with readme.txt's changelog when Version changes on main
```

## Hooks

- filter `nhrrob_social_share_auto_share` (bool, post): whether a post going live is shared on LinkedIn by itself; true only on a `production` environment.
- filter `nhrrob_social_share_unsaved_pattern` (pattern, time): the layout of a LinkedIn share saved without one (the blog sets its history).
- filter `nhrrob_social_share_card_label` (label, post): the bottom-left line of the card; the first category by default (the blog gives the series).
- filter `nhrrob_social_share_linkedin_redirect_uri` (url): the redirect URL registered in the LinkedIn app (the blog keeps its old one).
- CSS custom property `--nhrrob-social-share-accent`: the colour of the "Copy link" button.

## Conventions

The same as NHR Post Insights (nhrrob/nhrrob-post-insights): WordPress coding
standards, PHP 7.4 syntax, one class per job in `includes/` (PSR-4), hooks in
`init()` with named methods, escape on output and sanitize on input. The
settings screen is a React app built with `@wordpress/scripts` only, light and
dark, in the style of `.ai/DESIGN.md` there. PHPUnit with WP_Mock; PHPCS in
CI. After a change to `admin/src` run `npm run lint:js` and `npm run build`
and commit `admin/build`; after a new class run `composer dump-autoload
--no-dev` and commit `vendor/composer`. A release raises `Version` (header and
`VERSION`) and `Stable tag`, and adds a `readme.txt` changelog section; a
workflow tags `v<version>`, and the blog requires the plugin by that tag with
Composer.

## Branches and pull requests

Work on a branch, open a pull request into `main`. Follow
`.github/pull_request_template.md`. No AI tool attribution in descriptions or
commits. Commit and push only when asked.
