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
  `estado` enum('Presente','Tarde','Ausente','Justificado') COLLATE utf8mb4_general_ci DEFAULT 'Ausente',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_asistencia`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_horario` (`id_horario`),
  CONSTRAINT `tabla_asistencia_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tabla_usuario` (`id_usuario`),
  CONSTRAINT `tabla_asistencia_ibfk_2` FOREIGN KEY (`id_horario`) REFERENCES `tabla_horario` (`id_horario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_asistencia: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_dia_semana
CREATE TABLE IF NOT EXISTS `tabla_dia_semana` (
  `id_dia` int NOT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_dia`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_dia_semana: ~4 rows (aproximadamente)
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
  `id_usuario` int DEFAULT NULL,
  `id_dia` int NOT NULL,
  `hora_entrada` int NOT NULL,
  `minuto_entrada` int NOT NULL,
  `hora_salida` int NOT NULL,
  `minuto_salida` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_horario`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_dia` (`id_dia`),
  CONSTRAINT `tabla_horario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tabla_usuario` (`id_usuario`),
  CONSTRAINT `tabla_horario_ibfk_2` FOREIGN KEY (`id_dia`) REFERENCES `tabla_dia_semana` (`id_dia`),
  CONSTRAINT `tabla_horario_chk_1` CHECK ((`hora_entrada` between 0 and 23)),
  CONSTRAINT `tabla_horario_chk_2` CHECK ((`minuto_entrada` between 0 and 59)),
  CONSTRAINT `tabla_horario_chk_3` CHECK ((`hora_salida` between 0 and 23)),
  CONSTRAINT `tabla_horario_chk_4` CHECK ((`minuto_salida` between 0 and 59))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_horario: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_justificacion
CREATE TABLE IF NOT EXISTS `tabla_justificacion` (
  `id_justificacion` int NOT NULL AUTO_INCREMENT,
  `id_asistencia` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_justificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `aprobado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_justificacion`),
  KEY `id_asistencia` (`id_asistencia`),
  CONSTRAINT `tabla_justificacion_ibfk_1` FOREIGN KEY (`id_asistencia`) REFERENCES `tabla_asistencia` (`id_asistencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_justificacion: ~0 rows (aproximadamente)

-- Volcando estructura para tabla sistema_asistencia.tabla_rol
CREATE TABLE IF NOT EXISTS `tabla_rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
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
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `codigo_asistencia` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `codigo_asistencia` (`codigo_asistencia`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `tabla_usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `tabla_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla sistema_asistencia.tabla_usuario: ~3 rows (aproximadamente)
INSERT INTO `tabla_usuario` (`id_usuario`, `nombre`, `apellido`, `usuario`, `password`, `codigo_asistencia`, `id_rol`) VALUES
	(1, 'Juan', 'Pérez', 'juanperez', 'e10adc3949ba59abbe56e057f20f883e', 'USR-9709F6', 1),
	(2, 'María', 'Gómez', 'mariagomez', 'e10adc3949ba59abbe56e057f20f883e', 'USR-970A0D', 2),
	(3, 'Pedro', 'López', 'pedrolopez', 'e10adc3949ba59abbe56e057f20f883e', 'USR-970A1B', 1);

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
