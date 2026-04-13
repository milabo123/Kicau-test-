$u = \App\Models\User::firstOrCreate(
    ["email" => "dummy@kicau.com"], 
    [
        "name" => "Dummy Explore", 
        "username" => "explore_dummy", 
        "password" => bcrypt("password"), 
        "bio" => "Global feed dummy populated account."
    ]
); 
\App\Models\Post::create([
    "user_id" => $u->id, 
    "body" => "Welcome! Every post you create is now visible universally on the global feed without needing to explicitly follow the creator! #Explore"
]);
\App\Models\Post::create([
    "user_id" => $u->id, 
    "body" => "To test this out, I was created without automatically following your account. However, you can see this post right on your homepage!"
]); 
echo "Seeded Dummy successfully\n";
