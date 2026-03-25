# The Soccer Stats 2.0

## NOTE!! Work in progress...

Modern block-based WordPress plugin for football statistics.

## Build

```bash
npm install
npm run start
```

Production build:

```bash
npm run build
```

## Structure

- `the-soccer-stats.php`: plugin bootstrap
- `includes/`: PHP modules for CPTs, meta registration, data access and block registration
- `includes/Data/`: repositories, table names and DB schema
- `blocks/`: dynamic blocks with `render.php`
- `resources/js/`: source files for `@wordpress/scripts`
- `build/`: current checked-in editor asset
- `assets/style.css`: shared frontend/editor styles
