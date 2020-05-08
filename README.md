# Freemius Deploy

This Github Action deploys a plugin on Freemius. It uses the [Freemius PHP SDK](https://github.com/Freemius/freemius-php-sdk.git)


## Arguments
| Argument       | Required | Function                                                                                                                                                                                                                                                                                        | Default |
| -------------- | -------- | ------- | ------- |
| `file_name`    | Yes      | File name of the to be uploaded plugin (zip extension).  _Note: the file has to be in the root folder of your repository_                                                                                                                                                                                                                                                      |         |
| `release_mode` | No       | `pending`, `beta`, or `released`. Set to beta to release the product to valid license holders that opted into the beta list. Set to released to release it to all valid license holders. When the product is released, it will be available for download right within the WP Admin dashboard. | `pending` |
| `version` | Yes | This is used to check whether the release is already uploaded. **Action will fail if the release has already been uploaded** | |
| `sandbox` | No | Whether to upload in sandbox mode | `false` |

## Environment variables

**Required**:
- `PUBLIC_KEY`
- `DEV_ID`
- `SECRET_KEY`
- `PLUGIN_SLUG`
- `PLUGIN_ID`

All these are found in your Freemius dashboard.

_Tip: store these variables in your [secrets](https://help.github.com/en/actions/configuring-and-managing-workflows/creating-and-storing-encrypted-secrets)_

## Example
```
- name: Deploy to Freemius
  uses: buttonizer/freemius-deploy@master
  with:
    file_name: my_wordpress_plugin.zip
    release_mode: [pending
    version: 1.1.0
    sandbox: false
  env:
    PUBLIC_KEY: ${{ secrets.FREEMIUS_PUBLIC_KEY }}
    DEV_ID: 1234
    SECRET_KEY: ${{ secrets.FREEMIUS_SECRET_KEY }}
    PLUGIN_SLUG: my-wordpress-plugin
    PLUGIN_ID: 4321
```
