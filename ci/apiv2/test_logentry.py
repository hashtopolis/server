from hashtopolis import LogEntry
from utils import BaseTest


class LogEntryTest(BaseTest):
    model_class = LogEntry

    def test_get_one(self):
        obj = LogEntry.objects.get(pk=1)
        self.assertIsNotNone(obj)

    # TODO: Create event which generate logenties and check if logentry is created
