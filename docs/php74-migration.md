# PHP 7.4 migration

## Chosen compatibility baseline

PHP **7.4.33** is the least disruptive PHP 7 target for this application. It
is the final PHP 7 release, while CakePHP 3.10 is the final CakePHP 3 minor
line. This keeps the application on CakePHP 3 and keeps Bake and DebugKit on
their existing major lines. Composer emulates PHP 7.4.33 during dependency
resolution so that a developer using a newer PHP cannot accidentally lock
packages which production cannot run.

PHP 7.4 is end-of-life and receives no security fixes. This baseline is a
short-lived compatibility step, not a safe long-term runtime. After the
application is stable on these dependencies, move to a supported PHP release
and plan the separately testable CakePHP major-version upgrades.

## Why the dependency refresh is required

The checked-in dependencies date from the CakePHP 3.0 development cycle.
CakePHP 3.0 contains `Cake\Utility\String`, whose class name is reserved by
PHP 7, and therefore cannot boot on PHP 7. Updating only the PHP executable is
not sufficient. The constraints in `composer.json` select the last CakePHP 3
line and compatible plugin releases without upgrading CakePHP itself to 4.x.

The application also declared its entity for the `classes` table as `Class`.
That name is reserved in PHP 7. It is now named `SchoolClass`, and
`ClassesTable` explicitly selects it so that database and association names do
not change.

## Deployment procedure

1. Perform the dependency refresh in a connected build environment using PHP
   7.4 and Composer 2:

   ```sh
   rm -rf vendor plugins/Bake plugins/DebugKit plugins/Migrations
   composer update --with-all-dependencies
   ```

   Review and commit the resulting `composer.lock`. The old lock file is kept
   in this repository only until that connected update can be performed; it
   describes the incompatible 2015 dependency tree and must not be deployed.

2. Run the application test suite before creating the production artifact:

   ```sh
   vendor/bin/phpunit
   ```

3. Ensure the PHP 7.4 runtime has the extensions already required by the
   application and its dependencies: `curl`, `intl`, `json`, `mbstring`,
   `openssl`, `pdo`, the production PDO database driver, and `zip`.

4. Build one immutable artifact with `composer install --no-dev
   --classmap-authoritative`, deploy it to staging, and exercise login, CSV
   import/export, synchronization, Google API access, and all scheduled/CLI
   commands. Promote the same artifact to production after verification.

5. Do not copy production configuration into source control. Keep the existing
   environment-specific configuration and secrets, and verify them during the
   staging smoke test.

## Follow-up work

The bundled Google API client is old application source rather than a Composer
dependency. PHP 7.4 can execute its string-offset syntax, but emits deprecation
notices. Replace it with a maintained Composer package before moving to PHP 8,
where that syntax is an error. The PHP 8 migration should also replace APIs
removed after PHP 7 and update CakePHP one major version at a time.
