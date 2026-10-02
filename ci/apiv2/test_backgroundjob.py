import subprocess

import requests

from hashtopolis import File, Helper
from utils import BaseTest, BackgroundJob, create_restricted_user, get_hashtopolis_uri

CRON_RUNNER = '/var/www/html/src/inc/cron.php'


def run_background_job_runner():
    result = subprocess.run(
        ['php', '-d', 'xdebug.mode=off', '-f', CRON_RUNNER],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 0, f"Background job runner failed: {result.stderr}"


def get_jobs_for_file(file_id):
    return [job for job in BackgroundJob.objects.all() if job.payload.get('fileId') == file_id]


def get_admin_connector():
    connector = BackgroundJob.objects.get_conn()
    connector.authenticate()
    return connector


def enqueue_recount_job(file_obj):
    helper = Helper()
    helper.recount_file_lines(file=file_obj)


class BackgroundJobTest(BaseTest):
    model_class = BackgroundJob

    def create_test_object(self, *nargs, delete=True, **kwargs):
        # Background jobs cannot be created directly via the API, they get enqueued by
        # other operations. The recount file lines helper is the reference producer.
        file_obj = self.create_file()
        enqueue_recount_job(file_obj)
        jobs = get_jobs_for_file(file_obj.id)
        assert len(jobs) == 1
        if delete:
            self.delete_after_test(jobs[0])
        return jobs[0]

    def test_recount_file_job_is_enqueued_as_pending(self):
        model_obj = self.create_test_object()

        self.assertEqual(model_obj.jobType, 'recount_file')
        self.assertEqual(model_obj.status, 0)
        self.assertIn('fileId', model_obj.payload)
        self.assertIsNotNone(model_obj.userId)
        self.assertIsNotNone(model_obj.createdAt)
        self.assertIsNone(model_obj.startedAt)
        self.assertIsNone(model_obj.finishedAt)

    def test_recount_file_job_is_processed_by_runner(self):
        model_obj = self.create_test_object()
        file_id = model_obj.payload['fileId']

        run_background_job_runner()

        job = self.model_class.objects.get(pk=model_obj.id)
        self.assertEqual(job.status, 2)
        self.assertEqual(job.exitCode, 0)
        self.assertEqual(job.resultMessage, 'Recounted 3 lines.')
        self.assertIsNotNone(job.startedAt)
        self.assertIsNotNone(job.finishedAt)

        file = File.objects.get(pk=file_id)
        self.assertEqual(file.lineCount, 3)

    def test_delete_pending_job(self):
        model_obj = self.create_test_object(delete=False)

        # A pending (queued) job can be deleted, which cancels it
        model_obj.delete()

        with self.assertRaises(self.model_class.DoesNotExist):
            _ = self.model_class.objects.get(pk=model_obj.id)

    def test_delete_processed_job(self):
        model_obj = self.create_test_object(delete=False)

        run_background_job_runner()
        model_obj.delete()

        with self.assertRaises(self.model_class.DoesNotExist):
            _ = self.model_class.objects.get(pk=model_obj.id)

    def test_create_via_api_not_allowed(self):
        connector = get_admin_connector()
        response = requests.post(
            connector._api_endpoint + '/ui/backgroundJobs',
            headers=connector._headers,
            data='{"data": {"type": "backgroundJob", "attributes": {}}}',
        )
        self.assertEqual(response.status_code, 405, response.text)

    def test_restricted_user_cannot_access_without_server_config_access(self):
        self.create_test_object()

        auth = create_restricted_user(self, {'permFileRead': True})
        response = requests.post(
            get_hashtopolis_uri() + '/api/v2/auth/token',
            auth=auth,
        )
        self.assertIn(response.status_code, [200, 201], response.text)
        token = response.json()['token']

        connector = get_admin_connector()
        response = requests.get(
            connector._api_endpoint + '/ui/backgroundJobs',
            headers={'Authorization': f'Bearer {token}'},
        )
        self.assertEqual(response.status_code, 403, response.text)
