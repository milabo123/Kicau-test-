$u = \App\Models\User::where("username", "explore_dummy")->first();
if($u){
    \App\Models\Post::where("user_id", $u->id)->delete();
    \App\Models\Post::create(["user_id" => $u->id, "body" => "Welcome! Every post you create is now visible universally on the global feed without needing to explicitly follow the creator! #Explore #Kicau"]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "To test this out, I was created without automatically following your account. However, you can see this post right on your homepage! #Trending"]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "Did you know? Building an app with #Laravel and #PHP is incredibly fast! Just look at how snappy #Kicau is."]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "Good morning everyone! Coffee and #Programming are the best way to start the day. ☕ #Laravel"]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "If you are looking for new friends, try searching for #Explore. It is the best hashtag!"]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "This is a random thought, but #Kicau is starting to look really polished. I love the new features! #Trending"]);
    \App\Models\Post::create(["user_id" => $u->id, "body" => "Just another test post for the algorithm! Making sure #Explore and #Laravel stay firmly at the top."]);
    echo "Re-seeded Dummy successfully\n";
}
