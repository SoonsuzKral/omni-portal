<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\App;

class LocaleSelector extends Component
{
    public $currentLocale;
    public $locales;

    public function __construct()
    {
        $this->currentLocale = App::getLocale();
        $this->locales = [
            'TR' => 'Türkçe',
            'EN' => 'English',
            'RU' => 'Русский',
            'AR' => 'العربية',
        ];
    }

    public function switchLocale($locale)
    {
        session(['locale' => $locale]);
        App::setLocale($locale);
        return redirect()->back();
    }

    public function render()
    {
        return view('components.locale-selector');
    }
}
