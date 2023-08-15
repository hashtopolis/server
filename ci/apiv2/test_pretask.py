import datetime
import json
from pathlib import Path

from hashtopolis import Pretask
from test_file import do_create_file
from utils import BaseTest


def do_create_pretask(file_id='001', files=[]):
    p = Path(__file__).parent.joinpath(f'create_pretask_{file_id}.json')
    payload = json.loads(p.read_text('UTF-8'))
    payload['files'] = [file.id for file in files]
    pretask = Pretask(**payload)
    obj = pretask.save()
    return obj


class PretaskTest(BaseTest):
    def test_create_pretask(self):
        pretask = do_create_pretask()
        self.delete_after_test(pretask)

        obj = Pretask.objects.get(pretaskId=pretask.id)
        self.assertEqual(obj.taskName, pretask.taskName)

    def test_patch_pretask(self):
        pretask = do_create_pretask()
        self.delete_after_test(pretask)

        stamp = datetime.datetime.now().isoformat()
        obj_name = f'Dummy Pretask - {stamp}'
        pretask.taskName = obj_name
        pretask.save()

        obj = Pretask.objects.get(pretaskId=pretask.id)
        self.assertEqual(obj.taskName, obj_name)

    def test_delete_pretask(self):
        pretask = do_create_pretask()

        pretask.delete()
        objs = Pretask.objects.filter(pretaskId=id)
        self.assertEqual(objs, [])

    def test_expand_pretask_files(self):
        file = do_create_file()
        self.delete_after_test(file)
        pretask = do_create_pretask(files=[file])
        self.delete_after_test(pretask)

        objects = Pretask.objects.filter(pretaskId=pretask.id, expand='pretaskFiles')
        print(vars(objects[0]))
        self.assertEqual(objects[0].pretaskFiles_set[0].filename, file.filename)