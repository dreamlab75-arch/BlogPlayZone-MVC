.open banco.db
.mode column

-- ========== LIMPEZA ==========
DROP TABLE IF EXISTS post_tag;
DROP TABLE IF EXISTS Visualiza;
DROP TABLE IF EXISTS Curte;
DROP TABLE IF EXISTS Comentarios_posts;
DROP TABLE IF EXISTS Comentarios_noticias;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS noticias;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS perfil;

-- ========== PERFIL ==========
CREATE TABLE perfil (
    id   INTEGER PRIMARY KEY,
    tipo TEXT    NOT NULL
);

-- ========== USUARIOS ==========
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

-- ========== NOTICIAS ==========
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

-- ========== POSTS ==========
CREATE TABLE posts (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo          TEXT    NOT NULL,
    conteudo        TEXT    NOT NULL,
    imagem          TEXT,
    data_publicacao TEXT    NOT NULL DEFAULT (datetime('now')),
    usuario_id      INTEGER NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ========== TAGS ==========
CREATE TABLE tags (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT    NOT NULL UNIQUE
);

-- ========== POST_TAG (Post <-> Tag) ==========
CREATE TABLE post_tag (
    post_id INTEGER NOT NULL,
    tag_id  INTEGER NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (tag_id)  REFERENCES tags(id)
);

-- ========== COMENTARIOS_NOTICIAS ==========
CREATE TABLE Comentarios_noticias (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    comentario TEXT    NOT NULL,
    data       TEXT    NOT NULL DEFAULT (datetime('now')),
    noticia_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (noticia_id) REFERENCES noticias(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ========== COMENTARIOS_POSTS ==========
CREATE TABLE Comentarios_posts (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    comentario TEXT    NOT NULL,
    data       TEXT    NOT NULL DEFAULT (datetime('now')),
    post_id    INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (post_id)    REFERENCES posts(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ========== CURTE ==========
-- Noticia
CREATE TABLE Curte_noticia (
    usuario_id INTEGER NOT NULL,
    noticia_id INTEGER NOT NULL,
    ativo      INTEGER NOT NULL DEFAULT 1, -- 1 = curtido, 0 = descurtido
    PRIMARY KEY (usuario_id, noticia_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (noticia_id) REFERENCES noticias(id)
);

-- Post
CREATE TABLE Curte_post (
    usuario_id INTEGER NOT NULL,
    post_id    INTEGER NOT NULL,
    ativo      INTEGER NOT NULL DEFAULT 1,
    PRIMARY KEY (usuario_id, post_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id)    REFERENCES posts(id)
);

-- ========== VISUALIZA ==========
-- Noticia
CREATE TABLE Visualiza_noticia (
    usuario_id INTEGER NOT NULL,
    noticia_id INTEGER NOT NULL,
    PRIMARY KEY (usuario_id, noticia_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (noticia_id) REFERENCES noticias(id)
);

-- Post
CREATE TABLE Visualiza_post (
    usuario_id INTEGER NOT NULL,
    post_id    INTEGER NOT NULL,
    PRIMARY KEY (usuario_id, post_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (post_id)    REFERENCES posts(id)
);

-- ========== PERFIS PADRÃO ==========
INSERT INTO perfil (id, tipo) VALUES (1, 'adm');
INSERT INTO perfil (id, tipo) VALUES (2, 'leitor');
INSERT INTO perfil (id, tipo) VALUES (3, 'jornalista');

-- ========== USUARIO ADM FIXO ==========
-- senha: admin
INSERT INTO usuarios (nome, email, senha, avatar, bio, perfil_id) VALUES (
    'Administrador',
    'admin',
    '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918',
    'https://static.wikia.nocookie.net/pkxd/images/1/15/Admin.png/revision/latest?cb=20220114170153&path-prefix=pt-br',
    NULL,
    1
);

-- ========== INSERTS NOTICIAS ==========
INSERT INTO noticias (titulo, conteudo, resumo, imagem, categoria, usuario_id) VALUES
(
    'Sony anuncia showcase com novidades do PS5',
    'A Sony confirmou um novo PlayStation Showcase para o próximo mês. O evento promete revelar os principais jogos exclusivos do segundo semestre.

Entre os títulos esperados estão sequências de franquias consagradas. Fontes indicam que pelo menos dois jogos inéditos serão revelados.

O showcase deve durar 90 minutos e será seguido por um State of Play focado em jogos indie.

Fãs aguardam notícias sobre God of War, Horizon e uma possível surpresa da franquia Twisted Metal.',
    'Sony confirma novo showcase com exclusivos do PS5 para o próximo mês.',
    'img/2014-08-20-tw3-gameplay.webp',
    'evento',
    1
),
(
    'Novo jogo da franquia Final Fantasy ganha data',
    'A Square Enix confirmou a data de lançamento do novo Final Fantasy em evento em Tóquio. O jogo chega ao PS5, Xbox Series X e PC simultaneamente.

O produtor revelou que a equipe trabalhou por mais de quatro anos no projeto.

A trilha sonora ficou a cargo de Nobuo Uematsu, que retorna após alguns títulos de ausência.

O jogo terá legendas em português do Brasil.',
    'Square Enix revela data de lançamento do novo Final Fantasy para múltiplas plataformas.',
    'img/Prancheta 1.svg',
    'lançamento',
    1
),
(
    'Fortnite recebe novo mapa e modo de jogo',
    'A Epic Games lançou uma atualização massiva com novo mapa e modo inédito.

O novo mapa conta com biomas variados: área urbana destruída, floresta densa e região ártica.

O novo modo Zero Build Ranked oferece partidas competitivas sem construção.

A atualização também trouxe novos personagens colaborativos de franquias populares.',
    'Epic Games lança atualização com novo mapa e modo competitivo sem construção.',
    'img/Prancheta 2.svg',
    'atualização',
    1
),
(
    'Nintendo pode anunciar sucessor do Switch em breve',
    'Rumores consistentes apontam para um anúncio iminente do sucessor do Switch.

O novo hardware deve manter o conceito híbrido com melhorias significativas de desempenho.

A tela portátil deve receber upgrade com OLED maior e taxa de atualização de 120Hz.

Desenvolvedores third-party já estariam recebendo kits de desenvolvimento há alguns meses.',
    'Rumores indicam anúncio iminente do sucessor do Nintendo Switch com tela OLED maior.',
    'img/2014-08-20-tw3-gameplay.webp',
    'rumor',
    1
),
(
    'Como a IA está mudando o desenvolvimento de jogos',
    'A inteligência artificial está transformando o desenvolvimento de jogos, desde geração de assets até comportamento de NPCs.

Ferramentas de IA permitem que artistas criem variações de assets em minutos.

Modelos de linguagem estão sendo usados para criar personagens que respondem de forma mais natural.

O debate ético sobre o impacto da IA nos empregos da indústria continua intenso.',
    'IA transforma desenvolvimento de jogos com geração de assets e NPCs mais inteligentes.',
    'img/2014-08-20-tw3-gameplay.webp',
    'análise',
    1
),
(
    'Baldur''s Gate 3 recebe patch com novos finais',
    'A Larian Studios surpreendeu a comunidade lançando um patch não anunciado que adiciona variações nos finais do jogo.

As mudanças afetam principalmente as rotas dos companheiros e o epílogo do personagem principal.

O estúdio afirmou que este será um dos últimos grandes updates do jogo antes de focar em novos projetos.',
    'Larian adiciona novos finais e variações de epílogo em patch surpresa para Baldur''s Gate 3.',
    'img/2014-08-20-tw3-gameplay.webp',
    'atualização',
    1
),
(
    'Hollow Knight: Silksong finalmente ganha janela de lançamento',
    'A Team Cherry confirmou em entrevista que Silksong está em fase final de desenvolvimento e deve chegar ainda este ano.

O jogo foi anunciado em 2019 e se tornou um dos títulos mais aguardados do cenário indie.

Nenhuma data específica foi confirmada, mas a desenvolvedora garantiu que não haverá mais adiamentos.',
    'Team Cherry confirma que Hollow Knight: Silksong está em fase final e chega ainda este ano.',
    'img/2014-08-20-tw3-gameplay.webp',
    'prévia',
    1
),
(
    'GTA VI tem novo trailer vazado antes do lançamento oficial',
    'Imagens e clipes do novo trailer de GTA VI vazaram nas redes sociais horas antes da Rockstar publicar oficialmente.

O material mostra cenas da protagonista Lucia em Miami, confirmando o retorno da cidade de Vice City.

A Rockstar não comentou o vazamento mas removeu os vídeos rapidamente das plataformas.',
    'Trailer de GTA VI vaza antes do lançamento oficial mostrando Vice City e a protagonista Lucia.',
    'img/2014-08-20-tw3-gameplay.webp',
    'rumor',
    1
),
(
    'PS5 Pro — vale a pena o upgrade? Analisamos',
    'Testamos o PS5 Pro por duas semanas em títulos como Spider-Man 2, Demon''s Souls e Ratchet & Clank para entender se o upgrade faz sentido.

A melhoria de desempenho é real: jogos que rodavam a 30fps agora mantêm 60fps estáveis no modo qualidade.

Para quem já tem um PS5 padrão, a decisão depende muito de quantas horas por semana você joga.',
    'Testamos o PS5 Pro por duas semanas para saber se o upgrade vale o investimento.',
    'img/2014-08-20-tw3-gameplay.webp',
    'review',
    1
),
(
    'Microsoft demite mais 650 funcionários da divisão de games',
    'A Microsoft anunciou uma nova rodada de demissões afetando estúdios como Bethesda e 343 Industries.

É a terceira onda de cortes na divisão Xbox nos últimos 18 meses, somando mais de 2.500 postos eliminados.

Desenvolvedores afetados usaram redes sociais para anunciar as demissões e buscar novas oportunidades.',
    'Microsoft corta mais 650 cargos na divisão Xbox, afetando Bethesda e 343 Industries.',
    'img/2014-08-20-tw3-gameplay.webp',
    'negócios',
    1
);

-- ========== INSERTS POSTS ==========
-- usuario_id = 1 (adm) como autor temporário até usuários se cadastrarem
INSERT INTO posts (titulo, conteudo, usuario_id) VALUES
(
    'Por que Elden Ring definiu uma nova era para os RPGs',
    'Após mais de 200 horas de jogo, posso afirmar com segurança que Elden Ring não é apenas mais um souls-like. A FromSoftware conseguiu criar algo verdadeiramente único.

A liberdade de exploração é o que mais me surpreendeu. Diferente dos Souls anteriores, aqui você pode ir em qualquer direção — e vai morrer muito por isso.

O sistema de combate evoluiu sem perder a essência. As Ashes of War adicionam uma camada de customização que permite adaptar sua build ao seu estilo de jogo.

Se você nunca jogou um Souls-like, Elden Ring pode ser intimidador no início. Mas persista — a satisfação de derrotar um boss que te matou 30 vezes não tem comparação.',
    1
),
(
    'Como melhorei meu rank em Valorant: 5 dicas práticas',
    'Depois de ficar travada no Prata por quase quatro meses, finalmente consegui subir para Diamante. Não foi sorte — foi mudança de mentalidade.

1. Comunicação antes de tudo. Callouts claros, sem tilt, sem toxicidade.
2. Jogue menos agentes, domine mais. Foquei em Jett e Reyna e minha consistência aumentou muito.
3. Revise seus próprios replays. 30 minutos revisando suas mortes te ensina mais do que 5 partidas novas.
4. Mira antes de movimentação. Posicione a mira onde o inimigo vai aparecer antes de avançar.
5. Saiba quando parar. Se perdeu 3 partidas seguidas, feche o jogo.',
    1
),
(
    'Vale a pena montar um PC gamer em 2025? Minha experiência',
    'Gastei R$ 8.000 montando meu setup dos sonhos. Spoiler: valeu a pena, mas com algumas ressalvas.

A escolha dos componentes foi a parte mais trabalhosa. No final optei por uma RTX 4070 com um Ryzen 7 7700X.

A montagem foi mais tranquila do que eu esperava. Com tutoriais no YouTube e paciência, qualquer pessoa consegue.

O desempenho final superou minhas expectativas. Consigo rodar Cyberpunk 2077 em ultra com ray tracing a 60fps estáveis.',
    1
),
(
    'Zelda: Tears of the Kingdom — um ano depois ainda impressiona',
    'Revisitei Hyrule após 12 meses e descobri que ainda havia segredos que não tinha encontrado.

O sistema Ultrahand continua sendo uma das mecânicas mais criativas da geração.

Os Templos são superiores aos Santuários do jogo anterior. Cada um tem identidade visual única e puzzles inteligentes.

A história, contada através das memórias, entrega uma conclusão emocionante.',
    1
),
(
    'Por que os jogos indie estão salvando a indústria',
    'Enquanto as grandes publishers apostam em remakes, os estúdios independentes entregam as experiências mais originais dos últimos anos.

Balatro redefiniu o que um jogo de cartas pode ser. Hades II continua evoluindo o roguelike de formas que nenhum estúdio grande ousaria tentar.

O modelo independente permite riscos criativos impossíveis dentro de grandes corporações.

A ascensão das plataformas digitais democratizou a distribuição.',
    1
);

-- ========== INSERTS TAGS ==========
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

-- ========== INSERTS POST_TAG (Post <-> Tag) ==========
INSERT INTO post_tag (post_id, tag_id) VALUES
(1, 1),  -- Elden Ring -> RPG
(1, 9),  -- Elden Ring -> Souls-like
(2, 3),  -- Valorant -> FPS
(2, 4),  -- Valorant -> E-Sports
(3, 5),  -- PC Gamer -> Hardware
(3, 24), -- PC Gamer -> Setup
(4, 7),  -- Zelda -> Nintendo
(4, 14), -- Zelda -> Aventura
(5, 8),  -- Indie -> Indie
(5, 20); -- Indie -> Roguelike

-- ========== VERIFICAÇÕES ==========
SELECT id, nome, email, perfil_id FROM usuarios;
SELECT id, titulo, categoria FROM noticias;
SELECT id, titulo FROM posts;
SELECT p.id, p.titulo, GROUP_CONCAT(t.nome, ', ') AS tags
    FROM posts p
    JOIN post_tag po ON po.post_id = p.id
    JOIN tags t ON t.id = po.tag_id
    GROUP BY p.id;