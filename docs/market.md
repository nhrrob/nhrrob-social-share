# Market check: NHR Social Share

Checked 2026-11-17 with web searches (issue #1). Most prices come from
third-party reviews and comparison sites, which disagree with each other; the
vendors' own pricing pages were not opened. Treat each figure as "about", and
check a vendor's page before quoting it anywhere.

## The plugins

| Plugin | What it does | Personal LinkedIn profile | Price seen | Notes |
|---|---|---|---|---|
| [Jetpack Social](https://jetpack.com/social/) | Auto-share on publish, re-share, schedule | Yes | Free: "unlimited shares" on Jetpack's feature page, but its FAQ still says 30 per 30 days; paid about $9.95/month ($119.40/year) | Runs through Jetpack/WordPress.com servers |
| [Blog2Social](https://wordpress.org/plugins/blog2social/) | Auto-post and scheduler, 12–14 networks | Free (pages from Pro) | Premium about $7–25/month | 1 account per network in free |
| [Revive Social (Revive Old Posts)](https://revive.social/) | Shares old posts again on a schedule | Unclear in free | Business about $199/year for 3 sites | A May 2026 review reported LinkedIn shares failing on a retired API |
| [FS Poster](https://www.fs-poster.com/) | Auto-post to many networks | Yes | From about $58/year | Its own comparisons are the main source |
| [Social Publisher](https://wordpress.org/plugins/social-publisher/) | Auto-post to LinkedIn | Free | Pro about €9/month per site | |
| [WP LinkedIn Auto Publish](https://wordpress.org/plugins/wp-linkedin-auto-publish/) | Auto-post to LinkedIn | Free | Free | A 2025 report of a PHP warning |
| [Nelio Content](https://neliosoft.com/content/) | Editorial calendar and social queue, Mastodon and Bluesky | Yes | About $9/month | No share-format testing found |
| [Novashare](https://novashare.io/) | Fast share buttons, counts, click-to-tweet block | n/a | From about $29/year | Share-count recovery |
| [Social Snap](https://socialsnap.com/) | Share buttons, counts, click-to-tweet, auto-post in Pro | n/a | About $27–69/year | |
| [Social Warfare](https://warfareplugins.com/) | Share buttons, counts, click-to-tweet | n/a | About $29–39/year | Most installed of the three |

Sources: [FS Poster on LinkedIn plugins](https://www.fs-poster.com/blog/wordpress-plugins-for-linkedin),
[Jetpack Social plans FAQ](https://jetpack.com/support/jetpack-social-plans-frequently-asked-questions/),
[WP Tavern on Jetpack Social's paid plan](https://wptavern.com/jetpack-social-plugin-adds-paid-plan-free-users-now-limited-to-30-shares-per-month),
[Blog2Social pricing (SocialRails)](https://socialrails.com/blog/blog2social-pricing),
[Revive Social pricing](https://ecommerceparadise.com/revive-social-pricing/),
[Revive Old Posts review](https://www.fs-poster.com/blog/revive-old-posts-review),
[Novashare review](https://daveswift.com/novashare/),
[Social Snap vs Social Warfare](https://www.wmtips.com/technologies/compare/social-snap-vs-social-warfare/),
[Nelio Content on Capterra](https://www.capterra.com/p/174871/Nelio-Content/).

## Answers

1. **Who buys it.** Not checked with buyers. The plugins above sell to
   bloggers and small businesses who want posts to reach social networks
   without logging in to each. Ours fits a writer who shares to their own
   LinkedIn and wants to know which way of sharing brings readers back.
2. **Free elsewhere:** share buttons; Open Graph tags (any SEO plugin);
   auto-posting to a personal LinkedIn profile (Blog2Social, Social Publisher,
   WP LinkedIn Auto Publish); re-sharing and scheduling (Jetpack Social's
   free plan, if its feature page is right). **Charged for elsewhere:** pages
   and many accounts, queues and schedules, share counts and click-to-tweet.
3. **Pro list under rule 5.**
   - *Rotating layouts and visits per layout*: no plugin found that tests share
     formats against site visits. Keep as the Pro candidate.
   - *Pictures tried the same way*: same; Pro.
   - *Share again later*: Jetpack Social re-shares for free (unconfirmed limit),
     Revive Social charges. Free, or left out, not Pro.
4. **Our edges.** No relay service (Jetpack and the SaaS-backed ones route
   through their servers), no tracking on the buttons, copying the text for
   networks that take none, and measuring layouts by visits. The last one is
   the only thing found nowhere else.
5. **Networks.** Nelio Content added Mastodon and Bluesky; both have open
   APIs that allow posting, unlike X and Facebook profiles. Bluesky is the
   first candidate for posting by API. Pages (LinkedIn, Facebook) are what the
   paid plans of others charge for.
6. **Price.** Share-button plugins sit at about $27–39 per site per year;
   auto-posters at $58–199 per year. A Pro that holds the layout tests and
   their results fits about $39–49 per site per year, next to Post Insights
   Pro.
7. **wordpress.org.** The LinkedIn connection uses the site owner's own app
   and keys, and the readme names the service ("External services"), which is
   what the guidelines ask. The copied text and the redirect links open the
   network's page on the owner's click. IBM Plex is under the SIL Open Font
   License, which allows bundling. Not yet checked against a reviewer.
8. **Name.** Not checked for conflicts on wordpress.org; "Social Share" is a
   generic phrase used by many plugins, so the slug `nhrrob-social-share`
   (with the author prefix) is what keeps it unique.

## To verify before selling

- Each price on the vendor's own page.
- Jetpack Social's free share limit today.
- Whether any plugin added share-format testing since this check.

## 2026-11-17, later: the Pro ideas (nhrrob/nhrrob-social-share-pro#4)

Checked with web searches and the plugins' own wordpress.org pages, after the
rotating layouts and Results were kept free. Each idea from pro#4 against rule
5 (nothing paid that another plugin gives free). Installs and dates are as the
wordpress.org pages showed them that day. "Not found" means a search did not
find it, not that it does not exist.

| # | Idea | Free elsewhere? | Charged for elsewhere | Verdict |
|---|---|---|---|---|
| 1 | Pictures tried the same way, scored by visits | Not found | [OpenGraph.xyz](https://wordpress.org/plugins/opengraph-xyz/) (100+ installs) says its short links "A/B test your OG images"; it needs an OpenGraph.xyz account (trial with a card) and serves images from its own servers, which our rule 1 rules out | **Passes** |
| 2 | Text variants, scored by visits | Not found | [Revive Social](https://docs.revive.social/article/941-revive-old-post-free-vs-pro) has share variations in its paid plans only, picked at random and not scored | **Passes** |
| 3 | Best time to share, from the site's own visits | Not found | [Blog2Social](https://www.blog2social.com/en/faq/content/7/1/en/is-blog2social-free.html)'s Best Time Manager is Premium, with set times per network, not times from the site's data (one third-party review says it is free; the vendor's FAQ says Premium) | **Passes** |
| 4 | More than one LinkedIn account | Partly: [WP LinkedIn Auto Publish](https://wordpress.org/plugins/wp-linkedin-auto-publish/) (free, 7,000+) shares to a profile and several company pages at once | Blog2Social (one account per network in free) | **Fails** for pages; more than one personal profile not found free, but too small for Pro |
| 5 | LinkedIn and Facebook Pages | Yes: WP LinkedIn Auto Publish and Devenia Autoposter for LinkedIn post to company pages free | Blog2Social, FS Poster | **Fails** |
| 6 | Bluesky and Mastodon by API | Yes: [Autoblue](https://wordpress.org/plugins/autoblue/) (Bluesky, free), XPoster (Bluesky and Mastodon, about 10,000 installs) | Nelio Content | **Fails**: free later, if wanted |
| 7 | LinkedIn document (carousel) posts from a post | Not found as a WordPress plugin; outside tools (Publer, Buffer, Typefully) post PDFs | Those tools are paid services | **Passes on the market; unconfirmed on the API.** Microsoft's Documents API page lists `w_member_social`, but it sits under Community Management, and only a third-party guide says a self-serve app can post a document to a personal profile |
| 8 | X thread from a post, by hand | Yes, with AI: [Radical Writes Content Repurposer](https://wordpress.org/plugins/radical-writes-content-repurposer/) drafts a thread in its free tier; Jetpack published threads in 2021 (not checked now) | | **Fails**, or too close to free |
| 9 | Share texts drafted by AI | Yes: [Advanced Social Share Assistant](https://wordpress.org/plugins/famety-social-share-assistant/) (free, with the owner's own OpenAI key) | Bit Social's AI looks paid | **Fails** |

Other sources: [Revive Social variations](https://docs.revive.social/article/971-how-to-add-variations-to-revive-old-posts-shares),
[LinkedIn Documents API](https://learn.microsoft.com/en-us/linkedin/marketing/community-management/shares/documents-api),
[LinkedIn Posts API](https://learn.microsoft.com/en-us/linkedin/marketing/community-management/shares/posts-api),
[third-party guide on PDF posts by API](https://opentweet.io/answers/can-you-post-a-linkedin-pdf-carousel-via-api),
[Devenia Autoposter for LinkedIn](https://wordpress.org/plugins/devenia-autoposter-for-linkedin/).

**What passes:** ideas 1, 2 and 3 have one thing in common. Each is a test the
site runs on its own shares and scores in the free Results screen. Nobody was
found doing that free, and the paid tools that come near do it without
scoring (Revive) or through their own servers (OpenGraph.xyz). Idea 7 passes
the market check but waits on the LinkedIn API.

**Not done:** asking buyers. Nothing here says anyone would pay for these.
