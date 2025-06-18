-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para gameon
CREATE DATABASE IF NOT EXISTS `gameon` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `gameon`;

-- Volcando estructura para tabla gameon.amistades
CREATE TABLE IF NOT EXISTS `amistades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_solicitante_id` int(11) NOT NULL,
  `usuario_receptor_id` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amistad_unica` (`usuario_solicitante_id`,`usuario_receptor_id`),
  KEY `usuario_receptor_id` (`usuario_receptor_id`),
  CONSTRAINT `amistades_ibfk_1` FOREIGN KEY (`usuario_solicitante_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `amistades_ibfk_2` FOREIGN KEY (`usuario_receptor_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.amistades: ~2 rows (aproximadamente)
INSERT INTO `amistades` (`id`, `usuario_solicitante_id`, `usuario_receptor_id`, `estado`, `fecha_solicitud`, `fecha_respuesta`) VALUES
	(1, 2, 3, 'aceptada', '2025-06-02 19:24:11', '2025-06-02 19:24:13'),
	(3, 4, 2, 'aceptada', '2025-06-02 20:01:28', '2025-06-02 20:03:18');

-- Volcando estructura para tabla gameon.areas_deportivas
CREATE TABLE IF NOT EXISTS `areas_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  `nombre_area` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad_jugadores` int(11) DEFAULT NULL,
  `tarifa_por_hora` decimal(10,2) NOT NULL,
  `estado` enum('activa','mantenimiento','inactiva') DEFAULT 'activa',
  `imagen_area` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `institucion_deportiva_id` (`institucion_deportiva_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `areas_deportivas_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `areas_deportivas_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.areas_deportivas: ~13 rows (aproximadamente)
INSERT INTO `areas_deportivas` (`id`, `institucion_deportiva_id`, `deporte_id`, `nombre_area`, `descripcion`, `capacidad_jugadores`, `tarifa_por_hora`, `estado`, `imagen_area`, `creado_en`) VALUES
	(16, 1, 1, 'Cancha de Fútbol Principal', 'Cancha de césped sintético con iluminación LED completa', 22, 83.00, 'activa', 'https://i.ibb.co/JWPC8xCW/cancha1.jpg', '2025-06-16 21:44:21'),
	(17, 1, 1, 'Cancha de Fútbol Secundaria', 'Cancha de césped natural para entrenamientos', 22, 60.00, 'activa', 'https://i.ibb.co/Y7rqxcQ1/ga1.jpg', '2025-06-16 21:44:21'),
	(18, 1, 2, 'Cancha de Vóley A', 'Cancha de vóley techada con piso de parquet', 12, 50.00, 'activa', 'https://i.ibb.co/kVqBF060/volay1.jpg', '2025-06-16 21:44:21'),
	(19, 1, 2, 'Cancha de Vóley B', 'Cancha de vóley al aire libre', 12, 40.00, 'activa', NULL, '2025-06-16 21:44:21'),
	(20, 1, 3, 'Cancha de Básquet Techada', 'Cancha de básquet con tableros oficiales', 10, 55.00, 'activa', NULL, '2025-06-16 21:44:21'),
	(21, 2, 1, 'Campo de Fútbol Los Andes', 'Campo de fútbol con césped natural y graderías', 22, 70.00, 'activa', NULL, '2025-06-16 21:44:51'),
	(22, 2, 3, 'Cancha de Básquet Principal', 'Cancha de básquet techada con marcador electrónico', 10, 60.00, 'activa', NULL, '2025-06-16 21:44:51'),
	(23, 2, 3, 'Cancha de Básquet Entrenamiento', 'Cancha auxiliar para entrenamientos', 10, 45.00, 'activa', NULL, '2025-06-16 21:44:51'),
	(24, 2, 2, 'Cancha de Vóley Los Andes', 'Cancha de vóley con piso de cemento pulido', 12, 35.00, 'activa', NULL, '2025-06-16 21:44:51'),
	(25, 3, 1, 'Campo Principal del Estadio', 'Campo reglamentario con césped natural y capacidad para 5000 espectadores', 22, 120.00, 'activa', 'https://i.ibb.co/1f4vkrHz/fe.jpg', '2025-06-16 21:44:51'),
	(26, 3, 2, 'Cancha de Vóley Municipal', 'Cancha oficial para torneos municipales', 12, 45.00, 'activa', 'https://i.ibb.co/fz9YKBbK/fefefe.jpg', '2025-06-16 21:44:51'),
	(27, 3, 3, 'Cancha de Básquet Municipal', 'Cancha de básquet para eventos municipales', 10, 50.00, 'activa', 'https://i.ibb.co/sdD20DRS/rera.jpg', '2025-06-16 21:44:51'),
	(28, 3, 1, 'Campo de Entrenamiento', 'Campo auxiliar para entrenamientos y partidos menores', 22, 90.00, 'mantenimiento', 'https://i.ibb.co/C5mV0P8c/gaga.jpg', '2025-06-16 21:44:51');

-- Volcando estructura para tabla gameon.areas_horarios
CREATE TABLE IF NOT EXISTS `areas_horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_deportiva_id` int(11) NOT NULL,
  `dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `area_deportiva_id` (`area_deportiva_id`),
  CONSTRAINT `areas_horarios_ibfk_1` FOREIGN KEY (`area_deportiva_id`) REFERENCES `areas_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.areas_horarios: ~91 rows (aproximadamente)
INSERT INTO `areas_horarios` (`id`, `area_deportiva_id`, `dia`, `hora_apertura`, `hora_cierre`, `disponible`) VALUES
	(1, 16, 'Lunes', '06:00:00', '22:00:00', 1),
	(2, 16, 'Martes', '06:00:00', '22:00:00', 1),
	(3, 16, 'Miercoles', '06:00:00', '22:00:00', 1),
	(4, 16, 'Jueves', '06:00:00', '22:00:00', 1),
	(5, 16, 'Viernes', '06:00:00', '23:00:00', 1),
	(6, 16, 'Sabado', '07:00:00', '23:00:00', 1),
	(7, 16, 'Domingo', '08:00:00', '20:00:00', 1),
	(8, 17, 'Lunes', '07:00:00', '21:00:00', 1),
	(9, 17, 'Martes', '07:00:00', '21:00:00', 1),
	(10, 17, 'Miercoles', '07:00:00', '21:00:00', 1),
	(11, 17, 'Jueves', '07:00:00', '21:00:00', 1),
	(12, 17, 'Viernes', '07:00:00', '22:00:00', 1),
	(13, 17, 'Sabado', '08:00:00', '22:00:00', 1),
	(14, 17, 'Domingo', '09:00:00', '19:00:00', 1),
	(15, 18, 'Lunes', '07:00:00', '21:00:00', 1),
	(16, 18, 'Martes', '07:00:00', '21:00:00', 1),
	(17, 18, 'Miercoles', '07:00:00', '21:00:00', 1),
	(18, 18, 'Jueves', '07:00:00', '21:00:00', 1),
	(19, 18, 'Viernes', '07:00:00', '22:00:00', 1),
	(20, 18, 'Sabado', '08:00:00', '22:00:00', 1),
	(21, 18, 'Domingo', '09:00:00', '19:00:00', 1),
	(22, 19, 'Lunes', '08:00:00', '20:00:00', 1),
	(23, 19, 'Martes', '08:00:00', '20:00:00', 1),
	(24, 19, 'Miercoles', '08:00:00', '20:00:00', 1),
	(25, 19, 'Jueves', '08:00:00', '20:00:00', 1),
	(26, 19, 'Viernes', '08:00:00', '21:00:00', 1),
	(27, 19, 'Sabado', '09:00:00', '21:00:00', 1),
	(28, 19, 'Domingo', '10:00:00', '18:00:00', 1),
	(29, 20, 'Lunes', '07:00:00', '21:00:00', 1),
	(30, 20, 'Martes', '07:00:00', '21:00:00', 1),
	(31, 20, 'Miercoles', '07:00:00', '21:00:00', 1),
	(32, 20, 'Jueves', '07:00:00', '21:00:00', 1),
	(33, 20, 'Viernes', '07:00:00', '22:00:00', 1),
	(34, 20, 'Sabado', '08:00:00', '22:00:00', 1),
	(35, 20, 'Domingo', '09:00:00', '19:00:00', 1),
	(36, 21, 'Lunes', '06:00:00', '21:00:00', 1),
	(37, 21, 'Martes', '06:00:00', '21:00:00', 1),
	(38, 21, 'Miercoles', '06:00:00', '21:00:00', 1),
	(39, 21, 'Jueves', '06:00:00', '21:00:00', 1),
	(40, 21, 'Viernes', '06:00:00', '22:00:00', 1),
	(41, 21, 'Sabado', '07:00:00', '22:00:00', 1),
	(42, 21, 'Domingo', '08:00:00', '20:00:00', 1),
	(43, 22, 'Lunes', '07:00:00', '21:00:00', 1),
	(44, 22, 'Martes', '07:00:00', '21:00:00', 1),
	(45, 22, 'Miercoles', '07:00:00', '21:00:00', 1),
	(46, 22, 'Jueves', '07:00:00', '21:00:00', 1),
	(47, 22, 'Viernes', '07:00:00', '22:00:00', 1),
	(48, 22, 'Sabado', '08:00:00', '22:00:00', 1),
	(49, 22, 'Domingo', '09:00:00', '19:00:00', 1),
	(50, 23, 'Lunes', '08:00:00', '20:00:00', 1),
	(51, 23, 'Martes', '08:00:00', '20:00:00', 1),
	(52, 23, 'Miercoles', '08:00:00', '20:00:00', 1),
	(53, 23, 'Jueves', '08:00:00', '20:00:00', 1),
	(54, 23, 'Viernes', '08:00:00', '21:00:00', 1),
	(55, 23, 'Sabado', '09:00:00', '21:00:00', 1),
	(56, 23, 'Domingo', '10:00:00', '18:00:00', 1),
	(57, 24, 'Lunes', '07:00:00', '20:00:00', 1),
	(58, 24, 'Martes', '07:00:00', '20:00:00', 1),
	(59, 24, 'Miercoles', '07:00:00', '20:00:00', 1),
	(60, 24, 'Jueves', '07:00:00', '20:00:00', 1),
	(61, 24, 'Viernes', '07:00:00', '21:00:00', 1),
	(62, 24, 'Sabado', '08:00:00', '21:00:00', 1),
	(63, 24, 'Domingo', '09:00:00', '19:00:00', 1),
	(64, 25, 'Lunes', '08:00:00', '18:00:00', 1),
	(65, 25, 'Martes', '08:00:00', '18:00:00', 1),
	(66, 25, 'Miercoles', '08:00:00', '18:00:00', 1),
	(67, 25, 'Jueves', '08:00:00', '18:00:00', 1),
	(68, 25, 'Viernes', '08:00:00', '20:00:00', 1),
	(69, 25, 'Sabado', '09:00:00', '21:00:00', 1),
	(70, 25, 'Domingo', '10:00:00', '18:00:00', 1),
	(71, 26, 'Lunes', '07:00:00', '20:00:00', 1),
	(72, 26, 'Martes', '07:00:00', '20:00:00', 1),
	(73, 26, 'Miercoles', '07:00:00', '20:00:00', 1),
	(74, 26, 'Jueves', '07:00:00', '20:00:00', 1),
	(75, 26, 'Viernes', '07:00:00', '21:00:00', 1),
	(76, 26, 'Sabado', '08:00:00', '21:00:00', 1),
	(77, 26, 'Domingo', '09:00:00', '19:00:00', 1),
	(85, 28, 'Lunes', '00:00:00', '00:00:00', 0),
	(86, 28, 'Martes', '00:00:00', '00:00:00', 0),
	(87, 28, 'Miercoles', '00:00:00', '00:00:00', 0),
	(88, 28, 'Jueves', '00:00:00', '00:00:00', 0),
	(89, 28, 'Viernes', '00:00:00', '00:00:00', 0),
	(90, 28, 'Sabado', '00:00:00', '00:00:00', 0),
	(91, 28, 'Domingo', '00:00:00', '00:00:00', 0),
	(92, 27, 'Lunes', '08:00:00', '20:00:00', 1),
	(93, 27, 'Martes', '07:00:00', '20:00:00', 1),
	(94, 27, 'Miercoles', '07:00:00', '20:00:00', 1),
	(95, 27, 'Jueves', '07:00:00', '20:00:00', 1),
	(96, 27, 'Viernes', '07:00:00', '21:00:00', 1),
	(97, 27, 'Sabado', '08:00:00', '21:00:00', 1),
	(98, 27, 'Domingo', '09:00:00', '19:00:00', 1);

-- Volcando estructura para tabla gameon.deportes
CREATE TABLE IF NOT EXISTS `deportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.deportes: ~3 rows (aproximadamente)
INSERT INTO `deportes` (`id`, `nombre`) VALUES
	(1, 'futbol'),
	(2, 'voley'),
	(3, 'basquet');

-- Volcando estructura para tabla gameon.equipos
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `deporte_id` int(11) NOT NULL,
  `creador_id` int(11) NOT NULL,
  `limite_miembros` int(11) NOT NULL DEFAULT 10,
  `privado` tinyint(1) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `deporte_id` (`deporte_id`),
  KEY `creador_id` (`creador_id`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipos_ibfk_2` FOREIGN KEY (`creador_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.equipos: ~5 rows (aproximadamente)
INSERT INTO `equipos` (`id`, `nombre`, `descripcion`, `deporte_id`, `creador_id`, `limite_miembros`, `privado`, `estado`, `creado_en`) VALUES
	(1, 'FC Vodka Juniors', 'La nueva generación de apasionados por el deporte y la chela', 1, 2, 10, 1, 1, '2025-06-02 19:38:20'),
	(2, 'Las Mariposas', 'Somos mariposas en la cancha de voley xd', 2, 2, 10, 1, 1, '2025-06-02 21:34:35'),
	(3, 'Los Negros James', 'Somos los mas negros de todo Tacna, tiemblen ante nuestro Bascket de negros', 3, 2, 10, 1, 1, '2025-06-03 21:53:45'),
	(4, 'Los cojos de EPIS', 'Somos mariposas', 1, 2, 10, 0, 1, '2025-06-06 00:03:00'),
	(5, 'mongosdeverdad', 'dffdfdf', 1, 2, 10, 0, 1, '2025-06-06 00:21:20');

-- Volcando estructura para tabla gameon.equipo_miembros
CREATE TABLE IF NOT EXISTS `equipo_miembros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `rol` enum('creador','administrador','miembro') NOT NULL DEFAULT 'miembro',
  `fecha_union` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipo_usuario_unico` (`equipo_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `equipo_miembros_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipo_miembros_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.equipo_miembros: ~12 rows (aproximadamente)
INSERT INTO `equipo_miembros` (`id`, `equipo_id`, `usuario_id`, `rol`, `fecha_union`) VALUES
	(1, 1, 2, 'creador', '2025-06-02 19:38:20'),
	(2, 2, 2, 'creador', '2025-06-02 21:34:35'),
	(3, 1, 3, 'miembro', '2025-06-03 21:31:29'),
	(4, 2, 4, 'miembro', '2025-06-03 21:31:35'),
	(5, 1, 4, 'miembro', '2025-06-03 21:33:52'),
	(6, 3, 2, 'creador', '2025-06-03 21:53:45'),
	(7, 3, 3, 'miembro', '2025-06-04 21:32:01'),
	(8, 4, 2, 'creador', '2025-06-06 00:03:00'),
	(9, 4, 4, 'miembro', '2025-06-06 00:03:24'),
	(10, 5, 2, 'creador', '2025-06-06 00:21:20'),
	(11, 5, 3, 'miembro', '2025-06-06 00:21:42'),
	(12, 5, 4, 'miembro', '2025-06-06 00:21:53');

-- Volcando estructura para tabla gameon.horarios_atencion
CREATE TABLE IF NOT EXISTS `horarios_atencion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `dia` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `institucion_deportiva_id` (`institucion_deportiva_id`),
  CONSTRAINT `horarios_atencion_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.horarios_atencion: ~21 rows (aproximadamente)
INSERT INTO `horarios_atencion` (`id`, `institucion_deportiva_id`, `dia`, `hora_apertura`, `hora_cierre`) VALUES
	(1, 1, 'Lunes', '07:00:00', '22:00:00'),
	(2, 1, 'Martes', '07:00:00', '22:00:00'),
	(3, 1, 'Miercoles', '07:00:00', '22:00:00'),
	(4, 1, 'Jueves', '07:00:00', '22:00:00'),
	(5, 1, 'Viernes', '07:00:00', '23:00:00'),
	(6, 1, 'Sabado', '08:00:00', '23:00:00'),
	(7, 1, 'Domingo', '08:00:00', '20:00:00'),
	(8, 2, 'Lunes', '06:00:00', '21:00:00'),
	(9, 2, 'Martes', '06:00:00', '21:00:00'),
	(10, 2, 'Miercoles', '06:00:00', '21:00:00'),
	(11, 2, 'Jueves', '06:00:00', '21:00:00'),
	(12, 2, 'Viernes', '06:00:00', '21:00:00'),
	(13, 2, 'Sabado', '08:00:00', '22:00:00'),
	(14, 2, 'Domingo', '08:00:00', '19:00:00'),
	(15, 3, 'Lunes', '08:00:00', '22:00:00'),
	(16, 3, 'Martes', '08:00:00', '22:00:00'),
	(17, 3, 'Miercoles', '08:00:00', '22:00:00'),
	(18, 3, 'Jueves', '08:00:00', '22:00:00'),
	(19, 3, 'Viernes', '08:00:00', '23:30:00'),
	(20, 3, 'Sabado', '09:00:00', '23:30:00'),
	(21, 3, 'Domingo', '09:00:00', '21:00:00');

-- Volcando estructura para tabla gameon.instalaciones_ocupaciones
CREATE TABLE IF NOT EXISTS `instalaciones_ocupaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institucion_deportiva_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `tipo_ocupacion` enum('reserva_individual','partido_equipos','torneo','mantenimiento','evento_especial') NOT NULL,
  `estado` enum('reservado','confirmado','en_curso','finalizado','cancelado') DEFAULT 'reservado',
  `usuario_reserva_id` int(11) DEFAULT NULL,
  `equipo_local_id` int(11) DEFAULT NULL,
  `equipo_visitante_id` int(11) DEFAULT NULL,
  `torneo_partido_id` int(11) DEFAULT NULL,
  `torneo_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `creado_por_usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_institucion_fecha` (`institucion_deportiva_id`,`fecha`),
  KEY `idx_fecha_hora` (`fecha`,`hora_inicio`,`hora_fin`),
  KEY `idx_tipo_estado` (`tipo_ocupacion`,`estado`),
  KEY `ocupaciones_ibfk_2` (`usuario_reserva_id`),
  KEY `ocupaciones_ibfk_3` (`equipo_local_id`),
  KEY `ocupaciones_ibfk_4` (`equipo_visitante_id`),
  KEY `ocupaciones_ibfk_5` (`torneo_partido_id`),
  KEY `ocupaciones_ibfk_6` (`torneo_id`),
  KEY `ocupaciones_ibfk_7` (`creado_por_usuario_id`),
  CONSTRAINT `ocupaciones_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ocupaciones_ibfk_2` FOREIGN KEY (`usuario_reserva_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_3` FOREIGN KEY (`equipo_local_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_4` FOREIGN KEY (`equipo_visitante_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_5` FOREIGN KEY (`torneo_partido_id`) REFERENCES `torneos_partidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_6` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ocupaciones_ibfk_7` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios_deportistas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instalaciones_ocupaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.instituciones_deportes
CREATE TABLE IF NOT EXISTS `instituciones_deportes` (
  `institucion_deportiva_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`institucion_deportiva_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `instituciones_deportes_ibfk_1` FOREIGN KEY (`institucion_deportiva_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `instituciones_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instituciones_deportes: ~9 rows (aproximadamente)
INSERT INTO `instituciones_deportes` (`institucion_deportiva_id`, `deporte_id`) VALUES
	(1, 1),
	(2, 1),
	(2, 2),
	(2, 3),
	(3, 1),
	(3, 3),
	(4, 1),
	(4, 2),
	(4, 3);

-- Volcando estructura para tabla gameon.instituciones_deportivas
CREATE TABLE IF NOT EXISTS `instituciones_deportivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_instalacion_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `tarifa` decimal(10,2) NOT NULL,
  `calificacion` decimal(3,2) DEFAULT 0.00,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_instalacion_id` (`usuario_instalacion_id`),
  CONSTRAINT `instituciones_deportivas_ibfk_1` FOREIGN KEY (`usuario_instalacion_id`) REFERENCES `usuarios_instalaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.instituciones_deportivas: ~4 rows (aproximadamente)
INSERT INTO `instituciones_deportivas` (`id`, `usuario_instalacion_id`, `nombre`, `direccion`, `latitud`, `longitud`, `imagen`, `tarifa`, `calificacion`, `telefono`, `email`, `descripcion`, `estado`, `creado_en`) VALUES
	(1, 1, 'Top Gol Tacna', 'Av. Bolognesi 1234, Tacna', -17.99927959, -70.23738205, 'https://i.ibb.co/dJG8hdzS/images.png', 51.00, 4.50, '946143071', 'contacto@topgoltacna.com', 'Canchas de fútbol con césped sintético de primera calidad PREMIUM', 1, '2025-05-21 19:15:21'),
	(2, 1, 'Complejo Deportivo Municipal', 'Calle Patricio Meléndez 500, Tacna', -18.01220000, -70.25360000, 'https://i.ibb.co/Qvc2gsKS/complejodeportivo.jpg', 35.00, 4.20, '052987654', 'deportes@munitacna.gob.pe', 'Complejo deportivo municipal con múltiples canchas', 1, '2025-05-21 19:15:21'),
	(3, 1, 'Club Deportivo Tacna', 'Av. Cusco 750, Tacna', -18.00550000, -70.23980000, 'https://i.ibb.co/gb2kHZCq/111111.jpg', 65.00, 4.80, '052456789', 'info@clubdeportivotacna.com', 'Club exclusivo con instalaciones de primer nivel', 1, '2025-05-21 19:15:21'),
	(4, 2, 'IPD Tacna - Complejo Deportivo', 'Av. Gregorio Albarracín s/n, Tacna', -18.01500000, -70.25800000, NULL, 0.00, 5.00, '052-427070', 'ipd.tacna@ipd.gob.pe', 'Complejo deportivo del Instituto Peruano del Deporte', 1, '2025-06-04 20:03:09');

-- Volcando estructura para tabla gameon.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `area_deportiva_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `area_deportiva_id` (`area_deportiva_id`),
  CONSTRAINT `reservas_ibfk_area` FOREIGN KEY (`area_deportiva_id`) REFERENCES `areas_deportivas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.reservas: ~6 rows (aproximadamente)
INSERT INTO `reservas` (`id`, `id_usuario`, `area_deportiva_id`, `fecha`, `hora_inicio`, `hora_fin`, `estado`, `observaciones`, `creado_en`) VALUES
	(1, 2, 16, '2025-06-17', '07:00:00', '08:00:00', 'confirmada', NULL, '2025-06-17 07:47:20'),
	(2, 3, 16, '2025-06-17', '10:00:00', '12:00:00', 'confirmada', NULL, '2025-06-17 07:47:20'),
	(3, 4, 16, '2025-06-17', '16:00:00', '17:30:00', 'confirmada', NULL, '2025-06-17 07:47:20'),
	(4, 2, 18, '2025-06-17', '08:00:00', '09:00:00', 'confirmada', NULL, '2025-06-17 07:47:20'),
	(5, 2, 16, '2025-06-18', '09:00:00', '10:30:00', 'confirmada', NULL, '2025-06-17 07:47:20'),
	(6, 3, 18, '2025-06-18', '15:00:00', '16:30:00', 'pendiente', NULL, '2025-06-17 07:47:20');

-- Volcando estructura para tabla gameon.torneos
CREATE TABLE IF NOT EXISTS `torneos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `deporte_id` int(11) NOT NULL,
  `organizador_tipo` enum('institucion','ipd') NOT NULL,
  `organizador_id` int(11) NOT NULL,
  `institucion_sede_id` int(11) NOT NULL,
  `max_equipos` int(11) NOT NULL DEFAULT 16,
  `equipos_inscritos` int(11) NOT NULL DEFAULT 0,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_inscripcion_inicio` date NOT NULL,
  `fecha_inscripcion_fin` date NOT NULL,
  `estado` enum('proximo','inscripciones_abiertas','inscripciones_cerradas','activo','finalizado','cancelado') NOT NULL DEFAULT 'proximo',
  `modalidad` enum('eliminacion_simple','eliminacion_doble','todos_contra_todos','grupos_eliminatoria') NOT NULL DEFAULT 'eliminacion_simple',
  `premio_descripcion` text DEFAULT NULL,
  `costo_inscripcion` decimal(10,2) DEFAULT 0.00,
  `imagen_torneo` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_organizador` (`organizador_tipo`,`organizador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_deporte` (`deporte_id`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  KEY `idx_sede` (`institucion_sede_id`),
  CONSTRAINT `torneos_ibfk_1` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_ibfk_2` FOREIGN KEY (`institucion_sede_id`) REFERENCES `instituciones_deportivas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos: ~3 rows (aproximadamente)
INSERT INTO `torneos` (`id`, `nombre`, `descripcion`, `deporte_id`, `organizador_tipo`, `organizador_id`, `institucion_sede_id`, `max_equipos`, `equipos_inscritos`, `fecha_inicio`, `fecha_fin`, `fecha_inscripcion_inicio`, `fecha_inscripcion_fin`, `estado`, `modalidad`, `premio_descripcion`, `costo_inscripcion`, `imagen_torneo`, `creado_en`, `actualizado_en`) VALUES
	(1, 'Copa Top Gol 2025', 'Torneo de fútbol amateur en canchas de césped sintético', 1, 'institucion', 1, 1, 16, 0, '2025-07-15', '2025-07-28', '2025-06-15', '2025-07-10', 'inscripciones_abiertas', 'eliminacion_simple', 'Trofeo y medallas para los 3 primeros lugares', 150.00, 'futbol1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:48'),
	(2, 'Torneo IPD Voley Femenino', 'Campeonato oficial de voleibol femenino organizado por IPD', 2, 'ipd', 4, 4, 8, 0, '2025-08-01', '2025-08-15', '2025-07-01', '2025-07-25', 'proximo', 'todos_contra_todos', 'Copa IPD y reconocimientos oficiales', 0.00, 'voley1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:53'),
	(3, 'Liga Basket Municipal', 'Torneo de básquet amateur en el complejo municipal', 3, 'institucion', 2, 2, 12, 0, '2025-06-20', '2025-07-05', '2025-06-01', '2025-06-15', 'inscripciones_abiertas', 'grupos_eliminatoria', 'Trofeos y medallas + entradas al cine', 100.00, 'basket1.jpg', '2025-06-04 20:03:09', '2025-06-04 20:44:58');

-- Volcando estructura para tabla gameon.torneos_equipos
CREATE TABLE IF NOT EXISTS `torneos_equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `inscrito_por_usuario_id` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_inscripcion` enum('pendiente','confirmada','rechazada','retirado') DEFAULT 'pendiente',
  `comentarios` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipo_torneo_unico` (`torneo_id`,`equipo_id`),
  KEY `idx_torneo` (`torneo_id`),
  KEY `idx_equipo` (`equipo_id`),
  KEY `torneos_equipos_ibfk_3` (`inscrito_por_usuario_id`),
  CONSTRAINT `torneos_equipos_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_equipos_ibfk_2` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_equipos_ibfk_3` FOREIGN KEY (`inscrito_por_usuario_id`) REFERENCES `usuarios_deportistas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_equipos: ~2 rows (aproximadamente)
INSERT INTO `torneos_equipos` (`id`, `torneo_id`, `equipo_id`, `inscrito_por_usuario_id`, `fecha_inscripcion`, `estado_inscripcion`, `comentarios`) VALUES
	(1, 1, 1, 2, '2025-06-04 22:00:14', 'confirmada', NULL),
	(2, 2, 2, 2, '2025-06-04 22:00:14', 'confirmada', NULL);

-- Volcando estructura para tabla gameon.torneos_estadisticas
CREATE TABLE IF NOT EXISTS `torneos_estadisticas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `partidos_jugados` int(11) DEFAULT 0,
  `partidos_ganados` int(11) DEFAULT 0,
  `partidos_perdidos` int(11) DEFAULT 0,
  `partidos_empatados` int(11) DEFAULT 0,
  `goles_favor` int(11) DEFAULT 0,
  `goles_contra` int(11) DEFAULT 0,
  `puntos` int(11) DEFAULT 0,
  `posicion_final` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `torneo_equipo_stats` (`torneo_id`,`equipo_id`),
  KEY `torneos_estadisticas_ibfk_2` (`equipo_id`),
  CONSTRAINT `torneos_estadisticas_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_estadisticas_ibfk_2` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_estadisticas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla gameon.torneos_partidos
CREATE TABLE IF NOT EXISTS `torneos_partidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torneo_id` int(11) NOT NULL,
  `equipo_local_id` int(11) NOT NULL,
  `equipo_visitante_id` int(11) NOT NULL,
  `fase` enum('grupos','octavos','cuartos','semifinal','final','tercer_lugar') NOT NULL,
  `numero_grupo` int(11) DEFAULT NULL,
  `fecha_partido` datetime NOT NULL,
  `resultado_local` int(11) DEFAULT NULL,
  `resultado_visitante` int(11) DEFAULT NULL,
  `equipo_ganador_id` int(11) DEFAULT NULL,
  `estado_partido` enum('programado','en_curso','finalizado','suspendido','cancelado') DEFAULT 'programado',
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_torneo_fase` (`torneo_id`,`fase`),
  KEY `idx_fecha` (`fecha_partido`),
  KEY `torneos_partidos_ibfk_2` (`equipo_local_id`),
  KEY `torneos_partidos_ibfk_3` (`equipo_visitante_id`),
  KEY `torneos_partidos_ibfk_4` (`equipo_ganador_id`),
  CONSTRAINT `torneos_partidos_ibfk_1` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `torneos_partidos_ibfk_2` FOREIGN KEY (`equipo_local_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_3` FOREIGN KEY (`equipo_visitante_id`) REFERENCES `equipos` (`id`),
  CONSTRAINT `torneos_partidos_ibfk_4` FOREIGN KEY (`equipo_ganador_id`) REFERENCES `equipos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.torneos_partidos: ~3 rows (aproximadamente)
INSERT INTO `torneos_partidos` (`id`, `torneo_id`, `equipo_local_id`, `equipo_visitante_id`, `fase`, `numero_grupo`, `fecha_partido`, `resultado_local`, `resultado_visitante`, `equipo_ganador_id`, `estado_partido`, `observaciones`, `creado_en`) VALUES
	(5, 1, 1, 3, 'grupos', NULL, '2025-06-18 16:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34'),
	(6, 1, 3, 1, 'grupos', NULL, '2025-06-25 20:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34'),
	(7, 3, 3, 2, 'grupos', NULL, '2025-06-28 19:00:00', NULL, NULL, NULL, 'programado', NULL, '2025-06-04 22:12:34');

-- Volcando estructura para tabla gameon.usuarios_deportes
CREATE TABLE IF NOT EXISTS `usuarios_deportes` (
  `usuario_id` int(11) NOT NULL,
  `deporte_id` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`,`deporte_id`),
  KEY `deporte_id` (`deporte_id`),
  CONSTRAINT `usuarios_deportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_deportistas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_deportes_ibfk_2` FOREIGN KEY (`deporte_id`) REFERENCES `deportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_deportes: ~2 rows (aproximadamente)
INSERT INTO `usuarios_deportes` (`usuario_id`, `deporte_id`) VALUES
	(2, 1),
	(2, 3);

-- Volcando estructura para tabla gameon.usuarios_deportistas
CREATE TABLE IF NOT EXISTS `usuarios_deportistas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL DEFAULT '0',
  `apellidos` varchar(100) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '0',
  `telefono` varchar(20) NOT NULL DEFAULT '0',
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('Masculino','Feminino','Otro') NOT NULL,
  `nivel_habilidad` enum('Principiante','Intermedio','Avanzado') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_deportistas: ~3 rows (aproximadamente)
INSERT INTO `usuarios_deportistas` (`id`, `nombre`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `genero`, `nivel_habilidad`, `username`, `password`, `estado`, `creado_en`) VALUES
	(2, 'Sebastian', 'Fuentes Avalos', 'fuentessebastiansa4s@gmail.com', '946143071', '2005-01-18', 'Masculino', 'Intermedio', 'Chevi10', '$2y$10$IJrd1jNkOJNb73BS68/c.OeQG2R7NQmcuNoktqQINYtBYo1C4moOG', 1, '2025-05-19 19:10:38'),
	(3, 'Gabriela', 'Gutierrez Mamane', 'gabrielaga@gmail.com', '946143071', '2002-11-03', 'Feminino', 'Principiante', 'GabyGol', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-05-19 20:24:19'),
	(4, 'Victor', 'Cruz Mamani', 'victor@gmail.com', '946143072', '2001-06-02', 'Masculino', 'Principiante', 'Chamo', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-06-02 19:48:23');

-- Volcando estructura para tabla gameon.usuarios_instalaciones
CREATE TABLE IF NOT EXISTS `usuarios_instalaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_usuario` enum('privado','ipd') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla gameon.usuarios_instalaciones: ~2 rows (aproximadamente)
INSERT INTO `usuarios_instalaciones` (`id`, `username`, `password`, `estado`, `created_at`, `tipo_usuario`) VALUES
	(1, 'andy', '$2y$10$IJrd1jNkOJNb73BS68/c.OeQG2R7NQmcuNoktqQINYtBYo1C4moOG', 1, '2025-05-19 17:20:31', 'privado'),
	(2, 'ipd_tacna', '$2y$10$DetTzM9npZHxn9dufxtAoekAOZBzfmlQ568JEkpg4wIc3VrLJ6XEO', 1, '2025-06-04 20:03:09', 'ipd');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
