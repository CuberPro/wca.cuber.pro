SET CHARACTER SET utf8mb4;
--
-- Indexes for table `Competitions`
--
ALTER TABLE `Competitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `countryId` (`countryId`),
  ADD KEY `year_month_day` (`year`,`month`,`day`);

--
-- Indexes for table `Continents`
--
ALTER TABLE `Continents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Countries`
--
ALTER TABLE `Countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `continentId` (`continentId`);

--
-- Indexes for table `Events`
--
ALTER TABLE `Events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Formats`
--
ALTER TABLE `Formats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Persons`
--
ALTER TABLE `Persons`
  ADD PRIMARY KEY (`id`,`subid`),
  ADD KEY `countryId` (`countryId`),
  ADD KEY `name` (`name`),
  ADD KEY `gender` (`gender`),
  ADD KEY `subid` (`subid`);

--
-- Indexes for table `RanksAverage`
--
ALTER TABLE `RanksAverage`
  ADD PRIMARY KEY (`personId`,`eventId`),
  ADD KEY `personId` (`personId`),
  ADD KEY `eventId` (`eventId`),
  ADD KEY `worldRank` (`worldRank`),
  ADD KEY `continentRank` (`continentRank`),
  ADD KEY `countryRank` (`countryRank`);

--
-- Indexes for table `RanksSingle`
--
ALTER TABLE `RanksSingle`
  ADD PRIMARY KEY (`personId`,`eventId`),
  ADD KEY `personId` (`personId`),
  ADD KEY `eventId` (`eventId`),
  ADD KEY `worldRank` (`worldRank`),
  ADD KEY `continentRank` (`continentRank`),
  ADD KEY `countryRank` (`countryRank`);

--
-- Indexes for table `Results`
--
ALTER TABLE `Results`
  ADD KEY `personId` (`personId`),
  ADD KEY `best` (`best`),
  ADD KEY `average` (`average`),
  ADD KEY `competitionId` (`competitionId`),
  ADD KEY `eventId` (`eventId`),
  ADD KEY `personCountryId` (`personCountryId`),
  ADD KEY `regionalSingleRecord` (`regionalSingleRecord`),
  ADD KEY `regionalAverageRecord` (`regionalAverageRecord`);

--
-- Indexes for table `RoundTypes`
--
ALTER TABLE `RoundTypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Scrambles`
--
ALTER TABLE `Scrambles`
  ADD PRIMARY KEY (`scrambleId`);

-- ------------------------- Below Are Custom Tables ----------------------------------

--
-- Table for rankings considering gender
--
DROP TABLE IF EXISTS `RanksGender`;
CREATE TABLE `RanksGender` (
  `personId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `eventId` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `gender` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `best` int(11) NOT NULL DEFAULT '0',
  `worldRank` int(11) NOT NULL DEFAULT '0',
  `continentRank` int(11) NOT NULL DEFAULT '0',
  `countryRank` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `RanksGender`
--
ALTER TABLE `RanksGender`
  ADD PRIMARY KEY (`personId`,`eventId`,`gender`,`type`) USING BTREE,
  ADD KEY `personId` (`personId`),
  ADD KEY `eventId` (`eventId`),
  ADD KEY `worldRank` (`worldRank`),
  ADD KEY `continentRank` (`continentRank`),
  ADD KEY `countryRank` (`countryRank`);

--
-- Table Storing everyone's KinchScore
--
DROP TABLE IF EXISTS `KinchScores`;
CREATE TABLE `KinchScores` (
  `personId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `countryId` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `continentId` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventId` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `worldSame` decimal(16,2) NOT NULL DEFAULT '0.00',
  `worldAll` decimal(16,2) NOT NULL DEFAULT '0.00',
  `continentSame` decimal(16,2) NOT NULL DEFAULT '0.00',
  `continentAll` decimal(16,2) NOT NULL DEFAULT '0.00',
  `countrySame` decimal(16,2) NOT NULL DEFAULT '0.00',
  `countryAll` decimal(16,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `KinchScores`
--
ALTER TABLE `KinchScores`
 ADD PRIMARY KEY (`personId`,`eventId`), ADD KEY `countryId` (`countryId`), ADD KEY `continentId` (`continentId`), ADD KEY `gender` (`gender`), ADD KEY `eventId` (`eventId`);

-- ------------------------- Below Are Custom Functions ----------------------------------

DELIMITER $$

-- calculating ranks, caller should initialize global variables first
DROP FUNCTION IF EXISTS `multiRank`$$
CREATE FUNCTION `multiRank` (
  best varchar(10),
  gender char(1),
  event varchar(6),
  region varchar(50))
RETURNS INT
BEGIN
  IF @RANK IS NULL OR @COUNT IS NULL OR @LAST_BEST IS NULL OR @LAST_GENDER IS NULL OR @LAST_REGION IS NULL THEN
    SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
  END IF;
  IF gender<>@LAST_GENDER OR event<>@LAST_EVENT OR region<>@LAST_REGION THEN
    SET @RANK=0,@COUNT=0,@LAST_BEST=0;
  END IF;

  SET @COUNT = @COUNT + 1;
  IF best<>@LAST_BEST THEN
    SET @RANK = @COUNT;
  END IF;
  SET @LAST_BEST = best;
  SET @LAST_GENDER = gender;
  SET @LAST_EVENT = event;
  SET @LAST_REGION = region;

  RETURN(@RANK);
END$$

-- A function to calculate scores for multiple blindfolded
DROP FUNCTION IF EXISTS `mbfScore`$$
CREATE FUNCTION `mbfScore` (
  result varchar(10))
RETURNS DECIMAL(16,4)
BEGIN
  DECLARE difference INT;
  DECLARE time INT;
  SET difference = 99 - CAST(SUBSTRING(result, 1, 2) AS SIGNED);
  SET time = CAST(SUBSTRING(result, 3, 5) AS SIGNED);
  RETURN(difference + ROUND((3600 - time) / 3600, 4));
END$$

-- A function to calculate kinch scores
DROP FUNCTION IF EXISTS `kinchScore`$$
CREATE FUNCTION `kinchScore` (
  result varchar(10),
  eventId varchar(6),
  record varchar(10),
  lastScore DECIMAL(16,2))
RETURNS DECIMAL(16,2)
BEGIN
  DECLARE newScore DECIMAL(16,2);
  IF result IS NULL THEN
    RETURN(0.00);
  END IF;
  IF record IS NULL THEN
    RETURN(100.00);
  END IF;
  IF eventId='333mbf' THEN
    RETURN(ROUND(mbfScore(result) * 100 / mbfScore(record), 2));
  ELSEIF eventId IN ('333fm', '333bf') THEN
    SET newScore=ROUND(record * 100 / result, 2);
    RETURN(IF(newScore > lastScore, newScore, lastScore));
  ELSE
    RETURN(ROUND(record * 100 / result, 2));
  END IF;
END$$

DELIMITER ;

-- ------------------------- Below Are Calculating Gender Ranks ----------------------------------

-- World Single Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
INSERT INTO `RanksGender` (`personId`, `eventId`, `gender`, `type`, `best`, `worldRank`)
  SELECT `a`.`personId`, `a`.`eventId`, `a`.`gender`, 's' AS `type`, `a`.`best`, multiRank(`best`, `gender`, `eventId`, 'world') AS `worldRank` FROM (
    SELECT `rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`,`rk`.`worldRank`
      FROM `RanksSingle` `rk`
      LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
      WHERE `p`.`subid`=1 AND `rk`.`worldRank`>0
      ORDER BY `p`.`gender`,`rk`.`eventId`,`rk`.`worldRank`
  ) `a`;

-- World Average Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
INSERT INTO `RanksGender` (`personId`, `eventId`, `gender`, `type`, `best`, `worldRank`)
  SELECT `a`.`personId`, `a`.`eventId`, `a`.`gender`, 'a' AS `type`, `a`.`best`, multiRank(`best`, `gender`, `eventId`, 'world') AS `worldRank` FROM (
    SELECT `rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`,`rk`.`worldRank`
      FROM `RanksAverage` `rk`
      LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
      WHERE `p`.`subid`=1 AND `rk`.`worldRank`>0
      ORDER BY `p`.`gender`,`rk`.`eventId`,`rk`.`worldRank`
  ) `a`;

-- Continent Single Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
UPDATE `RanksGender` `r`, (SELECT `cy`.`continentId`,`rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`
  FROM `RanksSingle` `rk`
  LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
  LEFT JOIN `Countries` `cy` ON `p`.`countryId`=`cy`.`id`
  WHERE `p`.`subid`=1 AND `rk`.`continentRank`>0
  ORDER BY `cy`.`continentId`,`p`.`gender`,`rk`.`eventId`,`rk`.`continentRank`
) `a`
SET `r`.`continentRank`=multiRank(`a`.`best`, `a`.`gender`, `a`.`eventId`, `a`.`continentId`)
WHERE `r`.`personId`=`a`.`personId` AND `r`.`eventId`=`a`.`eventId` AND `r`.`type`='s';

-- Continent Average Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
UPDATE `RanksGender` `r`, (SELECT `cy`.`continentId`,`rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`
  FROM `RanksAverage` `rk`
  LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
  LEFT JOIN `Countries` `cy` ON `p`.`countryId`=`cy`.`id`
  WHERE `p`.`subid`=1 AND `rk`.`continentRank`>0
  ORDER BY `cy`.`continentId`,`p`.`gender`,`rk`.`eventId`,`rk`.`continentRank`
) `a`
SET `r`.`continentRank`=multiRank(`a`.`best`, `a`.`gender`, `a`.`eventId`, `a`.`continentId`)
WHERE `r`.`personId`=`a`.`personId` AND `r`.`eventId`=`a`.`eventId` AND `r`.`type`='a';

-- Region Single Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
UPDATE `RanksGender` `r`, (SELECT `p`.`countryId`,`rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`
  FROM `RanksSingle` `rk`
  LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
  WHERE `p`.`subid`=1 AND `rk`.`countryRank`>0
  ORDER BY `p`.`countryId`,`p`.`gender`,`rk`.`eventId`,`rk`.`countryRank`
) `a`
SET `r`.`countryRank`=multiRank(`a`.`best`, `a`.`gender`, `a`.`eventId`, `a`.`countryId`)
WHERE `r`.`personId`=`a`.`personId` AND `r`.`eventId`=`a`.`eventId` AND `r`.`type`='s';

-- Region Average Rankings
SET @RANK=0,@COUNT=0,@LAST_BEST=0,@LAST_GENDER = '',@LAST_EVENT='',@LAST_REGION='';
UPDATE `RanksGender` `r`, (SELECT `p`.`countryId`,`rk`.`personId`,`p`.`name`,`p`.`gender`,`rk`.`eventId`,`rk`.`best`
  FROM `RanksAverage` `rk`
  LEFT JOIN `Persons` `p` ON `rk`.`personId`=`p`.`id`
  WHERE `p`.`subid`=1 AND `rk`.`countryRank`>0
  ORDER BY `p`.`countryId`,`p`.`gender`,`rk`.`eventId`,`rk`.`countryRank`
) `a`
SET `r`.`countryRank`=multiRank(`a`.`best`, `a`.`gender`, `a`.`eventId`, `a`.`countryId`)
WHERE `r`.`personId`=`a`.`personId` AND `r`.`eventId`=`a`.`eventId` AND `r`.`type`='a';

-- ------------------------- Below Are Calculating Kinch Scores ----------------------------------

-- Pull person data from Ranks, people/event not existing in this table has 0.00 scores
INSERT INTO `KinchScores` (`personId`, `countryId`, `continentId`, `gender`, `eventId`)
  SELECT `rk`.`personId`, `cy`.`id` AS `countryId`, `ct`.`id` AS `continentId`, `p`.`gender`, `rk`.`eventId`
    FROM `RanksAverage` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id`
    LEFT JOIN `Countries` AS `cy` ON `p`.`countryId`=`cy`.`id`
    LEFT JOIN `Continents` AS `ct` ON `cy`.`continentId`=`ct`.`id`
    WHERE `rk`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777') AND `p`.`subid`=1;
INSERT INTO `KinchScores` (`personId`, `countryId`, `continentId`, `gender`, `eventId`)
  SELECT `rk`.`personId`, `cy`.`id` AS `countryId`, `ct`.`id` AS `continentId`, `p`.`gender`, `rk`.`eventId`
    FROM `RanksSingle` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id`
    LEFT JOIN `Countries` AS `cy` ON `p`.`countryId`=`cy`.`id`
    LEFT JOIN `Continents` AS `ct` ON `cy`.`continentId`=`ct`.`id`
    WHERE `rk`.`eventId` IN ('333fm', '333bf', '444bf', '555bf', '333mbf') AND `p`.`subid`=1;

-- -------------------------- Calculating Overall Scores according to WR -----------------------------

-- Temporary table storing every WRs
DROP TABLE IF EXISTS `WR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `WR`
  SELECT `eventId`, `best`, 'a' AS `type` FROM `RanksAverage` WHERE `worldRank`=1 GROUP BY `eventId`, `best`
  UNION
  SELECT `eventId`, `best`, 's' AS `type` FROM `RanksSingle` WHERE `worldRank`=1 GROUP BY `eventId`, `best`;

ALTER TABLE `WR` ADD KEY `eventId` (`eventId`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksAverage` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `WR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `r`.`type`='a'
SET `k`.`worldAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldAll`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `WR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `r`.`type`='s'
SET `k`.`worldAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldAll`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `WR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `r`.`type`='s'
SET `k`.`worldAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldAll`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `WR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `r`.`type`='s'
SET `k`.`worldAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldAll`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- -------------------------- Calculating Gender Scores according to Gender WR -----------------------------

-- Temporary table storing every WRs considering gender
DROP TABLE IF EXISTS `gWR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `gWR`
  SELECT `eventId`, `best`, `type`, `gender`
    FROM `RanksGender`
    WHERE `worldRank`=1
    GROUP BY `eventId`,`gender`,`type`, `best`;
ALTER TABLE `gWR` ADD KEY `eventId` (`eventId`), ADD KEY `gender` (`gender`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='a'
  LEFT JOIN `gWR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `r`.`type`='a'
SET `k`.`worldSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldSame`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gWR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `r`.`type`='s'
SET `k`.`worldSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldSame`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gWR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `r`.`type`='s'
SET `k`.`worldSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldSame`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gWR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `r`.`type`='s'
SET `k`.`worldSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`worldSame`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- -------------------------- Calculating Overall Scores according to CR -----------------------------

-- Temporary table storing every CRs
DROP TABLE IF EXISTS `CR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `CR`
  SELECT `cy`.`continentId`, `rk`.`eventId`, `rk`.`best`, 'a' AS `type`
    FROM `RanksAverage` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    LEFT JOIN `Countries` AS `cy` ON `p`.`countryId`=`cy`.`id`
    WHERE `rk`.`continentRank`=1 GROUP BY `continentId`, `eventId`, `best`
  UNION
  SELECT `cy`.`continentId`, `rk`.`eventId`, `rk`.`best`, 's' AS `type`
    FROM `RanksSingle` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    LEFT JOIN `Countries` AS `cy` ON `p`.`countryId`=`cy`.`id`
    WHERE `rk`.`continentRank`=1 GROUP BY `continentId`, `eventId`, `best`;

ALTER TABLE `CR` ADD KEY `eventId` (`eventId`), ADD KEY `continentId` (`continentId`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksAverage` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `CR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='a'
SET `k`.`continentAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentAll`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `CR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentAll`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `CR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentAll`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `CR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentAll`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- -------------------------- Calculating Gender Scores according to Gender CR -----------------------------

-- Temporary table storing every CRs considering gender
DROP TABLE IF EXISTS `gCR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `gCR`
  SELECT `cy`.`continentId`, `rk`.`eventId`, `rk`.`best`, `rk`.`type`, `rk`.`gender`
    FROM `RanksGender` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    LEFT JOIN `Countries` AS `cy` ON `p`.`countryId`=`cy`.`id`
    WHERE `rk`.`continentRank`=1
    GROUP BY `cy`.`continentId`,`rk`.`eventId`,`rk`.`gender`,`rk`.`type`, `best`;
ALTER TABLE `gCR` ADD KEY `eventId` (`eventId`), ADD KEY `gender` (`gender`), ADD KEY `continentId` (`continentId`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='a'
  LEFT JOIN `gCR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='a'
SET `k`.`continentSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentSame`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gCR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentSame`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gCR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentSame`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gCR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`continentId`=`r`.`continentId` AND `r`.`type`='s'
SET `k`.`continentSame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`continentSame`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- -------------------------- Calculating Overall Scores according to NR -----------------------------

-- Temporary table storing every NRs
DROP TABLE IF EXISTS `NR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `NR` 
  SELECT `p`.`countryId`, `rk`.`eventId`, `rk`.`best`, 'a' AS `type`
    FROM `RanksAverage` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    WHERE `rk`.`countryRank`=1 GROUP BY `countryId`, `eventId`, `best`
  UNION
  SELECT `p`.`countryId`, `rk`.`eventId`, `rk`.`best`, 's' AS `type`
    FROM `RanksSingle` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    WHERE `rk`.`countryRank`=1 GROUP BY `countryId`, `eventId`, `best`;

ALTER TABLE `NR` ADD KEY `eventId` (`eventId`), ADD KEY `countryId` (`countryId`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksAverage` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `NR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='a'
SET `k`.`countryAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countryAll`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `NR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countryAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countryAll`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `NR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countryAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countryAll`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksSingle` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId`
  LEFT JOIN `NR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countryAll`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countryAll`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- -------------------------- Calculating Gender Scores according to Gender NR -----------------------------

-- Temporary table storing every NRs considering gender
DROP TABLE IF EXISTS `gNR`;
CREATE TEMPORARY TABLE IF NOT EXISTS `gNR`
  SELECT `p`.`countryId`, `rk`.`eventId`, `rk`.`best`, `rk`.`type`, `rk`.`gender`
    FROM `RanksGender` AS `rk`
    LEFT JOIN `Persons` AS `p` ON `rk`.`personId`=`p`.`id` AND `p`.`subid`=1
    WHERE `rk`.`countryRank`=1
    GROUP BY `p`.`countryId`,`rk`.`eventId`,`rk`.`gender`,`rk`.`type`, `best`;
ALTER TABLE `gNR` ADD KEY `eventId` (`eventId`), ADD KEY `gender` (`gender`), ADD KEY `countryId` (`countryId`), ADD KEY `type` (`type`);

-- Averages, 333fm and 333bf scores here are temporary, might be replaced by single scores later
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='a'
  LEFT JOIN `gNR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='a'
SET `k`.`countrySame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countrySame`)
WHERE `k`.`eventId` IN ('333', '444', '555', '222', '333oh', '333ft', 'minx', 'pyram', 'sq1', 'clock', 'skewb', '666', '777', '333fm', '333bf') AND `rk`.`personId` IS NOT NULL;

-- Singles
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gNR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countrySame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countrySame`)
WHERE `k`.`eventId` IN ('444bf', '555bf') AND `rk`.`personId` IS NOT NULL;

-- Mbf
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gNR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countrySame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countrySame`)
WHERE `k`.`eventId` IN ('333mbf') AND `rk`.`personId` IS NOT NULL;

-- 333fm and 333bf, selecting better result between single and average
UPDATE `KinchScores` AS `k`
  LEFT JOIN `RanksGender` AS `rk` ON `k`.`personId`=`rk`.`personId` AND `k`.`eventId`=`rk`.`eventId` AND `rk`.`type`='s'
  LEFT JOIN `gNR` AS `r` ON `k`.`eventId`=`r`.`eventId` AND `k`.`gender`=`r`.`gender` AND `k`.`countryId`=`r`.`countryId` AND `r`.`type`='s'
SET `k`.`countrySame`=kinchScore(`rk`.`best`, `k`.`eventId`, `r`.`best`, `k`.`countrySame`)
WHERE `k`.`eventId` IN ('333fm', '333bf') AND `rk`.`personId` IS NOT NULL;
