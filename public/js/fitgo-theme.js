/**
 * Forces FitGo dark theme only and clears legacy "light" preference.
 */
(function () {
  try {
    localStorage.setItem("fitgo-theme", "dark");
    localStorage.removeItem("theme"); /* any stray keys */
  } catch (e) {}
  document.documentElement.setAttribute("data-theme", "dark");
  document.documentElement.style.colorScheme = "dark";
})();
