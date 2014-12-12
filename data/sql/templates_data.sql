-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: chains
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `templates`
--

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
INSERT INTO `templates` VALUES (1,1,'Productivity','productivity','Productivity is a measure of output from a production process, per unit of input. For example, labor productivity is typically measured as a ratio of output per labor-hour, an input. Productivity may be conceived of as a metric of the technical or engineering efficiency of production. As such, the emphasis is on quantitative metrics of input, and sometimes output. Productivity is distinct from metrics of allocative efficiency, which take into account both the monetary value (price) of what is produced and the cost of inputs used, and also distinct from metrics of profitability, which address the difference between the revenues obtained from output and the expense associated with consumption of inputs.','','','boolean',0,NULL,66,67,0,'','no','sum','yes',1),(2,1,'Arrived at work','arrived_at_work','','','','time',0,NULL,0,0,1,'','no','sum','yes',1),(3,1,'Left work','left_work','','','','time',0,NULL,0,0,2,'','no','sum','yes',1),(4,1,'Worked on project','worked_on_project','','','','timer',0,NULL,0,0,3,'','no','sum','yes',1),(5,1,'Learn words','learn_words','','','','numeric',0,NULL,0,0,4,'','no','sum','yes',1),(6,1,'Quit smoking','quit_smoking','','','','counter',68,NULL,0,0,1,'','no','sum','yes',2),(7,1,'Coffee','coffee','','','','counter',69,NULL,0,0,2,'','no','sum','yes',2),(8,1,'Tea','tea','','','','counter',70,NULL,0,0,3,'','no','sum','yes',2),(9,1,'Beer','beer','','','','counter',71,NULL,0,0,4,'','no','sum','yes',2),(10,1,'Wine','wine','','','','counter',72,NULL,0,0,5,'','no','sum','yes',2),(11,1,'Alcohol','alcohol','','','','counter',73,NULL,0,0,6,'','no','sum','yes',2),(12,1,'Wake up early','wake','','','','time',0,NULL,0,0,9,'','no','sum','yes',2),(13,1,'Fast food','fastfood','','','','counter',74,NULL,0,0,7,'','no','sum','yes',2),(14,1,'Gym','gym','','','','boolean',0,NULL,75,67,0,'','no','sum','yes',3),(15,1,'Swimming','swimmin','','','','boolean',0,NULL,76,67,1,'','no','sum','yes',3),(16,1,'Jogging','jogging','','','','numeric',0,NULL,0,0,2,'km','no','sum','yes',3),(17,1,'Push-ups','pushup','','','','numeric',0,NULL,0,0,3,'','no','sum','yes',3),(18,1,'Crunches','crunch','','','','numeric',0,NULL,0,0,4,'','no','sum','yes',3),(19,1,'Brush teeth','brush_teeth','','','','counter',77,NULL,0,0,0,'','no','avg','yes',4),(20,1,'Water','water','','','','counter',78,NULL,0,0,1,'','no','avg','yes',4),(21,1,'Take pills','pills','','','','counter',79,NULL,0,0,2,'','no','avg','yes',4),(22,1,'Expenses','expenses','','','','numeric',0,NULL,0,0,0,'$','yes','sum','yes',5),(23,1,'Income','income','','','','numeric',0,NULL,0,0,1,'$','yes','sum','yes',5),(24,1,'Water flowers','water_flowers','','','','counter',80,NULL,0,0,0,'','no','sum','yes',6),(25,1,'Walk the dog','walk_dog','','','','counter',81,NULL,0,0,1,'','no','sum','yes',6),(26,1,'Feed the cat','feed_cat','','','','counter',82,NULL,0,0,2,'','no','sum','yes',6),(27,1,'Feed the fish','feed_fish','','','','counter',83,NULL,0,0,3,'','no','sum','yes',6),(28,1,'Clean the house','clean_house','','','','counter',84,NULL,0,0,4,'','no','sum','yes',6),(29,1,'Post in blog','blog','','','','counter',85,NULL,0,0,0,'','no','sum','yes',7),(30,1,'Won in poker','won_poker','','','','counter',86,NULL,0,0,1,'','no','sum','yes',7),(31,1,'Lost in poker','lost_poker','','','','counter',87,NULL,0,0,2,'','no','sum','yes',7),(32,1,'Sleep','sleep','','','','time',0,NULL,0,0,3,'','no','sum','yes',4),(33,1,'Sleep early','sleep_early','','','','time',0,NULL,0,0,8,'','no','sum','yes',2),(34,9,'Продуктивность','productivity','','','','boolean',0,NULL,66,67,0,'','no','sum','yes',1),(35,9,'Прибыл на работу','arrived_at_work','','','','time',0,NULL,0,0,1,'','no','sum','yes',1),(36,9,'Покинул работу','left_work','','','','time',0,NULL,0,0,2,'','no','sum','yes',1),(37,9,'Работал над проектом','worked_on_project','','','','timer',0,NULL,0,0,3,'','no','sum','yes',1),(38,9,'Учить слова','learn_words','','','','numeric',0,NULL,0,0,4,'','no','sum','yes',1),(39,9,'Полить цветы','water_flowers','','','','counter',80,NULL,0,0,0,'','no','sum','yes',6),(40,9,'Выгулять собаку','walk_dog','','','','counter',81,NULL,0,0,1,'','no','sum','yes',6),(41,9,'Тренировка','gym','','','','boolean',0,NULL,75,67,0,'','no','sum','yes',3),(42,9,'Плавание','swimming','','','','boolean',0,NULL,76,67,1,'','no','sum','yes',3),(43,9,'Бросить курить','quit_smoking','','','','counter',5,NULL,0,0,0,'','no','sum','yes',2),(44,9,'Кофе','coffee','','','','counter',69,NULL,0,0,1,'','no','sum','yes',2),(45,9,'Чистить зубы','brush_teeth','','','','counter',77,NULL,0,0,0,'','no','avg','yes',4),(46,9,'Обновить блог','blog','','','','counter',85,NULL,0,0,0,'','no','sum','yes',7),(47,9,'Расходы','expenses','','','','numeric',0,NULL,0,0,0,'р.','no','sum','yes',5);
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-10-18 14:21:09
