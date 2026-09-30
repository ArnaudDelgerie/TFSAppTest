# TFSAppTest

The fixture app for [TFSAppHub](https://github.com/ArnaudDelgerie/TFSAppHub)
and [TFSAppBundle](https://github.com/ArnaudDelgerie/TFSAppBundle). It
declares every capability the hub's
[contract](https://github.com/ArnaudDelgerie/TFSAppHub/blob/main/CONTRACT.md)
covers, and its pages carry the probes that exercise them, clause by clause.

It is **not an example to copy**. Nothing here is written to be good practice:
each probe exists to pin one specific promise, and several are deliberately
crude so the probe stays independent of the code under test. To write your own
app, start from the hub's README ("Make your first app") and the bundle's
README; to read a real app, read Papermark.

## Run it

Both ways need the hub — `tfsapp-hub` on your path, or its AppImage beside
this checkout.

**Live, from this tree** — the developer's loop:

```sh
composer install
tfsapp-hub dev .
```

The window serves this directory as it is; a dev session installs nothing and
gets its own identity (`dev.` + the manifest's identifier), so it can stay open
beside an installed copy of the same app without either seeing the other's
data.

**Installed, the way a user gets it:**

```sh
mkdir -p out
tfsapp-hub publish . --local out/
tfsapp-hub install <the archive path publish prints>
tfsapp-hub open tfsapp-test
```

`publish` writes a local archive; `install` snapshots it, runs the manifest's
`pre-install` command (the Doctrine migrations), and writes the desktop entry;
`open` gives it a window. `tfsapp-hub list` shows it `ready`.

## What it declares

`tfsapp.config.json` is the fixture's backbone: two workers over four
transports, three `run` aliases (`mcp-serve`, `cleanup`, `spawn-child`), the
pre/post install and update commands, Markdown and directory file
associations, and every `actions` group the contract defines — `secrets`,
`update`, `picker`, `close_guard`, `open_files`, `media` (microphone) and
`paths` (downloads and pictures, with music left undeclared on purpose).

## The probe pages

Each section of the home page pins a contract clause:

| Probe | What it exercises |
|---|---|
| Background job | Messenger sync vs worker mode (§3's `TFS_ASYNC_WORKER`, §2's workers) and a Mercure push round trip (§4's authorization) |
| Secrets demo | `actions.secrets` (§7) over both transports: a PHP route through the loopback bridge, and the WebView's IPC |
| Check for updates | `actions.update` (§7), both transports; for a local archive or a dev session it answers `local_source`, never "newer version" |
| File upload | a plain Symfony upload route, reached through a real file-manager drag-and-drop onto the window |
| Durable file | `APP_UPLOAD_DIR` (§3, §5): a file that must survive an update, a rollback and an export/import round trip |
| Path picker &amp; save dialog | `actions.picker` (§7): `pick_path`, `save_path`, and the optional filters on both |
| Close guards | `actions.close_guard` (§7): frontend document guards through IPC, backend job guards through the bundle's bridge client |
| Open with local files | `actions.open_files` (§7) plus §2's file associations: the receiver sequence, the replay of an unacknowledged request, and file vs directory classification by the app's own backend |
| Microphone capture | `actions.media` (§7): audio-only capture, video refused, device labels, and the reported `TFS_MEDIA_MICROPHONE` — granted is reported, never assumed |
| User directories | `actions.paths` (§3, §7): `TFS_USER_DOWNLOADS_DIR` and `TFS_USER_PICTURES_DIR` resolve, `TFS_USER_MUSIC_DIR` stays absent because it is undeclared |
| Account | an ordinary login, so the session directory (§5) is exercised like any Symfony app's |

The `run` aliases are exercised from the terminal, per §6: `tfsapp-hub run
tfsapp-test cleanup` (standalone: refused while a window is open), and
`mcp-serve` (concurrent: runs beside the window, `run --stop` / `run --replace`
recover it). `spawn-child` spawns a grandchild process and blocks, proving the
host's teardown reaches the whole descendant tree.

## Working against a local TFSAppBundle checkout

The bundle is required from Packagist, like in any app:

```json
"arnauddelgerie/tfs-app-bundle": "^0.1"
```

It has to be: `tfsapp-hub install` runs Composer **inside the installed copy**,
where a `path` repository pointing at a sibling directory does not exist — and
`publish` never ships `vendor/` anyway. A local override can only ever serve
`dev`, which serves this tree in place, so it is a dev-time affair, never
committed.

To test a bundle change here, with the bundle checked out as a sibling:

```sh
composer config repositories.local-bundle path ../TFSAppBundle
composer require 'arnauddelgerie/tfs-app-bundle:@dev'
```

The path repository symlinks, so the checkout's source is what `dev` serves —
edit the bundle, reload the window. To undo, put the committed files back and
let `vendor/` follow the lock again:

```sh
git restore composer.json composer.lock
composer install
```

While the override is in place, `git status` shows it; none of it belongs in a
commit.
