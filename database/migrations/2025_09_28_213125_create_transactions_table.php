<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date');
            $table->string('folio_number')->unique()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tabla pivote para la relación muchos a muchos entre transacciones y cuotas
        Schema::create('installment_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained('installments')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->decimal('amount_applied', 10, 2); // Monto de esta transacción aplicado a esta cuota
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_transaction');
        Schema::dropIfExists('transactions');
    }
};