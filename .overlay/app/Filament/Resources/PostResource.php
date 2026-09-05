<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $context, $state, callable $set) {
                            if ($context === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('category')
                        ->required()
                        ->helperText('e.g. LeetCode, English, Projects — free text, used as a filter on the blog page.')
                        ->columnSpan(1),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->default(now())
                        ->columnSpan(1),
                    Forms\Components\Textarea::make('excerpt')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_published')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Body')
                ->description('Build the post out of blocks, in order. This mirrors how the post detail page renders.')
                ->schema([
                    Forms\Components\Repeater::make('blocks')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'text' => 'Paragraph',
                                    'heading' => 'Heading',
                                    'code' => 'Code block',
                                    'quote' => 'Quote',
                                ])
                                ->default('text')
                                ->required(),
                            Forms\Components\Textarea::make('content')
                                ->required()
                                ->rows(4),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                        ->collapsible()
                        ->addActionLabel('Add block')
                        ->defaultItems(1)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                Tables\Columns\TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn () => Post::query()->distinct()->pluck('category', 'category')->filter()->toArray()),
                Tables\Filters\TernaryFilter::make('is_published'),
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
