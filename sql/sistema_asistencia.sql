-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para sistema_asistencia
CREATE DATABASE IF NOT EXISTS `sistema_asistencia` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sistema_asistencia`;

-- Volcando estructura para función sistema_asistencia.generar_codigo_asistencia
DELIMITER //
CREATE FUNCTION `generar_codigo_asistencia`() RETURNS varchar(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
    DETERMINISTIC
BEGIN
    DECLARE codigo VARCHAR(20);
    SET codigo = CONCAT('USR-', UPPER(LEFT(UUID(), 6)));
    RETURN codigo;
END//
DELIMITER ;

-- Volcando estructura para tabla sistema_asistencia.tabla_asistencia
CREATE TABLE IF NOT EXISTS `tabla_asistencia` (
  `id_asistencia` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_horario` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_entrada_real` time DEFAULT NULL,
  `hora_salida_real` time DEFAULT NULL,
  `estado` enum('Asistió','Faltó','Justificado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Faltó',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `unique_usuario_fecha` (`id_usuario`,`fecha`),
  KEY `id_horario` (`id_horario`),
  CONSTRAINT `tabla_asistencia_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tabla_usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `tabla_asistencia_ibfk_2` FOREIGN KEY (`id_horario`) REFERENCES `tabla_horario` (`id_horario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_asistencia: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_dia_semana
CREATE TABLE IF NOT EXISTS `tabla_dia_semana` (
  `id_dia` int NOT NULL,
  `nombre` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_dia`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_dia_semana: ~7 rows (aproximadamente)
INSERT INTO `tabla_dia_semana` (`id_dia`, `nombre`) VALUES
	(7, 'Domingo'),
	(4, 'Jueves'),
	(1, 'Lunes'),
	(2, 'Martes'),
	(3, 'Miércoles'),
	(6, 'Sábado'),
	(5, 'Viernes');

-- Volcando estructura para tabla sistema_asistencia.tabla_horario
CREATE TABLE IF NOT EXISTS `tabla_horario` (
  `id_horario` int NOT NULL AUTO_INCREMENT,
  `id_dia` int NOT NULL,
  `hora_entrada` int NOT NULL,
  `minuto_entrada` int NOT NULL,
  `hora_salida` int NOT NULL,
  `minuto_salida` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `entrada_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salida_am_pm` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_horario`),
  KEY `id_dia` (`id_dia`),
  CONSTRAINT `tabla_horario_ibfk_2` FOREIGN KEY (`id_dia`) REFERENCES `tabla_dia_semana` (`id_dia`),
  CONSTRAINT `tabla_horario_chk_1` CHECK ((`hora_entrada` between 0 and 23)),
  CONSTRAINT `tabla_horario_chk_2` CHECK ((`minuto_entrada` between 0 and 59)),
  CONSTRAINT `tabla_horario_chk_3` CHECK ((`hora_salida` between 0 and 23)),
  CONSTRAINT `tabla_horario_chk_4` CHECK ((`minuto_salida` between 0 and 59))
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_horario: ~7 rows (aproximadamente)
INSERT INTO `tabla_horario` (`id_horario`, `id_dia`, `hora_entrada`, `minuto_entrada`, `hora_salida`, `minuto_salida`, `fecha_creacion`, `entrada_am_pm`, `salida_am_pm`) VALUES
	(55, 1, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM'),
	(56, 2, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM'),
	(57, 3, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM'),
	(58, 4, 4, 10, 7, 30, '2025-10-23 21:16:05', 'PM', 'PM'),
	(59, 5, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM'),
	(60, 6, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM'),
	(61, 7, 7, 30, 1, 30, '2025-10-23 21:16:05', 'AM', 'PM');

-- Volcando estructura para tabla sistema_asistencia.tabla_justificacion
CREATE TABLE IF NOT EXISTS `tabla_justificacion` (
  `id_justificacion` int NOT NULL AUTO_INCREMENT,
  `id_asistencia` int NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_justificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `aprobado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_justificacion`),
  KEY `id_asistencia` (`id_asistencia`),
  CONSTRAINT `tabla_justificacion_ibfk_1` FOREIGN KEY (`id_asistencia`) REFERENCES `tabla_asistencia` (`id_asistencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_justificacion: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_materiales
CREATE TABLE IF NOT EXISTS `tabla_materiales` (
  `id_material` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_material`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `tabla_materiales_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tabla_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_materiales: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_rol
CREATE TABLE IF NOT EXISTS `tabla_rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_rol: ~2 rows (aproximadamente)
INSERT INTO `tabla_rol` (`id_rol`, `nombre`) VALUES
	(1, 'Administrador'),
	(2, 'Profesor');

-- Volcando estructura para tabla sistema_asistencia.tabla_usuario
CREATE TABLE IF NOT EXISTS `tabla_usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `aula` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo_asistencia` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `codigo_asistencia` (`codigo_asistencia`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `tabla_usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `tabla_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_usuario: ~2 rows (aproximadamente)
INSERT INTO `tabla_usuario` (`id_usuario`, `nombre`, `apellido`, `usuario`, `password`, `aula`, `codigo_asistencia`, `id_rol`) VALUES
	(9, 'Profesor', 'Profesor', 'profesor', '793741d54b00253006453742ad4ed534', NULL, 'USR-CB6DCC', 2),
	(14, 'Admin', 'Admin', 'admin', '1844156d4166d94387f1a4ad031ca5fa', NULL, 'USR-AA6D0B', 1);

-- Volcando estructura para disparador sistema_asistencia.before_insert_usuario
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `before_insert_usuario` BEFORE INSERT ON `tabla_usuario` FOR EACH ROW BEGIN
    IF NEW.codigo_asistencia IS NULL THEN
        SET NEW.codigo_asistencia = generar_codigo_asistencia();
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
