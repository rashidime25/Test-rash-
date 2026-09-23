MR RASHIDI — RAILWAY DEPLOYMENT
================================

This package is prepared for Railway. The dashboard UI is unchanged from the approved PHP version.

STACK
-----
- PHP 8.3 + Apache
- MySQL (Railway database service)
- PDO MySQL
- cURL
- TMDB API (server-side only)
- Sessions + CSRF + password_hash/password_verify

RAILWAY SETUP
-------------
1) Create a new Railway project.
2) Add a MySQL database service. Railway exposes MYSQLHOST, MYSQLPORT, MYSQLUSER,
   MYSQLPASSWORD and MYSQLDATABASE to services in the same project.
3) Deploy this folder as the web service from GitHub or with `railway up`.
4) Set the required service variable:
      TMDB_ACCESS_TOKEN = your TMDB API Read Access Token
5) Optional variables:
      APP_URL=https://your-domain.example
      TMDB_LANGUAGE=fa-IR
      TMDB_FALLBACK_LANGUAGE=en-US
      TMDB_REGION=US
      FORCE_SECURE_COOKIES=1
6) Generate a Railway public domain for the web service.

DATABASE
--------
The Docker entrypoint waits for Railway MySQL and imports schema.sql automatically.
The schema contains users, favorites, watch_history and API cache tables.
It is safe to run repeatedly because tables are created with IF NOT EXISTS.

TMDB
----
TMDB is called from PHP, never from browser JavaScript. Keep TMDB_ACCESS_TOKEN in Railway
Variables and do not commit it to GitHub. Review TMDB's current API terms and attribution
requirements before public/commercial use.

FUNCTIONALITY
-------------
REAL:
- registration
- login/logout
- password hashing
- session authentication
- CSRF protection
- favorites
- watch history
- search
- dynamic movie/TV/animation catalog from TMDB
- details pages
- responsive UI

DEMO ONLY:
- video playback
- subscription purchase/payment

UI
--
Do not modify assets/style.css, assets/app.js or the HTML layout if you want to preserve the
approved dashboard design.

LOCAL DEPLOYMENT
----------------
Docker is recommended:
  docker build -t mr-rashidi .
  docker run -p 8080:8080 \
    -e MYSQLHOST=... -e MYSQLPORT=3306 -e MYSQLDATABASE=... \
    -e MYSQLUSER=... -e MYSQLPASSWORD=... \
    -e TMDB_ACCESS_TOKEN=... mr-rashidi

RAILWAY CLI
-----------
From the project root:
  railway link
  railway up

IMPORTANT
---------
Never place database passwords or TMDB tokens directly in PHP source committed to GitHub.
Use Railway Variables instead.
