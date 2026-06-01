# Adoption Evidence

This document records the first public adoption evidence for Minang Muda League, the open source application behind the Liga Anak Piaman Laweh production portal.

## Production Use

- Production site: https://ligaanakpiamanlaweh.com
- Active league brand: Liga Anak Piaman Laweh
- Active season visible on public pages: `Musim 2026`
- Verification date: 2026-06-02
- Repository: `git@github.com:fajarbs08/minangmudaleague.git`

## Verified Public Pages

The following public pages were checked directly against the production domain and returned `200 OK` on 2026-06-02.

| Page | URL | Evidence observed |
| --- | --- | --- |
| Home | `https://ligaanakpiamanlaweh.com/` | Official Liga Anak Piaman Laweh portal metadata for schedules, results, standings, clubs, players, sponsors, and contacts. |
| Clubs | `https://ligaanakpiamanlaweh.com/klub` | Active `Musim 2026` club listing with published club logos and detail pages. |
| Fixtures | `https://ligaanakpiamanlaweh.com/jadwal-pertandingan` | Public fixture page for `Musim 2026`, including match table structure and competition filters. |
| Standings | `https://ligaanakpiamanlaweh.com/klasemen` | Public standings page for `Musim 2026`, including season and club filters. |

## Club Evidence

The production club listing publicly showed `Musim 2026` and multiple registered clubs, including:

- Padang alai junior
- TARUNA FC Kepala Hilalang
- SSB PSKM MANGGUNG
- Subulussalam fc
- BASOKA FC
- SSB KASANG
- SSB PSC
- FAJAR TERBIT
- SSB SUNGAI ABANG
- BARINGIN SATI
- NETRAL UNITED

This is enough to establish that the app is not only a demo repository: it is serving an active youth football league portal with real public season, club, fixture, and standings surfaces.

## What Not To Publish

Do not publish private operational data when documenting adoption:

- admin credentials
- database credentials
- player documents or identity files
- internal review notes
- private storage paths
- server passwords or SSH secrets

Use public pages and aggregate operational evidence only.
