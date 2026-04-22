<?php

namespace App\Models;

use App\Core\Database;

class Usuario
{
    public static function buscarPorId(int $id): ?array
    {
        $stmt = Database::get()->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function buscarPorLogin(string $login, string $senhaHash): ?array
    {
        $stmt = Database::get()->prepare("
            SELECT u.*, p.tipo AS perfil_tipo
            FROM usuarios u
            JOIN perfil p ON p.id = u.perfil_id
            WHERE (u.email = :login OR u.nome = :login) AND u.senha = :senha
        ");
        $stmt->execute([':login' => $login, ':senha' => $senhaHash]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExiste(string $email, int $exceto = 0): bool
    {
        $stmt = Database::get()->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :exceto");
        $stmt->execute([':email' => $email, ':exceto' => $exceto]);
        return (bool) $stmt->fetch();
    }

    public static function criar(string $nome, string $email, string $senhaHash, ?string $avatar = null): int
    {
        $pdo = Database::get();
        $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, avatar, perfil_id)
            VALUES (:nome, :email, :senha, :avatar, 2)
        ")->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, ':avatar' => $avatar]);
        return (int) $pdo->lastInsertId();
    }

    public static function atualizarAvatar(int $id, string $avatar): void
    {
        Database::get()->prepare("UPDATE usuarios SET avatar = :avatar WHERE id = :id")
            ->execute([':avatar' => $avatar, ':id' => $id]);
    }

    public static function editar(int $id, string $nome, string $email, ?string $avatar, ?string $bio): void
    {
        Database::get()->prepare("
            UPDATE usuarios SET nome=:nome, email=:email, avatar=:avatar, bio=:bio WHERE id=:id
        ")->execute([':nome' => $nome, ':email' => $email, ':avatar' => $avatar, ':bio' => $bio, ':id' => $id]);
    }

    public static function trocarSenha(int $id, string $senhaHash): void
    {
        Database::get()->prepare("UPDATE usuarios SET senha=:senha WHERE id=:id")
            ->execute([':senha' => $senhaHash, ':id' => $id]);
    }

    public static function verificarSenha(int $id, string $senhaHash): bool
    {
        $stmt = Database::get()->prepare("SELECT senha FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row && $row['senha'] === $senhaHash;
    }

    public static function todos(): array
    {
        return Database::get()->query("
            SELECT u.id, u.nome, u.email, p.tipo AS perfil_tipo, u.perfil_id
            FROM usuarios u JOIN perfil p ON p.id = u.perfil_id
            ORDER BY u.id ASC
        ")->fetchAll();
    }

    public static function perfis(): array
    {
        return Database::get()->query("SELECT id, tipo FROM perfil ORDER BY tipo")->fetchAll();
    }

    public static function atualizarPerfil(int $id, int $perfilId): void
    {
        Database::get()->prepare("UPDATE usuarios SET perfil_id=:pid WHERE id=:id")
            ->execute([':pid' => $perfilId, ':id' => $id]);
    }

    public static function deletar(int $id): void
    {
        Database::get()->prepare("DELETE FROM usuarios WHERE id = :id")->execute([':id' => $id]);
    }
}
