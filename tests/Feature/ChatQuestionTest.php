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

        $this->actingAs($user);

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Please delete the latest order')
            ->expectsOutput('Your question doesnt make any sense. Try again.');

        $this->assertCount(1, Order::all());
    }

    public function test_user_cant_ask_chat_to_update_order()
    {
        $user = User::factory()
            ->has(Order::factory()->count(1))
            ->create();

        $firstOrder = $user->orders->first();

        $this->actingAs($user);

        $this->artisan('chat:ai')
            ->expectsQuestion('What is your question?', 'Please update the latest order name to "Nasi Kerabu"')
            ->expectsOutput('Your question doesnt make any sense. Try again.');

        $this->assertNotEquals('Nasi Kerabu', Order::first()->name);
        $this->assertEquals($firstOrder->fresh()->name, Order::first()->name);
        $this->assertCount(1, Order::all());
    }
}
