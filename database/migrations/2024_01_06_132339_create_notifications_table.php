<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type')->nullable();
            $table->integer('notification_from_id')->nullable();
            $table->integer('notification_to_id')->nullable();
            $table->integer('goal_id')->nullable();
            $table->string('link')->nullable();
            $table->text('text');
            $table->tinyInteger('is_read')->default('0')->nullable();
            $table->dateTime('sent_date')->nullable();
            $table->enum('status', ['active','inactive','deleted'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
