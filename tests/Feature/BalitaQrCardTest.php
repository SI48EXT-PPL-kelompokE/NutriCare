<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Generator;
use Tests\TestCase;

class BalitaQrCardTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (! isset(RefreshDatabaseState::$inMemoryConnections['sqlite'])) {
            RefreshDatabaseState::$migrated = false;
        }
    }

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        return $app;
    }

    public function test_registration_assigns_a_unique_identifier_and_edit_keeps_it_on_the_card(): void
    {
        $this->actingAs($this->userWithPermissions('Kader Posyandu', ['balita' => ['can_create', 'can_edit']]));
        $payload = [
            'orang_tua_id' => OrangTua::factory()->create()->id,
            'name' => 'Nabila Putri',
            'birth_date' => '2024-05-15',
            'jenis_kelamin' => 'P',
        ];
        $first = $this->postJson('/api/balita', $payload)->assertCreated()->json('data');
        $second = $this->postJson('/api/balita', $payload)->assertCreated()->json('data');

        $this->assertMatchesRegularExpression('/^NC-[A-Z0-9]{10}$/', $first['qr_code']);
        $this->assertMatchesRegularExpression('/^NC-[A-Z0-9]{10}$/', $second['qr_code']);
        $this->assertNotSame($first['qr_code'], $second['qr_code']);

        $this->patchJson("/api/balita/{$first['id']}", ['name' => 'Nabila Putri Maharani'])
            ->assertOk()
            ->assertJsonPath('data.qr_code', $first['qr_code']);

        $this->actingAs($this->userWithBalitaPermission('Nakes / Bidan'));
        $this->get(route('kependudukan.balita.identity-card', $first['id']))
            ->assertOk()
            ->assertSee('Nabila Putri Maharani')
            ->assertSee($first['qr_code']);
    }

    public function test_existing_balita_qr_and_print_card_use_only_the_saved_identifier_as_payload(): void
    {
        $balita = Balita::factory()->create([
            'name' => 'Nabila & Putri',
            'birth_date' => '2024-05-15',
            'jenis_kelamin' => 'P',
            'qr_code' => 'NC-ABC123DE45',
        ]);
        $expectedQr = (string) (new Generator)->format('svg')->size(232)->margin(4)->generate('NC-ABC123DE45');
        $this->actingAs($this->userWithBalitaPermission());

        $this->get(route('kependudukan.balita.qr', $balita))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertContent($expectedQr);

        $card = $this->get(route('kependudukan.balita.identity-card', $balita));
        $card->assertOk()
            ->assertSee('NutriCare')
            ->assertSee('Kartu Identitas Balita')
            ->assertSee('Nabila & Putri')
            ->assertSee('15 Mei 2024')
            ->assertSee('Perempuan')
            ->assertSee('NC-ABC123DE45')
            ->assertSee('data:image/svg+xml;base64,'.base64_encode($expectedQr))
            ->assertDontSee($balita->orangTua->nik)
            ->assertDontSee($balita->orangTua->alamat)
            ->assertViewHas('printOnLoad', false);

        $this->get(route('kependudukan.balita.identity-card', ['balita' => $balita, 'print' => 1]))
            ->assertOk()
            ->assertViewHas('printOnLoad', true)
            ->assertSee('data:image/svg+xml;base64,'.base64_encode($expectedQr))
            ->assertSee('@media print')
            ->assertSee('window.print()', false);
    }

    public function test_qr_dialog_triggers_point_to_the_correct_balita_after_filter_and_pagination(): void
    {
        [$first, $second] = Balita::factory()->count(2)->sequence(
            ['name' => 'Ahmad Rayhan', 'qr_code' => 'NC-AHMAD12345', 'created_at' => now()->subDays(2)],
            ['name' => 'Nabila Putri', 'qr_code' => 'NC-NABIL12345', 'created_at' => now()->subDay()],
        )->create()->all();
        Balita::factory()->count(7)->create(['created_at' => now()]);
        $this->actingAs($this->userWithBalitaPermission());

        $response = $this->get(route('kependudukan.balita.index', ['page' => 2]))->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $buttons = $xpath->query('//button[@data-identity-qr]');

        $this->assertCount(1, $xpath->query('//dialog[@id="balita-qr-dialog"]'));
        $this->assertCount(2, $buttons);
        foreach ([$second, $first] as $index => $balita) {
            $button = $buttons->item($index);
            $this->assertSame($balita->name, $button->getAttribute('data-identity-name'));
            $this->assertSame($balita->qr_code, $button->getAttribute('data-identity-code'));
            $this->assertSame(route('kependudukan.balita.qr', $balita), $button->getAttribute('data-identity-qr'));
            $this->assertSame(route('kependudukan.balita.identity-card', $balita), $button->getAttribute('data-identity-card'));
        }

        $this->get(route('kependudukan.balita.index', ['search' => $first->name]))
            ->assertOk()
            ->assertSee('data-identity-code="'.$first->qr_code.'"', false)
            ->assertDontSee('data-identity-code="'.$second->qr_code.'"', false);
    }

    public function test_qr_and_card_use_web_middleware_and_require_an_active_user_with_balita_view_permission(): void
    {
        $balita = Balita::factory()->create();
        $indexRoute = Route::getRoutes()->getByName('kependudukan.balita.index');

        foreach (['kependudukan.balita.qr', 'kependudukan.balita.identity-card'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            foreach ($indexRoute->gatherMiddleware() as $existingMiddleware) {
                $this->assertContains($existingMiddleware, $middleware);
            }
            $this->get(route($name, $balita))->assertRedirect(route('login'));
        }

        $this->get(route('kependudukan.balita.index'))
            ->assertRedirect(route('login'));

        $residentRole = Role::create(['name_role' => 'Warga / Orang Tua']);
        $this->actingAs(User::factory()->create(['role_id' => $residentRole->id]));
        $this->get(route('kependudukan.balita.qr', $balita))->assertForbidden();
        $this->get(route('kependudukan.balita.identity-card', $balita))->assertForbidden();

        $this->actingAs($this->userWithBalitaPermission(active: false));
        $this->get(route('kependudukan.balita.qr', $balita))->assertRedirect(route('login'));
        $this->get(route('kependudukan.balita.identity-card', $balita))->assertRedirect(route('login'));

        $this->actingAs($this->userWithBalitaPermission('Nakes / Bidan'));
        $missingId = $balita->id + 1;
        $this->get(route('kependudukan.balita.qr', $missingId))->assertNotFound();
        $this->get(route('kependudukan.balita.identity-card', $missingId))->assertNotFound();
    }

    private function userWithBalitaPermission(string $roleName = 'Kader Posyandu', bool $active = true): User
    {
        $role = Role::create(['name_role' => $roleName]);
        $role->permissions()->create(['name' => 'Balita', 'module_name' => 'balita', 'can_view' => true]);

        return User::factory()->create(['role_id' => $role->id, 'active_status' => $active]);
    }
}
