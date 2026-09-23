<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\History\Http\Controllers\HistoryController;
use Tests\TestCase;

class HistoryTableSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'history_test', 'database.connections.history_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->softDeletes();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('entity_type');
            $table->integer('entity_id');
            $table->string('description');
            $table->timestamps();
        });
        DB::table('users')->insert(['id' => 1, 'first_name' => 'Alice', 'last_name' => 'Rahman', 'email' => 'alice@example.test']);
        foreach (['created', 'deleted'] as $action) {
            DB::table('audit_logs')->insert([
                'user_id' => $action === 'created' ? 1 : null, 'action' => $action,
                'entity_type' => 'Product', 'entity_id' => 5,
                'description' => $action === 'created' ? 'Added blue shirt' : 'Removed red shirt',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_search_filters_records_and_preserves_details_actions(): void
    {
        foreach (['Alice', 'Rahman', 'alice@example.test', 'blue', 'created'] as $term) {
            $result = $this->search($term);
            $this->assertArrayNotHasKey('error', $result);
            $this->assertSame(1, $result['recordsFiltered'], $term);
            $this->assertStringContainsString('data-history-action="details"', $result['data'][0]['row_actions']);
        }
        $this->assertSame(0, $this->search('missing-term')['recordsFiltered']);
    }

    public function test_search_combines_with_dropdown_filter_and_hides_unauthorized_restore(): void
    {
        $this->assertSame(0, $this->search('blue', 'deleted')['recordsFiltered']);
        $result = $this->search('red', 'deleted');
        $this->assertSame(1, $result['recordsFiltered']);
        $this->assertStringNotContainsString('data-history-action="restore"', $result['data'][0]['row_actions']);
    }

    private function search(string $term, ?string $action = null): array
    {
        $columns = array_map(fn ($name) => ['data' => $name, 'name' => $name,
            'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['action_badge', 'entity_label', 'user_name', 'description']);
        $request = Request::create('/dataTable/histories', 'GET', [
            'draw' => 1, 'start' => 0, 'length' => 10, 'columns' => $columns,
            'search' => ['value' => $term, 'regex' => 'false'], 'action' => $action,
        ]);
        $this->app->instance('request', $request);
        return $this->app->make(HistoryController::class)->dataTable($request)->getData(true);
    }
}
