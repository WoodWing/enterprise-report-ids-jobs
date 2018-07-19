<?php
/**
 * Unresponsive Ids Jobs TestCase class that belongs to the HealthCheck2 TestSuite of wwtest.
 *
 * This class is automatically read and run by TestSuiteFactory class.
 * See TestSuiteInterfaces.php for more details about the TestSuite concept.
 *
 * @package     UnresponsiveIdsJobs
 * @copyright   WoodWing Software bv. All Rights Reserved.
 */
 
require_once BASEDIR.'/server/wwtest/testsuite/TestSuiteInterfaces.php';
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../UnresponsiveIdsJobsUtils.php';

class WW_TestSuite_HealthCheck2_UnresponsiveIdsJobs_TestCase extends TestCase
{
	public function getDisplayName()
	{
		return 'Notify Unresponsive InDesign Server Jobs';
	}

	public function getTestGoals()   { return 'Checks if the Notify Unresponsive InDesign Server Jobs server plug-in is installed and configured correctly.'; }
	public function getTestMethods() { return '<ul><li>Check if configured values are correct.</li></ul>'; }
	public function getPrio()        { return 30; }

	/**
	 * @inheritdoc
	 */
	final public function runTest()
	{
		LogHandler::Log( 'wwtest', 'INFO', 'Start validation of Unresponsive InDesign Server Jobs configuration.' );
		$this->checkAfterSeconds();
		if( !$this->checkUserGroupToMail() ) {
			$this->setResult( 'ERROR', 'The users in the UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY do not have an email address set.', 'Check the users for the group configured in UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY' );
		}
		if( !$this->checkSenderAddress() ) {
			$this->setResult( 'ERROR', 'The UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS option does not contain an email address.', 'Check the UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS configuration option' );
		}
		$this->checkSenderName();
		LogHandler::Log( 'wwtest', 'INFO', 'Validated Unresponsive InDesign Server Jobs configuration.' );
	}

	/**
	 * Check if the configured time to detect a unresponsive job is correct
	 */
	private function checkAfterSeconds()
	{
		$this->validateDefine('UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS', 'int');
	}

	/**
	 * Checks if the configured user group has minimal 1 user with a valid email address
	 * @return bool
	 */
	private function checkUserGroupToMail()
	{
		$unresponsiveIdsJobsUtils = new UnresponsiveIdsJobsUtils();
		return $unresponsiveIdsJobsUtils->doesConfiguredUserGroupHaveValidEmail();
	}

	/**
	 * Checks if the Sender Address is a valid email address
	 * @return bool
	 */
	private function checkSenderAddress()
	{
		$result = true;
		if($this->validateDefine('UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS', 'string')) {
			$result = filter_var(UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS, FILTER_VALIDATE_EMAIL);
		}		
		return $result;
	}

	/**
	 * Checks if the Sender Name is a valid string with value
	 */
	private function checkSenderName()
	{
		$this->validateDefine('UNRESPONSIVEIDSVRJOBS_SENDER_NAME', 'string');
	}

	private function validateDefine($defineName, $defineType)
	{
		require_once BASEDIR . '/server/utils/TestSuite.php';
		$utils = new WW_Utils_TestSuite();
		return $utils->validateDefines( $this, array($defineName => $defineType), 'config.php' );
	}
}