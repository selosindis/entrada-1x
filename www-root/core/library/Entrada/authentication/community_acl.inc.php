<?php
 /**
  * Entrada [ http://www.entrada-project.org ]
  * 
  * Provides the Entrada_Community_ACL class.
  * 
  * @author Organisation: David Geffen School of Medicine at UCLA
  * @author Unit: Instructional Design and Technology Unit
  * @author Developer: Robert Fotino <robert.fotino@gmail.com>
  * @copyright Copyright 2014 Regents of the University of California. All Rights Reserved.
  * 
  */

class Entrada_Community_ACL {
    /**
    * This function takes the place of what used to be calls to $ENTRADA_ACL->amIAllowed()
    * and determines whether a user has a certain CRUD permission on a community resource.
    * Used only if the community is linked to a course.
    *
    * @param string $resource_type
    * @param int $resource_value
    * @param string $permission
    * @return bool
    */
    public function amIAllowed($resource_type, $resource_value, $permission) {
        global $db, $ENTRADA_USER;
        $PROXY_ID = $ENTRADA_USER->getActiveID();

        //Get the resource permissions (not pertaining to a particular course group)
        $acl_query = "SELECT * FROM `community_acl`
                      WHERE `resource_type` = ".$db->qstr($resource_type)."
                      AND `resource_value` = ".$db->qstr($resource_value);
        $acl_results = $db->GetRow($acl_query);

        //If there is not a row for this resource, it is probably from before
        //the migration to community_acl so return true. Otherwise old communities
        //will not show any shares to students
        if (!$acl_results) {
            return true;
        }

        //If the resource does not give permission
        if (!isset($acl_results[$permission]) || !$acl_results[$permission]) {
            return false;
        }

        //If the row has an assertion, verify that it passes
        switch($acl_results['assertion']) {
            case 'CourseGroupMember' :
                return CourseGroupMemberAssertion::_checkCourseGroupMember($PROXY_ID, $resource_type, $resource_value, $permission);
            break;
            case 'CourseCommunityEnrollment' :
                switch ($resource_type) {
                    case 'communitydiscussion' :
                        $course_query = "SELECT `course_id`
                            FROM `community_courses` AS a
                            JOIN `community_discussions` AS b
                            ON a.`community_id` = b.`community_id`
                            WHERE b.`cdiscussion_id` = ".$db->qstr($resource_value);
                    break;
                    case 'communityfolder' :
                        $course_query = "SELECT `course_id`
                            FROM `community_courses` AS a
                            JOIN `community_shares` AS b
                            ON a.`community_id` = b.`community_id`
                            WHERE b.`cshare_id` = ".$db->qstr($resource_value);
                    break;
                    case 'communityfile' :
                        $course_query = "SELECT `course_id`
                            FROM `community_courses` AS a
                            JOIN `community_share_files` AS b
                            ON a.`community_id` = b.`community_id`
                            WHERE b.`csfile_id` = ".$db->qstr($resource_value);
                    break;
                    case 'communitylink' :
                        $course_query = "SELECT `course_id`
                            FROM `community_courses` AS a
                            JOIN `community_share_links` AS b
                            ON a.`community_id` = b.`community_id`
                            WHERE b.`cslink_id` = ".$db->qstr($resource_value);
                    break;
                }
                $COURSE_ID = $db->GetOne($course_query);
                return CourseCommunityEnrollmentAssertion::_checkCourseCommunityEnrollment($PROXY_ID, $COURSE_ID);
            break;
        }

        return true;
    }
}
