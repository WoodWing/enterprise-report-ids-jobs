# UnresponsiveIdsJobs

Enterprise Server plugin that searches long running InDesign Server jobs and notifies a set of users by email.

## Getting Started

### Prerequisites

- Have a running Enterprise installation

### Installing

1. Copy the UnresponsiveIdsJobs folder into the config/plugins folder of the Enterprise Server
2. Open the Server Plugins page in the Enterprise Admin UI to verify the server plugin is enabled
3. Setup Enterprise Server job queue processor. See: https://helpcenter.woodwing.com/hc/en-us/articles/211479563-Working-with-Enterprise-Server-Jobs-in-Enterprise-Server-10#setting-up-and-configuring-enterprise-server-jobs-4.-setting-up-the-job-queue-processor
```
curl "<server url>/jobindex.php?maxexectime=60"
```

4. Setup recurring job for 'UnresponsiveIdsJobs'. See: https://helpcenter.woodwing.com/hc/en-us/articles/211479563-Working-with-Enterprise-Server-Jobs-in-Enterprise-Server-10#setting-up-and-configuring-enterprise-server-jobs-5.-optional-setting-up-recurring-jobs
```
curl "<server url>/Enterprisejobindex.php?createrecurringjob=UnresponsiveIdsJobs"
```

### Configuring

1. In configserver.php / config_overrule.php configure setting for 'E-mail notification'
2. In config.php of the server plugin / config_overrule.php review and configure:
    - UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS: The time after which an InDesign Server job is detected as unresponsive. Default = 7200
    - UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY: User group to send notification mail. Users without an email address are skipped. Default = 'admin'
    - UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS: Email address of the sender of the notification mail. Default: EMAIL_SENDER_ADDRESS
    - UNRESPONSIVEIDSVRJOBS_SENDER_NAME: Name of the sender of the notification email. Default: EMAIL_SENDER_NAME
    - From the resources folder select your language to localize the text that is sent in the notification email.

### Testing

The first step of test is to run the Enterprise Server Health Check. For this plugin you need to perform the following tests:

- 'Server Plugin-ins'
- 'Server Jobs'
- 'Notify Unresponsive InDesign Server Jobs'

Testing the functionality of the plugin would require some manual work in order to get a unresponsive InDesign Server job in the database. I tested the plugin using the following steps:

1. In Enterprise Server/server/apps/IDPreview.js and Enterprise Server/server/plugins/IdsAutomation/indesignserverjob.jsx add the following endless loop code after the scripts open an opject:
```
while(true) {}
```
2. (optional) Delete all current InDesign Server jobs
3. Run an InDesign Server job (which will become unresponsive)
4. In the database make an export of the smart_indesignserverjobs and smart_indesignservers
5. Shut down the InDesign Server to save pc resources (causing the database rows to be altered)
6. Restore the database rows to the values of the export
7. Adjust the value of the UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS , in order to find the job directly after performing these steps (or wait two hours)

# Version and compatibility

19-07-2018 Version 1

Tested with Enterprise Maintenance (10.1.x) and Enterprise Innovation (10.2.x - 10.4.x).
Older Enterprise versions have not been tested, but could work.

Raoul de Grunt - WoodWing Software