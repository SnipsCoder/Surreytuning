# Stages, commits, and (optionally) pushes the current branding/vehicle-stats/CSRF
# changes to origin/master. Run from the repo root in PowerShell.
#
# This does NOT touch production — it only prepares and pushes the git history that
# scripts/deploy-production.sh later pulls on the server.

$ErrorActionPreference = "Stop"

$files = @(
    "app/Http/Controllers/Owner/VehicleStatController.php",
    "app/Http/Controllers/BrandingController.php",
    "resources/views/components/layouts/client.blade.php",
    "resources/views/components/layouts/owner.blade.php",
    "resources/views/emails/layout.blade.php",
    "resources/views/invoices/pdf.blade.php",
    "resources/views/layouts/auth.blade.php",
    "resources/views/owner/vehicle-stats/index.blade.php",
    "routes/tenant.php"
)

Write-Host "== git status before staging ==" -ForegroundColor Cyan
git status --porcelain

Write-Host "`n== staging files ==" -ForegroundColor Cyan
git add -- $files

Write-Host "`n== staged diff stat ==" -ForegroundColor Cyan
git diff --cached --stat

$commitMessage = @"
Add private R2 branding/logo delivery, fix vehicle-stats search, correct CSRF middleware alias

- BrandingController streams the portal logo through /branding/logo instead of
  exposing the R2 disk URL directly (falls back portal_logo -> logo_dark -> logo_light)
- Update layouts and invoice PDF header to use the new fallback chain / route
- Owner vehicle-stats: replace cascading auto-submit Make/Model/Engine/Fuel selects
  with a single form + explicit Filter button and client-side option repopulation;
  fix VehicleStatController to always populate results with like-wildcard matching
- routes/tenant.php: use PreventRequestForgery instead of the deprecated
  VerifyCsrfToken alias for the webhook exemption and route middleware import
"@

Write-Host "`n== commit message ==" -ForegroundColor Cyan
Write-Host $commitMessage

git commit -m $commitMessage

Write-Host "`n== commit created ==" -ForegroundColor Cyan
git log -1 --oneline

Write-Host "`nReady to push to origin/master." -ForegroundColor Yellow
Write-Host "This script stops here deliberately - review the commit above, then run:" -ForegroundColor Yellow
Write-Host "  git push origin master" -ForegroundColor Yellow
