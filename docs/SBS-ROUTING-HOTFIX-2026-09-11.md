# AI-HTML / SBS routing hotfix

On smartecommerce.it, AI-HTML 1.15.17 declared only Smart Site Blog support.
SBS 1.23.0 consequently rejected existing Home and Builder templates and
fell back to the theme page renderer. Do not migrate or recreate page data
to correct a theme capability regression.

## Contract

| Template | Builder | Compose |
| --- | --- | --- |
| smart-site-home.php | yes | no |
| smart-site-builder.php | yes | no |
| smart-site-blog.php | yes | yes |

All three templates retain the `aihl-smart-builder-template` body class and
remain selectable through the AI page API. Template support and Compose
support are independent capabilities.

## Live repair

Applied only inc/theme/support.php, inc/theme/utilities.php and
inc/integrations/ai-api.php to the installed theme. Server backup:
`/var/www/vhosts/smartecommerce.it/private/aihtml-sbs-fix-20260911/before.tar.gz`.
No database writes or page template changes were performed.

Verified HTTP 200, restored SBS markup and body class on / and /prodotti/.
Verified homepage visible in Chrome. Page 26 still uses smart-site-home.php;
page 134 still uses smart-site-builder.php. All three deployed PHP files
pass the server PHP syntax check.

The local 1.15.18 candidate includes the fix and a routing runtime test using
real SBS source. CI pins SBS v1.23.0 for this test. This repair is a live
hotfix, not a published 1.15.18 release: installed metadata and public
repository still report 1.15.17 until a separate release is published.
