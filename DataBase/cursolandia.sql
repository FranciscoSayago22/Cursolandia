-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 05:19:53
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

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `creador_id`, `titulo`, `descripcion`, `costo`, `cupo`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`) VALUES
(1, 6, 'Introduccion a la Programación', 'Los alumnos aprenderan lo basico de la Programación de una manera muy diferente.', 0.00, 1, '2026-11-13', '2026-11-27', 'publico', '2026-11-13 23:26:51'),
(2, 4, 'Politicas y sus Problemas', 'Se indagara sobre temas de politica y sus diferentes problematicas.', 0.00, NULL, '2026-11-13', '2026-11-14', 'publico', '2026-11-13 23:28:07'),
(3, 5, 'Introduccion a Python', 'Se aprenderan conceptos basicos de Python', 0.00, NULL, '2026-11-19', '2026-11-21', 'publico', '2026-11-13 23:29:37'),
(4, 5, 'Robotica', 'Se aprendera arduino con Tinkercad', 0.00, NULL, '2026-11-14', '2026-11-24', 'publico', '2026-11-13 23:30:21'),
(5, 6, 'c', 'Aca se va a ver lo basico de c', 0.00, NULL, '2026-11-18', '2026-11-28', 'publico', '2026-11-13 23:34:53'),
(6, 6, 'c++', 'Aca se va a ver lo basico de c++', 0.00, NULL, '2026-11-27', '2026-11-28', 'publico', '2026-11-13 23:35:27'),
(8, 5, 'Desarrollo Movil', 'Se aprendera a desarrollar aplicaciones Moviles.', 0.00, NULL, '2026-11-27', '2026-12-10', 'privado', '2026-11-15 21:37:01'),
(9, 5, 'Antiguedades y Vintage', 'Es un curso donde se veran cosas antiguas', 0.00, NULL, '2026-11-02', '2026-11-03', 'publico', '2026-11-15 21:48:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_etiquetas`
--

CREATE TABLE `curso_etiquetas` (
  `curso_id` int(11) NOT NULL,
  `etiqueta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `curso_etiquetas`
--

INSERT INTO `curso_etiquetas` (`curso_id`, `etiqueta_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(3, 4),
(4, 5),
(4, 6),
(4, 7),
(5, 8),
(6, 9),
(8, 11),
(8, 12),
(9, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id`, `nombre`) VALUES
(11, 'Aplicaciones'),
(6, 'Arduino'),
(8, 'C'),
(9, 'c++'),
(12, 'Desarollo Movil'),
(10, 'Java'),
(1, 'Logica de Programacion.'),
(2, 'Politica'),
(4, 'Programacion'),
(3, 'Python'),
(5, 'Robotica'),
(7, 'Tinkercad'),
(13, 'Vintage');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_preguntas`
--

CREATE TABLE `foro_preguntas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `cerrada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foro_preguntas`
--

INSERT INTO `foro_preguntas` (`id`, `curso_id`, `usuario_id`, `pregunta`, `cerrada`, `fecha`) VALUES
(1, 1, 6, 'Hola chicos, pueden ver esto?', 0, '2026-11-13 23:38:41'),
(2, 1, 1, 'Hola profe, que vamos a ver en el curso?', 1, '2026-11-13 23:40:27'),
(4, 5, 4, 'Hola profe, que vamos a ver en el curso?', 0, '2026-11-16 00:34:57');

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

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `usuario_id`, `curso_id`, `estado`, `fecha_inscripcion`) VALUES
(1, 1, 1, 'activa', '2026-11-13 23:30:38'),
(2, 1, 2, 'activa', '2026-11-13 23:30:47'),
(3, 1, 3, 'activa', '2026-11-13 23:30:51'),
(5, 6, 9, 'activa', '2026-11-15 21:49:00'),
(6, 4, 5, 'activa', '2026-11-16 00:34:52');

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

--
-- Volcado de datos para la tabla `material_curso`
--

INSERT INTO `material_curso` (`id`, `curso_id`, `nombre_archivo`, `peso_bytes`, `fecha_subida`) VALUES
(1, 1, '1794623211_6af7c6eb3aa82_P7.GUÍA DE RESOLUCIÓN 18 al 27.pdf', 2882213, '2026-11-13 23:26:51'),
(2, 2, '1794623287_6af7c7379788d_Stewart.pdf', 16829042, '2026-11-13 23:28:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `enlace` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
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

--
-- Volcado de datos para la tabla `solicitudes_pro`
--

INSERT INTO `solicitudes_pro` (`id`, `usuario_id`, `comprobante_pago`, `mensaje`, `estado`, `fecha_solicitud`) VALUES
(1, 4, '1794622468_pago_Comprobante de pago Banco Nación.png', '13/11/2026 Pague 5 Pesos.', 'aprobada', '2026-11-13 23:14:28'),
(2, 8, '1794801097_pago_Comprobante de pago Banco Nación.png', '16/11/2026 Pague 2 pesos', 'rechazada', '2026-11-16 00:51:37');

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
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `nombre_completo`, `imagen_perfil`, `titulo_profesional`, `comprobante_titulo`, `antecedentes`, `plan`, `vencimiento_pro`, `fecha_registro`) VALUES
(1, 'Marialopez@gmail.com', '$2y$10$1EGojOdao07nIGscvWWJ6OxG3s4mx7nYO4YQdN8FXMEJkEFlqMFFa', 'Maria Lopez', '1794621833_Avatar anime rubia con sudadera negra.png', 'Ingeniera en Informatica', '1794621833_doc_Certificado de Título Universitario.png', 'Soy una Ingeniera en Informatica fanatica de la programacion y me encanta Java', 'gratis', NULL, '2026-11-13 23:03:53'),
(4, 'julietamolinasaa@gmail.com', '$2y$10$1aCW5gsZrO3ZRChyuA.Mi.w70UfTsm/R79ES1157Ucppwoy1hUzWi', 'Julieta Molina', '1794622048_Avatar anime de chica sonriente.png', 'Licenciada en Ciencias Politicas', '1794622048_doc_Certificado Universitario con Distinción Dorada.png', 'Me encanta la Politica y Quiero hacer una Maestria en Politicas Publicas', 'pro', '2027-11-13', '2026-11-13 23:07:28'),
(5, 'francisco.sayago55@gmail.com', '$2y$10$7KbLgGsqT6kE0ZqfC77C6.VATznwXBoInWxNygBedZgEH5dDG3iOy', 'Francisco Sayago', '1794622129_Avatar masculino con capucha sobre fondo azul.png', 'Ingeniero en Informatica', '1794622129_doc_Certificado de Título Universitario.png', 'Soy un educador STEAM que da clases de Roblox, Programacion y Robotica', 'admin', NULL, '2026-11-13 23:08:49'),
(6, 'fran@gmail.com', '$2y$10$B3kT5vPNCVcoR/2iS/JFFe/JotwR.N1KmMaAxcrP7DuWVUNV5u3qi', 'Daniel Bertola', '1794622182_Avatar minimalista con capucha negra.png', '', NULL, 'Me gusta la programacion :3', 'gratis', NULL, '2026-11-13 23:09:42'),
(8, 'fran2@gmail.com', '$2y$10$rOk.lKsCIa2AtPUJrX4OpexFjc13qv934rzvwQBKxbh3x5TmzBNA2', 'Francisco Bertola', '1794795180_Avatar anime rubia con sudadera negra.png', '', NULL, 'Me gusta la naturaleza', 'gratis', NULL, '2026-11-15 23:13:00'),
(9, 'norberto@gmail.com', '$2y$10$EymlKucy2gwzFhpZqKfLDe66xsb483bAgfn3IrahpSf7sU8/L63AW', 'Norberto Ramirez', '1794796401_Avatar minimalista con capucha negra.png', '', NULL, 'Me gusta la medicina', 'gratis', NULL, '2026-11-15 23:33:21');

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
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `material_curso`
--
ALTER TABLE `material_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD CONSTRAINT `solicitudes_pro_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
