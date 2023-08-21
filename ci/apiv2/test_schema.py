from hashtopolis import Meta
from utils import BaseTest


class AttributeTypeTest(BaseTest):
    def test_get_meta(self):
        meta = Meta()
        meta.get_meta()
