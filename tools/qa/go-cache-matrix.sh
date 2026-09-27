#!/bin/bash
# /go/ behind a cache-everything edge: clicks, freshness and security through the cache.
# Uses the NORMAL affiliate URL (/go/{slug}/, no bypass parameter). Temporarily edits ONE TEST offer and restores it.
#   EDGE=http://127.0.0.1:6081 HOSTHDR=127.0.0.1:8080 WP=wp DB=wp OFFER_ID=388 PROVIDER_ID=381 ./go-cache-matrix.sh
# Against a real host: EDGE=https://staging.example HOSTHDR= (empty) and WP/DB over SSH.
set -u
W=${WP:-wp}; DB=${DB:-wp}; E=$EDGE; O=$OFFER_ID; PROV=$PROVIDER_ID
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36"
HH=(); [ -n "${HOSTHDR:-}" ] && HH=(-H "Host: $HOSTHDR")
SLUG=$($W post get $O --field=post_name 2>/dev/null)
clicks(){ mariadb -N $DB -e "select count(*) from wp_er_clicks"; }
hdr(){ tr -d '\r' | awk 'tolower($1)~/^(cache-control|age|cf-cache-status|x-gateway-cache-status|x-emu-cache|x-robots-tag):$/{printf "%s ",$0}'; }
# follow the chain through the edge; print hop summaries and set FINAL (last Location leaving the site, or last status)
go(){ local url="$1" n=0 out code loc path; FINAL=""; CHAIN=""
  while [ $n -lt 4 ]; do
    out=$(curl -s -o /dev/null -D - -A "$UA" "${HH[@]}" -H "Referer: $E/destinations/" "$url")
    code=$(echo "$out" | tr -d '\r' | awk '/^HTTP/{c=$2} END{print c}'); loc=$(echo "$out" | tr -d '\r' | grep -i '^location:' | cut -d' ' -f2-)
    CHAIN="$CHAIN\n      hop$n $code $(echo "$out" | hdr)"
    if [[ -n "$loc" && ( "$loc" == */wp-admin/admin-post.php* ) ]]; then path="/${loc#*://*/}"; url="$E$path"; n=$((n+1)); continue; fi
    FINAL="$code $loc"; return
  done; }
check(){ local label="$1" expect="$2" path="$3" before after ok
  before=$(clicks); go "$E$path"; after=$(clicks)
  case "$expect" in partner*) [[ $FINAL == 302\ https://tours.example.org/* ]] && ok=PASS || ok=FAIL;; fallback) [[ $FINAL == 302\ http*://*/experiences/* || $FINAL == 302\ http*://*/destinations/* ]] && ok=PASS || ok=FAIL;; home) [[ $FINAL =~ ^302\ https?://[^/]+/$ ]] && ok=PASS || ok=FAIL;; esac
  local want=0; [[ $expect == partner+click ]] && want=1
  [[ $((after-before)) == $want ]] || ok="$ok(clicks $((after-before))≠$want)"
  printf "%-5s %-40s → %s\n" "$ok" "$label" "${FINAL:0:120}"; [ -n "${VERBOSE:-}" ] && echo -e "$CHAIN"
}
setmeta(){ $W post meta update $O "$1" "$2" >/dev/null 2>&1; }
ORIG=$($W post meta get $O _er_target_url 2>/dev/null)
echo "== 4-request matrix (normal URL, no bypass parameter)"
VERBOSE=1 check "1 /go/$SLUG/" partner+click "/go/$SLUG/"
VERBOSE=1 check "2 /go/$SLUG/ (repeat)" partner+click "/go/$SLUG/"
check "3 /go/$SLUG/?foo=1" partner+click "/go/$SLUG/?foo=1"
check "4 /go/$SLUG/?utm_source=test" partner+click "/go/$SLUG/?utm_source=test"
echo "== tracking: placement and source preserved through the hop"
check "5 ?pl=hero-cta&src=8" partner+click "/go/$SLUG/?pl=hero-cta&src=8"; mariadb $DB -e "select placement, source_post_id, source_path from wp_er_clicks order by id desc limit 1"
echo "== freshness (cached hop must not matter)"
setmeta _er_target_url https://tours.example.org/edited-v9; check "6 URL edited → new target at once" partner+click "/go/$SLUG/"; setmeta _er_target_url "$ORIG"
check "7 URL restored → original at once" partner+click "/go/$SLUG/"
setmeta _er_status paused; check "8 paused → fallback at once" fallback "/go/$SLUG/"; setmeta _er_status active
setmeta _er_end 2020-01-01; check "9 expired → fallback" fallback "/go/$SLUG/"; $W post meta delete $O _er_end >/dev/null 2>&1
$W post meta update $PROV _er_status inactive >/dev/null 2>&1; check "10 provider inactive → fallback" fallback "/go/$SLUG/"; $W post meta update $PROV _er_status active >/dev/null 2>&1
$W post update $O --post_status=trash >/dev/null 2>&1; check "11 deleted (trashed) → home, no partner" home "/go/$SLUG/"; $W post update $O --post_status=publish >/dev/null 2>&1
check "12 restored → partner" partner+click "/go/$SLUG/"
echo "== security through the edge"
check "13 open redirect ?url=evil" partner+click "/go/$SLUG/?url=https://evil.test/&to=https://evil.test"
for bad in "https://evil.test/x" "https://example.org.evil.test/x" "https://example.org@evil.test/" "javascript:alert(1)" "//evil.test/x" "http:///x"; do setmeta _er_target_url "$bad"; check "target $bad" fallback "/go/$SLUG/"; done; setmeta _er_target_url "$ORIG"
check "14 unknown slug" home "/go/no-such-offer-xyz/"
check "15 direct endpoint, crafted offer" home "/wp-admin/admin-post.php?action=er_go&offer=../../wp-config"
check "16 direct endpoint, utm override" partner+click "/wp-admin/admin-post.php?action=er_go&offer=$SLUG&utm_source=evil&subid=evil"
echo "restored target: $($W post meta get $O _er_target_url 2>/dev/null)  status: $($W post get $O --field=post_status 2>/dev/null)"
