$u = \App\Models\User::where('username', 'explore_dummy')->first();
if($u) {
    \App\Models\Post::where('user_id', $u->id)->delete();
}
Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
echo 'Reset executed';
