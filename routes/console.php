<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Rodar o comando todo dia às 01:00 da manhã
Schedule::command('laboral:marcar-faltas')->dailyAt('01:00');
// Comando para rodar manualmente no terminal e testar o Robo de Marcar Faltas Automáticas
// php artisan laboral:marcar-faltas