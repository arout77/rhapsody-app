<?php
declare(strict_types=1);

namespace Tests\Unit\Snapshot;

use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Snapshot\Redactor;

class RedactorTest extends TestCase
{
    public function test_it_masks_sensitive_header_and_field_names(): void
    {
        $redactor = new Redactor();

        foreach (['Authorization', 'Cookie', 'Set-Cookie', 'X-Api-Key', 'X-CSRF-TOKEN', 'password', 'passwordConfirm', 'DB_PASS', 'APP_KEY', 'apiKey', 'client_secret', 'PHPSESSID'] as $name) {
            $this->assertTrue($redactor->isSensitiveKey($name), "{$name} should be sensitive");
        }
    }

    public function test_it_leaves_ordinary_names_alone(): void
    {
        $redactor = new Redactor();

        foreach (['User-Agent', 'Accept', 'Host', 'page', 'DB_HOST', 'APP_ENV', 'bypass', 'keyboard', 'author'] as $name) {
            $this->assertFalse($redactor->isSensitiveKey($name), "{$name} should not be sensitive");
        }
    }

    public function test_extra_keys_are_added_on_top_of_the_defaults(): void
    {
        $redactor = new Redactor(['ssn', 'Card-Number']);

        $this->assertTrue($redactor->isSensitiveKey('user_ssn'));
        $this->assertTrue($redactor->isSensitiveKey('cardNumber'));
        $this->assertTrue($redactor->isSensitiveKey('password'));
    }

    public function test_redact_array_masks_by_key_and_scrubs_the_rest_recursively(): void
    {
        $out = (new Redactor())->redactArray([
            'Authorization' => 'Bearer abc.def.ghi',
            'page'          => '2',
            'nested'        => ['api_key' => 'sk-123', 'note' => 'password=hunter2 ok'],
        ]);

        $this->assertSame(Redactor::MASK, $out['Authorization']);
        $this->assertSame('2', $out['page']);
        $this->assertIsArray($out['nested']);
        $this->assertSame(Redactor::MASK, $out['nested']['api_key']);
        $this->assertSame('password=' . Redactor::MASK . ' ok', $out['nested']['note']);
    }

    public function test_it_scrubs_bearer_tokens_jwts_and_url_credentials(): void
    {
        $redactor = new Redactor();
        $jwt      = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dBjftJeZ4CVPmB92K27uhbUJU1p1r_wW1gFWFOEjXk';

        $this->assertSame('Authorization: ' . Redactor::MASK, $redactor->scrubString('Authorization: Bearer abc123xyz'));
        $this->assertStringNotContainsString('eyJhbGci', $redactor->scrubString("token seen: {$jwt}"));
        $this->assertSame('mysql://' . Redactor::MASK . '@db/app', $redactor->scrubString('mysql://root:s3cret@db/app'));
    }

    public function test_it_scrubs_provider_key_shapes_and_cookie_lines(): void
    {
        $redactor = new Redactor();

        $this->assertStringNotContainsString('AIzaSyA', $redactor->scrubString('GET /v1?key=AIzaSyA1234567890abcdefghijklmnopqrstuvw'));
        $this->assertStringNotContainsString('sk_live_', $redactor->scrubString('charge failed for sk_live_abcdefghijklmnop'));
        $this->assertSame('Cookie: ' . Redactor::MASK, $redactor->scrubString('Cookie: a=b; session=zzz'));
    }

    public function test_it_scrubs_quoted_json_style_pairs_and_keeps_the_quotes(): void
    {
        $out = (new Redactor())->scrubString('{"user":"ann","password":"hunter2","note":"hi"}');

        $this->assertStringContainsString('"password":"' . Redactor::MASK . '"', $out);
        $this->assertStringContainsString('"user":"ann"', $out);
        $this->assertStringNotContainsString('hunter2', $out);
    }

    public function test_plain_text_passes_through_unchanged(): void
    {
        $text = 'SELECT * FROM posts WHERE id = 7 -- nothing secret here';

        $this->assertSame($text, (new Redactor())->scrubString($text));
        $this->assertSame('', (new Redactor())->scrubString(''));
    }

    public function test_mask_values_keeps_shape_but_hides_every_value(): void
    {
        $out = (new Redactor())->maskValues(['user_id' => 42, 'cart' => ['items' => 3, 'coupon' => 'SAVE10'], 'flash' => null]);

        $this->assertSame(['user_id' => '[REDACTED int]', 'cart' => ['items' => '[REDACTED int]', 'coupon' => '[REDACTED string]'], 'flash' => '[REDACTED null]'], $out);
    }

    public function test_objects_and_depth_are_handled_without_reading_contents(): void
    {
        $redactor = new Redactor();

        $this->assertSame('[object stdClass]', $redactor->redact(new \stdClass()));

        $deep = ['a' => 'x'];
        for ($i = 0; $i < 12; $i++) {
            $deep = ['n' => $deep];
        }
        $this->assertStringContainsString('max depth', (string) json_encode($redactor->redactArray($deep)));
    }

    public function test_query_params_are_masked_when_the_sql_touches_something_sensitive(): void
    {
        $redactor = new Redactor();

        $this->assertSame(Redactor::MASK, $redactor->redactQueryParams('UPDATE users SET password = ? WHERE id = ?', ['$2y$10$abc', 7]));
        $this->assertSame(Redactor::MASK, $redactor->redactQueryParams('SELECT * FROM personal_access_tokens WHERE id = ?', [7]));
        $this->assertSame([7, 'draft'], $redactor->redactQueryParams('SELECT * FROM posts WHERE id = ? AND status = ?', [7, 'draft']));
        $this->assertSame([], $redactor->redactQueryParams('SELECT 1', []));
        $this->assertNull($redactor->redactQueryParams('SELECT 1', null));
    }
}
