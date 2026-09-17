from hashtopolis import LogEntry
from utils import BaseTest


class LogEntryTest(BaseTest):
    model_class = LogEntry

    def test_get_one(self):
        # the id of the oldest entry cannot be assumed, the server deletes the
        # oldest log entries once the configured limit is exceeded
        entries = LogEntry.objects.all()
        if len(entries) == 0:
            self.skipTest('no log entries exist yet on this database')
        obj = LogEntry.objects.get(pk=entries[0].id)
        self.assertIsNotNone(obj)
        self.assertEqual(entries[0].id, obj.id)

    # TODO: Create event which generate logenties and check if logentry is created
