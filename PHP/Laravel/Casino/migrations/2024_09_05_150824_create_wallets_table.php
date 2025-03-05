<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\WalletTypes;
use App\Enums\WalletStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', array_column(WalletTypes::cases(), 'value'))->default(WalletTypes::Storage->value);
            $table->float('profit_factor')->default(1.0);
            $table->bigInteger('value')->default(0);
            $table->enum('status', array_column(WalletStatus::cases(), 'value'))->default(WalletStatus::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
