<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Colors\Color;
use Filament\Facades\Filament;
use App\Support\ColorPalette;

class FilamentUserSettings
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $userId = auth()->id();
            
            $savedColor = $this->getSavedColor($userId);
            FilamentColor::register([
                'primary' => $this->getColorConstant($savedColor),
            ]);

            $this->setNavigationStyle($userId);
        }

        return $next($request);
    }

    private function getSavedColor($userId): string
    {
        try {
            return Setting::getUserValue('filament_primary_color', 'blue', $userId);
        } catch (\Exception $e) {
            return 'blue';
        }
    }

    private function setNavigationStyle($userId): void
    {
        try {
            $navigationStyle = Setting::getUserValue('filament_navigation_style', 'sidebar', $userId);
            
            $panel = Filament::getCurrentPanel();
            
            if ($panel) {
                if ($navigationStyle === 'top') {
                    $panel->topNavigation();
                } else {
                    $panel->sidebarCollapsibleOnDesktop(true);
                }
            }
        } catch (\Exception $e) {
            // default
        }
    }

    private function getColorConstant(string $colorName)
    {
        return ColorPalette::constantFor($colorName);
    }
}