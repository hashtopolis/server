from hashtopolis import Chunk, Helper

from utils import BaseTest


class ChunkTest(BaseTest):
    model_class = Chunk

    def create_test_object(self, *nargs, **kwargs):
        retval = self.create_agent_with_task(*nargs, **kwargs)
        chunks = Chunk.objects.filter(taskId=retval['task'].id)
        self.assertGreaterEqual(len(chunks), 1)
        return chunks[0]

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['task']
        self._test_expandables(model_obj, expandables)

    def test_helper_abortChunk(self):
        chunk = self.create_test_object()
        helper = Helper()
        helper.abortChunk(chunk)

    def test_helper_resetChunk(self):
        chunk = self.create_test_object()
        helper = Helper()
        helper.abortChunk(chunk)
