<?php
/****************************************************************************
   Copyright 2018 WoodWing Software BV

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
****************************************************************************/

require_once BASEDIR . '/server/interfaces/plugins/connectors/ServerJob_EnterpriseConnector.class.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/UnresponsiveIdsJobsUtils.php';

class UnresponsiveIdsJobs_ServerJob extends ServerJob_EnterpriseConnector
{
	/**
	 * @inheritdoc
	 */
	public function getJobConfig( ServerJobConfig $jobConfig ) 
	{
		$jobConfig->SysAdmin = true;
		$jobConfig->Recurring = true;
	}

	/**
	 * @inheritdoc
	 */
	public function createJob( $putIntoQueue = true ) 
	{
		require_once BASEDIR.'/server/dataclasses/ServerJob.class.php';
		$job = new ServerJob();
		$job->JobType = 'UnresponsiveIdsJobs';
		self::serializeJobFieldsValue( $job );

		if( $putIntoQueue ) {
			// Push the job into the queue (for async execution)
			require_once BASEDIR.'/server/bizclasses/BizServerJob.class.php';
			$bizServerJob = new BizServerJob();
			$bizServerJob->createJob( $job );
		}
		return $job;
	}

	/**
	 * @inheritdoc
	 */
	public function runJob( ServerJob $job ) 
	{
		self::unserializeJobFieldsValue( $job ); // ServerJob came from BizServerJob->runJob(), so unserialize the necessary data.
		$notifyResult = true;
		if( self::isUnresponsiveIdsJobsEnabled() ) {
			$unresponsiveIdsJobsUtils = new UnresponsiveIdsJobsUtils();
			$notifyResult = $unresponsiveIdsJobsUtils->notifyUnresponsiveInDesignServerJobs();
		}
		else {
			LogHandler::Log('UnresponsiveIdsJobs_ServerJob', 'INFO', 'Job type of \'UnresponsiveIdsJobs\' is not enabled, job is not executed.');
		}

		if( $notifyResult ) {
			$job->JobStatus->setStatus( ServerJobStatus::COMPLETED );
		} else {
			$job->JobStatus->setStatus( ServerJobStatus::FATAL );
		}

		self::serializeJobFieldsValue( $job ); // Before handling back to BizServerJob->runJob, serialize the data.
	}

	/**
	 * Prepare ServerJob (parameter $job) to be ready for use by the caller.
	 *
	 * The parameter $job is returned from database as it is (i.e some data might be
	 * serialized for DB storage purposes ), this function make sure all the data are
	 * un-serialized.
	 *
	 * Mainly called when ServerJob Object is passed from functions in BizServerJob class.
	 *
	 * @param ServerJob $job
	 */
	private static function unserializeJobFieldsValue( ServerJob $job )
	{
		// Make sure to include the necessary class file(s) here, else it will result into
		// 'PHP_Incomplete_Class Object' during unserialize.
		require_once BASEDIR.'/server/interfaces/services/wfl/DataClasses.php';
		if( !is_null( $job->JobData )) {
			$job->JobData = unserialize( $job->JobData );
		}
	}

	/**
	 * Make sure the parameter $job passed in is ready for used by database.
	 *
	 * Mainly called when ServerJob Object needs to be passed to functions in BizServerJob class.
	 *
	 * @param ServerJob $job
	 */
	private static function serializeJobFieldsValue( ServerJob $job )
	{
		if( !is_null( $job->JobData )) {
			$job->JobData = serialize( $job->JobData ) ;
		}
	}

	/**
	 * To determine if the Unresponsive Ids Jobs is enabled.
	 *
	 * The function returns true when:
	 * L> The job type 'UnresponsiveIdsJobs' is registered in the admin page.
	 * L> A user is configured for the 'UnresponsiveIdsJobs' job type.
	 *
	 * @return bool Whether the 'UnresponsiveIdsJobs' job type is enabled (when all the criteria above are met).
	 */
	public static function isUnresponsiveIdsJobsEnabled()
	{
		$unresponsiveIdSvrEnabled = false;
		do {
			$registered = false;
			$userAssigned = false;
			$bizJobConfig = new BizServerJobConfig();
			$dbConfigs = $bizJobConfig->listJobConfigs();
			if( $dbConfigs ) foreach( $dbConfigs as $jobConfigs ) {
				foreach ( $jobConfigs as $name => $jobConfig ) {
					if( $name == 'UnresponsiveIdsJobs' ) {
						$registered = true;
						if( $jobConfig->UserId ) {
							$userAssigned = true;
						}
						break 2; // Quit two foreach loop.
					}
				}
			}

			if( !$registered || !$userAssigned ) {
				break;
			}

			$unresponsiveIdSvrEnabled = true;
		} while ( false );

		return $unresponsiveIdSvrEnabled;
	}
}
