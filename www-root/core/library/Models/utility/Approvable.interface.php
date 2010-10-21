<?php
interface Approvable {
	public function approve();
	public function unapprove();
	public function isApproved();
	
	public function reject($comment); //reason/comment required for rejection
	public function isRejected(); 
	
	public function getComment();
}