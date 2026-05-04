<?php

namespace App\Helpers;

class Upload
{
    /**
     * Converte path salvo no banco em URL absoluta para o browser.
     * Paths locais recebem '/' na frente; URLs externas passam direto.
     */
    public static function url(string $imagem, string $fallback = ''): string
    {
        if (empty(trim($imagem))) {
            return $fallback;
        }

        if (str_starts_with($imagem, 'http://') || str_starts_with($imagem, 'https://')) {
            return $imagem;
        }

        return '/' . ltrim(str_replace('\\', '/', $imagem), '/');
    }

    /**
     * Retorna atributo onerror HTML seguro (sem loop infinito).
     */
    public static function onerror(string $fallback = ''): string
    {
        if (empty($fallback)) {
            return "onerror=\"this.onerror=null;this.style.display='none';\"";
        }
        $fb = self::url($fallback);
        return "onerror=\"this.onerror=null;this.src='" . htmlspecialchars($fb, ENT_QUOTES) . "';\"";
    }

    /**
     * Retorna o caminho absoluto da pasta public/ do projeto.
     * Funciona independente de onde o script está sendo chamado.
     */
    public static function publicPath(): string
    {
        // __DIR__ = src/Helpers/
        // sobe 2 níveis: src/ -> raiz do projeto -> entra em public/
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
    }

    public static function salvar(string $campo, string $prefixo, int $id, string $subpasta): ?string
    {
        if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$campo];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erro no upload (código ' . $file['error'] . ').');
        }

        $mimePermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $mimePermitidos)) {
            throw new \RuntimeException('Formato não permitido. Use JPG, PNG, WEBP ou GIF.');
        }

        $extensoes   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $ext         = $extensoes[$mime];
        $nomeArquivo = "{$prefixo}_{$id}.{$ext}";

        $pastaAbs = self::publicPath() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subpasta;

        if (!is_dir($pastaAbs)) {
            if (!mkdir($pastaAbs, 0755, true)) {
                throw new \RuntimeException("Não foi possível criar a pasta de upload: {$pastaAbs}");
            }
        }

        $destino = $pastaAbs . DIRECTORY_SEPARATOR . $nomeArquivo;

        foreach ($extensoes as $e) {
            $antigo = $pastaAbs . DIRECTORY_SEPARATOR . "{$prefixo}_{$id}.{$e}";
            if (file_exists($antigo)) {
                @unlink($antigo);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException(
                "Falha ao salvar o arquivo. Verifique se a pasta tem permissão de escrita: {$pastaAbs}"
            );
        }

        return 'uploads/' . $subpasta . '/' . $nomeArquivo;
    }
}
