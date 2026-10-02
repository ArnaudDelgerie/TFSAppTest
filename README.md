# TFSAppTest

The fixture app for [TFSAppHub](https://github.com/ArnaudDelgerie/TFSAppHub)
and [TFSAppBundle](https://github.com/ArnaudDelgerie/TFSAppBundle): it
declares every capability the hub's
[contract](https://github.com/ArnaudDelgerie/TFSAppHub/blob/main/CONTRACT.md)
covers, and its pages carry the probes that exercise them.

It is **not an example to copy** — several probes are deliberately crude so
they stay independent of the code under test. To write an app, start from the
[bundle's README](https://github.com/ArnaudDelgerie/TFSAppBundle); to read a
real one, read [Papermark](https://github.com/ArnaudDelgerie/Papermark).

## Run it

Get the hub first: its
[README](https://github.com/ArnaudDelgerie/TFSAppHub#install) covers the
download and every command used below.

From this tree, the developer's loop:

```sh
composer install
tfsapp-hub dev .
```

Installed, the way a user gets it:

```sh
tfsapp-hub install github:ArnaudDelgerie/TFSAppTest
tfsapp-hub open tfsapp-test
```

## The probes

`tfsapp.config.json` declares two workers over four transports, three `run`
aliases, the install and update commands, file associations, and every
`actions` group. Each section of the home page pins a contract clause:

| Probe | What it exercises |
|---|---|
| Background job | Messenger sync vs worker mode and a Mercure round trip (§2, §3, §4) |
| Secrets demo | `actions.secrets` over the bridge and over IPC (§7) |
| Check for updates | `actions.update` over both transports (§7) |
| File upload | a plain upload route, reached by a file-manager drag-and-drop |
| Durable file | `APP_UPLOAD_DIR` across update, rollback and export/import (§3, §5) |
| Path picker &amp; save dialog | `actions.picker`: `pick_path`, `save_path`, filters (§7) |
| Close guards | `actions.close_guard`, frontend and backend guards (§7) |
| Open with local files | `actions.open_files` and file associations (§2, §7) |
| Microphone capture | `actions.media`: audio only, granted reported, never assumed (§7) |
| User directories | `actions.paths`: downloads and pictures declared, music left out (§3, §7) |
| Account | an ordinary login, so the session directory is exercised (§5) |

The `run` aliases are exercised from a terminal (§6): `cleanup` is
standalone, `mcp-serve` runs beside the window, `spawn-child` proves teardown
reaches the whole process tree.

## Against a local TFSAppBundle checkout

The bundle comes from Packagist: `install` runs Composer inside the installed
copy, where a `path` repository has nothing to point at. To try a bundle
change under `dev`, with the bundle checked out beside this one:

```sh
composer config repositories.local-bundle path ../TFSAppBundle
composer require 'arnauddelgerie/tfs-app-bundle:@dev'
```

Never commit it; undo with `git restore composer.json composer.lock` and
`composer install`.
