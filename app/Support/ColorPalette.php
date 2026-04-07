<?php

namespace App\Support;

use Filament\Support\Colors\Color;

class ColorPalette
{
    public static function options(): array
    {
        return [
            'blue' => 'Blue',
            'emerald' => 'Emerald',
            'green' => 'Green',
            'indigo' => 'Indigo',
            'orange' => 'Orange',
            'purple' => 'Purple',
            'red' => 'Red',
            'rose' => 'Rose',
            'sky' => 'Sky',
            'teal' => 'Teal',
            'violet' => 'Violet',
            'amber' => 'Amber',
            'yellow' => 'Yellow',
            'pink' => 'Pink',
            'cyan' => 'Cyan',
            'lime' => 'Lime',
            'slate' => 'Slate',
            'gray' => 'Gray',
            'zinc' => 'Zinc',
        ];
    }

    public static function map(): array
    {
        return [
            'blue' => Color::Blue,
            'emerald' => Color::Emerald,
            'green' => Color::Green,
            'indigo' => Color::Indigo,
            'orange' => Color::Orange,
            'purple' => Color::Purple,
            'red' => Color::Red,
            'rose' => Color::Rose,
            'sky' => Color::Sky,
            'teal' => Color::Teal,
            'violet' => Color::Violet,
            'amber' => Color::Amber,
            'yellow' => Color::Yellow,
            'pink' => Color::Pink,
            'cyan' => Color::Cyan,
            'lime' => Color::Lime,
            'slate' => Color::Slate,
            'gray' => Color::Gray,
            'zinc' => Color::Zinc,
        ];
    }

    public static function constantFor(string $colorName)
    {
        $map = static::map();

        return $map[$colorName] ?? Color::Blue;
    }
}