/**
* Dashboard Theme Config
* Liga Anak Piaman Laweh
*/

(function () {

     var savedConfig = sessionStorage.getItem("__THEME_CONFIG__");
     var html = document.getElementsByTagName("html")[0];

     var defaultConfig = {
          theme: "light",

          topbar: {
               color: "topbar-light",
          },

          menu: {
               size: "default",
               color: "sidebar-dark",
          },
     };

     var legacyDefaultConfig = {
          theme: "light",
          topbar: {
               color: "topbar-light",
          },
          menu: {
               size: "default",
               color: "sidebar-light",
          },
     };

     // The line below was causing the error and is now removed.
     // this.html = document.getElementsByTagName('html')[0];

     let config = Object.assign(JSON.parse(JSON.stringify(defaultConfig)), {});
     window.defaultConfig = JSON.parse(JSON.stringify(config));

     if (savedConfig !== null) {
          config = JSON.parse(savedConfig);

          var isLegacyDefault = config.theme === legacyDefaultConfig.theme
               && config.topbar?.color === legacyDefaultConfig.topbar.color
               && config.menu?.size === legacyDefaultConfig.menu.size
               && config.menu?.color === legacyDefaultConfig.menu.color;

          if (isLegacyDefault) {
               config.menu.color = defaultConfig.menu.color;
               sessionStorage.setItem("__THEME_CONFIG__", JSON.stringify(config));
          }
     }

     window.config = config;

     if (config) {
          html.setAttribute("data-bs-theme", config.theme);
          html.setAttribute("data-sidenav-size", config.menu.size === "sidebar-hover" ? "hover" : config.menu.size);
          html.classList.add(config.topbar.color);
          html.classList.add(config.menu.color);

          if (window.innerWidth <= 1140) {
               html.classList.add("sidebar-hidden");
          } else if (config.menu.size === "sidebar-hover") {
               html.classList.add("sidebar-hover");
          }
     }
})();
