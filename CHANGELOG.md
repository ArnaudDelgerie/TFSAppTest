# Changelog

## 0.7.1

Validation entry for the microphone indicator and local revocation checks —
the WebAudio/WAV recorder and the MediaRecorder retest button
are exercised against an installed copy, so the launch reads a real
`data/config.json` and the window title can be checked while a capture runs.

## 0.6.1

Validation entry for remote sources (install and update from a published
release) — a v0.6.0 install
updated to this release should
move `app_version` to 0.6.1, follow the new tag, and still roll back to 0.6.0
with `tfsapp-hub rollback`.

## 0.6.0

Validation entry for `run --stop` / `run --replace` —
reuses the `mcp-serve` (concurrent, long-running) and `cleanup`
(standalone, quick-exit) fixtures: a v0.5.9 install running this build's
`run --stop`/`run --replace <alias>` should recover an orphaned `mcp-serve`
without a manual `kill`, even while `data/config.json` still records 0.5.9.

## 0.5.9

Validation entry for `--rollback` — a v0.5.8 install
updated to this release, then `./tfsapp-test_0.5.9_amd64.AppImage --rollback`
should re-download v0.5.8, verify it, restore the pre-update database, and
swap the binary back.

## 0.5.8

Validation entry for the download & install update check —
the target release: a v0.5.7 install running `--update` should download,
verify against SHA256SUMS.txt, and swap in this AppImage in place.

## 0.5.7

Validation entry for the download & install update check —
the starting install: launched, then updated to v0.5.8 via
`./tfsapp-test_0.5.7_amd64.AppImage --update`.

## 0.5.6

Validation entry for the "update available"
case: a v0.5.5 install should detect this release.

## 0.5.5

Adds the "Check for updates" demo fixture for `actions.update`
(both transports) — validation baseline for the in-app
update check's end-to-end evidence.

## 0.5.4

Validation entry for the build-reuse questions — decline reuse, rebuild, keep
on default.

## 0.5.3

Validation entry for the build-reuse questions — accept reuse of an existing
build.

## 0.5.2

Validation entry for the build-reuse questions — fresh build, no reuse,
decline keep.

## 0.5.1

Earlier entry, not the version being released.
