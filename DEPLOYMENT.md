# Deployment Steps

To deploy the InOut system on a new server:

1. Copy all files to the web root of the target server.
2. Run `composer install` inside the project root to generate the `vendor/` directory. This step is required because `login.php` checks for `vendor/autoload.php` and will exit with the message "Vendor autoload not found" if it is missing.
3. Copy `.env.example` to `.env` and set **all** required variables:
   - `INOUT_DB_HOST`, `INOUT_DB_USER`, `INOUT_DB_PASS`, `INOUT_DB_NAME`
   - `KOHA_DB_HOST`, `KOHA_DB_USER`, `KOHA_DB_PASS`, `KOHA_DB_NAME`
   - `TTS_CREDENTIALS_PATH`, `TTS_LANGUAGE_CODE`, `TTS_VOICE`
     (optionally `GOOGLE_APPLICATION_CREDENTIALS` if the Google library needs it)
   Ensure the web server user can read this file by running `ls -l .env`.
4. Make sure the web server user can read the application files and write to any directories that require write access (such as `logs/`).

These steps complement the installation instructions in `README.md` and ensure that the application boots correctly in production.
