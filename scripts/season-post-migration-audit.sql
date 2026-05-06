-- Season post-migration audit
-- Jalankan setelah deploy season architecture di environment target.
-- Contoh:
-- mysql -u <user> -p <database> < scripts/season-post-migration-audit.sql

SELECT 'active_season_count' AS audit_key, COUNT(*) AS audit_value
FROM seasons
WHERE is_active = 1;

SELECT id, name, slug, status, is_active, starts_at, ends_at, archived_at
FROM seasons
ORDER BY id;

SELECT
    season_id,
    COUNT(*) AS season_clubs_count
FROM season_clubs
GROUP BY season_id
ORDER BY season_id;

SELECT
    season_id,
    COUNT(*) AS season_players_count
FROM season_players
GROUP BY season_id
ORDER BY season_id;

SELECT
    season_id,
    COUNT(*) AS season_officials_count
FROM season_officials
GROUP BY season_id
ORDER BY season_id;

SELECT
    season_id,
    COUNT(*) AS match_schedules_count
FROM match_schedules
GROUP BY season_id
ORDER BY season_id;

SELECT
    season_id,
    COUNT(*) AS lineup_lists_count
FROM lineup_lists
GROUP BY season_id
ORDER BY season_id;

SELECT
    season_id,
    COUNT(*) AS match_goals_count
FROM match_goals
GROUP BY season_id
ORDER BY season_id;

SELECT 'match_schedules_without_season' AS audit_key, COUNT(*) AS audit_value
FROM match_schedules
WHERE season_id IS NULL;

SELECT 'lineup_lists_without_season' AS audit_key, COUNT(*) AS audit_value
FROM lineup_lists
WHERE season_id IS NULL;

SELECT 'match_goals_without_season' AS audit_key, COUNT(*) AS audit_value
FROM match_goals
WHERE season_id IS NULL;

SELECT 'player_age_groups_without_season' AS audit_key, COUNT(*) AS audit_value
FROM player_age_groups
WHERE season_id IS NULL;

SELECT 'official_age_groups_without_season' AS audit_key, COUNT(*) AS audit_value
FROM official_age_groups
WHERE season_id IS NULL;

SELECT 'orphan_match_club_a_snapshot' AS audit_key, COUNT(*) AS audit_value
FROM match_schedules ms
LEFT JOIN season_clubs sc ON sc.id = ms.club_a_season_id
WHERE ms.club_a_season_id IS NOT NULL
  AND sc.id IS NULL;

SELECT 'orphan_match_club_b_snapshot' AS audit_key, COUNT(*) AS audit_value
FROM match_schedules ms
LEFT JOIN season_clubs sc ON sc.id = ms.club_b_season_id
WHERE ms.club_b_season_id IS NOT NULL
  AND sc.id IS NULL;

SELECT 'orphan_lineup_season_club' AS audit_key, COUNT(*) AS audit_value
FROM lineup_lists ll
LEFT JOIN season_clubs sc ON sc.id = ll.season_club_id
WHERE ll.season_club_id IS NOT NULL
  AND sc.id IS NULL;

SELECT 'orphan_goal_season_club' AS audit_key, COUNT(*) AS audit_value
FROM match_goals mg
LEFT JOIN season_clubs sc ON sc.id = mg.season_club_id
WHERE mg.season_club_id IS NOT NULL
  AND sc.id IS NULL;

SELECT 'orphan_goal_season_player' AS audit_key, COUNT(*) AS audit_value
FROM match_goals mg
LEFT JOIN season_players sp ON sp.id = mg.season_player_id
WHERE mg.season_player_id IS NOT NULL
  AND sp.id IS NULL;

SELECT 'orphan_goal_assist_season_player' AS audit_key, COUNT(*) AS audit_value
FROM match_goals mg
LEFT JOIN season_players sp ON sp.id = mg.assist_season_player_id
WHERE mg.assist_season_player_id IS NOT NULL
  AND sp.id IS NULL;

SELECT 'orphan_lineup_pivot_season_player' AS audit_key, COUNT(*) AS audit_value
FROM lineup_list_player llp
LEFT JOIN season_players sp ON sp.id = llp.season_player_id
WHERE llp.season_player_id IS NOT NULL
  AND sp.id IS NULL;

SELECT player_id, age_group_id, season_id, COUNT(*) AS duplicate_count
FROM player_age_groups
GROUP BY player_id, age_group_id, season_id
HAVING COUNT(*) > 1;

SELECT official_id, age_group_id, season_id, COUNT(*) AS duplicate_count
FROM official_age_groups
GROUP BY official_id, age_group_id, season_id
HAVING COUNT(*) > 1;

SELECT season_id, club_id, COUNT(*) AS duplicate_count
FROM season_clubs
GROUP BY season_id, club_id
HAVING COUNT(*) > 1;

SELECT season_id, player_id, COUNT(*) AS duplicate_count
FROM season_players
GROUP BY season_id, player_id
HAVING COUNT(*) > 1;

SELECT season_id, official_id, COUNT(*) AS duplicate_count
FROM season_officials
GROUP BY season_id, official_id
HAVING COUNT(*) > 1;

SELECT
    s.name AS season_name,
    sc.name AS club_name,
    COUNT(sp.id) AS players,
    COUNT(so.id) AS officials
FROM seasons s
LEFT JOIN season_clubs sc ON sc.season_id = s.id
LEFT JOIN season_players sp ON sp.season_id = s.id AND sp.club_id = sc.club_id
LEFT JOIN season_officials so ON so.season_id = s.id AND so.club_id = sc.club_id
GROUP BY s.id, s.name, sc.id, sc.name
ORDER BY s.id, sc.name;
