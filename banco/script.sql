-- SCRIPT SQL DO BANCO DE DADOS DA CLÍNICA DE PSICOLOGIA (MENTE RENOVADA)

Create database if not exists psicologia;
Use psicologia;

-- Tabela de Psicólogo

Create table psicologo (
  id int not null auto_increment primary key,
  nome varchar(100) not null,
  email varchar(100) not null,
  senha varchar(255) not null,
  CRP varchar(255) not null,
  foto_perfil varchar(255) null default 'image/default.png',
  data_criacao datetime not null default now(),
  data_atualizacao datetime null,
  ativo tinyint not null DEFAULT 1
);

-- Tabela de Paciente

Create table paciente (
  id int not null auto_increment primary key,
  psicologo_id int not null,
  nome varchar(100) not null,
  email varchar(100) not null,
  telefone char(14) not null,
  data_nasc date not null,
  data_criacao datetime not null  default now(),
  data_atualizacao datetime null,
  observacoes text null,
  ativo tinyint not null DEFAULT 1,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id)
);

-- Tabela de Histórico

Create table historico (
  id int auto_increment not null primary key,
  psicologo_id int not null,
  acao varchar(50) not null, 
  descricao text not null,
  data_hora timestamp not null,
  tipo_entidade varchar(50) not null,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id) 
);

-- Tabela de Sessão

Create table sessao (
  id int auto_increment not null primary key,
  psicologo_id int not null,
  paciente_id int not null,
  anotacoes text not null,
  data_hora_sessao timestamp not null,
  data_criacao datetime not null  default now(),
  data_atualizacao datetime null,
  status_sessao ENUM('AGENDADA','CANCELADA','REALIZADA') not null,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id),
  FOREIGN KEY (paciente_id) REFERENCES paciente(id)
);

-----------------------------------------
--            PROCEDURES
-----------------------------------------

-- procedure de inserir psicólogo

DELIMITER $$
CREATE PROCEDURE ps_psicologo_insert (
  psnome varchar(100),
  psemail varchar(100),
  pssenha varchar(255),
  psCRP varchar(255),
  psativo tinyint(1)
)
BEGIN 
  INSERT INTO psicologo(
    id,
    nome,
    email,
    senha,
    CRP,
    ativo
  )
  VALUES (
    0,
    psnome,
    psemail,
    pssenha,
    psCRP,
    1
  );
  SELECT * FROM psicologo WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar psicólogo 

DELIMITER $$
CREATE PROCEDURE ps_psicologo_update (
  psid int,
  psnome varchar(100),
  psemail varchar(100),
  pssenha varchar(255),
  psfoto_perfil varchar(255)
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
CREATE PROCEDURE ps_psicologo_disable(psid int)
BEGIN
  UPDATE psicologo SET ativo = 0 WHERE id = psid;
  SELECT * FROM psicologo WHERE id = psid;
END $$
DELIMITER ;

-- procedure de inserir paciente 

DELIMITER $$
CREATE PROCEDURE ps_paciente_insert (
  psicologo_id int,
  psnome varchar(100),
  psemail varchar(100),
  pstelefone char(14),
  psdata_nasc date,
  psdata_atualizacao datetime,
  psobservacoes text,
  psativo tinyint(1)
)
BEGIN 
  INSERT INTO paciente(
    id,
    psicologo_id,
    nome,
    email,
    telefone,
    data_nasc,
    data_criacao, 
    data_atualizacao,
    observacoes,
    ativo
  )
  VALUES (
    0,
    psicologo_id,
    psnome,
    psemail,
    pstelefone,
    psdata_nasc,
    NOW(),
    psdata_atualizacao,
    psobservacoes,
    1
  );
  SELECT * FROM paciente WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar paciente

DELIMITER $$
CREATE PROCEDURE ps_paciente_update (
  psid int,
  psicologo_id int,
  psnome varchar(100),
  psemail varchar(100),
  pstelefone char(14),
  psdata_nasc date,
  psobservacoes text
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
CREATE PROCEDURE ps_paciente_disable(psid int)
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
  psicologo_id int,
  psacao varchar(50),
  psdescricao text,
  psdata_hora timestamp,
  pstipo_entidade varchar(50)
) 
BEGIN
  INSERT INTO historico(
    id,
    psicologo_id,
    acao,
    descricao,
    data_hora,
    tipo_entidade
  ) VALUES (
    0,
    psicologo_id,
    psacao,
    psdescricao,
    psdata_hora,
    pstipo_entidade
  );
  SELECT * FROM historico WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure para excluir o histórico

DELIMITER $$
CREATE PROCEDURE ps_historico_delete(psid int)
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
  INSERT INTO sessao(
    id,
    psicologo_id,
    paciente_id,
    anotacoes,
    data_hora_sessao,
    data_criacao,
    data_atualizacao,
    status_sessao
  ) VALUES (
    0,
    psicologo_id,
    paciente_id,
    psanotacoes,
    psdata_hora_sessao,
    NOW(),
    psdata_atualizacao,
    psstatus_sessao
  );
  SELECT * FROM sessao WHERE id = LAST_INSERT_ID();
END $$
DELIMITER ;

-- procedure de alterar sessão

DELIMITER $$
CREATE PROCEDURE ps_sessao_update (
  psid int,
  psicologo_id int,
  paciente_id int,
  psanotacoes text,
  psdata_hora_sessao timestamp,
  psdata_atualizacao datetime,
  psstatus_sessao varchar(20)
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
CREATE PROCEDURE ps_sessao_disable(psid int)
BEGIN
  UPDATE sessao SET status_sessao = 'CANCELADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-- Procedure para ativar a sessão

DELIMITER $$
CREATE PROCEDURE ps_sessao_enable(psid int)
BEGIN
  UPDATE sessao SET status_sessao = 'AGENDADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-- Procedure de confirmação da sessão

DELIMITER $$
CREATE PROCEDURE ps_sessao_confirm(psid int)
BEGIN
  UPDATE sessao SET status_sessao = 'REALIZADA' WHERE id = psid;
  SELECT * FROM sessao WHERE id = psid;
END $$
DELIMITER ;

-----------------------------------------
--            TRIGGER
-----------------------------------------

DELIMITER $$
CREATE TRIGGER trg_checar_psicologo_sessao BEFORE INSERT ON sessao
FOR EACH ROW
BEGIN
  DECLARE psicologo_paciente INT;
  SELECT psicologo_id INTO psicologo_paciente FROM paciente WHERE id = NEW.paciente_id;
  IF psicologo_paciente != NEW.psicologo_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Psicólogo da sessão não corresponde ao psicólogo do paciente.';
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
  ON psicologia.psicologo TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON psicologia.paciente TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON psicologia.historico TO 'if0_39533260'@'192.168.%';
GRANT SELECT, INSERT, UPDATE, DELETE
  ON psicologia.sessao TO 'if0_39533260'@'192.168.%';

-- 3) Permissão para disparar trigger
GRANT TRIGGER ON psicologia.sessao TO 'if0_39533260'@'192.168.%';

-- 4) Permissões de EXECUTE para todas as procedures
GRANT EXECUTE ON PROCEDURE psicologia.ps_psicologo_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_psicologo_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_psicologo_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_paciente_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_paciente_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_paciente_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_paciente_enable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_historico_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_historico_delete TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_sessao_insert TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_sessao_update TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_sessao_disable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_sessao_enable TO 'if0_39533260'@'192.168.%';
GRANT EXECUTE ON PROCEDURE psicologia.ps_sessao_confirm TO 'if0_39533260'@'192.168.%';

FLUSH PRIVILEGES;
