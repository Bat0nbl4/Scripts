<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ChequeType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(ChequeType::cases(), 'value'));
            $table->bigInteger('from')->unsigned()->index()->nullable();
            $table->foreign('from')->references('id')->on('wallets')->onDelete('cascade');
            $table->bigInteger('to')->unsigned()->index()->nullable();
            $table->foreign('to')->references('id')->on('wallets')->onDelete('cascade');
            $table->integer('value');

            $table->string('description')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
