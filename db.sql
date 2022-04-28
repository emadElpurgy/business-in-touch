/*
SQLyog Enterprise - MySQL GUI v7.02 
MySQL - 5.1.50-community : Database - market
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`market` /*!40100 DEFAULT CHARACTER SET cp1256 */;

USE `market`;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_code` varchar(10) DEFAULT NULL,
  `description` text,
  `type` int(11) NOT NULL DEFAULT '0',
  `parent_category_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `FK_categories_parent_category` (`parent_category_id`),
  CONSTRAINT `FK_categories_parent_category` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `categories` */

insert  into `categories`(`category_id`,`category_name`,`category_code`,`description`,`type`,`parent_category_id`) values (0,'','','',0,0);

/*Table structure for table `companies` */

DROP TABLE IF EXISTS `companies`;

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `company_code` varchar(10) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(100) NOT NULL,
  `fax` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`company_id`),
  KEY `FK_companies_category` (`category_id`),
  CONSTRAINT `FK_companies_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `companies` */

insert  into `companies`(`company_id`,`company_name`,`company_code`,`type`,`category_id`,`phone`,`fax`,`mobile`,`email`,`address`,`balance`) values (0,'','',0,0,'','','','','',0);

/*Table structure for table `permit_products` */

DROP TABLE IF EXISTS `permit_products`;

CREATE TABLE `permit_products` (
  `permit_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `permit_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `expiry` date DEFAULT NULL,
  PRIMARY KEY (`permit_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `permit_products` */

/*Table structure for table `permit_types` */

DROP TABLE IF EXISTS `permit_types`;

CREATE TABLE `permit_types` (
  `permit_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `permit_type_name` varchar(255) NOT NULL,
  `descreption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`permit_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `permit_types` */

insert  into `permit_types`(`permit_type_id`,`permit_type_name`,`descreption`) values (0,'','');

/*Table structure for table `permits` */

DROP TABLE IF EXISTS `permits`;

CREATE TABLE `permits` (
  `permit_id` int(11) NOT NULL AUTO_INCREMENT,
  `permit_number` int(11) NOT NULL DEFAULT '0',
  `permit_type_id` int(11) NOT NULL DEFAULT '0',
  `permit_date` date NOT NULL,
  `total` double NOT NULL DEFAULT '0',
  `extra` double NOT NULL DEFAULT '0',
  `discount` double NOT NULL DEFAULT '0',
  `overall` double NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  `treasury_id` int(11) NOT NULL DEFAULT '0',
  `stock_id` int(11) NOT NULL DEFAULT '0',
  `parent_permit_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`permit_id`),
  KEY `FK_permits_company_id` (`company_id`),
  KEY `FK_permits_permit_type` (`parent_permit_id`),
  KEY `FK_permits_treasury` (`treasury_id`),
  KEY `FK_permits_stock` (`stock_id`),
  CONSTRAINT `FK_permits_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_permits_permit_type` FOREIGN KEY (`parent_permit_id`) REFERENCES `permit_types` (`permit_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_permits_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_permits_treasury` FOREIGN KEY (`treasury_id`) REFERENCES `treasuries` (`treasury_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `permits` */

insert  into `permits`(`permit_id`,`permit_number`,`permit_type_id`,`permit_date`,`total`,`extra`,`discount`,`overall`,`company_id`,`treasury_id`,`stock_id`,`parent_permit_id`) values (0,0,0,'0000-00-00',0,0,0,0,0,0,0,0);

/*Table structure for table `product_units` */

DROP TABLE IF EXISTS `product_units`;

CREATE TABLE `product_units` (
  `product_unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `parent_unit_id` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `purchase_price` double NOT NULL DEFAULT '0',
  `sell_price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_unit_id`),
  KEY `FK_product_units_product` (`product_id`),
  KEY `FK_product_units_unit` (`unit_id`),
  CONSTRAINT `FK_product_units_parent_unit` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`product_unit_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_product_units_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_product_units_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `product_units` */

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `description` text,
  PRIMARY KEY (`product_id`),
  KEY `FK_products_product_category` (`category_id`),
  CONSTRAINT `FK_products_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `products` */

insert  into `products`(`product_id`,`product_name`,`product_code`,`image`,`category_id`,`description`) values (0,'','','',0,'');

/*Table structure for table `sections` */

DROP TABLE IF EXISTS `sections`;

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(255) NOT NULL,
  `section_url` varchar(255) NOT NULL,
  `main_section_id` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  KEY `FK_sections_main_section` (`main_section_id`),
  CONSTRAINT `FK_sections_main_section` FOREIGN KEY (`main_section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=cp1256;

/*Data for the table `sections` */

insert  into `sections`(`section_id`,`section_name`,`section_url`,`main_section_id`,`icon`) values (0,'0','',0,NULL),(1,'البيانات الاساسية','',0,'img/sections/basicdata.png'),(2,'العمليات اليومية','',0,'img/sections/dailyactions.png'),(3,'مستخدمي البرنامج','users',1,'img/sections/users.png'),(4,'العملاء','',1,NULL),(5,'فئات العملاء','categories.php?type=1',4,'img/sections/customer_category.png'),(6,'بيانات العملاء','companies.php?type=1',4,'img/sections/customers.png'),(7,'الموردين','',1,NULL),(8,'فئات الموردين','categories.php?type=2',7,'img/sections/supplier_category.png'),(9,'بيانات الموردين','companies.php?type=2',7,'img/sections/suppliers.png'),(10,'الاصناف','',1,NULL),(11,'فئات الاصناف','categories.php?type=3',10,'img/sections/product_category.png'),(12,'الوحدات','units.php',10,'img/sections/units.png'),(13,'بيانات الاصناف','products.php',10,'img/sections/products.png'),(14,'المالية','',1,NULL),(15,'فئات المصروفات','categories.php?type=4',14,'img/sections/payment.png'),(16,'فئات الايرادات','categories.php?type=5',14,'img/sections/income.png'),(17,'المشتريات','',2,NULL),(18,'التوريدات','permits.php?type=1',17,'img/sections/in.png'),(19,'المرتجعات','permits.php?type=2',17,'img/sections/out.png'),(20,'المبيعات','',2,NULL),(21,'المبيعات','permits.php?type=3',20,'img/sections/sale.png'),(22,'المرتجعات','permits.php?type=4',20,'img/sections/salerollback.png'),(23,'الخزينة','',2,NULL),(24,'المصروفات','permits.php?type=5',23,'img/sections/cash_in.png'),(25,'الايرادات','permits.php?type=6',23,'img/sections/cash_out.png'),(26,'المطبوعات','',0,'img/sections/reports.png');

/*Table structure for table `stocks` */

DROP TABLE IF EXISTS `stocks`;

CREATE TABLE `stocks` (
  `stock_id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_name` varchar(255) NOT NULL,
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `stocks` */

insert  into `stocks`(`stock_id`,`stock_name`) values (0,'');

/*Table structure for table `treasuries` */

DROP TABLE IF EXISTS `treasuries`;

CREATE TABLE `treasuries` (
  `treasury_id` int(11) NOT NULL AUTO_INCREMENT,
  `treasury_name` varchar(255) NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`treasury_id`),
  KEY `FK_treasuries_user_id` (`user_id`),
  CONSTRAINT `FK_treasuries_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `treasuries` */

insert  into `treasuries`(`treasury_id`,`treasury_name`,`balance`,`user_id`) values (0,'',0,0);

/*Table structure for table `units` */

DROP TABLE IF EXISTS `units`;

CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) NOT NULL,
  `unit_code` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `units` */

insert  into `units`(`unit_id`,`unit_name`,`unit_code`) values (0,'','');

/*Table structure for table `units_in_stocks` */

DROP TABLE IF EXISTS `units_in_stocks`;

CREATE TABLE `units_in_stocks` (
  `stock_unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `stock_id` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `expiry` date DEFAULT NULL,
  PRIMARY KEY (`stock_unit_id`),
  KEY `FK_units_in_stocks_stock` (`stock_id`),
  KEY `FK_units_in_stocks_product` (`product_id`),
  KEY `FK_units_in_stocks_unit` (`unit_id`),
  CONSTRAINT `FK_units_in_stocks_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_units_in_stocks_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_units_in_stocks_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1256;

/*Data for the table `units_in_stocks` */

insert  into `units_in_stocks`(`stock_unit_id`,`product_id`,`unit_id`,`stock_id`,`amount`,`expiry`) values (0,0,0,0,0,'0000-00-00');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=cp1256;

/*Data for the table `users` */

insert  into `users`(`user_id`,`user_name`,`password`,`role`) values (0,'','',0),(1,'admin','',0),(2,'user1','123456',0),(3,'user2','852369741',0),(6,'user5','123456789',0),(7,'6','',0),(8,'7','',1),(9,'8','',1),(10,'9','',0),(11,'10','',1),(12,'11','',1),(14,'12','',0),(15,'13','',1),(16,'14','',1),(17,'15','',0),(18,'16','',1),(19,'17','',1),(20,'18','',0),(21,'19','',1),(22,'20','',1),(23,'21','',0),(24,'22','',1),(25,'23','',1),(29,'24','',0),(30,'25','',1),(31,'26','',1),(32,'27','',0),(33,'28','',1),(34,'29','',1),(35,'30','',0),(36,'31','',1),(37,'32','',1),(38,'33','',0),(39,'34','',1),(40,'35','',1),(41,'36','',0),(42,'37','',1),(43,'38','',1),(44,'39','',0),(45,'40','',1),(46,'41','',1),(47,'42','',0),(48,'43','',1),(49,'44','',1),(50,'45','',0),(51,'46','',1),(52,'47','',1),(60,'48','',0),(61,'49','',1),(62,'50','',1),(63,'51','',0),(64,'52','',1),(65,'53','',1),(66,'54','',0),(67,'55','',1),(68,'56','',1),(69,'57','',0),(70,'58','',1),(71,'59','',1),(72,'60','',0),(73,'61','',1),(74,'62','',1),(75,'63','',0),(76,'64','',1),(77,'65','',1),(78,'66','',0),(79,'67','',1),(80,'68','',1),(81,'69','',0),(82,'70','',1),(83,'71','',1),(84,'72','',0),(85,'73','',1),(86,'74','',1),(87,'75','',0),(88,'76','',1),(89,'77','',1),(90,'78','',0),(91,'79','',1),(92,'80','',1),(93,'81','',0),(94,'82','',1),(95,'83','',1),(96,'84','',0),(97,'85','',1),(98,'86','',1),(99,'87','',0),(100,'88','',1),(101,'89','',1),(102,'90','',0),(103,'91','',1),(104,'92','',1),(105,'93','',0),(106,'94','',1),(107,'95','',1),(123,'new user','',1),(124,'new user 2','newpassword2',1),(125,'new user 5','new user 5',1),(127,'admin','123',0),(128,'admin22','asdasdasd',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
