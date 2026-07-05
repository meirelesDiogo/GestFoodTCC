/* =====================================================
   GestFood - Paleta de cores (baseada no protótipo)
   ===================================================== */
:root {
    --gf-dark: #9a3a25;       /* laranja-avermelhado escuro (topo login / destaques) */
    --gf-primary: #e2601f;    /* laranja principal (sidebar, botões) */
    --gf-primary-light: #f0a06b; /* laranja claro (hover, tags) */
    --gf-accent: #8b3220;     /* marrom-avermelhado (botão "Entrar no Sistema") */
    --gf-bg: #f4f5f7;         /* fundo geral das telas internas */
    --gf-white: #ffffff;
    --gf-text: #2c2c2c;
    --gf-text-light: #6b7280;
    --gf-border: #eceef1;

    --gf-blue: #2f80ed;
    --gf-yellow: #f2a93b;
    --gf-green: #27ae60;
    --gf-purple: #8e44ad;
    --gf-gray: #9aa0a6;
    --gf-red: #e74c3c;
}

* { box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: var(--gf-bg);
    color: var(--gf-text);
    margin: 0;
}

a { text-decoration: none; }

/* ---------------------------------------------------
   TELA DE LOGIN
   --------------------------------------------------- */
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--gf-dark) 0%, var(--gf-primary) 100%);
    padding: 20px;
}

.login-card {
    background: var(--gf-white);
    width: 100%;
    max-width: 420px;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
}

.login-card__header {
    background: linear-gradient(135deg, var(--gf-dark) 0%, var(--gf-primary) 100%);
    color: #fff;
    text-align: center;
    padding: 36px 24px 28px;
}

.login-card__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 14px;
    background: rgba(255,255,255,0.18);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.login-card__header h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
}

.login-card__header p {
    margin: 6px 0 0;
    font-size: 14px;
    opacity: 0.9;
}

.login-card__body {
    padding: 28px 26px 30px;
}

.form-label {
    font-weight: 600;
    font-size: 13.5px;
    display: block;
    margin-bottom: 6px;
}

.form-control, .form-select {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14.5px;
    margin-bottom: 18px;
    background: #fff;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--gf-primary);
    box-shadow: 0 0 0 3px rgba(226,96,31,0.15);
}

.btn-gf {
    display: inline-block;
    width: 100%;
    text-align: center;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, var(--gf-accent), var(--gf-dark));
    transition: filter .15s ease;
}
.btn-gf:hover { filter: brightness(1.08); }

.btn-gf-outline {
    background: transparent;
    border: 1px solid var(--gf-primary);
    color: var(--gf-primary);
}

.login-demo-note {
    margin-top: 16px;
    background: #fbf1e4;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12.5px;
    text-align: center;
    color: #8a5a2b;
}

/* ---------------------------------------------------
   LAYOUT INTERNO (sidebar + conteúdo)
   --------------------------------------------------- */
.gf-app {
    display: flex;
    min-height: 100vh;
}

.gf-sidebar {
    width: 250px;
    flex-shrink: 0;
    background: linear-gradient(180deg, var(--gf-primary) 0%, var(--gf-dark) 140%);
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 26px 0;
    position: sticky;
    top: 0;
    height: 100vh;
}

.gf-sidebar__brand {
    padding: 0 24px 22px;
    border-bottom: 1px solid rgba(255,255,255,0.18);
    margin-bottom: 16px;
}
.gf-sidebar__brand h2 { margin: 0; font-size: 20px; font-weight: 700; }
.gf-sidebar__brand span { font-size: 12.5px; opacity: 0.85; }

.gf-nav { flex: 1; }
.gf-nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255,255,255,0.92);
    padding: 12px 24px;
    font-size: 14.5px;
    font-weight: 500;
    border-left: 4px solid transparent;
}
.gf-nav a:hover { background: rgba(255,255,255,0.10); }
.gf-nav a.active {
    background: rgba(255,255,255,0.16);
    border-left: 4px solid #fff;
    font-weight: 700;
}

.gf-sidebar__footer {
    padding: 16px 24px 0;
    border-top: 1px solid rgba(255,255,255,0.18);
    font-size: 13.5px;
}
.gf-sidebar__footer .logout { display:block; margin-top:8px; color:#ffe0d2; }

.gf-main { flex: 1; padding: 26px 32px; }

.gf-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}
.gf-topbar h1 { font-size: 22px; margin: 0; }
.gf-topbar .data-atual { font-size: 13px; color: var(--gf-text-light); }

/* ---------------------------------------------------
   CARDS / KPIs
   --------------------------------------------------- */
.gf-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.gf-card {
    background: var(--gf-white);
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid var(--gf-border);
}

.gf-card--kpi { display: flex; justify-content: space-between; align-items: center; }
.gf-card--kpi .label { font-size: 13px; color: var(--gf-text-light); margin-bottom: 6px; }
.gf-card--kpi .value { font-size: 26px; font-weight: 700; }
.gf-card--kpi .icon {
    width: 46px; height: 46px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
}
.icon-blue { background: #e8f0fe; color: var(--gf-blue); }
.icon-yellow { background: #fdf0e0; color: var(--gf-yellow); }
.icon-purple { background: #f2e6f7; color: var(--gf-purple); }
.icon-green { background: #e4f7ec; color: var(--gf-green); }

.gf-panel {
    background: var(--gf-white);
    border-radius: 12px;
    padding: 20px 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid var(--gf-border);
    margin-bottom: 22px;
}
.gf-panel h3 { margin-top: 0; font-size: 16px; }

/* ---------------------------------------------------
   TABELAS / LISTAS
   --------------------------------------------------- */
.gf-table { width: 100%; border-collapse: collapse; }
.gf-table th {
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--gf-text-light);
    padding: 10px 12px;
    border-bottom: 1px solid var(--gf-border);
}
.gf-table td {
    padding: 12px;
    border-bottom: 1px solid var(--gf-border);
    font-size: 14.5px;
    vertical-align: middle;
}
.gf-table tr:hover td { background: #fdf7f2; }

/* ---------------------------------------------------
   BADGES DE STATUS
   --------------------------------------------------- */
.badge-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-recebido    { background:#e8f0fe; color: var(--gf-blue); }
.badge-em_producao { background:#fdf0e0; color: #b8730f; }
.badge-pronto       { background:#e4f7ec; color: var(--gf-green); }
.badge-em_entrega   { background:#f2e6f7; color: var(--gf-purple); }
.badge-entregue     { background:#eceef1; color: var(--gf-gray); }
.badge-cancelado    { background:#fde8e6; color: var(--gf-red); }

.dot { width:9px; height:9px; border-radius:50%; display:inline-block; margin-right:8px; }
.dot-blue{background:var(--gf-blue);} .dot-yellow{background:var(--gf-yellow);}
.dot-green{background:var(--gf-green);} .dot-purple{background:var(--gf-purple);}
.dot-gray{background:var(--gf-gray);} .dot-red{background:var(--gf-red);}

/* ---------------------------------------------------
   BOTÕES DE AÇÃO
   --------------------------------------------------- */
.btn { border:none; border-radius:8px; padding:9px 16px; font-size:13.5px; font-weight:600; cursor:pointer; }
.btn-primary { background: linear-gradient(135deg, var(--gf-primary), var(--gf-dark)); color:#fff; }
.btn-light-orange { background:#fbe4d1; color: var(--gf-accent); }
.btn-light-red { background:#fbe0dd; color: var(--gf-red); }
.btn-green { background: var(--gf-green); color:#fff; }
.btn-amber { background: var(--gf-yellow); color:#fff; }
.btn-purple { background: var(--gf-purple); color:#fff; }
.btn-outline { background:#fff; border:1px solid #ddd; color: var(--gf-text); }
.btn:hover { filter: brightness(1.06); }

.gf-search {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--gf-border);
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.product-tile {
    background: linear-gradient(135deg, var(--gf-primary), var(--gf-dark));
    color: #fff;
    border-radius: 10px 10px 0 0;
    padding: 14px 18px;
}
.product-tile-body {
    background: #fff;
    border: 1px solid var(--gf-border);
    border-top: none;
    border-radius: 0 0 10px 10px;
    padding: 14px 18px;
}

@media (max-width: 900px) {
    .gf-sidebar { position: fixed; left: -260px; z-index: 40; transition: left .2s; }
    .gf-sidebar.open { left: 0; }
    .gf-main { padding: 18px; }
}

/* ---------------------------------------------------
   ÍCONES (Bootstrap Icons) usados no lugar de emojis
   --------------------------------------------------- */
.gf-nav a i, .btn i, .gf-sidebar__footer .logout i { margin-right: 4px; }
.gf-card--kpi .icon i { font-size: 20px; }
.data-atual i { margin-right: 4px; }

/* Botão de mostrar/ocultar senha */
.toggle-senha {
    position: absolute;
    right: 10px;
    top: 11px;
    background: none;
    border: none;
    color: var(--gf-text-light);
    cursor: pointer;
    font-size: 16px;
}
.toggle-senha:hover { color: var(--gf-primary); }
.campo-senha-wrap { position: relative; }
.campo-senha-wrap .form-control { padding-right: 44px; }
