import base64
import binascii
import subprocess

import pytest
import requests

from hashtopolis import File, Helper
from utils import (BaseTest, BackgroundJob, create_restricted_user,
                   get_hashtopolis_uri)

CRON_RUNNER = '/var/www/html/src/inc/cron.php'


def run_background_job_runner():
    result = subprocess.run(
        ['php', '-d', 'xdebug.mode=off', '-f', CRON_RUNNER],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 0, f"Background job runner failed: {result.stderr}"


def get_admin_connector():
    connector = File.objects.get_conn()
    connector.authenticate()
    return connector


class FileTest(BaseTest):
    model_class = File

    def create_test_object(self, *nargs, **kwargs):
        return self.create_file(*nargs, **kwargs)

    @staticmethod
    def _jobs_for_file(file_id, job_type=None):
        """Background jobs enqueued for one file, optionally narrowed to a single job type."""
        return [job for job in BackgroundJob.objects.all()
                if job.payload.get('fileId') == file_id
                and (job_type is None or job.jobType == job_type)]

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'isSecret', True)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['accessGroup']
        self._test_expandables(model_obj, expandables)

    def test_create_binary(self):
        model_obj = self.create_test_object(compress=True)
        self._test_create(model_obj)

    def test_recount_wordlist(self):
        # Note: The recounting is enqueued as background job, but it was already done on the object creation,
        #       the file is returned with its immutable line count.
        model_obj = self.create_test_object()

        helper = Helper()
        file = helper.recount_file_lines(file=model_obj)

        self.assertEqual(file.lineCount, 3)

        # The enqueued background job must be removed again, the runner processing is
        # covered by test_backgroundjob.py. Filtered by job type, as creating the file also
        # enqueues a checksum job for the very same file id.
        jobs = self._jobs_for_file(model_obj.id, 'recount_file')
        self.assertEqual(len(jobs), 1)
        jobs[0].delete()

    def test_helper_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj)
        self.assertEqual(file_data, "12345678\n123456\nprincess\n")

    def test_range_request_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj, range="bytes=9-15")
        self.assertEqual(file_data, "123456\n")

    def test_suffix_range_request_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj, range="bytes=-9")
        self.assertEqual(file_data, "princess\n")

    def test_open_ended_range_request_get_file(self):
        model_obj = self.create_test_object()

        helper = Helper()
        file_data = helper.get_file(file=model_obj, range="bytes=9-")
        self.assertEqual(file_data, "123456\nprincess\n")

    def test_bulk_delete(self):
        files = [self.create_test_object(delete=False) for i in range(5)]
        File.objects.delete_many(files)

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permFileRead': True})
        self._test_acl_count(model_obj, {'permFileRead': True})

    @pytest.mark.synthetic_only
    def test_helper_rescan_global_files(self):
        model_obj1 = self.create_test_object()
        model_obj2 = self.create_test_object()

        helper = Helper()
        data = helper.rescan_global_files()
        self.assertEqual(data, {"Rescan": "Success"})

        check_obj1 = File.objects.get(fileId=model_obj1.id)
        self.assertEqual(3, check_obj1.lineCount)

    # -----------------------------------------------------------------------
    # BLAKE3 checksumming
    # -----------------------------------------------------------------------

    # BLAKE3 of the default content do_create_file() writes.
    DEFAULT_CONTENT_CHECKSUM = '8f32179d4f837b92dbe56a469a313d9a4dd0ad97566e3b86a60046f2316b6417'

    def _run_checksum_job_for(self, file_obj):
        """Runs the pending checksum job of a file and returns the refreshed File."""
        jobs = self._jobs_for_file(file_obj.id, 'checksum_file')
        self.assertEqual(len(jobs), 1)
        run_background_job_runner()
        job = BackgroundJob.objects.get(pk=jobs[0].id)
        self.assertEqual(job.status, 2, job.resultMessage)
        self.assertEqual(job.exitCode, 0, job.resultMessage)
        job.delete()
        return File.objects.get(fileId=file_obj.id)

    def test_checksum_is_null_until_the_job_ran(self):
        model_obj = self.create_test_object()

        self.assertIsNone(model_obj.checksum)
        # creating a file enqueues the computation rather than doing it in the request
        self.assertEqual(len(self._jobs_for_file(model_obj.id, 'checksum_file')), 1)

    def test_checksum_is_stored_after_the_job_ran(self):
        model_obj = self.create_test_object()

        check_obj = self._run_checksum_job_for(model_obj)
        self.assertEqual(check_obj.checksum, self.DEFAULT_CONTENT_CHECKSUM)

    def test_checksum_is_filterable(self):
        model_obj = self.create_test_object()
        self._run_checksum_job_for(model_obj)

        found = File.objects.filter(checksum=self.DEFAULT_CONTENT_CHECKSUM)
        self.assertIn(model_obj.id, [f.id for f in found])

    def test_download_advertises_the_checksum_as_repr_digest(self):
        model_obj = self.create_test_object()
        self._run_checksum_job_for(model_obj)

        connector = get_admin_connector()
        response = requests.get(
            connector._api_endpoint + f'/helper/getFile?file={model_obj.id}',
            headers=connector._headers,
        )
        self.assertEqual(response.status_code, 200, response.text)

        expected = 'blake3=:' + base64.b64encode(
            binascii.unhexlify(self.DEFAULT_CONTENT_CHECKSUM)).decode() + ':'
        self.assertEqual(response.headers.get('Repr-Digest'), expected)
        # a browser can only read the header if it is exposed to it
        self.assertIn('Repr-Digest', response.headers.get('Access-Control-Expose-Headers', ''))

    def test_download_omits_repr_digest_while_the_checksum_is_unknown(self):
        model_obj = self.create_test_object()

        connector = get_admin_connector()
        response = requests.get(
            connector._api_endpoint + f'/helper/getFile?file={model_obj.id}',
            headers=connector._headers,
        )
        self.assertEqual(response.status_code, 200, response.text)
        self.assertNotIn('Repr-Digest', response.headers)

    def test_partial_download_still_advertises_the_full_representation_digest(self):
        """Repr-Digest covers the representation, so a 206 carries the whole file's digest."""
        model_obj = self.create_test_object()
        self._run_checksum_job_for(model_obj)

        connector = get_admin_connector()
        response = requests.get(
            connector._api_endpoint + f'/helper/getFile?file={model_obj.id}',
            headers={**connector._headers, 'Range': 'bytes=0-5'},
        )
        self.assertEqual(response.status_code, 206, response.text)

        expected = 'blake3=:' + base64.b64encode(
            binascii.unhexlify(self.DEFAULT_CONTENT_CHECKSUM)).decode() + ':'
        self.assertEqual(response.headers.get('Repr-Digest'), expected)

    def test_helper_recompute_file_checksum_enqueues_a_job(self):
        model_obj = self.create_test_object()
        # drop the job the creation enqueued, so only the helper's own job remains
        for job in self._jobs_for_file(model_obj.id, 'checksum_file'):
            job.delete()

        connector = get_admin_connector()
        response = requests.post(
            connector._api_endpoint + '/helper/recomputeFileChecksum',
            headers=connector._headers,
            json={'fileId': model_obj.id},
        )
        self.assertEqual(response.status_code, 200, response.text)
        self.assertEqual(response.json()['data']['id'], model_obj.id)

        jobs = self._jobs_for_file(model_obj.id, 'checksum_file')
        self.assertEqual(len(jobs), 1)
        self.assertEqual(jobs[0].status, 0)
        jobs[0].delete()

    def test_helper_recompute_file_checksum_denies_a_user_without_access(self):
        """A user outside the file's access group cannot enqueue its checksum.

        The permission check runs before the job is enqueued, so nothing is queued. The denial
        surfaces as a 500 rather than a 403, which is how FileUtils::getFile() reports it
        throughout this codebase; recountFileLines answers identically.
        """
        model_obj = self.create_test_object()
        for job in self._jobs_for_file(model_obj.id, 'checksum_file'):
            job.delete()

        auth = create_restricted_user(self, {'permFileRead': True})
        response = requests.post(
            get_hashtopolis_uri() + '/api/v2/auth/token',
            auth=auth,
        )
        self.assertIn(response.status_code, [200, 201], response.text)
        token = response.json()['token']

        connector = get_admin_connector()
        response = requests.post(
            connector._api_endpoint + '/helper/recomputeFileChecksum',
            headers={'Authorization': f'Bearer {token}'},
            json={'fileId': model_obj.id},
        )
        self.assertNotIn(response.status_code, [200, 201], response.text)
        self.assertIn('No access to this file', response.text)
        self.assertEqual(self._jobs_for_file(model_obj.id, 'checksum_file'), [])

    @pytest.mark.synthetic_only
    def test_rescan_global_files_does_not_pile_up_duplicate_checksum_jobs(self):
        """A rescan enqueues at most one checksum job per file, however often it runs."""
        model_obj = self.create_test_object()

        helper = Helper()
        self.assertEqual(helper.rescan_global_files(), {"Rescan": "Success"})
        self.assertEqual(helper.rescan_global_files(), {"Rescan": "Success"})

        jobs = self._jobs_for_file(model_obj.id, 'checksum_file')
        self.assertEqual(len(jobs), 1)
        jobs[0].delete()
