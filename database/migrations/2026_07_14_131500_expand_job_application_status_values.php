<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stati completi della pipeline candidature.
     *
     * Manteniamo anche i valori legacy per non rendere illeggibili o
     * non migrabili le candidature gia presenti in produzione.
     *
     * @var list<string>
     */
    private array $statuses = [
        'inviata',
        'visualizzata',
        'invitato_a_colloquio',
        'rifiutata',
        'in_valutazione',
        'colloquio_programmato',
        'colloquio_effettuato',
        'idoneo',
        'assunto',
        'non_idoneo',
        'ritirata',
    ];

    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->enum('status', $this->statuses)
                ->default('inviata')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->enum('status', [
                'inviata',
                'visualizzata',
                'invitato_a_colloquio',
                'rifiutata',
            ])->default('inviata')->change();
        });
    }
};
