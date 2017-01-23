<?php
class Migrate_2016_04_05_094031_558 extends Entrada_Cli_Migrate {

    /**
     * Required: SQL / PHP that performs the upgrade migration.
     */
    public function up() {
        $this->record();
        ?>
        RENAME TABLE `cbl_assessment_future_tasks` TO `cbl_assessment_ss_future_tasks`;

        CREATE TABLE `cbl_assessment_ss_current_tasks` (
        `current_task_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `adistribution_id` int(11) unsigned NOT NULL,
        `dassessment_id` int (11) unsigned,
        `assessor_type` enum('internal','external') DEFAULT NULL,
        `assessor_value` int(11) unsigned NOT NULL,
        `target_type` enum('proxy_id','cgroup_id','group_id','schedule_id','external_hash','course_id','organisation_id'),
        `target_value` int(11) NOT NULL,
        `title` TEXT,
        `rotation_start_date` bigint(64) DEFAULT 0,
        `rotation_end_date` bigint(64) DEFAULT 0,
        `delivery_date` bigint(64) NOT NULL,
        `schedule_details` TEXT,
        `created_by`int(11) NOT NULL,
        `created_date` bigint(64) NOT NULL,
        `deleted_by`int(11) DEFAULT NULL,
        `deleted_date` bigint(64) DEFAULT NULL,
        PRIMARY KEY (`current_task_id`),
        CONSTRAINT `cbl_assessment_ss_current_tasks_ibfk_1` FOREIGN KEY (`adistribution_id`) REFERENCES `cbl_assessment_distributions` (`adistribution_id`),
        CONSTRAINT `cbl_assessment_ss_current_tasks_ibfk_2` FOREIGN KEY (`dassessment_id`) REFERENCES `cbl_distribution_assessments` (`dassessment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        <?php
        $this->stop();

        return $this->run();
    }

    /**
     * Required: SQL / PHP that performs the downgrade migration.
     */
    public function down() {
        $this->record();
        ?>
        RENAME TABLE `cbl_assessment_ss_future_tasks` TO `cbl_assessment_future_tasks`;
        DROP TABLE IF EXISTS `cbl_assessment_ss_current_tasks`;
        <?php
        $this->stop();

        return $this->run();
    }

    /**
     * Optional: PHP that verifies whether or not the changes outlined
     * in "up" are present in the active database.
     *
     * Return Values: -1 (not run) | 0 (changes not present or complete) | 1 (present)
     *
     * @return int
     */
    public function audit() {
        $migration = new Models_Migration();
        if ($migration->tableExists(DATABASE_NAME, "cbl_assessment_ss_future_tasks")) {
            if ($migration->tableExists(DATABASE_NAME, "cbl_assessment_ss_current_tasks")) {
                return 1;
            }
        }

        return 0;
    }
}
