import datetime
import glob
import io
import os
import threading
import time
from http.server import BaseHTTPRequestHandler, HTTPServer

import requests

from hashtopolis import Cracker, CrackerType, FileImport, HashtopolisError
from utils import (BaseTest, SEVEN_ZIP_MAGIC, do_create_agent, do_create_local_cracker,
                   get_bearer_token, get_hashtopolis_uri)


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
        # the archive filename is composed from the cracker type, the version and the extension
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
