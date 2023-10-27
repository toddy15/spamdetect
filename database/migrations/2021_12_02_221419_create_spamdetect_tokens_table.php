<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spamdetect_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->integer('count_ham');
            $table->integer('count_spam');
        });

        // Insert the first token into the database.
        // This token is special, because the naive bayes
        // algorithm needs the number of spam and ham texts
        // already analyzed. In order to not create a second
        // table only for those two values, the first token
        // is used for this purpose. The 'count_ham' column
        // stores the number of ham texts, the 'count_spam'
        // column does the same for the spam texts.
        DB::table('spamdetect_tokens')->insert([
            'token' => 'COUNT_OF_HAM_AND_SPAM_TEXTS',
            'count_ham' => 0,
            'count_spam' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spamdetect_tokens');
    }
};
