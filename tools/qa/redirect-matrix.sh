#!/bin/bash
# Affiliate redirect security matrix. Temporarily edits ONE test offer/provider and restores them.
# Never run against production data.
#   WP="wp --path=/srv/site" OFFER_ID=388 PROVIDER_ID=381 FALLBACK_ID=8 DB=wp BASE=http://127.0.0.1:8080 ./redirect-matrix.sh
# FALLBACK_ID = the post the offer is attached to (its internal fallback page). The offer's provider must allow example.org.
set -u
W=${WP:-wp}; B=${BASE:-http://127.0.0.1:8080}; DB=${DB:-wp}; O=$OFFER_ID; PROV=$PROVIDER_ID
SLUG=$($W post get $O --field=post_name 2>/dev/null)
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36"
FALLBACK=$($W post url $FALLBACK_ID 2>/dev/null)
ORIG_URL=$($W post meta get $O _er_target_url 2>/dev/null)
clicks(){ mariadb -N $DB -e "select count(*) from wp_er_clicks"; }
hit(){ # label expect path
  local out; out=$(curl -s -o /dev/null -A "$UA" -D - "$B$3" | tr -d '\r')
  local code=$(echo "$out" | head -1 | awk '{print $2}'); local loc=$(echo "$out" | grep -i '^location:' | cut -d' ' -f2-)
  local xr=$(echo "$out" | grep -i '^x-robots-tag:' | cut -d' ' -f2-); local cc=$(echo "$out" | grep -i '^cache-control:' | cut -d' ' -f2-)
  local res="$code"; case "$2" in partner) [[ $code == 302 && $loc == https://* && $loc != "$B"* ]] && ok=PASS || ok=FAIL;; fallback) [[ $code == 302 && $loc == "$FALLBACK"* ]] && ok=PASS || ok=FAIL;; 404) [[ $code == 404 ]] && ok=PASS || ok=FAIL;; esac
  [[ $xr == *noindex* ]] || ok="$ok(no X-Robots noindex)"
  printf "%-5s %-44s %s %s | X-Robots=%s | CC=%s\n" "$ok" "$1" "$code" "${loc:0:110}" "$xr" "$cc"
}
seturl(){ mariadb $DB -e "update wp_postmeta set meta_value='$1' where post_id=$O and meta_key='_er_target_url'"; }
c0=$(clicks)
hit "valid offer → partner" partner "/go/$SLUG/?pl=gate&src=8"
c1=$(clicks); echo "      click logged for GET: $((c1-c0)) row"
curl -s -o /dev/null -I -A "$UA" "$B/go/$SLUG/"; c2=$(clicks); echo "      HEAD logged: $((c2-c1)) rows"
curl -s -o /dev/null -A "Googlebot/2.1" "$B/go/$SLUG/"; c3=$(clicks); echo "      bot logged: $((c3-c2)) rows"
hit "open-redirect attempt ?url=evil" partner "/go/$SLUG/?url=https://evil.test/&to=https://evil.test/"
hit "src non-digit / CRLF injection" partner "/go/$SLUG/?src=1%0d%0aSet-Cookie:x=1&pl=%0d%0aX:y"
for bad in "https://evil.test/x" "https://example.org.evil.test/x" "https://example.org@evil.test/" "javascript:alert(1)" "//evil.test/x" "http:///x" "ftp://example.org/x" "https://evil.test/?r=example.org"; do seturl "$bad"; hit "target $bad" fallback "/go/$SLUG/"; done
seturl "$ORIG_URL"
hit "invalid slug" 404 "/go/no-such-offer/"
hit "uppercase slug" 404 "/go/GIZA-SUNRISE-ACCEPTANCE-OFFER-2/"
hit "traversal" 404 "/go/..%2f..%2fwp-config.php/"
hit "draft offer" 404 "/go/$($W post list --post_type=er_offer --post_status=draft --field=post_name 2>/dev/null | head -1)/"
$W post meta update $O _er_status paused >/dev/null 2>&1; hit "paused offer" fallback "/go/$SLUG/"; $W post meta update $O _er_status active >/dev/null 2>&1
$W post meta update $O _er_end 2020-01-01 >/dev/null 2>&1; hit "expired offer" fallback "/go/$SLUG/"; $W post meta delete $O _er_end >/dev/null 2>&1
$W post meta update $O _er_start 2099-01-01 >/dev/null 2>&1; hit "not started offer" fallback "/go/$SLUG/"; $W post meta delete $O _er_start >/dev/null 2>&1
$W post meta update $PROV _er_status inactive >/dev/null 2>&1; hit "inactive provider" fallback "/go/$SLUG/"; $W post meta update $PROV _er_status active >/dev/null 2>&1
D=$($W post meta get $PROV _er_domains 2>/dev/null); $W post meta update $PROV _er_domains "" >/dev/null 2>&1; hit "provider without domains" fallback "/go/$SLUG/"; $W post meta update $PROV _er_domains "$D" >/dev/null 2>&1
$W post update $O --post_status=trash >/dev/null 2>&1; hit "trashed (deleted) offer" 404 "/go/$SLUG/"; $W post update $O --post_status=publish >/dev/null 2>&1
hit "restored offer" partner "/go/$SLUG/"
echo "--- tracking params on final Location:"; curl -s -o /dev/null -A "$UA" -w "%{redirect_url}\n" "$B/go/$SLUG/?pl=gate&src=8"
echo "--- robots.txt:"; curl -s $B/robots.txt | grep -i go
echo "--- /go/ in sitemaps:"; for s in $(curl -s $B/wp-sitemap.xml | grep -o '<loc>[^<]*' | cut -c6-); do curl -s "$s"; done | grep -c '/go/'
echo "--- tracking parameters (utm + sub-ID placeholders; visitor cannot override):"
$W post meta update $O _er_utm_source egyptroamer >/dev/null 2>&1; $W post meta update $O _er_params $'subid={placement}-{page}' >/dev/null 2>&1
curl -s -o /dev/null -A "$UA" -w "%{redirect_url}\n" "$B/go/$SLUG/?pl=gate&src=$FALLBACK_ID&utm_source=evil&subid=evil"
$W post meta delete $O _er_utm_source >/dev/null 2>&1; $W post meta delete $O _er_params >/dev/null 2>&1
