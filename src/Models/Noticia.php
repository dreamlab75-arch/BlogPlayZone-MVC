<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Noticia
{
    public static function buscarPorId(int $id): ?array
    {
        $stmt = Database::get()->prepare("
            SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
                   COUNT(DISTINCT cn.usuario_id) AS curtidas,
                   COUNT(DISTINCT co.id)         AS comentarios
            FROM noticias n
            JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
            LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
            LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
            WHERE n.id = :id GROUP BY n.id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function paginadas(int $pagina = 1, int $limite = 10, string $categoria = '', string $busca = ''): array
    {
        $offset = ($pagina - 1) * $limite;
        [$where, $params] = self::montarWhere($categoria, $busca);

        $stmt = Database::get()->prepare("
            SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
                   COUNT(DISTINCT cn.usuario_id) AS curtidas,
                   COUNT(DISTINCT co.id)         AS comentarios
            FROM noticias n
            JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
            LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
            LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
            $where
            GROUP BY n.id ORDER BY n.data_publicacao DESC
            LIMIT :limite OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contar(string $categoria = '', string $busca = ''): int
    {
        [$where, $params] = self::montarWhere($categoria, $busca);
        $stmt = Database::get()->prepare("SELECT COUNT(*) FROM noticias n $where");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public static function recentes(int $limite = 5): array
    {
        $stmt = Database::get()->prepare("
            SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
                   COUNT(DISTINCT cn.usuario_id) AS curtidas,
                   COUNT(DISTINCT co.id)         AS comentarios
            FROM noticias n
            JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
            LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
            LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
            GROUP BY n.id ORDER BY n.data_publicacao DESC LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function destaquesSemana(int $limite = 3): array
    {
        $stmt = Database::get()->prepare("
            SELECT n.*, u.nome AS autor_nome, u.avatar AS autor_avatar,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
                   COUNT(DISTINCT cn.usuario_id) AS curtidas,
                   COUNT(DISTINCT co.id)         AS comentarios
            FROM noticias n
            JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
            LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
            LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
            WHERE n.data_publicacao >= datetime('now', '-7 days')
            GROUP BY n.id ORDER BY visualizacoes DESC LIMIT :limite
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function doUsuario(int $usuarioId): array
    {
        $stmt = Database::get()->prepare("
            SELECT n.*, COUNT(DISTINCT vn.usuario_id) AS visualizacoes,
                   COUNT(DISTINCT cn.usuario_id) AS curtidas,
                   COUNT(DISTINCT co.id)         AS comentarios
            FROM noticias n
            LEFT JOIN Visualiza_noticia vn    ON vn.noticia_id = n.id
            LEFT JOIN Curte_noticia cn        ON cn.noticia_id = n.id AND cn.ativo = 1
            LEFT JOIN Comentarios_noticias co ON co.noticia_id = n.id
            WHERE n.usuario_id = :uid
            GROUP BY n.id ORDER BY n.data_publicacao DESC
        ");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public static function maisLidas(int $limite = 5, int $exceto = 0): array
    {
        $stmt = Database::get()->prepare("
            SELECT n.id, n.titulo, n.categoria,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes
            FROM noticias n
            LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
            WHERE n.id != :exceto
            GROUP BY n.id ORDER BY visualizacoes DESC LIMIT :limite
        ");
        $stmt->bindValue(':exceto', $exceto, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function relacionadas(int $noticiaId, string $categoria, int $limite = 3): array
    {
        $stmt = Database::get()->prepare("
            SELECT id, titulo, imagem, categoria, data_publicacao
            FROM noticias WHERE categoria = :cat AND id != :id
            ORDER BY data_publicacao DESC LIMIT :limite
        ");
        $stmt->bindValue(':cat', $categoria);
        $stmt->bindValue(':id', $noticiaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function categorias(): array
    {
        return Database::get()
            ->query("SELECT DISTINCT categoria FROM noticias ORDER BY categoria ASC")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function comentarios(int $noticiaId): array
    {
        $stmt = Database::get()->prepare("
            SELECT c.comentario, c.data, u.nome, u.avatar
            FROM Comentarios_noticias c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.noticia_id = :n ORDER BY c.data DESC
        ");
        $stmt->execute([':n' => $noticiaId]);
        return $stmt->fetchAll();
    }

    // ── Escrita ──────────────────────────────────────────────────────────────

    public static function criar(array $dados): int
    {
        $pdo = Database::get();
        $pdo->prepare("
            INSERT INTO noticias (titulo, resumo, conteudo, imagem, categoria, usuario_id)
            VALUES (:titulo, :resumo, :conteudo, :imagem, :categoria, :usuario_id)
        ")->execute($dados);
        return (int) $pdo->lastInsertId();
    }

    public static function atualizarImagem(int $id, string $imagem): void
    {
        Database::get()->prepare("UPDATE noticias SET imagem = :img WHERE id = :id")
            ->execute([':img' => $imagem, ':id' => $id]);
    }

    public static function editar(int $id, array $dados): void
    {
        Database::get()->prepare("
            UPDATE noticias
            SET titulo=:titulo, resumo=:resumo, conteudo=:conteudo, imagem=:imagem, categoria=:categoria
            WHERE id=:id
        ")->execute(array_merge($dados, [':id' => $id]));
    }

    public static function deletar(int $id): void
    {
        $pdo = Database::get();
        $pdo->prepare("DELETE FROM Comentarios_noticias WHERE noticia_id = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM Curte_noticia WHERE noticia_id = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM Visualiza_noticia WHERE noticia_id = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM noticias WHERE id = :id")->execute([':id' => $id]);
    }

    public static function pertenceAo(int $noticiaId, int $usuarioId): bool
    {
        $stmt = Database::get()->prepare("SELECT usuario_id FROM noticias WHERE id = :id");
        $stmt->execute([':id' => $noticiaId]);
        $row = $stmt->fetch();
        return $row && (int) $row['usuario_id'] === $usuarioId;
    }

    public static function registrarVisualizacao(int $noticiaId, int $usuarioId): void
    {
        try {
            Database::get()->prepare("
                INSERT OR IGNORE INTO Visualiza_noticia (usuario_id, noticia_id) VALUES (:uid, :nid)
            ")->execute([':uid' => $usuarioId, ':nid' => $noticiaId]);
        } catch (\Exception) {}
    }

    public static function toggleCurtida(int $noticiaId, int $usuarioId): void
    {
        $pdo  = Database::get();
        $stmt = $pdo->prepare("SELECT ativo FROM Curte_noticia WHERE usuario_id=:u AND noticia_id=:n");
        $stmt->execute([':u' => $usuarioId, ':n' => $noticiaId]);
        $row = $stmt->fetch();

        if ($row !== false) {
            $pdo->prepare("UPDATE Curte_noticia SET ativo=:a WHERE usuario_id=:u AND noticia_id=:n")
                ->execute([':a' => $row['ativo'] ? 0 : 1, ':u' => $usuarioId, ':n' => $noticiaId]);
        } else {
            $pdo->prepare("INSERT INTO Curte_noticia (usuario_id, noticia_id, ativo) VALUES (:u,:n,1)")
                ->execute([':u' => $usuarioId, ':n' => $noticiaId]);
        }
    }

    public static function usuarioCurtiu(int $noticiaId, int $usuarioId): bool
    {
        $stmt = Database::get()->prepare("SELECT ativo FROM Curte_noticia WHERE usuario_id=:u AND noticia_id=:n");
        $stmt->execute([':u' => $usuarioId, ':n' => $noticiaId]);
        $row = $stmt->fetch();
        return $row && (bool) $row['ativo'];
    }

    public static function comentar(int $noticiaId, int $usuarioId, string $comentario): void
    {
        Database::get()->prepare("
            INSERT INTO Comentarios_noticias (comentario, noticia_id, usuario_id) VALUES (:c, :n, :u)
        ")->execute([':c' => $comentario, ':n' => $noticiaId, ':u' => $usuarioId]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function categoriaBadge(string $categoria): string
    {
        $mapa = [
            'lançamento' => 'bg-primary text-white',  'rumor'       => 'bg-warning text-dark',
            'análise'    => 'bg-info text-white',      'urgente'     => 'bg-danger text-white',
            'review'     => 'bg-success text-white',   'prévia'      => 'bg-purple text-white',
            'atualização'=> 'bg-secondary text-white', 'evento'      => 'bg-dark text-white',
            'hardware'   => 'bg-warning text-dark',    'negócios'    => 'bg-danger text-white',
            'curiosidade'=> 'bg-info text-white',      'lista'       => 'bg-primary text-white',
        ];
        return $mapa[mb_strtolower(trim($categoria))] ?? 'bg-secondary text-white';
    }

    public static function categoriaCor(string $categoria): string
    {
        $mapa = [
            'lançamento' => '#0d6efd', 'rumor'       => '#f59e0b',
            'análise'    => '#0dcaf0', 'urgente'     => '#ef4444',
            'review'     => '#22c55e', 'prévia'      => '#8b5cf6',
            'atualização'=> '#6c757d', 'evento'      => '#1a0a4a',
            'hardware'   => '#f59e0b', 'negócios'    => '#ef4444',
            'curiosidade'=> '#0dcaf0', 'lista'       => '#0d6efd',
        ];
        return $mapa[mb_strtolower(trim($categoria))] ?? '#611DF2';
    }

    public static function categoriasValidas(): array
    {
        return ['lançamento','rumor','análise','urgente','review','prévia','atualização','evento','hardware','negócios','curiosidade','lista'];
    }

    private static function montarWhere(string $categoria, string $busca): array
    {
        $where  = [];
        $params = [];
        if ($categoria) { $where[] = 'n.categoria = :categoria'; $params[':categoria'] = $categoria; }
        if ($busca)     { $where[] = '(n.titulo LIKE :busca OR n.resumo LIKE :busca)'; $params[':busca'] = "%{$busca}%"; }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
