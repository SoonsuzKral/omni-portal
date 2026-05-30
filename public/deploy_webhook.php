<?php
/**
 * Omni Portal - GitHub Webhook Deployment Trigger
 * 
 * Place in: public/deploy_webhook.php
 * GitHub Repo > Settings > Webhooks > Add webhook:
 *   Payload URL: https://omviportal.com/deploy_webhook.php
 *   Content type: application/json
 *   Secret: <set a strong secret>
 *   Events: Just the push event
 */

$secret = getenv('DEPLOY_WEBHOOK_SECRET') ?: '';

if (empty($secret)) {
    http_response_code(500);
    die('{"error":"DEPLOY_WEBHOOK_SECRET not configured"}');
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('{"error":"Method not allowed"}');
}

// Verify signature
$headers = getallheaders();
$signature = $headers['X-Hub-Signature-256'] ?? '';

if (empty($signature)) {
    http_response_code(401);
    die('{"error":"Missing signature"}');
}

$payload = file_get_contents('php://input');
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    die('{"error":"Invalid signature"}');
}

// Verify it's a push event
$event = $headers['X-GitHub-Event'] ?? '';
if ($event !== 'push') {
    http_response_code(200);
    die('{"status":"ignored","event":"' . $event . '"}');
}

// Execute deploy script
$output = [];
$returnCode = 0;
$deployScript = __DIR__ . '/../deploy.sh';

if (!file_exists($deployScript)) {
    http_response_code(500);
    die('{"error":"deploy.sh not found"}');
}

exec('bash ' . escapeshellarg($deployScript) . ' 2>&1', $output, $returnCode);

http_response_code($returnCode === 0 ? 200 : 500);
header('Content-Type: application/json');
echo json_encode([
    'status' => $returnCode === 0 ? 'ok' : 'error',
    'output' => $output,
]);
