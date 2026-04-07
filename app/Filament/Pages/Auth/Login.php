<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();
        
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): string => view('filament.components.google-login-button')->render(),
        );
    }
}