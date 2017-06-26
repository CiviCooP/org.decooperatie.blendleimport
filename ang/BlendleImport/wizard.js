(function (angular, $, _, console) {

    angular.module('BlendleImport').config(function ($routeProvider) {
            $routeProvider.when('/blendleimport', {
                controller: 'BlendleImportWizard',
                templateUrl: '~/BlendleImport/wizard.html',
                resolve: {
                    job: function ($location, importJobsMgr) {
                        var queryParams = $location.search();
                        if (queryParams.action == 'update') {
                            // Fetch job
                            return importJobsMgr.getJob(queryParams.id);
                        } else {
                            // Return empty object
                            return {};
                        }
                    }
                }
            });
        }
    );

    angular.module('BlendleImport').controller('BlendleImportWizard', function ($scope, $location, $q, crmApi, crmStatus, crmUiHelp, crmUiAlert, importJobsMgr, job) {

        // -- Initialisation -- //

        var ts = $scope.ts = CRM.ts('org.decooperatie.blendleimport');

        $scope.job = job;
        $scope.job_fields = [];
        $scope.job_records = [];
        $scope.csv_upload = null;

        $scope.tasks = {};
        $scope.taskCounts = {};
        $scope.logOutput = [];

        $scope.includeTemplateNames = {
            0: '~/BlendleImport/wizard-step1.html',
            1: '~/BlendleImport/wizard-step2.html',
            2: '~/BlendleImport/wizard-step3.html',
            3: '~/BlendleImport/wizard-step4.html'
        };

        var queryParams = $location.search();
        if (queryParams.action == 'update' && !$scope.job) {
            crmUiAlert({'text': ts('An error occurred loading the import job.'), 'title': ts('Blendle Import Error')});
        }

        // -- Step submit functions -- //

        // Submit step 1 (upload CSV)
        $scope.submitStep1 = function () {
            // Check if form is valid + either a CSV file is already present or one is being uploaded
            // window.console.log($scope.uiWizardCtrl.$validStep(), $scope.uiWizardCtrl, $scope.job, $scope);
            if ($scope.uiWizardCtrl.$validStep() && ($scope.job.data_present || $scope.job.data)) {

                crmStatus({start: ts('Saving and processing...'), success: ts('Saved')},
                    importJobsMgr.saveJob($scope.job)
                ).then(function (result) {
                    if (result) {
                        $scope.loadThenGotoStep2();
                    } else {
                        crmUiAlert({
                            text: ts('An error occurred while trying to save and upload job data.'),
                            title: ts('Error')
                        });
                    }
                });
            } else {
                $scope.genericFormError();
            }
        };

        // Submit step 2 (parse file)
        $scope.submitStep2 = function () {

            var mappingData = {};
            var isValid = true;
            _.each($scope.job_fields, function(field, fIndex) {
                if(field.required && !field.mapping) {
                    isValid = false;
                }
               mappingData[field.name] = field.mapping;
            });
            if(!isValid) {
                $scope.genericFormError();
                return false;
            }
            // window.console.log(mappingData);

            crmStatus({start: ts('Saving and processing...'), success: ts('Saved')},
                importJobsMgr.updateMappingAndLoadData($scope.job.id, mappingData)
                ).then(function(result) {
                if (result) {
                    $scope.loadThenGotoStep3();
                } else {
                    crmUiAlert({
                        text: ts('An error occurred while trying to save column mapping data.'),
                        title: ts('Error')
                    });
                }
            });
        };

        // Submit step 3 (match contacts)
        $scope.submitStep3 = function () {

            crmStatus({start: ts('Checking match status...'), success: ts('Finished')},
                importJobsMgr.matchCheck($scope.job.id)
            ).then(function (result) {
                if (result.values == true) {
                    $scope.loadThenGotoStep4();
                } else {
                    crmUiAlert({
                        text: ts('Not all records have been matched with a CiviCRM contact yet. Match all contacts before continuing.'),
                        title: ts('Error')
                    });
                }
            });
        };

        // Submit step 4 (import data)
        $scope.submitStep4 = function () {
            $scope.logOutput = [{'message':'Running import, please wait...'}];
            var qchain = $q.when(); // We need to queue all import tasks in order!

            _.each($scope.tasks, function(enabled, taskName) {
                if(enabled == true) {
                    qchain.then(function() {
                            return importJobsMgr.runImport($scope.job.id, taskName).then(function (result) {
                                $scope.updateLogOutput(result);
                            });
                        });
                }
            });

            // crmStatus({start: ts('Importing data...'), success: ts('Import complete')}, qchain);
        };

        // -- Step loading functions -- //

        // Load data needed for the next step's view. This would usually happen in
        // a separate controller, but I think that isn't supported in combination with crmUiWizard
        $scope.loadThenGotoStep2 = function () {
            // Load records = contacts to match
            crmStatus({start: ts('Loading data...'), success: ts('Loading complete')},
                importJobsMgr.getColumnInfo($scope.job.id).then(function (result) {
                    $scope.job_fields = result;
                    $scope.uiWizardCtrl.goto(1);
                }));
        };

        $scope.loadThenGotoStep3 = function () {
            // Load records = contacts to match
            crmStatus({start: ts('Loading data...'), success: ts('Loading complete')},
                importJobsMgr.getRecords($scope.job.id).then(function (result) {
                    $scope.job_records = result;
                    $scope.uiWizardCtrl.goto(2);
                }));
        };

        $scope.loadThenGotoStep4 = function () {
            // Preset tasks to run
            $scope.tasks = {
                'activity': (['tagsmemb', 'payments', 'complete'].indexOf($scope.job.status) == -1),
                'tag': (['payments', 'complete'].indexOf($scope.job.status) == -1 && ($scope.job.add_tag != null)),
                'membership': (['payments', 'complete'].indexOf($scope.job.status) == -1 && ($scope.job.add_membership_type != null)),
                'payment': ($scope.job.status != 'complete')
            };

            // Get import counts, then show step UI
            crmStatus({start: ts('Loading data...'), success: ts('Loading complete')},
                importJobsMgr.getImportCount($scope.job.id).then(function (result) {
                    $scope.taskCounts = result;
                    $scope.uiWizardCtrl.goto(3);
                }));
        };

        // -- Wizard UI functions -- //

        // Skipping steps is allowed if already completed earlier
        $scope.skipGoto = function (stepId) {
            $scope.uiWizardCtrl.goto(stepId);
        };

        // Back to import jobs overview
        $scope.leave = function () {
            window.location = CRM.url('civicrm/blendleimport');
        };

        // Open new window/tab with contact data
        $scope.viewContact = function (cid) {
            var url = CRM.url('civicrm/contact/view', {cid: cid, reset: 1});
            window.open(url);
        };

        // Open create new tag window
        $scope.addNewTag = function () {
            window.open(CRM.url('civicrm/tag/edit?action=add'));
        };

        // Show generic form error alert
        $scope.genericFormError = function () {
            crmUiAlert({
                'text': ts('Please complete all required fields.'),
                'title': ts('Form Error'),
                'type': 'alert',
                'options': {'expires': 1500}
            });
        };

        // Update / append log output
        $scope.updateLogOutput = function (data) {
            if(data.is_error) {
                $scope.logOutput.push({'message': 'ERROR (API): ' + data.error_message});
            } else {
                _.each(data.values, function (line, index) {
                    // window.console.log('Log line', line);
                    $scope.logOutput.push(line);
                });
            }
            $('.log-output').animate({scrollTop: $('.log-output').eq(0).scrollHeight}, 250);
        };

        // -- Contact matching functions -- //
        // (Largely borrowed from CSVImportHelper/Main.js)

        $scope.countRecordsNoMatch = function () {
            return $scope.job_records.reduce(function (a, record) {
                return (record.state == 'impossible') ? a + 1 : a;
            }, 0);
        };

        $scope.updateRecord = function (params, rowIndex) {
            // window.console.log('updateRecord', params);
            return crmStatus(
                {start: ts('Updating...'), success: ts('OK')},
                importJobsMgr.updateRecord(params)
            ).then(function (result) {
                // window.console.log('Updating data', $scope.job_records[rowIndex], ' with ', result.values);
                $scope.job_records[rowIndex] = result.values;
            });
        };

        $scope.chooseContact = function (event) {
            // window.console.log('chooseContact', this.$parent.$parent.row, this.contact);
            return $scope.updateRecord({
                id: this.$parent.$parent.row.id,
                contact_id: this.contact.contact_id,
                state: 'chosen',
                match: 'Chosen by you'
            }, this.$parent.$parent.rowIndex);
        };

        $scope.unChooseContact = function (event) {
            return $scope.updateRecord({
                id: this.$parent.row.id,
                contact_id: 0,
                state: 'impossible'
            }, this.$parent.rowIndex);
        };

        $scope.chooseContact2 = function () {
            // This is trigged by the user selecting a contact with the crmEntityref widget.
            // window.console.log('chooseContact2', this.$parent.row);
            return $scope.updateRecord({
                id: this.$parent.row.id,
                contact_id: this.$parent.row.contact_id,
                state: 'chosen',
                match: 'Chosen by you'
            }, this.$parent.rowIndex);
        };

        $scope.recheckRecords = function () {
            crmStatus({start: ts('Re-scanning...'), success: ts('Finished')},
                importJobsMgr.matchRecords($scope.job.id)
            ).then(function (result) {
                if (result.is_error) {
                    crmUiAlert({'text': ts('An error occurred while matching records.'), 'title': ts('Error')});
                } else {
                    var count = result.values.shift().contacts_matched;
                    crmUiAlert({
                        'text': ts('%1 records (re)matched.', {1: count}),
                        'type': 'success',
                        'options': {expires: 1500}
                    });
                    crmStatus({start: ts('Reloading data...'), success: ts('Loading complete')},
                        importJobsMgr.getRecords($scope.job.id)
                    ).then(function (result) {
                        $scope.job_records = result;
                    });
                }
            });
        };

        $scope.createContacts = function () {
            crmStatus({start: ts('Creating new contacts...'), success: ts('Finished')},
                importJobsMgr.createContacts($scope.job.id)
            ).then(function (result) {
                if (result.is_error) {
                    crmUiAlert({'text': ts('An error occurred while creating new contacts.'), 'title': ts('Error')});
                } else {
                    var count = result.values.shift().contacts_created;
                    crmUiAlert({
                        'text': ts('%1 new contacts created.', {1: count}),
                        'type': 'success',
                        'options': {expires: 1500}
                    });
                    crmStatus({start: ts('Reloading data...'), success: ts('Loading complete')},
                        importJobsMgr.getRecords($scope.job.id)
                    ).then(function (result) {
                        $scope.job_records = result;
                    });
                }
            });
        };

        // --- End scope --- //

    });

})(angular, CRM.$, CRM._);
