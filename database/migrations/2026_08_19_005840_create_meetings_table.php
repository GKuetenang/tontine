<?php

use App\Models\Session;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Session::class)
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('number');

            $table->string('title');

            $table->string('slug');

            $table->text('description')
                ->nullable();

            $table->dateTime('scheduled_at');

            $table->string('location')
                ->nullable();

            $table->string('status');

            $table->dateTime('opened_at')
                ->nullable();

            $table->dateTime('closed_at')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->unique([
                'session_id',
                'number',
            ]);

            $table->unique([
                'session_id',
                'slug',
            ]);

            $table->index([
                'session_id',
                'status',
            ]);

            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
