/* ==========================================================================
   Egypt Roamer — content & affiliate data (WordPress adapter)
   Every component reads its data from here. In WordPress the data comes from
   the CMS as window.ER_DATA; affiliate links are the site's own tracked /go/
   URLs and prices appear only when an editor verified them.
   ========================================================================== */

/* WordPress renders window.ER_DATA from the CMS (see inc/payload.php for the
   shapes). The prototype's sample data (with its placeholder prices and
   ratings) is intentionally NOT shipped in the theme. */
const ER = window.ER_DATA || null;
const pick = (key, fallback) => (ER && ER[key] !== undefined ? ER[key] : fallback);

const isUrl = (v) => typeof v === "string" && /^(https?:)?\//.test(v);

/** Image URL at a given width. Accepts a WordPress image payload { src, sizes },
    a full URL, or an Unsplash photo id (prototype). */
export const img = (id, w = 1200, q = 75) => {
  if (!id) return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"; // no image yet: transparent, keeps the neutral backdrop
  if (id && typeof id === "object") {
    const widths = Object.keys(id.sizes || {}).map(Number).sort((a, b) => a - b);
    const fit = widths.find((x) => x >= w) || widths[widths.length - 1];
    return fit ? id.sizes[fit] : id.src;
  }
  if (isUrl(id)) return id;
  return `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${w}&q=${q}`;
};

/** srcset helper for responsive images. */
export const srcset = (id, widths = [480, 800, 1200, 1800]) => {
  if (!id) return "";
  if (id && typeof id === "object") return Object.entries(id.sizes || {}).map(([w, u]) => `${u} ${w}w`).join(", ");
  if (isUrl(id)) return "";
  return widths.map((w) => `${img(id, w)} ${w}w`).join(", ");
};

export const destinations = pick("destinations", []);

export const moods = pick("moods", []);

export const experiences = pick("experiences", []);

export const experienceFilters = pick("experienceFilters", []);

export const partnerCategories = pick("partnerCategories", []);

export const guides = pick("guides", []);

export const routeOrder = pick("routeOrder", []);
export const nightWeights = pick("nightWeights", {});
export const legs = pick("legs", {});
export const styleRates = pick("styleRates", {});

export const film = pick("film", []);
