## Dependencies
+ [Google APIs Client Library for PHP](https://github.com/googleapis/google-api-php-client) (external)

## Installation
1. Download and install S360 Most Popular module.
2. Install [Google APIs Client Library for PHP](https://github.com/googleapis/google-api-php-client)

   For Composer-managed Drupal installations, the recommended method is to use the [Composer Merge Plugin](https://github.com/wikimedia/composer-merge-plugin) and this module's `composer.libraries.json` file. From a Composer project root:

   1. Execute `composer require wikimedia/composer-merge-plugin`.
   2. Add the following to the `extra` section of the root `composer.json` file:

        ```
        "merge-plugin": {
            "include": [
                "{DOCROOT}/modules/contrib/s360_ga_pageviews/composer.libraries.json"
            ]
        },
        ```

        Note: Remember to replace `{DOCROOT}` with the appropriate root folder
        for the Drupal installation -- this is likely `web` or `docroot`.
    3. Execute `composer install` (or, in some cases, `composer update --lock`).

    That's it! Composer should install the Google API Client in the appropriate place (`/vendor/google` with `apiclient`, `apiclient-services`, and `auth` subfolders).

## Configuration Page
**Administration » Configuration » Web services » S360 Most Popular** (admin/config/services/s360-ga-pageviews).
