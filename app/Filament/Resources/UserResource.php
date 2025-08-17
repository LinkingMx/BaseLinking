<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Gestión de Usuarios';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('150')
                            ->imageResizeTargetHeight('150')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->helperText('Imagen de perfil del usuario (150x150px máximo)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Seguridad')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Roles y Permisos')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->options(function () {
                                return Role::all()->sortBy('name')->pluck('name', 'name')->toArray();
                            })
                            ->default([])
                            ->helperText('Los roles determinan los permisos y accesos del usuario en el sistema')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Seleccione los roles para el usuario')
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name='.urlencode('Usuario').'&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', ')
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'Travel' => 'info',
                        'Treasury' => 'warning',
                        'User' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'super_admin' => 'heroicon-o-shield-check',
                        'Travel' => 'heroicon-o-map-pin',
                        'Treasury' => 'heroicon-o-banknotes',
                        'User' => 'heroicon-o-user',
                        default => 'heroicon-o-tag',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filtrar por Rol')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Impersonate::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Usuarios Eliminados')
                            ->body('Los usuarios seleccionados han sido eliminados exitosamente.')
                            ->duration(5000)
                    ),

                Tables\Actions\BulkAction::make('assign_role')
                    ->label('Asignar Rol')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Rol a Asignar')
                            ->options(function () {
                                return Role::all()->sortBy('name')->pluck('name', 'name')->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (! $record->hasRole($data['role'])) {
                                $record->assignRole($data['role']);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Rol Asignado')
                            ->body("Se asignó el rol '{$data['role']}' a {$count} usuarios.")
                            ->duration(5000)
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Asignar Rol a Usuarios')
                    ->modalDescription('¿Está seguro que desea asignar el rol seleccionado a todos los usuarios seleccionados?')
                    ->modalSubmitActionLabel('Sí, asignar rol'),

                Tables\Actions\BulkAction::make('remove_role')
                    ->label('Remover Rol')
                    ->icon('heroicon-o-user-minus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Rol a Remover')
                            ->options(function () {
                                return Role::all()->sortBy('name')->pluck('name', 'name')->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->hasRole($data['role'])) {
                                $record->removeRole($data['role']);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Rol Removido')
                            ->body("Se removió el rol '{$data['role']}' de {$count} usuarios.")
                            ->duration(5000)
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Remover Rol de Usuarios')
                    ->modalDescription('¿Está seguro que desea remover el rol seleccionado de todos los usuarios seleccionados?')
                    ->modalSubmitActionLabel('Sí, remover rol'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información Personal')
                    ->schema([
                        Infolists\Components\ImageEntry::make('avatar_url')
                            ->label('Avatar')
                            ->circular()
                            ->size(80)
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name ?? 'Usuario').'&color=7F9CF5&background=EBF4FF')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre')
                            ->icon('heroicon-o-user')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Correo Electrónico')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('roles.name')
                            ->label('Roles Asignados')
                            ->badge()
                            ->separator(', ')
                            ->color(fn (string $state): string => match ($state) {
                                'super_admin' => 'danger',
                                'Travel' => 'info',
                                'Treasury' => 'warning',
                                'User' => 'success',
                                default => 'gray',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'super_admin' => 'heroicon-o-shield-check',
                                'Travel' => 'heroicon-o-map-pin',
                                'Treasury' => 'heroicon-o-banknotes',
                                'User' => 'heroicon-o-user',
                                default => 'heroicon-o-tag',
                            })
                            ->default('Sin roles asignados')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Fecha de Registro')
                            ->dateTime('d/M/Y H:i')
                            ->icon('heroicon-o-calendar-days')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/M/Y H:i')
                            ->icon('heroicon-o-clock')
                            ->badge()
                            ->color('warning'),
                    ])->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Estadísticas')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID de Usuario')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('days_since_registration')
                            ->label('Días desde el Registro')
                            ->state(function (User $record): string {
                                return $record->created_at->diffInDays(now()).' días';
                            })
                            ->badge()
                            ->color('info'),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
