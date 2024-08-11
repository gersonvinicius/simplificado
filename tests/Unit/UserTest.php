<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_user_successfully()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'cpf' => '12345678901',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
    }

    public function test_it_does_not_allow_duplicate_cpf()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'cpf' => '12345678901',
            'password' => bcrypt('password'),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'cpf' => '12345678901',
            'password' => bcrypt('password'),
        ]);
    }
}

