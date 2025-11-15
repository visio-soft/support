<?php

namespace VisioSoft\Support\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Models\PartnerSupport;

class SupportStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $openCount = PartnerSupport::where('status', SupportStatus::OPEN->value)->count();
        $inProgressCount = PartnerSupport::where('status', SupportStatus::IN_PROGRESS->value)->count();
        $waitingAdminCount = PartnerSupport::where('status', SupportStatus::WAITING_ADMIN->value)->count();
        $closedTodayCount = PartnerSupport::whereDate('closed_at', today())->count();
        $myTicketsCount = PartnerSupport::where('assigned_to', auth()->id())->open()->count();

        return [
            Stat::make('Open Tickets', $openCount)
                ->description('New tickets awaiting response')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('In Progress', $inProgressCount)
                ->description('Tickets being worked on')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),

            Stat::make('Waiting for Admin', $waitingAdminCount)
                ->description('Urgent - Customer replied')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('My Tickets', $myTicketsCount)
                ->description('Assigned to you')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),

            Stat::make('Closed Today', $closedTodayCount)
                ->description('Tickets resolved today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
