-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: quan_ly_kho
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
-- Current Database: `quan_ly_kho`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `quan_ly_kho` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `quan_ly_kho`;

--
-- Table structure for table `dieu_chinh_kho`
--

DROP TABLE IF EXISTS `dieu_chinh_kho`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dieu_chinh_kho` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `san_pham_id` int(11) NOT NULL,
  `ngay_dieu_chinh` date NOT NULL,
  `loai` enum('huy','cho_muon','muon') NOT NULL COMMENT 'huy: gi???m kho, cho_muon: gi???m kho, muon: t??ng kho',
  `so_luong` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nguoi_lien_quan` varchar(150) DEFAULT NULL COMMENT '?????i t??c/nh??n vi??n m?????n ho???c ng?????i duy???t h???y',
  `ghi_chu` varchar(255) DEFAULT NULL,
  `nguoi_tao_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ngay_dieu_chinh` (`ngay_dieu_chinh`),
  KEY `idx_loai` (`loai`),
  KEY `idx_san_pham` (`san_pham_id`),
  KEY `nguoi_tao_id` (`nguoi_tao_id`),
  CONSTRAINT `dieu_chinh_kho_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dieu_chinh_kho_ibfk_2` FOREIGN KEY (`nguoi_tao_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dieu_chinh_kho`
--

LOCK TABLES `dieu_chinh_kho` WRITE;
/*!40000 ALTER TABLE `dieu_chinh_kho` DISABLE KEYS */;
INSERT INTO `dieu_chinh_kho` VALUES (1,6,'2026-09-01','huy',7.00,'','Bánh hết date',1,'2026-09-07 01:42:05',NULL),(2,6,'2026-09-05','huy',2.00,'','Bánh hết date',1,'2026-09-07 01:42:48',NULL),(4,22,'2026-09-06','huy',2.00,'Test Huy Date','Kiem tra dinh ky',1,'2026-09-07 01:56:37',NULL),(5,21,'2026-09-06','huy',3.00,'Test Huy Date','Kiem tra dinh ky',1,'2026-09-07 01:56:37',NULL),(6,6,'2026-09-06','huy',2.00,'','',1,'2026-09-07 02:08:12',NULL);
/*!40000 ALTER TABLE `dieu_chinh_kho` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dinh_luong_mon`
--

DROP TABLE IF EXISTS `dinh_luong_mon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dinh_luong_mon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_mon_pos` varchar(100) NOT NULL COMMENT 'Mã món bán trên POS / File Excel',
  `ten_mon_pos` varchar(255) NOT NULL COMMENT 'Tên món trên POS (vd: Matcha Mochi, Sandwich)',
  `san_pham_id` int(11) NOT NULL COMMENT 'ID của nguyên vật liệu/sản phẩm kiểm kê trong kho',
  `so_luong_tieu_hao` decimal(12,3) NOT NULL DEFAULT 1.000 COMMENT 'Số lượng NVL dùng cho 1 phần bán',
  `ghi_chu` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ma_pos` (`ma_mon_pos`),
  KEY `idx_sp_id` (`san_pham_id`),
  CONSTRAINT `dinh_luong_mon_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dinh_luong_mon`
--

LOCK TABLES `dinh_luong_mon` WRITE;
/*!40000 ALTER TABLE `dinh_luong_mon` DISABLE KEYS */;
INSERT INTO `dinh_luong_mon` VALUES (15,'CF_MATCHA_MC','Matcha Tây Bắc Mochi',16,1.000,'1 ly dùng 1 viên mochi','2026-09-07 02:10:34',NULL),(16,'10090030','Wafu Pasta Heo Nướng Xốt Shoyu Butter',141,1.000,'','2026-09-07 02:31:27',NULL);
/*!40000 ALTER TABLE `dinh_luong_mon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kiem_ke`
--

DROP TABLE IF EXISTS `kiem_ke`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kiem_ke` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `san_pham_id` int(11) NOT NULL,
  `ngay_kiem_ke` date NOT NULL,
  `ton_ly_thuyet` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ton_thuc_te` decimal(12,2) NOT NULL DEFAULT 0.00,
  `chenh_lech` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ton_thuc_te - ton_ly_thuyet',
  `ghi_chu` varchar(255) DEFAULT NULL,
  `nguoi_kiem_ke_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sp_ngay_kiem_ke` (`san_pham_id`,`ngay_kiem_ke`),
  KEY `idx_ngay_kiem_ke` (`ngay_kiem_ke`),
  KEY `idx_san_pham` (`san_pham_id`),
  KEY `nguoi_kiem_ke_id` (`nguoi_kiem_ke_id`),
  CONSTRAINT `kiem_ke_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kiem_ke_ibfk_2` FOREIGN KEY (`nguoi_kiem_ke_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kiem_ke`
--

LOCK TABLES `kiem_ke` WRITE;
/*!40000 ALTER TABLE `kiem_ke` DISABLE KEYS */;
INSERT INTO `kiem_ke` VALUES (1,2,'2026-09-05',50.00,53.00,3.00,'',1,'2026-09-06 04:40:07',NULL),(2,1,'2026-09-05',100.00,98.00,-2.00,'',1,'2026-09-06 04:40:07',NULL),(3,3,'2026-09-05',80.00,80.00,0.00,'',1,'2026-09-06 04:40:07','2026-09-07 00:45:30'),(4,2,'2026-09-06',15.00,14.00,-1.00,'',1,'2026-09-06 05:01:44',NULL),(5,1,'2026-09-06',20.00,20.00,0.00,'',1,'2026-09-06 05:01:44',NULL),(6,3,'2026-09-06',80.00,28.00,-52.00,'',1,'2026-09-06 05:01:44','2026-09-07 01:25:10'),(7,22,'2026-09-06',-6.00,-6.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(8,21,'2026-09-06',-1.00,-2.00,-1.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(9,20,'2026-09-06',47.00,1.00,-46.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(10,6,'2026-09-06',-1.00,1.00,2.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(11,7,'2026-09-06',2.00,1.00,-1.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(12,15,'2026-09-06',23.00,1.00,-22.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(13,8,'2026-09-06',-2.00,1.00,3.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(14,10,'2026-09-06',10.00,-1.00,-11.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(15,13,'2026-09-06',25.00,-8.00,-33.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(16,17,'2026-09-06',-1.00,1.00,2.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(17,16,'2026-09-06',13.00,-7.00,-20.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(18,19,'2026-09-06',-2.00,-2.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(19,23,'2026-09-06',-3.00,-3.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(20,18,'2026-09-06',-7.00,-7.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(21,12,'2026-09-06',-9.00,-9.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(22,14,'2026-09-06',-7.00,-7.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(23,11,'2026-09-06',-4.00,-4.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(24,9,'2026-09-06',3.00,-2.00,-5.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(25,24,'2026-09-06',-1.00,-1.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 01:25:10'),(26,25,'2026-09-06',-14.00,-10.00,4.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(27,26,'2026-09-06',0.00,-9.00,-9.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(28,70,'2026-09-06',0.00,0.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:22:40'),(29,69,'2026-09-06',0.00,0.00,0.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:22:40'),(31,105,'2026-09-06',35.00,0.00,-35.00,'',1,'2026-09-06 05:20:48','2026-09-07 02:31:27'),(56,139,'2026-09-06',0.00,0.00,0.00,'',1,'2026-09-06 05:37:40','2026-09-07 01:25:10'),(57,138,'2026-09-06',0.00,0.00,0.00,'',1,'2026-09-06 05:37:40','2026-09-07 01:25:10'),(58,140,'2026-09-06',0.00,0.00,0.00,'',1,'2026-09-06 05:37:40','2026-09-07 01:25:10'),(85,141,'2026-09-06',55.00,4.00,-51.00,'',1,'2026-09-06 06:06:10','2026-09-07 02:31:27');
/*!40000 ALTER TABLE `kiem_ke` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhap_hang`
--

DROP TABLE IF EXISTS `nhap_hang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhap_hang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `san_pham_id` int(11) NOT NULL,
  `ngay_nhap` date NOT NULL,
  `so_luong` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nha_cung_cap` varchar(255) DEFAULT NULL,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `nguoi_nhap_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ngay_nhap` (`ngay_nhap`),
  KEY `idx_san_pham` (`san_pham_id`),
  KEY `nguoi_nhap_id` (`nguoi_nhap_id`),
  CONSTRAINT `nhap_hang_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nhap_hang_ibfk_2` FOREIGN KEY (`nguoi_nhap_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhap_hang`
--

LOCK TABLES `nhap_hang` WRITE;
/*!40000 ALTER TABLE `nhap_hang` DISABLE KEYS */;
INSERT INTO `nhap_hang` VALUES (10,6,'2026-09-02',4.00,'','',1,'2026-09-07 01:39:57',NULL),(11,8,'2026-09-02',2.00,'','',1,'2026-09-07 01:39:57',NULL),(12,9,'2026-09-02',2.00,'','',1,'2026-09-07 01:39:57',NULL),(13,105,'2026-09-02',48.00,'','',1,'2026-09-07 01:39:57',NULL),(14,7,'2026-09-02',6.00,'','',1,'2026-09-07 01:39:57',NULL),(15,6,'2026-09-06',4.00,'','',1,'2026-09-07 01:40:56',NULL),(16,7,'2026-09-06',8.00,'','',1,'2026-09-07 01:40:56',NULL),(17,9,'2026-09-06',2.00,'','',1,'2026-09-07 01:40:56',NULL),(18,8,'2026-09-06',2.00,'','',1,'2026-09-07 01:40:56',NULL);
/*!40000 ALTER TABLE `nhap_hang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `san_pham`
--

DROP TABLE IF EXISTS `san_pham`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `san_pham` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_sp` varchar(50) NOT NULL,
  `ten_sp` varchar(255) NOT NULL,
  `nhom_sp` varchar(100) NOT NULL COMMENT 'B??nh, ????? ??n, N?????c su???i, ????? u???ng pha ch???...',
  `don_vi_tinh` varchar(50) NOT NULL DEFAULT 'C??i',
  `can_kiem_ke` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: C???n ki???m k??, 0: B??? qua khi ki???m k??',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Ho???t ?????ng, 0: ???? x??a m???m',
  `ghi_chu` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_sp` (`ma_sp`),
  KEY `idx_ma_sp` (`ma_sp`),
  KEY `idx_can_kiem_ke` (`can_kiem_ke`),
  KEY `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `san_pham`
--

LOCK TABLES `san_pham` WRITE;
/*!40000 ALTER TABLE `san_pham` DISABLE KEYS */;
INSERT INTO `san_pham` VALUES (1,'CAKE01','B??nh Croissant B?? Ph??p','B??nh','C??i',1,0,'H??ng c???n ki???m k?? h??ng ng??y','2026-09-06 04:27:18','2026-09-06 05:17:49'),(2,'FOOD01','B??nh M?? Sandwich Th???t Ngu???i','????? ??n','C??i',1,0,'H??ng c???n ki???m k?? h??ng ng??y','2026-09-06 04:27:18','2026-09-06 05:17:38'),(3,'WATER01','N?????c Su???i Aquafina 500ml','N?????c su???i','Chai',0,1,'H??ng c???n ki???m k?? h??ng ng??y','2026-09-06 04:27:18','2026-09-07 01:26:13'),(4,'CF001','C?? Ph?? S???a Pha Phin','????? u???ng pha ch???','Ly',0,0,'M??n n?????c pha ch??? - t??? ?????ng b??? qua ki???m k??','2026-09-06 04:27:18','2026-09-06 05:17:41'),(5,'TEA01','Tr?? ????o Cam S???','????? u???ng pha ch???','Ly',0,0,'M??n n?????c pha ch??? - t??? ?????ng b??? qua ki???m k??','2026-09-06 04:27:18','2026-09-06 05:17:45'),(6,'10080041','Burnt Cheesecake','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(7,'10080043','Butter Croissant Mama','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(8,'10080047','Croissant Kẹp Ham - Cheese','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(9,'10080051','Su Kem','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(10,'10080057','Croissant Phô Mai Bơ Tỏi','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(11,'10080058','Soft Pizza Chà Bông Trứng Cút','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(12,'11040010','Mousse Gấu Chocolate','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(13,'11040146','Croissant Trứng muối','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(14,'11040159','Mousse Tiramisu','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(15,'11040162','Chà Bông Phô Mai','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(16,'11040242','Mochi Kem Matcha','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(17,'11040243','Mochi Kem Chocolate','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(18,'11040272','Mochi Kem Việt Quất','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(19,'11040273','Mochi Kem Phúc Bồn Tử','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(20,'11040310','Bánh Mì Que Pate Cột Đèn','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(21,'11040311','Bánh Mì Que Chà Bông Phô Mai Bơ Cay','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(22,'11040312','Bánh Mì Que Bò Nấm Xốt Bơ','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(23,'11040313','Mochi Kem Trà Sữa Trân Châu','BÁNH VÀ SNACK','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(24,'10080097','Bánh Trung Thu Matcha Ube','Bữa Trưa','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(25,'10090029','Wafu Pasta Bò Bằm Xốt Bolognes','Bữa Trưa','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38','2026-09-07 02:32:48'),(26,'10090030','Wafu Pasta Heo Nướng Xốt Shoyu Butter','Bữa Trưa','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38','2026-09-07 02:32:40'),(27,'10020499','Cold Brew Truyền Thống (Nhỏ)','CÀ PHÊ COLD BREW','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(28,'10020502','Cold Brew Truyền Thống (Vừa)','CÀ PHÊ COLD BREW','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(29,'10020504','Cold Brew Kim Quất (Vừa)','CÀ PHÊ COLD BREW','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(30,'10020003','Americano Nóng (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(31,'10020015','Cappucino Đá (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(32,'10020017','Cappucino Nóng (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(33,'10020026','Espresso nóng (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(34,'10020478','Americano Classic (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(35,'10020485','Americano Classic (Lớn)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(36,'10020497','Caramel Machiato (Đá) (Lớn)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(37,'10020531','Espresso Đá (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(38,'10020587','Americano Chanh Leo (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(39,'10020588','Americano Chanh Leo (Lớn)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(40,'10020589','Americano Phúc Bồn Tử (Nhỏ)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(41,'10020590','Americano Phúc Bồn Tử (Vừa)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(42,'10020591','Americano Phúc Bồn Tử (Lớn)','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(43,'10020611','Airy-cano Yuzu','CÀ PHÊ MÁY','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(44,'10020492','Bạc Xỉu Caramel Muối (Vừa)','CÀ PHÊ PHIN','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(45,'10020493','Bạc Xỉu Caramel Muối (Lớn)','CÀ PHÊ PHIN','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(46,'10020005','Bạc Xỉu (Vừa)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(47,'10020007','Cà Phê Đen Đá (Vừa)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(48,'10020011','Cà Phê Sữa Đá (Vừa)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(49,'10020222','Cà Phê Sữa Đá (Lớn)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(50,'10020223','Cà phê sữa nóng','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(51,'10020224','Cà Phê Đen Đá (Lớn)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(52,'10020225','Cà phê đen nóng','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(53,'10020226','Bạc Xỉu (Lớn)','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(54,'10020227','Bạc Xỉu nóng','CÀ PHÊ VIỆT NAM','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(55,'10030060','Frappe Choco Chip (Vừa)','FRAPPE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(56,'10030061','Frappe Choco Chip (Lớn)','FRAPPE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(57,'10030117','Frappe Matcha Tây Bắc (Vừa)','FRAPPE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(58,'10030118','Frappe Matcha Tây Bắc (Lớn)','FRAPPE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(59,'10020506','Latte Classic (Vừa)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(60,'10020507','Latte Classic (Lớn)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(61,'10020508','Latte Bạc Xỉu (Nhỏ)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(62,'10020509','Latte Bạc Xỉu (Vừa)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(63,'10020510','Latte Bạc Xỉu (Lớn)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(64,'10020521','Latte Hạnh Nhân Oatside (Vừa)','LATTE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(65,'10010225','Matcha Latte Tây Bắc (Nhỏ)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(66,'10010226','Matcha Latte Tây Bắc (Vừa)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(67,'10010227','Matcha Latte Tây Bắc (Lớn)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(68,'10010229','Matcha Latte Tây Bắc (Nóng) (Vừa)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(69,'10010341','Matcha Latte Tây Bắc Mochi (Vừa)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38','2026-09-07 02:14:24'),(70,'10010342','Matcha Latte Tây Bắc Mochi (Lớn)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38','2026-09-07 02:14:33'),(71,'10010410','Matcha Layers Dâu','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(72,'10010411','Matcha Layers Xoài','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(73,'10010412','Matcha Layers Đào Dưa Lưới','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(74,'10050003','Chocolate (Nóng)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(75,'10050004','Chocolate (Đá)','MATCHA VÀ SOCOLA','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(76,'10070025','Bean Bông Yêu Nước','MERCHANDISE','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(77,'10010297','Matcha Latte Kyoto (Vừa)','Món mới','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(78,'10010300','Matcha Latte Tây Bắc Trân Châu Hoàng Kim (Vừa)','Món mới','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(79,'10010312','Matcha Latte Tây Bắc Trân Châu Hoàng Kim (Lớn)','Món mới','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(80,'SHIP','Phí vận chuyển','PHÍ SHIP','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(81,'10010365','PLT Trà Đào Cam Sả (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(82,'10010371','PLT Matcha Latte Tây Bắc (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(83,'10010372','PLT Matcha Latte Tây Bắc (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(84,'10010383','PLT Shan Sữa Truyền Thống (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(85,'10020537','PLT Bạc Xỉu (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(86,'10020538','PLT Bạc Xỉu (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(87,'10020543','PLT Bạc Xỉu Caramel Muối (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(88,'10020545','PLT Cà Phê Sữa Đá (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(89,'10020551','PLT Americano Classic (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(90,'10020558','PLT Espresso Đá (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(91,'10020561','PLT Caramel Machiato (Đá) (Lớn)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(92,'10020572','PLT Cold Brew Truyền Thống (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(93,'10020573','PLT Cold Brew Kim Quất (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(94,'10020614','PLT Airy-cano Yuzu','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(95,'10040092','PLT Shan Xanh Quýt Cam (Vừa)','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(96,'10080062','PLT Bánh Mì Que Pate Cột Đèn','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(97,'10080063','PLT Bánh Mì Que Chà Bông Phô Mai Bơ Cay','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(98,'10080064','PLT Bánh Mì Que Bò Nấm Xốt Bơ','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(99,'10080077','PLT Chà Bông Phô Mai','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(100,'10100095','+ PLT Kem Phô Mai Macchiato','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(101,'10100103','+ PLT Syrup Hạnh Nhân','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(102,'10100117','+ PLT Sữa Tươi - MTB','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(103,'10100124','+ PLT Sữa Yến Mạch - CMD','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(104,'10100128','+ PLT Sữa Yến Mạch - MTB','Platform','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(105,'11030018','Nước suối TCH','THỨC UỐNG KHÁC','Cái',1,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38','2026-09-06 05:19:21'),(106,'10100003','+ Shot Espresso','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(107,'10100044','+ Trân Châu Hoàng Kim','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(108,'10100054','+ Sữa Tươi - CN','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(109,'10100065','+ Sữa Yến Mạch - MO','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(110,'10100067','+ Sữa Yến Mạch - CN','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(111,'10100081','+ Sữa Yến Mạch','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(112,'10100084','+ Sữa Tươi - MTB','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(113,'10100087','+ Sữa Tươi - SD','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(114,'10100089','+ Sữa Yến Mạch - MTB','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(115,'10100137','+ Trân châu củ năng','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(116,'10100139','Trân Châu Hoàng Kim (Lẻ)','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(117,'10100143','Trái Vải (Lẻ)','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(118,'10100150','Trân châu củ năng (Lẻ)','TOPPING','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(119,'10010003','Trà Đào Cam Sả (Vừa)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(120,'10010004','Trà Đào Cam Sả (Nhỏ)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(121,'10010042','Trà Đào Cam Sả nóng','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(122,'10010057','Trà Đào Cam Sả (Lớn)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(123,'10040083','Shan Xanh Quýt Cam (Vừa)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(124,'10040084','Shan Xanh Quýt Cam (Lớn)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(125,'10040086','Shan Xanh Vải Hibiscus (Vừa)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(126,'10040087','Shan Xanh Vải Hibiscus (Lớn)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(127,'10040089','Shan Xanh Sen (Vừa)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(128,'10040090','Shan Xanh Sen (Lớn)','TRÀ ĐẶC BIỆT','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(129,'10010347','Shan Sữa Truyền Thống (Vừa)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(130,'10010348','Shan Sữa Truyền Thống (Lớn)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(131,'10010349','Shan Cheese Foam (Vừa)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(132,'10010350','Shan Cheese Foam (Lớn)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(133,'10010391','Shan Sữa Truyền Thống Nóng','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(134,'10010416','Shan Sữa Khói Đậm Vị (Vừa)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(135,'10010417','Shan Sữa Khói Đậm Vị (Lớn)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(136,'10010418','Shan Sữa Khói Hoàng Kim (Vừa)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(137,'10010419','Shan Sữa Khói Hoàng Kim (Lớn)','TRÀ SỮA MACCHIATIO','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-06 05:15:38',NULL),(138,'NVL_VOBANH','Vỏ Bánh Sandwich','Nguyên Vật Liệu','Cái',1,0,NULL,'2026-09-06 05:31:40','2026-09-07 01:26:48'),(139,'NVL_PHOMAI','Phô Mai Lát Cheddar','Nguyên Vật Liệu','Lát',1,0,NULL,'2026-09-06 05:31:40','2026-09-07 01:26:57'),(140,'NVL_XOTGA','Xốt Gà Cay','Nguyên Vật Liệu','Gói',1,0,NULL,'2026-09-06 05:31:40','2026-09-07 01:26:39'),(141,'123567','sốt heo','Nguyên vật liệu','Bịch',1,1,'','2026-09-06 06:04:33',NULL),(142,'10020584','Americano Mơ (Lớn)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:01',NULL),(143,'10020610','Airy-cano Classic','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:01',NULL),(144,'10010364','PLT Trà Đào Cam Sả (Vừa)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:03',NULL),(145,'10020542','PLT Bạc Xỉu Caramel Muối (Vừa)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:04',NULL),(146,'10020578','PLT Latte Bạc Xỉu (Vừa)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:08',NULL),(147,'10100024','+ Kem Phô Mai Macchiato','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:12',NULL),(148,'10040094','PLT Shan Xanh Vải Hibiscus (Nhỏ)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:13',NULL),(149,'10040095','PLT Shan Xanh Vải Hibiscus (Vừa)','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:13',NULL),(150,'10100051','+ Sữa Tươi - MLK','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:15',NULL),(151,'10100070','+ Sữa Yến Mạch - LC','Đồ uống pha chế / Khác','Cái',0,1,'Tự động thêm từ file Excel số bán','2026-09-07 00:37:15',NULL);
/*!40000 ALTER TABLE `san_pham` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `so_ban`
--

DROP TABLE IF EXISTS `so_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `so_ban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `upload_file_id` int(11) DEFAULT NULL,
  `san_pham_id` int(11) NOT NULL,
  `ngay_ban` date NOT NULL,
  `so_luong` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sp_ngay_ban` (`san_pham_id`,`ngay_ban`),
  KEY `idx_ngay_ban` (`ngay_ban`),
  KEY `idx_san_pham` (`san_pham_id`),
  KEY `so_ban_ibfk_1` (`upload_file_id`),
  CONSTRAINT `so_ban_ibfk_1` FOREIGN KEY (`upload_file_id`) REFERENCES `upload_files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `so_ban_ibfk_2` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `so_ban`
--

LOCK TABLES `so_ban` WRITE;
/*!40000 ALTER TABLE `so_ban` DISABLE KEYS */;
INSERT INTO `so_ban` VALUES (512,13,6,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(513,13,7,'2026-09-01',4.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(514,13,9,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(515,13,11,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(516,13,12,'2026-09-01',2.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(517,13,13,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(518,13,15,'2026-09-01',3.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(519,13,16,'2026-09-01',7.00,'Tự động từ file: 0109.xls | Bao gồm 2 từ món [Matcha Latte Tây Bắc Mochi (Vừa)]; Bao gồm 3 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(520,13,18,'2026-09-01',3.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(521,13,19,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(522,13,20,'2026-09-01',10.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(523,13,22,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(524,13,23,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(525,13,25,'2026-09-01',1.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(526,13,141,'2026-09-01',1.00,'Tự động từ file: 0109.xls | Từ 1 phần [Wafu Pasta Heo Nướng Xốt Shoyu Butter]','2026-09-07 02:31:27',NULL),(527,13,105,'2026-09-01',3.00,'Tự động từ file: 0109.xls','2026-09-07 02:31:27',NULL),(528,14,6,'2026-09-02',4.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(529,14,7,'2026-09-02',5.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(530,14,10,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(531,14,11,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(532,14,12,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(533,14,13,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(534,14,14,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(535,14,15,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(536,14,16,'2026-09-02',5.00,'Tự động từ file: 0209.xls | Bao gồm 1 từ món [Matcha Latte Tây Bắc Mochi (Vừa)]; Bao gồm 1 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(537,14,17,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(538,14,18,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(539,14,20,'2026-09-02',6.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(540,14,21,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(541,14,22,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(542,14,23,'2026-09-02',1.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(543,14,25,'2026-09-02',2.00,'Tự động từ file: 0209.xls','2026-09-07 02:31:27',NULL),(544,15,6,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(545,15,7,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(546,15,11,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(547,15,12,'2026-09-03',2.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(548,15,13,'2026-09-03',2.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(549,15,14,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(550,15,15,'2026-09-03',2.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(551,15,18,'2026-09-03',2.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(552,15,19,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(553,15,20,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(554,15,23,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(555,15,25,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(556,15,141,'2026-09-03',5.00,'Tự động từ file: 0309.xls | Từ 5 phần [Wafu Pasta Heo Nướng Xốt Shoyu Butter]','2026-09-07 02:31:27',NULL),(557,15,16,'2026-09-03',2.00,'Tự động từ file: 0309.xls | Từ 1 phần [Matcha Latte Tây Bắc Mochi (Vừa)]; Bao gồm 1 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(558,15,105,'2026-09-03',1.00,'Tự động từ file: 0309.xls','2026-09-07 02:31:27',NULL),(559,18,6,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(560,18,11,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(561,18,13,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(562,18,14,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(563,18,15,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(564,18,16,'2026-09-04',3.00,'Tự động từ file: 0409.xls | Bao gồm 2 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(565,18,18,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(566,18,22,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(567,18,24,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(568,18,25,'2026-09-04',1.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(569,18,105,'2026-09-04',6.00,'Tự động từ file: 0409.xls','2026-09-07 02:31:27',NULL),(570,19,6,'2026-09-05',4.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(571,19,7,'2026-09-05',1.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(572,19,8,'2026-09-05',2.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(573,19,9,'2026-09-05',1.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(574,19,12,'2026-09-05',3.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(575,19,13,'2026-09-05',2.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(576,19,14,'2026-09-05',3.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(577,19,15,'2026-09-05',4.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(578,19,16,'2026-09-05',5.00,'Tự động từ file: 0509.xls | Bao gồm 2 từ món [Matcha Latte Tây Bắc Mochi (Vừa)]; Bao gồm 2 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(579,19,20,'2026-09-05',3.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(580,19,22,'2026-09-05',2.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(581,19,25,'2026-09-05',5.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(582,19,141,'2026-09-05',3.00,'Tự động từ file: 0509.xls | Từ 3 phần [Wafu Pasta Heo Nướng Xốt Shoyu Butter]','2026-09-07 02:31:27',NULL),(583,19,105,'2026-09-05',3.00,'Tự động từ file: 0509.xls','2026-09-07 02:31:27',NULL),(584,20,6,'2026-09-06',3.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(585,20,7,'2026-09-06',5.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(586,20,9,'2026-09-06',1.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(587,20,10,'2026-09-06',1.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(588,20,13,'2026-09-06',1.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(589,20,15,'2026-09-06',6.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(590,20,20,'2026-09-06',2.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(591,20,25,'2026-09-06',4.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL),(592,20,141,'2026-09-06',1.00,'Tự động từ file: 0609.xls | Từ 1 phần [Wafu Pasta Heo Nướng Xốt Shoyu Butter]','2026-09-07 02:31:27',NULL),(593,20,16,'2026-09-06',10.00,'Tự động từ file: 0609.xls | Từ 5 phần [Matcha Latte Tây Bắc Mochi (Vừa)]; Bao gồm 5 từ món [Matcha Latte Tây Bắc Mochi (Lớn)]','2026-09-07 02:31:27',NULL),(594,20,105,'2026-09-06',1.00,'Tự động từ file: 0609.xls','2026-09-07 02:31:27',NULL);
/*!40000 ALTER TABLE `so_ban` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ton_dau`
--

DROP TABLE IF EXISTS `ton_dau`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ton_dau` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `san_pham_id` int(11) NOT NULL,
  `ky_kiem_ke` varchar(7) NOT NULL COMMENT '?????nh d???ng YYYY-MM v?? d??? 2026-09',
  `so_luong` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ghi_chu` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sp_ky` (`san_pham_id`,`ky_kiem_ke`),
  CONSTRAINT `ton_dau_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=437 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ton_dau`
--

LOCK TABLES `ton_dau` WRITE;
/*!40000 ALTER TABLE `ton_dau` DISABLE KEYS */;
INSERT INTO `ton_dau` VALUES (1,1,'2026-09',100.00,'T???n ?????u k??? th??ng 9','2026-09-06 04:27:18',NULL),(2,2,'2026-09',50.00,'T???n ?????u k??? th??ng 9','2026-09-06 04:27:18',NULL),(3,3,'2026-09',80.00,'T???n ?????u k??? th??ng 9','2026-09-06 04:27:18','2026-09-07 01:35:39'),(4,141,'2026-09',65.00,'Tồn đầu kỳ khởi tạo ban đầu','2026-09-06 06:04:33','2026-09-07 01:35:39'),(5,6,'2026-09',16.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(6,7,'2026-09',4.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(7,8,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(8,9,'2026-09',2.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(9,10,'2026-09',12.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(10,11,'2026-09',4.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(11,12,'2026-09',11.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(12,13,'2026-09',34.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(13,14,'2026-09',18.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(14,15,'2026-09',41.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(15,16,'2026-09',45.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(16,17,'2026-09',52.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(17,18,'2026-09',47.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(18,19,'2026-09',48.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(19,20,'2026-09',69.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(20,21,'2026-09',60.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(21,22,'2026-09',75.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(22,23,'2026-09',52.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(23,24,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(24,25,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(25,26,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(26,69,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(27,70,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(29,105,'2026-09',1.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(30,27,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(31,28,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(32,29,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(33,30,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(34,31,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(35,32,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(36,33,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(37,34,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(38,35,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(39,36,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(40,37,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(41,38,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(42,39,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(43,40,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(44,41,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(45,42,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(46,43,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(47,44,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(48,45,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(49,46,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(50,47,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(51,48,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(52,49,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(53,50,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(54,51,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(55,52,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(56,53,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(57,54,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(58,144,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(59,145,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(60,146,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(61,142,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(62,143,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(63,148,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(64,149,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(65,147,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(66,150,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(67,151,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(68,55,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(69,56,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(70,57,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(71,58,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(72,59,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(73,60,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(74,61,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(75,62,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(76,63,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(77,64,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(78,65,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(79,66,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(80,67,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(81,68,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(82,71,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(83,72,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(84,73,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(85,74,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(86,75,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(87,76,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(88,77,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(89,78,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(90,79,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(92,80,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(93,81,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(94,82,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(95,83,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(96,84,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(97,85,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(98,86,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(99,87,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(100,88,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(101,89,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(102,90,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(103,91,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(104,92,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(105,93,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(106,94,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(107,95,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(108,96,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(109,97,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(110,98,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(111,99,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(112,100,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(113,101,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(114,102,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(115,103,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(116,104,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(117,106,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(118,107,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(119,108,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(120,109,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(121,110,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(122,111,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(123,112,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(124,113,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(125,114,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(126,115,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(127,116,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(128,117,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(129,118,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(130,119,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(131,120,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(132,121,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(133,122,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(134,123,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(135,124,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(136,125,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(137,126,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(138,127,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(139,128,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(140,129,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(141,130,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(142,131,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(143,132,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(144,133,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(145,134,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(146,135,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(147,136,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39'),(148,137,'2026-09',0.00,'','2026-09-07 01:28:05','2026-09-07 01:35:39');
/*!40000 ALTER TABLE `ton_dau` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upload_files`
--

DROP TABLE IF EXISTS `upload_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_file_goc` varchar(255) NOT NULL,
  `ten_file_luu` varchar(255) NOT NULL,
  `ngay_ban` date NOT NULL,
  `file_hash` varchar(64) NOT NULL COMMENT 'MD5/SHA256 ????? ch???ng upload tr??ng',
  `tong_dong` int(11) NOT NULL DEFAULT 0,
  `dong_kiem_ke` int(11) NOT NULL DEFAULT 0,
  `dong_bo_qua` int(11) NOT NULL DEFAULT 0,
  `nguoi_upload_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ngay_ban` (`ngay_ban`),
  KEY `idx_file_hash` (`file_hash`),
  KEY `nguoi_upload_id` (`nguoi_upload_id`),
  CONSTRAINT `upload_files_ibfk_1` FOREIGN KEY (`nguoi_upload_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upload_files`
--

LOCK TABLES `upload_files` WRITE;
/*!40000 ALTER TABLE `upload_files` DISABLE KEYS */;
INSERT INTO `upload_files` VALUES (13,'0109.xls','20260906_202203_0109.xls','2026-09-01','69d4de3b042a0009f0c973504c4d4ef4',68,16,53,1,'2026-09-07 01:22:03'),(14,'0209.xls','20260906_202222_0209.xls','2026-09-02','abca6f23b10395396ca30ab8d02613b2',77,16,61,1,'2026-09-07 01:22:22'),(15,'0309.xls','20260906_202240_0309.xls','2026-09-03','9e4aa51e7f958ccb4e2ad516b9020fde',55,15,42,1,'2026-09-07 01:22:40'),(18,'0409.xls','20260906_202348_0409.xls','2026-09-04','0f307ed92466e8fda33e7292eafb46b1',78,11,67,1,'2026-09-07 01:23:48'),(19,'0509.xls','20260906_202414_0509.xls','2026-09-05','740dd6001a9c94072517756317686c09',77,14,64,1,'2026-09-07 01:24:14'),(20,'0609.xls','20260906_202426_0609.xls','2026-09-06','a5c6673da6f95e4a1c5c33f95e62534c',73,11,64,1,'2026-09-07 01:24:26');
/*!40000 ALTER TABLE `upload_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `vai_tro` enum('admin','nhan_vien') NOT NULL DEFAULT 'admin',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Ho???t ?????ng, 0: Kh??a',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$XaHmGLIfEkkfSLzZbt7T0u/AYn0nUI7i/AI3Xo3ETiO/bEVe1ztYW','Quản Trị Viên','admin@quanlykho.vn','admin',1,'2026-09-06 04:27:18','2026-09-06 05:40:15');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07  2:48:52
