<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Post
{
    // ── Leitura ──────────────────────────────────────────────────────────────

    public static function buscarPorId(int $id): ?array
    {
        $stmt = Database::get()->prepare("
            SELECT
                p.id, p.titulo, p.conteudo, p.imagem, p.data_publicacao,
                u.nome                          AS autor,
                u.avatar                        AS avatar,
                (SELECT GROUP_CONCAT(t2.nome, ',')
                 FROM post_tag pt2
                 JOIN tags t2 ON t2.id = pt2.tag_id
                 WHERE pt2.post_id = p.id)      AS tags,
                COUNT(DISTINCT cp.usuario_id)   AS curtidas,
                COUNT(DISTINCT co.id)           AS comentarios,
                COUNT(DISTINCT vp.usuario_id)   AS visualizacoes
            FROM posts p
            JOIN usuarios u         ON u.id = p.usuario_id
            LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
            LEFT JOIN Comentarios_posts co ON co.post_id = p.id
            LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
            WHERE p.id = :id
            GROUP BY p.id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function paginados(int $pagina = 1, int $limite = 10, string $ordem = 'recentes', string $busca = '', array $tags = []): array
    {
        $offset  = ($pagina - 1) * $limite;
        $orderBy = match($ordem) {
            'antigos' => 'p.data_publicacao ASC',
            'vistos'  => 'visualizacoes DESC',
            default   => 'p.data_publicacao DESC',
        };

        [$whereSQL, $params] = self::montarWhere($busca, $tags);

        $stmt = Database::get()->prepare("
            SELECT
                p.id, p.titulo, p.conteudo, p.imagem, p.data_publicacao,
                u.nome                          AS autor,
                u.avatar                        AS avatar,
                (SELECT GROUP_CONCAT(t2.nome, ',')
                 FROM post_tag pt2
                 JOIN tags t2 ON t2.id = pt2.tag_id
                 WHERE pt2.post_id = p.id)      AS tags,
                COUNT(DISTINCT cp.usuario_id)   AS curtidas,
                COUNT(DISTINCT co.id)           AS comentarios,
                COUNT(DISTINCT vp.usuario_id)   AS visualizacoes
            FROM posts p
            JOIN usuarios u         ON u.id = p.usuario_id
            LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
            LEFT JOIN Comentarios_posts co ON co.post_id = p.id
            LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
            $whereSQL
            GROUP BY p.id
            ORDER BY $orderBy
            LIMIT :limite OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contar(string $busca = '', array $tags = []): int
    {
        [$whereSQL, $params] = self::montarWhere($busca, $tags);
        $stmt = Database::get()->prepare("
            SELECT COUNT(DISTINCT p.id) FROM posts p
            JOIN usuarios u ON u.id = p.usuario_id
            $whereSQL
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function emAlta(int $limite = 3): array
    {
        $stmt = Database::get()->prepare("
            SELECT
                p.id, p.titulo, p.conteudo, p.imagem, p.data_publicacao,
                u.nome AS autor, u.avatar AS avatar,
                (SELECT GROUP_CONCAT(t2.nome, ',')
                 FROM post_tag pt2 JOIN tags t2 ON t2.id = pt2.tag_id
                 WHERE pt2.post_id = p.id) AS tags,
                COUNT(DISTINCT cp.usuario_id) AS curtidas,
                COUNT(DISTINCT co.id)         AS comentarios,
                COUNT(DISTINCT vp.usuario_id) AS visualizacoes
            FROM posts p
            JOIN usuarios u         ON u.id = p.usuario_id
            LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
            LEFT JOIN Comentarios_posts co ON co.post_id = p.id
            LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
            GROUP BY p.id ORDER BY p.data_publicacao DESC LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function doUsuario(int $usuarioId): array
    {
        $stmt = Database::get()->prepare("
            SELECT
                p.id, p.titulo, p.conteudo, p.imagem, p.data_publicacao,
                (SELECT GROUP_CONCAT(t2.nome, ',')
                 FROM post_tag pt2 JOIN tags t2 ON t2.id = pt2.tag_id
                 WHERE pt2.post_id = p.id) AS tags,
                COUNT(DISTINCT cp.usuario_id) AS curtidas,
                COUNT(DISTINCT co.id)         AS comentarios,
                COUNT(DISTINCT vp.usuario_id) AS visualizacoes
            FROM posts p
            LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
            LEFT JOIN Comentarios_posts co ON co.post_id = p.id
            LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
            WHERE p.usuario_id = :uid
            GROUP BY p.id ORDER BY p.data_publicacao DESC
        ");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public static function comentarios(int $postId): array
    {
        $stmt = Database::get()->prepare("
            SELECT c.comentario, c.data, u.nome, u.avatar
            FROM Comentarios_posts c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.post_id = :p ORDER BY c.data DESC
        ");
        $stmt->execute([':p' => $postId]);
        return $stmt->fetchAll();
    }

    // ── Escrita ──────────────────────────────────────────────────────────────

    public static function criar(string $titulo, string $conteudo, int $usuarioId, ?string $imagem = null): int
    {
        $pdo = Database::get();
        $pdo->prepare("
            INSERT INTO posts (titulo, conteudo, imagem, usuario_id)
            VALUES (:titulo, :conteudo, :imagem, :usuario_id)
        ")->execute([
            ':titulo'     => $titulo,
            ':conteudo'   => $conteudo,
            ':imagem'     => $imagem,
            ':usuario_id' => $usuarioId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function atualizarImagem(int $id, string $imagem): void
    {
        Database::get()->prepare("UPDATE posts SET imagem = :img WHERE id = :id")
            ->execute([':img' => $imagem, ':id' => $id]);
    }

    public static function editar(int $id, string $titulo, string $conteudo, ?string $imagem): void
    {
        Database::get()->prepare("
            UPDATE posts SET titulo=:titulo, conteudo=:conteudo, imagem=:imagem WHERE id=:id
        ")->execute([':titulo' => $titulo, ':conteudo' => $conteudo, ':imagem' => $imagem, ':id' => $id]);
    }

    public static function deletar(int $id): void
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            foreach (['post_tag', 'Curte_post', 'Comentarios_posts', 'Visualiza_post'] as $tabela) {
                $col = ($tabela === 'post_tag') ? 'post_id' : (str_contains($tabela, 'post') ? 'post_id' : 'post_id');
                $pdo->prepare("DELETE FROM {$tabela} WHERE post_id = :id")->execute([':id' => $id]);
            }
            $pdo->prepare("DELETE FROM posts WHERE id = :id")->execute([':id' => $id]);
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function pertenceAo(int $postId, int $usuarioId): bool
    {
        $stmt = Database::get()->prepare("SELECT usuario_id FROM posts WHERE id = :id");
        $stmt->execute([':id' => $postId]);
        $row = $stmt->fetch();
        return $row && (int) $row['usuario_id'] === $usuarioId;
    }

    // ── Curtidas / Comentários / Visualizações ───────────────────────────────

    public static function registrarVisualizacao(int $postId, int $usuarioId): void
    {
        try {
            Database::get()->prepare("
                INSERT OR IGNORE INTO Visualiza_post (usuario_id, post_id) VALUES (:uid, :pid)
            ")->execute([':uid' => $usuarioId, ':pid' => $postId]);
        } catch (\Exception) {}
    }

    public static function toggleCurtida(int $postId, int $usuarioId): void
    {
        $pdo  = Database::get();
        $stmt = $pdo->prepare("SELECT ativo FROM Curte_post WHERE usuario_id=:u AND post_id=:p");
        $stmt->execute([':u' => $usuarioId, ':p' => $postId]);
        $row = $stmt->fetch();

        if ($row !== false) {
            $pdo->prepare("UPDATE Curte_post SET ativo=:a WHERE usuario_id=:u AND post_id=:p")
                ->execute([':a' => $row['ativo'] ? 0 : 1, ':u' => $usuarioId, ':p' => $postId]);
        } else {
            $pdo->prepare("INSERT INTO Curte_post (usuario_id, post_id, ativo) VALUES (:u,:p,1)")
                ->execute([':u' => $usuarioId, ':p' => $postId]);
        }
    }

    public static function usuarioCurtiu(int $postId, int $usuarioId): bool
    {
        $stmt = Database::get()->prepare("SELECT ativo FROM Curte_post WHERE usuario_id=:u AND post_id=:p");
        $stmt->execute([':u' => $usuarioId, ':p' => $postId]);
        $row = $stmt->fetch();
        return $row && (bool) $row['ativo'];
    }

    public static function comentar(int $postId, int $usuarioId, string $comentario): void
    {
        Database::get()->prepare("
            INSERT INTO Comentarios_posts (comentario, post_id, usuario_id) VALUES (:c, :p, :u)
        ")->execute([':c' => $comentario, ':p' => $postId, ':u' => $usuarioId]);
    }

    // ── Tags ─────────────────────────────────────────────────────────────────

    public static function sincronizarTags(int $postId, array $tagIds): void
    {
        $pdo = Database::get();
        $pdo->prepare("DELETE FROM post_tag WHERE post_id = :id")->execute([':id' => $postId]);
        if (!empty($tagIds)) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)");
            foreach ($tagIds as $tagId) {
                $stmt->execute([':post_id' => $postId, ':tag_id' => (int) $tagId]);
            }
        }
    }

    // ── Helpers internos ─────────────────────────────────────────────────────

    private static function montarWhere(string $busca, array $tags): array
    {
        $where  = [];
        $params = [];

        if ($busca) {
            $where[]          = 'p.titulo LIKE :busca';
            $params[':busca'] = "%{$busca}%";
        }

        foreach (array_values($tags) as $i => $tag) {
            $ph      = ":tag_{$i}";
            $where[] = "EXISTS (
                SELECT 1 FROM post_tag pt_f
                JOIN tags t_f ON t_f.id = pt_f.tag_id
                WHERE pt_f.post_id = p.id AND t_f.nome = {$ph}
            )";
            $params[$ph] = $tag;
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
