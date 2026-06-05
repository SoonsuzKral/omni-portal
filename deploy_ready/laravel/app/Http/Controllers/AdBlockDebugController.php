<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdBlockDebugController extends Controller
{
    public function debug(Request $request)
    {
        $data = $request->all();

        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $score = $data['score'] ?? 0;
        $signals = $data['signals'] ?? [];
        $result = $data['result'] ?? 'UNKNOWN';
        $checks = $data['checks'] ?? [];
        $url = $data['url'] ?? 'unknown';
        $adBlockDetected = $data['adBlockDetected'] ?? false;

        $logData = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'url' => $url,
            'score' => $score,
            'signals' => $signals,
            'result' => $result,
            'adblock_detected' => $adBlockDetected,
            'checks' => [
                'script_loaded' => $checks['scriptLoaded'] ?? null,
                'adsense_exists' => $checks['adsenseExists'] ?? null,
                'bait_visible' => $checks['baitVisible'] ?? null,
            ],
            'frontend_logs' => $data['logs'] ?? [],
        ];

        Log::info('[AdBlock Debug] ' . json_encode($logData, JSON_UNESCAPED_UNICODE));

        Log::info("[AdBlock Debug] IP: {$ip} | UA: " . substr($userAgent, 0, 100));
        Log::info("[AdBlock Debug] Score: {$score} | Signals: " . implode(', ', $signals));
        Log::info("[AdBlock Debug] Result: {$result} | Detected: " . ($adBlockDetected ? 'YES' : 'NO'));
        Log::info("[AdBlock Debug] Checks - Script: " . ($checks['scriptLoaded'] ? 'OK' : 'FAIL') . 
                  " | Adsense: " . ($checks['adsenseExists'] ? 'OK' : 'FAIL') . 
                  " | Bait: " . ($checks['baitVisible'] ? 'VISIBLE' : 'HIDDEN'));

        return response()->json([
            'received' => true,
            'score' => $score,
            'result' => $result,
            'adblock_detected' => $adBlockDetected
        ]);
    }
}