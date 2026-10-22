import base64
import datetime
import glob
import io
import os
import subprocess
import tempfile
import threading
import time
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path

import requests

from hashtopolis import Cracker, CrackerType, FileImport, HashType, HashtopolisError, User
from utils import (BackgroundJob, BaseTest, SEVEN_ZIP_MAGIC, create_restricted_user, do_create_agent, do_create_local_cracker,
                   get_bearer_token, get_hashtopolis_uri)
from test_backgroundjob import run_background_job_runner


CRACKERS_DIR = os.environ.get('HASHTOPOLIS_CRACKERS_PATH', '/usr/local/share/hashtopolis/crackers')
IMPORT_DIR = os.environ.get('HASHTOPOLIS_IMPORT_PATH', '/usr/local/share/hashtopolis/import')
APIV2 = get_hashtopolis_uri() + '/api/v2'


def archive_path(obj):
    """Absolute path of the locally stored archive of a cracker binary."""
    return os.path.join(CRACKERS_DIR, f'{obj.id}_{obj.filename}')


def url_copy_path(obj):
    """Absolute path of the local copy of a url-referenced cracker binary,
    composed by the server from the cracker type and the version."""
    cracker_type = CrackerType.objects.get(pk=obj.crackerBinaryTypeId)
    return os.path.join(CRACKERS_DIR, f'{obj.id}_{cracker_type.typeName}-{obj.version}.7z')


class CrackerTest(BaseTest):
    model_class = Cracker

    def create_test_object(self, *nargs, **kwargs):
        return self.create_cracker(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'binaryName')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_exception(self):
        self._test_exception(self.create_test_object, file_id='002', delete=False)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinaryType']
        self._test_expandables(model_obj, expandables)


class TestCrackerUpload(BaseTest):
    """Cracker binaries created by uploading a 7z archive instead of providing a url."""

    def _assert_local_binary(self, obj, content, version):
        # the archive filename is composed of the cracker type, the version and the extension
        cracker_type = CrackerType.objects.get(pk=obj.crackerBinaryTypeId)
        self.assertEqual(f'{cracker_type.typeName}-{version}.7z', obj.filename)
        # the download url was generated automatically and points to the download endpoint
        self.assertTrue(obj.downloadUrl.startswith('http'))
        self.assertTrue(obj.downloadUrl.endswith(f'/api/download.php/crackerBinary/{obj.id}'))
        # the archive is stored in the crackers directory
        self.assertTrue(os.path.isfile(archive_path(obj)), 'archive is not stored in the crackers directory')
        with open(archive_path(obj), 'rb') as f:
            self.assertEqual(content, f.read())

    def test_create_with_inline_source(self):
        content = SEVEN_ZIP_MAGIC + b'inline-archive-content'
        obj = self.create_local_cracker(content=content, extra_payload={'version': '7.2.7'})
        self._assert_local_binary(obj, content, '7.2.7')

    def test_create_with_import_source_chunked_upload(self):
        """Arbitrary-size archives are chunk uploaded to the import directory
        (the TUS protocol implementation) and then imported on creation."""
        content = SEVEN_ZIP_MAGIC + b'chunked-upload-archive-content'
        import_name = f'cracker-upload-{datetime.datetime.now().isoformat()}.7z'
        FileImport().do_upload(import_name, io.BytesIO(content))
        # the completed upload landed in the import directory
        self.assertTrue(os.path.isfile(os.path.join(IMPORT_DIR, import_name)))

        obj = self.create_local_cracker(source_type='import', source_data=import_name,
                                        extra_payload={'version': '7.2.7'})
        self._assert_local_binary(obj, content, '7.2.7')
        # the archive was moved out of the import directory
        self.assertFalse(os.path.isfile(os.path.join(IMPORT_DIR, import_name)))

    def test_create_with_url_source(self):
        """The server fetches the archive itself from a http url."""
        content = SEVEN_ZIP_MAGIC + b'url-archive-content'

        class Handler(BaseHTTPRequestHandler):
            def do_GET(self):
                self.send_response(200)
                self.send_header('Content-Type', 'application/octet-stream')
                self.send_header('Content-Length', str(len(content)))
                self.end_headers()
                self.wfile.write(content)

            def log_message(self, format, *args):
                pass

        server = HTTPServer(('127.0.0.1', 0), Handler)
        threading.Thread(target=server.serve_forever).start()
        try:
            obj = self.create_local_cracker(
                source_type='url',
                source_data=f'http://127.0.0.1:{server.server_address[1]}/archive.7z',
                extra_payload={'version': '7.2.7'})
            self._assert_local_binary(obj, content, '7.2.7')
        finally:
            server.shutdown()

    def test_create_invalid_archive_rejects(self):
        """An archive which is not a 7z file is rejected, the import file is
        restored and no binary is created."""
        version = f'9.9.9-{int(time.time())}'
        import_name = f'cracker-invalid-{datetime.datetime.now().isoformat()}.txt'
        FileImport().do_upload(import_name, io.BytesIO(b'not-a-7z-archive'))

        with self.assertRaises(HashtopolisError) as e:
            do_create_local_cracker(source_type='import', source_data=import_name,
                                    extra_payload={'version': version})
        self.assertEqual(400, e.exception.status_code)

        # the import file was put back to the import directory
        self.assertTrue(os.path.isfile(os.path.join(IMPORT_DIR, import_name)))
        # no binary was created
        self.assertEqual([], list(Cracker.objects.filter(version=version)))
        os.unlink(os.path.join(IMPORT_DIR, import_name))

    def test_create_with_both_sources_rejects(self):
        with self.assertRaises(HashtopolisError) as e:
            self.create_local_cracker(
                extra_payload={'downloadUrl': 'https://example.org/files/cracker.7z'},
                delete=False)
        self.assertEqual(400, e.exception.status_code)

    def test_create_with_missing_source_data_rejects(self):
        obj = Cracker(crackerBinaryTypeId=1, version='7.2.7', binaryName='cracker',
                      sourceType='inline', accessGroupId=1)
        with self.assertRaises(HashtopolisError) as e:
            obj.save()
        self.assertEqual(400, e.exception.status_code)

    def test_create_with_bogus_source_type_rejects(self):
        with self.assertRaises(HashtopolisError) as e:
            self.create_local_cracker(source_type='bogus', source_data='data', delete=False)
        self.assertEqual(400, e.exception.status_code)

    def test_create_with_invalid_base64_rejects(self):
        with self.assertRaises(HashtopolisError) as e:
            self.create_local_cracker(source_data='!!!no-base64!!!', delete=False)
        self.assertEqual(400, e.exception.status_code)

    def test_create_with_missing_import_file_rejects(self):
        with self.assertRaises(HashtopolisError) as e:
            self.create_local_cracker(source_type='import', source_data='does-not-exist.7z',
                                      delete=False)
        self.assertEqual(400, e.exception.status_code)

    def test_create_with_invalid_url_scheme_rejects(self):
        """Only http and https urls can be fetched, no local files or stream wrappers."""
        with self.assertRaises(HashtopolisError) as e:
            self.create_local_cracker(source_type='url', source_data='file:///etc/passwd',
                                      delete=False)
        self.assertEqual(400, e.exception.status_code)

    def test_patch_download_url_of_local_binary_rejects(self):
        """The download url of a locally stored binary is owned by the server."""
        obj = self.create_local_cracker()
        obj.downloadUrl = 'https://evil.example.org/cracker.7z'
        with self.assertRaises(HashtopolisError) as e:
            obj.save()
        self.assertEqual(400, e.exception.status_code)

        # the url was not changed, but other attributes can still be patched
        reloaded = Cracker.objects.get(pk=obj.id)
        self.assertTrue(reloaded.downloadUrl.endswith(f'/api/download.php/crackerBinary/{obj.id}'))
        reloaded.version = '8.0.0'
        reloaded.save()
        self.assertEqual('8.0.0', Cracker.objects.get(pk=obj.id).version)

    def test_patch_source_type_rejects(self):
        """sourceType is only valid at creation, patching it is forbidden."""
        obj = self.create_local_cracker()
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}
        r = requests.patch(
            f'{APIV2}/ui/crackers/{obj.id}',
            headers=headers,
            json={'data': {'type': 'crackerBinary', 'id': str(obj.id),
                           'attributes': {'sourceType': 'url'}}})
        self.assertEqual(403, r.status_code)

    def test_delete_removes_archive(self):
        obj = self.create_local_cracker(delete=False)
        self.assertTrue(os.path.isfile(archive_path(obj)))

        obj.delete()

        self.assertFalse(os.path.isfile(archive_path(obj)))
        # with valid authentication there is no such archive anymore
        r = requests.get(f'{get_hashtopolis_uri()}/api/download.php/crackerBinary/{obj.id}',
                         headers={'Authorization': f'Bearer {get_bearer_token()}'})
        self.assertEqual(404, r.status_code)


class TestCrackerUrlCopy(BaseTest):
    """Cracker binaries added with a download url: the server downloads a
    local copy of the archive, so it has it for later analysis. The agents
    still download the archive from the external url. If the download fails,
    the create is rejected and nothing is added."""

    def _serve_archive(self, content, status=200, content_length=None):
        """Start a local http server answering every request with the same
        response, returns the server and the url it is reachable at."""
        class Handler(BaseHTTPRequestHandler):
            def do_GET(self):
                self.send_response(status)
                if content is not None:
                    self.send_header('Content-Type', 'application/octet-stream')
                    self.send_header('Content-Length',
                                     str(len(content) if content_length is None else content_length))
                    self.end_headers()
                    self.wfile.write(content)
                else:
                    self.end_headers()

            def log_message(self, format, *args):
                pass

        server = HTTPServer(('127.0.0.1', 0), Handler)
        threading.Thread(target=server.serve_forever, daemon=True).start()
        return server, f'http://127.0.0.1:{server.server_address[1]}/archive.7z'

    def _assert_rejected(self, url, expected_message, version):
        """A create with a broken download url reports the failure on the
        create and leaves no binary and no local copy of the archive behind."""
        with self.assertRaises(HashtopolisError) as e:
            self.create_cracker(extra_payload={'downloadUrl': url, 'version': version})
        self.assertEqual(400, e.exception.status_code)
        self.assertIn(expected_message, e.exception.title)
        self.assertEqual([], list(Cracker.objects.filter(version=version)))
        self.assertEqual([], glob.glob(os.path.join(CRACKERS_DIR, f'*{version}*')))

    def test_create_with_download_url_stores_local_copy(self):
        """The server downloads a local copy of the archive from the download
        url, the url itself stays the external reference of the binary."""
        content = SEVEN_ZIP_MAGIC + b'url-copy-archive-content'
        server, url = self._serve_archive(content)
        try:
            version = 'url-copy'
            obj = self.create_cracker(extra_payload={'downloadUrl': url, 'version': version}, delete=False)

            # the binary still references the external url, it is not marked as locally stored
            self.assertEqual(url, obj.downloadUrl)
            self.assertIsNone(obj.filename)

            # the server downloaded a local copy of the archive
            path = url_copy_path(obj)
            self.assertTrue(os.path.isfile(path), 'local copy of the archive is missing')
            with open(path, 'rb') as f:
                self.assertEqual(content, f.read())

            # deleting the binary removes the local copy
            obj.delete()
            self.assertFalse(os.path.isfile(path))
        finally:
            server.shutdown()

    def test_create_with_unreachable_url_rejects(self):
        """Nothing is listening on the url, the connection is refused and
        the create is rejected."""
        self._assert_rejected('http://127.0.0.1:1/archive.7z',
                              'Failed to download the archive from the download url',
                              f'unreach-{int(time.time())}')

    def test_create_with_not_found_url_rejects(self):
        """The url answers with an http error status, the download fails and
        the create is rejected."""
        server, url = self._serve_archive(None, status=404)
        try:
            self._assert_rejected(url, 'Failed to download the archive from the download url',
                                  f'notfound-{int(time.time())}')
        finally:
            server.shutdown()

    def test_create_with_non_7z_archive_url_rejects(self):
        """The url serves something else than a 7z archive, the create is
        rejected and nothing is added."""
        server, url = self._serve_archive(b'not-a-7z-archive')
        try:
            self._assert_rejected(url, 'The archive at the download url is not a valid 7z archive!',
                                  f'non7z-{int(time.time())}')
        finally:
            server.shutdown()

    def test_create_with_truncated_download_rejects(self):
        """The url closes the connection before the announced content is
        completely sent, the incomplete download is rejected."""
        server, url = self._serve_archive(SEVEN_ZIP_MAGIC + b'only-half-of-the-archive',
                                          content_length=4096)
        try:
            self._assert_rejected(url, 'Download incomplete',
                                  f'trunc-{int(time.time())}')
        finally:
            server.shutdown()

    def test_create_with_invalid_url_scheme_rejects(self):
        """Only http and https urls are fetched by the server, no local files
        or stream wrappers."""
        self._assert_rejected('file:///etc/passwd',
                              'Only http and https download urls are supported!',
                              f'scheme-{int(time.time())}')

    def _assert_patch_rejected(self, obj, new_url, expected_message):
        """Patching the download url to a broken url reports the failure on
        the patch, the update is rolled back and the previously stored local
        copy is left untouched."""
        old_url = obj.downloadUrl
        path = url_copy_path(obj)
        with open(path, 'rb') as f:
            content_before = f.read()

        with self.assertRaises(HashtopolisError) as e:
            obj.downloadUrl = new_url
            obj.save()
        self.assertEqual(400, e.exception.status_code)
        self.assertIn(expected_message, e.exception.title)

        # the update was rolled back, the local copy is untouched
        reloaded = Cracker.objects.get(pk=obj.id)
        self.assertEqual(old_url, reloaded.downloadUrl)
        with open(path, 'rb') as f:
            self.assertEqual(content_before, f.read())
        return reloaded

    def test_patch_download_url_refreshes_local_copy(self):
        """Changing the download url re-downloads the local copy of the
        archive from the new url, the binary itself keeps referencing the
        new external url."""
        content_a = SEVEN_ZIP_MAGIC + b'archive-from-url-a'
        server_a, url_a = self._serve_archive(content_a)
        content_b = SEVEN_ZIP_MAGIC + b'archive-from-url-b'
        server_b, url_b = self._serve_archive(content_b)
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url_a, 'version': 'url-patch'},
                                      delete=False)
            path = url_copy_path(obj)
            with open(path, 'rb') as f:
                self.assertEqual(content_a, f.read())

            obj.downloadUrl = url_b
            obj.save()

            reloaded = Cracker.objects.get(pk=obj.id)
            self.assertEqual(url_b, reloaded.downloadUrl)
            self.assertIsNone(reloaded.filename)
            # the local copy was replaced with the archive from the new url
            with open(path, 'rb') as f:
                self.assertEqual(content_b, f.read())

            obj.delete()
            self.assertFalse(os.path.isfile(path))
        finally:
            server_a.shutdown()
            server_b.shutdown()

    def test_patch_download_url_to_null_rejects(self):
        """A nullable create field cannot be set to null by PATCH."""
        server, url = self._serve_archive(SEVEN_ZIP_MAGIC + b'url-patch-null')
        obj = None
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url}, delete=False)
            headers = {'Authorization': f'Bearer {get_bearer_token()}',
                       'Content-Type': 'application/json'}
            response = requests.patch(
                f'{APIV2}/ui/crackers/{obj.id}',
                headers=headers,
                json={'data': {'type': 'crackerBinary', 'id': str(obj.id),
                               'attributes': {'downloadUrl': None}}})

            self.assertEqual(400, response.status_code)
            self.assertIn('downloadUrl cannot be null', response.text)
            self.assertEqual(url, Cracker.objects.get(pk=obj.id).downloadUrl)
        finally:
            if obj is not None:
                obj.delete()
            server.shutdown()

    def test_patch_download_url_with_version_stores_new_copy(self):
        """Changing the download url together with the version stores the
        refreshed local copy under the new archive filename and removes the
        copy of the previous version."""
        content_a = SEVEN_ZIP_MAGIC + b'archive-old-version'
        server_a, url_a = self._serve_archive(content_a)
        content_b = SEVEN_ZIP_MAGIC + b'archive-new-version'
        server_b, url_b = self._serve_archive(content_b)
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url_a, 'version': 'p-old'},
                                      delete=False)
            cracker_type = CrackerType.objects.get(pk=obj.crackerBinaryTypeId)
            old_path = os.path.join(CRACKERS_DIR, f'{obj.id}_{cracker_type.typeName}-p-old.7z')
            new_path = os.path.join(CRACKERS_DIR, f'{obj.id}_{cracker_type.typeName}-p-new.7z')
            self.assertTrue(os.path.isfile(old_path))

            obj.downloadUrl = url_b
            obj.version = 'p-new'
            obj.save()

            reloaded = Cracker.objects.get(pk=obj.id)
            self.assertEqual('p-new', reloaded.version)
            self.assertEqual(url_b, reloaded.downloadUrl)
            with open(new_path, 'rb') as f:
                self.assertEqual(content_b, f.read())
            self.assertFalse(os.path.isfile(old_path))

            obj.delete()
            self.assertFalse(os.path.isfile(new_path))
        finally:
            server_a.shutdown()
            server_b.shutdown()

    def test_patch_download_url_to_unreachable_url_rejects(self):
        """Changing the download url to an url the server cannot download
        from rejects the patch, the update is rolled back."""
        server, url = self._serve_archive(SEVEN_ZIP_MAGIC + b'url-patch-unreachable')
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url}, delete=False)
            self._assert_patch_rejected(obj, 'http://127.0.0.1:1/archive.7z',
                                        'Failed to download the archive from the download url')
            obj.delete()
        finally:
            server.shutdown()

    def test_patch_download_url_to_non_7z_archive_rejects(self):
        """Changing the download url to an url which does not serve a 7z
        archive rejects the patch and rolls the update back."""
        server, url = self._serve_archive(SEVEN_ZIP_MAGIC + b'url-patch-non-7z')
        bad_server, bad_url = self._serve_archive(b'not-a-7z-archive')
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url}, delete=False)
            self._assert_patch_rejected(obj, bad_url,
                                        'The archive at the download url is not a valid 7z archive!')
            obj.delete()
        finally:
            server.shutdown()
            bad_server.shutdown()

    def test_patch_download_url_invalid_scheme_rejects(self):
        """The download url can only be changed to an http or https url."""
        server, url = self._serve_archive(SEVEN_ZIP_MAGIC + b'url-patch-scheme')
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url}, delete=False)
            self._assert_patch_rejected(obj, 'file:///etc/passwd',
                                        'Only http and https download urls are supported!')
            obj.delete()
        finally:
            server.shutdown()

    def test_patch_version_keeps_local_copy(self):
        """The local copy tracks the download url: patching other attributes
        like the version does not re-download it."""
        content = SEVEN_ZIP_MAGIC + b'url-copy-stays-on-version-patch'
        server, url = self._serve_archive(content)
        try:
            obj = self.create_cracker(extra_payload={'downloadUrl': url, 'version': 'keep-1'},
                                      delete=False)
            cracker_type = CrackerType.objects.get(pk=obj.crackerBinaryTypeId)
            path = os.path.join(CRACKERS_DIR, f'{obj.id}_{cracker_type.typeName}-keep-1.7z')

            obj.version = 'keep-2'
            obj.save()

            reloaded = Cracker.objects.get(pk=obj.id)
            self.assertEqual('keep-2', reloaded.version)
            # the local copy is untouched, still under the filename of its version
            self.assertTrue(os.path.isfile(path))
            with open(path, 'rb') as f:
                self.assertEqual(content, f.read())

            obj.delete()
            self.assertFalse(os.path.isfile(path))
        finally:
            server.shutdown()


class TestDownloadEndpoint(BaseTest):
    """The download endpoint serving the locally stored cracker binary archives."""

    def _download(self, obj, headers=None, params=None, kind='crackerBinary'):
        return requests.get(f'{get_hashtopolis_uri()}/api/download.php/{kind}/{obj.id}',
                            headers=headers, params=params)

    def test_download_without_auth_rejected(self):
        obj = self.create_local_cracker()
        self.assertEqual(401, self._download(obj).status_code)

    def test_download_with_invalid_agent_token_rejected(self):
        obj = self.create_local_cracker()
        self.assertEqual(401, self._download(obj, params={'token': 'invalid-token'}).status_code)

    def test_download_with_invalid_bearer_rejected(self):
        obj = self.create_local_cracker()
        self.assertEqual(401,
                         self._download(obj, headers={'Authorization': 'Bearer invalid.jwt.token'}).status_code)

    def test_download_with_agent_token(self):
        agent = do_create_agent()
        self.delete_after_test(agent)
        content = SEVEN_ZIP_MAGIC + b'download-endpoint-content'
        obj = self.create_local_cracker(content=content)

        r = self._download(obj, params={'token': agent.token})
        self.assertEqual(200, r.status_code)
        self.assertEqual(content, r.content)
        self.assertEqual('application/x-7z-compressed', r.headers['Content-Type'])
        self.assertEqual(f'attachment; filename="{obj.filename}"', r.headers['Content-Disposition'])

    def test_download_with_bearer_token(self):
        content = SEVEN_ZIP_MAGIC + b'download-endpoint-content'
        obj = self.create_local_cracker(content=content)

        r = self._download(obj, headers={'Authorization': f'Bearer {get_bearer_token()}'})
        self.assertEqual(200, r.status_code)
        self.assertEqual(content, r.content)

    def test_download_range_request(self):
        content = SEVEN_ZIP_MAGIC + b'download-endpoint-content'
        obj = self.create_local_cracker(content=content)

        r = self._download(obj, headers={'Authorization': f'Bearer {get_bearer_token()}',
                                         'Range': 'bytes=0-5'})
        self.assertEqual(206, r.status_code)
        self.assertEqual(content[:6], r.content)
        self.assertEqual(f'bytes 0-5/{len(content)}', r.headers['Content-Range'])

    def test_download_unknown_kind_rejected(self):
        agent = do_create_agent()
        self.delete_after_test(agent)
        obj = self.create_local_cracker()
        r = self._download(obj, params={'token': agent.token}, kind='unknown')
        self.assertEqual(404, r.status_code)


class TestCrackerHashtypes(BaseTest):
    """The n-m association between cracker binaries and hashtypes.

    Hashcat binaries start without any association, a scan of the binary
    (unpacking the archive and reading the supported hash-modes from the
    binary) populates them. Binaries of other types are never scanned, their
    supported hashtypes have to be associated manually via the relationship
    endpoints.
    """

    # canned '--example-hashes --machine-readable' report echoed by the fake
    # cracker binary
    FAKE_MODES_OUTPUT = (
        'hashcat (v9.9.9) starting in autodetect mode\n'
        '\n'
        '{"0": { "name": "MD5", "slow_hash": false, "is_salted": false, "salt_type": null },'
        ' "880001": { "name": "Fake Mode One", "slow_hash": false, "is_salted": false, "salt_type": null },'
        ' "880002": { "name": "Fake Salted Mode", "slow_hash": true, "is_salted": true, "salt_type": "generic" }}\n'
    )

    def hashtype_ids_of(self, obj):
        obj = Cracker.objects.prefetch_related('hashtypes').get(pk=obj.id)
        return sorted(ht.id for ht in obj.hashtypes_set)

    def create_unique_hashtype(self):
        stamp = int(time.time() * 1000)
        # ids above the seeded hashcat modes and the ranges of the other test files
        # (hashtype ids are user supplied, they are the hashcat mode numbers)
        return self.create_hashtype(extra_payload={'hashTypeId': 100001 + stamp % 99999,
                                                    'description': f'cracker-relation-hashtype-{stamp}'})

    def create_generic_cracker(self, **kwargs):
        """Creates a binary of a non-hashcat type, which is never scanned."""
        stamp = int(time.time() * 1000)
        cracker_type = self.create_crackertype(extra_payload={'typeName': f'generic-cracker-{stamp}'})
        return self.create_cracker(extra_payload={'crackerBinaryTypeId': cracker_type.id}, **kwargs)

    def scan_jobs_of(self, obj):
        return [job for job in BackgroundJob.objects.all()
                if job.payload.get('crackerBinaryId') == obj.id]

    def delete_scan_jobs(self, obj):
        for job in self.scan_jobs_of(obj):
            job.delete()

    def relationship_request(self, obj, method, data, model='crackers', relation='hashtypes'):
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                    'Content-Type': 'application/json'}
        return requests.request(method,
                                 f'{APIV2}/ui/{model}/{obj.id}/relationships/{relation}',
                                 headers=headers, json={'data': data})

    def test_create_hashcat_binary_enqueues_scan(self):
        """A new hashcat binary starts without associations and gets a scan queued."""
        obj = self.create_cracker()

        self.assertListEqual([], self.hashtype_ids_of(obj))
        self.assertEqual(1, len([job for job in self.scan_jobs_of(obj) if job.status == 0]),
                         'Expected exactly one pending scan job')
        self.delete_scan_jobs(obj)

    def test_create_non_hashcat_type_associates_no_hashtypes(self):
        """A binary of a non-hashcat type is created without any hashtype associations and no scan."""
        obj = self.create_generic_cracker()

        self.assertListEqual([], self.hashtype_ids_of(obj))
        self.assertListEqual([], self.scan_jobs_of(obj))

    def test_user_associates_hashtype_with_generic_cracker(self):
        """A user can create a hashtype and associate it with his generic cracker binary."""
        obj = self.create_generic_cracker()
        hashtype = self.create_unique_hashtype()

        # the generic binary starts without associations, the new hashtype is
        # not associated with it automatically either
        self.assertListEqual([], self.hashtype_ids_of(obj))
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries').get(pk=hashtype.id)
        self.assertNotIn(obj.id, sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))

        # the user associates his hashtype with the generic binary manually
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code, f'Patching failed: {r.text}')
        self.assertListEqual([hashtype.id], self.hashtype_ids_of(obj))

    def test_hashcat_hashtypes_are_not_manually_editable(self):
        """The hashtypes of a hashcat binary are determined by the scan alone."""
        obj = self.create_cracker()
        hashtype = self.create_unique_hashtype()

        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(403, r.status_code, f'Patching should be rejected: {r.text}')

        r = self.relationship_request(obj, 'POST', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(403, r.status_code, f'Adding should be rejected: {r.text}')

        r = self.relationship_request(obj, 'DELETE', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(403, r.status_code, f'Deleting should be rejected: {r.text}')

        self.assertListEqual([], self.hashtype_ids_of(obj))
        self.delete_scan_jobs(obj)

    def test_scan_populates_associations_and_creates_hashtypes(self):
        """The scan of a hashcat binary populates its associations and creates missing hashtypes."""
        # fake cracker archive: a shell script 'cracker.bin' which echoes a
        # canned '--example-hashes --machine-readable' report, packed with 7z
        with tempfile.TemporaryDirectory() as tmpdir:
            binary_dir = Path(tmpdir) / 'fake-hashcat'
            binary_dir.mkdir()
            (binary_dir / 'cracker.bin').write_text(
                '#!/bin/sh\n'
                "if [ \"$1\" = '--example-hashes' ] && [ \"$2\" = '--machine-readable' ]; then\n"
                f"cat <<'HTP_EOF'\n{self.FAKE_MODES_OUTPUT}HTP_EOF\n"
                'exit 0\n'
                'fi\n'
                'exit 1\n'
            )
            (binary_dir / 'cracker.bin').chmod(0o755)
            archive = Path(tmpdir) / 'fake-hashcat.7z'
            result = subprocess.run(['7z', 'a', str(archive), str(binary_dir)],
                                     capture_output=True, text=True)
            self.assertEqual(0, result.returncode, f'Packing the fake archive failed: {result.stderr}')
            source_data = base64.b64encode(archive.read_bytes()).decode()

        obj = self.create_local_cracker(source_data=source_data)
        try:
            # until the scan ran, the binary has no associations, but the scan is queued
            self.assertListEqual([], self.hashtype_ids_of(obj))
            self.assertEqual(1, len([job for job in self.scan_jobs_of(obj) if job.status == 0]))

            run_background_job_runner()

            # mode 0 exists already, the two fake modes were created with the
            # description and flags of the mode report
            self.assertListEqual([0, 880001, 880002], self.hashtype_ids_of(obj))
            created = HashType.objects.get(pk=880001)
            self.assertEqual('Fake Mode One', created.description)
            self.assertFalse(created.isSalted)
            self.assertFalse(created.isSlowHash)
            salted = HashType.objects.get(pk=880002)
            self.assertEqual('Fake Salted Mode', salted.description)
            # only a generic salt counts as salted, the slow-hash flag is
            # taken over from the report
            self.assertTrue(salted.isSalted)
            self.assertTrue(salted.isSlowHash)
        finally:
            # clean up, the scan jobs and hashtypes are not covered by the
            # regular test object teardown
            self.delete_scan_jobs(obj)
            for hashtype_id in (880001, 880002):
                r = requests.delete(f'{APIV2}/ui/hashtypes/{hashtype_id}',
                                    headers={'Authorization': f'Bearer {get_bearer_token()}'})
                self.assertIn(r.status_code, [204, 404], f'Deleting hashtype {hashtype_id} failed: {r.text}')

    def test_patch_hashtypes_replaces_set(self):
        """Patching the relationship replaces the associated hashtypes with the given list."""
        obj = self.create_generic_cracker()
        hashtype1 = self.create_unique_hashtype()
        hashtype2 = self.create_unique_hashtype()

        work_obj = Cracker.objects.prefetch_related('hashtypes').get(pk=obj.id)
        work_obj.hashtypes_set = [hashtype1, hashtype2]
        work_obj.save()

        self.assertListEqual(sorted([hashtype1.id, hashtype2.id]), self.hashtype_ids_of(obj))

    def test_add_hashtype_with_relationship_post(self):
        """Single hashtypes can be added to the association of a binary."""
        obj = self.create_generic_cracker()
        hashtype1 = self.create_unique_hashtype()
        hashtype2 = self.create_unique_hashtype()

        # replace the set of the new binary with a single hashtype
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype1.id}])
        self.assertEqual(204, r.status_code)
        self.assertListEqual([hashtype1.id], self.hashtype_ids_of(obj))

        # add the second hashtype to the association
        r = self.relationship_request(obj, 'POST', [{'type': 'hashType', 'id': hashtype2.id}])
        self.assertIn(r.status_code, [201, 204], f'Adding failed: {r.text}')
        self.assertListEqual(sorted([hashtype1.id, hashtype2.id]), self.hashtype_ids_of(obj))

        # adding the same hashtype again results in a conflict
        r = self.relationship_request(obj, 'POST', [{'type': 'hashType', 'id': hashtype2.id}])
        self.assertEqual(409, r.status_code)

    def test_concurrent_add_creates_single_association(self):
        """Concurrent requests cannot create the same association twice.

        The unique key on (crackerBinaryId, hashTypeId) guarantees that of two
        concurrent requests adding the same hashtype only one can create the
        association; the other one is answered with a conflict instead of
        creating a duplicate.
        """
        # a generic (non-hashcat) binary starts without any association and
        # the fresh hashtype is only auto-associated with hashcat binaries,
        # so both requests start from a non-existing association
        stamp = int(time.time() * 1000)
        cracker_type = self.create_crackertype(extra_payload={'typeName': f'generic-cracker-{stamp}'})
        obj = self.create_cracker(extra_payload={'crackerBinaryTypeId': cracker_type.id})
        hashtype = self.create_unique_hashtype()
        self.assertListEqual([], self.hashtype_ids_of(obj))

        barrier = threading.Barrier(2)
        results = []

        def add():
            barrier.wait()
            results.append(self.relationship_request(obj, 'POST', [{'type': 'hashType', 'id': hashtype.id}]))

        threads = [threading.Thread(target=add) for _ in range(2)]
        for thread in threads:
            thread.start()
        for thread in threads:
            thread.join()

        statuses = sorted(r.status_code for r in results)
        self.assertEqual([201, 409], statuses,
                         f'Concurrent adds should give exactly one success and one conflict: '
                         f'{[(r.status_code, r.text) for r in results]}')
        self.assertListEqual([hashtype.id], self.hashtype_ids_of(obj))

    def test_remove_hashtype_with_relationship_delete(self):
        """Single hashtypes can be removed from the association of a binary."""
        obj = self.create_generic_cracker()
        hashtype = self.create_unique_hashtype()

        # replace the set of the new binary with two hashtypes
        seeded_hashtype = HashType.objects.all()[0]
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': seeded_hashtype.id},
                                                     {'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code)
        self.assertListEqual(sorted([seeded_hashtype.id, hashtype.id]), self.hashtype_ids_of(obj))

        # remove one of them
        r = self.relationship_request(obj, 'DELETE', [{'type': 'hashType', 'id': seeded_hashtype.id}])
        self.assertIn(r.status_code, [201, 204], f'Removal failed: {r.text}')
        self.assertListEqual([hashtype.id], self.hashtype_ids_of(obj))

    def test_association_is_readonly_from_hashtype_side(self):
        """The association can only be edited from the cracker binary side."""
        hashtype = self.create_unique_hashtype()
        obj = self.create_generic_cracker()

        # associate the hashtype with the binary from the cracker binary side
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code, f'Patching failed: {r.text}')

        # the hashtype is visible from the hashtype side
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries').get(pk=hashtype.id)
        self.assertIn(obj.id, sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))

        # but it cannot be changed from there
        r = self.relationship_request(hashtype, 'PATCH',
                                      [{'type': 'crackerBinary', 'id': obj.id}],
                                      model='hashtypes', relation='crackerBinaries')
        self.assertEqual(400, r.status_code, f'Patching should be rejected: {r.text}')

        r = self.relationship_request(hashtype, 'POST',
                                      [{'type': 'crackerBinary', 'id': obj.id}],
                                      model='hashtypes', relation='crackerBinaries')
        self.assertEqual(400, r.status_code, f'Adding should be rejected: {r.text}')

        r = self.relationship_request(hashtype, 'DELETE',
                                      [{'type': 'crackerBinary', 'id': obj.id}],
                                      model='hashtypes', relation='crackerBinaries')
        self.assertEqual(400, r.status_code, f'Deleting should be rejected: {r.text}')

    def test_relationship_routes_enforce_binary_access_group(self):
        """The hashtype relationship routes enforce the binary's access group.

        A user with global cracker permissions who is not a member of the
        binary's access group must not be able to read, replace, add or remove
        its hashtype associations, even though hashtypes themselves are global
        and not isolated by access groups.
        """
        # a generic (non-hashcat) binary, its hashtypes are manually editable
        obj = self.create_generic_cracker()
        hashtype = self.create_unique_hashtype()
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code, f'Patching failed: {r.text}')
        self.assertListEqual([hashtype.id], self.hashtype_ids_of(obj))

        username, password = create_restricted_user(self, {
            'permCrackerBinaryRead': True,
            'permCrackerBinaryUpdate': True,
            'permCrackerBinaryDelete': True,
            'permHashTypeRead': True,
        })
        r = requests.post(f'{APIV2}/auth/token', auth=(username, password))
        self.assertEqual(201, r.status_code, f'Could not get a token: {r.text}')
        # only requests with a body may announce a json content type, the body
        # parser rejects bodyless requests which claim to carry json
        auth_headers = {'Authorization': f"Bearer {r.json()['token']}"}
        headers = {**auth_headers, 'Content-Type': 'application/json'}

        related_url = f'{APIV2}/ui/crackers/{obj.id}/hashtypes'
        relationship_url = f'{APIV2}/ui/crackers/{obj.id}/relationships/hashtypes'
        data = [{'type': 'hashType', 'id': hashtype.id}]

        # reading the association is denied
        r = requests.get(related_url, headers=auth_headers)
        self.assertEqual(403, r.status_code, f'Related resource listing should be denied: {r.text}')
        r = requests.get(relationship_url, headers=auth_headers)
        self.assertEqual(403, r.status_code, f'Relationship link should be denied: {r.text}')

        # every modifying request is denied
        r = requests.patch(relationship_url, headers=headers, json={'data': []})
        self.assertEqual(403, r.status_code, f'Replacing the association should be denied: {r.text}')
        r = requests.post(relationship_url, headers=headers, json={'data': data})
        self.assertEqual(403, r.status_code, f'Adding to the association should be denied: {r.text}')
        r = requests.delete(relationship_url, headers=headers, json={'data': data})
        self.assertEqual(403, r.status_code, f'Removing from the association should be denied: {r.text}')

        # the association is unchanged
        self.assertListEqual([hashtype.id], self.hashtype_ids_of(obj))

        # an out-of-group hashcat binary is rejected with the generic access
        # error, not with the hashcat-specific read-only message: otherwise
        # the modifying routes would disclose the binary type of a binary
        # the user has no access to
        hashcat_obj = self.create_cracker()
        self.delete_scan_jobs(hashcat_obj)
        hashcat_relationship_url = f'{APIV2}/ui/crackers/{hashcat_obj.id}/relationships/hashtypes'
        for method, payload in (
            ('patch', []),
            ('post', [{'type': 'hashType', 'id': hashtype.id}]),
            ('delete', [{'type': 'hashType', 'id': hashtype.id}]),
        ):
            r = requests.request(method, hashcat_relationship_url, headers=headers, json={'data': payload})
            self.assertEqual(403, r.status_code, f'{method} should be denied: {r.text}')
            self.assertNotIn('hashcat', r.text,
                              'The rejection must not disclose the hashcat type of the binary!')

        # a member of the binary's access group can read and edit the association
        user = User.objects.get(name=username)
        admin_headers = {'Authorization': f'Bearer {get_bearer_token()}',
                         'Content-Type': 'application/json'}
        r = requests.post(f'{APIV2}/ui/accessgroups/1/relationships/userMembers',
                          headers=admin_headers,
                          json={'data': [{'type': 'user', 'id': user.id}]})
        self.assertEqual(201, r.status_code, f'Could not add the user to the access group: {r.text}')

        r = requests.get(related_url, headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Related resource listing should be allowed: {r.text}')
        r = requests.get(relationship_url, headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Relationship link should be allowed: {r.text}')
        r = requests.delete(relationship_url, headers=headers, json={'data': data})
        self.assertIn(r.status_code, [201, 204], f'Removing should be allowed: {r.text}')
        self.assertListEqual([], self.hashtype_ids_of(obj))

    def test_reverse_relation_filters_binaries_by_access_group(self):
        """Expansions and relationship links of a hashtype only show accessible binaries.

        Hashtypes are global, but the binaries associated with them are isolated
        by access group: a user without access to a binary's access group must
        not see the binary when the hashtype is expanded, when its relationship
        link is read or when the related binaries are listed.
        """
        # a generic (non-hashcat) binary, its hashtypes are manually editable
        obj = self.create_generic_cracker()
        hashtype = self.create_unique_hashtype()
        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code, f'Patching failed: {r.text}')

        # the admin is a member of the default access group and sees the binary
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries').get(pk=hashtype.id)
        self.assertIn(obj.id, sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))

        username, password = create_restricted_user(self, {
            'permCrackerBinaryRead': True,
            'permHashTypeRead': True,
        })
        r = requests.post(f'{APIV2}/auth/token', auth=(username, password))
        self.assertEqual(201, r.status_code, f'Could not get a token: {r.text}')
        auth_headers = {'Authorization': f"Bearer {r.json()['token']}"}

        # expanding the hashtype does not include the inaccessible binary
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries') \
            .authenticate((username, password)).get(pk=hashtype.id)
        self.assertEqual([], [cracker.id for cracker in hashtype_obj.crackerBinaries_set])

        # the relationship link and the related resource listing are empty as well
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}/relationships/crackerBinaries',
                         headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Relationship link should be readable: {r.text}')
        self.assertEqual([], r.json()['data'])
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}/crackerBinaries',
                         headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Related listing should be readable: {r.text}')
        self.assertEqual([], r.json()['data'])

        # a member of the binary's access group sees it in all three views
        user = User.objects.get(name=username)
        admin_headers = {'Authorization': f'Bearer {get_bearer_token()}',
                         'Content-Type': 'application/json'}
        r = requests.post(f'{APIV2}/ui/accessgroups/1/relationships/userMembers',
                          headers=admin_headers,
                          json={'data': [{'type': 'user', 'id': user.id}]})
        self.assertEqual(201, r.status_code, f'Could not add the user to the access group: {r.text}')

        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries') \
            .authenticate((username, password)).get(pk=hashtype.id)
        self.assertIn(obj.id, [cracker.id for cracker in hashtype_obj.crackerBinaries_set])

        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}/relationships/crackerBinaries',
                         headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Relationship link should be readable: {r.text}')
        self.assertIn(obj.id, [int(item['id']) for item in r.json()['data']])
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}/crackerBinaries',
                         headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Related listing should be readable: {r.text}')
        self.assertIn(obj.id, [int(item['id']) for item in r.json()['data']])

    def test_delete_binary_removes_associations(self):
        """Deleting a binary also removes its hashtype associations."""
        obj = self.create_generic_cracker(delete=False)
        hashtype = self.create_unique_hashtype()

        r = self.relationship_request(obj, 'PATCH', [{'type': 'hashType', 'id': hashtype.id}])
        self.assertEqual(204, r.status_code, f'Patching failed: {r.text}')
        self.assertIn(hashtype.id, self.hashtype_ids_of(obj))
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries').get(pk=hashtype.id)
        self.assertIn(obj.id, sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))

        obj.delete()

        # the deleted binary is not associated with the hashtype anymore
        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries').get(pk=hashtype.id)
        self.assertNotIn(obj.id, sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))
