<?php

namespace App\Filament\Resources;

use App\Models\Company;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'SaaS';

    protected static ?string $navigationLabel = 'Transaksi Tenant';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.company.name')
                    ->label('Usaha')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'       => 'success',
                        'pending'    => 'warning',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('preferred_payment')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('midtrans_status')
                    ->label('Midtrans')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'settlement', 'capture' => 'success',
                        'pending'               => 'warning',
                        'cancel', 'expire'      => 'danger',
                        default                 => 'gray',
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->label('Usaha')
                    ->options(fn () => Company::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(fn ($query, array $data) => filled($data['value'])
                        ? $query->whereHas('branch', fn ($q) => $q->where('company_id', $data['value']))
                        : $query),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'paid'      => 'Paid',
                        'pending'   => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('preferred_payment')
                    ->label('Metode Bayar')
                    ->options([
                        'cash' => 'Cash',
                        'qris' => 'QRIS',
                        'card' => 'Kartu',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()->with('branch.company');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Orders\Pages\ListOrders::route('/'),
        ];
    }
}
