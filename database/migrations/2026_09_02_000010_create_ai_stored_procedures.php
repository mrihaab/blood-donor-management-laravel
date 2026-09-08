<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite testing environment, stored procedures are handled via PHP fallbacks / custom SQL functions
            return;
        }

        // MySQL Stored Procedures

        // 1. sp_begin_action_execution
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_begin_action_execution");
        DB::unprepared("
            CREATE PROCEDURE sp_begin_action_execution(
                IN p_idempotency_key VARCHAR(255),
                IN p_logical_op_id VARCHAR(255),
                IN p_attempt_num INT,
                IN p_approval_nonce VARCHAR(255),
                IN p_command_type VARCHAR(255),
                IN p_facility_id BIGINT
            )
            BEGIN
                DECLARE v_current_epoch BIGINT;

                -- Acquire Safety Epoch Serialization Lock for UPDATE
                SELECT epoch_version INTO v_current_epoch 
                FROM ai_safety_epoch 
                WHERE id = 1 FOR UPDATE;

                -- Check if logical_operation_id + attempt_number already exists
                IF EXISTS (
                    SELECT 1 FROM action_executions 
                    WHERE idempotency_key = p_idempotency_key
                ) THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Duplicate idempotency key detected.';
                END IF;

                -- Insert initial EXECUTING state
                INSERT INTO action_executions (
                    idempotency_key, logical_operation_id, attempt_number,
                    approval_nonce, command_envelope_hash, safety_epoch,
                    command_type, facility_id, status, created_at
                ) VALUES (
                    p_idempotency_key, p_logical_op_id, p_attempt_num,
                    p_approval_nonce, 'PENDING_VERIFICATION', v_current_epoch,
                    p_command_type, p_facility_id, 'EXECUTING', UTC_TIMESTAMP()
                );

                SELECT v_current_epoch AS current_epoch;
            END
        ");

        // 2. sp_allocate_blood_unit
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_allocate_blood_unit");
        DB::unprepared("
            CREATE PROCEDURE sp_allocate_blood_unit(
                IN p_logical_op_id VARCHAR(255),
                IN p_request_id BIGINT,
                IN p_inventory_ids_json JSON,
                IN p_supplied_epoch BIGINT,
                IN p_approval_nonce VARCHAR(255)
            )
            BEGIN
                DECLARE v_current_epoch BIGINT;

                SELECT epoch_version INTO v_current_epoch 
                FROM ai_safety_epoch 
                WHERE id = 1 FOR UPDATE;

                IF v_current_epoch <> p_supplied_epoch THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Safety epoch changed mid-transaction. Allocation aborted.';
                END IF;

                -- Check domain ledger for previous allocation under logical_op_id
                IF EXISTS (
                    SELECT 1 FROM blood_unit_allocations 
                    WHERE logical_operation_id = p_logical_op_id
                ) THEN
                    -- Idempotent return of existing allocation
                    SELECT blood_unit_id FROM blood_unit_allocations 
                    WHERE logical_operation_id = p_logical_op_id 
                    ORDER BY blood_unit_id ASC;
                ELSE
                    -- Perform allocation with ascending lock ordering
                    INSERT INTO blood_unit_allocations (
                        logical_operation_id, request_id, blood_unit_id,
                        safety_epoch, approval_nonce, allocated_at
                    )
                    SELECT 
                        p_logical_op_id, p_request_id, bu.id,
                        v_current_epoch, p_approval_nonce, UTC_TIMESTAMP()
                    FROM blood_units bu
                    WHERE JSON_CONTAINS(p_inventory_ids_json, CAST(bu.id AS JSON))
                      AND bu.availability_state IN ('IMMEDIATELY_USABLE', 'TENTATIVELY_HELD')
                    ORDER BY bu.id ASC;

                    -- Update blood_units availability_state to ALLOCATED
                    UPDATE blood_units 
                    SET availability_state = 'ALLOCATED',
                        updated_at = UTC_TIMESTAMP()
                    WHERE JSON_CONTAINS(p_inventory_ids_json, CAST(id AS JSON));

                    SELECT blood_unit_id FROM blood_unit_allocations 
                    WHERE logical_operation_id = p_logical_op_id 
                    ORDER BY blood_unit_id ASC;
                END IF;
            END
        ");

        // 3. sp_enqueue_outbox_event
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_enqueue_outbox_event");
        DB::unprepared("
            CREATE PROCEDURE sp_enqueue_outbox_event(
                IN p_event_type VARCHAR(255),
                IN p_idempotency_key VARCHAR(255),
                IN p_payload_json JSON
            )
            BEGIN
                DECLARE v_intent_id VARCHAR(255);
                SET v_intent_id = SHA2(CONCAT(p_event_type, ':', p_idempotency_key), 256);

                INSERT INTO outbox_events (
                    notification_intent_id, idempotency_key, event_type, payload, status, created_at
                ) VALUES (
                    v_intent_id, p_idempotency_key, p_event_type, p_payload_json, 'QUEUED', UTC_TIMESTAMP()
                ) ON DUPLICATE KEY UPDATE status = status;
            END
        ");

        // 4. sp_complete_action_execution
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_complete_action_execution");
        DB::unprepared("
            CREATE PROCEDURE sp_complete_action_execution(
                IN p_idempotency_key VARCHAR(255),
                IN p_result_payload JSON
            )
            BEGIN
                DECLARE v_status VARCHAR(255);

                SELECT status INTO v_status 
                FROM action_executions 
                WHERE idempotency_key = p_idempotency_key FOR UPDATE;

                IF v_status <> 'EXECUTING' THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Cannot transition action execution to COMPLETED from non-EXECUTING status.';
                END IF;

                UPDATE action_executions 
                SET status = 'COMPLETED',
                    result_payload = p_result_payload,
                    completed_at = UTC_TIMESTAMP()
                WHERE idempotency_key = p_idempotency_key;
            END
        ");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        DB::unprepared("DROP PROCEDURE IF EXISTS sp_begin_action_execution");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_allocate_blood_unit");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_enqueue_outbox_event");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_complete_action_execution");
    }
};
