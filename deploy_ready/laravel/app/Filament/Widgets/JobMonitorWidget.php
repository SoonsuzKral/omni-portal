<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class JobMonitorWidget extends BaseWidget
{
    protected int $columns = 4;

    protected function getStats(): array
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        $recentJob = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->first();
            
        $recentFailed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->first();

        return [
            Stat::make('🔴 Failed Jobs', number_format($failedJobs))
                ->description($failedJobs > 0 ? 'Check queue:clear' : 'All clear')
                ->icon('heroicon-o-exclamation-circle')
                ->color($failedJobs > 0 ? 'danger' : 'success'),

            Stat::make('🟢 Pending Jobs', number_format($pendingJobs))
                ->description('In queue')
                ->icon('heroicon-o-clock')
                ->color($pendingJobs > 100 ? 'warning' : 'success'),

            Stat::make('⚡ Latest Job', $recentJob ? class_basename($recentJob->payload) ?? 'Unknown' : 'None')
                ->description($recentJob ? \Carbon\Carbon::parse($recentJob->created_at)->diffForHumans() : 'No jobs')
                ->icon('heroicon-o-bolt'),

            Stat::make('💥 Last Failure', $recentFailed ? class_basename($recentFailed->payload) ?? 'None' : 'None')
                ->description($recentFailed ? \Carbon\Carbon::parse($recentFailed->failed_at)->diffForHumans() : 'No failures')
                ->icon('heroicon-o-x-circle')
                ->color($failedJobs > 0 ? 'danger' : 'gray'),
        ];
    }
}