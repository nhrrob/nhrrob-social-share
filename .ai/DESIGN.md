# DESIGN — Social Share settings app

> Dev-only document. Implemented in `admin/src/style.scss` (tokens) and `admin/src/components/ui.js` (primitives). Screenshots of 0.1 are in `.ai/screens/`.

## 1. Direction

A sibling of Post Insights, Secure and Database Cleaner: the same shell (gradient app bar, collapsible left nav, one screen per section), the same token structure and the same primitives, copied from Post Insights' `admin/src`. Its accent is **indigo**, so it is related to the others but not confused with them. wp-admin density and the system font stack. No framework, no icon font, no images: CSS custom properties and inline SVG only.

The name shown in the UI is **Social Share**. "NHR" never appears.

## 2. Tokens (`.nhrrob-social-share-app`, prefix `--ss-`)

| Token | Light | Dark | Use |
|---|---|---|---|
| `--ss-primary` | `#4338ca` | `#a5b4fc` | focus ring, panel icons |
| `--ss-primary-600` | `#4f46e5` | `#8b9bff` | links, soft-button text |
| `--ss-grad-a` → `--ss-grad-b` | `#3730a3` → `#6366f1` | `#2e2a78` → `#4f46c8` | app bar, active nav, primary button |
| `--ss-soft` | `#eef0ff` | `#1f2238` | soft button, hover |
| semantic (`--ss-success`, `-warning`, `-danger`, `-info`, `-bg`) | as in Post Insights | as in Post Insights | pills, notes, toasts |
| surfaces, borders, text | as in Post Insights | as in Post Insights | |

**Dark theme:** the tokens are redefined under `@media (prefers-color-scheme: dark)` guarded by `:not([data-theme="light"])`, and again under `[data-theme="dark"]`, so the app-bar toggle wins both ways. `html:has(...)` paints wp-admin's canvas. The choice is kept in `localStorage` (`nhrrob-social-share-theme`), the nav state in `nhrrob-social-share-nav`.

## 3. Components

From Post Insights: Panel, Pill (`ok | warn | bad | info | off`), Button (`primary | soft | ghost | danger`, `small`), Field, Note, Grid, External, Toast. Added here: `checks` (a bordered list of rows with a checkbox and up/down buttons), `choices` (radio or checkbox with a line of help under the label), `swatches` (colour inputs with their hex), `card` (the drawn card at 1200:630), `copyrow` (a value with a Copy button), `steps` (a numbered list).

## 4. Screens

- **Share buttons**: the networks with a checkbox each, the ticked ones first, in order, with up and down. One primary button.
- **Link previews**: on the left, Automatic / Always / Never with a pill saying whether the tags are printed and a note naming an SEO plugin; on the right, the card of the latest post. Below, the five card colours.
- **LinkedIn**: the connection with its pill (Connected, Expires soon, Expired, Not connected) and Connect / Reconnect / Disconnect; the sharing mode; then the four steps to make the LinkedIn app, the redirect URL with Copy, the Client ID and the write-only Client Secret.
- **Share by hand**: the networks with what each one can and cannot do; the default Reddit targets; the layouts switch.
- **Results** (separated in the nav): one table per network (LinkedIn, X, Facebook): layout, shares, visits in the first 7 days, per share. A note when Post Insights is not active.
- **Post editor panel** (block editor sidebar, core components only): LinkedIn and Facebook text, X text with a 280-character count, Reddit, LinkedIn layout.

Below 782px the nav becomes a row above the content.

## 5. Copy

Plain words from the site owner's side. Say what each network allows and why ("Facebook lets no link fill in a post's text, so the click copies…"). Errors say what went wrong and what to do next.
