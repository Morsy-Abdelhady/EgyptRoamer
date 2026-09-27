#!/bin/bash
# Verifies the two-step /go/ architecture through a cache-everything edge, using the PLAIN URL
# /go/{slug}/ (no bypass parameter). Creates its own test providers A/B and a test offer on
# reserved example.com / example.net hosts, and deletes them at the end. Never run on production data.
#   EDGE=http://127.0.0.1:6081 HOSTHDR=127.0.0.1:8080 WP=wp DB=wp FALLBACK_ID=8 ./go-architecture.sh
set -u
W=${WP:-wp}; DB=${DB:-wp}; E=$EDGE; FB=${FALLBACK_ID}
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36"
HH=(); [ -n "${HOSTHDR:-}" ] && HH=(-H "Host: $HOSTHDR")
SLUG="arch-test-offer-$RANDOM"; PASSN=0; FAILN=0
wpq(){ $W "$@" 2>/dev/null; }
clicks(){ mariadb -N $DB -e "select count(*) from wp_er_clicks"; }
mkprov(){ local id; id=$(wpq post create --post_type=er_provider --post_status=publish --post_title="ARCH TEST provider $1" --porcelain)
  wpq post meta update $id _er_domains "$2" >/dev/null; wpq post meta update $id _er_website "https://$2/" >/dev/null; wpq post meta update $id _er_status active >/dev/null; echo $id; }
PA=$(mkprov A a.example.com); PB=$(mkprov B b.example.net)
O=$(wpq post create --post_type=er_offer --post_status=publish --post_title="ARCH TEST offer" --post_name=$SLUG --porcelain)
for kv in "_er_provider $PA" "_er_target_url https://a.example.com/tour" "_er_experience $FB" "_er_status active" "_er_utm_campaign archtest" "_er_cta check_availability"; do wpq post meta update $O $kv >/dev/null; done
FALLBACK=$(wpq post url $FB); HOME_URL=$(wpq option get home)/
echo "test providers A=$PA (a.example.com) B=$PB (b.example.net), offer=$O /go/$SLUG/, fallback=$FALLBACK"

# One click through the edge. Sets FINAL ("status location") and prints both hops.
req(){ local path="$1" out0 out1 st0 st1 loc0 loc1
  out0=$(curl -s -o /dev/null -D - -A "$UA" "${HH[@]}" -H "Referer: $E/destinations/" "$E$path" | tr -d '\r')
  st0=$(echo "$out0" | awk '/^HTTP/{c=$2} END{print c}'); loc0=$(echo "$out0" | grep -i '^location:' | cut -d' ' -f2-)
  HOP0="hop0 $path → $st0 [$(echo "$out0" | grep -iE '^(x-emu-cache|cf-cache-status|age|cache-control):' | tr '\n' ' ')] → ${loc0#*://*/}"
  if [[ "$loc0" == */wp-admin/admin-post.php* ]]; then
    out1=$(curl -s -o /dev/null -D - -A "$UA" "${HH[@]}" -H "Referer: $E/destinations/" "$E/${loc0#*://*/}" | tr -d '\r')
    st1=$(echo "$out1" | awk '/^HTTP/{c=$2} END{print c}'); loc1=$(echo "$out1" | grep -i '^location:' | cut -d' ' -f2-)
    HOP1="hop1 admin-post → $st1 [$(echo "$out1" | grep -iE '^(x-emu-cache|cf-cache-status|age|cache-control):' | tr '\n' ' ')] → $loc1"
    FINAL="$st1 $loc1"; INJ=$(echo "$out0$out1" | grep -ciE '^(x: y|set-cookie: x=1)')
  else HOP1="(no second hop)"; FINAL="$st0 $loc0"; INJ=$(echo "$out0" | grep -ciE '^(x: y|set-cookie: x=1)'); fi; }
# t <label> <path> <expect-final-regex> <expected click delta> [verbose]
t(){ local b a ok; b=$(clicks); req "$2"; a=$(clicks)
  if [[ "$FINAL" =~ $3 ]] && [ $((a-b)) -eq $4 ] && [ "${INJ:-0}" -eq 0 ]; then ok=PASS; PASSN=$((PASSN+1)); else ok=FAIL; FAILN=$((FAILN+1)); fi
  printf "%-4s %-52s final=%-70s clicks+%d\n" "$ok" "$1" "${FINAL:0:70}" $((a-b)); [ -n "${5:-}" ] && printf "       %s\n       %s\n" "$HOP0" "$HOP1"; }
A='^302 https://a\.example\.com/tour\?utm_campaign=archtest$'; B='^302 https://b\.example\.net/tour-b\?utm_campaign=archtest$'
FBRE="^302 ${FALLBACK//./\\.}$"; HOMERE="^302 ${HOME_URL//./\\.}$"

echo "== TEST 1/2  normal URL, three requests, through the edge"
t "1.1 /go/$SLUG/" "/go/$SLUG/" "$A" 1 v
t "1.2 /go/$SLUG/ (same URL)" "/go/$SLUG/" "$A" 1 v
t "1.3 /go/$SLUG/ (same URL)" "/go/$SLUG/" "$A" 1 v
echo "== TEST 3  change offer to provider B after the hop is cached (no purge)"
wpq post meta update $O _er_provider $PB >/dev/null; wpq post meta update $O _er_target_url https://b.example.net/tour-b >/dev/null
t "3   same /go/ URL → provider B" "/go/$SLUG/" "$B" 1 v
echo "== TEST 4  pause after cache (no purge)"
wpq post meta update $O _er_status paused >/dev/null; t "4   paused → fallback page, no click" "/go/$SLUG/" "$FBRE" 0 v; wpq post meta update $O _er_status active >/dev/null
t "4b  unpaused → provider B again" "/go/$SLUG/" "$B" 1
echo "== TEST 6  expired / not started after cache"
wpq post meta update $O _er_end 2020-01-01 >/dev/null; t "6   expired → fallback, no click" "/go/$SLUG/" "$FBRE" 0; wpq post meta delete $O _er_end >/dev/null
wpq post meta update $O _er_start 2099-01-01 >/dev/null; t "6b  not started → fallback, no click" "/go/$SLUG/" "$FBRE" 0; wpq post meta delete $O _er_start >/dev/null
echo "== TEST 7  tracking parameters"
wpq post meta update $O _er_params $'subid={placement}-{page}' >/dev/null
b=$(clicks); req "/go/$SLUG/?utm_source=test&utm_medium=affiliate&utm_campaign=test&pl=hero-cta&src=$FB"; a=$(clicks)
last=$(mariadb -N $DB -e "select concat(placement,'|',source_post_id,'|',source_path,'|',utm_campaign) from wp_er_clicks order by id desc limit 1")
if [[ "$FINAL" == "302 https://b.example.net/tour-b?utm_campaign=archtest&subid=hero-cta-"* && "$FINAL" != *utm_source=test* && $((a-b)) -eq 1 && "$last" == "hero-cta|$FB|/destinations/|archtest" ]]; then echo "PASS 7   visitor utm_* ignored; configured utm + sub-ID applied; click logged [$last]"; PASSN=$((PASSN+1)); else echo "FAIL 7   $FINAL clicks+$((a-b)) [$last]"; FAILN=$((FAILN+1)); fi
echo "       $HOP0"; echo "       $HOP1"
wpq post meta delete $O _er_params >/dev/null
echo "== TEST 8  security (through the edge)"
t "8.1 invalid offer" "/go/no-such-offer-$RANDOM/" "$HOMERE" 0
t "8.2 open redirect ?url= ?to= ?redirect_to=" "/go/$SLUG/?url=https://evil.test/&to=https://evil.test&redirect_to=https://evil.test" "$B" 1
t "8.3 offer switch via hop (?offer=,&action=)" "/go/$SLUG/?offer=other&action=er_subscribe" "$B" 1
t "8.4 CRLF header injection in pl/src" "/go/$SLUG/?pl=%0d%0aSet-Cookie:x=1&src=1%0d%0aX:%20y" "$B" 1
t "8.5 path traversal /go/..%2f" "/go/..%2f..%2fwp-config.php/" "^404" 0
ORIG=$(wpq post meta get $O _er_target_url)
for bad in "https://evil.test/x" "https://b.example.net.evil.test/x" "https://b.example.net@evil.test/" "javascript:alert(1)" "//evil.test/x" "http:///x" "ftp://b.example.net/x" "https://evil.test/?b.example.net"; do
  wpq post meta update $O _er_target_url "$bad" >/dev/null; t "8.6 target $bad" "/go/$SLUG/" "$FBRE" 0; done
wpq post meta update $O _er_target_url "$ORIG" >/dev/null
wpq post meta update $PB _er_status inactive >/dev/null; t "8.7 provider inactive" "/go/$SLUG/" "$FBRE" 0; wpq post meta update $PB _er_status active >/dev/null
wpq post meta update $O _er_provider $PA >/dev/null; t "8.8 target domain not in provider's allow-list" "/go/$SLUG/" "$FBRE" 0; wpq post meta update $O _er_provider $PB >/dev/null
echo "== TEST 9  admin-post access (anonymous, no cookies)"
t "9.1 anonymous handler works" "/wp-admin/admin-post.php?action=er_go&offer=$SLUG" "$B" 1
t "9.2 crafted offer ../../wp-config" "/wp-admin/admin-post.php?action=er_go&offer=../../wp-config" "$HOMERE" 0
t "9.3 offer[] array" "/wp-admin/admin-post.php?action=er_go&offer%5B%5D=$SLUG&src%5B%5D=1&adults%5B%5D=2" "$HOMERE" 0
t "9.4 uppercase / encoded slug" "/wp-admin/admin-post.php?action=er_go&offer=${SLUG^^}" "$HOMERE" 0
t "9.5 no offer" "/wp-admin/admin-post.php?action=er_go" "$HOMERE" 0
st=$(curl -s -o /dev/null -w "%{http_code}" "${HH[@]}" -A "$UA" "$E/wp-admin/admin-post.php?action=er_go_evil&offer=$SLUG"); [ "$st" = 400 ] && { echo "PASS 9.6 unregistered action → $st (WordPress default)"; PASSN=$((PASSN+1)); } || { echo "FAIL 9.6 unregistered action → $st"; FAILN=$((FAILN+1)); }
b=$(clicks); curl -s -o /dev/null -I "${HH[@]}" -A "$UA" "$E/wp-admin/admin-post.php?action=er_go&offer=$SLUG"; curl -s -o /dev/null "${HH[@]}" -A "Googlebot/2.1" "$E/wp-admin/admin-post.php?action=er_go&offer=$SLUG"; curl -s -o /dev/null -X POST "${HH[@]}" -A "$UA" "$E/wp-admin/admin-post.php?action=er_go&offer=$SLUG"; a=$(clicks)
[ $((a-b)) -eq 0 ] && { echo "PASS 9.7 HEAD, bot and POST are not logged"; PASSN=$((PASSN+1)); } || { echo "FAIL 9.7 logged $((a-b))"; FAILN=$((FAILN+1)); }
grep -iE "PHP (Warning|Notice|Fatal)|Array to string" ${PHPLOG:-/dev/null} | tail -3
echo "== TEST 10  status codes"
req "/go/$SLUG/"; echo "     $HOP0"; echo "     $HOP1"
[[ "$HOP0" == *"→ 302 "* && "$FINAL" == 302* ]] && { echo "PASS 10  /go/ 302 (temporary) → admin-post 302 (temporary); no 301"; PASSN=$((PASSN+1)); } || { echo "FAIL 10"; FAILN=$((FAILN+1)); }
echo "== TEST 5  delete after cache (last: removes the offer)"
wpq eval "wp_trash_post( $O );" >/dev/null; [ "$(wpq post get $O --field=post_status)" = trash ] || echo "WARN: offer not trashed"; t "5   trashed (admin Trash) → home page, never the provider" "/go/$SLUG/" "$HOMERE" 0 v
wpq post delete $O --force >/dev/null; t "5b  permanently deleted → home page" "/go/$SLUG/" "$HOMERE" 0
wpq post delete $PA $PB --force >/dev/null
echo "== $PASSN passed, $FAILN failed"
