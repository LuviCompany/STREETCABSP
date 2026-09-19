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
js/main.js           menu mobile, formulário → WhatsApp, scroll-reveal, contador animado
images/, logos/, videos/   assets do site
```

## Rebuild do CSS

Sempre que uma classe Tailwind nova for usada no `index.html` ou o `input.css` mudar, recompilar:

```bash
npx tailwindcss -i input.css -o css/styles.css --minify
```

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
- Cliente testa e aprova mudanças visuais olhando o resultado renderizado — sempre validar no navegador (screenshot ou inspeção via JS) antes de considerar uma tarefa visual concluída, não confiar só no código.

## Deploy

- Repositório: [github.com/LuviCompany/STREETCAB](https://github.com/LuviCompany/STREETCAB)
- Hospedagem: Vercel, projeto estático (sem framework preset, sem build command)
- `vercel.json` define cache-control para `images/`, `logos/`, `videos/`, `css/`, `js/` (1 semana com stale-while-revalidate) — o HTML fica sem cache agressivo para não atrasar propagação de conteúdo novo
