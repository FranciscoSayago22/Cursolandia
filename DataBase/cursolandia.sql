-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2026 a las 02:24:35
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cursolandia`
--
CREATE DATABASE IF NOT EXISTS `cursolandia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cursolandia`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `creador_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `cupo` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('publico','privado') DEFAULT 'publico',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_etiquetas`
--

CREATE TABLE `curso_etiquetas` (
  `curso_id` int(11) NOT NULL,
  `etiqueta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_preguntas`
--

CREATE TABLE `foro_preguntas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_respuestas`
--

CREATE TABLE `foro_respuestas` (
  `id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `respuesta` text NOT NULL,
  `es_aceptada` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `estado` enum('activa','completada','baja') DEFAULT 'activa',
  `fecha_inscripcion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_curso`
--

CREATE TABLE `material_curso` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `peso_bytes` int(11) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_pro`
--

CREATE TABLE `solicitudes_pro` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comprobante_pago` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `fecha_solicitud` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `imagen_perfil` varchar(255) NOT NULL,
  `titulo_profesional` varchar(100) DEFAULT NULL,
  `comprobante_titulo` varchar(255) DEFAULT NULL,
  `antecedentes` text DEFAULT NULL,
  `plan` enum('gratis','pro','admin') NOT NULL DEFAULT 'gratis',
  `vencimiento_pro` date DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creador_id` (`creador_id`);

--
-- Indices de la tabla `curso_etiquetas`
--
ALTER TABLE `curso_etiquetas`
  ADD PRIMARY KEY (`curso_id`,`etiqueta_id`),
  ADD KEY `etiqueta_id` (`etiqueta_id`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`curso_id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `material_curso`
--
ALTER TABLE `material_curso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `material_curso`
--
ALTER TABLE `material_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`creador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `curso_etiquetas`
--
ALTER TABLE `curso_etiquetas`
  ADD CONSTRAINT `curso_etiquetas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curso_etiquetas_ibfk_2` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  ADD CONSTRAINT `foro_preguntas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foro_preguntas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  ADD CONSTRAINT `foro_respuestas_ibfk_1` FOREIGN KEY (`pregunta_id`) REFERENCES `foro_preguntas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foro_respuestas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `material_curso`
--
ALTER TABLE `material_curso`
  ADD CONSTRAINT `material_curso_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD CONSTRAINT `solicitudes_pro_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
