# Decisions

Why NHR Social Share is the way it is, and whether each choice can be undone.
One row per decision, newest last. A pull request that makes a decision adds
its row; reversing one strikes the old row and adds a new one. The rows up to
2026-11-17 come from the decision table that was in `.ai/PRD.md`.

| Date | Decision | Why | Can it be undone? | Who |
| --- | --- | --- | --- | --- |
| 2026-11-17 | Sharing leaves the blog's site plugin for a plugin of its own | It is stable, about 2,500 lines, and built from real use (nhrrob/nazmulrobin_blog#139) | Hard: the blog requires it now | Robin |
| 2026-11-17 | The rotating layouts and Results are in the free plugin | Robin's call; the blog uses them every day | Moving a feature out later takes it from users who have it | Robin |
| 2026-11-17 | Automatic LinkedIn shares only on a `production` environment | A staging or local copy holds the live site's token and must never post. The blog keeps its own staging check through the filter | Yes, through `nhrrob_social_share_auto_share` | Robin |
| 2026-11-17 | Facebook and X by hand, not by API | Facebook lets no app post to a personal profile; X charges per post, so X texts stay under 280 characters (blog #134, #135) | Yes, if the networks change | Robin |
| 2026-11-17 | Hooks for add-ons in 0.2.0 (#14): picture, text, results, shared, and the screens JS filter | Each does nothing without an add-on, so the plugin stays complete on its own | Hard once an add-on uses them | Robin |
| 2026-11-24 | The repository is public and the plugin goes to WordPress.org (#25), as NHR Post Insights did | Free plugins are listed on WordPress.org; plans for a paid version move to the private add-on repo first | Making it private again does not hide what was public | Robin (made public 2026-11-24; submitting is Robin's step) |
| 2026-11-24 | Releases by hand: ~~a GitHub release with~~ a pushed tag `v<version>` deploys to WordPress.org (corrected by Robin the same day: the tag push is the trigger, no GitHub release), `trunk` updates the listing; `release.yml`, which tagged on every version change, is removed | A tag now publishes to WordPress.org, so it must be a choice, as in NHR Post Insights and NHR Secure | Yes | Agent, for Robin to confirm |
| 2026-11-24 | `docs/` (decisions, notes, listing sources) leaves the zip | Dev-only documents, as in NHR Post Insights | Yes | Agent |
