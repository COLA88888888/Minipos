-- ==========================================================
-- ເພີ່ມຂໍ້ມູນ Admin ເຂົ້າໃນຕາຕະລາງ tbuser (ຖານຂໍ້ມູນ minipos)
-- ==========================================================
USE `minipos`;

-- ຕັ້ງຄ່າ Id ໃຫ້ເປັນ AUTO_INCREMENT
ALTER TABLE `tbuser` MODIFY `Id` INT(11) NOT NULL AUTO_INCREMENT;

-- ລຶບຂໍ້ມູນເກົ່າຖ້າມີຊໍ້າ
DELETE FROM `tbuser` WHERE `username` = 'admin';

-- ເພີ່ມຜູ້ໃຊ້ admin ພ້ອມສິດທັງໝົດ ແລະ ລະຫັດຜ່ານ 123456 (SHA-256)
INSERT INTO `tbuser` (
  `Id`,
  `user_code`,
  `username`,
  `password`,
  `status`,
  `sale`,
  `cafe`,
  `order`,
  `kitchen`,
  `tbl`,
  `report`,
  `stock`,
  `setup`,
  `users`,
  `edit`,
  `store_id`
) VALUES (
  1,
  '001',
  'admin',
  SHA2('123456', 256),
  'Admin',
  1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
  1
);
