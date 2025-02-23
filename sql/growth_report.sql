-- --------------------------------------------------------
-- File: growth_report.sql
-- Description: Query for generating the Net Growth Report
-- Author: [WA0TJT & Claude 3.5 Sonnet]
-- Created: 2023-07-30
-- Last Modified: 2023-07-31
-- --------------------------------------------------------

-- This query generates a report showing net growth over time
-- It compares current year data with previous year data
-- and calculates historical averages

-- Define variables for current date and year
-- --------------------------------------------------------

SET @current_date = CURDATE();
SET @current_year = YEAR(@current_date);
SET @current_month = MONTH(@current_date);

WITH net_counts AS (
    -- This CTE calculates the counts for each net and activity
    SELECT 
        CASE 
            WHEN `netcall` IN ('PCARG', 'NR0AD') THEN 'NR0AD/PCARG'
            WHEN `netcall` IN ('W0KCN', 'NARES', 'KCNARES') THEN 'W0KCN/NARES/KCNARES'
            ELSE `netcall`
        END AS `grouped_netcall`,
        -- Group activities into predefined categories
        CASE
            WHEN activity LIKE '%2 Meter%Voice%' THEN '2 Meter Voice Net'
            WHEN activity LIKE '%2 Meter%Digital%' THEN '2 Meter Digital Net'
            WHEN activity LIKE '%70cm%Voice%' THEN '70cm Voice Net'
            WHEN activity LIKE '%70cm%Digital%' THEN '70cm Digital Net'
            WHEN activity LIKE '%6 Meter%' THEN '6 Meter Net'
            WHEN activity LIKE '%DMR%' THEN 'DMR Net'
            WHEN activity LIKE '%Weather%' OR activity LIKE '%Severe WX%' OR activity LIKE '%SkyWarn%' THEN 'Weather Net'
            WHEN activity LIKE '%Training%' AND activity NOT LIKE '%Test%' THEN 'Training Net'
            WHEN activity LIKE '%ARES%' AND activity LIKE '%Net%' AND activity NOT LIKE '%Test%' THEN 'ARES Net'
            WHEN activity LIKE '%Monthly Meeting%' OR activity LIKE '%Meeting Net%' THEN 'Monthly Meeting'
            WHEN activity LIKE '%Planning Meeting%' THEN 'Planning Meeting'
            WHEN activity LIKE '%Board Meeting%' THEN 'Board Meeting'
            WHEN activity LIKE '%SET%' OR activity LIKE '%Simulated Emergency Test%' THEN 'SET Exercise'
            WHEN activity LIKE '%Tornado Drill%' THEN 'Tornado Drill'
            WHEN activity LIKE '%Shelter in Place%' THEN 'Shelter in Place Exercise'
            WHEN activity LIKE '%Field Day%' THEN 'Field Day'
            WHEN activity LIKE '%Marathon%' OR activity LIKE '%Triathlon%' THEN 'Race Event'
            WHEN activity LIKE '%Fair%' OR activity LIKE '%Night Out%' THEN 'Public Event'
            WHEN activity LIKE '%Digital%' AND activity NOT LIKE '%Test%' THEN 'Other Digital Net'
            WHEN activity LIKE '%Voice%' AND activity NOT LIKE '%Test%' THEN 'Other Voice Net'
            WHEN activity NOT LIKE '%Test%' THEN 'Other'
            ELSE NULL
        END AS `grouped_activity`,
        YEAR(`dttm`) AS `year`,
        MONTH(`dttm`) AS `month`,
        COUNT(DISTINCT `netID`) AS `net_count`,
        COUNT(DISTINCT 
            CASE 
                WHEN `frequency` IN ('Multiple Bands', '80/40 Meters') THEN `callsign`
                ELSE CONCAT(`netID`, '-', `callsign`)
            END
        ) AS `callsign_count`
    FROM `netcontrolcp_ncm`.`NetLog`
    WHERE 
        `netID` != 0
        AND `netcall` NOT IN ('TE0ST', 'TEST')
        AND `netcall` NOT LIKE '%TEST%'
        AND `netcall` IN ('W0KCN', 'PCARG', 'NR0AD', 'NARES', 'KCNARES')
        AND NOT (`netcall` = '' AND `activity` = '')
    GROUP BY `grouped_netcall`, `grouped_activity`, `year`, `month`
)
-- Main SELECT statement to generate the report
SELECT 
    `grouped_netcall`,
    `grouped_activity`,
    -- Calculate historical average
    ROUND(AVG(CASE WHEN `year` < @current_year - 1 THEN `callsign_count` / `net_count` END), 2) AS `Past %`,
    -- Generate monthly comparisons (current year vs previous year)
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 1 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 1 THEN `callsign_count` END), 0)
    ) AS `Jan`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 2 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 2 THEN `callsign_count` END), 0)
    ) AS `Feb`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 3 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 3 THEN `callsign_count` END), 0)
    ) AS `Mar`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 4 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 4 THEN `callsign_count` END), 0)
    ) AS `Apr`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 5 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 5 THEN `callsign_count` END), 0)
    ) AS `May`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 6 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 6 THEN `callsign_count` END), 0)
    ) AS `Jun`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 7 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 7 THEN `callsign_count` END), 0)
    ) AS `Jul`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 8 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 8 THEN `callsign_count` END), 0)
    ) AS `Aug`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 9 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 9 THEN `callsign_count` END), 0)
    ) AS `Sep`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 10 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 10 THEN `callsign_count` END), 0)
    ) AS `Oct`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 11 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 11 THEN `callsign_count` END), 0)
    ) AS `Nov`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 12 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 12 THEN `callsign_count` END), 0)
    ) AS `Dec`,
    -- Calculate year-to-date percentage
    CONCAT(
        ROUND(
            (SUM(CASE WHEN `year` = @current_year THEN `callsign_count` ELSE 0 END) /
            NULLIF(SUM(CASE WHEN `year` = @current_year - 1 THEN `callsign_count` ELSE 0 END), 0) * 100),
        2),
        '%'
    ) AS `YTD %`
FROM net_counts
WHERE `grouped_activity` IS NOT NULL
GROUP BY `grouped_netcall`, `grouped_activity`

UNION ALL

SELECT 
    `grouped_netcall`,
    'TOTAL' AS `grouped_activity`,
    ROUND(AVG(CASE WHEN `year` < @current_year - 1 THEN `callsign_count` / `net_count` END), 2) AS `Past %`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 1 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 1 THEN `callsign_count` END), 0)
    ) AS `Jan`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 2 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 2 THEN `callsign_count` END), 0)
    ) AS `Feb`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 3 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 3 THEN `callsign_count` END), 0)
    ) AS `Mar`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 4 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 4 THEN `callsign_count` END), 0)
    ) AS `Apr`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 5 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 5 THEN `callsign_count` END), 0)
    ) AS `May`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 6 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 6 THEN `callsign_count` END), 0)
    ) AS `Jun`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 7 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 7 THEN `callsign_count` END), 0)
    ) AS `Jul`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 8 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 8 THEN `callsign_count` END), 0)
    ) AS `Aug`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 9 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 9 THEN `callsign_count` END), 0)
    ) AS `Sep`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 10 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 10 THEN `callsign_count` END), 0)
    ) AS `Oct`,
 CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 11 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 11 THEN `callsign_count` END), 0)
    ) AS `Nov`,
    CONCAT(
        IFNULL(SUM(CASE WHEN `year` = @current_year - 1 AND `month` = 12 THEN `callsign_count` END), 0),
        '/',
        IFNULL(SUM(CASE WHEN `year` = @current_year AND `month` = 12 THEN `callsign_count` END), 0)
    ) AS `Dec`,
    CONCAT(
        ROUND(
            (SUM(CASE WHEN `year` = @current_year THEN `callsign_count` ELSE 0 END) /
            NULLIF(SUM(CASE WHEN `year` = @current_year - 1 THEN `callsign_count` ELSE 0 END), 0) * 100),
        2),
        '%'
    ) AS `YTD %`
FROM net_counts
GROUP BY `grouped_netcall`
ORDER BY `grouped_netcall`, 
    CASE 
        WHEN `grouped_activity` = 'TOTAL' THEN 1 
        ELSE 0 
    END, 
    `grouped_activity`;