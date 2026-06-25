<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSubscriptionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Langganan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(Subscription::with(['company', 'plan'])->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('company.name')->label('Usaha')->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label('Paket')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'    => 'success',
                        'trial'     => 'warning',
                        'expired'   => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ends_at')->label('Berakhir')->date()->sortable(),
            ]);
    }
}
