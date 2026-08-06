# Changelog

## 0.6.0

Validation entry for the station's plan 057 (`run --stop` / `run --replace`) —
reuses plan 047's `mcp-serve` (concurrent, long-running) and `cleanup`
(standalone, quick-exit) fixtures: a v0.5.9 install running this build's
`run --stop`/`run --replace <alias>` should recover an orphaned `mcp-serve`
without a manual `kill`, even while `data/config.json` still records 0.5.9.

## 0.5.9

Validation entry for the station's plan 041 (`--rollback`) — a v0.5.8 install
updated to this release, then `./tfsapp-test_0.5.9_amd64.AppImage --rollback`
should re-download v0.5.8, verify it, restore the pre-update database, and
swap the binary back.

## 0.5.8

Validation entry for the station's plan 040 (download & install update) —
the target release: a v0.5.7 install running `--update` should download,
verify against SHA256SUMS.txt, and swap in this AppImage in place.

## 0.5.7

Validation entry for the station's plan 040 (download & install update) —
the starting install: launched, then updated to v0.5.8 via
`./tfsapp-test_0.5.7_amd64.AppImage --update`.

## 0.5.6

Validation entry for the station's plan 035 — the "update available"
case: a v0.5.5 install should detect this release.

## 0.5.5

Adds the "Check for updates" demo fixture for the station's plan 035
(actions.update, both transports) — validation baseline for the in-app
update check's end-to-end evidence.

## 0.5.4

Validation entry for plan 027 — decline reuse, rebuild, keep on default.

## 0.5.3

Validation entry for plan 027 — accept reuse of an existing build.

## 0.5.2

Validation entry for plan 027 — fresh build, no reuse, decline keep.

## 0.5.1

Earlier entry, not the version being released.
