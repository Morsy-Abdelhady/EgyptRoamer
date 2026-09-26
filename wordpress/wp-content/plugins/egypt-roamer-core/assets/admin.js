/* Egypt Roamer admin: media picker for image fields. */
(function () {
  "use strict";
  document.addEventListener("click", function (e) {
    var pick = e.target.closest("[data-er-image-pick]");
    var clear = e.target.closest("[data-er-image-clear]");
    var box = (pick || clear) && (pick || clear).closest("[data-er-image]");
    if (!box) return;
    e.preventDefault();
    var input = box.querySelector("input");
    var img = box.querySelector("img");
    if (clear) {
      input.value = "";
      img.hidden = true;
      img.removeAttribute("src");
      return;
    }
    if (!window.wp || !wp.media) return;
    var frame = wp.media({ multiple: false, library: { type: "image" } });
    frame.on("select", function () {
      var a = frame.state().get("selection").first().toJSON();
      input.value = a.id;
      img.src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail : a).url;
      img.hidden = false;
    });
    frame.open();
  });
})();
