<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('oracle_his')->statement(
            <<<SQL
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREATMENT_ROOM_GROUP AS
SELECT
    bed_room.bed_room_code,
    bed_room.bed_room_name,
    department.department_code,
    department.department_name,
    COUNT(treatment_bed_room.treatment_id) AS treatment_count, -- �?m t?ng s? di?u tr?
    COUNT(
        CASE
            WHEN treatment_bed_room.bed_id IS NOT NULL
                 AND treatment_bed_room.remove_time IS NULL
            THEN 1
            ELSE NULL
        END
    ) AS treatment_in_bed_count -- �?m s? di?u tr? c� giu?ng & chua x�a
FROM his_treatment_bed_room treatment_bed_room
    LEFT JOIN his_bed_room bed_room ON bed_room.id = treatment_bed_room.bed_room_id
    LEFT JOIN his_room room ON room.id = bed_room.room_id -- K?t n?i v?i ph�ng
    LEFT JOIN his_department department ON department.id = room.department_id -- K?t n?i v?i khoa
    LEFT JOIN his_treatment treatment ON treatment.id = treatment_bed_room.treatment_id
GROUP BY bed_room.bed_room_code, bed_room.bed_room_name, department.department_code,department.department_name
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TREATMENT_ROOM_GROUP");
    }
};
