/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Version: 4.3.0
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Common Plugins Js File
*/

//Common plugins
const shouldLoadPlugins =
  document.querySelectorAll("[toast-list]").length > 0 ||
  document.querySelectorAll("[data-choices]").length > 0 ||
  document.querySelectorAll("[data-provider]").length > 0;

if (shouldLoadPlugins) {
  const loadScript = (src) => {
    const absoluteUrl = new URL(src, window.location.origin).toString();
    const alreadyLoaded = Array.from(document.scripts).some(
      (script) => script.src === absoluteUrl
    );

    if (alreadyLoaded) {
      return;
    }

    const script = document.createElement("script");
    script.type = "text/javascript";
    script.src = src;
    script.defer = true;
    document.head.appendChild(script);
  };

  loadScript("/assets/libs/toastify-js/src/toastify.js");
  loadScript("/assets/libs/choices.js/public/assets/scripts/choices.min.js");
  loadScript("/assets/libs/flatpickr/flatpickr.min.js");
}
