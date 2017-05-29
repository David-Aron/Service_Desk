CREATE DATABASE tmw_bd_servicedesk;
USE tmw_bd_servicedesk;

CREATE TABLE `administradores` (
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `administradores` (`idusuario`) VALUES
(56);

CREATE TABLE `categorias` (
  `idcategoria` int(11) NOT NULL,
  `titulo` varchar(50) NOT NULL,
  `descricao` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `categorias` (`idcategoria`, `titulo`, `descricao`) VALUES
(1, 'Rede', 'Problemas de rede'),
(2, 'TV', 'Problemas de TV'),
(3, 'Banco', 'Problemas com o banco'),
(4, 'Relatorios', 'Problemas com os relatorios');

CREATE TABLE `chamados` (
  `idchamado` int(11) NOT NULL,
  `idcategoria` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` varchar(1050) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `idadministrador` int(11) DEFAULT NULL,
  `idtecnico` int(11) DEFAULT NULL,
  `estado` int(11) NOT NULL,
  `dataabertura` datetime NOT NULL,
  `datafechamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `chamados` (`idchamado`, `idcategoria`, `titulo`, `descricao`, `idusuario`, `idadministrador`, `idtecnico`, `estado`, `dataabertura`, `datafechamento`) VALUES
(70, 1, 'Problema de conexÃ£o', 'NÃ£o sei, nÃ£o quer conectar aqui', 59, 56, 57, 1, '2017-05-29 13:20:10', NULL),
(71, 2, 'A TV nÃ£o liga', 'opa, a tv nÃ£o tÃ¡ ligando aqui, qual foi?', 56, 56, 58, 1, '2017-05-29 13:24:16', NULL),
(73, 2, 'A conexÃ£o da tv', 'NÃ£o consigo conectar com o smartphone, me ajude', 56, 56, 0, 1, '2017-05-29 13:46:50', NULL),
(74, 1, 'Testes estranhos', 'aparecem uns testes aqui que nÃ£o entendo, ajudem aÃ­!', 56, 56, 57, 1, '2017-05-29 15:05:08', NULL),
(75, 2, 'Pegou fogo', 'do nada pegou fogo????', 56, NULL, NULL, 0, '2017-05-29 15:06:25', NULL),
(76, 1, 'Wi-fi nÃ£o funciona', 'nÃ£o tÃ¡ funcionando, como ligo?', 56, NULL, NULL, 0, '2017-05-29 15:07:21', NULL),
(77, 2, 'Testando', 'alguma coisa?', 56, 56, 58, 2, '2017-05-29 15:11:39', '2017-05-29 15:24:41'),
(78, 1, 'A internet parou', 'Oi, vocÃªs podem por favor conferior? meu ip Ã©', 60, NULL, NULL, 0, '2017-05-29 15:21:42', NULL),
(79, 4, 'Ainda nÃ£o temos relatÃ³rios', 'Ainda nÃ£o foi desenvolvida a funÃ§Ã£o de criar relatÃ³rios automaticamente!', 56, NULL, NULL, 0, '2017-05-29 15:22:42', NULL);

CREATE TABLE `relatorios` (
  `idrelatorio` int(11) NOT NULL,
  `dataemissao` datetime NOT NULL,
  `arquivo` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `tecnicos` (
  `idusuario` int(11) NOT NULL,
  `idcategoria` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tecnicos` (`idusuario`, `idcategoria`, `status`) VALUES
(57, 1, ''),
(58, 2, '');

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `login` varchar(45) NOT NULL,
  `senha` varchar(45) NOT NULL,
  `datacadastro` date NOT NULL,
  `qntchamadas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `usuarios` (`idusuario`, `nome`, `login`, `senha`, `datacadastro`, `qntchamadas`) VALUES
(55, 'David Aron', 'david@teste.com', '698dc19d489c4e4db73e28a713eab07b', '2017-05-28', 0),
(56, 'Admin', 'admin@teste.com', '698dc19d489c4e4db73e28a713eab07b', '2017-05-29', 0),
(57, 'Leonardo Sampaio', 'leonardo@teste.com', '698dc19d489c4e4db73e28a713eab07b', '2017-05-29', 0),
(58, 'Gabriel Ribeiro', 'gabriel@teste.com', '698dc19d489c4e4db73e28a713eab07b', '2017-05-29', 0),
(60, 'Bruna da Silva', 'bruna@teste.com', '698dc19d489c4e4db73e28a713eab07b', '2017-05-29', 0);


ALTER TABLE `administradores`
  ADD PRIMARY KEY (`idusuario`);

ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idcategoria`);

ALTER TABLE `chamados`
  ADD PRIMARY KEY (`idchamado`);

ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`idrelatorio`);

ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`idusuario`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `login` (`login`);

ALTER TABLE `categorias`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `chamados`
  MODIFY `idchamado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

ALTER TABLE `relatorios`
  MODIFY `idrelatorio` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
