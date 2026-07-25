<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminStatusToAdminsTable extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('admin_status')->default(true);
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('admin_status');
        });
    }
}
