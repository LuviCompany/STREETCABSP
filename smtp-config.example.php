<?php
/**
 * Modelo de configuração SMTP para o enviar.php.
 *
 * Como usar:
 * 1. No Gerenciador de Arquivos da Hostinger, copie este arquivo para
 *    "smtp-config.php" (mesma pasta, public_html).
 * 2. Preencha SMTP_PASS com a senha da caixa contato@streetcab.com.br
 *    (a senha criada em E-mails > Contas de e-mail no hPanel).
 * 3. NÃO envie smtp-config.php para o GitHub — o repositório é público e
 *    o .gitignore já bloqueia esse arquivo por nome, mas confira antes de
 *    subir qualquer alteração manual.
 *
 * Host e porta: confira em hPanel > E-mails > Contas de e-mail > Configurar
 * cliente de e-mail. Os valores abaixo são os mais comuns na Hostinger.
 */

const SMTP_HOST = 'smtp.hostinger.com';
const SMTP_PORT = 465;
const SMTP_SECURE = 'ssl'; // 'ssl' para a porta 465, 'tls' para a porta 587
const SMTP_USER = 'contato@streetcab.com.br';
const SMTP_PASS = 'coloque-a-senha-da-caixa-aqui';
