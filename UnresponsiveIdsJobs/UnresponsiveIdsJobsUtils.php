<?php

require_once BASEDIR . '/server/bizclasses/BizInDesignServerJob.class.php';
require_once BASEDIR . '/server/bizclasses/BizEmail.class.php';
require_once BASEDIR . '/server/bizclasses/BizObject.class.php';
require_once BASEDIR . '/server/dbclasses/DBObject.class.php';

class UnresponsiveIdsJobsUtils
{
	private $dbDriver;
	private $emailText = '';

	function __construct()
    {
		$this->dbDriver = DBDriverFactory::gen();
    }

	/**
	 *  Public functions
	 */
	/**
	 * Check for long running InDesign Server Jobs and send a notification mail when found
	 * @return bool
	 */
    public function notifyUnresponsiveInDesignServerJobs()
    {
		$idSvrJobs = $this->getLongRunningIdSvrJobs();
		if(!empty($idSvrJobs)) {
			foreach($idSvrJobs as $idSvrJob) {
				$this->handleIdSvrJob($idSvrJob);
			}
			$result = $this->sendNotificationEmail();
		}
		return $result;
	}

	/**
	 * Checks if the configured user group has minimal 1 user with a valid email address
	 * @return bool
	 */
	public function doesConfiguredUserGroupHaveValidEmail()
	{
		return !empty($this->getConfiguredGroupEmailAddresses());
	}

	/**
	 *  Protected functions
	 */
	protected function getLongRunningIdSvrJobs()
	{
		LogHandler::Log('UnresponsiveIdsJobsUtils', 'DEBUG', 'Searching long running InDesign Server jobs');
		$queryResult = $this->dbDriver->query($this->createLongRunningIdSvrJobsSql(), $this->createLongRunningIdSvrJobsSqlParams());
		$result = array();
		while(($row = $this->dbDriver->fetch($queryResult)) ) {
			$result[] = $row;
		}
		return $result;
	}

	protected function createLongRunningIdSvrJobsSql()
	{
		$idSvrTable = $this->dbDriver->tablename('indesignservers');
		$idSvrJobsTable = $this->dbDriver->tablename('indesignserverjobs');		
		$sql = 'SELECT * FROM ' . $idSvrTable . ' i ';
		$sql .= 'LEFT JOIN ' . $idSvrJobsTable . 'j on (i.`locktoken` = j.`locktoken`) ';
		$sql .= 'WHERE i.locktoken != ? AND j.starttime <= ?';
		return $sql;
	}

	protected function createLongRunningIdSvrJobsSqlParams()
	{
		return array('', $this->getStartTimeSearchParam());
	}

	protected function getStartTimeSearchParam()
	{
		return date('Y-m-d\TH:i:s', time() - UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS);
	}

	protected function handleIdSvrJob($idSvrJob)
	{
		LogHandler::Log('UnresponsiveIdsJobsUtils', 'DEBUG', 'Handling job: ' . $idSvrJob['jobid']);
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_HEADER') . '<br /><br />';
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_JOB_FOUND', true, array($idSvrJob['jobid'])) . '<br />';
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_JOB_START_TIME', true, array($idSvrJob['starttime'])) . '<br />';
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_JOB_IDSVR_INSTANCE', true, array($idSvrJob['hostname'], $idSvrJob['portnumber'])) . '<br /><br />';
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_CONSIDER_RESTART') . '<br /><br />';
		$this->emailText .= BizResources::localize('UnresponsiveIdsJobs.NOTIFY_OBJECT_POSSIBLY_LOCKED', true, array(DBObject::getObjectName($idSvrJob['objid']))) . '<br />';
		$this->emailText .= NOTIFY_EMAIL_JOB_SEPARATOR . '<br />';
	}

	protected function sendNotificationEmail()
	{
		$result = true;
		if(!BizEmail::sendEmail(UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS, UNRESPONSIVEIDSVRJOBS_SENDER_NAME, $this->getConfiguredGroupEmailAddresses(), BizResources::localize('UnresponsiveIdsJobs.NOTIFY_EMAIL_SUBJECT'), $this->emailText)) {
			LogHandler::Log('UnresponsiveIdsJobs', 'ERROR', 'Failed to send out Unresponsive InDesignSever Jobs report via email. Please run wwtest to check the email settings.');
			$result = false;
		}
		return $result;
	}

	protected function getConfiguredGroupEmailAddresses()
	{
		$result = array();
		$userGroupId = $this->getGroupIdForConfiguredGroup();
		if(!empty($userGroupId))
			$result = $this->getEmailAddresses($userGroupId);
		return $result;
	}

	protected function getGroupIdForConfiguredGroup()
	{
		$result = '';
		$userGroup = DBUser::getUserGroup(UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY);
		if(!empty($userGroup)) {
			$result = $userGroup['id'];
		}
		return $result;
	}

	protected function getEmailAddresses($userGroupId)
	{
		$result = array();
		$queryResult = $this->dbDriver->query($this->createEmailsFromGroupSql(), $this->createEmailsFromGroupSqlParams($userGroupId));
		while( ($row = $this->dbDriver->fetch($queryResult)) ) {
			if(empty($row['fullname'])) {
				$row['fullname'] = $row['email'];
			}
			$result[$row['email']] = $row['fullname'];
		}
		return $result;
	}

	protected function createEmailsFromGroupSql()
	{
		$usersTable = $this->dbDriver->tablename('users');
		$usrGrpTable = $this->dbDriver->tablename('usrgrp');
		$sql = 'SELECT u.`email`, u.`fullname` FROM ' . $usersTable . ' u, ' . $usrGrpTable . ' x ';
		$sql .= 'WHERE x.`usrid` = u.`id` AND x.`grpid` = ? AND u.`email` != ?';
		return $sql;
	}

	protected function createEmailsFromGroupSqlParams($userGroupId)
	{
		return array($userGroupId, '');
	}
}