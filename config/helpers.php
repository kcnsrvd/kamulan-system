<?php
// helpers.php
require_once(__DIR__ . '/push.php');

function json_response($data){
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send a OneSignal push notification when an order status changes.
 *
 * We target the buyer using OneSignal's external_user_id, which should
 * match your users.id in the database and be set on the client side
 * (web/mobile) via the OneSignal SDK.
 */
function send_order_status_push($orderId, $status, $userId = null) {
    if (!defined('ONESIGNAL_APP_ID') || !defined('ONESIGNAL_REST_API_KEY')) {
        return; // not configured
    }

    if (empty($userId)) {
        // If we don't know which user to target, skip sending
        return;
    }

    $statusText = (string)$status;

    // Basic title/body – customize as you like
    $heading = 'Order #' . (int)$orderId . ' update';
    $content = 'Your order status is now: ' . $statusText;

    $body = [
        'app_id'                  => ONESIGNAL_APP_ID,
        'include_external_user_ids' => [ (string)$userId ],
        'headings'                => [ 'en' => $heading ],
        'contents'                => [ 'en' => $content ],
        'data'                    => [
            'order_id' => (int)$orderId,
            'status'   => $statusText,
        ],
    ];

    $ch = curl_init('https://onesignal.com/api/v1/notifications');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . ONESIGNAL_REST_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_TIMEOUT        => 5,
    ]);

    curl_exec($ch);
    curl_close($ch);
}
?>
