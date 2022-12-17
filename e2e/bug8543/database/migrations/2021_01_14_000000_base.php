<?php declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('log', function (Blueprint $table) {
            $table->id();

            $table->string('action')->index();

            $table->string('related_table')->index();
            $table->unsignedBigInteger('related_id')->nullable()->index();

            $table->json('payload')->nullable();

            $this->timestamps($table);

            $table->unsignedBigInteger('log_id')->nullable();
        });
    }
};
