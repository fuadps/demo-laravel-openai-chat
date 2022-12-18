<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cant_ask_chat_to_delete_order()
    {
        $user = User::factory()
            ->has(Order::factory()->count(1))
            ->create();

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Please delete the latest order')
            ->expectsOutput('Your question doesnt make any sense. Try again.')
            ->assertExitCode(0);

        $this->assertCount(1, Order::all());
    }

    public function test_user_cant_ask_chat_to_update_order()
    {
        $user = User::factory()
            ->has(Order::factory()->count(1))
            ->create();

        $firstOrder = $user->orders->first();

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Please update the latest order name to "Nasi Kerabu"')
            ->expectsOutput('Your question doesnt make any sense. Try again.')
            ->assertExitCode(0);

        $this->assertNotEquals('Nasi Kerabu', Order::first()->name);
        $this->assertEquals($firstOrder->fresh()->name, Order::first()->name);
    }

    public function test_user_cant_ask_not_related_question()
    {
        $user = User::factory()
            ->has(Order::factory()->count(1))
            ->create();

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Hey, what is your thought on Vue.js?')
            ->expectsOutput('Your question doesnt make any sense. Try again.')
            ->assertExitCode(0);
    }

    public function test_user_ask_question_that_need_to_joining_two_table()
    {
        $user = User::factory()
            ->has(
                Order::factory()
                    ->count(2)
                    ->state(fn($attributes) => ['price' => 10.00, 'quantity' => 2])
            )
            ->create();

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Give me a total spend by our latest user with his name.')
            ->expectsOutputToContain('40')
            ->assertExitCode(0);
    }
}
