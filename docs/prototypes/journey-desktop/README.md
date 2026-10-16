# Desktop booking journey prototype (2026-10-16)

The owner asked to design one reference journey (arrive → browse trips → choose a trip → book) and approve it before it is rolled out to every trip, language and device. The phone version waits until the desktop one is approved.

- **Published for approval:** https://claude.ai/artifact/SdbGnRYkXTKQDyMsA73cJG (private to the owner's account).
- **Content:** real, taken from the live site and the `?ux=2` preview on 16 Oct: titles, intros, partner offers, partner-stated facts, the disclosure.
  - Nothing is invented.
  - The comparison table says "Not stated" where a partner page does not state a fact.
- **Photos:**
  - Only the approved Unsplash photos (`imgs.mjs` lists them), fetched and embedded as data URIs, because the artifact page cannot load other hosts.
  - Viator's own photos are not copied. On the live site they come from Viator's API.
- **Build:**
  1. `node imgs.mjs` writes `imgs.json`.
  2. Replace `src="{{key}}"` with `data-img="key"` and `{{imgmap}}` with the JSON (see the session's build step).
- **New against the `?ux=2` preview, marked "جديد" in the notes:**
  - "Where in Egypt?" place chips in the hero;
  - filters on the trips page (place, length, private option, hotel pickup);
  - a "Compare side by side" table in the booking panel;
  - after "Book", a note on the trip page ("{Partner} opened in a new tab") with a hotel suggestion.
