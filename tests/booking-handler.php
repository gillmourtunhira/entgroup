<?php
/**
 * Isolated handler checks: no WordPress connection or database writes.
 * Run: php tests/booking-handler.php
 */
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

const MINUTE_IN_SECONDS = 60;
$saved = [];
$limited = false;
function wp_unslash($value) { return $value; }
function wp_validate_redirect($value, $fallback) { return $fallback; }
function esc_url_raw($value) { return $value; }
function home_url($path) { return 'https://example.test' . $path; }
function wp_verify_nonce($value, $action) { return $value === 'valid'; }
function sanitize_text_field($value) { return strip_tags($value); }
function sanitize_textarea_field($value) { return strip_tags($value); }
function sanitize_email($value) { return filter_var($value, FILTER_SANITIZE_EMAIL); }
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); }
function current_time($format) { return $format === 'Y-m-d' ? '2026-09-10' : '2026-09-10 12:00:00'; }
function wp_salt() { return 'test-salt'; }
function get_transient($key) { return $GLOBALS['limited']; }
function set_transient($key, $value, $ttl) { $GLOBALS['limited'] = true; }
function wp_insert_post($post, $error) { $GLOBALS['saved'][] = $post; return 42; }
function is_wp_error($value) { return false; }
function add_query_arg($key, $value, $url) { return $url . '?' . $key . '=' . $value; }
function wp_safe_redirect($url, $code) { throw new RuntimeException($url); }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function current_user_can($capability) { return true; }
function get_post_meta($id, $key, $single) { return $GLOBALS['saved'][0]['meta_input'][$key] ?? ''; }
function update_post_meta($id, $key, $value) { $GLOBALS['saved'][0]['meta_input'][$key] = $value; }
function is_admin() { return true; }
function get_post_type($id) { return 'ent_booking'; }
function check(bool $condition, string $message): void {
    if (! $condition) { throw new LogicException($message); }
}
function submit(array $overrides = []): string {
    $_POST = array_merge([
        'booking_nonce' => 'valid', 'patient_name' => 'Test Patient',
        'email' => 'test@example.test', 'phone' => '+263 778 000000',
        'preferred_date' => '2026-09-11', 'consent' => 'yes',
        'service' => 'general-ent', 'clinic' => 'harare-main',
        'return_url' => 'https://untrusted.example/',
    ], $overrides);
    try { EntGroup\Bookings::submit(); } catch (RuntimeException $e) { return $e->getMessage(); }
    throw new LogicException('Expected redirect.');
}
foreach ([
    ['booking_nonce' => 'invalid'], ['patient_name' => []], ['email' => 'bad'],
    ['preferred_date' => '2026-02-31'], ['preferred_date' => '2026-09-09'],
    ['consent' => 'no'], ['website' => 'spam'], ['phone' => 'bad'],
    ['service' => 'unknown'], ['clinic' => 'unknown'], ['service' => ''],
    ['clinic' => []], ['message' => str_repeat('a', 2001)], ['message' => []],
] as $invalid) {
    $url = submit($invalid);
    check(! str_contains($url, 'received'), 'Invalid input was accepted.');
}
check(count($saved) === 0, 'Invalid requests must never be stored.');
check(str_contains(submit(), 'received'), 'Valid request not accepted.');
check(count($saved) === 1, 'Expected exactly one stored request.');
check($saved[0]['post_status'] === 'private', 'Request must be private.');
check($saved[0]['post_title'] === 'Test Patient', 'Title must contain only the full name.');
check($saved[0]['meta_input']['_ent_status'] === 'new', 'Request must be unconfirmed.');
check($saved[0]['meta_input']['_ent_email'] === 'test@example.test', 'Email missing.');
check($saved[0]['meta_input']['_ent_service'] === 'general-ent', 'Service missing.');
check($saved[0]['meta_input']['_ent_clinic'] === 'harare-main', 'Clinic missing.');
check($saved[0]['meta_input']['_ent_message'] === '', 'Optional message should be empty.');
check(str_contains(submit(), 'recent'), 'Repeat request not limited.');
check(count($saved) === 1, 'Repeat request was stored.');
foreach (EntGroup\Bookings::services() as $service => $label) {
    foreach (EntGroup\Bookings::clinics() as $clinic => $clinicLabel) {
        $limited = false;
        check(str_contains(submit([
            'service' => $service, 'clinic' => $clinic,
            'message' => "Please call after 2pm.\n<strong>Thank you.</strong>",
        ]), 'received'), 'Valid service/clinic combination rejected.');
        $record = end($saved);
        check($record['meta_input']['_ent_service'] === $service, 'Wrong service stored.');
        check($record['meta_input']['_ent_clinic'] === $clinic, 'Wrong clinic stored.');
        check($record['meta_input']['_ent_message'] === "Please call after 2pm.\nThank you.", 'Message must be plain text with preserved line breaks.');
    }
}
function columnText(string $column): string {
    ob_start();
    EntGroup\Bookings::column($column, 42);
    return (string) ob_get_clean();
}
check(columnText('ent_service') === 'General ENT consultation', 'Service label missing from column.');
check(columnText('ent_preferred_date') === '11 September 2026', 'Preferred date missing from column.');
check(columnText('ent_review_status') === 'New', 'New request status incorrect.');
$_POST = ['ent_status_nonce' => 'valid', 'ent_status' => 'contacted'];
EntGroup\Bookings::saveStatus(42);
check(columnText('ent_review_status') === 'Contacted', 'Column must reflect the updated review status.');
$_POST['ent_status'] = 'invalid';
EntGroup\Bookings::saveStatus(42);
check(columnText('ent_review_status') === 'Contacted', 'Invalid status must not replace the saved status.');
check(EntGroup\Bookings::adminTitle('Old composite title', 42) === 'Test Patient', 'Existing titles must display the name only in admin.');
echo "Booking checks passed: validation, storage, repeat limiting, titles and live status columns.\n";
