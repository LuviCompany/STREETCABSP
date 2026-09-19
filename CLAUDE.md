# Streetcab — Landing Page

Site institucional de página única para a Streetcab (cabeamento estruturado e infraestrutura de rede para provedores/telecom). Cliente final: Streetcab. Desenvolvido pela Luvi Company.

## Stack

HTML + CSS (Tailwind, compilado) + JavaScript puro (vanilla). **Sem framework, sem build de aplicação, sem Node em produção.** Essa é uma decisão deliberada: o projeto começou em Next.js/React e foi migrado para HTML estático a pedido do cliente, para simplificar hospedagem (qualquer servidor estático, incluindo Vercel sem configuração de build).

Não reintroduzir React/Next.js/bundlers a menos que explicitamente solicitado.

## Estrutura

```
index.html          página única, todas as seções por âncora (#inicio, #servicos, #portfolio, #diferenciais, #sobre, #contato)
css/styles.css       CSS compilado do Tailwind (gerado — não editar direto)
input.css            fonte do Tailwind (editar aqui, depois recompilar)
tailwind.config.js   tokens de cor/fonte do projeto
js/main.js           menu mobile, formulário → WhatsApp (+ e-mail via enviar.php), scroll-reveal, contador animado
enviar.php           recebe o formulário e envia e-mail para contato@streetcab.com.br (só roda na Hostinger/PHP)
images/, logos/, videos/   assets do site
```

## Rebuild do CSS

Sempre que uma classe Tailwind nova for usada no `index.html` ou o `input.css` mudar, recompilar:

```bash
npx -y tailwindcss@3 -i input.css -o css/styles.css --minify
```

Não há `package.json` nem `node_modules` (o projeto é estático), então o `npx tailwindcss` puro falha com "could not determine executable" — usar sempre a versão com `-y tailwindcss@3` (a config é do Tailwind v3).

O `tailwind.config.js` aponta `content` para `./index.html` — se novos arquivos HTML forem criados, adicionar ao content.

## Identidade visual

- Fundo escuro principal: `#0a1330` (navy) / `#070d1f` (navy mais escuro, header e footer)
- Destaque/CTA: azul `#2563eb` / `#3b82f6`
- Detalhe metálico/prata: `#c9d3e0`
- Tipografia: Space Grotesk (títulos, `font-heading`) + Inter (texto corrido, padrão)
- Estilo: industrial, tech, premium

## Preferências do cliente

- **WhatsApp é o canal principal de contato.** Todo CTA relevante (header, hero, cards, formulário, botão flutuante) deve linkar para `https://wa.me/5511965937180`, com mensagem pré-preenchida quando fizer sentido. Não trocar por formulário genérico sem fallback de WhatsApp.
- **Ícones de marca precisam ser os reais**, não substitutos genéricos. A biblioteca de ícones usada (Lucide) não tem ícones de marca (WhatsApp, Instagram) — para esses, usar SVG inline customizado (já implementado em `index.html`, reaproveitar em vez de recriar).
- **Animações devem ser sutis e premium**, não chamativas: scroll-reveal (fade + slide), contador animado nos números, hover leve nos cards, pulso no botão do WhatsApp. Implementadas em CSS/JS vanilla (sem biblioteca de animação) — manter esse padrão em novas seções.
- **Não inventar depoimentos, avaliações ou dados** — se faltar conteúdo real (ex: depoimentos de clientes), deixar o espaço de fora até o cliente enviar, não preencher com placeholder fictício apresentado como real.
- Cliente testa e aprova mudanças visuais olhando o resultado renderizado — sempre validar no navegador (screenshot ou inspeção via JS) antes de considerar uma tarefa visual concluída, não confiar só no código. Testar também em viewport mobile (375px), pois o cliente revisa bastante pelo celular.
- **Mobile importa tanto quanto desktop.** Exemplos já ajustados a pedido: itens da seção de números ficam alinhados à esquerda no mobile (centralizados só a partir de `sm:`); o vídeo do hero deve tocar sozinho como background, mudo e inline, **sem nunca exibir o player/controles nativos** (por isso `js/main.js` força `play()` e `muted`; não adicionar `controls`).
- O vídeo do hero (`videos/banner-principal.mp4`) foi recortado (1280x498) para remover barras pretas embutidas no arquivo original — se o vídeo for trocado, checar se há barras em cima/embaixo antes de publicar.
- O crédito do rodapé "Luvi Company" linka para `https://luvicompany.com` (nova aba).
- Comunicação em português do Brasil.

## Fluxo de trabalho com git

- Não fazer commit/push sem o usuário pedir. O padrão combinado: eu termino a alteração, valido no navegador, pergunto se pode subir, e só então commito e dou push na `main`.
- Mensagens de commit em inglês, curtas, explicando o "porquê".

## Deploy

- **Hospedagem oficial: Hostinger** (`public_html`). A Vercel foi só o ambiente de testes/preview. O `enviar.php` só executa em servidor com PHP — na Vercel o formulário segue funcionando pelo WhatsApp, mas sem o e-mail.
- **Formulário → e-mail:** `js/main.js` faz `POST` para `enviar.php` (fire-and-forget, com honeypot `website`) e abre o WhatsApp em seguida; o WhatsApp continua sendo o canal principal e abre mesmo se o e-mail falhar. Destino/remetente ficam nas constantes no topo do `enviar.php` (o remetente precisa ser do domínio para não cair no spam). Não dá para testar o envio localmente (sem PHP) — validar no site da Hostinger.

- Repositório: [github.com/LuviCompany/STREETCABSP](https://github.com/LuviCompany/STREETCABSP) (renomeado de `STREETCAB`; o remote local já aponta para o novo nome)
- Hospedagem: Vercel, projeto estático (sem framework preset, sem build command)
- `vercel.json` define cache-control para `images/`, `logos/`, `videos/`, `css/`, `js/` (1 semana com stale-while-revalidate) — o HTML fica sem cache agressivo para não atrasar propagação de conteúdo novo
- **Cache-busting:** `css/styles.css` e `js/main.js` têm cache de 1 dia no navegador e são referenciados no `index.html` com `?v=AAAAMMDD`. Sempre que recompilar o CSS ou mudar o JS, atualizar esse número nos dois links — senão quem já visitou o site continua vendo o CSS/JS antigo (foi o que quebrou o formulário no celular/desktop do cliente após o deploy da seção de contato).
