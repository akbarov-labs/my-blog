<?php

namespace App\Filament\Pages;

use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Profile::current()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('About you')
                    ->description('Shown on the homepage hero and in the site footer.')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('headline')
                            ->required()
                            ->helperText('e.g. "Backend Developer" — the small kicker above your name.'),
                        Forms\Components\Textarea::make('bio_1')
                            ->label('Bio — first paragraph')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('bio_2')
                            ->label('Bio — second paragraph')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Footer')
                    ->schema([
                        Forms\Components\TextInput::make('footer_location')
                            ->label('Location line')
                            ->helperText('e.g. "Written in Tashkent"'),
                        Forms\Components\TextInput::make('footer_note')
                            ->label('Extra note (optional)'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Profile::current()->update($this->form->getState());

        Notification::make()
            ->title('Site settings saved')
            ->success()
            ->send();
    }
}
