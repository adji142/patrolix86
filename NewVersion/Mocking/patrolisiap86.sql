/*
 Navicat Premium Data Transfer

 Source Server         : AISServer
 Source Server Type    : MySQL
 Source Server Version : 100240
 Source Host           : localhost:3306
 Source Schema         : patrolisiap86

 Target Server Type    : MySQL
 Target Server Version : 100240
 File Encoding         : 65001

 Date: 02/05/2026 10:28:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for absensi
-- ----------------------------
DROP TABLE IF EXISTS `absensi`;
CREATE TABLE `absensi`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(11) NOT NULL,
  `KodeKaryawan` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `KoordinatIN` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ImageIN` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `KoordinatOUT` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ImageOUT` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `Tanggal` date NOT NULL,
  `Shift` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Checkin` datetime(6) NOT NULL,
  `CheckOut` datetime(6) NOT NULL,
  `CreatedOn` datetime(6) NOT NULL,
  `UpdatedOn` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20597 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dailyactivity
-- ----------------------------
DROP TABLE IF EXISTS `dailyactivity`;
CREATE TABLE `dailyactivity`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Tanggal` datetime(6) NOT NULL,
  `DeskripsiAktifitas` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Gambar1` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Gambar2` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Gambar3` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreatedOn` datetime(6) NULL DEFAULT NULL,
  `UpdatedOn` datetime(0) NULL DEFAULT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(11) NOT NULL,
  `KodeKaryawan` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaKaryawan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1771 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for guestlog
-- ----------------------------
DROP TABLE IF EXISTS `guestlog`;
CREATE TABLE `guestlog`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Tanggal` date NOT NULL,
  `TglMasuk` datetime(0) NOT NULL,
  `TglKeluar` datetime(0) NULL DEFAULT NULL,
  `ImageIn` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ImageOut` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(11) NULL DEFAULT NULL,
  `Keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreatedAt` datetime(6) NOT NULL,
  `LastUpdatedAt` datetime(6) NULL DEFAULT NULL,
  `NamaTamu` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaYangDicari` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Tujuan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 66 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for itemmovinghistory
-- ----------------------------
DROP TABLE IF EXISTS `itemmovinghistory`;
CREATE TABLE `itemmovinghistory`  (
  `KodeItem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `KodeGudang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TglPencatatan` datetime(0) NOT NULL,
  `BaseReff` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `BaseType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `QtyIN` double NOT NULL,
  `QtyOut` double NOT NULL,
  `RecordOwnerID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for itemwarehouse
-- ----------------------------
DROP TABLE IF EXISTS `itemwarehouse`;
CREATE TABLE `itemwarehouse`  (
  `KodeItem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `KodeGudang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Qty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `RecordOwnerID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for logdata
-- ----------------------------
DROP TABLE IF EXISTS `logdata`;
CREATE TABLE `logdata`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `LogDate` datetime(0) NOT NULL,
  `Event` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `IPAddress` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `retValue` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14277 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pascode
-- ----------------------------
DROP TABLE IF EXISTS `pascode`;
CREATE TABLE `pascode`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Passcode` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ValidTo` datetime(6) NOT NULL,
  `Status` int(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for patroli
-- ----------------------------
DROP TABLE IF EXISTS `patroli`;
CREATE TABLE `patroli`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `KodeCheckPoint` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(6) NOT NULL,
  `TanggalPatroli` datetime(6) NOT NULL,
  `KodeKaryawan` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Koordinat` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Image` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Catatan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Rank` int(6) NOT NULL,
  `Shift` int(11) NOT NULL,
  `ShiftJadwal` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 139048 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for permission
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permissionname` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `link` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ico` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `menusubmenu` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `multilevel` bit(1) NULL DEFAULT NULL,
  `separator` bit(1) NULL DEFAULT NULL,
  `order` int(255) NULL DEFAULT NULL,
  `status` bit(1) NULL DEFAULT NULL,
  `AllowMobile` bit(1) NULL DEFAULT NULL,
  `MobileRoute` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `MobileLogo` int(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for permissionrole
-- ----------------------------
DROP TABLE IF EXISTS `permissionrole`;
CREATE TABLE `permissionrole`  (
  `roleid` int(11) NOT NULL,
  `permissionid` int(11) NOT NULL
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pricingtable
-- ----------------------------
DROP TABLE IF EXISTS `pricingtable`;
CREATE TABLE `pricingtable`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `PricingName` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NormalPrice` decimal(10, 2) NOT NULL,
  `DiscPrice` decimal(10, 2) NOT NULL,
  `DiscPercent` decimal(10, 2) NOT NULL,
  `PriceTerm` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Status` int(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pricingterm
-- ----------------------------
DROP TABLE IF EXISTS `pricingterm`;
CREATE TABLE `pricingterm`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `PriceID` int(11) NOT NULL,
  `FiturDesc` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Allowed` int(1) NOT NULL COMMENT '1: Yes 0 :No',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for returpenjualandetail
-- ----------------------------
DROP TABLE IF EXISTS `returpenjualandetail`;
CREATE TABLE `returpenjualandetail`  (
  `NoTransaksi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `BaseReff` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoUrut` int(11) NOT NULL,
  `BaseLine` int(11) NOT NULL,
  `KodeItem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Qty` double NOT NULL,
  `Satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Harga` double NOT NULL,
  `HargaNet` double NOT NULL,
  `LineStatus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `KodeGudang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `RecordOwnerID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for returpenjualanheader
-- ----------------------------
DROP TABLE IF EXISTS `returpenjualanheader`;
CREATE TABLE `returpenjualanheader`  (
  `Periode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoTransaksi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TglTransaksi` date NOT NULL,
  `NoReff` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `KodeSupplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TotalTransaksi` double NOT NULL,
  `Status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Posted` int(11) NOT NULL,
  `CreatedBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `UpdatedBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `RecordOwnerID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sos
-- ----------------------------
DROP TABLE IF EXISTS `sos`;
CREATE TABLE `sos`  (
  `id` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(11) NOT NULL,
  `KodeKaryawan` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Comment` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Image1` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Image2` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Image3` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Koordinat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `SubmitDate` datetime(0) NOT NULL,
  `VoiceNote` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `isRead` int(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tcheckpoint
-- ----------------------------
DROP TABLE IF EXISTS `tcheckpoint`;
CREATE TABLE `tcheckpoint`  (
  `KodeCheckPoint` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaCheckPoint` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `LocationID` int(6) NOT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`KodeCheckPoint`, `RecordOwnerID`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tcompany
-- ----------------------------
DROP TABLE IF EXISTS `tcompany`;
CREATE TABLE `tcompany`  (
  `KodePartner` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaPartner` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `AlamatTagihan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NoTlp` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NoHP` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NIKPIC` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaPIC` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `CreatedOn` datetime(6) NOT NULL,
  `CreatedBy` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LastUpdatedOn` datetime(0) NULL DEFAULT NULL,
  `LastUpdatedBy` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tempStore` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `StartSubs` datetime(0) NULL DEFAULT NULL,
  `EndSubs` datetime(0) NULL DEFAULT NULL,
  `ExtraDays` int(1) NULL DEFAULT NULL,
  `AllowMobile` int(1) NULL DEFAULT NULL,
  `AllowDashboard` int(1) NULL DEFAULT NULL,
  `AllowFaceRecognition` int(1) NULL DEFAULT NULL,
  PRIMARY KEY (`KodePartner`) USING BTREE,
  INDEX `idxKodePartner`(`KodePartner`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tjadwal
-- ----------------------------
DROP TABLE IF EXISTS `tjadwal`;
CREATE TABLE `tjadwal`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Tanggal` date NOT NULL,
  `NIK` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Jadwal` int(6) NOT NULL,
  `StatusKehadiran` int(6) NULL DEFAULT NULL COMMENT '-1 : OFF, -2 : Izin, -3 : Alpha',
  `Keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `CreatedBy` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `CreatedOn` datetime(6) NOT NULL,
  `LastUpdatedBy` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `LastUpdatedOn` datetime(6) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14755 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tlokasipatroli
-- ----------------------------
DROP TABLE IF EXISTS `tlokasipatroli`;
CREATE TABLE `tlokasipatroli`  (
  `id` int(55) NOT NULL AUTO_INCREMENT,
  `NamaArea` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `AlamatArea` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Keterangan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `StartPatroli` time(6) NOT NULL,
  `IntervalPatroli` int(6) NOT NULL,
  `IntervalType` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `EndPatroli` time(6) NOT NULL,
  `Toleransi` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Latitude` decimal(10, 8) NULL DEFAULT NULL,
  `Longitude` decimal(11, 8) NULL DEFAULT NULL,
  `Radius` int(11) NULL DEFAULT 100,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idxArea`(`RecordOwnerID`, `id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 103 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tsecurity
-- ----------------------------
DROP TABLE IF EXISTS `tsecurity`;
CREATE TABLE `tsecurity`  (
  `NIK` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `NamaSecurity` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `JoinDate` date NOT NULL,
  `LocationID` int(11) NOT NULL,
  `Status` int(1) NOT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tempEncrypt` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Shift` int(11) NOT NULL,
  `Image` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`NIK`, `RecordOwnerID`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tshift
-- ----------------------------
DROP TABLE IF EXISTS `tshift`;
CREATE TABLE `tshift`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `NamaShift` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `MulaiBekerja` time(6) NOT NULL,
  `SelesaiBekerja` time(6) NOT NULL,
  `IntervalPatroli` int(6) NOT NULL,
  `IntervalType` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Toleransi` int(6) NOT NULL,
  `RecordOwnerID` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `LocationID` int(11) NOT NULL,
  `GantiHari` int(6) NOT NULL DEFAULT 0,
  `MulaiAbsen` time(6) NULL DEFAULT NULL,
  `MaxAbsen` time(6) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 600 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tstatuskehadiran
-- ----------------------------
DROP TABLE IF EXISTS `tstatuskehadiran`;
CREATE TABLE `tstatuskehadiran`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Nama` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for userrole
-- ----------------------------
DROP TABLE IF EXISTS `userrole`;
CREATE TABLE `userrole`  (
  `userid` int(11) NOT NULL,
  `roleid` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`userid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(75) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(75) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `createdby` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `createdon` datetime(0) NULL DEFAULT NULL,
  `HakAkses` int(255) NULL DEFAULT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `verified` bit(1) NULL DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `browser` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `RecordOwnerID` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `AreaUser` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `UserToken` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idUserID`(`id`, `username`, `RecordOwnerID`, `AreaUser`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 702 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Function structure for fn_createIDDayName
-- ----------------------------
DROP FUNCTION IF EXISTS `fn_createIDDayName`;
delimiter ;;
CREATE FUNCTION `fn_createIDDayName`(`Tanggal` DATE)
 RETURNS varchar(55) CHARSET latin1
BEGIN
	#Routine body goes here...
	SET @DayName = '';
	SET @idDayName = '';
	
	SELECT DAYNAME(Tanggal) INTO @DayName;
	
	IF @DayName = 'Sunday' THEN SET @idDayName= 'Minggu'; END IF;	
	IF @DayName = 'Monday' THEN SET @idDayName= 'Senin'; END IF;	
	IF @DayName = 'Tuesday' THEN SET @idDayName= 'Selasa'; END IF;	
	IF @DayName = 'Wednesday' THEN SET @idDayName= 'Rabu'; END IF;	
	IF @DayName = 'Thursday' THEN SET @idDayName= 'Kamis'; END IF;	
	IF @DayName = 'Friday' THEN SET @idDayName= 'Jumat'; END IF;	
	IF @DayName = 'Saturday' THEN SET @idDayName= 'Sabtu'; END IF;	
	RETURN @idDayName;
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for fn_ReadReview
-- ----------------------------
DROP PROCEDURE IF EXISTS `fn_ReadReview`;
delimiter ;;
CREATE PROCEDURE `fn_ReadReview`(IN `TglAwal` DATE, IN `TglAkhir` DATE, IN `RecordOwnerID` VARCHAR(55), IN `KodeKaryawan` VARCHAR(55), IN `LocationID` VARCHAR(55), IN `KodeShift` VARCHAR(55))
BEGIN
	DECLARE jamfix DATETIME;
	DECLARE cursor_ID INT;
	DECLARE cursor_location INT;
  DECLARE done INT ;
	DECLARE cursor_i CURSOR FOR SELECT x.id, x.LocationID FROM tshift x WHERE x.RecordOwnerID = RecordOwnerID AND (x.LocationID = LocationID OR LocationID = '');
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	DROP TEMPORARY TABLE IF EXISTS temp;
	CREATE TEMPORARY TABLE temp(Jadwal TIME, idx INT, shiftid INT, LocationID INT);
	
	SET @jmlLoop = 0;
	SET @intervaltime = 0;
	SET @Toleransi = 0;
	OPEN cursor_i;
	
	read_loop: LOOP
		FETCH cursor_i INTO cursor_ID, cursor_location;
			-- 			Done
			IF done THEN
				LEAVE read_loop;
			END IF;
			
			SELECT 
				TIMESTAMPDIFF(HOUR,CONCAT(DATE(NOW()), ' ', MulaiBekerja), CASE WHEN GantiHari = 1 THEN DATE_ADD(CONCAT(DATE(NOW()), ' ', SelesaiBekerja), INTERVAL 1 DAY) ELSE CONCAT(DATE(NOW()), ' ', SelesaiBekerja) END
	), 
				a.IntervalPatroli, 
				a.Toleransi,
				CONCAT(DATE(NOW()), ' ', MulaiBekerja)
			INTO @jmlLoop,@intervaltime, @Toleransi , jamfix
			FROM tshift a WHERE a.id = cursor_ID AND a.LocationID = cursor_location;
			
			SET @Counter = 0;
			
		-- 	SELECT @jmlLoop / @intervaltime, @Counter;
			WHILE @Counter < (@jmlLoop / @intervaltime) +1 DO
				INSERT INTO temp
				VALUES(TIME(jamfix), @Counter,cursor_ID,cursor_location);
				
				SELECT TIMESTAMPADD(HOUR,@intervaltime,jamfix) INTO jamfix;
				
				SET @Counter = @Counter + 1;
			END WHILE;
			
			
		END LOOP;
		CLOSE cursor_i;
	
		
		SELECT 
				a.id,
				c.NamaArea				AS NamaLokasi,
				d.NamaSecurity,
				b.NamaCheckPoint,
				a.Koordinat,
				a.Image,
				a.Catatan,
				DATE_FORMAT(a.TanggalPatroli,'%d/%m/%Y') TanggalPatroli,
				COALESCE(e.Jadwal,'00:00:00') JamJadwal,
				DATE_FORMAT(a.TanggalPatroli,'%H:%i:%S') JamAktual,
				CONCAT('00:',COALESCE(f.Toleransi,'00'),':00') Toleransi,
				a.Shift,
				f.NamaShift,
				f.GantiHari
			FROM patroli a
			LEFT JOIN tcheckpoint b ON a.KodeCheckPoint = b.KodeCheckPoint AND a.RecordOwnerID = b.RecordOwnerID AND a.LocationID = b.LocationID
			LEFT JOIN tlokasipatroli c ON a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
			LEFT JOIN tsecurity d ON a.KodeKaryawan = d.NIK = a.RecordOwnerID = d.RecordOwnerID
			LEFT JOIN temp e ON a.Rank = e.idx AND a.Shift = e.shiftid AND a.LocationID = e.LocationID
			LEFT JOIN tshift f ON a.Shift = f.id AND a.RecordOwnerID = f.RecordOwnerID
			WHERE DATE(a.TanggalPatroli) BETWEEN TglAwal AND TglAkhir
			AND a.RecordOwnerID = RecordOwnerID
			AND (a.LocationID = LocationID OR LocationID = '')
			AND (a.KodeKaryawan = KodeKaryawan OR KodeKaryawan = '')
AND (a.Shift = KodeShift or KodeShift = -1)
			ORDER BY a.TanggalPatroli DESC;
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for updateshift
-- ----------------------------
DROP PROCEDURE IF EXISTS `updateshift`;
delimiter ;;
CREATE PROCEDURE `updateshift`()
UPDATE patroli p
JOIN (
    SELECT 
        a.id,
        (SELECT x.id 
         FROM tshift x 
         WHERE x.RecordOwnerID = a.RecordOwnerID 
         AND x.LocationID = a.LocationID 
         AND a.TanggalPatroli BETWEEN 
             CAST(CONCAT(CAST(a.TanggalPatroli AS DATE), ' ', x.MulaiBekerja) AS DATETIME) 
             AND CAST(CONCAT(CASE 
                 WHEN x.GantiHari = 1 THEN DATE_ADD(CAST(a.TanggalPatroli AS DATE), INTERVAL 1 DAY) 
                 ELSE CAST(a.TanggalPatroli AS DATE) 
             END,  ' ', x.SelesaiBekerja) AS DATETIME) 
         ORDER BY x.MulaiBekerja 
         LIMIT 1) AS shiftid
    FROM patroli a
    WHERE a.RecordOwnerID = 'CL0009'
) AS subquery
ON p.id = subquery.id
SET p.Shift = subquery.shiftid
WHERE p.RecordOwnerID = 'CL0009'
;;
delimiter ;

-- ----------------------------
-- Event structure for autoupdatelocation
-- ----------------------------
DROP EVENT IF EXISTS `autoupdatelocation`;
delimiter ;;
CREATE EVENT `autoupdatelocation`
ON SCHEDULE
EVERY '1' DAY STARTS '2024-03-08 22:08:38' ENDS '2029-03-08 22:08:38'
DO UPDATE absensi a 
LEFT JOIN tlokasipatroli b on a.LocationID = b.id
LEFT JOIN tsecurity c on a.KodeKaryawan = c.NIK
SET a.LocationID = c.LocationID
where b.id IS NULL
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table tcompany
-- ----------------------------
DROP TRIGGER IF EXISTS `trg_addUser`;
delimiter ;;
CREATE TRIGGER `trg_addUser` AFTER INSERT ON `tcompany` FOR EACH ROW BEGIN
	INSERT into users(username, nama, `password`, CreatedBy, CreatedOn,verified,RecordOwnerID,AreaUser)
	VALUE
	('manager',NEW.NamaPartner,NEW.tempStore,'AUTO',NOW(),0,NEW.KodePartner,'');
	INSERT into userrole
	SELECT id, 1 FROM users where RecordOwnerID = NEW.KodePartner;
END
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table tsecurity
-- ----------------------------
DROP TRIGGER IF EXISTS `trgAddUser`;
delimiter ;;
CREATE TRIGGER `trgAddUser` AFTER INSERT ON `tsecurity` FOR EACH ROW BEGIN
	INSERT into users(username, nama, `password`, CreatedBy, CreatedOn,verified,RecordOwnerID, AreaUser)
	VALUE
	(NEW.NIK,NEW.NamaSecurity,NEW.tempEncrypt,'AUTO',NOW(),0, NEW.RecordOwnerID, NEW.LocationID);
	INSERT into userrole
	SELECT id, 3 FROM users where username = NEW.NIK;
END
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table tsecurity
-- ----------------------------
DROP TRIGGER IF EXISTS `trgDeleteUser`;
delimiter ;;
CREATE TRIGGER `trgDeleteUser` AFTER DELETE ON `tsecurity` FOR EACH ROW BEGIN
	DELETE FROM users WHERE username = OLD.NIK;
END
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table users
-- ----------------------------
DROP TRIGGER IF EXISTS `trgDelete`;
delimiter ;;
CREATE TRIGGER `trgDelete` AFTER DELETE ON `users` FOR EACH ROW DELETE FROM userrole where userid = old.id
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
