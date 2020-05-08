# Freemius Deploy

This github action deploys a plugin on freemius.

Usage:
``
- uses: buttonizer/freemius-deploy@master
  with:
    file_name: ./buttonizer.zip
    release_mode: pending
    version: ${{steps.get_version.outputs.VERSION}}
    sandbox: false
  env:
    PUBLIC_KEY: pk_6a1cf11dc545a43474167db1f259f
    USER_ID: 1189
    SECRET_KEY: 'sk_;k7ol~;%{?4>?@hJg8uqhJiuv?MMa'
    PLUGIN_SLUG: buttonizer-multifunctional-button
    PLUGIN_ID: 1219
``
