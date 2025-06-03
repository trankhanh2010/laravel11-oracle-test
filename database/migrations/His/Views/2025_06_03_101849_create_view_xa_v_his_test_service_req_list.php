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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TEST_SERVICE_REQ_LIST AS
WITH transaction_type_cte AS (
  SELECT id
  FROM "HIS_TRANSACTION_TYPE"
  WHERE transaction_type_code IN ('HU', 'NO')
)
SELECT
    service_req.id,
    service_req.create_time,
    service_req.creator,
    service_req.vir_create_date,
    service_req.is_active,
    service_req.is_delete,
    service_req.service_req_code,
    service_req.intruction_time,
    service_req.tdl_hein_card_number as hein_card_number,
    service_req.icd_code,
    service_req.icd_name,
    service_req.tdl_hein_medi_org_code as hein_medi_org_code,
    service_req.tdl_hein_medi_org_name as hein_medi_org_name,
    service_req.request_loginname,
    service_req.request_username,
    service_req.assign_turn_code as turn_code,
    service_req.tdl_patient_national_name as national_name,
    service_req.priority,
    service_req.barcode as original_barcode,
    service_req.service_req_type_id,
    service_req.service_req_stt_id,
    service_req.execute_department_id,
    service_req.treatment_type_id,
    service_req.treatment_id,
    service_req.ICD_SUB_CODE,
    service_req.ICD_TEXT,
    service_req.tdl_patient_id,
    -- treatment
    treatment.tdl_patient_code as patient_code,
    treatment.tdl_patient_name as patient_name,
    treatment.tdl_patient_address as address,
    treatment.tdl_patient_dob as date_of_birth,
    treatment.tdl_patient_gender_name as gender,
    treatment.treatment_code,
    treatment.tdl_hein_card_from_time as hein_card_from_time,
    treatment.tdl_hein_card_to_time as hein_card_to_time,
    treatment.in_time,
    treatment.out_time,
    treatment.CLINICAL_IN_TIME,
    treatment.tdl_patient_phone as patient_phone,
    treatment.tdl_patient_military_rank_name as  patient_military_rank_name,
    treatment.tdl_patient_career_name as patient_career_name,
    treatment.tdl_patient_work_place_name as patient_work_place_name,
    treatment.fee_lock_loginname,
    treatment.fee_lock_time, --khoa vien phi
    treatment.is_lock_fee,
    treatment.IS_PAUSE, -- 1 - ket thuc dieu tri
    treatment.HEIN_LOCK_TIME, -- khoa bao hiem
    -- request_room
    COALESCE(request_execute_room.execute_room_code, request_bed_room.bed_room_code) as request_room_code,
    COALESCE(request_execute_room.execute_room_name, request_bed_room.bed_room_name) as request_room_name,
/*
    COALESCE(
        (SELECT bed_room_code FROM his_bed_room WHERE service_req.request_room_id = his_bed_room.room_id),
        (SELECT execute_room_code FROM his_execute_room WHERE service_req.request_room_id = his_execute_room.room_id)
    ) as request_room_code,

    COALESCE(
        (SELECT bed_room_name FROM his_bed_room WHERE service_req.request_room_id = his_bed_room.room_id),
        (SELECT execute_room_name FROM his_execute_room WHERE service_req.request_room_id = his_execute_room.room_id)
    ) as request_room_name,
*/
    -- patient_type
    patient_type.patient_type_code,
    patient_type.patient_type_name,
    -- treatment_type
    treatment_type.treatment_type_code,
    treatment_type.treatment_type_name,
    -- execute_room
    execute_room.execute_room_code,
    execute_room.execute_room_name,
    -- execute_department
    execute_department.department_code as execute_department_code,
    execute_department.department_name as execute_department_name,
    -- request_department
    request_department.department_code as request_department_code,
    request_department.department_name as request_department_name,
    -- treatment_result
    treatment_result.treatment_result_name,
    -- patient_type_alter
    (SELECT right_route_code
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = service_req.treatment_id
                  AND ROWNUM = 1) AS right_route_code,
    -- treatment_end_type
    treatment_end_type.treatment_end_type_name
    -- test_service_type_list
/*    test_service_type_list.test_service_type_list*/
/*    (
    SELECT '[' ||
        LISTAGG(
            '{"isSpecimen":"' || sere_serv.is_specimen || '",'
            || '"isNoExecute":"' || sere_serv.is_no_execute || '",'
            || '"serviceTypeName":"' || service_type.service_type_name || '",'
            || '"amount":"' || sere_serv.amount || '",' -- so luong
            || '"price":"' || sere_serv.price || '",' -- thanh tien
            || '"originalPrice":"' || sere_serv.original_price || '",' -- bao hiem tra
            || '"patientTypeName":"' || patient_type.patient_type_name || '",'
            || '"vatRatio":"' || sere_serv.vat_ratio || '",'
            || '"isExpend":"' || sere_serv.is_expend || '",'
            || '"ServiceReqCode":"' || sere_serv.tdl_service_req_code || '",'
            || '"serviceCode":"' || sere_serv.tdl_service_code || '",'
            || '"serviceName":"' || sere_serv.tdl_service_name || '"}',
            ', '
        ) WITHIN GROUP (ORDER BY sere_serv.id) || ']' AS test_service_type_list
    FROM his_sere_serv sere_serv
    LEFT JOIN his_service_type service_type on service_type.id = sere_serv.tdl_service_type_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv.patient_type_id
    WHERE sere_serv.tdl_patient_id = service_req.tdl_patient_id
    ) as test_service_type_list
    */
/*
    hss_zero_check.all_vir_total_price_zero,
*/
/*
    hss_total.total_vir_total_patient_price,
    ht_total.total_treatment_bill_amount
*/
/*
    -- tien benh nhan phai tra
    CASE
        WHEN hss_zero_check.all_vir_total_price_zero = 0 THEN
            COALESCE((SELECT SUM(hss.vir_total_patient_price)
                      FROM his_sere_serv hss
                      WHERE hss.tdl_treatment_id = service_req.treatment_id
                        AND hss.is_delete = 0
                        AND hss.is_no_execute IS NULL
            ), 0)
        ELSE 0
    END AS total_vir_total_patient_price,

    -- tien da thanh toan
    CASE
        WHEN hss_zero_check.all_vir_total_price_zero = 0 THEN
            COALESCE((SELECT SUM(
                         CASE
                             WHEN ht.transaction_type_id IN (
                                 SELECT id FROM his_transaction_type where transaction_type_code = 'HU' OR transaction_type_code = 'NO'
                             ) THEN -ht.amount
                             ELSE ht.amount
                         END
                      )
                      FROM his_transaction ht
                      WHERE ht.treatment_id = service_req.treatment_id
                        AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
            ), 0)
        ELSE 0
    END AS total_treatment_bill_amount
*/
/*
    CASE
        WHEN NOT EXISTS (
            SELECT 1
            FROM his_sere_serv hss_sub
            WHERE hss_sub.service_req_id = service_req.id
              AND hss_sub.is_delete = 0
              AND hss_sub.is_no_execute IS NULL
              AND hss_sub.is_no_pay IS NULL
              AND hss_sub.vir_total_patient_price <> 0
        ) THEN 1
        ELSE 0
    END AS all_vir_total_price_zero,

    COALESCE((SELECT SUM(hss.vir_total_patient_price)
                          FROM his_sere_serv hss
                          WHERE hss.tdl_treatment_id = service_req.treatment_id
                            AND hss.is_delete = 0
                            AND hss.is_no_execute IS NULL
                            AND hss.is_no_pay IS NULL
                ), 0) AS total_vir_total_patient_price,
*/

/*

    -- tong da thanh toan
    COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'HU' OR transaction_type_code = 'NO'
                                 ) THEN -ht.amount
                                 ELSE ht.amount
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                            AND ht.is_active = 1
                            ANd ht.is_delete = 0
                ), 0) AS total_treatment_bill_amount,
    -- tong tam ung
    COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'TU'
                                 ) THEN ht.amount
                                 ELSE 0
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                ), 0) AS total_treatment_TU,
    -- tong thanh toan
    COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'TT'
                                 ) THEN ht.amount
                                 ELSE 0
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                ), 0) AS total_treatment_TT,
    -- tong cong no
    COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'NO'
                                 ) THEN ht.amount
                                 ELSE 0
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                ), 0) AS total_treatment_No,
    -- tong hoan ung
    COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'HU'
                                 ) THEN ht.amount
                                 ELSE 0
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                ), 0) AS total_treatment_HU,
    -- tong ket chuyen
    COALESCE((SELECT SUM(KC_AMOUNT)
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                            AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                ), 0) AS total_treatment_KC_AMOUNT,
    -- tien phai tra them
    (
              (
                SELECT SUM(hss.vir_total_patient_price)
                FROM "HIS_SERE_SERV" hss
                WHERE hss.tdl_treatment_id = service_req.treatment_id
                  AND hss.is_delete = 0
                  AND hss.is_no_execute IS NULL
                  AND hss.is_no_pay IS NULL
              )
              -
                      COALESCE((SELECT SUM(
                             CASE
                                 WHEN ht.transaction_type_id IN (
                                     SELECT id FROM his_transaction_type where transaction_type_code = 'HU' OR transaction_type_code = 'NO'
                                 ) THEN -ht.amount
                                 ELSE ht.amount
                             END
                          )
                          FROM his_transaction ht
                          WHERE ht.treatment_id = service_req.treatment_id
                          AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
                          AND ht.is_active = 1
                          ANd ht.is_delete = 0
                ), 0)
            ) AS fee_add,
    -- tong hao phi
    COALESCE((SELECT SUM(vir_total_price_no_expend)
                          FROM his_sere_serv hss
                          WHERE hss.tdl_treatment_id = service_req.treatment_id
                            AND (hss.is_delete = 0 and hss.is_active = 1 and hss.is_expend = 1)
                ), 0) AS total_treatment_expend
*/


FROM HIS_SERVICE_REQ service_req
    INNER JOIN his_treatment treatment
        ON treatment.id = service_req.treatment_id
    INNER JOIN his_patient_type patient_type
        ON patient_type.id = service_req.tdl_patient_type_id
    INNER JOIN his_treatment_type treatment_type
        ON treatment_type.id = service_req.treatment_type_id
    INNER JOIN his_execute_room execute_room
        ON execute_room.room_id = service_req.execute_room_id
    INNER JOIN his_department execute_department
        ON execute_department.id = service_req.execute_department_id
    INNER JOIN his_department request_department
        ON request_department.id = service_req.request_department_id
    LEFT JOIN his_execute_room request_execute_room
        ON service_req.request_room_id = request_execute_room.id
    LEFT JOIN his_bed_room request_bed_room
        ON service_req.request_room_id = request_bed_room.id
    LEFT JOIN his_treatment_result treatment_result
        ON treatment.treatment_result_id = treatment_result.id
    LEFT JOIN his_treatment_end_type treatment_end_type
        ON treatment.treatment_end_type_id = treatment_end_type.id
/*    LEFT JOIN
    (
    SELECT sere_serv.service_req_id,
           '[' ||
           LISTAGG(
               '{"isSpecimen":"' || sere_serv.is_specimen || '",'
               || '"isNoExecute":"' || sere_serv.is_no_execute || '",'
               || '"serviceCode":"' || sere_serv.tdl_service_code || '",'
               || '"serviceName":"' || sere_serv.tdl_service_name || '"}',
               ', '
           ) WITHIN GROUP (ORDER BY sere_serv.id) || ']' AS test_service_type_list
    FROM his_sere_serv sere_serv
    GROUP BY sere_serv.service_req_id
    ) test_service_type_list
        ON test_service_type_list.service_req_id = service_req.id*/

/*
    INNER JOIN (
            SELECT hss.service_req_id,
                   CASE
                       WHEN MAX(CASE WHEN hss.vir_total_patient_price <> 0 THEN 1 ELSE 0 END) = 0 THEN 1
                       ELSE 0
                   END AS all_vir_total_price_zero
            FROM his_sere_serv hss
            WHERE hss.is_delete = 0
                  AND hss.is_no_execute IS NULL
                  AND hss.service_req_id IS NOT NULL
            GROUP BY hss.service_req_id
        ) hss_zero_check ON hss_zero_check.service_req_id = service_req.id
*/
/*
    LEFT JOIN (
              SELECT hss.tdl_treatment_id, SUM(hss.vir_total_patient_price) AS total_vir_total_patient_price
              FROM his_sere_serv hss
              WHERE hss.is_delete = 0
                    AND hss.is_no_execute IS NULL
              GROUP BY hss.tdl_treatment_id
          ) hss_total ON hss_total.tdl_treatment_id = service_req.treatment_id
    LEFT JOIN (
            SELECT ht.treatment_id,
                   SUM(
                       CASE
                             WHEN ht.transaction_type_id IN (
                                 SELECT id FROM his_transaction_type where transaction_type_code = 'HU' OR transaction_type_code = 'NO'
                             ) THEN -ht.amount
                             ELSE ht.amount
                        END
                   ) AS total_treatment_bill_amount
            FROM his_transaction ht
            WHERE (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
            GROUP BY ht.treatment_id
        ) ht_total ON ht_total.treatment_id = service_req.treatment_id
*/
WHERE
        treatment_type.treatment_type_code = '01'
/*         EXISTS (
            SELECT 1
            FROM HIS_TREATMENT_TYPE
            WHERE treatment_type_code = '01'
              AND id = service_req.treatment_type_id
        )*/
        AND
        EXISTS (
            SELECT 1
            FROM HIS_SERVICE_REQ_TYPE
            WHERE service_req_type_code = 'XN'
              AND id = service_req.service_req_type_id
        )
        AND (
          ((
          -- bo cac ban ghi k co bill va deposit
           NOT EXISTS (
            SELECT 1
            FROM "HIS_SERE_SERV" hss
            WHERE hss.service_req_id = service_req.id
                and not exists (
                  select
                    *
                  from
                    "HIS_SERE_SERV_BILL"
                  where
                    hss.ID = "HIS_SERE_SERV_BILL"."SERE_SERV_ID"
                )
                and not exists (
                  select
                    *
                  from
                    "HIS_SERE_SERV_DEPOSIT"
                  where
                    hss.ID = "HIS_SERE_SERV_DEPOSIT"."SERE_SERV_ID"
                )
          )
          )
          AND EXISTS (
          -- co 1 ban ghi co bill hoac depost voi is_delete va is_cancel khac 1
            SELECT hss.*
            FROM "HIS_SERE_SERV" hss
            WHERE hss.service_req_id = service_req.id
              AND (
                EXISTS (
                  SELECT 1
                  FROM "HIS_SERE_SERV_DEPOSIT" hss_deposit
                  WHERE hss.id = hss_deposit.sere_serv_id
                    AND hss_deposit.is_delete = 0
                    AND (hss_deposit.is_cancel = 0 OR hss_deposit.is_cancel IS NULL)
                )
                OR EXISTS (
                  SELECT 1
                  FROM "HIS_SERE_SERV_BILL" hss_bill
                  WHERE hss.id = hss_bill.sere_serv_id
                    AND hss_bill.is_delete = 0
                    AND (hss_bill.is_cancel = 0 OR hss_bill.is_cancel IS NULL)
                )
              )
          ))
          OR (
          -- lay ban ghi tat ca gia = 0
            NOT EXISTS (
              SELECT 1
              FROM "HIS_SERE_SERV" hss_sub
              WHERE hss_sub.service_req_id = service_req.id
                AND hss_sub.is_delete = 0
                AND hss_sub.is_no_execute IS NULL
                AND hss_sub.is_no_pay IS NULL
                AND hss_sub.vir_total_patient_price <> 0
            )
          )
/*          OR (
          -- lay ban ghi tra du tien
            (
              (
                SELECT SUM(
                  CASE
                    WHEN ht.transaction_type_id IN (
                         SELECT id FROM transaction_type_cte
                    ) THEN -ht.amount
                    ELSE ht.amount
                  END
                )
                FROM "HIS_TRANSACTION" ht
                WHERE ht.treatment_id = service_req.treatment_id
                  AND (ht.is_cancel = 0 OR ht.is_cancel IS NULL)
              )
              >=
              (
                SELECT SUM(hss.vir_total_patient_price)
                FROM "HIS_SERE_SERV" hss
                WHERE hss.tdl_treatment_id = service_req.treatment_id
                  AND hss.is_delete = 0
                  AND hss.is_no_execute IS NULL
                  AND hss.is_no_pay IS NULL
              )
            )
          )*/
        )
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TEST_SERVICE_REQ_LIST");
    }
};
