<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportTicketRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_id')->nullable();
            $table->enum('sender_type', ['admin','client'])->default('admin');
            $table->enum('receiver_type', ['admin','client'])->default('client');
            $table->integer('sender_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('is_read')->default(0)->nullable();
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
        Schema::dropIfExists('support_ticket_replies');
    }
}
