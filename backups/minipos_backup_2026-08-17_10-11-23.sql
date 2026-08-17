-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: minipos
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounting_categories`
--

DROP TABLE IF EXISTS `accounting_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `record_type` varchar(20) NOT NULL DEFAULT 'expense',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_categories`
--

LOCK TABLES `accounting_categories` WRITE;
/*!40000 ALTER TABLE `accounting_categories` DISABLE KEYS */;
INSERT INTO `accounting_categories` VALUES (8,'ຄ່າໄຟຟ້າ','expense','2026-08-17 08:47:10'),(9,'ຄ່ານໍ້າປະປາ','expense','2026-08-17 08:47:10'),(10,'ຄ່າເຊົ່າສະຖານທີ່','expense','2026-08-17 08:47:10'),(11,'ເງິນດ່ວນ/ເງິນເດືອນ','expense','2026-08-17 08:47:10'),(12,'ຄ່າຕົ້ນທຶນ/ເຄື່ອງໃຊ້','expense','2026-08-17 08:47:10'),(13,'ລາຍຮັບຄ່ານາຍໜ້າ','income','2026-08-17 08:47:10'),(14,'ລາຍຮັບບໍລິການ','income','2026-08-17 08:47:10');
/*!40000 ALTER TABLE `accounting_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounting_records`
--

DROP TABLE IF EXISTS `accounting_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_type` varchar(20) NOT NULL DEFAULT 'expense',
  `category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `record_date` date NOT NULL,
  `note` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `store_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_records`
--

LOCK TABLES `accounting_records` WRITE;
/*!40000 ALTER TABLE `accounting_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounting_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `bank_code` varchar(50) DEFAULT 'BCEL',
  `bank_logo` varchar(255) DEFAULT NULL,
  `qr_code_img` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_accounts`
--

LOCK TABLES `bank_accounts` WRITE;
/*!40000 ALTER TABLE `bank_accounts` DISABLE KEYS */;
INSERT INTO `bank_accounts` VALUES (1,'BCEL One','16012000012345','ບໍລິສັດ ມິນິ ພອສ ຈຳກັດ','BCEL ONE','bank_1786689457_659.png','qr_bank_1786689457_118.jpeg',1,'2026-08-14 12:02:22'),(2,'LDB','01011000098765','ບໍລິສັດ ມິນິ ພອສ ຈຳກັດ','LDB','bank_1786689645_328.png','qr_bank_1786689645_710.jpeg',1,'2026-08-14 12:02:22'),(3,'JDB','05012000045678','ບໍລິສັດ ມິນິ ພອສ ຈຳກັດ','JDB','bank_1786689679_409.png','qr_bank_1786689679_840.jpeg',1,'2026-08-14 12:02:22');
/*!40000 ALTER TABLE `bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'ຂອງຫວານ','','2026-08-11 10:46:47'),(2,'ເຄື່ອງດື່ມ','','2026-08-11 10:47:03'),(4,'ເຫຼົ້າ','','2026-08-11 10:48:50'),(5,'ເຄື່ອງສຳອາງ','','2026-08-11 10:49:50'),(6,'ເຄື່ອງປຸງໃຊ້ຄົວເຮືອນ','','2026-08-11 10:50:31'),(7,'ເຄື່ອງໃຊ້ຫ້ອງການ','','2026-08-11 10:50:57'),(8,'ເຂົ້າໜົມເຄື່ອງແຫ້ງ','','2026-08-11 10:51:16'),(9,'ນົມ','','2026-08-11 10:51:56'),(10,'ຢາສູບ','','2026-08-11 10:52:34'),(11,'ສີນຄ້າ ບໍ່ມີບາໂຄດ','','2026-08-11 10:52:50'),(12,'ເບຍ','','2026-08-11 22:01:51');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `member_card` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `store_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_code` (`customer_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (3,'CUST-001','ໂຄລ່າ','020 77354334','MB001',NULL,NULL,'','2026-08-14 09:29:13',1);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_details`
--

DROP TABLE IF EXISTS `import_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_details` (
  `import_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `import_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unit_name` varchar(50) DEFAULT NULL,
  `multiplier` int(11) DEFAULT 1,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_base_qty` int(11) NOT NULL DEFAULT 1,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`import_detail_id`),
  KEY `import_id` (`import_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_details`
--

LOCK TABLES `import_details` WRITE;
/*!40000 ALTER TABLE `import_details` DISABLE KEYS */;
INSERT INTO `import_details` VALUES (1,1,120003,'ເເກັດ',1,5,5,250000.00,1250000.00,NULL),(2,2,20004,'ປ໋ອງ',1,5,5,6000.00,30000.00,NULL),(3,3,90011,'ປ໋ອງໃຫຍ່',1,100,100,6000.00,600000.00,NULL),(4,3,120003,'ເເກັດ',1,100,100,250000.00,25000000.00,NULL),(5,3,120004,'ແກ້ວ',1,200,200,16000.00,3200000.00,NULL),(6,3,20001,'ຕຸກ',1,100,100,6000.00,600000.00,NULL),(7,3,20006,'ເເພັກ',1,100,100,160000.00,16000000.00,NULL),(8,3,120009,'ແກ້ວ',1,100,100,10000.00,1000000.00,NULL),(9,4,120010,'ປ໋ອງສັ້ນ',1,200,200,10000.00,2000000.00,NULL),(10,4,120008,'ລັງ',1,100,100,200000.00,20000000.00,NULL),(11,4,20005,'ປ໋ອງຍາວ',1,100,100,6000.00,600000.00,NULL),(12,5,80002,'ຖົງ',1,100,100,20000.00,2000000.00,NULL),(13,5,20007,'ແກ້ວ',1,30,30,10000.00,300000.00,NULL);
/*!40000 ALTER TABLE `import_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imports` (
  `import_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `supplier_name` varchar(150) DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `import_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `store_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`import_id`),
  KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
INSERT INTO `imports` VALUES (1,'000001','',1250000.00,13,'2026-08-12 10:24:51','',1),(2,'000002','',30000.00,13,'2026-08-12 10:34:41','',1),(3,'000003','',46400000.00,4,'2026-08-12 15:13:39','',1),(4,'000004','',22600000.00,4,'2026-08-12 15:14:56','',1),(5,'000005','',2300000.00,4,'2026-08-12 15:15:28','',1);
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_adjustment_items`
--

DROP TABLE IF EXISTS `price_adjustment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_adjustment_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adjust_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `new_price` decimal(12,2) DEFAULT NULL,
  `diff_price` decimal(12,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_adjustment_items`
--

LOCK TABLES `price_adjustment_items` WRITE;
/*!40000 ALTER TABLE `price_adjustment_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_adjustment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_adjustments`
--

DROP TABLE IF EXISTS `price_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_adjustments` (
  `adjust_id` int(11) NOT NULL AUTO_INCREMENT,
  `adjust_date` date DEFAULT NULL,
  `adjust_time` time DEFAULT NULL,
  `target_mode` varchar(20) DEFAULT 'product',
  `category_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `target_price_type` varchar(20) DEFAULT 'price',
  `calc_type` varchar(20) DEFAULT 'set',
  `adjust_value` decimal(12,2) DEFAULT 0.00,
  `old_bprice` decimal(12,2) DEFAULT 0.00,
  `old_price` decimal(12,2) DEFAULT 0.00,
  `new_bprice` decimal(12,2) DEFAULT 0.00,
  `new_price` decimal(12,2) DEFAULT 0.00,
  `diff_amount` decimal(12,2) DEFAULT 0.00,
  `adjust_mode` varchar(20) DEFAULT NULL,
  `adjust_type` varchar(20) DEFAULT NULL,
  `price_type` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`adjust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_adjustments`
--

LOCK TABLES `price_adjustments` WRITE;
/*!40000 ALTER TABLE `price_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_batches`
--

DROP TABLE IF EXISTS `product_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_batches` (
  `batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `import_detail_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `initial_qty` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`batch_id`),
  KEY `product_id` (`product_id`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_batches`
--

LOCK TABLES `product_batches` WRITE;
/*!40000 ALTER TABLE `product_batches` DISABLE KEYS */;
INSERT INTO `product_batches` VALUES (1,120003,1,NULL,5,5,'2026-08-12 10:24:51'),(2,20004,2,NULL,5,5,'2026-08-12 10:34:41'),(3,90011,3,NULL,100,100,'2026-08-12 15:13:39'),(4,120003,4,NULL,100,100,'2026-08-12 15:13:39'),(5,120004,5,NULL,200,200,'2026-08-12 15:13:39'),(6,20001,6,NULL,100,100,'2026-08-12 15:13:39'),(7,20006,7,NULL,100,100,'2026-08-12 15:13:39'),(8,120009,8,NULL,100,100,'2026-08-12 15:13:39'),(9,120010,9,NULL,200,200,'2026-08-12 15:14:56'),(10,120008,10,NULL,100,100,'2026-08-12 15:14:56'),(11,20005,11,NULL,100,100,'2026-08-12 15:14:56'),(12,80002,12,NULL,100,100,'2026-08-12 15:15:28'),(13,20007,13,NULL,30,30,'2026-08-12 15:15:28');
/*!40000 ALTER TABLE `product_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_units`
--

DROP TABLE IF EXISTS `product_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `multiplier` int(11) NOT NULL DEFAULT 1,
  `bprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `barcode` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_product_units_barcode` (`barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_units`
--

LOCK TABLES `product_units` WRITE;
/*!40000 ALTER TABLE `product_units` DISABLE KEYS */;
INSERT INTO `product_units` VALUES (15,120010,'ເເກັດ',24,250000.00,360000.00,'5657456456344','2026-08-12 08:40:17'),(16,120010,'ແກ້ວ',1,96000.00,140000.00,'8859500422224','2026-08-12 08:40:17');
/*!40000 ALTER TABLE `product_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `code1` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `barcode` varchar(20) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `bprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(100) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `cook` tinyint(1) NOT NULL DEFAULT 0,
  `cut_qty` tinyint(1) NOT NULL DEFAULT 1,
  `branch_id` int(11) DEFAULT 1,
  `store_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`code1`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `idx_products_category_id` (`category_id`),
  KEY `idx_products_barcode` (`barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=5216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (5202,120003,'1838691642375','ເບຍລາວ',12,250000.00,350000.00,60,'ເເກັດ','prod_1786460621_4664.jfif',0,1,1,1),(5203,20001,'3501930447361','ເເປັບຊີ',2,6000.00,10000.00,70,'ຕຸກ','prod_1786460712_2808.jfif',0,1,1,1),(5205,80002,'4445707432261','ເລ',8,20000.00,30000.00,50,'ຖົງ','prod_1786460870_8363.png',0,1,1,1),(5208,120004,'5105201119395','ເບຍລາວ',12,16000.00,24000.00,67,'ແກ້ວ','prod_1786517492_5222.png',0,1,1,1),(5209,20005,'9959804716529','ແປັບຊີ',2,6000.00,12000.00,60,'ປ໋ອງຍາວ','prod_1786517534_2951.png',0,1,1,1),(5210,20006,'7048532538460','ເເປັບຊີ',2,160000.00,240000.00,70,'ເເພັກ','prod_1786517586_4535.jfif',0,1,1,1),(5211,20007,'5643534545438','ເເປັບຊີ',2,10000.00,15000.00,9,'ແກ້ວ','prod_1786517650_6779.png',0,1,1,1),(5212,120008,'5656458676797','ເບຍລາວ',12,200000.00,350000.00,20,'ລັງ','prod_1786517755_2517.jfif',0,1,1,1),(5213,120009,'3455453453451','ເບຍໄຮນິເກັນ',12,10000.00,20000.00,50,'ແກ້ວ','prod_1786517847_8201.png',0,1,1,1),(5214,120010,'8859500422223','ເບຍລາວ',12,10000.00,20000.00,0,'ປ໋ອງສັ້ນ','prod_1786517951_3219.jfif',0,1,1,1),(5215,90011,'6756456574567','ນົມເລັກຕາຊອຍ',9,6000.00,12000.00,70,'ປ໋ອງໃຫຍ່',NULL,0,1,1,1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotion_items`
--

DROP TABLE IF EXISTS `promotion_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotion_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `special_price` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `promo_id` (`promo_id`),
  KEY `product_id` (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotion_items`
--

LOCK TABLES `promotion_items` WRITE;
/*!40000 ALTER TABLE `promotion_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `promotion_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promo_name` varchar(255) DEFAULT NULL,
  `promo_type` varchar(50) DEFAULT NULL,
  `discount_type` varchar(50) DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `gift_product_name` varchar(255) DEFAULT NULL,
  `gift_qty` int(11) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `min_qty` int(11) DEFAULT 0,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `target_type` varchar(50) DEFAULT 'all',
  `target_name` varchar(255) DEFAULT 'ທຸກສິນຄ້າ',
  `target_unit_name` varchar(100) DEFAULT 'all',
  `status` int(11) DEFAULT 1,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES (7,'ສ່ວນຫຼຸດພິເສດ','discount','fixed',50000.00,'',1,'2026-08-14','2026-08-15',30,300000.00,'product','ເບຍລາວ','all',0,1),(8,'ແຖມນ້ຳກ້ອນຟຣີ','buy_x_get_y','fixed',0.00,'ນ້ຳກ້ອນ',1,'2026-08-14','2026-08-21',0,0.00,'category','ເບຍລາວ','ເເກັດ',1,1);
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `sold_by` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT 'ລູກຄ້າທົ່ວໄປ',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `vat_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `total_profit` decimal(12,2) DEFAULT 0.00,
  `cash_received` decimal(12,2) DEFAULT 0.00,
  `change_amount` decimal(12,2) DEFAULT 0.00,
  `payment_type` varchar(50) DEFAULT 'ເງິນສົດ',
  `status` varchar(20) DEFAULT 'SUCCESS',
  `created_at` datetime DEFAULT current_timestamp(),
  `store_id` int(11) NOT NULL DEFAULT 1,
  `tax_type` varchar(20) DEFAULT 'inclusive',
  `vat_rate` decimal(5,2) DEFAULT 7.00,
  `bank_account_id` int(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`sale_id`),
  UNIQUE KEY `inv_num` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'20260814-0002',0,0,'ລູກຄ້າທົ່ວໄປ',120000.00,0.00,0.00,120000.00,0.00,120000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 11:38:31',1,'none',0.00,NULL,NULL),(2,'20260814-0003',0,0,'ລູກຄ້າທົ່ວໄປ',4220000.00,0.00,0.00,4220000.00,0.00,4220000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 11:39:48',1,'none',0.00,NULL,NULL),(3,'20260814-0004',0,0,'ລູກຄ້າທົ່ວໄປ',6100000.00,0.00,0.00,6100000.00,0.00,6100000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 11:48:45',1,'none',0.00,NULL,NULL),(4,'20260814-0005',0,0,'ລູກຄ້າທົ່ວໄປ',240000.00,0.00,0.00,240000.00,0.00,240000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 11:51:30',1,'none',0.00,NULL,NULL),(5,'20260814-0006',0,0,'ລູກຄ້າທົ່ວໄປ',2720000.00,0.00,0.00,2720000.00,0.00,1000000.00,0.00,'ເງິນສົດ + ໂອນ','SUCCESS','2026-08-14 11:52:55',1,'none',0.00,NULL,NULL),(6,'20260814-0007',0,0,'ລູກຄ້າທົ່ວໄປ',400000.00,100000.00,0.00,300000.00,0.00,300000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 11:53:41',1,'none',0.00,NULL,NULL),(7,'20260814-0008',0,0,'ລູກຄ້າທົ່ວໄປ',3500000.00,0.00,0.00,3500000.00,0.00,3500000.00,0.00,'ໂອນເງິນ / QR','SUCCESS','2026-08-14 14:21:11',1,'none',0.00,2,'LDB'),(8,'20260814-0009',0,0,'ລູກຄ້າທົ່ວໄປ',750000.00,0.00,0.00,750000.00,0.00,750000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-14 16:03:30',1,'none',0.00,NULL,NULL),(9,'20260815-0001',0,0,'ລູກຄ້າທົ່ວໄປ',80000.00,0.00,0.00,80000.00,0.00,80000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-15 11:31:11',1,'none',0.00,NULL,NULL),(10,'20260815-0002',0,0,'ລູກຄ້າທົ່ວໄປ',20000.00,0.00,0.00,20000.00,0.00,20000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-15 11:32:34',1,'none',0.00,NULL,NULL),(16,'20260816-0001',0,0,'ລູກຄ້າທົ່ວໄປ',2066000.00,0.00,0.00,2066000.00,0.00,2066000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-16 09:36:52',1,'none',0.00,NULL,NULL),(17,'20260816-0002',0,0,'ລູກຄ້າທົ່ວໄປ',1063000.00,0.00,0.00,1063000.00,0.00,1063000.00,0.00,'ໂອນເງິນ / QR','SUCCESS','2026-08-16 09:52:51',1,'none',0.00,1,'BCEL ONE'),(18,'20260817-0001',0,0,'ລູກຄ້າທົ່ວໄປ',784000.00,0.00,0.00,784000.00,0.00,784000.00,0.00,'ເງິນສົດ','SUCCESS','2026-08-17 09:59:05',1,'none',0.00,NULL,NULL),(19,'20260817-0002',0,0,'ລູກຄ້າທົ່ວໄປ',998000.00,0.00,0.00,998000.00,0.00,998000.00,0.00,'ໂອນເງິນ / QR','SUCCESS','2026-08-17 10:00:46',1,'none',0.00,1,'BCEL ONE'),(20,'20260817-0003',0,0,'ລູກຄ້າທົ່ວໄປ',7934000.00,0.00,0.00,7934000.00,0.00,7934000.00,0.00,'ໂອນເງິນ / QR','SUCCESS','2026-08-17 10:04:42',1,'none',0.00,1,'BCEL ONE'),(21,'20260817-0004',0,0,'ລູກຄ້າທົ່ວໄປ',4192000.00,0.00,0.00,4192000.00,0.00,4192000.00,0.00,'ໂອນເງິນ / QR','SUCCESS','2026-08-17 10:08:27',1,'none',0.00,2,'LDB');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shelves`
--

DROP TABLE IF EXISTS `shelves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shelves` (
  `shelf_id` int(11) NOT NULL AUTO_INCREMENT,
  `shelf_name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`shelf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shelves`
--

LOCK TABLES `shelves` WRITE;
/*!40000 ALTER TABLE `shelves` DISABLE KEYS */;
INSERT INTO `shelves` VALUES (1,'ຕູ້ແຊ່ເຄື່ອງດື່ມ A1',1,'2026-08-11 10:06:55'),(2,'ຊັ້ນວາງຂະໜົມ B1',2,'2026-08-11 10:06:55');
/*!40000 ALTER TABLE `shelves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'vat_rate','0','2026-08-12 11:18:14'),(2,'expiry_warning_days','30','2026-08-12 11:18:14');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_delete_bill_log`
--

DROP TABLE IF EXISTS `tb_delete_bill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_delete_bill_log` (
  `id` int(11) NOT NULL,
  `sale_bill` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `delete_reason` text DEFAULT NULL,
  `delete_date` date DEFAULT NULL,
  `delete_time` time DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_delete_bill_log`
--

LOCK TABLES `tb_delete_bill_log` WRITE;
/*!40000 ALTER TABLE `tb_delete_bill_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_delete_bill_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbcompanyinfo`
--

DROP TABLE IF EXISTS `tbcompanyinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbcompanyinfo` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `com_name_la` varchar(255) NOT NULL,
  `com_address` varchar(255) DEFAULT NULL,
  `com_tel` varchar(255) DEFAULT NULL,
  `com_email` varchar(255) DEFAULT NULL,
  `img_url` varchar(120) NOT NULL,
  `qr_img` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `barcode2` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  `vat_percent` decimal(5,2) DEFAULT 7.00,
  `tax_type` varchar(20) DEFAULT 'inclusive',
  `license_start_date` date DEFAULT '2026-01-01',
  `license_expire_date` date DEFAULT '2026-12-31',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbcompanyinfo`
--

LOCK TABLES `tbcompanyinfo` WRITE;
/*!40000 ALTER TABLE `tbcompanyinfo` DISABLE KEYS */;
INSERT INTO `tbcompanyinfo` VALUES (1,'ຮ້ານ ຂາຍດີ ຈະເລີນຊັບ','ນະຄອນຫຼວງວຽງຈັນ','020 95321848','kholainfo@gmail.com','logo.png','qr_1786682094.jpeg','ຂອບໃຈທີ່ມາອຸດໜູນ, ໂອກາດໜ້າເຊີນໃໝ່!',NULL,NULL,NULL,NULL,NULL,1,0.00,'none','2026-01-01','2026-12-31');
/*!40000 ALTER TABLE `tbcompanyinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbexchange`
--

DROP TABLE IF EXISTS `tbexchange`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbexchange` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ex_date` date DEFAULT NULL,
  `ex_time` time DEFAULT NULL,
  `ex_kip_bath` varchar(255) DEFAULT NULL,
  `ex_kip_us` varchar(255) DEFAULT NULL,
  `ex_status` varchar(255) DEFAULT NULL,
  `ex_userlogin` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbexchange`
--

LOCK TABLES `tbexchange` WRITE;
/*!40000 ALTER TABLE `tbexchange` DISABLE KEYS */;
INSERT INTO `tbexchange` VALUES (4,'2026-08-14','14:52:15','720','22000','Active','ສອນປະເສີດ',1);
/*!40000 ALTER TABLE `tbexchange` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_printer`
--

DROP TABLE IF EXISTS `tbl_printer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_printer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `type` enum('ip','browser') DEFAULT 'ip',
  `status` tinyint(1) DEFAULT 1,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_printer`
--

LOCK TABLES `tbl_printer` WRITE;
/*!40000 ALTER TABLE `tbl_printer` DISABLE KEYS */;
INSERT INTO `tbl_printer` VALUES (3,'ເຄື່ອງພິມບິນໜ້າຮ້ານ','192.168.1.110','ip',0,1),(4,'ເຄື່ອງພິມບິນເຮືອນຄົວ','192.168.1.112','browser',0,1);
/*!40000 ALTER TABLE `tbl_printer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbsale_save`
--

DROP TABLE IF EXISTS `tbsale_save`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbsale_save` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_save_bill` varchar(50) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `sale_save_table` varchar(20) DEFAULT NULL,
  `sale_save_status` int(11) DEFAULT NULL,
  `sale_qty` int(11) DEFAULT NULL,
  `sale_amount` decimal(10,2) DEFAULT NULL COMMENT '(ยอดรวมก่อนส่วนลด)',
  `sale_discount_item` decimal(10,2) DEFAULT NULL,
  `sale_after_item` decimal(10,2) DEFAULT NULL,
  `sale_pay` decimal(10,2) DEFAULT NULL COMMENT '(เงินสด)',
  `sale_transfer` decimal(10,2) DEFAULT NULL COMMENT '(เงินโอน)',
  `sale_percented_bill` int(11) DEFAULT NULL COMMENT '(ส่วนลด %)',
  `sale_discount_bill` decimal(10,2) DEFAULT NULL COMMENT '(ส่วนลดเป็นเงิน)',
  `sale_tip` decimal(10,2) DEFAULT NULL,
  `sale_barlance` decimal(10,2) DEFAULT NULL COMMENT '(ยอดสุทธิที่ต้องจ่าย)',
  `sale_return` decimal(10,2) DEFAULT NULL COMMENT '(เงินทอน)',
  `user_receive` varchar(255) DEFAULT NULL COMMENT '(ผู้รับเงิน)',
  `sale_remark` text DEFAULT NULL,
  `sale_status` varchar(50) DEFAULT NULL COMMENT '(paid / cancel)',
  `customer_id` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `type_pay` varchar(255) DEFAULT NULL COMMENT '(cash / transfer / split)',
  `sale_time` time DEFAULT '00:00:00',
  `queue_no` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  `store_id` int(11) NOT NULL DEFAULT 1,
  `bank_account_id` int(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `uq_sale_save_bill` (`sale_save_bill`),
  KEY `idx_sale_date` (`sale_date`),
  KEY `idx_sale_status` (`sale_status`)
) ENGINE=InnoDB AUTO_INCREMENT=23823 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbsale_save`
--

LOCK TABLES `tbsale_save` WRITE;
/*!40000 ALTER TABLE `tbsale_save` DISABLE KEYS */;
INSERT INTO `tbsale_save` VALUES (23769,'INV-20260812-0001','2026-08-12',NULL,NULL,11,1880000.00,NULL,NULL,1880000.00,NULL,NULL,0.00,NULL,1880000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','14:55:09',NULL,1,1,NULL,NULL),(23770,'INV-20260812-23770','2026-08-12',NULL,NULL,11,1880000.00,NULL,NULL,1880000.00,NULL,NULL,0.00,NULL,1880000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','14:56:41',NULL,1,1,NULL,NULL),(23771,'INV-20260812-23771','2026-08-12',NULL,NULL,5,1090000.00,NULL,NULL,1090000.00,NULL,NULL,0.00,NULL,1090000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ໂອນເງິນ / QR','14:57:55',NULL,1,1,NULL,NULL),(23772,'INV-20260812-23772','2026-08-12',NULL,NULL,4,748000.00,NULL,NULL,748000.00,NULL,NULL,0.00,NULL,748000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ໂອນເງິນ / QR','14:59:00',NULL,1,1,NULL,NULL),(23773,'INV-20260812-23773','2026-08-12',NULL,NULL,9,2164000.00,NULL,NULL,2164000.00,NULL,NULL,0.00,NULL,2164000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:00:07',NULL,1,1,NULL,NULL),(23774,'INV-20260812-23774','2026-08-12',NULL,NULL,3,720000.00,NULL,NULL,720000.00,NULL,NULL,0.00,NULL,720000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:00:29',NULL,1,1,NULL,NULL),(23775,'INV-20260812-23775','2026-08-12',NULL,NULL,9,2164000.00,NULL,NULL,2164000.00,NULL,NULL,0.00,NULL,2164000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:01:31',NULL,1,1,NULL,NULL),(23776,'INV-20260812-23776','2026-08-12',NULL,NULL,6,1448000.00,NULL,NULL,1448000.00,NULL,NULL,0.00,NULL,1448000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ໂອນເງິນ / QR','15:03:36',NULL,1,1,NULL,NULL),(23777,'INV-20260812-23777','2026-08-12',NULL,NULL,6,780000.00,NULL,NULL,780000.00,NULL,NULL,0.00,NULL,780000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:05:16',NULL,1,1,NULL,NULL),(23778,'INV-20260812-23778','2026-08-12',NULL,NULL,6,780000.00,NULL,NULL,780000.00,NULL,NULL,0.00,NULL,780000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:06:01',NULL,1,1,NULL,NULL),(23779,'INV-20260812-23779','2026-08-12',NULL,NULL,8,2148000.00,NULL,NULL,2148000.00,NULL,NULL,0.00,NULL,2148000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:11:22',NULL,1,1,NULL,NULL),(23780,'INV-20260812-23780','2026-08-12',NULL,NULL,10,150000.00,NULL,NULL,150000.00,NULL,NULL,0.00,NULL,150000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:21:15',NULL,1,1,NULL,NULL),(23781,'INV-20260812-23781','2026-08-12',NULL,NULL,6,90000.00,NULL,NULL,90000.00,NULL,NULL,0.00,NULL,90000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:28:23',NULL,1,1,NULL,NULL),(23782,'INV-20260812-23782','2026-08-12',NULL,NULL,1,360000.00,NULL,NULL,360000.00,NULL,NULL,0.00,NULL,360000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:30:26',NULL,1,1,NULL,NULL),(23783,'INV-20260812-23783','2026-08-12',NULL,NULL,157,3140000.00,NULL,NULL,3140000.00,NULL,NULL,0.00,NULL,3140000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','15:45:31',NULL,1,1,NULL,NULL),(23784,'INV-20260812-23784','2026-08-12',NULL,NULL,2,700000.00,NULL,NULL,177077.00,NULL,NULL,0.00,NULL,700000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ + ໂອນ','16:26:37',NULL,1,1,NULL,NULL),(23785,'INV-20260812-23785','2026-08-12',NULL,NULL,15,360000.00,NULL,NULL,100000.00,NULL,NULL,0.00,NULL,360000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ + ໂອນ','16:31:04',NULL,1,1,NULL,NULL),(23786,'INV-20260812-23786','2026-08-12',NULL,NULL,12,288000.00,NULL,NULL,288000.00,NULL,NULL,0.00,NULL,288000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','16:31:38',NULL,1,1,NULL,NULL),(23787,'INV-20260812-23787','2026-08-12',NULL,NULL,5,120000.00,NULL,NULL,120000.00,NULL,NULL,0.00,NULL,120000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','16:39:09',NULL,1,1,NULL,NULL),(23788,'INV-20260812-23788','2026-08-12',NULL,NULL,3,1050000.00,NULL,NULL,1000000.00,NULL,NULL,50000.00,NULL,1000000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','16:42:24',NULL,1,1,NULL,NULL),(23789,'INV-20260812-23789','2026-08-12',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','16:42:54',NULL,1,1,NULL,NULL),(23790,'INV-20260813-23790','2026-08-13',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','08:29:16',NULL,1,1,NULL,NULL),(23791,'INV-20260813-23791','2026-08-13',NULL,NULL,73,5990000.00,NULL,NULL,6000000.00,NULL,NULL,0.00,NULL,5990000.00,10000.00,'ສອນປະເສີດ',NULL,'CANCEL','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','08:35:05',NULL,1,1,NULL,NULL),(23792,'INV-20260813-23792','2026-08-13',NULL,NULL,20,480000.00,NULL,NULL,480000.00,NULL,NULL,0.00,NULL,480000.00,0.00,'ສອນປະເສີດ',NULL,'CANCEL','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','09:10:22',NULL,1,1,NULL,NULL),(23793,'INV-20260813-0004','2026-08-13',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','09:12:15',NULL,1,1,NULL,NULL),(23794,'20260813-0005','2026-08-13',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','09:14:54',NULL,1,1,NULL,NULL),(23795,'20260813-0006','2026-08-13',NULL,NULL,53,5490000.00,NULL,NULL,3000000.00,NULL,NULL,200000.00,NULL,5290000.00,0.00,'ສອນປະເສີດ',NULL,'CANCEL','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ + ໂອນ','10:24:18',NULL,1,1,NULL,NULL),(23796,'20260813-0007','2026-08-13',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','11:24:57',NULL,1,1,NULL,NULL),(23797,'20260813-0008','2026-08-13',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','11:39:31',NULL,1,1,NULL,NULL),(23798,'20260813-0009','2026-08-13',NULL,NULL,7,2450000.00,NULL,NULL,2450000.00,NULL,NULL,0.00,NULL,2450000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','11:39:58',NULL,1,1,NULL,NULL),(23799,'20260813-0010','2026-08-13',NULL,NULL,6,180000.00,NULL,NULL,180000.00,NULL,NULL,0.00,NULL,180000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','11:46:02',NULL,1,1,NULL,NULL),(23800,'20260813-0011','2026-08-13',NULL,NULL,10,120000.00,NULL,NULL,120000.00,NULL,NULL,0.00,NULL,120000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ',NULL,'ເງິນສົດ','12:50:08',NULL,1,1,NULL,NULL),(23801,'20260814-0001','2026-08-14',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','3','ໂຄລ່າ','::1','ເງິນສົດ','09:29:19',NULL,1,1,NULL,NULL),(23802,'20260814-0002','2026-08-14',NULL,NULL,10,120000.00,NULL,NULL,120000.00,NULL,NULL,0.00,NULL,120000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:38:31',NULL,1,1,NULL,NULL),(23803,'20260814-0003','2026-08-14',NULL,NULL,50,4220000.00,NULL,NULL,4220000.00,NULL,NULL,0.00,NULL,4220000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:39:48',NULL,1,1,NULL,NULL),(23804,'20260814-0004','2026-08-14',NULL,NULL,30,6100000.00,NULL,NULL,6100000.00,NULL,NULL,0.00,NULL,6100000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:48:44',NULL,1,1,NULL,NULL),(23805,'20260814-0005','2026-08-14',NULL,NULL,10,240000.00,NULL,NULL,240000.00,NULL,NULL,0.00,NULL,240000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:51:30',NULL,1,1,NULL,NULL),(23806,'20260814-0006','2026-08-14',NULL,NULL,30,2720000.00,NULL,NULL,1000000.00,NULL,NULL,0.00,NULL,2720000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ + ໂອນ','11:52:55',NULL,1,1,NULL,NULL),(23807,'20260814-0007','2026-08-14',NULL,NULL,20,400000.00,NULL,NULL,300000.00,NULL,NULL,100000.00,NULL,300000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:53:41',NULL,1,1,NULL,NULL),(23808,'20260814-0008','2026-08-14',NULL,NULL,10,3500000.00,NULL,NULL,3500000.00,NULL,NULL,0.00,NULL,3500000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ໂອນເງິນ / QR','14:21:11',NULL,1,1,2,'LDB'),(23809,'20260814-0009','2026-08-14',NULL,NULL,3,750000.00,NULL,NULL,750000.00,NULL,NULL,0.00,NULL,750000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','16:03:30',NULL,1,1,NULL,NULL),(23810,'20260815-0001','2026-08-15',NULL,NULL,4,80000.00,NULL,NULL,80000.00,NULL,NULL,0.00,NULL,80000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:31:11',NULL,1,1,NULL,NULL),(23811,'20260815-0002','2026-08-15',NULL,NULL,1,20000.00,NULL,NULL,20000.00,NULL,NULL,0.00,NULL,20000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ເງິນສົດ','11:32:34',NULL,1,1,NULL,NULL),(23817,'20260816-0001','2026-08-16',NULL,NULL,11,2066000.00,NULL,NULL,2066000.00,NULL,NULL,0.00,NULL,2066000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','192.168.100.143','ເງິນສົດ','09:36:52',NULL,1,1,NULL,NULL),(23818,'20260816-0002','2026-08-16',NULL,NULL,10,1063000.00,NULL,NULL,1063000.00,NULL,NULL,0.00,NULL,1063000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','192.168.100.143','ໂອນເງິນ / QR','09:52:51',NULL,1,1,1,'BCEL ONE'),(23819,'20260817-0001','2026-08-17',NULL,NULL,6,784000.00,NULL,NULL,784000.00,NULL,NULL,0.00,NULL,784000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','192.168.100.28','ເງິນສົດ','09:59:05',NULL,1,1,NULL,NULL),(23820,'20260817-0002','2026-08-17',NULL,NULL,14,998000.00,NULL,NULL,998000.00,NULL,NULL,0.00,NULL,998000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ໂອນເງິນ / QR','10:00:46',NULL,1,1,1,'BCEL ONE'),(23821,'20260817-0003','2026-08-17',NULL,NULL,52,7934000.00,NULL,NULL,7934000.00,NULL,NULL,0.00,NULL,7934000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ໂອນເງິນ / QR','10:04:42',NULL,1,1,1,'BCEL ONE'),(23822,'20260817-0004','2026-08-17',NULL,NULL,43,4192000.00,NULL,NULL,4192000.00,NULL,NULL,0.00,NULL,4192000.00,0.00,'ສອນປະເສີດ',NULL,'SUCCESS','','ລູກຄ້າທົ່ວໄປ','::1','ໂອນເງິນ / QR','10:08:27',NULL,1,1,2,'LDB');
/*!40000 ALTER TABLE `tbsale_save` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbsale_save_detail`
--

DROP TABLE IF EXISTS `tbsale_save_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbsale_save_detail` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `save_bill` varchar(50) DEFAULT NULL,
  `save_date` date DEFAULT NULL,
  `save_time` time NOT NULL,
  `save_table` varchar(255) DEFAULT NULL,
  `save_proid` int(11) DEFAULT NULL,
  `save_proname` varchar(255) NOT NULL,
  `save_qty` int(11) DEFAULT NULL,
  `save_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ຕົ້ນທືນ',
  `save_money` decimal(10,2) NOT NULL DEFAULT 0.00,
  `save_discount_item` decimal(10,2) DEFAULT NULL,
  `save_net_money` decimal(10,2) DEFAULT NULL,
  `save_status` int(11) DEFAULT NULL,
  `user_receives` varchar(255) DEFAULT NULL,
  `sale_sale_remark` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  PRIMARY KEY (`Id`),
  KEY `idx_save_bill` (`save_bill`),
  KEY `idx_save_proid` (`save_proid`),
  KEY `idx_save_date` (`save_date`)
) ENGINE=InnoDB AUTO_INCREMENT=52947 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbsale_save_detail`
--

LOCK TABLES `tbsale_save_detail` WRITE;
/*!40000 ALTER TABLE `tbsale_save_detail` DISABLE KEYS */;
INSERT INTO `tbsale_save_detail` VALUES (52809,'INV-20260812-0001','2026-08-12','14:55:09',NULL,120008,'ເບຍລາວ (ລັງ)',3,350000.00,200000.00,1050000.00,NULL,1050000.00,NULL,'ສອນປະເສີດ',NULL,1),(52810,'INV-20260812-0001','2026-08-12','14:55:09',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52811,'INV-20260812-0001','2026-08-12','14:55:09',NULL,120003,'ເບຍລາວ (ເເກັດ)',2,350000.00,250000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52812,'INV-20260812-0001','2026-08-12','14:55:09',NULL,20007,'ເເປັບຊີ (ແກ້ວ)',2,15000.00,10000.00,30000.00,NULL,30000.00,NULL,'ສອນປະເສີດ',NULL,1),(52813,'INV-20260812-0001','2026-08-12','14:55:09',NULL,80002,'ເລ (ຖົງ)',2,30000.00,20000.00,60000.00,NULL,60000.00,NULL,'ສອນປະເສີດ',NULL,1),(52814,'INV-20260812-23770','2026-08-12','14:56:41',NULL,120008,'ເບຍລາວ (ລັງ)',3,350000.00,200000.00,1050000.00,NULL,1050000.00,NULL,'ສອນປະເສີດ',NULL,1),(52815,'INV-20260812-23770','2026-08-12','14:56:41',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52816,'INV-20260812-23770','2026-08-12','14:56:41',NULL,120003,'ເບຍລາວ (ເເກັດ)',2,350000.00,250000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52817,'INV-20260812-23770','2026-08-12','14:56:41',NULL,20007,'ເເປັບຊີ (ແກ້ວ)',2,15000.00,10000.00,30000.00,NULL,30000.00,NULL,'ສອນປະເສີດ',NULL,1),(52818,'INV-20260812-23770','2026-08-12','14:56:41',NULL,80002,'ເລ (ຖົງ)',2,30000.00,20000.00,60000.00,NULL,60000.00,NULL,'ສອນປະເສີດ',NULL,1),(52819,'INV-20260812-23771','2026-08-12','14:57:55',NULL,120003,'ເບຍລາວ (ເເກັດ)',2,350000.00,250000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52820,'INV-20260812-23771','2026-08-12','14:57:55',NULL,120008,'ເບຍລາວ (ລັງ)',1,350000.00,200000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52821,'INV-20260812-23771','2026-08-12','14:57:55',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52822,'INV-20260812-23772','2026-08-12','14:59:00',NULL,120004,'ເບຍລາວ (ແກ້ວ)',2,24000.00,16000.00,48000.00,NULL,48000.00,NULL,'ສອນປະເສີດ',NULL,1),(52823,'INV-20260812-23772','2026-08-12','14:59:00',NULL,120003,'ເບຍລາວ (ເເກັດ)',1,350000.00,250000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52824,'INV-20260812-23772','2026-08-12','14:59:00',NULL,120008,'ເບຍລາວ (ລັງ)',1,350000.00,200000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52825,'INV-20260812-23773','2026-08-12','15:00:07',NULL,120004,'ເບຍລາວ (ແກ້ວ)',1,24000.00,16000.00,24000.00,NULL,24000.00,NULL,'ສອນປະເສີດ',NULL,1),(52826,'INV-20260812-23773','2026-08-12','15:00:07',NULL,120008,'ເບຍລາວ (ລັງ)',6,350000.00,200000.00,2100000.00,NULL,2100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52827,'INV-20260812-23773','2026-08-12','15:00:07',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52828,'INV-20260812-23774','2026-08-12','15:00:29',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',1,20000.00,10000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52829,'INV-20260812-23774','2026-08-12','15:00:29',NULL,120008,'ເບຍລາວ (ລັງ)',2,350000.00,200000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52830,'INV-20260812-23775','2026-08-12','15:01:31',NULL,120004,'ເບຍລາວ (ແກ້ວ)',1,24000.00,16000.00,24000.00,NULL,24000.00,NULL,'ສອນປະເສີດ',NULL,1),(52831,'INV-20260812-23775','2026-08-12','15:01:31',NULL,120008,'ເບຍລາວ (ລັງ)',6,350000.00,200000.00,2100000.00,NULL,2100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52832,'INV-20260812-23775','2026-08-12','15:01:31',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52833,'INV-20260812-23776','2026-08-12','15:03:36',NULL,120003,'ເບຍລາວ (ເເກັດ)',1,350000.00,250000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52834,'INV-20260812-23776','2026-08-12','15:03:36',NULL,120004,'ເບຍລາວ (ແກ້ວ)',2,24000.00,16000.00,48000.00,NULL,48000.00,NULL,'ສອນປະເສີດ',NULL,1),(52835,'INV-20260812-23776','2026-08-12','15:03:36',NULL,120008,'ເບຍລາວ (ລັງ)',3,350000.00,200000.00,1050000.00,NULL,1050000.00,NULL,'ສອນປະເສີດ',NULL,1),(52836,'INV-20260812-23777','2026-08-12','15:05:16',NULL,120008,'ເບຍລາວ (ລັງ)',2,350000.00,200000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52837,'INV-20260812-23777','2026-08-12','15:05:16',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',4,20000.00,10000.00,80000.00,NULL,80000.00,NULL,'ສອນປະເສີດ',NULL,1),(52838,'INV-20260812-23778','2026-08-12','15:06:01',NULL,120008,'ເບຍລາວ (ລັງ)',2,350000.00,200000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52839,'INV-20260812-23778','2026-08-12','15:06:01',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',4,20000.00,10000.00,80000.00,NULL,80000.00,NULL,'ສອນປະເສີດ',NULL,1),(52840,'INV-20260812-23779','2026-08-12','15:11:22',NULL,120008,'ເບຍລາວ (ລັງ)',6,350000.00,200000.00,2100000.00,NULL,2100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52841,'INV-20260812-23779','2026-08-12','15:11:22',NULL,120004,'ເບຍລາວ (ແກ້ວ)',2,24000.00,16000.00,48000.00,NULL,48000.00,NULL,'ສອນປະເສີດ',NULL,1),(52842,'INV-20260812-23780','2026-08-12','15:21:15',NULL,20007,'ເເປັບຊີ (ແກ້ວ)',10,15000.00,10000.00,150000.00,NULL,150000.00,NULL,'ສອນປະເສີດ',NULL,1),(52843,'INV-20260812-23781','2026-08-12','15:28:23',NULL,20007,'ເເປັບຊີ (ແກ້ວ)',6,15000.00,10000.00,90000.00,NULL,90000.00,NULL,'ສອນປະເສີດ',NULL,1),(52844,'INV-20260812-23782','2026-08-12','15:30:26',NULL,120010,'ເບຍລາວ (ເເກັດ)',1,360000.00,250000.00,360000.00,NULL,360000.00,NULL,'ສອນປະເສີດ',NULL,1),(52845,'INV-20260812-23783','2026-08-12','15:45:31',NULL,120010,'ເບຍລາວ (ປ໋ອງສັ້ນ)',157,20000.00,10000.00,3140000.00,NULL,3140000.00,NULL,'ສອນປະເສີດ',NULL,1),(52846,'INV-20260812-23784','2026-08-12','16:26:37',NULL,120008,'ເບຍລາວ (ລັງ)',2,350000.00,200000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52847,'INV-20260812-23785','2026-08-12','16:31:04',NULL,120004,'ເບຍລາວ (ແກ້ວ)',15,24000.00,16000.00,360000.00,NULL,360000.00,NULL,'ສອນປະເສີດ',NULL,1),(52848,'INV-20260812-23786','2026-08-12','16:31:38',NULL,120004,'ເບຍລາວ (ແກ້ວ)',12,24000.00,16000.00,288000.00,NULL,288000.00,NULL,'ສອນປະເສີດ',NULL,1),(52849,'INV-20260812-23787','2026-08-12','16:39:09',NULL,120004,'ເບຍລາວ (ແກ້ວ)',5,24000.00,16000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52850,'INV-20260812-23788','2026-08-12','16:42:24',NULL,120008,'ເບຍລາວ (ລັງ)',3,350000.00,200000.00,1050000.00,NULL,1050000.00,NULL,'ສອນປະເສີດ',NULL,1),(52851,'INV-20260812-23789','2026-08-12','16:42:54',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52852,'INV-20260813-23790','2026-08-13','08:29:16',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52853,'INV-20260813-23791','2026-08-13','08:35:05',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',10,20000.00,10000.00,200000.00,NULL,200000.00,NULL,'ສອນປະເສີດ',NULL,1),(52854,'INV-20260813-23791','2026-08-13','08:35:05',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52855,'INV-20260813-23791','2026-08-13','08:35:05',NULL,20006,'ເເປັບຊີ (ເເພັກ)',10,240000.00,160000.00,2400000.00,NULL,2400000.00,NULL,'ສອນປະເສີດ',NULL,1),(52856,'INV-20260813-23791','2026-08-13','08:35:05',NULL,20001,'ເເປັບຊີ (ຕຸກ)',10,10000.00,6000.00,100000.00,NULL,100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52857,'INV-20260813-23791','2026-08-13','08:35:05',NULL,80002,'ເລ (ຖົງ)',16,30000.00,20000.00,480000.00,NULL,480000.00,NULL,'ສອນປະເສີດ',NULL,1),(52858,'INV-20260813-23791','2026-08-13','08:35:05',NULL,120003,'ເບຍລາວ (ເເກັດ)',7,350000.00,250000.00,2450000.00,NULL,2450000.00,NULL,'ສອນປະເສີດ',NULL,1),(52859,'INV-20260813-23791','2026-08-13','08:35:05',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52860,'INV-20260813-23792','2026-08-13','09:10:23',NULL,120004,'ເບຍລາວ (ແກ້ວ)',20,24000.00,16000.00,480000.00,NULL,480000.00,NULL,'ສອນປະເສີດ',NULL,1),(52861,'INV-20260813-0004','2026-08-13','09:12:15',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52862,'20260813-0005','2026-08-13','09:14:54',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52863,'20260813-0006','2026-08-13','10:24:18',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52864,'20260813-0006','2026-08-13','10:24:18',NULL,120003,'ເບຍລາວ (ເເກັດ)',7,350000.00,250000.00,2450000.00,NULL,2450000.00,NULL,'ສອນປະເສີດ',NULL,1),(52865,'20260813-0006','2026-08-13','10:24:18',NULL,80002,'ເລ (ຖົງ)',6,30000.00,20000.00,180000.00,NULL,180000.00,NULL,'ສອນປະເສີດ',NULL,1),(52866,'20260813-0006','2026-08-13','10:24:18',NULL,20001,'ເເປັບຊີ (ຕຸກ)',10,10000.00,6000.00,100000.00,NULL,100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52867,'20260813-0006','2026-08-13','10:24:18',NULL,20006,'ເເປັບຊີ (ເເພັກ)',10,240000.00,160000.00,2400000.00,NULL,2400000.00,NULL,'ສອນປະເສີດ',NULL,1),(52868,'20260813-0006','2026-08-13','10:24:18',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52869,'20260813-0007','2026-08-13','11:24:57',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52870,'20260813-0008','2026-08-13','11:39:31',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52871,'20260813-0009','2026-08-13','11:39:58',NULL,120003,'ເບຍລາວ (ເເກັດ)',7,350000.00,250000.00,2450000.00,NULL,2450000.00,NULL,'ສອນປະເສີດ',NULL,1),(52872,'20260813-0010','2026-08-13','11:46:02',NULL,80002,'ເລ (ຖົງ)',6,30000.00,20000.00,180000.00,NULL,180000.00,NULL,'ສອນປະເສີດ',NULL,1),(52873,'20260813-0011','2026-08-13','12:50:08',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52874,'20260814-0001','2026-08-14','09:29:19',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52875,'20260814-0002','2026-08-14','11:38:31',NULL,90011,'ນົມເລັກຕາຊອຍ (ປ໋ອງໃຫຍ່)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52876,'20260814-0003','2026-08-14','11:39:49',NULL,90011,'ນົມເລັກຕາຊອຍ (ປ໋ອງໃຫຍ່)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52877,'20260814-0003','2026-08-14','11:39:49',NULL,120003,'ເບຍລາວ (ເເກັດ)',10,350000.00,250000.00,3500000.00,NULL,3500000.00,NULL,'ສອນປະເສີດ',NULL,1),(52878,'20260814-0003','2026-08-14','11:39:49',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',10,20000.00,10000.00,200000.00,NULL,200000.00,NULL,'ສອນປະເສີດ',NULL,1),(52879,'20260814-0003','2026-08-14','11:39:49',NULL,20001,'ເເປັບຊີ (ຕຸກ)',10,10000.00,6000.00,100000.00,NULL,100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52880,'20260814-0003','2026-08-14','11:39:49',NULL,80002,'ເລ (ຖົງ)',10,30000.00,20000.00,300000.00,NULL,300000.00,NULL,'ສອນປະເສີດ',NULL,1),(52881,'20260814-0004','2026-08-14','11:48:45',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',10,20000.00,10000.00,200000.00,NULL,200000.00,NULL,'ສອນປະເສີດ',NULL,1),(52882,'20260814-0004','2026-08-14','11:48:45',NULL,120008,'ເບຍລາວ (ລັງ)',10,350000.00,200000.00,3500000.00,NULL,3500000.00,NULL,'ສອນປະເສີດ',NULL,1),(52883,'20260814-0004','2026-08-14','11:48:45',NULL,20006,'ເເປັບຊີ (ເເພັກ)',10,240000.00,160000.00,2400000.00,NULL,2400000.00,NULL,'ສອນປະເສີດ',NULL,1),(52884,'20260814-0005','2026-08-14','11:51:30',NULL,120004,'ເບຍລາວ (ແກ້ວ)',10,24000.00,16000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52885,'20260814-0006','2026-08-14','11:52:55',NULL,20006,'ເເປັບຊີ (ເເພັກ)',10,240000.00,160000.00,2400000.00,NULL,2400000.00,NULL,'ສອນປະເສີດ',NULL,1),(52886,'20260814-0006','2026-08-14','11:52:55',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52887,'20260814-0006','2026-08-14','11:52:55',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',10,20000.00,10000.00,200000.00,NULL,200000.00,NULL,'ສອນປະເສີດ',NULL,1),(52888,'20260814-0007','2026-08-14','11:53:41',NULL,80002,'ເລ (ຖົງ)',10,30000.00,20000.00,300000.00,NULL,300000.00,NULL,'ສອນປະເສີດ',NULL,1),(52889,'20260814-0007','2026-08-14','11:53:41',NULL,20001,'ເເປັບຊີ (ຕຸກ)',10,10000.00,6000.00,100000.00,NULL,100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52890,'20260814-0008','2026-08-14','14:21:11',NULL,120008,'ເບຍລາວ (ລັງ)',10,350000.00,200000.00,3500000.00,NULL,3500000.00,NULL,'ສອນປະເສີດ',NULL,1),(52891,'20260814-0009','2026-08-14','16:03:30',NULL,120003,'ເບຍລາວ (ເເກັດ)',3,250000.00,250000.00,750000.00,NULL,750000.00,NULL,'ສອນປະເສີດ',NULL,1),(52892,'20260815-0001','2026-08-15','11:31:11',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',4,20000.00,10000.00,80000.00,NULL,80000.00,NULL,'ສອນປະເສີດ',NULL,1),(52893,'20260815-0002','2026-08-15','11:32:34',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',1,20000.00,10000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52908,'20260816-0001','2026-08-16','09:36:52',NULL,120003,'ເບຍລາວ (ເເກັດ)',3,350000.00,250000.00,1050000.00,NULL,1050000.00,NULL,'ສອນປະເສີດ',NULL,1),(52909,'20260816-0001','2026-08-16','09:36:52',NULL,120008,'ເບຍລາວ (ລັງ)',2,350000.00,200000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52910,'20260816-0001','2026-08-16','09:36:52',NULL,120004,'ເບຍລາວ (ແກ້ວ)',1,24000.00,16000.00,24000.00,NULL,24000.00,NULL,'ສອນປະເສີດ',NULL,1),(52911,'20260816-0001','2026-08-16','09:36:52',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',1,20000.00,10000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52912,'20260816-0001','2026-08-16','09:36:52',NULL,20001,'ເເປັບຊີ (ຕຸກ)',2,10000.00,6000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52913,'20260816-0001','2026-08-16','09:36:52',NULL,20006,'ເເປັບຊີ (ເເພັກ)',1,240000.00,160000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52914,'20260816-0001','2026-08-16','09:36:52',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',1,12000.00,6000.00,12000.00,NULL,12000.00,NULL,'ສອນປະເສີດ',NULL,1),(52915,'20260816-0002','2026-08-16','09:52:51',NULL,120003,'ເບຍລາວ (ເເກັດ)',1,350000.00,250000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52916,'20260816-0002','2026-08-16','09:52:51',NULL,90011,'ນົມເລັກຕາຊອຍ (ປ໋ອງໃຫຍ່)',1,12000.00,6000.00,12000.00,NULL,12000.00,NULL,'ສອນປະເສີດ',NULL,1),(52917,'20260816-0002','2026-08-16','09:52:51',NULL,120004,'ເບຍລາວ (ແກ້ວ)',1,24000.00,16000.00,24000.00,NULL,24000.00,NULL,'ສອນປະເສີດ',NULL,1),(52918,'20260816-0002','2026-08-16','09:52:51',NULL,120008,'ເບຍລາວ (ລັງ)',1,350000.00,200000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52919,'20260816-0002','2026-08-16','09:52:51',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',1,20000.00,10000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52920,'20260816-0002','2026-08-16','09:52:51',NULL,20001,'ເເປັບຊີ (ຕຸກ)',1,10000.00,6000.00,10000.00,NULL,10000.00,NULL,'ສອນປະເສີດ',NULL,1),(52921,'20260816-0002','2026-08-16','09:52:51',NULL,80002,'ເລ (ຖົງ)',1,30000.00,20000.00,30000.00,NULL,30000.00,NULL,'ສອນປະເສີດ',NULL,1),(52922,'20260816-0002','2026-08-16','09:52:51',NULL,20007,'ເເປັບຊີ (ແກ້ວ)',1,15000.00,10000.00,15000.00,NULL,15000.00,NULL,'ສອນປະເສີດ',NULL,1),(52923,'20260816-0002','2026-08-16','09:52:51',NULL,20006,'ເເປັບຊີ (ເເພັກ)',1,240000.00,160000.00,240000.00,NULL,240000.00,NULL,'ສອນປະເສີດ',NULL,1),(52924,'20260816-0002','2026-08-16','09:52:51',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',1,12000.00,6000.00,12000.00,NULL,12000.00,NULL,'ສອນປະເສີດ',NULL,1),(52925,'20260817-0001','2026-08-17','09:59:05',NULL,120003,'ເບຍລາວ (ເເກັດ)',1,350000.00,250000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52926,'20260817-0001','2026-08-17','09:59:05',NULL,120008,'ເບຍລາວ (ລັງ)',1,350000.00,200000.00,350000.00,NULL,350000.00,NULL,'ສອນປະເສີດ',NULL,1),(52927,'20260817-0001','2026-08-17','09:59:05',NULL,120004,'ເບຍລາວ (ແກ້ວ)',1,24000.00,16000.00,24000.00,NULL,24000.00,NULL,'ສອນປະເສີດ',NULL,1),(52928,'20260817-0001','2026-08-17','09:59:05',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',1,20000.00,10000.00,20000.00,NULL,20000.00,NULL,'ສອນປະເສີດ',NULL,1),(52929,'20260817-0001','2026-08-17','09:59:05',NULL,80002,'ເລ (ຖົງ)',1,30000.00,20000.00,30000.00,NULL,30000.00,NULL,'ສອນປະເສີດ',NULL,1),(52930,'20260817-0001','2026-08-17','09:59:05',NULL,20001,'ເເປັບຊີ (ຕຸກ)',1,10000.00,6000.00,10000.00,NULL,10000.00,NULL,'ສອນປະເສີດ',NULL,1),(52931,'20260817-0002','2026-08-17','10:00:46',NULL,80002,'ເລ (ຖົງ)',3,30000.00,20000.00,90000.00,NULL,90000.00,NULL,'ສອນປະເສີດ',NULL,1),(52932,'20260817-0002','2026-08-17','10:00:46',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',2,20000.00,10000.00,40000.00,NULL,40000.00,NULL,'ສອນປະເສີດ',NULL,1),(52933,'20260817-0002','2026-08-17','10:00:46',NULL,120004,'ເບຍລາວ (ແກ້ວ)',7,24000.00,16000.00,168000.00,NULL,168000.00,NULL,'ສອນປະເສີດ',NULL,1),(52934,'20260817-0002','2026-08-17','10:00:46',NULL,120003,'ເບຍລາວ (ເເກັດ)',2,350000.00,250000.00,700000.00,NULL,700000.00,NULL,'ສອນປະເສີດ',NULL,1),(52935,'20260817-0003','2026-08-17','10:04:42',NULL,90011,'ນົມເລັກຕາຊອຍ (ປ໋ອງໃຫຍ່)',9,12000.00,6000.00,108000.00,NULL,108000.00,NULL,'ສອນປະເສີດ',NULL,1),(52936,'20260817-0003','2026-08-17','10:04:42',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',8,12000.00,6000.00,96000.00,NULL,96000.00,NULL,'ສອນປະເສີດ',NULL,1),(52937,'20260817-0003','2026-08-17','10:04:42',NULL,20006,'ເເປັບຊີ (ເເພັກ)',8,240000.00,160000.00,1920000.00,NULL,1920000.00,NULL,'ສອນປະເສີດ',NULL,1),(52938,'20260817-0003','2026-08-17','10:04:42',NULL,20001,'ເເປັບຊີ (ຕຸກ)',6,10000.00,6000.00,60000.00,NULL,60000.00,NULL,'ສອນປະເສີດ',NULL,1),(52939,'20260817-0003','2026-08-17','10:04:42',NULL,80002,'ເລ (ຖົງ)',5,30000.00,20000.00,150000.00,NULL,150000.00,NULL,'ສອນປະເສີດ',NULL,1),(52940,'20260817-0003','2026-08-17','10:04:42',NULL,120003,'ເບຍລາວ (ເເກັດ)',10,350000.00,250000.00,3500000.00,NULL,3500000.00,NULL,'ສອນປະເສີດ',NULL,1),(52941,'20260817-0003','2026-08-17','10:04:42',NULL,120008,'ເບຍລາວ (ລັງ)',6,350000.00,200000.00,2100000.00,NULL,2100000.00,NULL,'ສອນປະເສີດ',NULL,1),(52942,'20260817-0004','2026-08-17','10:08:27',NULL,80002,'ເລ (ຖົງ)',10,30000.00,20000.00,300000.00,NULL,300000.00,NULL,'ສອນປະເສີດ',NULL,1),(52943,'20260817-0004','2026-08-17','10:08:27',NULL,20005,'ແປັບຊີ (ປ໋ອງຍາວ)',10,12000.00,6000.00,120000.00,NULL,120000.00,NULL,'ສອນປະເສີດ',NULL,1),(52944,'20260817-0004','2026-08-17','10:08:27',NULL,120009,'ເບຍໄຮນິເກັນ (ແກ້ວ)',10,20000.00,10000.00,200000.00,NULL,200000.00,NULL,'ສອນປະເສີດ',NULL,1),(52945,'20260817-0004','2026-08-17','10:08:27',NULL,120008,'ເບຍລາວ (ລັງ)',10,350000.00,200000.00,3500000.00,NULL,3500000.00,NULL,'ສອນປະເສີດ',NULL,1),(52946,'20260817-0004','2026-08-17','10:08:27',NULL,120004,'ເບຍລາວ (ແກ້ວ)',3,24000.00,16000.00,72000.00,NULL,72000.00,NULL,'ສອນປະເສີດ',NULL,1);
/*!40000 ALTER TABLE `tbsale_save_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbstore`
--

DROP TABLE IF EXISTS `tbstore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbstore` (
  `store_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_code` varchar(50) NOT NULL,
  `store_name` varchar(150) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `address` text DEFAULT NULL,
  `tel` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`store_id`),
  UNIQUE KEY `store_code` (`store_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbstore`
--

LOCK TABLES `tbstore` WRITE;
/*!40000 ALTER TABLE `tbstore` DISABLE KEYS */;
INSERT INTO `tbstore` VALUES (1,'1','ຮ້ານ ຂາຍດີ ຈະເລີນຊັບ',1,'ນະຄອນຫຼວງວຽງຈັນ','020 95321848','active','2026-08-11 08:59:38'),(2,'2','ຂາຍດີ ຈະເລີນຊັບ 2',0,'ຈັນທະບູລີ','020 77354334','active','2026-08-14 14:47:46');
/*!40000 ALTER TABLE `tbstore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbuser`
--

DROP TABLE IF EXISTS `tbuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbuser` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `user_code` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `sale` int(11) NOT NULL DEFAULT 0,
  `cafe` int(11) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0,
  `kitchen` int(11) NOT NULL DEFAULT 0,
  `tbl` int(11) NOT NULL DEFAULT 0,
  `report` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `setup` int(11) NOT NULL DEFAULT 0,
  `users` int(11) NOT NULL DEFAULT 0,
  `edit` int(11) NOT NULL DEFAULT 0,
  `expired_date` date DEFAULT NULL,
  `store_id` int(11) DEFAULT 1,
  `accounting` tinyint(1) DEFAULT 0,
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `tel` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT 'default.png',
  `customers` tinyint(1) DEFAULT 1,
  `dashboard` tinyint(1) DEFAULT 0,
  `permissions` tinyint(1) DEFAULT 0,
  `branches` tinyint(1) DEFAULT 0,
  `database` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbuser`
--

LOCK TABLES `tbuser` WRITE;
/*!40000 ALTER TABLE `tbuser` DISABLE KEYS */;
INSERT INTO `tbuser` VALUES (4,'002','ສອນປະເສີດ','$2y$10$HxlxPyYFk9YIdtgiADt2f.0nvSFkj/A2Mz05fjsCmNJpuBQF1Yyka','ຜູ້ບໍລິຫານ',1,0,0,0,0,1,1,1,1,1,NULL,1,1,'ສອນປະເສີດ','','ຊາຍ','2026-08-10','02077354334','','','default.png',1,0,0,0,0),(5,'003','admin','111111','ພະນັກງານຂາຍ',1,0,0,0,0,0,0,0,0,0,NULL,2,0,'admin','','ຊາຍ','2005-09-16','020 95321848','','','default.png',0,0,0,0,0);
/*!40000 ALTER TABLE `tbuser` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-17 10:11:23
