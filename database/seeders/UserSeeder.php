<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'handle' => 'testuser',
            'email' => 'test@example.com',
        ]);

        User::factory()->count(19)->create();

        $users = User::all();

        foreach ($users as $user) {
            $followees = $users
                ->reject(fn (User $candidate) => $candidate->id === $user->id)
                ->random(fake()->numberBetween(2, 5));

            foreach ($followees as $followee) {
                Follow::create([
                    'follower_id' => $user->id,
                    'followed_id' => $followee->id,
                ]);
            }
        }
    }
}
