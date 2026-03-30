<?php

use Illuminate\Support\Facades\Schedule;

// Retornos EDP
Schedule::command('edp:pegar-todos-retornos')
    ->name('edp-retornos-08h')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/edp-retornos.log'));

Schedule::command('edp:pegar-todos-retornos')
    ->name('edp-retornos-12h30')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('12:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/edp-retornos.log'));

Schedule::command('edp:pegar-todos-retornos')
    ->name('edp-retornos-17h')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('17:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/edp-retornos.log'));

// Sync nightly
Schedule::command('edp:sync-financials')
    ->name('edp-sync-financials-nightly')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/edp-sync-financials.log'));

// Schedule::command('asaas:create-missing-charges --days=30')
//     ->timezone('America/Sao_Paulo')
//     ->dailyAt('07:00')
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/asaas-create-missing.log'));

// Schedule::command('asaas:create-missing-charges --days=30')
//     ->timezone('America/Sao_Paulo')
//     ->dailyAt('21:00')
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/asaas-create-missing.log'));

// Schedule::command('asaas:reconcile --days=120')
//     ->timezone('America/Sao_Paulo')
//     ->dailyAt('03:00')
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/asaas-reconcile.log'));