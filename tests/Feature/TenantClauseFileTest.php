<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantClause;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TenantClauseFileTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        Permission::create(['guard_name' => 'web', 'name' => 'settings.clauses']);
        $this->user->givePermissionTo('settings.clauses');
    }

    public function test_store_clause_with_pdf_file_creates_has_file_true(): void
    {
        Storage::fake('r2');

        $file = UploadedFile::fake()->create('policy.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('settings.clauses.store'), [
                'title' => 'Política de garantía',
                'type' => 'warranty',
                'file' => $file,
                'print_on_receipt' => true,
            ]);

        $response->assertSessionHas('success');

        $clause = TenantClause::where('title', 'Política de garantía')->first();

        $this->assertNotNull($clause);
        $this->assertTrue($clause->has_file);
        $this->assertNotNull($clause->file_path);
        $this->assertNotNull($clause->file_name);
        $this->assertNotNull($clause->file_url);
        $this->assertEquals('policy.pdf', $clause->file_name);
        $this->assertStringContainsString('clauses/', $clause->file_path);
        $this->assertStringContainsString('/r2/', $clause->file_url);
    }

    public function test_store_clause_without_file_creates_content_text(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.clauses.store'), [
                'title' => 'Términos y condiciones',
                'type' => 'terms',
                'content' => 'Este es el contenido de la cláusula.',
                'print_on_receipt' => true,
            ]);

        $response->assertSessionHas('success');

        $clause = TenantClause::where('title', 'Términos y condiciones')->first();

        $this->assertNotNull($clause);
        $this->assertFalse($clause->has_file);
        $this->assertNull($clause->file_path);
        $this->assertNull($clause->file_name);
        $this->assertNull($clause->file_url);
        $this->assertEquals('Este es el contenido de la cláusula.', $clause->content);
    }

    public function test_update_clause_replaces_file(): void
    {
        Storage::fake('r2');

        $clause = TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Original',
            'content' => 'Contenido original.',
            'type' => 'terms',
            'is_active' => true,
            'print_on_receipt' => true,
        ]);

        $file = UploadedFile::fake()->create('new_policy.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->put(route('settings.clauses.update', $clause), [
                'title' => 'Actualizado',
                'type' => 'warranty',
                'is_active' => true,
                'print_on_receipt' => true,
                'file' => $file,
            ]);

        $response->assertSessionHas('success');

        $clause->refresh();

        $this->assertTrue($clause->has_file);
        $this->assertNotNull($clause->file_path);
        $this->assertEquals('new_policy.pdf', $clause->file_name);
    }

    public function test_delete_clause_with_file_removes_from_storage(): void
    {
        Storage::fake('r2');

        $path = 'clauses/2026/06/test.pdf';
        Storage::disk('r2')->put($path, 'fake content', ['visibility' => 'public']);

        $clause = TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Con archivo',
            'content' => null,
            'type' => 'other',
            'is_active' => true,
            'print_on_receipt' => false,
            'file_path' => $path,
            'file_name' => 'test.pdf',
            'file_url' => route('r2.serve', ['path' => $path]),
            'has_file' => true,
        ]);

        $this->assertTrue(Storage::disk('r2')->exists($path));

        $response = $this->actingAs($this->user)
            ->delete(route('settings.clauses.destroy', $clause));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tenant_clauses', ['id' => $clause->id]);
        $this->assertFalse(Storage::disk('r2')->exists($path));
    }

    public function test_file_clause_renders_link_instead_of_text_in_print_view(): void
    {
        $clause = TenantClause::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Política PDF',
            'content' => null,
            'type' => 'terms',
            'is_active' => true,
            'print_on_receipt' => true,
            'file_path' => 'clauses/test.pdf',
            'file_name' => 'policy.pdf',
            'file_url' => route('r2.serve', ['path' => 'clauses/test.pdf']),
            'has_file' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('settings.clauses'));

        $response->assertOk();
        $response->assertSee('Ver archivo');
        $response->assertSee('policy.pdf');
        $response->assertDontSee($clause->content);
    }
}
