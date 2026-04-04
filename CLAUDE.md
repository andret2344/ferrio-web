# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ferrio-web is a Symfony 7 (PHP 8.5) web application that displays holidays for any day of the year. It consumes an external REST API (`api.ferrio.app`) and presents holidays in a
browsable calendar-style interface. Frontend uses Webpack Encore with TypeScript, Tailwind CSS 4, and PostCSS.

## Commands

### Backend

- **Install dependencies:** `composer install`
- **Run all tests:** `php bin/phpunit`
- **Run a single test file:** `php bin/phpunit tests/Service/VirtualDateTest.php`
- **Run a single test method:** `php bin/phpunit --filter testMethodName`
- **Clear cache:** `php bin/console cache:clear`
- **Clear test cache:** `php bin/console cache:clear --env=test`

### Frontend

- **Install dependencies:** `yarn install`
- **Dev build:** `yarn dev`
- **Watch mode:** `yarn watch`
- **Production build:** `yarn build`
- **Dev server:** `yarn dev-server`

## Architecture

### Request Flow

Controllers receive requests with locale from the URL path (`/{_locale}/...`) -> `HolidayConnector` orchestrates data
fetching -> `FerrioApiClient` calls the external API with caching (86400s TTL) -> entities (`DayResult`, `Holiday`) are passed to Twig templates.

### URL Structure & Internationalization

All content routes are locale-prefixed: `/en/day/1/1`, `/pl/upcoming/0`, `/en/privacy`. Symfony's `{_locale}` route parameter
sets the request locale automatically. Supported locales: `en`, `pl`.

- `/` detects locale via `LanguageResolver` (cookie/Accept-Language) and redirects to `/{locale}/day/{m}/{d}`
- `LegacyRedirectSubscriber` 301-redirects old non-prefixed URLs (`/day/1/1` -> `/en/day/1/1`)
- hreflang tags and sitemap use path-based locale URLs (no query params)
- Language picker in navbar is link-based (navigates to alternate locale URL)

### Virtual Date System

The app treats every year as having 366 days (Feb has 30 days: Feb 28->29->30->Mar 1). `VirtualDate` is a static utility that handles this virtual calendar, including navigation
between days, date validation, and mapping virtual dates (Feb 29/30) back to real calendar positions. This is central to how day navigation and the upcoming-week view work.

### Key Services

- **`FerrioApiClient`** -- HTTP client wrapper for the external API (`FERRIO_API_BASE_URL` env var). Caches responses via Symfony's cache system. Filters out mature content.
- **`HolidayConnector`** -- Business logic layer. Builds `DayResult` objects from API data. Has `*Safe` variants that catch exceptions and return null (used by controllers to show
  error states).
- **`VirtualDate`** -- Static date math for the 366-day virtual calendar. No dependencies.
- **`LanguageResolver`** -- Resolves language from `language` cookie or browser Accept-Language header. Used only for root redirect and legacy URL redirects.
- **`TimezoneResolver`** -- Resolves timezone from `timezone` cookie (set by JS).

### Routes

Routes use PHP attributes on controllers, all prefixed with `/{_locale}`:

- `/` -- Home (detects locale, redirects to day page)
- `/{_locale}/day/{month}/{day}` -- Show holidays for a specific day
- `/{_locale}/upcoming/{week}` -- Show upcoming 7 days of holidays (week offset from today, range -52 to 52)
- `/{_locale}/privacy` -- Privacy policy

### SEO

- Day page carousel renders all holiday cards visible in HTML (progressive enhancement); JS enhances to single-card carousel
- Titles include "Holidays" keyword; meta descriptions include first 1-2 holiday names
- Upcoming pages with `|weekOffset| > 4` are noindexed to prevent thin content
- Day pages include month navigation and cross-link to upcoming view
- `SecurityHeaderSubscriber` sets `Vary: Accept-Encoding` (not Cookie/Accept-Language, since locale is in URL path)
- Sitemap generated via `SitemapSubscriber` with hreflang alternates per locale

### Testing

Tests use PHPUnit 13. Controller tests extend `WebTestCase` and use Symfony's kernel test client. Service tests are unit tests. The test environment uses
`config/services_test.yaml` to make `FerrioApiClient` public for mocking. The `FERRIO_API_BASE_URL` env var must be set in test env.
All controller test URLs must use locale prefix (e.g. `/en/day/1/1`, not `/day/1/1`).

### Environment Variables

- `FERRIO_API_BASE_URL` -- Base URL for the Ferrio holidays API (default: `https://api.ferrio.app`)
- `APP_ENV` / `APP_SECRET` -- Standard Symfony env vars

## Conventions

- All PHP files use `declare(strict_types=1)`
- Entities are `final readonly` classes with promoted constructor properties
- Services use constructor injection with `readonly` properties
- Controllers are `final` classes extending `AbstractController`
- Responses set HTTP cache headers (max-age, s-maxage, stale-while-revalidate, ETag)
- Templates use `app.request.locale` (not a separate `lang` variable) for locale checks in shared partials
