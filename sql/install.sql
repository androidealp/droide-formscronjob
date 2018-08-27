CREATE TABLE IF NOT EXISTS `#__droide_forms_cronjob` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `form_id` VARCHAR(50) NOT NULL,
  `id_register` int NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB