INSERT INTO `serv28_kills_old`(`id_victim`, `faction_id_victim`, `armor_victim`, `id_killer`, `faction_id_killer`, `weapon_killer`, `date`) 
SELECT `id_victim`, `faction_id_victim`, `armor_victim`, `id_killer`, `faction_id_killer`, `weapon_killer`, `date` FROM `serv28_kills`;


CREATE TABLE `serv28_perks_old` (
  `id` int(11) NOT NULL,
  `pidlist` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;


INSERT INTO `serv28_perks_old`(`id`, `pidlist`) 
SELECT `id`, `pidlist` FROM `serv28_perks`;