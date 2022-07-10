<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id');
            $table->dateTime('login_time')->index();
            $table->dateTime('logout_time')->nullable(true)->index();
            $table->timestamps();
        });

        DB::statement("
        ALTER TABLE sessions
        ADD COLUMN login_range tsrange NOT NULL
        GENERATED ALWAYS AS (
            tsrange(login_time, logout_time, '[]')
        ) STORED;
    ");

        Schema::table('sessions', function (Blueprint $table) {
            $table->excludeRangeOverlapping('login_range', 'user_id');
        });

        DB::statement("
        CREATE INDEX ON sessions USING GIST (login_range);
    ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
