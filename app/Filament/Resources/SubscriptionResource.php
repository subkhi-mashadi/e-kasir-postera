<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'SaaS';

    protected static ?string $navigationLabel = 'Langganan';

    protected static ?string $modelLabel = 'Langganan';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Usaha')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('plan_id')
                    ->label('Paket')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'trial'     => 'Trial',
                        'active'    => 'Active',
                        'expired'   => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

                Forms\Components\Select::make('period')
                    ->label('Periode')
                    ->options([
                        'monthly' => 'Bulanan',
                        'yearly'  => 'Tahunan',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('trial_ends_at')
                    ->label('Trial Berakhir'),

                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Mulai'),

                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Berakhir'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Usaha')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Paket')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trial'     => 'warning',
                        'active'    => 'success',
                        'expired'   => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('period')
                    ->label('Periode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Bulanan',
                        'yearly'  => 'Tahunan',
                        default   => $state,
                    }),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial s/d')
                    ->date('d M Y')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'trial'     => 'Trial',
                        'active'    => 'Active',
                        'expired'   => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('period')
                    ->label('Periode')
                    ->options([
                        'monthly' => 'Bulanan',
                        'yearly'  => 'Tahunan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('aktifkan')
                    ->label('Aktifkan')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Subscription $record): bool => $record->status !== 'active')
                    ->action(function (Subscription $record): void {
                        $ends_at = $record->period === 'yearly'
                            ? now()->addYear()
                            : now()->addMonth();

                        $record->update([
                            'status'    => 'active',
                            'starts_at' => now(),
                            'ends_at'   => $ends_at,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Langganan diaktifkan.')
                            ->send();
                    }),

                Action::make('expire')
                    ->label('Expire')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Subscription $record): bool => in_array($record->status, ['active', 'trial']))
                    ->requiresConfirmation()
                    ->modalHeading('Expire Langganan')
                    ->modalDescription('Langganan ini akan ditandai sebagai expired.')
                    ->action(function (Subscription $record): void {
                        $record->update(['status' => 'expired']);

                        Notification::make()
                            ->danger()
                            ->title('Langganan diexpire.')
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['company', 'plan']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'edit'  => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
