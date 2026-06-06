$logFile = "C:\SEO\akilli_loop.log"
$maxRuns = 600  # 500 x 600 = 300,000 nodes, ~8 saat

for ($i = 1; $i -le $maxRuns; $i++) {
    $start = Get-Date
    "$(Get-Date -Format 'HH:mm:ss') Run $i/$maxRuns basladi..." | Out-File $logFile -Append
    
    $output = python bots/akilli_bot.py --quick --resume 2>&1
    $exitCode = $LASTEXITCODE
    
    $elapsed = [math]::Round(((Get-Date) - $start).TotalSeconds, 1)
    
    # Extract sent/failed counts from output
    $sentLine = $output | Select-String "Gönderilen"
    $failedLine = $output | Select-String "Başarısız"
    
    $msg = "$(Get-Date -Format 'HH:mm:ss') Run $i : $($elapsed)s | EXIT=$exitCode"
    $msg | Out-File $logFile -Append
    
    if ($exitCode -ne 0) {
        "HATA!" | Out-File $logFile -Append
        $output | Out-File $logFile -Append
        break
    }
    
    Start-Sleep -Seconds 2
}

"DONE - $(Get-Date)" | Out-File $logFile -Append
