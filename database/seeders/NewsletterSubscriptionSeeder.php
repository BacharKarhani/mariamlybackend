<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NewsletterSubscription;

class NewsletterSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subscriptions = [
            [
                'email' => 'john.doe@example.com',
                'name' => 'John Doe',
                'subscribed_at' => now()->subDays(30),
            ],
            [
                'email' => 'jane.smith@example.com',
                'name' => 'Jane Smith',
                'subscribed_at' => now()->subDays(15),
            ],
            [
                'email' => 'mike.wilson@example.com',
                'name' => 'Mike Wilson',
                'subscribed_at' => now()->subDays(7),
            ],
            [
                'email' => 'sarah.johnson@example.com',
                'name' => 'Sarah Johnson',
                'subscribed_at' => now()->subDays(60),
                'unsubscribed_at' => now()->subDays(30),
            ],
            [
                'email' => 'alex.brown@example.com',
                'name' => 'Alex Brown',
                'subscribed_at' => now()->subDays(3),
            ],
            [
                'email' => 'emma.davis@example.com',
                'name' => 'Emma Davis',
                'subscribed_at' => now()->subDays(1),
            ],
        ];

        foreach ($subscriptions as $subscription) {
            NewsletterSubscription::create($subscription);
        }
    }
}
