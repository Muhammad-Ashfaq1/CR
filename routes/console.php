<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:sync-permissions', function () {
    $this->info('Syncing roles and permissions...');
    $this->call('db:seed', [
        '--class' => 'RolePermissionSeeder',
        '--force' => true,
    ]);
    $this->info('Roles and permissions synced.');
})->purpose('Sync application roles and permissions');
