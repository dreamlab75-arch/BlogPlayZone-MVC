.open banco.db
.mode column

DROP TABLE IF EXISTS post_tag;
DROP TABLE IF EXISTS Visualiza_noticia;
DROP TABLE IF EXISTS Visualiza_post;
DROP TABLE IF EXISTS Curte_post;
DROP TABLE IF EXISTS Curte_noticia;
DROP TABLE IF EXISTS Comentarios_posts;
DROP TABLE IF EXISTS Comentarios_noticias;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS noticias;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS perfil;


CREATE TABLE perfil (
    id   INTEGER PRIMARY KEY,
    tipo TEXT    NOT NULL
);

CREATE TABLE usuarios (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    nome      TEXT    NOT NULL,
    email     TEXT    NOT NULL UNIQUE,
    senha     TEXT    NOT NULL,
    avatar    TEXT    DEFAULT 'img/avatar-default.png',
    bio       TEXT,
    perfil_id INTEGER NOT NULL DEFAULT 2,
    FOREIGN KEY (perfil_id) REFERENCES perfil(id)
);

CREATE TABLE noticias (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo          TEXT    NOT NULL,
    conteudo        TEXT    NOT NULL,
    resumo          TEXT    NOT NULL,
    imagem          TEXT    NOT NULL,
    categoria       TEXT    NOT NULL,
    data_publicacao TEXT    NOT NULL DEFAULT (datetime('now')),
    usuario_id      INTEGER NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE posts (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo          TEXT    NOT NULL,
    conteudo        TEXT    NOT NULL,
    imagem          TEXT,
    data_publicacao TEXT    NOT NULL DEFAULT (datetime('now')),
    usuario_id      INTEGER NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE tags (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT    NOT NULL UNIQUE
);

CREATE TABLE post_tag (
    post_id INTEGER NOT NULL,
    tag_id  INTEGER NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (tag_id)  REFERENCES tags(id)
);

CREATE TABLE Comentarios_noticias (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    comentario TEXT    NOT NULL,
    data       TEXT    NOT NULL DEFAULT (datetime('now')),
    noticia_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (noticia_id) REFERENCES noticias(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE Comentarios_posts (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    comentario TEXT    NOT NULL,
    data       TEXT    NOT NULL DEFAULT (datetime('now')),
    post_id    INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (post_id)    REFERENCES posts(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);


CREATE TABLE Curte_noticia (
    usuario_id INTEGER NOT NULL,
    noticia_id INTEGER NOT NULL,
    ativo      INTEGER NOT NULL DEFAULT 1, 
    PRIMARY KEY (usuario_id, noticia_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (noticia_id) REFERENCES noticias(id)
);


CREATE TABLE Curte_post (
    usuario_id INTEGER NOT NULL,
    post_id    INTEGER NOT NULL,
    ativo      INTEGER NOT NULL DEFAULT 1,
    PRIMARY KEY (usuario_id, post_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id)    REFERENCES posts(id)
);


CREATE TABLE Visualiza_noticia (
    usuario_id INTEGER NOT NULL,
    noticia_id INTEGER NOT NULL,
    PRIMARY KEY (usuario_id, noticia_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (noticia_id) REFERENCES noticias(id)
);


CREATE TABLE Visualiza_post (
    usuario_id INTEGER NOT NULL,
    post_id    INTEGER NOT NULL,
    PRIMARY KEY (usuario_id, post_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id)    REFERENCES posts(id)
);

INSERT INTO perfil (id, tipo) VALUES (1, 'adm');
INSERT INTO perfil (id, tipo) VALUES (2, 'leitor');
INSERT INTO perfil (id, tipo) VALUES (3, 'jornalista');


INSERT INTO usuarios (nome, email, senha, avatar, bio, perfil_id) VALUES (
    'Administrador',
    'admin',
    '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918',
    'https://static.wikia.nocookie.net/pkxd/images/1/15/Admin.png/revision/latest?cb=20220114170153&path-prefix=pt-br',
    NULL,
    1
);

INSERT INTO tags (nome) VALUES
('RPG'),
('Opinião'),
('FPS'),
('E-Sports'),
('Hardware'),
('Review'),
('Nintendo'),
('Indie'),
('Souls-like'),
('Open World'),
('Shooter'),
('Estratégia'),
('Plataforma'),
('Aventura'),
('Terror'),
('Luta'),
('Simulação'),
('Esporte'),
('Puzzle'),
('Roguelike'),
('MOBA'),
('Battle Royale'),
('Retro'),
('Setup'),
('Dicas'),
('PC'),
('PlayStation'),
('Xbox'),
('Mobile'),
('Multijogador');

SELECT id, nome, email, perfil_id FROM usuarios;
SELECT id, titulo, categoria FROM noticias;
SELECT id, titulo FROM posts;
SELECT p.id, p.titulo, GROUP_CONCAT(t.nome, ', ') AS tags
    FROM posts p
    JOIN post_tag po ON po.post_id = p.id
    JOIN tags t ON t.id = po.tag_id
    GROUP BY p.id;