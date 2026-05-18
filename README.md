# Lab Notes Theme

A WordPress theme version of the static lab-notes prototype.

## Local Development

The classic theme lives in `blog-theme/`. This project includes a `wp-now` script for testing that theme locally with fixture categories and posts from `wp-now/blueprint.json`:

```sh
npm run wp-now
```

The official WordPress Playground docs now mark `@wp-now/wp-now` as deprecated in favor of `@wp-playground/cli`, but this script keeps the requested `wp-now` workflow in place for this branch.
