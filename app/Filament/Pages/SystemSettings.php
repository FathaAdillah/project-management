<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Actions\Action;
use App\Models\Setting;
use App\Support\ColorPalette;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $title = 'UI Settings';
    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $userId = auth()->id();

        $this->form->fill([
            'navigation_style' => Setting::getUserValue('filament_navigation_style', 'sidebar', $userId),
            'panel_color' => Setting::getUserValue('filament_primary_color', 'blue', $userId),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Navigation Layout')
                    ->description('Choose your preferred navigation style')
                    ->icon('heroicon-o-bars-3')
                    ->schema([
                        Radio::make('navigation_style')
                            ->label('Layout Style')
                            ->options([
                                'sidebar' => 'Sidebar Navigation',
                                'top' => 'Top Navigation',
                            ])
                            ->descriptions([
                                'sidebar' => 'Classic sidebar layout (recommended for desktop)',
                                'top' => 'Modern top navigation bar (great for tablets)',
                            ])
                            ->inline(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateNavigationStyle($state);
                            }),
                    ]),

                Section::make('Color Theme')
                    ->description('Personalize your interface colors')
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Select::make('panel_color')
                            ->label('Primary Color')
                            ->options(ColorPalette::options())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateColorTheme($state);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function updateNavigationStyle(string $style): void
    {
        Setting::setUserValue('filament_navigation_style', $style, 'ui', auth()->id());

        $this->dispatch('navigation-style-updated', style: $style);

        Notification::make()
            ->title('Navigation Updated')
            ->body($style === 'top'
                ? 'Top navigation preference saved. Reload to apply.'
                : 'Sidebar navigation preference saved.')
            ->success()
            ->send();
    }

    protected function updateColorTheme(string $color): void
    {
        Setting::setUserValue('filament_primary_color', $color, 'ui', auth()->id());

        $this->applyColorChange($color);

        $this->dispatch('color-theme-updated', color: $color);

        Notification::make()
            ->title('Color Theme Updated')
            ->body("Primary color changed to {$color}.")
            ->success()
            ->send();
    }

    protected function applyColorChange(string $colorName): void
    {
        FilamentColor::register([
            'primary' => ColorPalette::constantFor($colorName),
        ]);
    }

    public function save(): void
    {
        $this->updateNavigationStyle($this->data['navigation_style']);
        $this->updateColorTheme($this->data['panel_color']);

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('Preferences saved. Reloading to apply layout...')
            ->success()
            ->send();

        $this->dispatch('settings-saved');
    }
}