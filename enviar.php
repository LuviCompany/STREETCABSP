<?php
/**
 * Recebe o formulário de contato e envia por e-mail para a Streetcab.
 * O WhatsApp continua sendo o canal principal: o front-end abre o WhatsApp
 * independentemente do resultado deste script.
 *
 * Usa SMTP autenticado (via PHPMailer) em vez da função mail() do PHP:
 * em hospedagens como a Hostinger, mail() costuma "funcionar" (retorna
 * sucesso) mas a mensagem não chega às caixas de e-mail da própria conta,
 * porque o envio local não é autenticado no servidor de e-mail. As
 * credenciais reais ficam em smtp-config.php (fora do Git — veja
 * smtp-config.example.php).
 */

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

const DESTINO = 'contato@streetcab.com.br';
const NOME_REMETENTE = 'Site Streetcab';
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

// Credenciais reais (SMTP_HOST, SMTP_PORT, SMTP_SECURE, SMTP_USER, SMTP_PASS).
// Ver smtp-config.example.php para o modelo — esse arquivo não existe no Git.
$configPath = __DIR__ . '/smtp-config.php';
if (!is_file($configPath)) {
    error_log('enviar.php: smtp-config.php não encontrado — copie smtp-config.example.php e preencha a senha.');
    responder(500, false);
}
require $configPath;

require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

date_default_timezone_set('America/Sao_Paulo');

$assunto = 'Novo pedido de orçamento - ' . $nome . ' (' . $empresa . ')';

$corpo = "Novo pedido de orçamento enviado pelo site.\n\n"
    . "Nome: $nome\n"
    . "Empresa: $empresa\n"
    . "WhatsApp: $whatsapp\n"
    . "E-mail: $email\n\n"
    . "Mensagem:\n$mensagem\n\n"
    . "--\n"
    . 'Enviado em ' . date('d/m/Y H:i') . " (IP $ip)\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_USER, NOME_REMETENTE);
    $mail->addAddress(DESTINO);
    $mail->addReplyTo($email, $nome);

    $mail->isHTML(false);
    $mail->Subject = $assunto;
    $mail->Body = $corpo;

    $mail->send();

    @touch($arquivoLimite);
    responder(200, true);
} catch (PHPMailerException $e) {
    error_log('enviar.php: falha ao enviar e-mail — ' . $mail->ErrorInfo);
    responder(500, false);
}
