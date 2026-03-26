-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 25, 2026 at 01:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `urenregistratiesysteem`
--

-- --------------------------------------------------------

--
-- Table structure for table `klanten`
--

CREATE TABLE `klanten` (
  `klanten_ID` int(11) NOT NULL,
  `Voornaam` varchar(100) DEFAULT NULL,
  `Tussenvoegsel` varchar(50) DEFAULT NULL,
  `Achternaam` varchar(100) DEFAULT NULL,
  `bedrijfsnaam` varchar(200) DEFAULT NULL,
  `functie` varchar(100) DEFAULT NULL,
  `email` text NOT NULL,
  `PhoneNumber` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `klanten`
--

INSERT INTO `klanten` (`klanten_ID`, `Voornaam`, `Tussenvoegsel`, `Achternaam`, `bedrijfsnaam`, `functie`, `email`, `PhoneNumber`) VALUES
(101, 'Jan', 'de', 'Vries', 'bedrijf1', 'Project Manager', 'jan.devries@email.com', '0612345678'),
(102, 'Piet', '', 'Jansen', 'SuperCoolBedrijf', 'Developer', 'piet.jansen@email.com', '0612345679'),
(103, 'Klaas', '', 'Bakker', 'TechnoLogica BV', 'Designer', 'klaas.bakker@email.com', '0612345680'),
(104, 'Lisa', '', 'Smit', 'InnovatieCorp', 'Consultant', 'lisa.smit@email.com', '0612345681'),
(105, 'Emma', '', 'Visser', 'DesignHub', 'Accountmanager', 'emma.visser@email.com', '0612345682'),
(106, 'Noah', '', 'Meijer', 'CodeMasters', 'CEO', 'noah.meijer@email.com', '0612345683'),
(107, 'Daan', '', 'Mulder', 'WebWizards', 'CFO', 'daan.mulder@email.com', '0612345684'),
(108, 'Sophie', '', 'Bos', 'DataDynamics', 'Marketing Manager', 'sophie.bos@email.com', '0612345685'),
(109, 'Lucas', '', 'Vos', 'CloudCompany', 'HR Manager', 'lucas.vos@email.com', '0612345686'),
(110, 'Mila', '', 'Peters', 'SmartSolutions', 'Sales Representative', 'mila.peters@email.com', '0612345687'),
(111, 'Finn', '', 'Hendriks', 'bedrijf1', 'Project Manager', 'finn.hendriks@email.com', '0612345688'),
(112, 'Sara', '', 'Dekker', 'SuperCoolBedrijf', 'Developer', 'sara.dekker@email.com', '0612345689'),
(113, 'Levi', '', 'Brouwer', 'TechnoLogica BV', 'Designer', 'levi.brouwer@email.com', '0612345690'),
(114, 'Julia', '', 'Kuiper', 'InnovatieCorp', 'Consultant', 'julia.kuiper@email.com', '0612345691'),
(115, 'Sem', 'van', 'Dijk', 'DesignHub', 'Accountmanager', 'sem.vandijk@email.com', '0612345692'),
(116, 'Nina', '', 'Timmer', 'CodeMasters', 'CEO', 'nina.timmer@email.com', '0612345693'),
(117, 'Lars', '', 'Vermeer', 'WebWizards', 'CFO', 'lars.vermeer@email.com', '0612345694'),
(118, 'Eva', 'van', 'Leeuwen', 'DataDynamics', 'Marketing Manager', 'eva.vanleeuwen@email.com', '0612345695'),
(119, 'Mats', '', 'Prins', 'CloudCompany', 'HR Manager', 'mats.prins@email.com', '0612345696'),
(120, 'Tess', '', 'Blom', 'SmartSolutions', 'Sales Representative', 'tess.blom@email.com', '0612345697'),
(121, 'Bram', 'van der', 'Meer', 'bedrijf1', 'Project Manager', 'bram.meer@email.com', '0612345698'),
(122, 'Zoe', '', 'Sanders', 'SuperCoolBedrijf', 'Developer', 'zoe.sanders@email.com', '0612345699'),
(123, 'Tim', '', 'Koster', 'TechnoLogica BV', 'Designer', 'tim.koster@email.com', '0612345700'),
(124, 'Amber', '', 'Hoekstra', 'InnovatieCorp', 'Consultant', 'amber.hoekstra@email.com', '0612345701'),
(125, 'Nick', 'van', 'Rijn', 'DesignHub', 'Accountmanager', 'nick.vanrijn@email.com', '0612345702'),
(126, 'Iris', '', 'Post', 'CodeMasters', 'CEO', 'iris.post@email.com', '0612345703'),
(127, 'Ruben', '', 'Willems', 'WebWizards', 'CFO', 'ruben.willems@email.com', '0612345704'),
(128, 'Sanne', '', 'Kok', 'DataDynamics', 'Marketing Manager', 'sanne.kok@email.com', '0612345705'),
(129, 'Thomas', '', 'Vink', 'CloudCompany', 'HR Manager', 'thomas.vink@email.com', '0612345706'),
(130, 'Fleur', 'van', 'Dam', 'SmartSolutions', 'Sales Representative', 'fleur.vandam@email.com', '0612345707'),
(131, 'Gijs', '', 'Scholten', 'bedrijf1', 'Project Manager', 'gijs.scholten@email.com', '0612345708'),
(132, 'Lotte', 'van den', 'Berg', 'SuperCoolBedrijf', 'Developer', 'lotte.vandenberg@email.com', '0612345709'),
(133, 'Koen', '', 'Dijkstra', 'TechnoLogica BV', 'Designer', 'koen.dijkstra@email.com', '0612345710'),
(134, 'Anouk', '', 'Smits', 'InnovatieCorp', 'Consultant', 'anouk.smits@email.com', '0612345711'),
(135, 'Rick', '', 'Evers', 'DesignHub', 'Accountmanager', 'rick.evers@email.com', '0612345712'),
(136, 'Femke', 'van', 'Loon', 'CodeMasters', 'CEO', 'femke.vanloon@email.com', '0612345713'),
(137, 'Wouter', '', 'Martens', 'WebWizards', 'CFO', 'wouter.martens@email.com', '0612345714'),
(138, 'Ilse', '', 'Gerrits', 'DataDynamics', 'Marketing Manager', 'ilse.gerrits@email.com', '0612345715'),
(139, 'Jelle', '', 'Verhoeven', 'CloudCompany', 'HR Manager', 'jelle.verhoeven@email.com', '0612345716'),
(140, 'Maud', 'van', 'Beek', 'SmartSolutions', 'Sales Representative', 'maud.vanbeek@email.com', '0612345717'),
(141, 'Bas', 'van', 'Kessel', 'bedrijf1', 'Project Manager', 'bas.vankessel@email.com', '0612345718'),
(142, 'Naomi', '', 'Rutgers', 'SuperCoolBedrijf', 'Developer', 'naomi.rutgers@email.com', '0612345719'),
(143, 'Roy', '', 'Schouten', 'TechnoLogica BV', 'Designer', 'roy.schouten@email.com', '0612345720'),
(144, 'Elin', 'van', 'Doorn', 'InnovatieCorp', 'Consultant', 'elin.vandoorn@email.com', '0612345721'),
(145, 'Stefan', '', 'Mol', 'DesignHub', 'Accountmanager', 'stefan.mol@email.com', '0612345722'),
(146, 'Mirthe', 'van', 'Ginkel', 'CodeMasters', 'CEO', 'mirthe.vanginkel@email.com', '0612345723'),
(147, 'Kevin', '', 'Jacobs', 'WebWizards', 'CFO', 'kevin.jacobs@email.com', '0612345724'),
(148, 'Danique', '', 'Wolters', 'DataDynamics', 'Marketing Manager', 'danique.wolters@email.com', '0612345725'),
(149, 'Patrick', 'de', 'Boer', 'CloudCompany', 'HR Manager', 'patrick.deboer@email.com', '0612345726'),
(150, 'Laura', 'van', 'Dalen', 'SmartSolutions', 'Sales Representative', 'laura.vandalen@email.com', '0612345727');

-- --------------------------------------------------------

--
-- Table structure for table `medewerkers`
--

CREATE TABLE `medewerkers` (
  `medewerker_ID` int(11) NOT NULL,
  `voornaam` varchar(50) NOT NULL,
  `tussenvoegsel` varchar(25) NOT NULL,
  `achternaam` varchar(50) NOT NULL,
  `geboortedatum` date NOT NULL,
  `functie` varchar(50) NOT NULL,
  `werkmail` varchar(100) NOT NULL,
  `kantoorruimte` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medewerkers`
--

INSERT INTO `medewerkers` (`medewerker_ID`, `voornaam`, `tussenvoegsel`, `achternaam`, `geboortedatum`, `functie`, `werkmail`, `kantoorruimte`) VALUES
(1, 'Arjen', '', 'Kramer', '1985-01-12', 'Developer', 'arjen.kramer@bedrijf.nl', 'A1'),
(2, 'Dennis', '', 'Groen', '1990-03-22', 'Designer', 'dennis.groen@bedrijf.nl', 'A2'),
(3, 'Mark', '', 'Kuipers', '1987-07-15', 'Project Manager', 'mark.kuipers@bedrijf.nl', 'A3'),
(4, 'Niels', '', 'Schipper', '1992-09-18', 'Consultant', 'niels.schipper@bedrijf.nl', 'A4'),
(5, 'Tom', '', 'Veldman', '1994-11-30', 'HR Manager', 'tom.veldman@bedrijf.nl', 'A5'),
(6, 'Jeroen', '', 'Baars', '1983-05-14', 'CEO', 'jeroen.baars@bedrijf.nl', 'B1'),
(7, 'Sven', '', 'Wessels', '1988-08-09', 'CFO', 'sven.wessels@bedrijf.nl', 'B2'),
(8, 'Thijs', '', 'Boersma', '1991-12-01', 'Marketing Manager', 'thijs.boersma@bedrijf.nl', 'B3'),
(9, 'Robin', '', 'Hofman', '1989-04-21', 'HR Manager', 'robin.hofman@bedrijf.nl', 'B4'),
(10, 'Dylan', '', 'Koning', '1996-02-11', 'Sales Representative', 'dylan.koning@bedrijf.nl', 'B5'),
(11, 'Bjorn', '', 'Nieuwenhuis', '1993-06-18', 'Developer', 'bjorn.nieuwenhuis@bedrijf.nl', 'C1'),
(12, 'Martijn', '', 'Zuidema', '1986-10-27', 'Developer', 'martijn.zuidema@bedrijf.nl', 'C2'),
(13, 'Erik', '', 'Otter', '1988-03-03', 'Designer', 'erik.otter@bedrijf.nl', 'C3'),
(14, 'Hugo', '', 'Rietveld', '1992-07-19', 'Consultant', 'hugo.rietveld@bedrijf.nl', 'C4'),
(15, 'Stijn', 'van', 'Hees', '1988-02-25', 'Accountmanager', 'stijn.vanhees@bedrijf.nl', 'C5'),
(16, 'Koos', '', 'Verschuren', '1991-11-13', 'CEO', 'koos.verschuren@bedrijf.nl', 'D1'),
(17, 'Bart', '', 'Looman', '1984-06-06', 'CFO', 'bart.looman@bedrijf.nl', 'D2'),
(18, 'Joris', 'van', 'Etten', '1990-09-29', 'Marketing Manager', 'joris.vanetten@bedrijf.nl', 'D3'),
(19, 'Wim', '', 'Schellekens', '1987-12-15', 'HR Manager', 'wim.schellekens@bedrijf.nl', 'D4'),
(20, 'Kees', '', 'Brands', '1995-04-04', 'Sales Representative', 'kees.brands@bedrijf.nl', 'D5'),
(21, 'Henk', 'van der', 'Pol', '1986-08-08', 'Project Manager', 'henk.pol@bedrijf.nl', 'E1'),
(22, 'Ralph', '', 'Kleijn', '1993-01-21', 'Developer', 'ralph.kleijn@bedrijf.nl', 'E2'),
(23, 'Joost', '', 'Terlouw', '1989-05-30', 'Designer', 'joost.terlouw@bedrijf.nl', 'E3'),
(24, 'Sander', '', 'Verbeek', '1994-07-12', 'Consultant', 'sander.verbeek@bedrijf.nl', 'E4'),
(25, 'Maurice', 'van', 'Aalst', '1988-03-27', 'Accountmanager', 'maurice.vanaalst@bedrijf.nl', 'E5'),
(26, 'Frank', '', 'Kool', '1992-09-09', 'CEO', 'frank.kool@bedrijf.nl', 'F1'),
(27, 'Leo', '', 'Hoogland', '1985-11-23', 'CFO', 'leo.hoogland@bedrijf.nl', 'F2'),
(28, 'Basjan', '', 'Vernooy', '1991-06-16', 'Marketing Manager', 'basjan.vernooy@bedrijf.nl', 'F3'),
(29, 'Rik', '', 'Tempelman', '1987-02-02', 'HR Manager', 'rik.tempelman@bedrijf.nl', 'F4'),
(30, 'Timo', 'van', 'Houten', '1996-12-12', 'Sales Representative', 'timo.vanhouten@bedrijf.nl', 'F5'),
(31, 'Gerben', '', 'Slagter', '1983-04-18', 'Project Manager', 'gerben.slagter@bedrijf.nl', 'G1'),
(32, 'Pim', '', 'Doedens', '1990-10-10', 'Developer', 'pim.doedens@bedrijf.nl', 'G2'),
(33, 'Dirk', '', 'Kruizinga', '1989-01-05', 'Designer', 'dirk.kruizinga@bedrijf.nl', 'G3'),
(34, 'Arno', '', 'Witkamp', '1993-03-22', 'Consultant', 'arno.witkamp@bedrijf.nl', 'G4'),
(35, 'Koert', '', 'Banning', '1988-07-07', 'Accountmanager', 'koert.banning@bedrijf.nl', 'G5'),
(36, 'Frits', 'van', 'Beers', '1991-05-25', 'CEO', 'frits.vanbeers@bedrijf.nl', 'H1'),
(37, 'Karel', '', 'Steenhuis', '1984-09-14', 'CFO', 'karel.steenhuis@bedrijf.nl', 'H2'),
(38, 'Nico', '', 'Kamps', '1992-11-30', 'Marketing Manager', 'nico.kamps@bedrijf.nl', 'H3'),
(39, 'Otto', '', 'Meursing', '1987-08-19', 'HR Manager', 'otto.meursing@bedrijf.nl', 'H4'),
(40, 'Ruud', 'van', 'Zanten', '1995-02-08', 'Sales Representative', 'ruud.vanzanten@bedrijf.nl', 'H5'),
(41, 'Eddy', 'van', 'Vliet', '1986-06-11', 'Project Manager', 'eddy.vanvliet@bedrijf.nl', 'I1'),
(42, 'Harm', '', 'Talsma', '1994-12-03', 'Developer', 'harm.talsma@bedrijf.nl', 'I2'),
(43, 'Geert', '', 'Bosma', '1989-04-26', 'Designer', 'geert.bosma@bedrijf.nl', 'I3'),
(44, 'Janus', 'van', 'Raalte', '1993-09-17', 'Consultant', 'janus.vanraalte@bedrijf.nl', 'I4'),
(45, 'Wessel', '', 'Koopman', '1988-01-29', 'Accountmanager', 'wessel.koopman@bedrijf.nl', 'I5'),
(46, 'Teun', 'van', 'Gorp', '1992-07-07', 'CEO', 'teun.vangorp@bedrijf.nl', 'J1'),
(47, 'Casper', '', 'Helder', '1985-10-20', 'CFO', 'casper.helder@bedrijf.nl', 'J2'),
(48, 'Boudewijn', '', 'Kieft', '1991-03-14', 'Marketing Manager', 'boudewijn.kieft@bedrijf.nl', 'J3'),
(49, 'Sjoerd', '', 'Ravensberg', '1987-06-06', 'HR Manager', 'sjoerd.ravensberg@bedrijf.nl', 'J4'),
(50, 'Laurens', 'van', 'Rooij', '1995-11-11', 'Sales Representative', 'laurens.vanrooij@bedrijf.nl', 'J5');

-- --------------------------------------------------------

--
-- Table structure for table `opdrachten`
--

CREATE TABLE `opdrachten` (
  `Opdrachten_ID` int(11) NOT NULL,
  `Klantnaam` varchar(50) NOT NULL,
  `Titel` varchar(50) NOT NULL,
  `Omschrijving` text NOT NULL,
  `Aanvraagdatum` date NOT NULL,
  `Benodigde_kennis` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opdrachten`
--

INSERT INTO `opdrachten` (`Opdrachten_ID`, `Klantnaam`, `Titel`, `Omschrijving`, `Aanvraagdatum`, `Benodigde_kennis`) VALUES
(51, 'bedrijf1', 'Website redesign', 'Complete redesign van bedrijfswebsite', '2025-01-10', 'HTML, CSS, UX'),
(52, 'SuperCoolBedrijf', 'API ontwikkeling', 'REST API bouwen voor mobiele app', '2025-01-12', 'PHP, Laravel'),
(53, 'TechnoLogica BV', 'Logo design', 'Nieuw logo ontwerpen', '2025-01-15', 'Photoshop, Illustrator'),
(54, 'InnovatieCorp', 'Consultancy IT', 'Advies over IT infrastructuur', '2025-01-18', 'ITIL, Netwerken'),
(55, 'DesignHub', 'Marketing campagne', 'Online campagne opzetten', '2025-01-20', 'SEO, SEA'),
(56, 'CodeMasters', 'SaaS platform', 'Nieuw platform ontwikkelen', '2025-01-22', 'Node.js, React'),
(57, 'WebWizards', 'Financieel systeem', 'Boekhoudsoftware bouwen', '2025-01-25', 'Java, Spring'),
(58, 'DataDynamics', 'Data analyse', 'Dashboard bouwen', '2025-01-28', 'Python, PowerBI'),
(59, 'CloudCompany', 'Cloud migratie', 'Migratie naar AWS', '2025-02-01', 'AWS, DevOps'),
(60, 'SmartSolutions', 'CRM systeem', 'CRM implementeren', '2025-02-03', 'Salesforce'),
(61, 'bedrijf1', 'Project planning tool', 'Tool voor projectbeheer', '2025-02-05', 'Angular, Firebase'),
(62, 'SuperCoolBedrijf', 'Bug fixing', 'Bugs oplossen in bestaande app', '2025-02-07', 'JavaScript'),
(63, 'TechnoLogica BV', 'UX audit', 'Gebruiksvriendelijkheid verbeteren', '2025-02-09', 'UX research'),
(64, 'InnovatieCorp', 'Security audit', 'Beveiliging testen', '2025-02-11', 'Cybersecurity'),
(65, 'DesignHub', 'Branding', 'Nieuwe huisstijl ontwikkelen', '2025-02-13', 'Design'),
(66, 'CodeMasters', 'AI integratie', 'AI toevoegen aan app', '2025-02-15', 'Python, AI'),
(67, 'WebWizards', 'ERP systeem', 'ERP implementeren', '2025-02-17', 'SAP'),
(68, 'DataDynamics', 'Big data project', 'Data pipeline bouwen', '2025-02-19', 'Hadoop'),
(69, 'CloudCompany', 'Server setup', 'Servers configureren', '2025-02-21', 'Linux'),
(70, 'SmartSolutions', 'Sales dashboard', 'Dashboard maken', '2025-02-23', 'Tableau'),
(71, 'bedrijf1', 'Mobiele app', 'iOS app bouwen', '2025-02-25', 'Swift'),
(72, 'SuperCoolBedrijf', 'Android app', 'Android app ontwikkelen', '2025-02-27', 'Kotlin'),
(73, 'TechnoLogica BV', 'Animatie design', 'Animaties maken', '2025-03-01', 'After Effects'),
(74, 'InnovatieCorp', 'Proces optimalisatie', 'Bedrijfsprocessen verbeteren', '2025-03-03', 'Lean'),
(75, 'DesignHub', 'Landing page', 'Nieuwe landingspagina', '2025-03-05', 'HTML, CSS'),
(76, 'CodeMasters', 'Microservices', 'Architectuur bouwen', '2025-03-07', 'Docker'),
(77, 'WebWizards', 'Backend upgrade', 'Upgrade backend systeem', '2025-03-09', 'PHP'),
(78, 'DataDynamics', 'ML model', 'Machine learning model bouwen', '2025-03-11', 'Python'),
(79, 'CloudCompany', 'CI/CD pipeline', 'Pipeline opzetten', '2025-03-13', 'Jenkins'),
(80, 'SmartSolutions', 'Klantenportaal', 'Portaal ontwikkelen', '2025-03-15', 'Vue.js'),
(81, 'bedrijf1', 'Intranet', 'Intern platform bouwen', '2025-03-17', 'SharePoint'),
(82, 'SuperCoolBedrijf', 'Chat applicatie', 'Realtime chat bouwen', '2025-03-19', 'Socket.io'),
(83, 'TechnoLogica BV', '3D design', '3D modellen maken', '2025-03-21', 'Blender'),
(84, 'InnovatieCorp', 'Risico analyse', 'Risico’s in kaart brengen', '2025-03-23', 'Analyse'),
(85, 'DesignHub', 'Social media', 'Social media strategie', '2025-03-25', 'Marketing'),
(86, 'CodeMasters', 'Blockchain app', 'Blockchain implementatie', '2025-03-27', 'Solidity'),
(87, 'WebWizards', 'Webshop', 'E-commerce bouwen', '2025-03-29', 'Magento'),
(88, 'DataDynamics', 'ETL proces', 'ETL pipeline maken', '2025-03-31', 'SQL'),
(89, 'CloudCompany', 'Kubernetes cluster', 'Cluster opzetten', '2025-04-02', 'Kubernetes'),
(90, 'SmartSolutions', 'Support systeem', 'Ticket systeem bouwen', '2025-04-04', 'Zendesk'),
(91, 'bedrijf1', 'HR systeem', 'HR software ontwikkelen', '2025-04-06', 'HRM'),
(92, 'SuperCoolBedrijf', 'Test automatisering', 'Tests automatiseren', '2025-04-08', 'Selenium'),
(93, 'TechnoLogica BV', 'Illustraties', 'Custom illustraties maken', '2025-04-10', 'Illustrator'),
(94, 'InnovatieCorp', 'Training IT', 'Training geven', '2025-04-12', 'Coaching'),
(95, 'DesignHub', 'Portfolio site', 'Portfolio website bouwen', '2025-04-14', 'HTML'),
(96, 'CodeMasters', 'Refactoring', 'Code verbeteren', '2025-04-16', 'Clean code'),
(97, 'WebWizards', 'Hosting setup', 'Hosting configureren', '2025-04-18', 'cPanel'),
(98, 'DataDynamics', 'Reporting tool', 'Rapportage tool bouwen', '2025-04-20', 'PowerBI'),
(99, 'CloudCompany', 'Backup systeem', 'Backups automatiseren', '2025-04-22', 'Azure'),
(100, 'SmartSolutions', 'E-mail systeem', 'Mailserver opzetten', '2025-04-24', 'SMTP');

-- --------------------------------------------------------

--
-- Table structure for table `werkzaamheden`
--

CREATE TABLE `werkzaamheden` (
  `werkzaamheden_ID` int(11) NOT NULL,
  `voornaam` varchar(50) NOT NULL,
  `tussenvoegsel` varchar(25) NOT NULL,
  `achternaam` varchar(50) NOT NULL,
  `gewerkte_uren` int(11) NOT NULL,
  `opdracht_titel` varchar(100) NOT NULL,
  `omschrijving_werkzaamheden` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `werkzaamheden`
--

INSERT INTO `werkzaamheden` (`werkzaamheden_ID`, `voornaam`, `tussenvoegsel`, `achternaam`, `gewerkte_uren`, `opdracht_titel`, `omschrijving_werkzaamheden`) VALUES
(1, 'Arjen', '', 'Kramer', 8, 'Website redesign', 'Frontend ontwikkeling en styling'),
(2, 'Dennis', '', 'Groen', 6, 'API ontwikkeling', 'API endpoints bouwen'),
(3, 'Mark', '', 'Kuipers', 7, 'Logo design', 'Concepten uitwerken'),
(4, 'Niels', '', 'Schipper', 5, 'Consultancy IT', 'Analyse infrastructuur'),
(5, 'Tom', '', 'Veldman', 4, 'Marketing campagne', 'Campagne planning'),
(6, 'Jeroen', '', 'Baars', 9, 'SaaS platform', 'Backend ontwikkeling'),
(7, 'Sven', '', 'Wessels', 8, 'Financieel systeem', 'Database ontwerp'),
(8, 'Thijs', '', 'Boersma', 6, 'Data analyse', 'Dashboard maken'),
(9, 'Robin', '', 'Hofman', 7, 'Cloud migratie', 'Migratie uitvoeren'),
(10, 'Dylan', '', 'Koning', 5, 'CRM systeem', 'Configuratie CRM'),
(11, 'Bjorn', '', 'Nieuwenhuis', 8, 'Project planning tool', 'Frontend bouwen'),
(12, 'Martijn', '', 'Zuidema', 6, 'Bug fixing', 'Fouten oplossen'),
(13, 'Erik', '', 'Otter', 7, 'UX audit', 'UX verbeteringen voorstellen'),
(14, 'Hugo', '', 'Rietveld', 5, 'Security audit', 'Security scan uitvoeren'),
(15, 'Stijn', 'van', 'Hees', 4, 'Branding', 'Huisstijl ontwerpen'),
(16, 'Koos', '', 'Verschuren', 9, 'AI integratie', 'Machine learning model trainen'),
(17, 'Bart', '', 'Looman', 8, 'ERP systeem', 'Implementatie ERP'),
(18, 'Joris', 'van', 'Etten', 6, 'Big data project', 'Data pipeline bouwen'),
(19, 'Wim', '', 'Schellekens', 7, 'Server setup', 'Servers configureren'),
(20, 'Kees', '', 'Brands', 5, 'Sales dashboard', 'Dashboard ontwikkelen'),
(21, 'Henk', 'van der', 'Pol', 8, 'Mobiele app', 'iOS app bouwen'),
(22, 'Ralph', '', 'Kleijn', 6, 'Android app', 'Android features ontwikkelen'),
(23, 'Joost', '', 'Terlouw', 7, 'Animatie design', 'Animaties maken'),
(24, 'Sander', '', 'Verbeek', 5, 'Proces optimalisatie', 'Proces analyse'),
(25, 'Maurice', 'van', 'Aalst', 4, 'Landing page', 'Pagina ontwerpen'),
(26, 'Frank', '', 'Kool', 9, 'Microservices', 'Services opzetten'),
(27, 'Leo', '', 'Hoogland', 8, 'Backend upgrade', 'Backend refactoren'),
(28, 'Basjan', '', 'Vernooy', 6, 'ML model', 'Model trainen'),
(29, 'Rik', '', 'Tempelman', 7, 'CI/CD pipeline', 'Pipeline bouwen'),
(30, 'Timo', 'van', 'Houten', 5, 'Klantenportaal', 'Portaal ontwikkelen'),
(31, 'Gerben', '', 'Slagter', 8, 'Intranet', 'Intern platform bouwen'),
(32, 'Pim', '', 'Doedens', 6, 'Chat applicatie', 'Realtime chat bouwen'),
(33, 'Dirk', '', 'Kruizinga', 7, '3D design', '3D modellen maken'),
(34, 'Arno', '', 'Witkamp', 5, 'Risico analyse', 'Risico analyse uitvoeren'),
(35, 'Koert', '', 'Banning', 4, 'Social media', 'Posts plannen'),
(36, 'Frits', 'van', 'Beers', 9, 'Blockchain app', 'Smart contracts schrijven'),
(37, 'Karel', '', 'Steenhuis', 8, 'Webshop', 'E-commerce bouwen'),
(38, 'Nico', '', 'Kamps', 6, 'ETL proces', 'Data verwerken'),
(39, 'Otto', '', 'Meursing', 7, 'Kubernetes cluster', 'Cluster opzetten'),
(40, 'Ruud', 'van', 'Zanten', 5, 'Support systeem', 'Ticketsysteem bouwen'),
(41, 'Eddy', 'van', 'Vliet', 8, 'HR systeem', 'HR software bouwen'),
(42, 'Harm', '', 'Talsma', 6, 'Test automatisering', 'Tests schrijven'),
(43, 'Geert', '', 'Bosma', 7, 'Illustraties', 'Illustraties maken'),
(44, 'Janus', 'van', 'Raalte', 5, 'Training IT', 'Training geven'),
(45, 'Wessel', '', 'Koopman', 4, 'Portfolio site', 'Website bouwen'),
(46, 'Teun', 'van', 'Gorp', 9, 'Refactoring', 'Code verbeteren'),
(47, 'Casper', '', 'Helder', 8, 'Hosting setup', 'Hosting configureren'),
(48, 'Boudewijn', '', 'Kieft', 6, 'Reporting tool', 'Rapportages maken'),
(49, 'Sjoerd', '', 'Ravensberg', 7, 'Backup systeem', 'Backups instellen'),
(50, 'Laurens', 'van', 'Rooij', 5, 'E-mail systeem', 'Mailserver configureren');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klanten`
--
ALTER TABLE `klanten`
  ADD PRIMARY KEY (`klanten_ID`);

--
-- Indexes for table `medewerkers`
--
ALTER TABLE `medewerkers`
  ADD PRIMARY KEY (`medewerker_ID`);

--
-- Indexes for table `opdrachten`
--
ALTER TABLE `opdrachten`
  ADD PRIMARY KEY (`Opdrachten_ID`);

--
-- Indexes for table `werkzaamheden`
--
ALTER TABLE `werkzaamheden`
  ADD PRIMARY KEY (`werkzaamheden_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `klanten`
--
ALTER TABLE `klanten`
  MODIFY `klanten_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `medewerkers`
--
ALTER TABLE `medewerkers`
  MODIFY `medewerker_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `opdrachten`
--
ALTER TABLE `opdrachten`
  MODIFY `Opdrachten_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `werkzaamheden`
--
ALTER TABLE `werkzaamheden`
  MODIFY `werkzaamheden_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
