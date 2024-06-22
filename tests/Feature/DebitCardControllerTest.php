<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Carbon\Carbon;

class DebitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCards()
    {
        // get /debit-cards
        DebitCard::factory()->active()->for($this->user)->create();

        $response = $this->getJson('/api/debit-cards/');

        $response->assertStatus(200)->assertJsonCount(1);
    }

    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        // get /debit-cards
        $otherUser = User::factory()->create();
        DebitCard::factory()->for($otherUser)->create();

        $response = $this->getJson('/api/debit-cards/');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testCustomerCanCreateADebitCard()
    {
        // post /debit-cards
        $response = $this->postJson('/api/debit-cards/', [
            'type' => 'Visa',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('debit_cards', [
            'user_id' => $this->user->id,
            'type' => 'Visa',
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create();

        $response = $this->getJson('/api/debit-cards/' . $debitCard->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $debitCard->id,
            ]);
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
        $otherUser = User::factory()->create();
        $debitCard = DebitCard::factory()->for($otherUser)->create();

        $response = $this->getJson('/api/debit-cards/' . $debitCard->id);

        $response->assertStatus(403);
    }

    public function testCustomerCanActivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create([
            'disabled_at' => Carbon::now(),
        ]);

        $response = $this->putJson('/api/debit-cards/' . $debitCard->id, [
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $this->assertNull($debitCard->fresh()->disabled_at);
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create([
            'disabled_at' => null,
        ]);

        $response = $this->putJson('/api/debit-cards/' . $debitCard->id, [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($debitCard->fresh()->disabled_at);
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        // put api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create();

        $response = $this->putJson('/api/debit-cards/' . $debitCard->id, [
            'is_active' => 'not_a_boolean',
        ]);

        $response->assertStatus(422);
    }

    public function testCustomerCanDeleteADebitCard()
    {
        // delete api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create();

        $response = $this->deleteJson('/api/debit-cards/' . $debitCard->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('debit_cards', ['id' => $debitCard->id]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        // delete api/debit-cards/{debitCard}
        $debitCard = DebitCard::factory()->for($this->user)->create();
        $debitCard->debitCardTransactions()->create([
            'amount' => 100,
            'currency_code' => 'IDR',
        ]);

        $response = $this->deleteJson('/api/debit-cards/' . $debitCard->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('debit_cards', ['id' => $debitCard->id]);
    }
    
    // Extra bonus for extra tests :)
}
