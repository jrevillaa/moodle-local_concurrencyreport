# local_concurrencyreport

Historical concurrency report for Moodle based on `logstore_standard_log`.

## Purpose

This local plugin estimates concurrent active users across a selected time range. A user is considered active while they have log activity inside the configured activity window.

The report is intended for site administrators who need a historical approximation of concurrency for capacity analysis, usage review, and operational decisions.

## Features

- Filter by date range, granularity, activity window, and audience.
- Supported granularities: second, minute, hour, day, month, and year.
- Audience modes: students only, or all authenticated users.
- Browser pagination for large result sets.
- CSV and XLSX export via `core\\dataformat`.
- Configurable range limits per granularity.

## Branches and Tags

This repository is organised by Moodle release line so that each branch and tag can be downloaded directly for a specific Moodle version.

| Moodle version | Branch |
| --- | --- |
| 3.9 | `MOODLE_39_STABLE` |
| 3.10 | `MOODLE_310_STABLE` |
| 3.11 | `MOODLE_311_STABLE` |
| 4.0 | `MOODLE_400_STABLE` |
| 4.1 | `MOODLE_401_STABLE` |
| 4.2 | `MOODLE_402_STABLE` |
| 4.3 | `MOODLE_403_STABLE` |
| 4.4 | `MOODLE_404_STABLE` |
| 4.5 | `MOODLE_405_STABLE` |
| 5.0 | `MOODLE_500_STABLE` |
| 5.1 | `MOODLE_501_STABLE` |

The `main` branch tracks the latest supported Moodle line and is currently prepared for Moodle 5.1.

## Requirements

This branch works with Moodle 4.0 version 2022041900 and above within the Moodle 4.0 release line.

Please use the matching branch or tag for older Moodle versions.

## Installation

1. Copy this folder into `local/concurrencyreport`.
2. Log in as an administrator.
3. Visit `Site administration > Notifications`.
4. Complete the installation.
5. Access the report from `Site administration > Reports > Historical concurrency report`.

## Configuration

The plugin provides these settings:

- Activity window in seconds.
- Maximum allowed range for second granularity.
- Maximum allowed range for minute granularity.
- Maximum allowed range for hour granularity.
- Maximum allowed range for day granularity.
- Rows per page in the browser.

## How It Works

- The report reads `logstore_standard_log`.
- It streams matching log records ordered by `timecreated` and `userid`.
- It keeps an in-memory set of currently active users based on the configured activity window.
- It aggregates counts into buckets according to the selected granularity.

## Performance Notes

- The plugin is intentionally designed to work without altering core Moodle database schema.
- It does not add custom indexes to `logstore_standard_log`, which is a core table.
- Query performance should be evaluated with `EXPLAIN` on the target site before considering database-level tuning.
- Range limits exist to prevent unbounded browser rendering and overly expensive report requests.
- Export always reuses the validated filters and capability checks from the page flow.

## Development Notes

- The UI uses Moodle Form API, renderer output, and Mustache templates.
- The report query uses Moodle DB APIs and a streaming recordset.
- PHPUnit coverage is included for the core calculation and pagination/export guards.

## Support

For bugs, feature requests, and maintenance discussion, use the repository issue tracker.

## License

GNU GPL v3 or later.
