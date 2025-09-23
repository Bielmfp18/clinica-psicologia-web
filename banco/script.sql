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
data_criacao datetime not null default now() ,
data_atualizacao datetime null,
ativo tinyint not null DEFAULT 1
);

ALTER TABLE psicologo
  ADD COLUMN verification_code_hash VARCHAR(255) NULL,
  ADD COLUMN verification_expires DATETIME NULL,
  ADD COLUMN verification_attempts INT NOT NULL DEFAULT 0,
  ADD COLUMN verification_sent_at DATETIME NULL;

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


-- Tabela de Tokens para recuperação de senha

CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  psicologo_id INT NOT NULL,
  token_hash VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id) ON DELETE CASCADE
);

-- Tabela de Tokens para verificação de login

CREATE TABLE login_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  psicologo_id INT NOT NULL,
  token_hash VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(psicologo_id),
  FOREIGN KEY (psicologo_id) REFERENCES psicologo(id) ON DELETE CASCADE
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
DELIMITER;

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
create procedure ps_psicologo_disable(psid int)
begin
    update psicologo set ativo = 0 where id = psid;
select * from psicologo where id = psid;
END $$
DELIMITER ;






-- procedure de inserir paciente 

DELIMITER $$
CREATE PROCEDURE ps_paciente_insert (
pspsicologo_id int,
psnome varchar(100),
psemail varchar(100),
pstelefone char(14),
psdata_nasc date,
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
  observacoes,
  ativo
)
VALUES (
  0,
  pspsicologo_id,
  psnome,
  psemail,
  pstelefone,
  psdata_nasc,
  NOW(),
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
pspsicologo_id int,
psnome varchar(100),
psemail varchar(100),
pstelefone char(14),
psdata_nasc date,
psobservacoes text
)
 BEGIN 
UPDATE paciente SET 
psicologo_id = pspsicologo_id,
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
create procedure ps_paciente_disable(psid int)
begin
    update paciente set ativo = 0 where id = psid;
select * from paciente where id = psid;
END $$
DELIMITER ;

-- procedure de ativar paciente

DELIMITER $$
CREATE PROCEDURE ps_paciente_enable(psid INT)
begin 
	update paciente set ativo = 1 where id = psid;
select * from paciente where id = psid;
END $$
DELIMITER ;






-- procedure de inserir histórico
 
DELIMITER $$
create procedure ps_historico_insert(
pspsicologo_id int,
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
pspsicologo_id,
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
 create procedure ps_historico_delete(psid int)
 begin
 delete from historico where id = psid;
 end $$
DELIMITER ;






-- procedure para inserir sessão

DELIMITER $$
CREATE PROCEDURE ps_sessao_insert(
  pspsicologo_id INT,
  pspaciente_id INT,
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
    pspsicologo_id,
    pspaciente_id,
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
pspsicologo_id int,
pspaciente_id int,
psanotacoes text,
psdata_hora_sessao timestamp,
psdata_atualizacao datetime,
psstatus_sessao varchar(20)
)
 BEGIN 
UPDATE sessao SET 
psicologo_id = pspsicologo_id,
paciente_id = pspaciente_id,
anotacoes = psanotacoes,
data_hora_sessao = psdata_hora_sessao,
status_sessao = psstatus_sessao
WHERE id = psid;
SELECT * FROM sessao WHERE id = psid;
END $$ 
DELIMITER ;


-- procedure para cancelar sessão

DELIMITER $$
create procedure ps_sessao_disable(psid int)
begin
    update sessao set status_sessao = 'CANCELADA' where id = psid;
select * from sessao where id = psid;
END $$
DELIMITER ;

-- Procedure para ativar a sessão

DELIMITER $$
create procedure ps_sessao_enable(psid int)
begin
    update sessao set status_sessao = 'AGENDADA' where id = psid;
    select * from sessao where id = psid;
END $$
DELIMITER ;


-- Procedure de confirmação da sessão

DELIMITER $$
create procedure ps_sessao_confirm(psid int)
begin
    update sessao  set status_sessao = 'REALIZADA' where id = psid;
    select * from sessao where id = psid;
END$$
DELIMITER ;

-----------------------------------------
--			    TRIGGER
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
END$$
DELIMITER ;
