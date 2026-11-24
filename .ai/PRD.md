# NHR Social Share: scope

First written 2026-11-17 (nhrrob/nazmulrobin_blog#139). Built and running the
blog since 0.1; prepared for WordPress.org in 0.3. Decisions are in
[`docs/DECISIONS.md`](../docs/DECISIONS.md).

Slug `nhrrob-social-share` · display name "NHR Social Share" · menu label
**Social Share** (Settings → Social Share; never "NHR" in the UI). The name
is a working name until Robin confirms it.

## Purpose

Share each post where its readers are, in the way that brings them back, and
show which way works, from WordPress and without an outside service in
between. It grew out of the blog's own needs (nazmulrobin.com/blog, 1.10.0 to
1.30.0), so every feature has been used on a live site before it ships.

## Rules

1. **Nothing between the site and the network.** No service of ours or anyone
   else's relays a share: the site uses its own LinkedIn app and keys, and
   every other network through its own page. A buyer's token never leaves
   their site.
2. **Nothing tracks the reader.** The share buttons are plain links to the
   networks' own share pages: no script, pixel, cookie or count from any
   network is loaded.
3. **By hand where an API costs, forbids or breaks.** X charges per post,
   Facebook lets no app post to a personal profile, Medium takes no new
   integrations and Reddit approves each app by hand and removes links posted
   by scripts. For those the plugin opens the network's page with the post
   filled in, copies what the page cannot take, and remembers what was used.
4. **Small.** A network or feature is added when it is needed and used, not to
   lengthen a list. No share counts (the networks removed them), no URL
   shortener, no floating bars.
5. **GPL**, and complete on its own. Hooks let another plugin add to it
   (see `CLAUDE.md`, "Hooks"); nothing here waits for one.

## Free, version 0.1 (built 2026-11-17: what the blog had, made to work on any site)

- **Share buttons** under each post: LinkedIn, X, Facebook, Reddit, WhatsApp,
  Email, with line icons and the word; which ones and their order in the
  settings.
- **Link previews:** Open Graph and X card tags, and a share picture drawn
  from the title (1200 × 630) for posts without a featured image, in the
  site's colours and a bundled font. Off when an SEO plugin already prints
  them.
- **LinkedIn:** connect the site's own LinkedIn app (OAuth, warning before the
  60-day token ends), share to the personal profile, Automatic (on first
  publish, including scheduled posts) or Manual (a link in the Posts list).
  Each post is shared once; a failure shows why.
- **Share by hand:** in the Posts list, links for X, Facebook, Reddit and
  Medium that open the network's page with the post filled in; Facebook's
  text and Medium's link are copied on the click. Used links read "again";
  a **Shared on** box keeps where it was posted.
- **Reddit targets** in the settings: default subreddits and/or the
  `u/name` profile every post gets, changeable per post.
- **Per-post texts** in a box in the post editor: the LinkedIn text (also
  used for Facebook), the X text (with a 280-character count), the Reddit
  targets. A site that writes posts from files (like the blog) fills the same
  post meta through a filter.
- **Settings → Social Share**, a React app in the style of Robin's other
  plugins (`@wordpress/scripts` only, light and dark): Share buttons, Link
  previews, LinkedIn, Share by hand, Results (`.ai/DESIGN.md`).

## Also free, since 0.1

- **Layouts that take turns** on LinkedIn, X and Facebook: A a link card, B a
  picture with the link in the text, C a picture with the link in the first
  comment (X: in a reply). Each post takes the one used least, or a forced
  one; the Posts list helps with each (download picture, reply with the link,
  copy the link for the comment).
- **Results:** visits from each network per layout in the first 7 days after
  a share, from NHR Post Insights when it is active.

## Left out on purpose

- **Posting to X, Facebook, Reddit or Medium through an API**, for the reasons
  in rule 3. Looked at on 2026-10-12, 2026-11-03 and 2026-11-17.
- **A LinkedIn or Facebook Page.** Shares go to the person's profile, which
  reaches their network; a Page reaches only its followers. Can be added if
  users ask.
- **Instagram, TikTok, Threads, Bluesky, Mastodon** until someone needs one.
- **Post analytics from the networks' APIs** (impressions, likes): LinkedIn
  needs Community Management approval (blog #92, #120). Visits to the site are
  counted instead.
- **Share counts**, a share bar that follows the scroll, and click tracking on
  the buttons (rule 2).

## Identifiers

Namespace `NHRRob\SocialShare\`, class `NHRRob_Social_Share`, constants
`NHRROB_SOCIAL_SHARE_*`, option, meta and hook prefix `nhrrob_social_share_`
(meta starting with `_`), CSS and HTML id prefix `nhrrob-social-share-`, REST
namespace `nhrrob-social-share/v1`, text domain `nhrrob-social-share`.

## Moving the blog onto it (done in 0.1)

The blog's code moves as listed in #139: `includes/Linkedin/`,
`includes/AuthorShare/`, `ShareLinks`, `ShareMeta`, `ShareImage`,
`InsightsNetworks` and their assets and tests. On activation the plugin copies
the blog's option `nazmulrobin_linkedin` and the post meta
`_nazmulrobin_linkedin_*`, `_nazmulrobin_author_share*`, `_nazmulrobin_x_text`
and `_nazmulrobin_reddit` to its own keys, once, so the connection and every
share's history carry over. The blog's history constants
(`Pattern::COMMENT_FROM`, `TEXT_FROM`) are set by the blog through the filter
`nhrrob_social_share_unsaved_pattern`, its series label on the card through
`nhrrob_social_share_card_label`, its LinkedIn redirect URL through
`nhrrob_social_share_linkedin_redirect_uri`, and staging is kept from
sharing through `nhrrob_social_share_auto_share`. The blog requires the
plugin by tag with Composer, like Post Insights.

## Decisions

In [`docs/DECISIONS.md`](../docs/DECISIONS.md).
