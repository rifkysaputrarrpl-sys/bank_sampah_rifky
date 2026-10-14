-- Jalankan sekali di phpMyAdmin (database bank_sampah_rifky)
CREATE TABLE `top_up` (
  `id_topup` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_topup`),
  KEY `fk_topup_user` (`id_user`),
  CONSTRAINT `fk_topup_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
