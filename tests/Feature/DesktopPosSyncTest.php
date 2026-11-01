<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosTerminal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class DesktopPosSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('desktop_pos_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('client_id', 100);
            $table->string('machine_id', 100);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('operation', 20);
            $table->json('payload');
            $table->string('idempotency_key', 150)->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['machine_id', 'idempotency_key']);
        });
    }

    public function test_registered_terminal_syncs_sale_and_retries_idempotently(): void
    {
        [$terminal, $token] = $this->registeredTerminal();
        $payload = $this->salePayload();
        $headers = $this->headers($terminal, $token);

        $first = $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $payload);
        $first->assertOk()->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('pos_sales', 1);
        $this->assertDatabaseCount('pos_sale_items', 1);
        $this->assertDatabaseHas('desktop_pos_sync_logs', [
            'machine_id' => $terminal->machine_id,
            'entity_id' => 19,
            'status' => 'success',
        ]);

        $duplicate = $this->withHeaders($headers)->postJson('/api/desktop-pos/sync', $payload);
        $duplicate->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame($first->json('sync_id'), $duplicate->json('sync_id'));
        $this->assertDatabaseCount('pos_sales', 1);
    }

    public function test_machine_id_without_bearer_token_is_not_authorized(): void
    {
        [$terminal] = $this->registeredTerminal();

        $this->postJson('/api/desktop-pos/sync', $this->salePayload(), [
            'Idempotency-Key' => 'missing-token',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('desktop_pos_sync_logs', 0);
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

        $this->createSale($terminal, 19, 'OWN-RECEIPT');
        $this->createSale($otherTerminal, 20, 'PRIVATE-RECEIPT');

        $user = $terminal->business->users()->create([
            'name' => 'Business Staff',
            'email' => 'staff@example.test',
            'password' => 'password-password',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/sales')
            ->assertOk()
            ->assertSee('OWN-RECEIPT')
            ->assertDontSee('PRIVATE-RECEIPT');
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
                    'total' => 1000,
                    'amount_paid' => 1000,
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
