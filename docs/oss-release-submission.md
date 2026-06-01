# OSS Release Submission

## Project

- Name: Minang Muda League
- Production deployment: Liga Anak Piaman Laweh
- Repository: `git@github.com:fajarbs08/minangmudaleague.git`
- First release target: `v1.0.0`
- License: MIT

## Submission Summary

Minang Muda League is an open source Laravel application for running a youth football league. It covers admin-managed club accounts, clubs, officials, players, document verification, DSP, match scheduling, results, standings, identity cards, public QR scan pages, and season-aware public history.

## Adoption Claim

This application is already used by an active league: Liga Anak Piaman Laweh.

Evidence checked on 2026-06-02:

- `https://ligaanakpiamanlaweh.com/` returned `200 OK` and identifies itself as the official Liga Anak Piaman Laweh portal.
- `https://ligaanakpiamanlaweh.com/klub` returned `200 OK`, shows `Musim 2026`, and lists multiple clubs with published logos and detail pages.
- `https://ligaanakpiamanlaweh.com/jadwal-pertandingan` returned `200 OK` and exposes the public fixture surface for the league.
- `https://ligaanakpiamanlaweh.com/klasemen` returned `200 OK` and exposes the public standings surface for the league.

See `docs/adoption.md` for the detailed evidence log.

## Release Checklist

- MIT license included as `LICENSE`.
- README documents installation, production notes, public adoption, and release references.
- Adoption evidence is documented without exposing private credentials or participant documents.
- Changelog includes `v1.0.0` release notes.
- Release tag: `v1.0.0`.

## Suggested GitHub Release Text

```text
Minang Muda League v1.0.0 is the first OSS release of the Laravel application powering Liga Anak Piaman Laweh.

The release includes league administration, club and account management, official/player records, document verification workflows, DSP, match scheduling, results, standings, public club/player/fixture pages, QR-backed identity cards, and season-aware history.

Adoption evidence: the production site https://ligaanakpiamanlaweh.com was checked on 2026-06-02 and serves active Liga Anak Piaman Laweh public pages for Musim 2026, including clubs, fixtures, and standings.
```
