<?php

namespace App\Filament\Pages;

use App\Models\LiveDataVault;
use App\Models\GlobalAdBlock;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
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
        $positionStats = $this->getPositionStats();

        return $form
            ->schema([
                Section::make('📊 Ad Positions Overview')
                    ->description('Current status of all ad positions. Manage individual ad blocks in the Ad Blocks resource.')
                    ->schema([
                        Grid::make(4)
                            ->schema(function () use ($positionStats) {
                                $cells = [];
                                $i = 0;
                                foreach (GlobalAdBlock::POSITIONS as $key => $label) {
                                    if (in_array($key, ['ga4_tracking', 'clarity_tracking', 'head_script'])) {
                                        continue;
                                    }
                                    $count = $positionStats[$key]['count'] ?? 0;
                                    $active = $positionStats[$key]['active'] ?? 0;
                                    $status = $active > 0 ? '🟢' : ($count > 0 ? '🟡' : '⚪');
                                    $cells[] = Placeholder::make("pos_{$key}")
                                        ->label("{$status} {$label}")
                                        ->content("{$active}/{$count} active")
                                        ->extraAttributes(['class' => 'text-xs']);
                                    $i++;
                                }
                                return $cells;
                            }),
                    ])->collapsible(),

                Section::make('📄 ads.txt Yönetimi')
                    ->description('Google AdSense ads.txt içeriğini buradan yönetebilirsiniz. Değişiklikler otomatik olarak /ads.txt adresine yansır.')
                    ->schema([
                        Textarea::make('ads_txt')
                            ->label('ads.txt İçeriği')
                            ->helperText('Google AdSense hesabınızdan aldığınız ads.txt kodunu buraya yapıştırın.')
                            ->rows(10)
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
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

    private function getPositionStats(): array
    {
        $stats = [];
        $blocks = GlobalAdBlock::selectRaw('position, COUNT(*) as total, SUM(active) as active_count')
            ->groupBy('position')
            ->pluck('active_count', 'position');

        foreach (GlobalAdBlock::POSITIONS as $key => $label) {
            $stats[$key] = [
                'count' => (int) ($blocks[$key] ?? 0),
                'active' => (int) ($blocks[$key . '_active'] ?? 0),
            ];
        }

        foreach ($blocks as $pos => $activeCount) {
            if (isset($stats[$pos])) {
                $stats[$pos]['active'] = (int) $activeCount;
            }
        }

        return $stats;
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
        $total = GlobalAdBlock::where('active', 1)->count();
        return (string) $total;
    }
}
