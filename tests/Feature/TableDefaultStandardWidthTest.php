<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Urusan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TableDefaultStandardWidthTest extends TestCase
{
    public function test_default_table_headers_render_compact_standard_widths(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::factory()->create([
                'username' => 'superadmin_test',
                'role' => 'super_admin',
            ]);
        }

        $urusan = Urusan::first();
        $this->assertNotNull($urusan);

        $response = $this->actingAs($superAdmin)->get(route('urusan.show', $urusan->slug ?: $urusan->id));
        $response->assertStatus(200);

        $html = $response->getContent();

        // 1. Pastikan kolom Uraian tidak memiliki min-width hardcoded (seperti 240px atau 120px) yang memaksanya melebar saat kata pendek
        $this->assertDoesNotMatchRegularExpression('/data-col-id="[^"]*"\s+class="[^"]*column-header-th[^"]*"\s+style="[^"]*min-width:\s*(240px|120px)/i', $html);

        // 2. Pastikan sub-kolom tidak dipaksa min-width: 60px yang membuat sub-kolom menciut dan mendesak uraian melebar
        $this->assertDoesNotMatchRegularExpression('/class="[^"]*sub-col-header-th[^"]*"\s+style="[^"]*min-width:\s*60px/i', $html);

        // 3. Pastikan kolom Aksi tetap sticky dan fixed 78px
        $this->assertStringContainsString('sticky-action-col', $html);
        $this->assertStringContainsString('width: 78px; min-width: 78px; max-width: 78px;', $html);
    }
}
