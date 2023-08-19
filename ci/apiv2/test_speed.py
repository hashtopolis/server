from hashtopolis import HashtopolisResponseError, Speed
from utils import BaseTest
from utils import create_dummy_agent, do_create_hashlist, do_create_task


class SpeedTest(BaseTest):
    model_class = Speed

    def create_test_object(self, *nargs, **kwargs):
        dummy_agent, agent = create_dummy_agent()
        self.delete_after_test(agent)

        hashlist = do_create_hashlist()
        self.delete_after_test(hashlist)

        task = do_create_task(hashlist=hashlist)
        self.delete_after_test(task)

        # TODO: Assign agent to task
        dummy_agent.get_task()
        self.assertEqual(dummy_agent.task['hashlistId'], hashlist.id,
                         "Hashlist created is not being working on by agent!")
        dummy_agent.get_hashlist()

        dummy_agent.get_chunk()
        dummy_agent.send_keyspace()
        dummy_agent.get_chunk()
        dummy_agent.send_benchmark()
        dummy_agent.get_chunk()
        dummy_agent.send_process(progress=50)

        return Speed.objects.filter(taskId=task.id)[0]

    def test_create(self):
        model_obj = self.create_test_object()
        print(vars(model_obj))
        self._test_create(model_obj)

    def test_patch(self):
        # Patching should not be possible via API
        model_obj = self.create_test_object()
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_patch(model_obj, 'speed', 1234)
        self.assertEqual(e.exception.status_code, 500)

    def test_delete(self):
        # Delete should not be possible via API
        model_obj = self.create_test_object(delete=False)
        with self.assertRaises(HashtopolisResponseError) as e:
            self._test_delete(model_obj)
        self.assertEqual(e.exception.status_code, 500)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['agent', 'task']
        self._test_expandables(model_obj, expandables)
