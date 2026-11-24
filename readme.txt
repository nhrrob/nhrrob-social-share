=== NHR Social Share ===
Contributors: nhrrob
Tags: social share, linkedin, open graph, share buttons, privacy
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Share buttons, link previews with a drawn card, LinkedIn sharing from your own app, and X, Facebook, Reddit and Medium filled in by hand.

== Description ==
NHR Social Share helps you share your posts and helps readers share them, without a service in between and without tracking anyone.

### For readers
- **Share buttons** under each post: LinkedIn, X, Reddit, Facebook, WhatsApp, Email and Copy link, in the order you choose. They are plain links to each network's own share page: no script, cookie or counter from any network.
- **Link previews**: Open Graph and X card tags, so a shared link shows the title, the excerpt and a picture. A post without a featured image gets a card drawn from its title, in your colours. Next to an SEO plugin that prints its own tags, it steps aside.

### For you
- **LinkedIn**: connect your own LinkedIn app and share each post to your profile, automatically when it is first published or from the Posts list. The keys and the token stay on your site.
- **Share by hand** where an API costs money or is not allowed: X, Facebook, Reddit and Medium open from the Posts list with the post filled in. What a page cannot take in its address (Facebook's text, Medium's link) is copied for you to paste. Used links say "again", and the post's "Shared on" box keeps where it was posted.
- **Reddit targets**: default subreddits and your own profile (u/name) for every post, or a post's own list.
- **Three layouts take turns** on LinkedIn, X and Facebook (link card; picture with the link in the text; picture with the link in the first comment or a reply), and **Results** shows which one brings readers back, with the visits counted by NHR Post Insights.
- **A Social Share panel** in the post editor for the post's LinkedIn and Facebook text, its X text with a 280-character count, its Reddit targets and its LinkedIn layout.

Settings → Social Share has a screen for each, in a light and a dark theme.

== Installation ==
1. Install and activate the plugin from Plugins → Add New, or upload the zip.
2. Open Settings → Social Share and choose the buttons and their order. They appear under each post at once.
3. For LinkedIn, follow the steps on the LinkedIn tab: create your own LinkedIn app, add its redirect URL, then save its Client ID and Secret and connect.
4. For Results, install NHR Post Insights, which counts the visits.

== External services ==
The plugin connects to LinkedIn only when you connect it and when a post is shared there:

* **LinkedIn** (www.linkedin.com and api.linkedin.com): to authorize the connection with your own LinkedIn app, read your member id and name, upload the post's picture and post the share and its first comment. Sent: your app's Client ID and Secret during the connection, then the post's text, link, title, excerpt and picture. LinkedIn's terms: https://www.linkedin.com/legal/user-agreement and privacy policy: https://www.linkedin.com/legal/privacy-policy

The share buttons and the share-by-hand links are ordinary links that open the network's page in the browser; the plugin itself sends nothing to X, Facebook, Reddit, Medium, WhatsApp or anyone else.

== Frequently Asked Questions ==

= Why are X and Facebook not posted automatically like LinkedIn? =
X charges for every post made through its API, and Facebook lets no app post to a personal profile. The plugin opens their page with the post filled in, and you press Post.

= Why does the X text have to be short? =
Without X Premium a post can have at most 280 characters. A link counts as 23.

= Does it work with Yoast SEO, Rank Math and the like? =
Yes. On "Automatic" the link preview tags are left to them. The share buttons and the sharing work the same.

= Do I need another plugin for Results? =
Results counts the visits each share brought, which needs NHR Post Insights (free) to count visits. Without it, everything else works and Results shows no visits.

= Where is the source code? =
On GitHub: https://github.com/nhrrob/nhrrob-social-share. The settings screen and the editor panel are React apps; their source is in `admin/src` in the plugin, and `npm install && npm run build` rebuilds `admin/build`.

= What is removed when I delete the plugin? =
Its settings, the LinkedIn connection and the share texts and history it kept with each post. The drawn cards stay in the uploads folder, under nhrrob-social-share.

== Screenshots ==

1. The share buttons under a post: plain links, in the order you choose.
2. Settings → Social Share: which buttons and their order.
3. Link previews: the tags, and the card drawn for posts without a featured image, in your colours.
4. LinkedIn: connect your own LinkedIn app, then share automatically or from the Posts list.
5. Share by hand, in the dark theme: X, Facebook, Reddit and Medium from the Posts list, Reddit targets and the rotating layouts.
6. The Posts list: a share link for each network under each post.

== Changelog ==

= 0.3.0 =
* Ready for WordPress.org: a shorter summary, screenshots, and answers on Results and on where the source is.
* The plugin zip no longer carries development files (`.eslintrc.js`, `docs/`).

= 0.2.1 =
* The Results screen and the Post Insights tiles get their visits from NHR Post Insights 0.6.0's `Read` functions instead of querying its tables, so a change to how Post Insights stores its numbers no longer affects them. With an older NHR Post Insights they show no visits.

= 0.2.0 =
* Hooks for add-ons: the filters `nhrrob_social_share_picture` (featured image or card), `nhrrob_social_share_text` (a share's text) and `nhrrob_social_share_results` (the Results tables), the action `nhrrob_social_share_shared`, and the JS filter `nhrrob_social_share.screens` (more tabs on the settings screen). Nothing changes for a site without an add-on.
* The link preview and the shared picture always agree: both follow the picture the post's shares carry.

= 0.1.1 =
* Runs on PHP 8.6 without a deprecation notice: the REST class no longer has a constant named "namespace".

= 0.1.0 =
* First version, from the code that ran nazmulrobin.com/blog's sharing: share buttons, link previews with a drawn card, LinkedIn sharing from your own app, X, Facebook, Reddit and Medium by hand, Reddit targets, three rotating layouts with their results, and a Social Share panel in the post editor.
* Settings → Social Share, in a light and a dark theme.
* A site that ran that code keeps its LinkedIn connection and every post's share history: they are copied once on activation.
