<?php

use App\Jobs\CheckAlertsJob;
use App\Jobs\SyncAlertItemsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Verifica alertas e notifica usuários — fila default (definida no job), a cada hora
Schedule::job(new CheckAlertsJob)->hourly();

// Atualiza receitas dos itens com alertas ativos — fila sync (definida no job), toda semana
Schedule::job(new SyncAlertItemsJob)->weekly();
