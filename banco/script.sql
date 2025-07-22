-- SCRIPT SQL DO BANCO DE DADOS DA CLÍNICA DE PSICOLOGIA (MENTE RENOVADA)

CREATE DATABASE IF NOT EXISTS if0_39533260_mente_renovada;
USE if0_39533260_mente_renovada;

-- Tabela de Psicólogo
CREATE TABLE IF NOT EXISTS psicologo (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  CRP VARCHAR(255) NOT NULL,
  foto_perfil VARCHAR(255) NULL DEFAULT 'image/default.png',
  data_criacao DATETIME NOT NULL DEFAULT NOW(),
  data_atualizacao DATETIME NULL,
  ativo TINYINT NOT NULL DEFAULT 1
);

-- Tabela de Paciente
CREATE TABLE IF NOT EXISTS paciente (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  psicologo_id INT NOT NULL,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  telefone CHAR(14) NOT NULL,
  data_nasc DATE NOT NULL,
  data_criacao DATETIME NOT NULL DEFAULT NOW(),
  data_atualizacao DATETIME NULL,
  observacoes TEXT NULL,
  ativo TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id)
);

-- Tabela de Histórico
CREATE TABLE IF NOT EXISTS historico (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  psicologo_id INT NOT NULL,
  acao VARCHAR(50) NOT NULL,
  descricao TEXT NOT NULL,
  data_hora TIMESTAMP NOT NULL,
  tipo_entidade VARCHAR(50) NOT NULL,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id)
);

-- Tabela de Sessão
CREATE TABLE IF NOT EXISTS sessao (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  psicologo_id INT NOT NULL,
  paciente_id INT NOT NULL,
  anotacoes TEXT NOT NULL,
  data_hora_sessao TIMESTAMP NOT NULL,
  data_criacao DATETIME NOT NULL DEFAULT NOW(),
  data_atualizacao DATETIME NULL,
  status_sessao ENUM('AGENDADA','CANCELADA','REALIZADA') NOT NULL,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id),
  FOREIGN KEY (paciente_id) REFERENCES paciente(id)
);

-----------------------------------------
--            PROCEDURES
-----------------------------------------

-- procedure de inserir psicólogo
DELIMITER $$
CREATE PROCEDURE ps_psicologo_insert (
  psnome VARCHAR(100),
  psemail VARCHAR(100),
  pssenha VARCHAR(255),
  psCRP VARCHAR(255),
  psativo TINYINT(1)
)
BEGIN 
  INSERT INTO psicologo(nome, email, senha, CRP, ativo)
  VALUES (psnome, psemail, pssenha, psCRP, psativo);
  SELECT * FROM psicologo WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar psicólogo
DELIMITER $$
CREATE PROCEDURE ps_psicologo_update (
  psid INT,
  psnome VARCHAR(100),
  psemail VARCHAR(100),
  pssenha VARCHAR(255),
  psfoto_perfil VARCHAR(255)
)
BEGIN 
  UPDATE psicologo SET
    nome = psnome,
    email = psemail,
    senha = pssenha,
    foto_perfil = psfoto_perfil
  WHERE id = psid;
  SELECT * FROM psicologo WHERE id = psid;
END $$
DELIMITER ;

-- procedure de inativar psicólogo
DELIMITER $$
CREATE PROCEDURE ps_psicologo_disable(psid INT)
BEGIN
  UPDATE psicologo SET ativo = 0 WHERE id = psid;
  SELECT * FROM psicologo WHERE id = psid;
END $$
DELIMITER ;

-- procedure de inserir paciente
DELIMITER $$
CREATE PROCEDURE ps_paciente_insert (
  psicologo_id INT,
  psnome VARCHAR(100),
  psemail VARCHAR(100),
  pstelefone CHAR(14),
  psdata_nasc DATE,
  psdata_atualizacao DATETIME,
  psobservacoes TEXT,
  psativo TINYINT(1)
)
BEGIN 
  INSERT INTO paciente(psicologo_id, nome, email, telefone, data_nasc, data_criacao, data_atualizacao, observacoes, ativo)
  VALUES (psicologo_id, psnome, psemail, pstelefone, psdata_nasc, NOW(), psdata_atualizacao, psobservacoes, psativo);
  SELECT * FROM paciente WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar paciente
DELIMITER $$
CREATE PROCEDURE ps_paciente_update (
  psid INT,
  psicologo_id INT,
  psnome VARCHAR(100),
  psemail VARCHAR(100),
  pstelefone CHAR(14),
  psdata_nasc DATE,
  psobservacoes TEXT
)
BEGIN 
  UPDATE paciente SET
    psicologo_id = psicologo_id,
    nome = psnome,
    email = psemail,
    telefone = pstelefone,
    data_nasc = psdata_nasc,
    observacoes = psobservacoes
  WHERE id = psid;
  SELECT * FROM paciente WHERE id = psid;
END $$
DELIMITER ;

-- procedure de inativar paciente
DELIMITER $$
CREATE PROCEDURE ps_paciente_disable(psid INT)
BEGIN
  UPDATE paciente SET ativo = 0 WHERE id = psid;
  SELECT * FROM paciente WHERE id = psid;
END $$
DELIMITER ;

-- procedure de ativar paciente
DELIMITER $$
CREATE PROCEDURE ps_paciente_enable(psid INT)
BEGIN 
  UPDATE paciente SET ativo = 1 WHERE id = psid;
  SELECT * FROM paciente WHERE id = psid;
END $$
DELIMITER ;

-- procedure de inserir histórico
DELIMITER $$
CREATE PROCEDURE ps_historico_insert(
  psicologo_id INT,
  psacao VARCHAR(50),
  psdescricao TEXT,
  psdata_hora TIMESTAMP,
  pstipo_entidade VARCHAR(50)
)
BEGIN
  INSERT INTO historico(psicologo_id, acao, descricao, data_hora, tipo_entidade)
  VALUES (psicologo_id, psacao, psdescricao, psdata_hora, pstipo_entidade);
  SELECT * FROM historico WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure para excluir o histórico
DELIMITER $$
CREATE PROCEDURE ps_historico_delete(psid INT)
BEGIN
  DELETE FROM historico WHERE id = psid;
END $$
DELIMITER ;

-- procedure para inserir sessão
DELIMITER $$
CREATE PROCEDURE ps_sessao_insert(
  psicologo_id INT,
  paciente_id INT,
  psanotacoes TEXT,
  psdata_hora_sessao TIMESTAMP,
  psdata_atualizacao DATETIME,
  psstatus_sessao VARCHAR(20)
)
BEGIN
  INSERT INTO sessao(psicologo_id, paciente_id, anotacoes, data_hora_sessao, data_criacao, data_atualizacao, status_sessao)
  VALUES (psicologo_id, paciente_id, psanotacoes, psdata_hora_sessao, NOW(), psdata_atualizacao, psstatus_sessao);
  SELECT * FROM sessao WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar sessão
DELIMITER $$
CREATE PROCEDURE ps_sessao_update (
  psid INT,
  psicologo_id INT,
  paciente_id INT,
  psanotacoes TEXT,
  psdata_hora_sessao TIMESTAMP,
  psdata_atualizacao DATETIME,
  psstatus_sessao VARCHAR(20)
)
BEGIN
  UPDATE sessao SET
    psicologo_id = psicologo_id,
    paciente_id = paciente_id,
    anotacoes = psanotacoes,
    data_hora_sessao = psdata_hora_sessao,
    status_sessao = psstatus_sessao
  WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-- procedure para cancelar sessão
DELIMITER $$
CREATE PROCEDURE ps_sessao_disable(psid INT)
BEGIN
  UPDATE sessao SET status_sessao = 'CANCELADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-- procedure para ativar sessão
DELIMITER $$
CREATE PROCEDURE ps_sessao_enable(psid INT)
BEGIN
  UPDATE sessao SET status_sessao = 'AGENDADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-- procedure de confirmar sessão
DELIMITER $$
CREATE PROCEDURE ps_sessao_confirm(psid INT)
BEGIN
  UPDATE sessao SET status_sessao = 'REALIZADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-----------------------------------------
--            TRIGGER
-----------------------------------------
DELIMITER $$
CREATE TRIGGER trg_checar_psicologo_sessao
BEFORE INSERT ON sessao
FOR EACH ROW
BEGIN
  DECLARE psicologo_paciente INT;
  SELECT psicologo_id INTO psicologo_paciente
    FROM paciente WHERE id = NEW.paciente_id;
  IF psicologo_paciente <> NEW.psicologo_id THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Psicólogo da sessão não corresponde ao psicólogo do paciente.';
  END IF;
END $$
DELIMITER ;

-----------------------------------------
--      USUÁRIO E PERMISSÕES
-----------------------------------------

-- 1) Criação do usuário de aplicação
CREATE USER IF NOT EXISTS 'if0_39533260'@'192.168.%'
  IDENTIFIED BY 'SUA_SENHA_AQUI';

-- 2) Permissões de CRUD nas tabelas
GRANT SELECT, INSERT, UPDATE, DELETE
  ON if0_39533260_mente_renovada.psicologo TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON if0_39533260_mente_renovada.paciente TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON if0_39533260_mente_renovada.historico TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON if0_39533260_mente_renovada.sessao TO 'if0_39533260'@'192.168.%';

-- 3) Permissão para disparar trigger
GRANT TRIGGER ON if0_39533260_mente_renovada.sessao TO 'if0_39533260'@'192.168.%';

-- 4) Permissões de EXECUTE para todas as procedures
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_psicologo_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_psicologo_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_psicologo_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_paciente_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_paciente_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_paciente_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_paciente_enable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_historico_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_historico_delete TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_sessao_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_sessao_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_sessao_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_sessao_enable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE if0_39533260_mente_renovada.ps_sessao_confirm TO 'if0_39533260'@'192.168.%';

FLUSH PRIVILEGES;
