<?php

namespace App\Filament\Pages;

use App\Models\LiveDataVault;
use App\Models\GlobalAdBlock;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $verificationEnabled = LiveDataVault::where('key', 'adsense_verification_enabled')->first();
        $testMode = LiveDataVault::where('key', 'ads_test_mode')->first();

        Log::debug('AdSettings.mount', [
            'ads_txt_exists' => !is_null($adsTxt),
            'verification_raw' => $verificationEnabled?->value,
            'test_mode_raw' => $testMode?->value,
        ]);

        $this->form->fill([
            'ads_txt' => $adsTxt?->value ?? $this->getDefaultAdsTxt(),
            'adsense_verification_enabled' => $verificationEnabled ? (bool) $verificationEnabled->value : false,
            'ads_test_mode' => $testMode ? ($testMode->value === '1') : false,
        ]);
    }

    public function form(Form $form): Form
    {
        $positionStats = $this->getPositionStats();

        return $form
            ->schema([
                Section::make('✅ AdSense Verification')
                    ->description('Google AdSense doğrulama scriptini aç/kapat. Açıkken site <head> etiketine adsbygoogle.js scripti eklenir.')
                    ->schema([
                        Toggle::make('adsense_verification_enabled')
                            ->label('AdSense Verification Active')
                            ->helperText('Aktif edildiğinde: <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"> head etiketine eklenir.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false),
                    ]),

                Section::make('🧪 Test Modu')
                    ->description('Aktif edildiğinde tüm reklam slotları yerine test kutuları gösterilir (gerçek reklam gösterilmez).')
                    ->schema([
                        Toggle::make('ads_test_mode')
                            ->label('Ad Test Mode (Aktif)')
                            ->helperText('AÇIK: Tüm <x-ad-slot> bileşenleri gri test kutuları gösterir. KAPALI: Normal reklam akışı devam eder.')
                            ->onColor('warning')
                            ->offColor('gray')
                            ->default(false),
                    ]),

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
        try {
            Log::debug('AdSettings.save.started', ['data' => $this->data]);

            $data = $this->form->getState();

            Log::debug('AdSettings.save.getState', ['state' => $data]);

            $adsTxtRecord = LiveDataVault::updateOrCreate(
                ['key' => 'ads_txt'],
                ['value' => $data['ads_txt'] ?? '', 'data_type' => 'string']
            );

            Log::debug('AdSettings.save.ads_txt', [
                'id' => $adsTxtRecord->id,
                'value_len' => strlen($adsTxtRecord->value),
            ]);

            $rawFormVal = $data['ads_test_mode'] ?? false;
            $testModeOn = $rawFormVal ? '1' : '0';

            Log::debug('AdSettings.save.test_mode', [
                'raw_form_value' => $rawFormVal,
                'will_save' => $testModeOn,
            ]);

            $testRecord = LiveDataVault::updateOrCreate(
                ['key' => 'ads_test_mode'],
                ['value' => $testModeOn, 'data_type' => 'boolean']
            );

            Log::debug('AdSettings.save.test_mode.result', [
                'id' => $testRecord->id,
                'saved_value' => $testRecord->value,
                'was_dirty' => $testRecord->wasChanged('value'),
            ]);

            if ($testModeOn === '0') {
                $verRecord = LiveDataVault::updateOrCreate(
                    ['key' => 'adsense_verification_enabled'],
                    ['value' => '1', 'data_type' => 'boolean']
                );
                Log::debug('AdSettings.save.verification.auto_enabled', [
                    'id' => $verRecord->id,
                    'value' => $verRecord->value,
                ]);
                Notification::make()
                    ->title('Test Modu Kapatıldı & AdSense Verification Aktif!')
                    ->body('Test modu kapatıldı ve AdSense Verification Active otomatik olarak etkinleştirildi.')
                    ->success()
                    ->send();
            } else {
                $verValue = ($data['adsense_verification_enabled'] ?? false) ? '1' : '0';
                $verRecord = LiveDataVault::updateOrCreate(
                    ['key' => 'adsense_verification_enabled'],
                    ['value' => $verValue, 'data_type' => 'boolean']
                );
                Log::debug('AdSettings.save.verification.manual', [
                    'id' => $verRecord->id,
                    'value' => $verRecord->value,
                ]);
                Notification::make()
                    ->title('Ayarlar kaydedildi!')
                    ->success()
                    ->send();
            }

            // Verify by re-reading from DB
            $verifyTestMode = LiveDataVault::where('key', 'ads_test_mode')->first();
            $verifyVerification = LiveDataVault::where('key', 'adsense_verification_enabled')->first();
            Log::debug('AdSettings.save.verify', [
                'ads_test_mode.value' => $verifyTestMode?->value,
                'adsense_verification_enabled.value' => $verifyVerification?->value,
            ]);

            Cache::forget('adsense_verification_enabled');
            Cache::forget('ads_test_mode');
            Cache::flush();

            Log::debug('AdSettings.save.completed');
        } catch (\Throwable $e) {
            Log::error('AdSettings.save.error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Notification::make()
                ->title('Hata: Kaydetme başarısız!')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
