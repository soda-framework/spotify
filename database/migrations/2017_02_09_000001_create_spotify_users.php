<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotifyUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spotify_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->string('uri')->nullable();
            $table->string('image')->nullable();
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
            $table->integer('follower_count')->nullable();
            $table->string('country')->nullable();
            $table->text('access_token')->nullable();
            $table->string('access_token_expiration')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('remember_token',100)->nullable();
            $table->timestamps();
            $table->dateTime('last_loggedin_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spotify_users');
    }
}
