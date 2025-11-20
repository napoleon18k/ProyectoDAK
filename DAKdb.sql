-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 19-11-2025 a las 03:27:54
-- Versión del servidor: 8.0.43
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `DAKdb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargador`
--

CREATE TABLE `cargador` (
  `id` int NOT NULL,
  `potencia` decimal(10,2) DEFAULT NULL,
  `tipo` enum('Tipo2','CCS2','GBT') DEFAULT NULL,
  `id_E` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cargador`
--

INSERT INTO `cargador` (`id`, `potencia`, `tipo`, `id_E`) VALUES
(301, 50.00, 'CCS2', 201),
(302, 22.00, 'Tipo2', 201),
(303, 7.40, 'Tipo2', 202),
(304, 150.00, 'CCS2', 202),
(305, 50.00, 'GBT', 203);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `efectuapago`
--

CREATE TABLE `efectuapago` (
  `id_P` int NOT NULL,
  `id_V` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `efectuapago`
--

INSERT INTO `efectuapago` (`id_P`, `id_V`) VALUES
(901, 701),
(902, 702);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estacion`
--

CREATE TABLE `estacion` (
  `id` int NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `departamento` varchar(50) NOT NULL,
  `lat` varchar(50) DEFAULT NULL,
  `lng` varchar(50) DEFAULT NULL,
  `estado` enum('Disponible','En uso','fuera de servicio') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `estacion`
--

INSERT INTO `estacion` (`id`, `nombre`, `direccion`, `departamento`, `lat`, `lng`, `estado`) VALUES
(201, 'Estación Central MVD', 'Av. 18 de Julio 1234', 'Montevideo', '-34.9011', '-56.1645', 'Disponible'),
(202, 'Punto de Carga Punta', 'Rambla del Puerto 500', 'Maldonado', '-34.9667', '-54.9482', 'Disponible'),
(203, 'Recarga Ciudad Vieja', 'Pérez Castellano 1450', 'Montevideo', '-34.9069', '-56.2023', 'En uso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hacereserva`
--

CREATE TABLE `hacereserva` (
  `id_R` int NOT NULL,
  `id_U` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `hacereserva`
--

INSERT INTO `hacereserva` (`id_R`, `id_U`) VALUES
(501, 2),
(503, 2),
(502, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mediante`
--

CREATE TABLE `mediante` (
  `id_P` int NOT NULL,
  `id_R` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mediante`
--

INSERT INTO `mediante` (`id_P`, `id_R`) VALUES
(801, 601),
(803, 602);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id`, `monto`, `fecha_pago`, `metodo`) VALUES
(901, 500.00, '2025-11-19 10:30:00', 'Tarjeta Crédito'),
(902, 350.50, '2025-11-18 15:45:00', 'Transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parada`
--

CREATE TABLE `parada` (
  `id` int NOT NULL,
  `orden` int NOT NULL,
  `id_V` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `parada`
--

INSERT INTO `parada` (`id`, `orden`, `id_V`) VALUES
(801, 1, 701),
(802, 2, 701),
(803, 1, 702);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva`
--

CREATE TABLE `reserva` (
  `ID_Asociacion` int NOT NULL,
  `ID_Reserva` int NOT NULL,
  `ID_Slot` int NOT NULL,
  `ID_Cargador` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `reserva`
--

INSERT INTO `reserva` (`ID_Asociacion`, `ID_Reserva`, `ID_Slot`, `ID_Cargador`) VALUES
(601, 501, 401, 301),
(602, 502, 403, 303),
(603, 503, 404, 302);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva_cargador`
--

CREATE TABLE `reserva_cargador` (
  `id` int NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `estado` enum('pendiente','activa','cancelada','finalizada') NOT NULL DEFAULT 'pendiente',
  `creado_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `reserva_cargador`
--

INSERT INTO `reserva_cargador` (`id`, `nombre`, `estado`, `creado_at`) VALUES
(501, 'Reserva de Juan 1', 'pendiente', '2025-11-19 03:12:30'),
(502, 'Reserva de Maria', 'activa', '2025-11-19 03:12:30'),
(503, 'Reserva de Juan 2', 'finalizada', '2025-11-19 03:12:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sehace`
--

CREATE TABLE `sehace` (
  `id_VJE` int NOT NULL,
  `id_V` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `sehace`
--

INSERT INTO `sehace` (`id_VJE`, `id_V`) VALUES
(701, 101),
(702, 102);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `slot_horario`
--

CREATE TABLE `slot_horario` (
  `id` int NOT NULL,
  `inicio` time NOT NULL,
  `fin` time NOT NULL,
  `duracion` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `slot_horario`
--

INSERT INTO `slot_horario` (`id`, `inicio`, `fin`, `duracion`) VALUES
(401, '09:00:00', '10:00:00', 60),
(402, '10:00:00', '11:30:00', 90),
(403, '14:00:00', '15:00:00', 60),
(404, '18:00:00', '18:30:00', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','cliente','gestor') NOT NULL,
  `correo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `usuario`, `password`, `rol`, `correo`) VALUES
(1, 'admin_sys', '$2y$10$abcdefghijklmnopqrstuvwxyza', 'admin', 'admin@mail.com'),
(2, 'juan_cliente', '$2y$10$abcdefghijklmnopqrstuvwxyzb', 'cliente', 'juan@mail.com'),
(3, 'maria_cliente', '$2y$10$abcdefghijklmnopqrstuvwxyzc', 'cliente', 'maria@mail.com'),
(4, 'gestion_uy', '$2y$10$abcdefghijklmnopqrstuvwxyzd', 'gestor', 'gestion@mail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo`
--

CREATE TABLE `vehiculo` (
  `id` int NOT NULL,
  `marca` varchar(20) NOT NULL,
  `modelo` varchar(20) NOT NULL,
  `ano` int NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `autonomia` varchar(20) NOT NULL,
  `tipo_conector` enum('lenta','rapida','ultra') NOT NULL,
  `duenio` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vehiculo`
--

INSERT INTO `vehiculo` (`id`, `marca`, `modelo`, `ano`, `matricula`, `autonomia`, `tipo_conector`, `duenio`) VALUES
(101, 'Tesla', 'Model 3', 2022, 'AAA1234', '500km', 'ultra', 2),
(102, 'Nissan', 'Leaf', 2019, 'BBB5678', '250km', 'lenta', 3),
(103, 'BMW', 'iX3', 2023, 'CCC9012', '450km', 'rapida', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viaje`
--

CREATE TABLE `viaje` (
  `id` int NOT NULL,
  `fecha_v` date NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `viaje`
--

INSERT INTO `viaje` (`id`, `fecha_v`, `origen`, `destino`) VALUES
(701, '2025-11-20', 'Montevideo', 'Colonia'),
(702, '2025-11-22', 'Maldonado', 'Rocha');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargador`
--
ALTER TABLE `cargador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_E` (`id_E`);

--
-- Indices de la tabla `efectuapago`
--
ALTER TABLE `efectuapago`
  ADD PRIMARY KEY (`id_P`),
  ADD KEY `id_V` (`id_V`);

--
-- Indices de la tabla `estacion`
--
ALTER TABLE `estacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hacereserva`
--
ALTER TABLE `hacereserva`
  ADD PRIMARY KEY (`id_R`),
  ADD KEY `id_U` (`id_U`);

--
-- Indices de la tabla `mediante`
--
ALTER TABLE `mediante`
  ADD PRIMARY KEY (`id_P`),
  ADD KEY `id_R` (`id_R`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `parada`
--
ALTER TABLE `parada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_V` (`id_V`);

--
-- Indices de la tabla `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`ID_Asociacion`),
  ADD UNIQUE KEY `UQ_Slot_Cargador` (`ID_Slot`,`ID_Cargador`),
  ADD KEY `ID_Reserva` (`ID_Reserva`),
  ADD KEY `ID_Cargador` (`ID_Cargador`);

--
-- Indices de la tabla `reserva_cargador`
--
ALTER TABLE `reserva_cargador`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sehace`
--
ALTER TABLE `sehace`
  ADD PRIMARY KEY (`id_VJE`,`id_V`),
  ADD KEY `id_V` (`id_V`);

--
-- Indices de la tabla `slot_horario`
--
ALTER TABLE `slot_horario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `duenio` (`duenio`);

--
-- Indices de la tabla `viaje`
--
ALTER TABLE `viaje`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargador`
--
ALTER TABLE `cargador`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- AUTO_INCREMENT de la tabla `estacion`
--
ALTER TABLE `estacion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=903;

--
-- AUTO_INCREMENT de la tabla `parada`
--
ALTER TABLE `parada`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=804;

--
-- AUTO_INCREMENT de la tabla `reserva`
--
ALTER TABLE `reserva`
  MODIFY `ID_Asociacion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=604;

--
-- AUTO_INCREMENT de la tabla `reserva_cargador`
--
ALTER TABLE `reserva_cargador`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=504;

--
-- AUTO_INCREMENT de la tabla `slot_horario`
--
ALTER TABLE `slot_horario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `viaje`
--
ALTER TABLE `viaje`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=703;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cargador`
--
ALTER TABLE `cargador`
  ADD CONSTRAINT `cargador_ibfk_1` FOREIGN KEY (`id_E`) REFERENCES `estacion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `efectuapago`
--
ALTER TABLE `efectuapago`
  ADD CONSTRAINT `efectuapago_ibfk_1` FOREIGN KEY (`id_P`) REFERENCES `pago` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `efectuapago_ibfk_2` FOREIGN KEY (`id_V`) REFERENCES `viaje` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `hacereserva`
--
ALTER TABLE `hacereserva`
  ADD CONSTRAINT `hacereserva_ibfk_1` FOREIGN KEY (`id_R`) REFERENCES `reserva` (`ID_Reserva`) ON DELETE CASCADE,
  ADD CONSTRAINT `hacereserva_ibfk_2` FOREIGN KEY (`id_U`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mediante`
--
ALTER TABLE `mediante`
  ADD CONSTRAINT `mediante_ibfk_1` FOREIGN KEY (`id_P`) REFERENCES `parada` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mediante_ibfk_2` FOREIGN KEY (`id_R`) REFERENCES `reserva` (`ID_Asociacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `parada`
--
ALTER TABLE `parada`
  ADD CONSTRAINT `parada_ibfk_1` FOREIGN KEY (`id_V`) REFERENCES `viaje` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `reserva_ibfk_1` FOREIGN KEY (`ID_Reserva`) REFERENCES `reserva_cargador` (`id`),
  ADD CONSTRAINT `reserva_ibfk_2` FOREIGN KEY (`ID_Slot`) REFERENCES `slot_horario` (`id`),
  ADD CONSTRAINT `reserva_ibfk_3` FOREIGN KEY (`ID_Cargador`) REFERENCES `cargador` (`id`);

--
-- Filtros para la tabla `sehace`
--
ALTER TABLE `sehace`
  ADD CONSTRAINT `sehace_ibfk_1` FOREIGN KEY (`id_VJE`) REFERENCES `viaje` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sehace_ibfk_2` FOREIGN KEY (`id_V`) REFERENCES `vehiculo` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD CONSTRAINT `vehiculo_ibfk_1` FOREIGN KEY (`duenio`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
