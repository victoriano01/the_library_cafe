/*
SQLyog Ultimate v12.4.3 (64 bit)
MySQL - 8.0.31 : Database - gestion_biblio
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`gestion_biblio` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `gestion_biblio`;

/*Table structure for table `admin` */

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `mail` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mdp` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `admin` */

insert  into `admin`(`id_admin`,`mail`,`mdp`) values 
(3,'admin@gmail.com','12345678');

/*Table structure for table `auteur` */

DROP TABLE IF EXISTS `auteur`;

CREATE TABLE `auteur` (
  `id_auteur` int NOT NULL AUTO_INCREMENT,
  `nom_auteur` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id_auteur`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `auteur` */

insert  into `auteur`(`id_auteur`,`nom_auteur`) values 
(1,'Victor Hugo'),
(2,'J.K. Rowling'),
(3,'labert Einstein'),
(4,'Fernand Braudel'),
(5,'Bescherelle'),
(6,'moi'),
(7,'victor huge'),
(8,'molote'),
(9,'trif primade'),
(10,'steeve'),
(11,'zaka'),
(12,'author name'),
(13,' James Noggle'),
(14,'Manuel Delgado'),
(15,'Chase McGhee'),
(16,'Stephen Webb'),
(17,'Jeni Conrad'),
(18,'Brian Christian & Tom Griffiths'),
(19,'Ellen Lupton'),
(20,'L.K. Rowling'),
(21,'Cyril lignac'),
(22,'test');

/*Table structure for table `categorie` */

DROP TABLE IF EXISTS `categorie`;

CREATE TABLE `categorie` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `titre_categorie` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `categorie` */

insert  into `categorie`(`id_categorie`,`titre_categorie`) values 
(1,'fiction'),
(2,'Littérature '),
(3,'Livre pratique'),
(4,'Technique '),
(5,'Biographie / Mémoires'),
(6,'Documentaire / Historique'),
(7,'Beaux livres / Art'),
(8,'Religion / Spiritualité'),
(9,'Jeunesse / Adolescents'),
(10,'Bandes dessinées / Manga'),
(11,'Manuels scolaires / pédagogiques');

/*Table structure for table `emprunts` */

DROP TABLE IF EXISTS `emprunts`;

CREATE TABLE `emprunts` (
  `id_emprunt` int NOT NULL AUTO_INCREMENT,
  `id_livre` int DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  `date_emprunt` date DEFAULT NULL,
  `date_retour_prevue` date DEFAULT NULL,
  `date_retour_effective` date DEFAULT NULL,
  `statut` enum('en cours','rendu','annule') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'en cours',
  PRIMARY KEY (`id_emprunt`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `emprunts` */

insert  into `emprunts`(`id_emprunt`,`id_livre`,`id_utilisateur`,`date_emprunt`,`date_retour_prevue`,`date_retour_effective`,`statut`) values 
(31,29,8,'2025-07-30','2025-08-06',NULL,'en cours'),
(30,38,1,'2025-07-29','2025-07-30',NULL,'en cours');

/*Table structure for table `livre` */

DROP TABLE IF EXISTS `livre`;

CREATE TABLE `livre` (
  `id_livre` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_auteur` int DEFAULT NULL,
  `id_categorie` int DEFAULT NULL,
  `date_de_sortie` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `statut` enum('disponible','emprunté','réservé','retardé','supprimé','nouveau') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'disponible',
  `is_new` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_livre`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `livre` */

insert  into `livre`(`id_livre`,`titre`,`id_auteur`,`id_categorie`,`date_de_sortie`,`photo`,`statut`,`is_new`) values 
(1,'the silver crow',9,1,'2017-02-14','livre.jpg','disponible',0),
(2,'Unfelt',13,2,'2023-01-01','Unfelt.jpg','disponible',0),
(5,'Visions of Tomorrow',16,1,'2023-09-10','Visions of Tomorrow.jpg','disponible',0),
(3,' Voice of the Ancestors',15,3,'2019-03-20','Voice of the Ancestors.jpg','disponible',0),
(6,'Alice in Neverland',17,1,'2023-09-10','Alice in Neverland.jpg','disponible',0),
(8,'Design Is Storytelling',19,4,'2017-08-28','Design Is Storytelling.jpg','disponible',0),
(9,'HARRY POTTER',20,1,'2021-03-07','livre (18).jpg','disponible',0),
(10,'Fait Maison',21,3,'2023-11-21','fait maison.jpg','disponible',0),
(27,'the silver crow',8,1,'2022-06-21','livre.jpg','disponible',0),
(29,' Voice of the Ancestors',9,7,'2025-07-30','L’espace public comme idéologie.jpg','',0);

/*Table structure for table `utilisateur` */

DROP TABLE IF EXISTS `utilisateur`;

CREATE TABLE `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) DEFAULT NULL,
  `prenom` varchar(60) DEFAULT NULL,
  `mail` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mdp` varchar(60) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `utilisateur` */

insert  into `utilisateur`(`id_utilisateur`,`nom`,`prenom`,`mail`,`mdp`,`photo`) values 
(6,'utilisateur','maka','utilisateur2@gmail.com','12345678',NULL),
(8,'victoriano','maka','zzz@gmail.com','1234','profil_688a4f95e4d176.36213460.png');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
