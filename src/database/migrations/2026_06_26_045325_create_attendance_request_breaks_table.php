<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestBreaksTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')
                ->constrained('attendance_requests')
                ->onDelete('cascade');
            $table->time('requested_break_start');
            $table->time('requested_break_end');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_request_breaks');
    }
}
