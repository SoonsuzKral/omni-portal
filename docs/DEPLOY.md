# DEPLOY SÜRECİ

## Manuel Deploy (FileZilla ile)

1. Local'de değişiklik yap
2. `git status` → değişen dosyaları gör
3. `git add . && git commit -m "açıklama"`
4. `git diff HEAD~1 --name-only` → değişen dosya listesi
5. FileZilla ile sadece o dosyaları sunucuya at
6. `https://omviportal.com/sync.php?token=omniportal2026` aç
7. Bitti

## GitHub Webhook (Otomatik Deploy)

- Mevcut `public/deploy_webhook.php` kullanılıyor
- GitHub Repo > Settings > Webhooks > Add webhook:
  - Payload URL: `https://omviportal.com/deploy_webhook.php`
  - Content type: `application/json`
  - Secret: `.env` içindeki `DEPLOY_WEBHOOK_SECRET` değeri
  - Events: Just the push event
- Her `git push` sonrası otomatik deploy çalışır

## GitHub'a İlk Push

```bash
# GitHub'da yeni repo oluştur (web arayüzünden)
# Sonra:
git remote add origin https://github.com/KULLANICI_ADIN/omni-portal.git
git branch -M main
git push -u origin main
```
