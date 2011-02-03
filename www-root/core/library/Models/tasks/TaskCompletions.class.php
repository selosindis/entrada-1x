<?php

require_once("Models/utility/Collection.class.php");
require_once("TaskCompletion.class.php");
require_once("Models/users/User.class.php");

class TaskCompletions extends Collection {
	
	/**
	 * Returns a collectionof TaskVerification objects. One for each user requiring verification. 
	 * 
	 * @return TasVerifications
	 */
	public static function getByTask($task_id, $options=null) {
		
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					$order[] = "`".$orders[0]."` ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
		}
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		} else {
			$limit = -1;
		}
		if (isset($options['offset'])) {
			$offset = $options['offset'];
		} else {
			$offset = -1;
		}
		
		global $db;
		
		$query = "	SELECT DISTINCT * from (
					SELECT a.*, b.`task_id`, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date`, c.`faculty_id`, c.`completion_comment`, c.`rejection_comment`, c.`rejection_date` 
					from `".AUTH_DATABASE."`.`user_data` a 
					inner join `task_recipients` b on a.`grad_year`=b.`recipient_id` 
					left join `task_completion` c on c.`task_id`=b.`task_id` and c.`recipient_id` = a.`id`
					where b.`recipient_type`='grad_year' and b.`task_id`=?
					UNION
					SELECT w.*, z.`task_id`, y.`verifier_id`, y.`verified_date`, z.`recipient_id`, y.`completed_date`, y.`faculty_id`, y.`completion_comment`, y.`rejection_comment`, y.`rejection_date` 
					from `task_recipients` z 
					left join `task_completion` y on z.`recipient_id`=y.`recipient_id` and y.`task_id`=z.`task_id`
					inner join `".AUTH_DATABASE."`.`user_data` w on z.`recipient_id`=w.`id`
					where z.`recipient_type`='user' and z.`task_id`=?
					UNION
					SELECT i.*, j.`task_id`, k.`verifier_id`, k.`verified_date`, i.`id` as `recipient_id`, k.`completed_date`, k.`faculty_id`, k.`completion_comment`, k.`rejection_comment`, k.`rejection_date` 
					from `task_recipients` j
					inner join `".AUTH_DATABASE."`.`user_data` i on j.`recipient_id`=i.`organisation_id` 
					left join `task_completion` k on i.`id`=k.`recipient_id` and j.`task_id`=k.`task_id`
					where j.`recipient_type` = 'organisation' and j.`task_id`=?) m".$order_by;
		
		$results = $db->SelectLimit($query,$limit, $offset, array($task_id,$task_id, $task_id));
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$completion = TaskCompletion::fromArray($result);
				$recipient = User::fromArray($result); //and throw away. will be retrieved from cache when needed
				$completions[] = $completion;
			}
		}
		return new self($completions);
	}
	
	public static function getByVerifier($proxy_id, $options=null) {
		
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					$order[] = "`".$orders[0]."` ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
		}
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		} else {
			$limit = -1;
		}
		if (isset($options['offset'])) {
			$offset = $options['offset'];
		} else {
			$offset = -1;
		}
		if (isset($options['where'])) {
			$where = " AND " . $options['where'];
		}
		global $db;
		
		$query = "	SELECT a.*, b.*,c.* from `task_completion` a
					left join `tasks` b on b.`task_id`=a.`task_id`
					inner join `".AUTH_DATABASE."`.`user_data` c on a.`recipient_id`=c.`id` 
					where a.`verifier_id`=?".$where.$order_by;
		
		$results = $db->SelectLimit($query,$limit, $offset, array($proxy_id));
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$task = Task::fromArray($result);//for cache
				$user = User::fromArray($result);//for cache
				$completion = TaskCompletion::fromArray($result);
				$completions[] = $completion;
			}
		}
		return new self($completions);
	}
	
	public static function isVerifier($proxy_id, $task_id) {
		global $db;

		$query = "SELECT `verifier_id` from `task_completion` where `verifier_id`=? and `task_id`=?";
		
		$result = $db->getOne($query, array($proxy_id,$task_id));
		
		return (!!$result);
	}
	
	public static function getVerifiers($task_id) {
		global $db;

		$query = "SELECT  b.* from `task_completions` a left join `".AUTH_DATABASE."`.`user_data` b on a.`verifier_id`=b.`id` where a.`task_id`=?";
		
		$result = $db->getAll($query, array($task_id));
		$verifiers = array();
		if ($results) {
			foreach ($results as $result) {
				$verifier = User::fromArray($result);
				if ($verifier) {
					$verifiers[] = $verifier;
				}
			}
		}
		return new TaskVerifiers($verifiers);
	}
	
	public static function getByRecipient(User $recipient, $options=null) {
		if (isset($options['order_by'])) {
			if (is_array($options['order_by'])) {
				foreach ($options['order_by'] as $orders) {
					if ($orders[0] == "deadline") {
						$orders[0] = "COALESCE(`deadline`,9223372036854775807)";
					} else {
						$orders[0] = "`".$orders[0]."`";
					}
					$order[] = $orders[0]." ". (isset($orders[1]) ? $orders[1] : "asc"); 
				}	
			}
			$order_by = " ORDER BY ".implode(",",$order);
		}
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		} else {
			$limit = -1;
		}
		if (isset($options['offset'])) {
			$offset = $options['offset'];
		} else {
			$offset = -1;
		}
		if (isset($options['where'])) {
			$where = ' and ' . $options['where'] . " ";
		}		
		
		
		global $db;
		
		$proxy_id = $recipient->getID();
		
		$query = "	SELECT DISTINCT * from (
					SELECT a.id, d.*, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date`, c.`faculty_id`, c.`completion_comment`, c.`rejection_comment`, c.`rejection_date` 
					from `".AUTH_DATABASE."`.`user_data` a 
					inner join `task_recipients` b on a.`grad_year`=b.`recipient_id` 
					left join `task_completion` c on c.`task_id`=b.`task_id` and c.`recipient_id` = a.`id`
					left join `tasks` d on b.`task_id`=d.`task_id`
					where b.`recipient_type`='grad_year'
					UNION
					SELECT w.id, x.*, y.`verifier_id`, y.`verified_date`, z.`recipient_id`, y.`completed_date`, y.`faculty_id`, y.`completion_comment`, y.`rejection_comment`, y.`rejection_date` 
					from `task_recipients` z 
					left join `task_completion` y on z.`recipient_id`=y.`recipient_id` and y.`task_id`=z.`task_id`
					inner join `".AUTH_DATABASE."`.`user_data` w on z.`recipient_id`=w.`id`
					left join `tasks` x on x.`task_id`=z.`task_id`
					where z.`recipient_type`='user'
					UNION
					SELECT i.id, l.*, k.`verifier_id`, k.`verified_date`, i.`id` as `recipient_id`, k.`completed_date`, k.`faculty_id`, k.`completion_comment`, k.`rejection_comment`, k.`rejection_date` 
					from `task_recipients` j
					inner join `".AUTH_DATABASE."`.`user_data` i on j.`recipient_id`=i.`organisation_id` 
					left join `task_completion` k on i.`id`=k.`recipient_id` and j.`task_id`=k.`task_id`
					left join `tasks` l on l.`task_id`=j.`task_id`
					where j.`recipient_type` = 'organisation') m where id=?";
		
		$results = $db->SelectLimit($query.$where.$order_by, $limit, $offset,array($proxy_id));
		
		
		$completions = array();
		if ($results) {
			foreach ($results as $result) {
				$completion = TaskCompletion::fromArray($result);
				$task = Task::fromArray($result);
				$completions[] = $completion;
			}
		}
		return new self($completions);
		
	}
}