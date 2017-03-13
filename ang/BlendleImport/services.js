(function (angular, $, _, console) {

    // ImportJobsMgr service to get and save data using the API
    angular.module('BlendleImport').factory('importJobsMgr', function (crmApi, crmQueue) {
        var qApi = crmQueue(crmApi);
        return {
            getJob: function getJob(id) {
                // Get a single BlendleImportJob
                return qApi('BlendleImportJob', 'getsingle', {id: id}).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    return apiResult;
                });
            },

            saveJob: function saveJob(job) {
                // Save BlendleImportJob
                return qApi('BlendleImportJob', 'create', job).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    if(apiResult.values != undefined) {
                        job.id = apiResult.values.id;
                    }
                    return apiResult;
                });
            },

            getRecords: function getRecords(jobId) {
                // Get all BlendleImportRecords for a job ID
                return qApi('BlendleImportRecord', 'get', {job_id: jobId, sequential: 1}).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error || !apiResult.values) {
                        return false;
                    }
                    return apiResult.values;
                });
            },

            updateRecord: function updateRecord(params) {
                // Update a BlendleImportRecord
                return qApi('BlendleImportRecord', 'create', params).then(function(apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    return apiResult;
                });
            },

            matchRecords: function matchRecords(jobId) {
                return qApi('BlendleImportJob', 'match', {id: jobId}).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    return apiResult;
                });
            },

            createContacts: function createContacts(jobId) {
                return qApi('BlendleImportJob', 'createcontacts', {id: jobId}).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    return apiResult;
                });
            },

            matchCheck: function matchCheck(jobId) {
                return qApi('BlendleImportJob', 'matchcheck', {id: jobId}).then(function (apiResult) {
                    if (!apiResult || apiResult.is_error) {
                        return false;
                    }
                    return apiResult;
                });
            }
        };
    });

})(angular, CRM.$, CRM._);
