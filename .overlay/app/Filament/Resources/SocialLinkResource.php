<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Social & Contact Links';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->required()
                ->helperText('e.g. Email, GitHub, Telegram'),
            Forms\Components\TextInput::make('value')
                ->required()
                ->helperText('What is displayed, e.g. sarvar@example.com'),
            Forms\Components\TextInput::make('href')
                ->required()
                ->helperText('Full link, e.g. mailto:sarvar@example.com or https://github.com/you'),
            Forms\Components\Toggle::make('show_on_hero')
                ->label('Show as a button on the homepage'),
            Forms\Components\Toggle::make('show_on_contact')
                ->label('Show on the Contact page')
                ->default(true),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers show first.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('value'),
                Tables\Columns\IconColumn::make('show_on_hero')->boolean(),
                Tables\Columns\IconColumn::make('show_on_contact')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
