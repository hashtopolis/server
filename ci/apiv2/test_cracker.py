import datetime
import io
import os
import threading
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
        version = f'9.9.9-{datetime.datetime.now().timestamp()}'
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
