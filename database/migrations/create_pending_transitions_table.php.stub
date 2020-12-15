<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingTransitionsTable extends Migration
{
    public function up()
    {
        Schema::create('pending_transitions', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->string('field');

            $table->string('from')->nullable();
            $table->string('to')->nullable();

            $table->json('custom_properties')->nullable();
            $table->nullableMorphs('responsible');

            $table->dateTime('transition_at');
            $table->dateTime('applied_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_transitions');
    }
}
