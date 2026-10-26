#!/usr/bin/env bash
# Rebuild the local Egypt Roamer site from the repo, in the order production was built.
#   ./reset.sh       English only
#   ./reset.sh ml    all 8 languages (Polylang), guide translations and legal pages, like production
# The current database is copied to wordpress/wp-content/database/backup-<time>.sqlite first.
# Local trips get no offers (offers and partner URLs live only in the production database).
set -euo pipefail
cd "$(dirname "$0")"
WP=./wp.sh
DB=wordpress/wp-content/database/.ht.sqlite

if [ -f "$DB" ]; then
  cp "$DB" "wordpress/wp-content/database/backup-$(date +%Y%m%d-%H%M%S).sqlite"
  rm -f "$DB"
fi

$WP core install --url=http://127.0.0.1:8080 --title="Egypt Roamer" --admin_user=admin --admin_password=admin \
  --admin_email=admin@example.test --skip-email
$WP rewrite structure '/%postname%/'
$WP theme activate egypt-roamer
if [ "${1:-}" = "ml" ]; then
  $WP plugin activate polylang
fi
$WP plugin activate egypt-roamer-core

if [ "${1:-}" = "ml" ]; then
  $WP egypt-roamer languages
  $WP egypt-roamer seed --translations --publish-translations
else
  $WP egypt-roamer seed
fi
$WP egypt-roamer editorial
if [ "${1:-}" = "ml" ]; then
  for l in de fr it es ru zh ar; do $WP egypt-roamer editorial --lang="$l"; done
  # Legal pages are published on production; their translations follow from data/legal.
  for slug in privacy-policy cookies affiliate-disclosure contact; do
    id=$($WP post list --post_type=page --name="$slug" --post_status=any --field=ID --lang=en 2>/dev/null | head -1 || true)
    if [ -n "$id" ]; then $WP post update "$id" --post_status=publish >/dev/null; fi
  done
  $WP egypt-roamer legal
  $WP eval 'echo wp_json_encode( er_guide_translations_sync( false )["counts"] ), "\n";'
fi
$WP rewrite flush
$WP egypt-roamer health || true
echo "Done: http://127.0.0.1:8080/ (start it with: python proxy.py)"
