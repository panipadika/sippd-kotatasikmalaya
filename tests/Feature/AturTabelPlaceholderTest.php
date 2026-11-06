<?php

namespace Tests\Feature;

use App\Models\KelompokUrusan;
use App\Models\User;
use App\Models\Urusan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AturTabelPlaceholderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_setup_table_with_empty_col_names_uses_fallback_placeholder()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test_placeholder@tasikmalayakota.go.id'],
            [
                'name' => 'Admin Test Placeholder',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status_aktif' => true,
            ]
        );

        $root = KelompokUrusan::firstOrCreate(
            ['kode' => 'PL-01'],
            ['nama' => 'Pendidikan Placeholder Test', 'parent_id' => null]
        );

        $urusan = Urusan::create([
            'kelompok_urusan_id' => $root->id,
            'kode_urusan' => 'PL-01.01',
            'nama_urusan' => 'Urusan Placeholder Test',
            'slug' => 'urusan-placeholder-test-' . time(),
            'status_aktif' => true,
        ]);

        // Submit setup_table dengan col_names kosong (hanya mengandalkan placeholder)
        $response = $this->actingAs($admin)->post(route('urusan.columns', $urusan->slug), [
            'action' => 'setup_table',
            'table_id' => 1,
            'num_cols' => 3,
            'num_rows' => 5,
            'col_names' => ['', '', ''],
            'col_types' => ['text', 'number', 'text'],
        ]);

        $response->assertRedirect();

        $urusan->refresh();
        $cols = $urusan->getColumnsForTable(1);

        $this->assertCount(3, $cols);
        $this->assertEquals('Kolom 1', $cols[0]['name']);
        $this->assertEquals('Kolom 2', $cols[1]['name']);
        $this->assertEquals('Kolom 3', $cols[2]['name']);
    }

    public function test_setup_table_with_custom_names_preserves_custom_values()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test_custom@tasikmalayakota.go.id'],
            [
                'name' => 'Admin Test Custom',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status_aktif' => true,
            ]
        );

        $root = KelompokUrusan::firstOrCreate(
            ['kode' => 'PL-02'],
            ['nama' => 'Pendidikan Custom Test', 'parent_id' => null]
        );

        $urusan = Urusan::create([
            'kelompok_urusan_id' => $root->id,
            'kode_urusan' => 'PL-02.01',
            'nama_urusan' => 'Urusan Custom Test',
            'slug' => 'urusan-custom-test-' . time(),
            'status_aktif' => true,
        ]);

        // Submit setup_table dengan custom names
        $response = $this->actingAs($admin)->post(route('urusan.columns', $urusan->slug), [
            'action' => 'setup_table',
            'table_id' => 1,
            'num_cols' => 3,
            'num_rows' => 5,
            'col_names' => ['no', 'thn', 'text'],
            'col_types' => ['text', 'number', 'text'],
        ]);

        $response->assertRedirect();

        $urusan->refresh();
        $cols = $urusan->getColumnsForTable(1);

        $this->assertCount(3, $cols);
        $this->assertEquals('no', $cols[0]['name']);
        $this->assertEquals('thn', $cols[1]['name']);
        $this->assertEquals('text', $cols[2]['name']);
    }

    public function test_view_contains_modal_state_management_scripts()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_view_test@tasikmalayakota.go.id'],
            [
                'name' => 'Admin View Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status_aktif' => true,
            ]
        );

        $root = KelompokUrusan::firstOrCreate(
            ['kode' => 'PL-03'],
            ['nama' => 'Pendidikan View Test', 'parent_id' => null]
        );

        $urusan = Urusan::create([
            'kelompok_urusan_id' => $root->id,
            'kode_urusan' => 'PL-03.01',
            'nama_urusan' => 'Urusan View Test',
            'slug' => 'urusan-view-test-' . time(),
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('urusan.show', $urusan->slug));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('modalColState', $content);
        $this->assertStringContainsString('syncModalColStateFromDOM', $content);
        $this->assertStringContainsString('initModalColState', $content);
        $this->assertStringContainsString('col-name-input', $content);
    }
}
