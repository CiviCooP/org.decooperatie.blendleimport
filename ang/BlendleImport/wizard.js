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
        $scope.job_records = [];
        $scope.csv_upload = null;

        var queryParams = $location.search();
        if (queryParams.action == 'update' && !$scope.job) {
            crmUiAlert({'text': ts('An error occurred loading the import job.'), 'title': ts('Blendle Import Error')});
        }

        $scope.includeTemplateNames = {
            0: '~/BlendleImport/wizard-step1.html',
            1: '~/BlendleImport/wizard-step2.html',
            2: '~/BlendleImport/wizard-step3.html',
            3: '~/BlendleImport/wizard-step4.html',
            4: '~/BlendleImport/wizard-step5.html'
        };

        // -- Step submit functions -- //

        // Submit step 1 (upload CSV)
        $scope.submitStep1 = function () {
            // Check if form is valid + either a CSV file is already present or one is being uploaded
            // window.console.log($scope.uiWizardCtrl.$validStep(), $scope.uiWizardCtrl, $scope.job, $scope);
            if ($scope.uiWizardCtrl.$validStep() && ($scope.job.record_count || $scope.job.csv_upload)) {

                $scope.job.status = 'contacts';
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

        // Submit step 2 (match contacts)
        $scope.submitStep2 = function () {

            crmStatus({start: ts('Checking match status...'), success: ts('Finished')},
                importJobsMgr.matchCheck($scope.job.id)
            ).then(function (result) {
                if (result.values == true) {
                    $scope.loadThenGotoStep3();
                } else {
                    crmUiAlert({
                        text: ts('Not all records have been matched with a CiviCRM contact yet. Match all contacts before continuing.'),
                        title: ts('Error')
                    });
                }
            });
        };

        // -- Step loading functions -- //

        // Load data needed for the next step's view. This would usually happen in
        // a separate controller, but I think that isn't supported in combination with crmUiWizard
        $scope.loadThenGotoStep2 = function () {
            crmStatus({start: ts('Loading data...'), success: ts('Loading complete')},
                importJobsMgr.getRecords($scope.job.id).then(function (result) {
                    $scope.job_records = result;
                    $scope.uiWizardCtrl.goto(1);
                }));
        };

        $scope.loadThenGotoStep3 = function () {
            crmUiAlert({'text': ts('TODO Load data and show step 3'), 'type': 'success', 'options': {'expires': 1500}});
            $scope.uiWizardCtrl.goto(2);
        }

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
            var url = CRM.url('civicrm/contact/view', { cid: cid, reset: 1 });
            window.open(url);
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
            // e.g. after you've tweaked some data in CiviCRM.
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
