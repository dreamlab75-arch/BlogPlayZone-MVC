<?php

namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Models\Usuario;

class AdminController
{
    // GET /admin
    public function index(): void
    {
        Auth::exigirAdm();

        $usuarios = Usuario::todos();
        $perfis   = Usuario::perfis();

        View::render('admin/index', compact('usuarios', 'perfis'), true);
    }

    // POST /admin/adicionar-usuario
    public function adicionarUsuario(): void
    {
        Auth::exigirAdm();

        $nome     = trim($_POST['nome']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $senha    = $_POST['senha']         ?? '';
        $perfilId = (int) ($_POST['perfil_id'] ?? 2);

        if (strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            Router::redirect('/admin?erro=' . urlencode('Dados inválidos. Verifique os campos.'));
        }
        if (Usuario::emailExiste($email)) {
            Router::redirect('/admin?erro=' . urlencode('E-mail já cadastrado.'));
        }

        $id = Usuario::criar($nome, $email, hash('sha256', $senha));
        Usuario::atualizarPerfil($id, $perfilId);

        Router::redirect('/admin?sucesso=' . urlencode('Usuário criado com sucesso.'));
    }

    // POST /admin/editar-usuario
    public function editarUsuario(): void
    {
        Auth::exigirAdm();

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $perfilId  = (int) ($_POST['perfil_id']  ?? 2);

        if (!$usuarioId) {
            Router::redirect('/admin?erro=' . urlencode('Usuário inválido.'));
        }

        Usuario::atualizarPerfil($usuarioId, $perfilId);
        Router::redirect('/admin?sucesso=' . urlencode('Usuário atualizado.'));
    }

    // GET /admin/apagar-usuario?id=X
    public function apagarUsuario(): void
    {
        Auth::exigirAdm();

        $usuarioId = (int) ($_GET['id'] ?? 0);
        if (!$usuarioId || $usuarioId === Auth::id()) {
            Router::redirect('/admin?erro=' . urlencode('Operação inválida.'));
        }

        Usuario::deletar($usuarioId);
        Router::redirect('/admin?sucesso=' . urlencode('Usuário removido.'));
    }
}
