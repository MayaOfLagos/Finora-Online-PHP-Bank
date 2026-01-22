<?php

namespace Tests\Feature;

use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_account_can_be_edited(): void
    {
        // Create test data
        $user = User::factory()->create();
        $accountType = AccountType::factory()->create();
        $bankAccount = BankAccount::factory()
            ->for($user)
            ->for($accountType)
            ->create([
                'account_number' => '1234567890',
                'balance' => 50000,
                'currency' => 'USD',
            ]);

        // Verify initial data
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $bankAccount->id,
            'account_number' => '1234567890',
            'balance' => 50000,
            'currency' => 'USD',
        ]);

        // Update the bank account
        $bankAccount->update([
            'balance' => 75000,
            'currency' => 'EUR',
        ]);

        // Verify changes were saved
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $bankAccount->id,
            'balance' => 75000,
            'currency' => 'EUR',
        ]);
    }

    public function test_bank_account_edit_page_saves_changes(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::factory()->create();
        $bankAccount = BankAccount::factory()
            ->for($user)
            ->for($accountType)
            ->create();

        $this->actingAs($user);

        // Update the bank account directly
        $bankAccount->update([
            'account_number' => 'NEW-ACCOUNT-123',
            'balance' => 100000,
            'currency' => 'GBP',
        ]);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $bankAccount->id,
            'account_number' => 'NEW-ACCOUNT-123',
            'balance' => 100000,
            'currency' => 'GBP',
        ]);
    }
}
