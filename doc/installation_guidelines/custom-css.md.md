# Customize Web Interface

you would like to customize the web-interface using your own layout just follow the instructions explained here:

## Custom Themes 

Drop `.css` files in this folder to register custom runtime themes.

No additional JSON or TypeScript changes are required for standard theme creation.

For a complete authoring guide, see **Custom Theme Authoring Guide**.

In our repository you find two example themes — `rainbow.css` and `fallout.css`: https://github.com/hashtopolis/web-ui/tree/master/custom-themes

### Enabling custom themes (opt-in)

No custom theme is loaded by default. The built-in `light` and `dark` themes are always available; everything in this folder is opt-in.

`HASHTOPOLIS_CUSTOM_THEMES_DIR` is the on/off switch: it names the folder the container scans for `.css` files on startup. When it is unset, no custom themes are loaded and the app uses the built-in `light`/`dark` themes only.

To enable themes with Docker Compose, set the variable to the container path the entrypoint should scan and mount your `.css` folder to that same path by uncommenting both lines in `docker-compose.yml` (or `.devcontainer/docker-compose.yml`):

```yaml
    environment:
      - HASHTOPOLIS_CUSTOM_THEMES_DIR=/custom-themes
    volumes:
      - ./custom-themes:/custom-themes:ro
```

### Baking themes into the image (no runtime mount)

When building your own production image, pass the `CUSTOM_THEMES_DIR` build arg to copy a folder of `.css` files into the image (the default bakes nothing):

```shell
docker build --target hashtopolis-web-ui-prod \
  --build-arg CUSTOM_THEMES_DIR=custom-themes -t hashtopolis/web-ui .
```

Baked themes are still opt-in at runtime: set `HASHTOPOLIS_CUSTOM_THEMES_DIR=/custom-themes` to enable them.

### Naming

- File names are converted to a theme id using lowercase kebab-case.
- Example: `Ocean Glow.css` becomes `ocean-glow`.

### Runtime behavior

- When `HASHTOPOLIS_CUSTOM_THEMES_DIR` is set, `docker-entrypoint.sh` scans that folder on container startup (otherwise no custom themes are loaded).
- Valid CSS files are copied to `/assets/custom-themes/`.
- A manifest is generated at `/assets/themes/custom-themes.json`.
- The frontend reads the manifest and adds the themes to the selectors.
- Theme menu icons are currently auto-set to `style` for custom themes.
- A theme whose CSS declares `color-scheme: dark` is flagged dark, so the app uses its dark-mode logo, icon colors, and chart palettes.

### Reserved IDs

These ids are built-in and cannot be overridden by custom files:

- `light`
- `dark`

### How to get help generating a theme?

Use the prompt template at the end of **Custom Theme Authoring Guide** with your favorite LLM.

## Custom Theme Authoring Guide

This project supports runtime custom themes from CSS files.

For normal usage, custom theme creation is intentionally low-complexity: add one CSS file and recreate the container.

**1) Add a CSS file**

Place a `.css` file in the `custom-themes` folder.
Examples:

- `custom-themes/ocean.css`
- `custom-themes/cyber-night.css`

The file name is converted to the theme id using lowercase kebab-case.

- `Ocean Night.css` -> `ocean-night`

**2) Define your theme scope**


Use the generated id as a body class in the format `.<theme-id>-theme`.

Example for `ocean.css`:

```css
.ocean-theme {
  --background: #07131f;
  --foreground: #d9f2ff;
  --primary: #4db5ff;
  --border: rgba(77, 181, 255, 0.35);
  color-scheme: dark;
}
```

At minimum, define the color tokens your components rely on.

**3) Recreate container**

Custom themes are opt-in. Set `HASHTOPOLIS_CUSTOM_THEMES_DIR` to the folder the container should scan and mount your `.css` folder to that path (see `custom-themes/README.md`). When the container starts, `docker-entrypoint.sh` scans that folder, copies CSS files into served assets, and generates a manifest.

Apply changes with:

```shell
docker compose up -d --build --force-recreate
```

If your stack is already running and mounted correctly, a restart may be enough:

```shell
docker compose restart
```

**4) Verify in browser**

Open:

- `/assets/themes/custom-themes.json`
- `/assets/custom-themes/<theme-id>.css`

If both are available, the theme appears in theme selectors.

**Notes**

- Custom theme ids must match `^[a-z0-9-]+$`.
- Built-in ids are reserved and ignored in custom manifest: `light`, `dark`.
- Use absolute URLs in CSS for assets, for example `/assets/img/...`.
- Custom menu icons are currently generated as `style` by default.
- Declare `color-scheme: dark` for a dark theme so the app picks dark-mode logos, icon colors, and charts.
- Do not add JavaScript or TypeScript to create a theme unless you are extending platform behavior.

## LLM Prompt Template

Copy and adapt this prompt when asking an LLM to generate a new theme CSS file.

```
Generate a single CSS file for a Hashtopolis custom theme.

Requirements:
- Output CSS only, no markdown.
- Use this class selector exactly: .<theme-id>-theme
- Define all variables below with visually coherent values:
  --background
  --muted
  --well
  --card
  --card-hover
  --input
  --sidebar
  --foreground
  --muted-foreground
  --subtle-foreground
  --heading-foreground
  --primary
  --primary-hover
  --primary-muted
  --primary-foreground
  --secondary
  --border
  --border-strong
  --border-faint
  --surface-faint
  --surface-soft
  --surface-soft-hover
  --cell-hover
  --success
  --warning
  --destructive
  --info
  --link
  --link-hover
  --success-bg
  --destructive-bg
  --info-bg
  --header
  --shell-frame-image
  --brand-backdrop
  --shadow-sm
  --shadow-md
  --shadow-lg
  --gradient-accent
- Include color-scheme: dark; or color-scheme: light; to match the palette.
- Keep contrast accessible for body text and headings.
- Avoid changing any selector outside .<theme-id>-theme.

Theme request:
- Theme id: <theme-id>
- Mood/style: <describe visual direction>
- Preferred primary hue: <color>
- Dark or light: <dark|light>
```

**Example Output**

This is the kind of CSS structure the prompt should generate.

```css
.ocean-night-theme {
  --background: #06131f;
  --muted: #0d1d2c;
  --well: #10263a;
  --card: rgba(255, 255, 255, 0.05);
  --card-hover: rgba(255, 255, 255, 0.09);
  --input: rgba(255, 255, 255, 0.06);
  --sidebar: #081520;

  --foreground: #e6f4ff;
  --muted-foreground: rgba(230, 244, 255, 0.75);
  --subtle-foreground: rgba(230, 244, 255, 0.55);
  --heading-foreground: #ffffff;

  --primary: #4db5ff;
  --primary-hover: #7ac8ff;
  --primary-muted: rgba(77, 181, 255, 0.2);
  --primary-foreground: #04111b;
  --secondary: #7df9ff;

  --border: rgba(77, 181, 255, 0.25);
  --border-strong: rgba(77, 181, 255, 0.45);
  --border-faint: rgba(77, 181, 255, 0.15);

  --surface-faint: rgba(255, 255, 255, 0.03);
  --surface-soft: rgba(255, 255, 255, 0.06);
  --surface-soft-hover: rgba(255, 255, 255, 0.1);

  --cell-hover: rgba(77, 181, 255, 0.16);

  --success: #76e39b;
  --warning: #ffd26a;
  --destructive: #ff7f96;
  --info: #7df9ff;
  --link: #7df9ff;
  --link-hover: #b7fdff;

  --success-bg: color-mix(in oklch, var(--success) 18%, var(--well));
  --destructive-bg: color-mix(in oklch, var(--destructive) 18%, var(--well));
  --info-bg: color-mix(in oklch, var(--info) 18%, var(--well));

  --header: rgba(6, 19, 31, 0.72);
  --shell-frame-image: linear-gradient(135deg, rgba(77, 181, 255, 0.18), rgba(125, 249, 255, 0.08));
  --brand-backdrop: #4db5ff;

  --shadow-sm: none;
  --shadow-md: 0 3px 12px rgba(3, 11, 20, 0.42);
  --shadow-lg: 0 10px 30px rgba(3, 11, 20, 0.58);

  --gradient-accent: linear-gradient(130deg, #4db5ff 0%, #7df9ff 100%);

  color-scheme: dark;
}
```