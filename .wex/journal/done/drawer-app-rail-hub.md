# Tool hub with an icon rail and an embedded app frame (2018 member dashboard)

Opened: 2026-10-09
Updated: 2026-10-13
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page linked below.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

Drawers page: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-outside.md.j2`

## What it is

The 2018 "dashboard" of the cooperative (GitLab `wexample/dashboard`): one page that gathers the member tools (site, chat, projects, drive, videos) behind a vertical icon rail, the chosen tool opening in a full-height iframe. Concept for a member portal / app switcher; trivial code.

## Where

`/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/outside/wexample_dashboard/project/{index.html,js/script.js,css/theme.css}` (13 commits 2018-05-16 → 05-21, last `516ad69`). `weeger/dashboard` is an empty scaffold.

## Look and behaviour

White 60 px left rail with an inset shadow, Font Awesome icons (home, chat, projects, drive, videos); selected icon white on black, the others black at 0.2 opacity; page background `#222`, Quicksand font. Click → the tool's URL loads in the right-hand iframe (full height).

## Proposed shape (suggestion)

A DS layout variant "rail + content" (the rail is a vertical nav of icon buttons with tooltips and an active state); network 2027 could use it as an "apps" switcher linking to chat, drive, etc. (links or embeds, since most tools refuse iframes today).

## Open questions

1. Is an app switcher to the cooperative's tools wanted in network 2027?

## Decision (2026-10-13, owner)

Dropped: nothing to build. A vertical toolbar of the design system beside a zone holding an iframe or a `document_embed` gives the same screen, the day an application wants one.
