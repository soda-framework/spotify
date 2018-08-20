<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotifySettings extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('spotify_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('package')->nullable();
            $table->string('action')->nullable();
            $table->string('playlist_title')->nullable();
            $table->string('playlist_description')->nullable();
            $table->string('playlist_image')->nullable();
            $table->string('token_user')->nullable();
            $table->string('token_user_link')->nullable();
            $table->text('access_token')->nullable();
            $table->string('access_token_expiration')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('spotify_settings');
    }
}
