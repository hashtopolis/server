window.onload = function() {
  //<editor-fold desc="Changeable Configuration Block">

  // the following lines will be replaced by docker/configurator, when it runs in a docker-container
  window.ui = SwaggerUIBundle({
    url: "/api/v2/openapi.json",
    dom_id: '#swagger-ui',
    deepLinking: true,
    filter: true,
    persistAuthorization: true,
    // Authorize bearerAuth with the JWT returned by /api/v2/auth/token
    responseInterceptor: (response) => {
      if (response.ok && response.url.endsWith("/api/v2/auth/token") && response.body?.token) {
        window.ui.preauthorizeApiKey("bearerAuth", response.body.token);
      }
      return response;
    },
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });

  //</editor-fold>
};
