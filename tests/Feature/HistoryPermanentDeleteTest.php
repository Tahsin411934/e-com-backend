<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Services\CampaignService;
use Modules\Catalog\Models\Product;
use Modules\History\Models\History;
use Modules\History\Services\HistoryService;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Support\CurrentStore;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HistoryPermanentDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CurrentStore::set(null);
        $user = new User;
        $user->setRelation('roles', collect([new Role(['name' => 'Super Admin'])]));
        $this->actingAs($user);
    }

    private function deletedCampaign(): array
    {
        $campaign = Campaign::create(['name' => 'Offer', 'slug' => 'offer', 'status' => 'active']);
        app(CampaignService::class)->destroy($campaign);
        $history = History::where('entity_type', Campaign::class)
            ->where('entity_id', $campaign->id)->where('action', 'deleted')->latest('id')->firstOrFail();

        return [$campaign, $history];
    }

    public function test_campaign_soft_delete_restore_and_permanent_delete(): void
    {
        [$campaign, $history] = $this->deletedCampaign();
        $service = app(HistoryService::class);
        $this->assertSoftDeleted('campaigns', ['id' => $campaign->id]);
        $this->assertTrue($service->canPermanentlyDelete($history));
        $service->restore($history->id);
        $this->assertNotSoftDeleted('campaigns', ['id' => $campaign->id]);
        $this->assertFalse($service->canPermanentlyDelete($history));
        $campaign->refresh()->delete();
        $service->permanentlyDelete($history->id);
        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $history->id]);
        $this->assertFalse($service->canPermanentlyDelete($history));
    }

    public function test_restored_records_cannot_be_permanently_deleted(): void
    {
        [$campaign, $history] = $this->deletedCampaign();
        $campaign->restore();
        $this->expectException(InvalidArgumentException::class);
        app(HistoryService::class)->permanentlyDelete($history->id);
    }

    public function test_permanent_delete_also_supports_catalog_products(): void
    {
        $product = Product::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active', 'visibility' => 'public', 'product_type' => 'physical']);
        $product->delete();
        $history = History::where('entity_type', Product::class)
            ->where('entity_id', $product->id)->where('action', 'deleted')->latest('id')->firstOrFail();
        app(HistoryService::class)->permanentlyDelete($history->id);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_store_context_cannot_permanently_delete_records(): void
    {
        [, $history] = $this->deletedCampaign();
        CurrentStore::set(999);
        try {
            $this->expectException(HttpException::class);
            app(HistoryService::class)->permanentlyDelete($history->id);
        } finally {
            CurrentStore::set(null);
        }
    }

    public function test_unregistered_model_cannot_be_permanently_deleted(): void
    {
        $history = History::create([
            'action' => 'deleted', 'entity_type' => History::class,
            'entity_id' => 1, 'description' => 'Unsupported model',
        ]);
        $this->expectException(InvalidArgumentException::class);
        app(HistoryService::class)->permanentlyDelete($history->id);
    }
}
