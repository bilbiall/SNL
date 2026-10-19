<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use App\Support\Payments;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetLoaded();
        $this->seed(DatabaseSeeder::class);
    }

    private function payOn(string $env = 'simulate', array $prices = ['lovers' => 50, 'girlies' => 30]): void
    {
        Setting::put('pay.enabled', '1');
        Setting::put('pay.env', $env);
        Setting::put('pay.prices', json_encode($prices));
        if ($env !== 'simulate') {
            Setting::put('pay.consumer_key', 'k');
            Setting::putSecret('pay.consumer_secret', 's');
            Setting::put('pay.shortcode', '174379');
            Setting::putSecret('pay.passkey', 'p');
        }
    }

    private function setup_(string $mode = 'girlies', string $type = 'online'): array
    {
        return [
            'type' => $type, 'board_id' => Board::first()->id, 'content_mode' => $mode, 'intensity' => 2, 'adult' => 1,
            'host_name' => 'Amina', 'names' => ['A', 'B'],
        ];
    }

    public function test_setup_page_shows_price_only_for_paid_modes(): void
    {
        $this->payOn();
        $this->get('/new?type=online')->assertOk()->assertSee('KES 50')->assertSee('payNote', false);
        Setting::put('pay.enabled', '0');
        $this->get('/new?type=online')->assertOk()->assertDontSee('KES 50');
    }

    public function test_free_mode_creates_room_directly(): void
    {
        $this->payOn();
        $r = $this->post('/new', $this->setup_('family'));
        $r->assertRedirect();
        $this->assertStringContainsString('/play/', $r->headers->get('Location'));
        $this->assertSame(0, Payment::count());
        $this->assertSame(1, Room::count());
    }

    public function test_payments_off_means_everything_is_free(): void
    {
        $this->post('/new', $this->setup_('lovers'))->assertRedirect();
        $this->assertSame(0, Payment::count());
        $this->assertSame(1, Room::count());
    }

    public function test_paid_mode_makes_an_order_not_a_room(): void
    {
        $this->payOn();
        $r = $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $r->assertRedirect(route('checkout.show', $p->token));
        $this->assertSame(50, $p->amount);
        $this->assertSame('created', $p->status);
        $this->assertSame(0, Room::count());
        $this->get(route('checkout.show', $p->token))->assertOk()->assertSee('KES 50');
    }

    public function test_simulated_payment_creates_exactly_one_room(): void
    {
        $this->payOn();
        $this->post('/new', $this->setup_('girlies'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '0712345678'])->assertOk();
        $this->assertSame('pending', $p->fresh()->status);

        $this->travel(6)->seconds();
        $this->getJson(route('checkout.status', $p->token))->assertJsonPath('status', 'paid');

        $a = $this->postJson(route('checkout.finish', $p->token))->assertOk()->json('redirect');
        $b = $this->postJson(route('checkout.finish', $p->token))->assertOk()->json('redirect');
        $this->assertSame($a, $b);
        $this->assertSame(1, Room::count());
        $this->assertSame('completed', $p->fresh()->status);
    }

    public function test_cannot_finish_before_paying(): void
    {
        $this->payOn();
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.finish', $p->token))->assertStatus(409);
        $this->assertSame(0, Room::count());
    }

    public function test_simulated_cancel(): void
    {
        $this->payOn();
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '0712345670'])->assertOk();
        $this->travel(6)->seconds();
        $this->getJson(route('checkout.status', $p->token))->assertJsonPath('status', 'cancelled');
        $this->assertSame(0, Room::count());
    }

    public function test_bad_phone_rejected(): void
    {
        $this->payOn();
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '12345'])->assertStatus(422);
    }

    public function test_real_flow_stk_push_and_callback(): void
    {
        $this->payOn('sandbox');
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => '3599']),
            '*/mpesa/stkpush/v1/processrequest' => Http::response(['MerchantRequestID' => 'M1', 'CheckoutRequestID' => 'ws_CO_1', 'ResponseCode' => '0', 'CustomerMessage' => 'ok']),
        ]);
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '+254712345678'])->assertOk();
        $p->refresh();
        $this->assertSame('ws_CO_1', $p->checkout_request_id);
        $this->assertSame('254712345678', $p->phone);
        Http::assertSent(fn ($req) => str_contains($req->url(), 'stkpush') && $req['Amount'] == 50 && $req['PartyB'] == '174379');

        $cb = fn (int $code, array $items = []) => ['Body' => ['stkCallback' => [
            'MerchantRequestID' => 'M1', 'CheckoutRequestID' => 'ws_CO_1', 'ResultCode' => $code, 'ResultDesc' => 'x',
            'CallbackMetadata' => ['Item' => $items],
        ]]];
        $url = '/mpesa/callback/' . Payments::callbackSecret();

        $this->postJson('/mpesa/callback/wrong', $cb(0))->assertOk();
        $this->assertSame('pending', $p->fresh()->status); // wrong secret ignored

        $ok = [['Name' => 'Amount', 'Value' => 50], ['Name' => 'MpesaReceiptNumber', 'Value' => 'ABC123XYZ']];
        $this->postJson($url, $cb(0, $ok))->assertOk();
        $this->postJson($url, $cb(1032))->assertOk(); // a late contradicting callback must not undo money
        $p->refresh();
        $this->assertSame('paid', $p->status);
        $this->assertSame('ABC123XYZ', $p->receipt);

        $this->postJson(route('checkout.finish', $p->token))->assertOk();
        $this->assertSame(1, Room::count());
    }

    public function test_amount_mismatch_is_not_paid(): void
    {
        $this->payOn('sandbox');
        Http::fake([
            '*/oauth/*' => Http::response(['access_token' => 't', 'expires_in' => '3599']),
            '*/stkpush/*' => Http::response(['MerchantRequestID' => 'M2', 'CheckoutRequestID' => 'ws_2', 'ResponseCode' => '0']),
        ]);
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '0712345678']);
        $this->postJson('/mpesa/callback/' . Payments::callbackSecret(), ['Body' => ['stkCallback' => [
            'CheckoutRequestID' => 'ws_2', 'ResultCode' => 0, 'ResultDesc' => 'ok',
            'CallbackMetadata' => ['Item' => [['Name' => 'Amount', 'Value' => 1], ['Name' => 'MpesaReceiptNumber', 'Value' => 'R']]],
        ]]]);
        $this->assertSame('failed', $p->fresh()->status);
    }

    public function test_stk_query_reconciles_when_callback_never_arrives(): void
    {
        $this->payOn('sandbox');
        Http::fake([
            '*/oauth/*' => Http::response(['access_token' => 't', 'expires_in' => '3599']),
            '*/stkpush/*' => Http::response(['MerchantRequestID' => 'M3', 'CheckoutRequestID' => 'ws_3', 'ResponseCode' => '0']),
            '*/stkpushquery/*' => Http::response(['ResultCode' => '1032', 'ResultDesc' => 'Request cancelled by user']),
        ]);
        $this->post('/new', $this->setup_('lovers'));
        $p = Payment::firstOrFail();
        $this->postJson(route('checkout.pay', $p->token), ['phone' => '0712345678']);
        $this->travel(25)->seconds();
        $this->getJson(route('checkout.status', $p->token))->assertJsonPath('status', 'cancelled');
    }

    public function test_bad_team_setup_is_refused_before_charging(): void
    {
        $this->payOn();
        $d = $this->setup_('lovers', 'party') + ['teams' => 1, 'team_of' => [1, 1]];
        $this->post('/new', $d);
        $this->assertSame(0, Payment::count());
    }

    public function test_dashboard_and_settings_render_for_admin(): void
    {
        $this->payOn();
        $this->post('/new', $this->setup_('lovers'));
        $admin = User::where('is_admin', true)->first() ?? User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->get(route('gameset.payments.index'))->assertOk()->assertSee('Payments');
        $this->get(route('gameset.payments.index', ['env' => 'simulate', 'status' => 'created']))->assertOk();
        $this->get(route('gameset.payments.export'))->assertOk();
        $this->get(route('gameset.settings'))->assertOk()->assertSee('Safaricom Daraja keys');
    }

    public function test_settings_refuse_enabling_without_keys_and_hide_secrets(): void
    {
        $admin = User::where('is_admin', true)->first() ?? User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $this->post(route('gameset.settings.payments'), ['enabled' => 1, 'env' => 'production', 'paid' => ['lovers'], 'price' => ['lovers' => 50]]);
        $this->assertFalse(Payments::enabled());

        $this->post(route('gameset.settings.payments'), [
            'enabled' => 1, 'env' => 'sandbox', 'consumer_key' => 'KEY', 'consumer_secret' => 'TOPSECRET', 'shortcode' => '174379', 'passkey' => 'PASSKEY',
            'txn_type' => 'CustomerPayBillOnline', 'paid' => ['lovers'], 'price' => ['lovers' => 50],
        ]);
        $this->assertTrue(Payments::enabled());
        $this->assertSame(50, Payments::priceFor('lovers'));
        $this->assertNull(Payments::priceFor('family'));
        $this->get(route('gameset.settings'))->assertOk()->assertDontSee('TOPSECRET')->assertDontSee('PASSKEY');
    }
}
