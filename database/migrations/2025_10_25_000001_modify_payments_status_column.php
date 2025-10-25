<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyPaymentsStatusColumn extends Migration
{
    /**
     * Run the migrations.
     * Change payments.status to a VARCHAR so string statuses like 'successful' can be stored.
     * Uses raw SQL to avoid requiring doctrine/dbal.
     *
     * @return void
     */
    public function up()
    {
        // Adjust the column to accept longer string values
        // This will work for MySQL. If you use a different DB, replace with appropriate SQL.
        DB::statement("ALTER TABLE `payments` MODIFY `status` VARCHAR(50) DEFAULT 'initiated';");
    }

    /**
     * Reverse the migrations.
     * We revert to a generic VARCHAR again (safe default). If you prefer a different type,
     * modify this method accordingly.
     *
     * @return void
     */
    public function down()
    {
        // Keep as VARCHAR on rollback to avoid further data loss.
        DB::statement("ALTER TABLE `payments` MODIFY `status` VARCHAR(50) DEFAULT 'initiated';");
    }
}
