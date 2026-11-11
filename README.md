# Delta State 2011 Election Results (Bincom test)

Plain PHP 8 + PDO + MySQL. No framework and no Composer, so you can upload it to any cPanel host.

| Page | Question |
|---|---|
| `polling_unit.php` | **Q1**: result for one polling unit, picked with chained LGA → Ward → Polling unit selects (✓ marks units that have results) |
| `lga_results.php` | **Q2**: summed total of all polling-unit results in an LGA, picked from a select box. Calculated from `announced_pu_results` only. `announced_lga_results` is shown next to it for cross-checking |
| `new_result.php` | **Q3**: create a new polling unit and store a score for **every** party (validated, saved in one transaction) |
| `api.php` | JSON for the chained selects |

## Deploy on cPanel
1. **MySQL Databases**: create a database (e.g. `cpuser_bincom`) and a user, then add the user to the database with ALL PRIVILEGES.
2. **phpMyAdmin**: select that database, open **Import**, and upload `bincom_test.sql` (the file from the test document).
3. Edit `config.php` and set `dsn` (`dbname=cpuser_bincom`), `user` and `pass`.
4. **File Manager**: upload everything except `tests/` into `public_html/` (or a subfolder like `public_html/bincom/`).
5. Open `https://yourdomain/bincom/`.

## Run locally
```bash
php -S localhost:8000
```

## Notes
- `announced_pu_results.polling_unit_uniqueid` matches `polling_unit.uniqueid`, not `polling_unit_id`.
- Ward IDs repeat across LGAs, so wards and units are always filtered by `lga_id` **and** `ward_id`.
- `party_abbreviation` is `CHAR(4)`, so LABOUR is stored as `LABO`, the same as in the existing data.
- `tests/sample_schema.sql` is a tiny made-up fixture used only for local testing. Do not import it on the server.
