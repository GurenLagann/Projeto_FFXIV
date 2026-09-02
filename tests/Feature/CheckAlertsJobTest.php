<?php

namespace Tests\Feature;

use App\Clients\UniversalisClient;
use App\DTOs\ItemPrice;
use App\Jobs\CheckAlertsJob;
use App\Mail\AlertTriggeredMail;
use App\Models\Alert;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckAlertsJobTest extends TestCase
{
    use RefreshDatabase;

    private function makeItemPrice(int $itemId, int $minPriceNQ): ItemPrice
    {
        return new ItemPrice(
            itemId:                  $itemId,
            minPriceNQ:              $minPriceNQ,
            minPriceHQ:              0,
            medianSalePriceNQ:       (float) $minPriceNQ,
            medianSalePriceHQ:       0.0,
            averageSalePriceNQ:      (float) $minPriceNQ,
            recentPurchasePriceNQ:   $minPriceNQ,
            regionMinPriceNQ:        $minPriceNQ,
            regionMedianSalePriceNQ: (float) $minPriceNQ,
            salesPerWeek:            10.0,
        );
    }

    private function createAlertWithRecipe(string $serverSlug, string $itemId, string $materialId): Alert
    {
        $user   = User::factory()->create();
        $server = Server::create([
            'name' => $serverSlug, 'slug' => $serverSlug,
            'datacenter' => 'Crystal', 'region' => 'NA',
        ]);
        $item     = Item::create(['id' => $itemId, 'name' => "Item {$itemId}", 'is_craftable' => true]);
        $material = Item::create(['id' => $materialId, 'name' => "Material {$materialId}"]);
        $recipe   = Recipe::create([
            'item_id' => $itemId, 'job_id' => 9, 'craft_level' => 1,
            'yields' => 1, 'can_be_hq' => false, 'stars' => 0, 'difficulty' => 0,
        ]);
        $recipe->materials()->attach($materialId, ['quantity' => 1]);

        return Alert::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'server_id' => $server->id,
            'min_profit' => 100,
            'min_margin' => 1,
        ]);
    }

    public function test_a_failing_server_does_not_prevent_other_servers_from_being_checked(): void
    {
        Mail::fake();

        $failingAlert = $this->createAlertWithRecipe('balmung', '1001', '2001');
        $okAlert      = $this->createAlertWithRecipe('gilgamesh', '1002', '2002');

        $this->mock(UniversalisClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPrices')
                ->with('balmung', \Mockery::any())
                ->andThrow(new \RuntimeException('Universalis unavailable for balmung'));

            $mock->shouldReceive('getPrices')
                ->with('gilgamesh', \Mockery::any())
                ->andReturn([
                    '1002' => $this->makeItemPrice(1002, 10_000),
                    '2002' => $this->makeItemPrice(2002, 100),
                ]);
        });

        app(CheckAlertsJob::class)->handle(app(UniversalisClient::class), app(\App\Services\ProfitCalculator::class));

        Mail::assertNotSent(AlertTriggeredMail::class, function (AlertTriggeredMail $mail) use ($failingAlert) {
            return $mail->alert->is($failingAlert);
        });
        Mail::assertSent(AlertTriggeredMail::class, function (AlertTriggeredMail $mail) use ($okAlert) {
            return $mail->alert->is($okAlert);
        });
    }
}
