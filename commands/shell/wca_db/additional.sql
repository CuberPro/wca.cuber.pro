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
