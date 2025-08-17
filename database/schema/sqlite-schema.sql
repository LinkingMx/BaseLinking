CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "avatar_url" varchar
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "email_configurations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "driver" varchar check("driver" in('smtp', 'mailgun', 'postmark', 'ses', 'sendmail')) not null,
  "settings" text,
  "is_active" tinyint(1) not null default '0',
  "last_tested_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "settings"(
  "id" integer primary key autoincrement not null,
  "group" varchar not null,
  "name" varchar not null,
  "locked" tinyint(1) not null default '0',
  "payload" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "settings_group_name_unique" on "settings"(
  "group",
  "name"
);
CREATE TABLE IF NOT EXISTS "pulse_values"(
  "id" integer primary key autoincrement not null,
  "timestamp" integer not null,
  "type" varchar not null,
  "key" text not null,
  "key_hash" varchar not null,
  "value" text not null
);
CREATE INDEX "pulse_values_timestamp_index" on "pulse_values"("timestamp");
CREATE INDEX "pulse_values_type_index" on "pulse_values"("type");
CREATE UNIQUE INDEX "pulse_values_type_key_hash_unique" on "pulse_values"(
  "type",
  "key_hash"
);
CREATE TABLE IF NOT EXISTS "pulse_entries"(
  "id" integer primary key autoincrement not null,
  "timestamp" integer not null,
  "type" varchar not null,
  "key" text not null,
  "key_hash" varchar not null,
  "value" integer
);
CREATE INDEX "pulse_entries_timestamp_index" on "pulse_entries"("timestamp");
CREATE INDEX "pulse_entries_type_index" on "pulse_entries"("type");
CREATE INDEX "pulse_entries_key_hash_index" on "pulse_entries"("key_hash");
CREATE INDEX "pulse_entries_timestamp_type_key_hash_value_index" on "pulse_entries"(
  "timestamp",
  "type",
  "key_hash",
  "value"
);
CREATE TABLE IF NOT EXISTS "pulse_aggregates"(
  "id" integer primary key autoincrement not null,
  "bucket" integer not null,
  "period" integer not null,
  "type" varchar not null,
  "key" text not null,
  "key_hash" varchar not null,
  "aggregate" varchar not null,
  "value" numeric not null,
  "count" integer
);
CREATE UNIQUE INDEX "pulse_aggregates_bucket_period_type_aggregate_key_hash_unique" on "pulse_aggregates"(
  "bucket",
  "period",
  "type",
  "aggregate",
  "key_hash"
);
CREATE INDEX "pulse_aggregates_period_bucket_index" on "pulse_aggregates"(
  "period",
  "bucket"
);
CREATE INDEX "pulse_aggregates_type_index" on "pulse_aggregates"("type");
CREATE INDEX "pulse_aggregates_period_type_aggregate_bucket_index" on "pulse_aggregates"(
  "period",
  "type",
  "aggregate",
  "bucket"
);
CREATE TABLE IF NOT EXISTS "menus"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "is_visible" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "menu_items"(
  "id" integer primary key autoincrement not null,
  "menu_id" integer not null,
  "parent_id" integer,
  "linkable_type" varchar,
  "linkable_id" integer,
  "title" varchar not null,
  "url" varchar,
  "target" varchar not null default '_self',
  "order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("menu_id") references "menus"("id") on delete cascade,
  foreign key("parent_id") references "menu_items"("id") on delete set null
);
CREATE INDEX "menu_items_linkable_type_linkable_id_index" on "menu_items"(
  "linkable_type",
  "linkable_id"
);
CREATE TABLE IF NOT EXISTS "menu_locations"(
  "id" integer primary key autoincrement not null,
  "menu_id" integer not null,
  "location" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("menu_id") references "menus"("id") on delete cascade
);
CREATE UNIQUE INDEX "menu_locations_location_unique" on "menu_locations"(
  "location"
);
CREATE TABLE IF NOT EXISTS "filament_exceptions_table"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "code" varchar not null,
  "message" text not null,
  "file" varchar not null,
  "line" integer not null,
  "trace" text not null,
  "method" varchar not null,
  "path" varchar not null,
  "query" text not null,
  "body" text not null,
  "cookies" text not null,
  "headers" text not null,
  "ip" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "email_templates"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "name" varchar not null,
  "subject" varchar not null,
  "content" text not null,
  "variables" text,
  "language" varchar not null default 'es',
  "is_active" tinyint(1) not null default '1',
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "model_type" varchar,
  "model_variables" text
);
CREATE INDEX "email_templates_key_language_index" on "email_templates"(
  "key",
  "language"
);
CREATE INDEX "email_templates_is_active_index" on "email_templates"(
  "is_active"
);
CREATE UNIQUE INDEX "email_templates_key_unique" on "email_templates"("key");
CREATE TABLE IF NOT EXISTS "breezy_sessions"(
  "id" integer primary key autoincrement not null,
  "authenticatable_type" varchar not null,
  "authenticatable_id" integer not null,
  "panel_id" varchar,
  "guard" varchar,
  "ip_address" varchar,
  "user_agent" text,
  "expires_at" datetime,
  "two_factor_secret" text,
  "two_factor_recovery_codes" text,
  "two_factor_confirmed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "breezy_sessions_authenticatable_type_authenticatable_id_index" on "breezy_sessions"(
  "authenticatable_type",
  "authenticatable_id"
);
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "activity_log"(
  "id" integer primary key autoincrement not null,
  "log_name" varchar,
  "description" text not null,
  "subject_type" varchar,
  "subject_id" integer,
  "causer_type" varchar,
  "causer_id" integer,
  "properties" text,
  "created_at" datetime,
  "updated_at" datetime,
  "event" varchar,
  "batch_uuid" varchar
);
CREATE INDEX "subject" on "activity_log"("subject_type", "subject_id");
CREATE INDEX "causer" on "activity_log"("causer_type", "causer_id");
CREATE INDEX "activity_log_log_name_index" on "activity_log"("log_name");
CREATE TABLE IF NOT EXISTS "advanced_workflows"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "target_model" varchar not null,
  "trigger_conditions" text not null,
  "is_active" tinyint(1) not null default '1',
  "version" integer not null default '1',
  "global_variables" text,
  "created_at" datetime,
  "updated_at" datetime,
  "is_master_workflow" tinyint(1) not null default '0'
);
CREATE INDEX "advanced_workflows_target_model_is_active_index" on "advanced_workflows"(
  "target_model",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "workflow_step_definitions"(
  "id" integer primary key autoincrement not null,
  "advanced_workflow_id" integer not null,
  "step_name" varchar not null,
  "description" text,
  "step_type" varchar check("step_type" in('notification', 'approval', 'action', 'condition', 'wait')) not null,
  "step_order" integer not null,
  "step_config" text not null,
  "conditions" text,
  "is_required" tinyint(1) not null default '1',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("advanced_workflow_id") references "advanced_workflows"("id") on delete cascade
);
CREATE INDEX "workflow_step_definitions_advanced_workflow_id_step_order_index" on "workflow_step_definitions"(
  "advanced_workflow_id",
  "step_order"
);
CREATE TABLE IF NOT EXISTS "workflow_step_templates"(
  "id" integer primary key autoincrement not null,
  "workflow_step_definition_id" integer not null,
  "recipient_type" varchar not null,
  "recipient_config" text not null,
  "email_template_key" varchar not null,
  "template_variables" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("workflow_step_definition_id") references "workflow_step_definitions"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "advanced_workflow_executions"(
  "id" integer primary key autoincrement not null,
  "advanced_workflow_id" integer not null,
  "target_model" varchar not null,
  "target_id" integer not null,
  "status" varchar check("status" in('pending', 'in_progress', 'completed', 'failed', 'cancelled')) not null,
  "current_step_id" integer,
  "current_step_order" integer not null default '1',
  "context_data" text,
  "step_results" text,
  "initiated_by" integer,
  "started_at" datetime,
  "completed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("advanced_workflow_id") references "advanced_workflows"("id") on delete cascade,
  foreign key("current_step_id") references "workflow_step_definitions"("id"),
  foreign key("initiated_by") references "users"("id") on delete set null
);
CREATE INDEX "advanced_workflow_executions_target_model_target_id_index" on "advanced_workflow_executions"(
  "target_model",
  "target_id"
);
CREATE INDEX "advanced_workflow_executions_status_current_step_order_index" on "advanced_workflow_executions"(
  "status",
  "current_step_order"
);
CREATE TABLE IF NOT EXISTS "workflow_step_executions_advanced"(
  "id" integer primary key autoincrement not null,
  "workflow_execution_id" integer not null,
  "step_definition_id" integer not null,
  "status" varchar check("status" in('pending', 'in_progress', 'completed', 'failed', 'skipped', 'cancelled')) not null,
  "input_data" text,
  "output_data" text,
  "notifications_sent" text,
  "assigned_to" integer,
  "completed_by" integer,
  "comments" text,
  "started_at" datetime,
  "completed_at" datetime,
  "due_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("workflow_execution_id") references "advanced_workflow_executions"("id") on delete cascade,
  foreign key("step_definition_id") references "workflow_step_definitions"("id") on delete cascade,
  foreign key("assigned_to") references "users"("id") on delete set null,
  foreign key("completed_by") references "users"("id") on delete set null
);
CREATE INDEX "workflow_step_executions_advanced_status_assigned_to_index" on "workflow_step_executions_advanced"(
  "status",
  "assigned_to"
);
CREATE INDEX "workflow_step_executions_advanced_due_at_index" on "workflow_step_executions_advanced"(
  "due_at"
);
CREATE TABLE IF NOT EXISTS "model_variable_mappings"(
  "id" integer primary key autoincrement not null,
  "model_class" varchar not null,
  "variable_key" varchar not null,
  "variable_name" varchar not null,
  "description" text,
  "data_type" varchar not null default 'string',
  "category" varchar not null default 'custom',
  "mapping_config" text not null,
  "is_active" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "example_value" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "model_variable_mappings_model_class_variable_key_unique" on "model_variable_mappings"(
  "model_class",
  "variable_key"
);
CREATE INDEX "model_variable_mappings_model_class_is_active_index" on "model_variable_mappings"(
  "model_class",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "documentations"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "description" text not null,
  "status" varchar not null default('draft'),
  "created_by" integer,
  "approved_by" integer,
  "approved_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "last_edited_by" integer,
  "last_edited_at" datetime,
  "rejected_by" integer,
  "rejected_at" datetime,
  "rejection_reason" text,
  "approval_level" integer not null default '0',
  "approval_history" text,
  "state" varchar,
  foreign key("last_edited_by") references users("id") on delete set null on update no action,
  foreign key("created_by") references users("id") on delete set null on update no action,
  foreign key("approved_by") references users("id") on delete set null on update no action,
  foreign key("rejected_by") references "users"("id") on delete set null
);
CREATE INDEX "documentations_created_by_index" on "documentations"(
  "created_by"
);
CREATE INDEX "documentations_status_index" on "documentations"("status");
CREATE TABLE IF NOT EXISTS "approval_states"(
  "id" integer primary key autoincrement not null,
  "model_type" varchar not null,
  "name" varchar not null,
  "label" varchar not null,
  "description" text,
  "color" varchar,
  "icon" varchar,
  "is_initial" tinyint(1) not null default '0',
  "is_final" tinyint(1) not null default '0',
  "requires_approval" tinyint(1) not null default '0',
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "approval_states_model_type_is_active_index" on "approval_states"(
  "model_type",
  "is_active"
);
CREATE INDEX "approval_states_model_type_is_initial_index" on "approval_states"(
  "model_type",
  "is_initial"
);
CREATE INDEX "approval_states_model_type_is_final_index" on "approval_states"(
  "model_type",
  "is_final"
);
CREATE UNIQUE INDEX "unique_model_state" on "approval_states"(
  "model_type",
  "name"
);
CREATE TABLE IF NOT EXISTS "state_transitions"(
  "id" integer primary key autoincrement not null,
  "from_state_id" integer not null,
  "to_state_id" integer not null,
  "name" varchar not null,
  "label" varchar not null,
  "description" text,
  "requires_permission" tinyint(1) not null default '0',
  "permission_name" varchar,
  "requires_role" tinyint(1) not null default '0',
  "role_names" text,
  "requires_approval" tinyint(1) not null default '0',
  "approver_roles" text,
  "condition_rules" text,
  "notification_template" varchar,
  "success_message" varchar,
  "failure_message" varchar,
  "is_active" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("from_state_id") references "approval_states"("id") on delete cascade,
  foreign key("to_state_id") references "approval_states"("id") on delete cascade
);
CREATE INDEX "state_transitions_from_state_id_is_active_index" on "state_transitions"(
  "from_state_id",
  "is_active"
);
CREATE INDEX "state_transitions_to_state_id_is_active_index" on "state_transitions"(
  "to_state_id",
  "is_active"
);
CREATE INDEX "email_templates_model_type_index" on "email_templates"(
  "model_type"
);
CREATE TABLE IF NOT EXISTS "filament_email_log"(
  "id" integer primary key autoincrement not null,
  "from" varchar,
  "to" varchar,
  "cc" varchar,
  "bcc" varchar,
  "subject" varchar,
  "text_body" text,
  "html_body" text,
  "raw_body" text,
  "sent_debug_info" text,
  "created_at" datetime,
  "updated_at" datetime,
  "attachments" text,
  "team_id" integer
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_08_03_010910_create_email_configurations_table',2);
INSERT INTO migrations VALUES(5,'2022_12_14_083707_create_settings_table',3);
INSERT INTO migrations VALUES(6,'2025_08_03_013142_general',4);
INSERT INTO migrations VALUES(7,'2025_08_03_013145_appearance',4);
INSERT INTO migrations VALUES(8,'2025_08_03_013148_localization',4);
INSERT INTO migrations VALUES(10,'2025_08_03_185000_update_appearance_colors',5);
INSERT INTO migrations VALUES(11,'2025_08_02_203254_backup',6);
INSERT INTO migrations VALUES(12,'2025_08_02_204512_backup_add_original_name',7);
INSERT INTO migrations VALUES(13,'2025_08_02_225442_create_pulse_tables',8);
INSERT INTO migrations VALUES(14,'2023_06_23_072639_create_mail_templates_table',9);
INSERT INTO migrations VALUES(15,'2023_06_23_130040_create_mail_history_table',9);
INSERT INTO migrations VALUES(16,'2025_08_03_002503_create_menus_table',10);
INSERT INTO migrations VALUES(17,'2025_08_03_002942_create_filament_exceptions_table',11);
INSERT INTO migrations VALUES(18,'2025_08_03_004845_create_email_templates_table',12);
INSERT INTO migrations VALUES(19,'2025_08_03_005003_email_template_settings',13);
INSERT INTO migrations VALUES(20,'2025_08_03_132052_create_breezy_sessions_table',14);
INSERT INTO migrations VALUES(21,'2025_08_03_132129_add_avatar_url_to_users_table',15);
INSERT INTO migrations VALUES(22,'2025_08_03_132440_create_personal_access_tokens_table',16);
INSERT INTO migrations VALUES(23,'2025_08_03_135353_create_permission_tables',17);
INSERT INTO migrations VALUES(24,'2025_08_03_140708_create_activity_log_table',18);
INSERT INTO migrations VALUES(25,'2025_08_03_140709_add_event_column_to_activity_log_table',18);
INSERT INTO migrations VALUES(26,'2025_08_03_140710_add_batch_uuid_column_to_activity_log_table',18);
INSERT INTO migrations VALUES(27,'2025_08_03_143639_create_configurable_workflows_table',19);
INSERT INTO migrations VALUES(28,'2025_08_03_143652_create_workflow_executions_table',19);
INSERT INTO migrations VALUES(29,'2025_08_03_145339_create_documentations_table',20);
INSERT INTO migrations VALUES(30,'2025_08_03_150917_create_workflow_steps_table',21);
INSERT INTO migrations VALUES(31,'2025_08_03_150934_create_workflow_step_executions_table',21);
INSERT INTO migrations VALUES(32,'2025_08_03_150946_add_step_support_to_workflow_executions_table',21);
INSERT INTO migrations VALUES(33,'2025_08_03_190416_add_last_editor_to_documentation_table',22);
INSERT INTO migrations VALUES(34,'2025_08_03_193625_create_advanced_workflows_table',23);
INSERT INTO migrations VALUES(35,'2025_08_04_cleanup_legacy_workflows',24);
INSERT INTO migrations VALUES(36,'2025_08_04_create_model_variable_mappings_table',25);
INSERT INTO migrations VALUES(37,'2025_08_03_210701_add_approval_rejection_fields_to_documentations_table',26);
INSERT INTO migrations VALUES(38,'2025_08_04_113326_create_approval_states_table',27);
INSERT INTO migrations VALUES(39,'2025_08_04_113338_create_state_transitions_table',27);
INSERT INTO migrations VALUES(41,'2025_08_04_113653_add_state_column_to_documentations_table',28);
INSERT INTO migrations VALUES(42,'2025_08_04_120000_update_existing_documentations_with_state',28);
INSERT INTO migrations VALUES(43,'2025_08_04_140000_add_state_management_permissions',29);
INSERT INTO migrations VALUES(44,'2025_08_04_150000_migrate_workflows_to_unified_events',30);
INSERT INTO migrations VALUES(45,'2025_08_04_160000_add_master_workflow_flag',31);
INSERT INTO migrations VALUES(46,'2025_08_04_165052_add_model_fields_to_email_templates_table',32);
INSERT INTO migrations VALUES(47,'2025_08_04_183110_remove_category_from_email_templates_table',33);
INSERT INTO migrations VALUES(48,'2025_08_05_082648_remove_html_editor_fields_from_email_templates_table',34);
INSERT INTO migrations VALUES(49,'2025_08_05_141938_create_filament_email_table',35);
INSERT INTO migrations VALUES(50,'2025_08_05_141939_add_attachments_field_to_filament_email_log_table',35);
INSERT INTO migrations VALUES(51,'2025_08_05_141940_add_team_id_field_to_filament_email_log_table',35);
