# API Reference

Note: We recommend the display in light mode, as the framework for API visualization only supports dark mode in a very cumbersome way.

<div id="redoc-container"></div>

<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
<script>
  function isDarkMode() {
    return document.documentElement.getAttribute("data-md-color-scheme") === "slate";
  }

  function getRedocTheme() {
    if (isDarkMode()) {
      return {
        colors: {
          tonalOffset: 0.5,
          primary: { main: '#90caf9' },
          text: { primary: '#ffffff', secondary: '#cccccc' },
          background: { default: '#121212', paper: '#1e1e1e' },
          http: {
            get: '#4caf50',
            post: '#2196f3',
            put: '#ff9800',
            delete: '#f44336'
          }
        },
        typography: {
          fontSize: '14px',
          fontFamily: 'Roboto, sans-serif',
          headings: {
            fontFamily: 'Roboto, sans-serif'
          }
        }
      };
    } else {
      return {
        colors: {
          background: {
            default: '#ffffff',
            paper: '#ffffff'
          },
          text: {
            primary: '#000000',
            secondary: '#444444'
          }
        }
      };
    }
  }

  function renderRedoc() {
    document.getElementById('redoc-container').innerHTML = '';
    Redoc.init('/openapi.json', {
      scrollYOffset: 60,
      hideHostname: true,
      theme: getRedocTheme()
    }, document.getElementById('redoc-container'));
  }

  // Initial render, after short delay to allow mkdocs theme to apply
  window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      window.__lastColorScheme = document.documentElement.getAttribute("data-md-color-scheme");
      renderRedoc();
    }, 100); // delay helps for initial detection
  });

  // Watch for theme toggle
  const observer = new MutationObserver(() => {
    const current = document.documentElement.getAttribute("data-md-color-scheme");
    if (window.__lastColorScheme !== current) {
      window.__lastColorScheme = current;
      renderRedoc();
    }
  });

  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['data-md-color-scheme']
  });
</script>
