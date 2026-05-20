<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Mercado — FFXIV</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f6fb; font-family: Arial, sans-serif; }
        .wrap { max-width: 580px; margin: 32px auto; background: #fff; border: 1px solid #dde3ef; }
        .header { background: #0e1128; padding: 28px 32px; border-bottom: 2px solid #f0c030; }
        .header-title { color: #f0c030; font-size: 13px; letter-spacing: 0.18em; text-transform: uppercase; margin: 0 0 4px; }
        .header-sub { color: #8899cc; font-size: 11px; margin: 0; }
        .body { padding: 28px 32px; }
        .item-row { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .item-icon { width: 40px; height: 40px; border: 1px solid #dde3ef; flex-shrink: 0; }
        .item-name { font-size: 18px; font-weight: bold; color: #1a2040; }
        .item-server { font-size: 12px; color: #6b7cb0; margin-top: 2px; }
        .metrics { border: 1px solid #e5eaf5; margin-bottom: 24px; }
        .metric-row { display: flex; justify-content: space-between; align-items: center;
                      padding: 10px 16px; border-bottom: 1px solid #e5eaf5; }
        .metric-row:last-child { border-bottom: none; }
        .metric-label { font-size: 12px; color: #6b7cb0; }
        .metric-value { font-size: 13px; font-weight: bold; color: #1a2040; }
        .metric-value.profit { color: #16a34a; }
        .metric-value.cost    { color: #dc2626; }
        .thresholds { background: #f8f9fd; border: 1px solid #e5eaf5; padding: 12px 16px; margin-bottom: 24px; font-size: 12px; color: #6b7cb0; }
        .thresholds strong { color: #1a2040; }
        .cta { text-align: center; margin-bottom: 24px; }
        .cta a { display: inline-block; background: #0e1128; color: #f0c030; text-decoration: none;
                  font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase;
                  padding: 12px 28px; border: 1px solid #f0c030; }
        .footer { padding: 16px 32px; background: #f4f6fb; border-top: 1px solid #dde3ef;
                  font-size: 11px; color: #9aa5c0; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <p class="header-title">✦ FFXIV Market Analyzer</p>
        <p class="header-sub">Alerta de oportunidade de lucro detectada</p>
    </div>

    <div class="body">

        <div class="item-row">
            @if($result->itemIcon)
                <img class="item-icon"
                     src="https://xivapi.com{{ $result->itemIcon }}"
                     alt="{{ $result->itemName }}">
            @endif
            <div>
                <div class="item-name">{{ $result->itemName }}</div>
                <div class="item-server">{{ $alert->server->name }} &mdash; {{ $alert->server->datacenter }}</div>
            </div>
        </div>

        <div class="metrics">
            <div class="metric-row">
                <span class="metric-label">Lucro estimado</span>
                <span class="metric-value profit">{{ number_format($result->profit) }} gil</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Margem</span>
                <span class="metric-value profit">{{ number_format($result->marginPercent, 1) }}%</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Custo de craft</span>
                <span class="metric-value cost">{{ number_format($result->costEstimate) }} gil</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Receita estimada</span>
                <span class="metric-value">{{ number_format($result->revenueEstimate) }} gil</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Vendas / semana</span>
                <span class="metric-value">{{ number_format($result->salesPerWeek, 1) }}</span>
            </div>
            @if(!empty($result->craftJobs))
            <div class="metric-row">
                <span class="metric-label">Jobs</span>
                <span class="metric-value">{{ implode(', ', $result->craftJobs) }}</span>
            </div>
            @endif
        </div>

        <div class="thresholds">
            Seus limites configurados &mdash;
            <strong>Lucro mín.:</strong> {{ number_format($alert->min_profit) }} gil &nbsp;·&nbsp;
            <strong>Margem mín.:</strong> {{ number_format($alert->min_margin, 1) }}%
        </div>

        <div class="cta">
            <a href="{{ url('/') }}">Ver Dashboard de Mercado</a>
        </div>

    </div>

    <div class="footer">
        Você recebe este e-mail porque configurou um alerta no FFXIV Market Analyzer.<br>
        Para desativar, acesse a seção de Alertas no painel.
    </div>

</div>
</body>
</html>
