-- SOLO si la tabla de destino NO existe o hay que diseñarla (confirmar con el docente).
-- Primero revisar con:  SHOW TABLES;  DESCRIBE solicitudes_soporte;
-- Si ya existe con otra estructura, adaptar el método fila() en app/Services/SincronizadorDestino.php

CREATE TABLE IF NOT EXISTS solicitudes_soporte (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grupo         VARCHAR(100) NOT NULL,
  id_origen     BIGINT UNSIGNED NOT NULL,
  solicitante   VARCHAR(255) NOT NULL,
  departamento  VARCHAR(255) NOT NULL,
  descripcion   TEXT NOT NULL,
  prioridad     VARCHAR(20)  NOT NULL,
  estado        VARCHAR(20)  NOT NULL,
  created_at    TIMESTAMP NULL,
  updated_at    TIMESTAMP NULL,
  UNIQUE KEY uq_grupo_origen (grupo, id_origen)   -- evita duplicados al reenviar
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
