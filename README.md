rtCamp Google Embeds
=========================

[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)

This plugin allows adding preview for your Google Drive Documents right in your editor.

## Setup
1. Create a project from [Google Developers Console](https://console.developers.google.com/apis/dashboard) if none exists.
2. Click on **ENABLE APIS AND SERVICES** 
3. In the displayed list of available APIs, click the **Drive API** link and click **Enable API**.
4. Go to **Credentials** tab, then create credential for **API key**.
5. This will give you **Drive API KEY**.

Input this value in `wp-config.php` using the following code snippet:
```php
define( 'WP_GOOGLE_DRIVE_API_KEY', 'YOUR_API_KEY' );
```
https://developers.google.com/drive/api/v3/enable-drive-api

## Development Notes

##### Please run following commands from the root directory.

1. Please verify your code is in compliance to the Coding Standards used in this Project.
2. Run `composer phpcs filename` or `composer phpcs` to check for PHPCS errors/warnings.

#### Building Plugin assets

- Run ```npm install``` from repositories root to install all required dependencies.
- To build a production version run `npm run build`.
- While developing the plugin run `npm run dev` to build assets on file changes.
- If any string is added/updated, run `npm run language` to update the language file. ( Assumes availability of `wp i18n` command. )

## Does this interest you?

<a href="https://rtcamp.com/"><img src="https://rtcamp.com/wp-content/uploads/2019/04/github-banner@2x.png" alt="Join us at rtCamp, we specialize in providing high performance enterprise WordPress solutions"></a>
