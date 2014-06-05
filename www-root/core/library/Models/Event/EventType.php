<?php

class Models_Event_EventType extends Models_Base {
    protected $eventtype_id,
              $eventtype_title,
              $eventtype_description,
              $eventtype_active,
              $eventtype_order,
              $eventtype_default_enrollment,
              $eventtype_report_calculation,
              $updated_date,
              $updated_by;
    
    protected $table_name = "events_lu_eventtypes";
    protected $default_sort_column = "eventtype_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getID () {
        return $this->eventtype_id;
    }
    
    public function getEventTypeTitle () {
        return $this->eventtype_title;
    }
    
    public function getEventTypeDescription () {
        return $this->eventtype_description;
    }
    
    public function getEventTypeActive () {
        return $this->eventtype_active;
    }
    
    public function getEventTypeOrder () {
        return $this->eventtype_order;
    }
    
    public function getEventTypeDefaultEnrollment () {
        return $this->eventtype_default_enrollment;
    }
    
    public function getEventTypeReportCalculation () {
        return $this->eventtype_report_calculation;
    }
    
    public function getUpdatedDate () {
        return $this->updated_date;
    }
    
    public function getUpdatedBy () {
        return $this->updated_by;
    }
    
    public static function get ($eventtype_id = null, $active = 1) {
        $self = new self();
        return $self->fetchRow(array("eventtype_id" => $eventtype_id, "eventtype_active" => $active));
    }
}
