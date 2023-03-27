# Mastodon Backup

A simple PHP application that logs Mastodon posts to an SQLite database, using public RSS feeds.

## Installation

- Save the files to a directory on your webserver
- Rename [`config.sample.php`](config.sample.php) to `config.php` and do some configuration there (only the users to backup, for now)
- If you're prepared to do some work, the PDO interface in `database.php` can probably be adapted to save to databases other than SQLite as well.

Also take care to preserve the `.htaccess` file. It prevents the direct download of the database file in the browser.

## Usage

Call `refresh.php` from your browser. If the output is blank, it probably worked.
You can now call `index.php` and check how many posts ("toots") were saved to your database. (There should be a UI at some point in the future)

Ideally, make sure that `refresh.php` is called regularly, eg. via a cron job.
