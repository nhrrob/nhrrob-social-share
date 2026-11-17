# NHR Social Share: project notes

Slug `nhrrob-social-share` · display name "NHR Social Share" (working name) ·
menu label **Social Share** (Settings → Social Share; never "NHR" in the UI).

Private repository, nothing built yet. The code comes out of the blog's site
plugin (nhrrob/nazmulrobin_blog, `includes/Linkedin/`, `includes/AuthorShare/`,
`ShareLinks`, `ShareMeta`, `ShareImage`, `InsightsNetworks`), which runs it
live today. A paid add-on, `nhrrob-social-share-pro`, comes later, sold
through the store (nhrrob/nazmulrobin_store).

## Read first

- `.ai/PRD.md`: purpose, rules, the free and Pro lists, what is left out, the
  identifiers and how the blog moves onto the plugin. Check it before
  proposing a feature.
- The open issues, in their numbered order: the R&D issue comes first, then
  the 0.1 issues. When asked to "continue", say in a few lines where each
  stands, then work through them from the top.

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

## Conventions

The same as NHR Post Insights (nhrrob/nhrrob-post-insights): WordPress coding
standards, PHP 7.4 syntax, one class per job in `includes/` (PSR-4), hooks in
`init()` with named methods, escape on output and sanitize on input. The
settings screen is a React app built with `@wordpress/scripts` only, light and
dark, in the style of `.ai/DESIGN.md` there. PHPUnit with WP_Mock; PHPCS in
CI. A release raises `Version` and adds a `readme.txt` changelog section; a
workflow tags `v<version>`, and the blog requires the plugin by that tag with
Composer.

## Branches and pull requests

Work on a branch, open a pull request into `main`. Follow
`.github/pull_request_template.md`. No AI tool attribution in descriptions or
commits. Commit and push only when asked.
