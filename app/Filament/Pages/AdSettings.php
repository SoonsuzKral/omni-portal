<?php

namespace App\Filament\Pages;

use App\Models\LiveDataVault;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class AdSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Ad Settings';
    protected static ?string $navigationGroup = '💰 REVENUE CONTROL';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.ad-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $adsTxt = LiveDataVault::where('key', 'ads_txt')->first();
        $this->form->fill([
            'ads_txt' => $adsTxt?->value ?? $this->getDefaultAdsTxt(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ads.txt Yönetimi')
                    ->description('Google AdSense ads.txt içeriğini buradan yönetebilirsiniz. Değişiklikler otomatik olarak /ads.txt adresine yansır.')
                    ->schema([
                        Textarea::make('ads_txt')
                            ->label('ads.txt İçeriği')
                            ->helperText('Google AdSense hesabınızdan aldığınız ads.txt kodunu buraya yapıştırın.')
                            ->rows(15)
                            ->extraInputAttributes(['class' => 'font-mono'])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        LiveDataVault::updateOrCreate(
            ['key' => 'ads_txt'],
            ['value' => $data['ads_txt'], 'data_type' => 'adsense']
        );

        Notification::make()
            ->title('ads.txt kaydedildi')
            ->success()
            ->send();
    }

    private function getDefaultAdsTxt(): string
    {
        return '# ads.txt - OmviPortal
# Bu dosya Google AdSense tarafından otomatik olarak okunur.
# Lütfen AdSense hesabınızdan aldığınız ads.txt kodunu ekleyin.

# Example:
# google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0
';
    }

    public static function getNavigationBadge(): ?string
    {
        $adsTxt = LiveDataVault::where('key', 'ads_txt')->first();
        return $adsTxt?->value ? '✓' : '!';
    }
}
