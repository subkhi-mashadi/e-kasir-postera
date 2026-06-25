<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionInvoiceResource\Pages;
use App\Models\SubscriptionInvoice;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class SubscriptionInvoiceResource extends Resource
{
    protected static ?string $model = SubscriptionInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'SaaS';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?string $modelLabel = 'Invoice';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Usaha')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid'    => 'success',
                        'failed'  => 'danger',
                        'expired' => 'gray',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->badge()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expired')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid'    => 'Paid',
                        'failed'  => 'Failed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Action::make('tandai_lunas')
                    ->label('Tandai Lunas')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn (SubscriptionInvoice $record): bool => in_array($record->status, ['pending', 'failed']))
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Invoice sebagai Lunas')
                    ->modalDescription('Invoice ini akan ditandai lunas secara manual.')
                    ->action(function (SubscriptionInvoice $record): void {
                        $record->update([
                            'status'         => 'paid',
                            'paid_at'        => now(),
                            'payment_method' => 'manual',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Invoice ditandai lunas.')
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
        return parent::getEloquentQuery()->with(['company', 'subscription']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionInvoices::route('/'),
        ];
    }
}
