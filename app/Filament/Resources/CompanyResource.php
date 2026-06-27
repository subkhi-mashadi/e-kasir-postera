<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'SaaS';

    protected static ?string $navigationLabel = 'Tenant';

    protected static ?string $modelLabel = 'Usaha';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Usaha')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Forms\Components\Section::make('Midtrans (dikonfigurasi oleh Owner)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('midtrans_server_key')
                            ->label('Server Key')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Belum diisi'),

                        Forms\Components\TextInput::make('midtrans_client_key')
                            ->label('Client Key')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Belum diisi'),

                        Forms\Components\Toggle::make('midtrans_is_production')
                            ->label('Mode Produksi')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Usaha')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Company $record): string => static::getUrl('edit', ['record' => $record])),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subscription.plan.name')
                    ->label('Paket')
                    ->badge()
                    ->default('—'),

                Tables\Columns\TextColumn::make('subscription.status')
                    ->label('Status Langganan')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'trial'     => 'warning',
                        'active'    => 'success',
                        'expired'   => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subscription.ends_at')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),

                Tables\Filters\SelectFilter::make('subscription_status')
                    ->label('Status Langganan')
                    ->options([
                        'trial'     => 'Trial',
                        'active'    => 'Aktif',
                        'expired'   => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('subscription', fn ($q) => $q->where('status', $data['value']));
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('aktifkan')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Company $record): bool => ! $record->is_active)
                    ->action(function (Company $record): void {
                        $record->update(['is_active' => true]);
                        Notification::make()
                            ->success()
                            ->title('Usaha berhasil diaktifkan.')
                            ->send();
                    }),

                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Company $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Usaha')
                    ->modalDescription('Usaha ini akan dinonaktifkan. Lanjutkan?')
                    ->action(function (Company $record): void {
                        $record->update(['is_active' => false]);
                        Notification::make()
                            ->warning()
                            ->title('Usaha berhasil di-suspend.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit'   => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
