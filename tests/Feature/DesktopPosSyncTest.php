<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosTerminal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DesktopPosSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_terminal_syncs_sale_and_retries_idempotently(): void
    {
        [$terminal, $token] = $this->registeredTerminal();
        $payload = $this->salePayload();
        $headers = $this->headers($terminal, $token);

        $first = $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $payload);
        $first->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        $sale = PosSale::query()->where('desktop_sale_id', 19)->firstOrFail();
        $this->assertSame(route('sales.receipt', $sale), $first->json('data.receipt_url'));
        $this->assertDatabaseCount('pos_sales', 1);
        $this->assertDatabaseCount('pos_sale_items', 1);
        $this->assertDatabaseHas('desktop_pos_sync_logs', [
            'machine_id' => $terminal->machine_id,
            'entity_id' => 19,
            'status' => 'success',
        ]);

        $duplicate = $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $payload);
        $duplicate->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'This synchronization request has already been received.');
        $this->assertSame($first->json('data.receipt_url'), $duplicate->json('data.receipt_url'));
        $this->assertSame($first->json('data.sync_id'), $duplicate->json('data.sync_id'));
        $this->assertDatabaseCount('pos_sales', 1);
    }

    public function test_saved_sale_receipt_displays_sale_data_and_business_email(): void
    {
        [$terminal] = $this->registeredTerminal();
        $response = $this->withHeaders(['Idempotency-Key' => 'receipt-sale-19'])
            ->postJson('/api/desktop-pos/sync', $this->salePayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $terminal->business->update(['email' => 'store@example.test']);
        $sale = PosSale::query()->with('business')->where('desktop_sale_id', 19)->firstOrFail();
        $this->assertSame(route('sales.receipt', $sale), $response->json('data.receipt_url'));
        $user = $terminal->business->users()->create([
            'name' => 'Store Owner',
            'email' => 'receipt-owner@example.test',
            'password' => 'password-password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get($response->json('data.receipt_url'))
            ->assertOk()
            ->assertSee('Kikimo Test Store')
            ->assertSee('store@example.test')
            ->assertSee('SALES RECEIPT')
            ->assertSee('KDP-TEST-19')
            ->assertSee('Test cashier')
            ->assertSee('Sample item')
            ->assertSee('Unit price')
            ->assertSee('Qty')
            ->assertSee('KES 1,000.00')
            ->assertSee('Discount / offer savings')
            ->assertSee('KES 120.00')
            ->assertSee('KES 880.00')
            ->assertSee('Cash')
            ->assertSee('Amount paid')
            ->assertSee('Change due')
            ->assertSee('KES 0.00')
            ->assertSee('Print receipt');

        $this->assertDatabaseCount('pos_sales', 1);
    }

    public function test_registered_terminal_syncs_sale_without_bearer_token(): void
    {
        [$terminal] = $this->registeredTerminal();

        $this->postJson('/api/desktop-pos/sync', $this->salePayload(), [
            'Idempotency-Key' => 'legacy-request-19',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseCount('desktop_pos_sync_logs', 1);
        $this->assertDatabaseCount('pos_sales', 1);
        $this->assertDatabaseHas('desktop_pos_sync_logs', [
            'machine_id' => $terminal->machine_id,
            'status' => 'success',
        ]);
    }

    public function test_registered_terminal_syncs_sale_with_minimal_sale_fields(): void
    {
        [$terminal] = $this->registeredTerminal();
        $payload = $this->salePayload();
        $payload['payload'] = [
            'sale' => ['total' => 3],
            'items' => [[]],
        ];

        $this->withHeaders(['Idempotency-Key' => 'minimal-sale'])
            ->postJson('/api/desktop-pos/sync', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseHas('pos_sales', [
            'business_id' => $terminal->business_id,
            'desktop_sale_id' => 19,
            'total' => 3,
        ]);
    }

    public function test_reusing_an_idempotency_key_for_the_same_sale_updates_its_data(): void
    {
        [$terminal] = $this->registeredTerminal();
        $headers = ['Idempotency-Key' => 'same-key'];

        $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $this->salePayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $changedPayload = $this->salePayload();
        $changedPayload['payload']['sale']['total'] = 999;

        $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $changedPayload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseCount('pos_sales', 1);
        $this->assertDatabaseCount('desktop_pos_sync_logs', 1);
        $this->assertDatabaseHas('pos_sales', [
            'terminal_id' => $terminal->id,
            'desktop_sale_id' => 19,
            'total' => 999,
        ]);
    }

    public function test_registered_terminal_upserts_product_without_bearer_token(): void
    {
        [$terminal] = $this->registeredTerminal();

        $this->withHeaders(['Idempotency-Key' => 'product-9-upsert-event-1'])
            ->postJson('/api/desktop-pos/sync', [
                'client_id' => $terminal->client_id,
                'machine_id' => $terminal->machine_id,
                'entity_type' => 'product',
                'entity_id' => 9,
                'operation' => 'upsert',
                'payload' => [
                    'id' => 9,
                    'name' => 'Sample Product',
                    'sku' => 'SKU-9',
                    'barcode' => '123456789',
                    'price' => 125.50,
                    'stock' => 7,
                ],
            ])->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseHas('products', [
            'business_id' => $terminal->business_id,
            'desktop_product_id' => 9,
            'name' => 'Sample Product',
            'sku' => 'SKU-9',
            'unit_price' => 125.50,
            'stock_quantity' => 7,
        ]);
    }

    public function test_product_details_page_displays_the_saved_payload_fields(): void
    {
        [$terminal] = $this->registeredTerminal();
        $payload = [
            'id' => 6,
            'sku' => 'GNN - 01',
            'name' => 'guinness',
            'price' => 270,
            'stock' => 47,
            'barcode' => '2000000000015',
            'is_active' => 1,
            'unit_label' => 'unit',
            'buying_price' => 219,
            'product_type' => 'standard',
            'offer_enabled' => 0,
            'offer_percent' => 0,
            'conversion_quantity' => 1,
        ];

        $this->withHeaders(['Idempotency-Key' => 'product-6-details'])
            ->postJson('/api/desktop-pos/sync', [
                'client_id' => $terminal->client_id,
                'machine_id' => $terminal->machine_id,
                'entity_type' => 'product',
                'entity_id' => 6,
                'operation' => 'upsert',
                'payload' => $payload,
            ])->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $product = $terminal->business->products()->where('desktop_product_id', 6)->firstOrFail();
        $user = $terminal->business->users()->create([
            'name' => 'Store Owner',
            'email' => 'product-owner@example.test',
            'password' => 'password-password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Received product fields')
            ->assertSee('GNN - 01')
            ->assertSee('Buying Price')
            ->assertSee('219')
            ->assertSee('Offer Enabled')
            ->assertSee('Disabled')
            ->assertSee('Conversion Quantity')
            ->assertSee('standard');
    }

    public function test_unregistered_machine_request_is_retained_without_business_assignment(): void
    {
        $this->registeredTerminal();
        $payload = $this->salePayload();
        $payload['machine_id'] = 'unregistered-machine';
        $payload['client_id'] = 'unregistered-client';

        $this->withHeaders(['Idempotency-Key' => 'unregistered-request'])
            ->postJson('/api/desktop-pos/sync', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.receipt_url', null);

        $this->assertDatabaseHas('desktop_pos_sync_logs', [
            'machine_id' => 'unregistered-machine',
            'status' => 'pending',
            'error_message' => '[unregistered-terminal] Register this exact machine_id and client_id to a business before importing this request.',
        ]);
        $this->assertDatabaseCount('pos_terminals', 1);
        $this->assertDatabaseCount('pos_sales', 0);
    }

    public function test_unregistered_request_is_recovered_after_admin_registers_matching_terminal(): void
    {
        $this->registeredTerminal();
        $payload = $this->salePayload();
        $payload['machine_id'] = 'machine-waiting-for-registration';
        $payload['client_id'] = 'client-waiting-for-registration';

        $this->withHeaders(['Idempotency-Key' => 'late-register-request'])
            ->postJson('/api/desktop-pos/sync', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $business = Business::query()->where('business_code', 'TEST-001')->firstOrFail();
        PosTerminal::query()->create([
            'business_id' => $business->id,
            'machine_id' => $payload['machine_id'],
            'client_id' => $payload['client_id'],
            'terminal_name' => 'Newly registered terminal',
            'api_token_hash' => Hash::make('optional-token'),
            'is_active' => true,
        ]);

        $this->travel(2)->minutes();
        $this->artisan('kikimo:recover-syncs')->assertExitCode(0);
        $this->assertDatabaseCount('pos_sales', 1);
        $this->assertDatabaseCount('desktop_pos_sync_logs', 1);
    }

    public function test_registered_terminal_sync_ignores_bearer_header(): void
    {
        [$terminal] = $this->registeredTerminal();

        $this->withHeaders($this->headers($terminal, 'wrong-token'))
            ->postJson('/api/desktop-pos/sync', $this->salePayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseCount('desktop_pos_sync_logs', 1);
        $this->assertDatabaseCount('pos_sales', 1);
    }

    public function test_sales_from_other_business_are_not_visible(): void
    {
        [$terminal] = $this->registeredTerminal();
        $otherBusiness = Business::query()->create([
            'name' => 'Other business',
            'business_code' => 'OTHER-001',
            'currency' => 'KES',
        ]);
        $otherTerminal = PosTerminal::query()->create([
            'business_id' => $otherBusiness->id,
            'machine_id' => 'machine-other',
            'client_id' => 'client-other',
            'terminal_name' => 'Other terminal',
            'api_token_hash' => Hash::make('other-token'),
            'is_active' => true,
        ]);
        $otherOwnBusinessTerminal = PosTerminal::query()->create([
            'business_id' => $terminal->business_id,
            'machine_id' => 'machine-other-till',
            'client_id' => 'client-other-till',
            'terminal_name' => 'Other till',
            'api_token_hash' => Hash::make('other-till-token'),
            'is_active' => true,
        ]);

        $this->createSale($terminal, 19, 'OWN-RECEIPT');
        $this->createSale($otherOwnBusinessTerminal, 21, 'OTHER-TILL-RECEIPT');
        $this->createSale($otherTerminal, 20, 'PRIVATE-RECEIPT');

        $user = $terminal->business->users()->create([
            'name' => 'Business Staff',
            'email' => 'staff@example.test',
            'password' => 'password-password',
            'role' => 'staff',
            'pos_terminal_id' => $terminal->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/sales')
            ->assertOk()
            ->assertSee('OWN-RECEIPT')
            ->assertDontSee('OTHER-TILL-RECEIPT')
            ->assertDontSee('PRIVATE-RECEIPT');
    }

    public function test_administrator_assigns_a_user_to_a_terminal_and_can_reset_the_password(): void
    {
        [$terminal] = $this->registeredTerminal();
        $admin = $terminal->business->users()->create([
            'name' => 'Store Owner',
            'email' => 'owner@example.test',
            'password' => 'password-password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'New Cashier',
            'email' => 'cashier@example.test',
            'password' => 'initial-password',
            'password_confirmation' => 'initial-password',
            'role' => 'staff',
            'pos_terminal_id' => $terminal->id,
        ])->assertRedirect();

        $cashier = User::query()->where('email', 'cashier@example.test')->firstOrFail();
        $this->assertSame($terminal->id, $cashier->pos_terminal_id);
        $this->assertTrue(Hash::check('initial-password', $cashier->password));

        $this->patch('/users/'.$cashier->id, [
            'role' => 'staff',
            'is_active' => true,
            'pos_terminal_id' => $terminal->id,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('replacement-password', $cashier->fresh()->password));
    }

    public function test_signed_in_user_can_change_their_own_password_after_confirming_current_password(): void
    {
        [$terminal] = $this->registeredTerminal();
        $user = $terminal->business->users()->create([
            'name' => 'Store Owner',
            'email' => 'owner-password@example.test',
            'password' => 'initial-password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'initial-password',
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));

        $this->put('/account/password', [
            'current_password' => 'incorrect-password',
            'password' => 'another-replacement-password',
            'password_confirmation' => 'another-replacement-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
    }

    private function registeredTerminal(): array
    {
        $business = Business::query()->create([
            'name' => 'Kikimo Test Store',
            'business_code' => 'TEST-001',
            'currency' => 'KES',
        ]);
        $token = 'test-terminal-token';
        $terminal = PosTerminal::query()->create([
            'business_id' => $business->id,
            'machine_id' => 'machine-test-001',
            'client_id' => 'client-test-001',
            'terminal_name' => 'Test till',
            'api_token_hash' => Hash::make($token),
            'is_active' => true,
        ]);

        return [$terminal, $token];
    }

    private function headers(PosTerminal $terminal, string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'Idempotency-Key' => 'sale-request-19',
        ];
    }

    private function salePayload(): array
    {
        return [
            'client_id' => 'client-test-001',
            'machine_id' => 'machine-test-001',
            'entity_type' => 'sale',
            'entity_id' => 19,
            'operation' => 'create',
            'payload' => [
                'sale' => [
                    'id' => 19,
                    'receipt_no' => 'KDP-TEST-19',
                    'subtotal' => 1000,
                    'total' => 880,
                    'amount_paid' => 880,
                    'change_due' => 0,
                    'payment_method' => 'Cash',
                    'cashier_name' => 'Test cashier',
                    'cashier_id' => 1,
                    'created_at' => '2026-10-31T07:58:27.347Z',
                ],
                'items' => [[
                    'id' => 45,
                    'sale_id' => 19,
                    'product_id' => 9,
                    'product_name' => 'Sample item',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'line_total' => 1000,
                ]],
            ],
        ];
    }

    private function createSale(PosTerminal $terminal, int $saleId, string $receipt): PosSale
    {
        $log = DesktopPosSyncLog::query()->create([
            'client_id' => $terminal->client_id,
            'machine_id' => $terminal->machine_id,
            'entity_type' => 'sale',
            'entity_id' => $saleId,
            'operation' => 'create',
            'payload' => [],
            'status' => 'success',
            'received_at' => now(),
            'processed_at' => now(),
        ]);

        return PosSale::query()->create([
            'business_id' => $terminal->business_id,
            'terminal_id' => $terminal->id,
            'source_sync_log_id' => $log->id,
            'machine_id' => $terminal->machine_id,
            'desktop_sale_id' => $saleId,
            'receipt_no' => $receipt,
            'subtotal' => 50,
            'total' => 50,
            'amount_paid' => 50,
            'change_due' => 0,
            'sold_at' => now(),
        ]);
    }
}
