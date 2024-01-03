<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goal_steps', function (Blueprint $table) {
            $table->id();
            $table->integer('goal_id');
            $table->string('step_name')->nullable();
            $table->text('description')->nullable();
            $table->date('end_date')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('reminder_time')->nullable();
            $table->string('step_occurrence')->nullable();
            $table->string('step_occurrence_weekdays')->nullable();
            $table->text('attachments')->nullable();
            $table->tinyInteger('is_complete')->default(0)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active','inactive','deleted'])->default('active');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goal_steps');
    }
}
