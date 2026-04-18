/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pagcontasdb
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nome_categoria` varchar(120) NOT NULL,
  `cor` varchar(20) NOT NULL DEFAULT '#239a55',
  `meta_mensal` decimal(12,2) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uk_categoria_usuario_nome` (`id_usuario`,`nome_categoria`),
  KEY `idx_categoria_usuario` (`id_usuario`),
  CONSTRAINT `fk_usuario_categoria` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_relatorios_usuario`
--

DROP TABLE IF EXISTS `config_relatorios_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_relatorios_usuario` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo_agendamento` enum('intervalo','personalizado','desativado') NOT NULL DEFAULT 'intervalo',
  `intervalo_dias` int(11) DEFAULT NULL,
  `horario_envio` time NOT NULL DEFAULT '09:00:00',
  `fuso_horario` varchar(80) NOT NULL DEFAULT 'America/Sao_Paulo',
  `proximo_envio` date DEFAULT NULL,
  `ultimo_envio` datetime DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_quando` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `fk_usuario_config` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `chk_intervalo_dias` CHECK (`intervalo_dias` is null or `intervalo_dias` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_relatorios_usuario`
--

LOCK TABLES `config_relatorios_usuario` WRITE;
/*!40000 ALTER TABLE `config_relatorios_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `config_relatorios_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datas_personalizadas_relatorio`
--

DROP TABLE IF EXISTS `datas_personalizadas_relatorio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `datas_personalizadas_relatorio` (
  `id_personalizada` int(11) NOT NULL AUTO_INCREMENT,
  `id_config` int(11) NOT NULL,
  `dia_mes` int(11) DEFAULT NULL,
  `data_especifica` date DEFAULT NULL,
  `criado_quando` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_personalizada`),
  KEY `idx_datas_personalizadas_config` (`id_config`),
  CONSTRAINT `fk_config_datas` FOREIGN KEY (`id_config`) REFERENCES `config_relatorios_usuario` (`id_config`) ON DELETE CASCADE,
  CONSTRAINT `chk_dia_mes` CHECK (`dia_mes` is null or `dia_mes` between 1 and 31),
  CONSTRAINT `chk_data_personalizada_preenchida` CHECK (`dia_mes` is not null or `data_especifica` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datas_personalizadas_relatorio`
--

LOCK TABLES `datas_personalizadas_relatorio` WRITE;
/*!40000 ALTER TABLE `datas_personalizadas_relatorio` DISABLE KEYS */;
/*!40000 ALTER TABLE `datas_personalizadas_relatorio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gasto`
--

DROP TABLE IF EXISTS `gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gasto` (
  `id_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `descricao_gasto` varchar(255) NOT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `valor_gastos` decimal(12,2) NOT NULL,
  `data_gasto` date NOT NULL,
  `vencimento_gasto` date DEFAULT NULL,
  `status` enum('pendente','pago') NOT NULL DEFAULT 'pendente',
  `pago_quando` datetime DEFAULT NULL,
  `deletado_quando` datetime DEFAULT NULL,
  `criado_quando` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_gasto`),
  KEY `fk_usuario_gasto` (`id_usuario`),
  KEY `idx_gasto_categoria` (`id_categoria`),
  CONSTRAINT `fk_categoria_gasto` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuario_gasto` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gasto`
--

LOCK TABLES `gasto` WRITE;
/*!40000 ALTER TABLE `gasto` DISABLE KEYS */;
/*!40000 ALTER TABLE `gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `modo_simplificado` tinyint(1) NOT NULL DEFAULT 0,
  `alto_contraste` tinyint(1) NOT NULL DEFAULT 0,
  `data_criado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `telefone` (`telefone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES
(1,'Pedro','65999999999','$2y$10$VEuiaTLTJZJrKdeLBLQe5eysPQCE3ahqRUGJysKv7olKObRiD8Ezm','ativo',0,0,'2026-04-18 20:46:38');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'pagcontasdb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-04-18 17:49:33
