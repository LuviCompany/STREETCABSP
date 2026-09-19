<?php
/**
 * Recebe o formulário de contato e envia por e-mail para a Streetcab.
 * O WhatsApp continua sendo o canal principal: o front-end abre o WhatsApp
 * independentemente do resultado deste script.
 */

const DESTINO = 'contato@streetcab.com.br';
const REMETENTE = 'contato@streetcab.com.br'; // precisa ser do domínio para não cair no spam
const INTERVALO_SEGUNDOS = 20; // limite simples por IP

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(int $status, bool $ok): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok]);
    exit;
}

function limpar(string $valor, int $max, bool $multilinha = false): string
{
    $valor = trim($valor);
    $valor = $multilinha
        ? preg_replace('/[^\P{C}\n]+/u', '', str_replace("\r", '', $valor))
        : preg_replace('/\p{C}+/u', ' ', $valor);
    return mb_substr((string) $valor, 0, $max);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, false);
}

// Só aceita envios vindos do próprio site.
$origem = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origem !== '') {
    $hostOrigem = preg_replace('/^www\./', '', (string) parse_url($origem, PHP_URL_HOST));
    $hostSite = preg_replace('/^www\./', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($hostOrigem !== $hostSite) {
        responder(403, false);
    }
}

// Campo isca: pessoas não veem, robôs preenchem. Finge sucesso e descarta.
if (!empty($_POST['website'])) {
    responder(200, true);
}

// Limite de frequência por IP (se o servidor não permitir gravar, segue sem limite).
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
$arquivoLimite = sys_get_temp_dir() . '/streetcab_form_' . md5($ip);
if (is_file($arquivoLimite) && (time() - (int) filemtime($arquivoLimite)) < INTERVALO_SEGUNDOS) {
    responder(429, false);
}

$nome = limpar((string) ($_POST['nome'] ?? ''), 120);
$empresa = limpar((string) ($_POST['empresa'] ?? ''), 120);
$whatsapp = limpar((string) ($_POST['whatsapp'] ?? ''), 30);
$email = limpar((string) ($_POST['email'] ?? ''), 160);
$mensagem = limpar((string) ($_POST['mensagem'] ?? ''), 4000, true);

if ($nome === '' || $empresa === '' || $whatsapp === '' || $mensagem === '') {
    responder(422, false);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(422, false);
}

date_default_timezone_set('America/Sao_Paulo');

$assunto = 'Novo pedido de orçamento - ' . $nome . ' (' . $empresa . ')';
$assuntoCodificado = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

$corpo = "Novo pedido de orçamento enviado pelo site.\n\n"
    . "Nome: $nome\n"
    . "Empresa: $empresa\n"
    . "WhatsApp: $whatsapp\n"
    . "E-mail: $email\n\n"
    . "Mensagem:\n$mensagem\n\n"
    . "--\n"
    . 'Enviado em ' . date('d/m/Y H:i') . " (IP $ip)\n";

$de = '=?UTF-8?B?' . base64_encode('Site Streetcab') . '?=';
$cabecalhos = implode("\r\n", [
    'From: ' . $de . ' <' . REMETENTE . '>',
    'Reply-To: ' . $email, // já validado: não contém quebras de linha
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

$enviado = mail(DESTINO, $assuntoCodificado, $corpo, $cabecalhos, '-f' . REMETENTE);

if ($enviado) {
    @touch($arquivoLimite);
    responder(200, true);
}

responder(500, false);
