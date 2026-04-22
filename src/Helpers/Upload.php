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
     * Salva arquivo de imagem enviado via $_FILES.
     *
     * @param  string $campo        Nome do campo em $_FILES
     * @param  string $prefixo      Ex: 'post', 'noticia', 'avatar'
     * @param  int    $id           ID do registro
     * @param  string $pasta        Caminho absoluto da pasta de destino
     * @return string|null          Path relativo à raiz do projeto, ou null se sem upload
     */
    public static function salvar(string $campo, string $prefixo, int $id, string $pasta): ?string
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

        $extensoes    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $ext          = $extensoes[$mime];
        $nomeArquivo  = "{$prefixo}_{$id}.{$ext}";
        $destino      = rtrim($pasta, '/') . '/' . $nomeArquivo;

        // Apaga versão anterior (qualquer extensão)
        foreach ($extensoes as $e) {
            $antigo = rtrim($pasta, '/') . "/{$prefixo}_{$id}.{$e}";
            if (file_exists($antigo)) {
                @unlink($antigo);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException('Falha ao salvar o arquivo no servidor.');
        }

        // Retorna path relativo à raiz (sem / inicial)
        $raiz = str_replace('\\', '/', dirname(__DIR__, 2) . '/public');
        $dest = str_replace('\\', '/', realpath($destino));
        // O path salvo no banco é relativo a public/
        return 'uploads/' . $prefixo . 's/' . $nomeArquivo;
    }
}
