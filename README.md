# Freemius Deploy

This GitHub Action deploys your WordPress plugin on Freemius. It uses the [Freemius PHP SDK](https://github.com/Freemius/freemius-php-sdk.git) and uses some functionality of [CodeAtCode/freemius-suite](https://github.com/CodeAtCode/freemius-suite).
It was created as a fork from [buttonizer/freemius-deploy](https://github.com/buttonizer/freemius-deploy) but with some 
updates that clean up the code and allow for more customization.

## Inputs

| Input               | Required | Description                                                                                                                                                                                                                                                                                   | Default   |
|---------------------|----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------|
| `file_name`         | Yes      | File name of the to be uploaded WordPress plugin (zip extension).  _Note: the file has to be in the root folder of your repository_                                                                                                                                                           | none      |
| `release_mode`      | No       | `pending`, `beta`, or `released`. Set to beta to release the product to valid license holders that opted into the beta list. Set to released to release it to all valid license holders. When the product is released, it will be available for download right within the WP Admin dashboard. | `pending` |
| `sandbox`           | No       | Whether to upload in sandbox mode. `True` or `false`.                                                                                                                                                                                                                                         | `false`   |
| `limit`             | No       | The absolute limit of license owners who will receive an update (expected `int`)                                                                                                                                                                                                              | none      |
| `percentage_limit`  | No       | The percentage (1-100%) of license owners who will receive an update (expected `int`)                                                                                                                                                                                                         | none      |
| `is_incremental`    | No       | Whether to flag the version as incremental or not. `True` or `false`.                                                                                                                                                                                                                         | `false`   |
| `add_contributor`   | No       | Add Freemius as a contributor to the plugin. `True` or `false`.                                                                                                                                                                                                                               | `false`   |
| `fail_on_duplicate` | No       | If the action should fail if the version already exisits on Freemius.                                                                                                                                                                                                                         | `true`    |
| `overwrite`         | No       | Overwrite a tag with the same version, otherwise skips upload and continues with the rest of the action. `True` or `false`.                                                                                                                                                                   | `false`   |
| `download_free`     | No       | Controls whether or not to download the free version as part of the action.                                                                                                                                                                                                                   | `true`    |
| `download_premium`  | No       | Controls whether or not to download the premium version as part of the action.                                                                                                                                                                                                                | `true`    |

## Environment variables

Set the following required environment variables in your GitHub repository [secrets](https://help.github.com/en/actions/configuring-and-managing-workflows/creating-and-storing-encrypted-secrets):

- `DEV_ID`: Your Freemius Developer ID

- `PUBLIC_KEY`: Your Freemius Public Key

- `SECRET_KEY`: Your Freemius Secret Key

- `PLUGIN_SLUG`: Your plugin's slug

- `PLUGIN_ID`: Your plugin's ID

Note: The `PUBLIC_KEY` and `SECRET_KEY` are your developer keys from Freemius, not the plugin-specific keys.

These are all found in your Freemius dashboard. You can find your public key, secret key, and your Dev ID under 
the keys section on the bottom of the 'My Profile' page. Your plugin slug and ID can be found on the products' settings 
page or in the SDK integration snippet.

## Action outputs (since v0.1.1)

The action by default downloads both the **free** and **pro** versions and outputs their filenames as outputs:

- free_version
- pro_version

You can access these by setting an **id** to your workflow step. In a later step reference them like this: 

```
${{ steps.deploy_step_id.outputs.free_version }}
${{ steps.deploy_step_id.outputs.pro_version }}
```

These files can then be uploaded as artifacts or deployed to the WordPress SVN repository using actions like
[10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy).

## Example

```yml
name: Deploy Plugin to Freemius

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'

      # If needed
      - name: Install dependencies
        run: composer install --no-dev --no-progress --optimize-autoloader

      # replace "includes vendor *.php *.txt" with whatever is relevant for you
      - name: Zip plugin
        run: |
          mkdir -p build
          zip -r build/plugin.zip includes vendor *.php *.txt

      - name: Deploy to Freemius
        uses: Eitan-brightleaf/freemius-deploy@main
        with:
          file_name: build/plugin.zip
          release_mode: released
        env:
          DEV_ID: ${{ secrets.DEV_ID }}
          PUBLIC_KEY: ${{ secrets.FREEMIUS_PUBLIC_KEY }}
          SECRET_KEY: ${{ secrets.FREEMIUS_SECRET_KEY }}
          PLUGIN_SLUG: my-plugin
          PLUGIN_ID: 1234
          
      - name: Unzip plugin for WP.org
        run: |
          mkdir plugin-dir
          unzip ${{ steps.freemius_deploy.outputs.free_version }} -d plugin-dir
          if [ -d "plugin-dir/${{ inputs.plugin_slug }}" ]; then
            mv "plugin-dir/${{ inputs.plugin_slug }}"/* plugin-dir/
            rm -rf "plugin-dir/${{ inputs.plugin_slug }}"
          fi
          
        # Deploy to WordPress.org SVN
      - name: Deploy to WordPress.org
        uses: 10up/action-wordpress-plugin-deploy@stable
        env:
          SVN_USERNAME: ${{ secrets.WP_SVN_USERNAME }}
          SVN_PASSWORD: ${{ secrets.WP_SVN_PASSWORD }}
          BUILD_DIR: plugin-dir
          SLUG: ${{ inputs.plugin_slug }}
          VERSION: ${{ inputs.version }}

```

## Notes

- Ensure that your plugin ZIP file is present in the root of your repository before running the action. This can be 
done through steps prior to this one as in the example.

- If the `fail_on_duplicate` input is set to true the action will fail if the version you are trying to upload already
exists on Freemius. Default is `true`. Note that if the value is `true` the value of the `overwrite` input will be ignored.

- If the `overwrite` input is set to true, it will delete the existing version and redeploy it with the provided zip file.
Otherwise, if the version exists already the API will reject the upload, and the action will log this and continue with
updating the status of the deploy (release mode, release limits, etc.). This obviates the need for the previous `version` input
as the Freemius API itself will reject duplicates, removing the need for an extra API call and searching existing versions.

- If an invalid value was passed to the `release_mode` input it will fall back to pending.
- If a number less than 1 or greater than 100 is given for `percentage_limit` the input will be ignored.
- `limit` and `percentage_limit` are **not** mutually inclusive. You can use them both.
- I plan on potentially updating the action to a JS GitHub action in the future. This should have no impact on your workflow files,
just some performance benefits and the like.
- Any feedback and contributions are welcome.
