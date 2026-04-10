<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users
        $users = collect([
            ['name' => 'Budi Santoso', 'username' => 'budi_s', 'email' => 'budi@kicau.id', 'bio' => 'Pecinta teknologi dan kopi ☕'],
            ['name' => 'Siti Rahayu', 'username' => 'siti_r', 'email' => 'siti@kicau.id', 'bio' => 'Fotografer & traveler 📸'],
            ['name' => 'Ahmad Kurniawan', 'username' => 'ahmadkurn', 'email' => 'ahmad@kicau.id', 'bio' => 'Developer | Open Source Enthusiast 💻'],
            ['name' => 'Demo User', 'username' => 'demo', 'email' => 'demo@kicau.id', 'bio' => 'Akun demo untuk testing 🐦'],
        ])->map(fn($data) => User::create([
            ...$data,
            'password' => Hash::make('password'),
        ]));

        // Sample posts
        $samplePosts = [
            'Selamat pagi Indonesia! Hari ini semangat baru untuk berkarya lebih baik 🌅',
            'Kicau adalah tempat terbaik untuk berbagi cerita dan terhubung dengan semua orang! 🐦✨',
            'Laravel 12 + Bootstrap 5.3 = kombinasi sempurna untuk web development! 💻🔥',
            'Kuliner Indonesia terenak ada di mana ya? Saya lagi pengen bakso! 🍲',
            'Akhirnya weekend! Waktunya istirahat dan isi ulang energi 😴',
        ];

        foreach ($users as $user) {
            foreach (array_slice($samplePosts, 0, rand(2, 4)) as $body) {
                Post::create([
                    'user_id' => $user->id,
                    'body'    => $body,
                ]);
            }
        }

        // Mutual follows
        Follow::create(['follower_id' => $users[0]->id, 'following_id' => $users[1]->id]);
        Follow::create(['follower_id' => $users[0]->id, 'following_id' => $users[2]->id]);
        Follow::create(['follower_id' => $users[1]->id, 'following_id' => $users[0]->id]);
        Follow::create(['follower_id' => $users[2]->id, 'following_id' => $users[0]->id]);
        Follow::create(['follower_id' => $users[3]->id, 'following_id' => $users[0]->id]);
        Follow::create(['follower_id' => $users[3]->id, 'following_id' => $users[1]->id]);

        // Sample likes & comments
        $posts = Post::all();
        foreach ($posts->take(6) as $post) {
            foreach ($users->random(rand(1, 3)) as $likeUser) {
                Like::firstOrCreate(['post_id' => $post->id, 'user_id' => $likeUser->id]);
            }

            Comment::create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
                'body'    => 'Setuju banget! 👍',
            ]);
        }

        $this->command->info('✅ Demo data berhasil dibuat! Login: demo@kicau.id / password');
    }
}
