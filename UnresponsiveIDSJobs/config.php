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

if(!defined('UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS'))
    define('UNRESPONSIVEIDSVRJOBS_AFTER_SECONDS', 7200);

if(!defined('UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY'))
    define('UNRESPONSIVEIDSVRJOBS_USERGROUP_TO_NOTIFY', 'admin');

if(!defined('UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS'))
    define('UNRESPONSIVEIDSVRJOBS_SENDER_ADDRESS', EMAIL_SENDER_ADDRESS);

if(!defined('UNRESPONSIVEIDSVRJOBS_SENDER_NAME'))
    define('UNRESPONSIVEIDSVRJOBS_SENDER_NAME', EMAIL_SENDER_NAME);

if(!defined('NOTIFY_EMAIL_JOB_SEPARATOR'))
    define('NOTIFY_EMAIL_JOB_SEPARATOR', '------------------------------');