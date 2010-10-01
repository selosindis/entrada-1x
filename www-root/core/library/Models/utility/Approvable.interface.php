<?php
interface Approvable {
	public function approve();
	public function unapprove();
		public function isApproved();
	
	public function reject();
	public function isRejected(); 
}