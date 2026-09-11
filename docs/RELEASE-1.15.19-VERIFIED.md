# AI-HTML 1.15.19 release verification

2026-09-11: supersedes the candidate status in SBS-ROUTING-HOTFIX-2026-09-11.md.

- Release commit 93dd8a4; GitHub Actions run 34568453922 succeeded.
- Public manifest version 1.15.19 and package checksum verified.
- SHA-256: fe8e2586bfa25f49f7885315f9456cbb5b1b22b78ab850ac7facf1832460fd11.
- Installed on smartecommerce.it after private theme backup; WP-CLI reports 1.15.19.
- Home and Products HTTP 200, SBS output and aihl-smart-builder-template present.
- Page templates and database content were not changed.
- Home and Builder remain available without Compose; Blog supports Compose.
- The updater source directory retains the trailing slash required after moving
  the archive root, preserving the style.css package installation fix.
- Central signed Hub/Store catalog synchronization is not claimed by this record.
