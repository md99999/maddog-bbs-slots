# Maddog BBS Slots

**A turn-based progressive slot machine game for WordPress.**

Pull up a stool at the machine. Every player gets a handful of spins a day, a bankroll that carries
over for good, and a shot at a progressive jackpot that grows with every pull of the handle until
somebody lines up three Jackpots. The Gazette reports the day's play and the Hall of Fame keeps the
records.

Maddog BBS Slots is played entirely in the web browser through ordinary WordPress pages. Players sign in
to your WordPress site, choose a player name and play.

> **Credits have no cash value and depict a game score only.** Nothing can be bought, won, sold or
> redeemed for money or prizes. Maddog BBS Slots is a score-keeping game, not gambling.
> The home and Play pages say so at the foot: *This game has no monetary value, Players play
> against other players for high score points to get on the Hall of Fame page.*

---

## Not affiliated with anyone

Maddog BBS Slots takes its inspiration from the mechanical progressive slot machines of old and from
the turn-based "door" games of the BBS era, where you dialled in, played your turns for the day and
came back tomorrow. It is an **entirely new, independently written game**:

- Its code, text, rules, score table and artwork are original to this project.
- It contains no code, text, artwork or sound from any casino game, slot machine, gaming platform or
  other slot machine software.
- It is **not affiliated with, endorsed by or connected to** any casino, sportsbook, sports
  organisation, gaming company, slot machine maker, or any other slot machine game or code.

Any product or company names mentioned anywhere are the property of their respective owners.

---

## Disclaimer: install and run at your own risk

**You run this plugin at your own risk. The author accepts no responsibility or liability for any
loss, damage or compromise arising from its use.**

Every effort has been made to write it safely: every spin is decided on the server, forms and AJAX
calls are protected against cross-site request forgery, database queries are prepared, output is
escaped, and administrative functions require WordPress administrator rights. Even so, new
vulnerabilities appear in software of every kind every day, and no website can be guaranteed
secure. This software is provided **as is, without warranty of any kind**, as set out in the
[GNU General Public License v2](LICENSE), under which it is released. You cannot hold the author
responsible for the consequences of using it.

Before installing it on a site you care about:

- **Test it first** on a staging or local site rather than a live one.
- **Back up your database and files**, and keep doing so.
- **Keep WordPress, PHP, your theme and every plugin up to date**, and serve the site over HTTPS.
- **Protect player accounts.** The game relies on WordPress for registration, login and passwords,
  so secure those as you would on any site, for example with an anti-bot plugin on the registration form.
- **Read the code.** It is open source precisely so you can audit it, and change it, before trusting it.

Found a security problem? Please report it privately by email to **sysop@maddogproductions.online**
rather than opening a public issue. See [SECURITY.md](SECURITY.md).

---

## How to play

1. **Sign in** to the WordPress site and open the **Maddog BBS Slots** page, which explains the game.
2. Go to **Play** and **choose a player name**. It is yours for good; nobody else can take it.
3. New players start with **10,000 credits**. Your bankroll is your score.
4. **Choose a wager**: 100, 200, 300, 400, 500, 1,000, 2,000, 3,000, 4,000, 5,000, 6,000, 7,000, 8,000, 9,000 or 10,000 credits. It stays set from spin to spin until you
   change it, or until your bankroll can no longer cover it, when it is lowered for you.
5. **Pull the handle**: click **Pull**, or press **Enter** or the **space bar**.
6. The three reels spin. The first stops after three seconds, the second a second later and the
   third a second after that, and then any win is added to your score.

### The reels

| Symbol | How often |
|---|---|
| 🍒 Cherry | often |
| 🍋 Lemon | often |
| 🔔 Bell | moderately |
| BAR | less often |
| 💎 Diamond | rarely |
| 7 Lucky Seven | very rarely |
| JACKPOT | extremely rarely |

### Score table

| Combination | Score |
|---|---|
| Jackpot Jackpot Jackpot | the **Progressive Jackpot** |
| 7 7 7 | 500x the wager |
| Diamond Diamond Diamond | 250x |
| BAR BAR BAR | 100x |
| Bell Bell Bell | 50x |
| Lemon Lemon Lemon | 10x |
| Cherry Cherry Cherry | 3x |
| Any pair on the first two reels | 2x |
| A Cherry anywhere, with no other win | a Bonus Spin |

Every score also earns **one Bonus Spin**, and so does a Cherry. A Bonus Spin gives back the spin
just used; the wager is not returned.

### Daily play

- Each player gets **10 spins a day** (the admin can set 1 to 50). Unused spins do not carry over.
- **Missed days catch up:** a player who misses whole days receives each missed day's spins, up to
  7 days' worth, so at 10 a day the most at once is 70.
- **The bankroll carries over permanently.**
- **Daily top-up:** at the start of each day, anyone with less than 10,000 credits is raised to 10,000.
- **Daily bailout:** if a bankroll drops below 1,000 while spins remain, the house adds a one-time
  bailout of 10,000 credits so the player can finish the day. Only one a day: spend that too, and
  you are done until tomorrow's top-up.

### Ranks

A player rises through the ranks by their **best-ever score** (their highest bankroll) **or** by the
number of **days they have played** (days on which they spun at least once), whichever gets them
there first. **A rank once reached is kept for good:** a losing streak, a bailout or a new season
never takes it away.

| Rank | Best-ever score | or days played |
|---|---|---|
| Newcomer | where everyone starts | |
| Regular | 25,000 | 3 |
| High Roller | 100,000 | 10 |
| Card Shark | 500,000 | 25 |
| Millionaire | 1,000,000 | 50 |
| Tycoon | 10,000,000 | 100 |
| Mogul | 100,000,000 | 200 |
| BBS Legend | 1,000,000,000 | 365 |

A promotion is announced to the player and in the Gazette, and the Hall of Fame records the first
player to reach each rank. The ladder is `MDBBS_Game::RANKS` in `includes/class-mdbbs-core.php`.

### The progressive jackpot

The progressive starts at **100,000,000** and every spin by anyone adds **5,000** to it. It is
shown in large type at the top of the Play page. Three Jackpots win the whole pot: it is added to
the winner's score, announced in the Gazette, entered in the Hall of Fame and the jackpot winners
list, and the progressive resets to 100,000,000.

### Tips from the regulars

Every spin is random, but successful players often build their bankroll with smaller wagers, raise
their bets when credits are plentiful, save the big bets for jackpot runs, log in daily to make the
most of their spins, and climb the leaderboards through steady play rather than risking everything
on a few spins.

### The odds

The reel weights are in `MDBBS_Game::SYMBOLS` in `includes/class-mdbbs-core.php`. Every reel uses
the same strip. With the shipped weights the machine scores back about 145% of wagers before bonus
spins and the progressive, about 27% of spins win something, and three Jackpots come up roughly once
in 29,000 spins. Because wins and Cherries hand spins back, a day's 10 spins make about 30 pulls of
the handle on average.

---

## Requirements

- WordPress 7.0 or later
- PHP 8.0 or later, 64-bit (the usual build; scores and the jackpot need 64-bit integers)
- MySQL 5.7+ / 8.x or MariaDB 10.3+ (the standard WordPress database)

---

## Installation

1. **Install the plugin.** Copy the `maddog-bbs-slots` folder into your site's `wp-content/plugins/`
   directory, or zip the folder and upload it under **Plugins → Add New → Upload Plugin**.
2. **Activate it.** Activation creates the game's database tables (`wp_mdbbs_*`), seeds the
   progressive at 100,000,000 and schedules the daily job.
3. **Create the game pages.** Go to **Maddog BBS Slots → Dashboard** and click **Create pages**. Choose
   whether to add the home page to a menu: *Don't add it* (the default), one of your theme's menu
   locations, or, on block themes such as Twenty Twenty-Five, *Theme header*. Only the home page
   goes in a site menu; players move between the game's pages with its own navigation bar.
4. **Let players in.** Only signed-in WordPress users can play. Either create accounts yourself or
   enable **Settings → General → Anyone can register** (new-user role *Subscriber*), with an anti-bot
   plugin on the registration form.
5. **Set up cron** so the daily top-up happens at midnight. See below.
6. **Optional: tune the game** under **Maddog BBS Slots → Settings**.

### Installing from the repository

Install from a release zip (or one you build, below) where you can. Two things go wrong when a
copy of the repository is uploaded instead:

**The folder name.** WordPress identifies a plugin by its folder. GitHub's **Download ZIP** names
the folder after the branch (`maddog-bbs-slots-main`), so installing a proper zip later adds a *second*
copy of the plugin rather than updating this one. Rename the folder to `maddog-bbs-slots` before you
activate it, and keep that name.

**Files a web server should not serve.** A clone carries `.git`, the project's entire history; on
many servers anyone who knows the path can read it. Nothing a release zip installs needs that protection:
every PHP file exits when loaded directly (the database schema is a PHP file for exactly that
reason), every directory has an empty `index.php` so nothing can be listed, and the rest is
stylesheets, scripts and public documentation. For copies that carry extra files, the plugin ships
an `.htaccess` that refuses `.git`, `*.sql`, `*.md`, logs and editor leftovers. Apache is the only
server that reads `.htaccess`; on nginx, add this to the server block:

```nginx
location ~ /wp-content/plugins/.*/\.(git|svn)(/|$) { deny all; }
location ~ /wp-content/plugins/.*\.(sql|md|log|ya?ml|lock)$ { deny all; }
```

The plugin also checks itself. **Maddog BBS Slots → Dashboard** has an **Install health** panel, and an
administrator sees a notice on the Plugins screen and the game's own screens, if the folder is not
named `maddog-bbs-slots`, if a `.git` directory is present (it asks your site whether it actually serves
it, and remembers the answer for a day), if any other git file or folder came along (`.gitattributes`, `.gitignore`, `.github` and the like,
anywhere in the plugin), if a second copy of the plugin is installed, or if
files from an older version (such as `sql/install.sql`) were left behind by copying an update over
the old folder. Each notice
explains the fix. Game data lives in the database, so renaming the folder or deleting an extra copy
loses nothing.

### Building a release zip

There is no build step: no compiler, no bundler, no dependencies to install. The plugin is its own
source, and there are three ways to package it.

#### 1. With git (the release build)

From a clone of the repository:

```bash
git archive --format=zip --prefix=maddog-bbs-slots/ -o maddog-bbs-slots-1.7.0.zip HEAD
```

That gives a zip whose single top-level folder is `maddog-bbs-slots`, which is what **Plugins → Add New →
Upload Plugin** expects. It takes the files from the **last commit**, not the working tree, so
uncommitted edits are left out, and `.gitattributes` keeps development-only files (`.gitignore`,
`.gitattributes`, `.github`, `tools/` and the setup guide) out of the archive. On Windows the same command works in Git
Bash or PowerShell wherever `git` is on the path.

#### 2. With PHP, without git

If git is not installed, or you are on Windows without a `zip` command, `tools/build-zip.php` does the
same job with nothing but PHP:

```bash
php tools/build-zip.php
```

That writes `maddog-bbs-slots-<version>.zip` next to the plugin folder, taking the version from the plugin
header, and prints the path, the file count and the size. Pass a path to put it somewhere else:

```bash
php tools/build-zip.php /path/to/maddog-bbs-slots.zip
```

It needs PHP's `zip` extension, which is standard on hosting but sometimes switched off in a
command-line PHP on Windows; the script says so plainly and stops if it is missing. (With the winget
PHP on Windows, add `-d extension_dir="<php folder>\ext" -d extension=zip` to the command.) It packs the
**working tree**, uncommitted edits included, which is the difference from `git archive`: useful while
testing a change, and worth remembering when cutting a release. It skips `.git`, `.github`,
`.gitignore`, `.gitattributes`, `tools/`, `node_modules`, `vendor`, editor leftovers and any zips or
logs lying about, so it produces the same file list as the `git archive` command above. The script
refuses to run over the web, and `tools/` is left out of both builds, so it never reaches a site.

#### 3. Zipping the folder by hand

```bash
cd .. && zip -r maddog-bbs-slots.zip maddog-bbs-slots -x '*/.git*' '*/tools/*' '*/docs/SETUP-BBS-ON-WORDPRESS.md' '*.zip' '*.log'
```

`'*/.git*'` covers the `.git` and `.github` folders and the `.gitignore` and `.gitattributes` files.
On Windows, File Explorer's *Send to → Compressed folder* cannot leave anything out, so use one of the
other methods, or zip a copy of the folder with the items below deleted from it first.

#### What to leave out of the zip

Only the plugin itself belongs in the zip. Leave these out (methods 1 and 2 do it for you):

| Leave out | What it is |
|---|---|
| `.git/` | the repository and its whole history; the one that matters most |
| `.github/` | GitHub settings and workflows |
| `.gitignore`, `.gitattributes`, and any other `.git*` file (`.gitmodules`, `.gitkeep`) | git configuration |
| `tools/` | the zip builder, `build-zip.php` |
| `docs/SETUP-BBS-ON-WORDPRESS.md` | the sysop's site-setup guide; it is about the site, not the plugin |
| `.vscode/`, `.idea/`, `node_modules/`, `vendor/` | editor settings and dependency folders, if you have any |
| `*.zip`, `*.log`, `*.swp`, `*.bak`, `.DS_Store`, `Thumbs.db`, `desktop.ini` | earlier builds, logs and editor or system leftovers |

Everything else ships: `maddog-bbs-slots.php`, `uninstall.php`, `index.php`, `.htaccess`, `README.md`,
`SECURITY.md`, `LICENSE`, and the `admin/`, `assets/`, `docs/` (just its `index.php`), `includes/`,
`maintenance/` and `sql/` folders. If any of the left-out items reach a site anyway, **Maddog BBS Slots →
Dashboard → Install health** lists them.

#### Checking the zip

Whichever you use, the zip should contain **one top-level folder named `maddog-bbs-slots`** with
`maddog-bbs-slots.php` directly inside it, and nothing from the list above. To check, list the zip and
look for anything that should not be there; the second command should print nothing:

```bash
unzip -l maddog-bbs-slots-1.7.0.zip
unzip -l maddog-bbs-slots-1.7.0.zip | grep -E '/\.git|/tools/|SETUP-BBS|\.zip$|\.log$'
```

#### Cutting a release

1. Raise the version in **both** places in `maddog-bbs-slots.php`: the `Version:` line in the plugin header
   and the `MDBBS_VERSION` constant. The header is what WordPress shows; the constant is what the game's
   footer shows and what busts browser caches for the stylesheet and script.
2. If the database tables changed, raise `MDBBS_DB_VERSION` as well, so existing sites update their
   tables on the next page load.
3. Commit and push.
4. Build the zip with `git archive` (method 1), so it matches the commit exactly, and check it as above.
5. Install it on a test site before a live one: **Plugins → Add New → Upload Plugin**, then
   **Replace current with uploaded**. Game data is in the database and survives the update.

### Game pages

| Page | Slug | Shortcode |
|---|---|---|
| Maddog BBS Slots (home and how to play) | `maddog-bbs-slots` | `[mdbbs-home]` |
| Maddog BBS Slots - Play | `maddog-bbs-slots-play` | `[mdbbs-play]` |
| Maddog BBS Slots - Gazette | `maddog-bbs-slots-gazette` | `[mdbbs-gazette]` |
| Maddog BBS Slots - Hall of Fame | `maddog-bbs-slots-hall-of-fame` | `[mdbbs-hof]` |

The home page, Gazette and Hall of Fame are public. Play asks visitors to sign in, and asks a
signed-in user without a player name to choose one first. If you create pages by hand, keep these
slugs: the plugin finds its pages by slug. Each shortcode also answers to an underscore spelling
(`[mdbbs_gazette]`, `[mdbbs_hof]` and so on). The shortcodes from before the rename to Maddog BBS
Slots (`[wpbs-home]`, `[wpbs-play]`, `[wpbs-gazette]`, `[wpbs-hof]`, their underscore spellings and
`[wpb-hof]`) still work, so existing pages and widgets need no editing.

### Widgets: the Gazette and Hall of Fame in a sidebar

Add a **Shortcode** block to a sidebar or footer (**Appearance → Widgets** on classic themes; the
Site Editor's template parts on block themes; or a page builder's Shortcode element) and paste one of:

```
[mdbbs-gazette limit=10 compact=1]   the progressive and the top 10 scores
[mdbbs-hof limit=10 compact=1]      the Hall of Fame records and the top 10 scores
```

| Attribute | Gazette | Hall of Fame |
|---|---|---|
| `limit` | full page: news items (40, up to 200); compact: top scores (10, up to 50) | top scores (20 on the page, 10 compact, up to 100) |
| `compact` | `1` for the widget: the panel only, no title, navigation or footer | same |

### The Gazette

The Gazette shows where the progressive stands, **who played today** (with their spins, winnings and
score), the **top 20 scores**, and the news: new players, big wins (50,000 or more by default),
jackpots, new Hall of Fame records, the first player to reach each rank, bailouts, the daily
top-up, each player's first spin of the day (with the credits and spins they sat down with) and the
end of their day (their last spin, or running out of credits, with their score). News is kept for 30 days by default. Its masthead is the `MDBBS_GAZETTE_NAME` constant in
the plugin's main file.

### The Hall of Fame

A permanent record of:

- the **biggest jackpot** won, all time, and every jackpot winner
- the **highest single spin win**
- the **most consecutive wins**
- the **highest monthly bankroll**, all time and for each of the last 12 months
- the **first player to reach each rank**, from Regular to BBS Legend (see [Ranks](#ranks))

and the top scores.

---

## Administration

The **Maddog BBS Slots** menu in wp-admin (administrators only):

- **Dashboard:** player and jackpot figures, page status, **Create pages** (with the optional menu
  link), widget shortcodes and recent admin activity.
- **Settings:** spins per day (1 to 50), catch-up days (up to 7), starting bankroll, daily top-up,
  bailout threshold and amount, starting jackpot and the amount each spin adds, Gazette options,
  whether new players may join, and **Delete all data** on uninstall. At the foot is the
  **DANGER SECTION**, whose resets cannot be undone and each need a box ticked and a word typed:
  - **Reset the progressive jackpot** to its starting value without anyone winning it (type `RESET`).
  - **Reset all scores**, for a new season: every player keeps their name but goes back to the
    starting bankroll and spins with their stats cleared; their ranks and days played, the Hall of
    Fame, jackpot winners, monthly bests and the progressive are kept (type `SCORES`).
  - **Reset the game to new**: every player, score, record, jackpot win, monthly best, news item and
    the admin log are deleted and the progressive is reseeded. Settings, pages and WordPress accounts
    are kept (type `NEW GAME`).
- **Players:** every player with their score and spins; edit a score, spins left or days played
  (an edit can raise a rank but never lower one), or **delete a
  player** who has left the game or asks for their data to be removed. Their row, monthly history and
  news are deleted; Hall of Fame entries they held stay as "A former player".
- **Maintenance:** run the daily job now, see when it last and next runs, a **daily job log** (one line
  per day for the last 10 days: when it ran, what started it, what it did, and how many attempts there
  were), and copy-ready cron commands.

Every admin action posts to `admin-post.php` with a nonce and a capability check, and is recorded in
the admin log shown on the Dashboard. The disclaimer is shown at the foot of every admin screen.

### Scheduled maintenance (cron)

One job runs daily at midnight in the site's timezone (**Settings → General → Timezone**): every
player is given the day's spins, every bankroll under 10,000 is topped up to 10,000, and old news is
purged. If you change the site's timezone, the job moves to the new midnight by itself. Players are
also given their spins and topped up the first time they visit on a new day, so the game works even
if cron never runs; cron makes the new day show on the scores and the Players screen at midnight.

WP-Cron only runs when someone visits the site. For a live game add a real cron job in your hosting
control panel. The recommended job runs every 5 minutes and triggers all of WordPress's scheduled
tasks:

```bash
curl -s "https://example.com/wp-cron.php?doing_wp_cron" > /dev/null 2>&1
```

Or run the game's job directly, once a day at your site's midnight (cron uses the server's clock,
often UTC):

```bash
php /path/to/wordpress/wp-content/plugins/maddog-bbs-slots/maintenance/daily_maintenance.php --wp-path=/path/to/wordpress
```

Running both is safe: the job takes a database lock and refuses to run twice on the same day. The
Maintenance screen prints these commands with your site's real URL and paths filled in.

### Updating and uninstalling

- **Updating:** replace the plugin folder with the new version. Database changes are applied
  automatically on the next page load.
- **Coming from WP BBS Slots** (1.6.0 and earlier): see the next section.

- **Deactivating** stops the scheduled job but keeps all game data.
- **Deleting** the plugin from the Plugins screen removes the scheduled job. It removes the game's
  tables and settings **only if** *Delete all data* is ticked under **Settings**; otherwise the data
  stays for a later reinstall. The game pages are ordinary pages for you to remove.

### Moving from WP BBS Slots (1.6.0 and earlier)

Version 1.7.0 renamed the plugin from WP BBS Slots to Maddog BBS Slots. WordPress knows a plugin by
its folder, and the folder is now `maddog-bbs-slots` (it was `wp-bbs-slots`), so the new version
installs **beside** the old one rather than replacing it. Your players, scores, Hall of Fame and
settings move across on their own:

1. **Plugins → Add New → Upload Plugin**, choose `maddog-bbs-slots-1.7.0.zip`, then **Activate**.
   Leave the old plugin where it is for now.
2. On activation, Maddog BBS Slots deactivates WP BBS Slots, renames its tables from `wp_wpbbs_*`
   to `wp_mdbbs_*` (a rename: every row stays), moves its options from `wpbbs_*` to `mdbbs_*`, and
   swaps the daily job for the new one. The move is recorded in the admin log on the Dashboard. If a
   table or option already exists under the new name, nothing is overwritten: the old one is kept and
   the log says which. If a table cannot be renamed (a database user without the `ALTER` privilege,
   say), any renames already made are undone, nothing else is changed, the old plugin keeps running,
   and Maddog BBS Slots refuses to activate and says why.
3. Your game pages keep their URLs (`/wp-bbs-slots/`, `/wp-bbs-slots-play/` and so on). Page and menu
   titles that still read exactly as the old plugin wrote them are changed to the new name; titles you
   edited yourself are left alone. **Create pages** on the Dashboard keeps using these pages and does not
   make new ones. If you would rather have the new slugs, edit each page's URL yourself; the game finds
   its pages by ID, so nothing else needs changing (old links to the previous URLs will then stop working).
4. **Then** delete WP BBS Slots from the Plugins screen. Its data has already moved, so its uninstaller
   finds nothing to remove. A notice on the Plugins screen reminds you while it is still installed. Do not
   activate it again: it would start an empty game of its own.
5. **If you run maintenance from a system cron job**, update its command: the script is now in
   `wp-content/plugins/maddog-bbs-slots/maintenance/`. The `WPBBS_WP_PATH` environment variable still
   works, though `MDBBS_WP_PATH` is the new name.

What changes for anyone who customised the game: the shortcodes are now `[mdbbs-home]`,
`[mdbbs-play]`, `[mdbbs-gazette]` and `[mdbbs-hof]` (the old ones still work); the CSS classes and custom
properties are prefixed `mdbbs-` instead of `wpbbs-`, so any theme CSS that targeted `.wpbbs-...`
needs the new prefix; the stylesheet and script handles and the text domain are `maddog-bbs-slots`; and
the admin screens are at `admin.php?page=mdbbs_dashboard` and so on. A player halfway through a spin
when the update lands may see one "expired" message; reloading the page fixes it.

---

## License

Copyright (C) 2026 Bill Mantz, <https://maddogproductions.online/>

Maddog BBS Slots is released under the **GNU General Public License, version 2 or later** (GPLv2+), the
same license as WordPress itself. The full text is in [LICENSE](LICENSE).

You are free to use, modify and redistribute it, including commercially, provided derivative works
are distributed under the same license and keep the copyright notice. It comes with **no warranty**:
as the license says, it is provided "as is", and the entire risk as to its quality and performance
is with you.

---

## Code layout

```
maddog-bbs-slots.php              plugin bootstrap and hooks
.htaccess                     refuses .git, the schema and docs on Apache, for installs from a clone
uninstall.php                 removes the cron job, and the data if "Delete all data" is ticked
SECURITY.md                   how to report a vulnerability, and how input is handled
sql/schema.php                database schema (applied with dbDelta and the site's table prefix)
includes/class-mdbbs-core.php settings, table names, logging, symbols, wagers, ranks
includes/class-mdbbs-health.php warns if the install came from a clone or a branch-named zip
includes/class-mdbbs-migration.php moves the data of the old WP BBS Slots plugin across, once
tools/build-zip.php           builds an installable zip with PHP alone (not shipped in releases)
tools/build-logo.py           draws the ANSI-art logo in assets/img (not shipped in releases)
includes/services/            players, the machine (spins, scores, jackpot, bailout),
                              Hall of Fame records, daily maintenance
includes/frontend/            shortcodes, form and AJAX handlers, UI helpers, page views
admin/                        wp-admin screens
assets/                       stylesheet, the reel-spinning JavaScript and the logo (img/, SVG and PNG)
maintenance/                  optional CLI script for a system cron
```

### Database tables

All tables use the site's table prefix (shown as `wp_`).

| Table | Contents |
|---|---|
| wp_mdbbs_players | one row per player: WordPress user, player name, bankroll, spins, dates of the last top-up and bailout, the dates their day's play was last started and finished, last wager, rank reached and days played, today's play and lifetime stats |
| wp_mdbbs_state | the progressive jackpot |
| wp_mdbbs_jackpots | every jackpot won |
| wp_mdbbs_records | Hall of Fame records: highest spin, longest streak, first to each rank |
| wp_mdbbs_monthly | each player's highest bankroll for each month |
| wp_mdbbs_news | the Gazette |
| wp_mdbbs_admin_log | admin audit log |

Options: `mdbbs_settings`, `mdbbs_db_version`, `mdbbs_page_ids`, `mdbbs_nav_post_id`, `mdbbs_last_daily`, `mdbbs_daily_log`, and `mdbbs_migrated_from_wpbbs` (when the move from WP BBS Slots was made).
