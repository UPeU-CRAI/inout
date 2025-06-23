# Deployment Steps

To deploy the InOut system on a new server:

1. Copy all files to the web root of the target server.
2. Run `composer install` inside the project root to generate the `vendor/` directory. This step is required because `login.php` checks for `vendor/autoload.php` and will exit with the message "Vendor autoload not found" if it is missing.
3. Configure your `.env` file with the database credentials and other settings.
4. Make sure the web server user can read the application files and write to any directories that require write access (such as `logs/`).

These steps complement the installation instructions in `README.md` and ensure that the application boots correctly in production.
