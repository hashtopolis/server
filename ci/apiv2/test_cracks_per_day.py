from datetime import date

import requests

from hashtopolis import Hashlist, Helper
from utils import BaseTest, create_restricted_user


class CracksPerDayTest(BaseTest):
    model_class = Hashlist

    def test_returns_dict(self):
        helper = Helper()
        result = helper.get_cracks_per_day()
        self.assertIsInstance(result, dict)

    def test_keys_are_current_year(self):
        hashlist = self.create_hashlist()
        helper = Helper()
        helper.import_cracked_hashes(hashlist, 'paste', 'cc03e747a6afbbcbf8be7668acfebee5:test123', ':', 0)

        result = helper.get_cracks_per_day()
        current_year = str(date.today().year)
        for key in result.keys():
            self.assertRegex(key, r'^\d{4}-\d{2}-\d{2}$', f"Key '{key}' is not in YYYY-MM-DD format")
            self.assertTrue(key.startswith(current_year), f"Key '{key}' is not in the current year")

    def test_today_count_after_import(self):
        hashlist = self.create_hashlist()
        helper = Helper()
        helper.import_cracked_hashes(hashlist, 'paste', 'cc03e747a6afbbcbf8be7668acfebee5:test123', ':', 0)

        result = helper.get_cracks_per_day()
        today = date.today().strftime('%Y-%m-%d')
        self.assertIn(today, result, f"Today's date '{today}' not found in result")
        self.assertGreaterEqual(result[today], 1)

    def test_count_increases_with_more_cracks(self):
        hashlist1 = self.create_hashlist()
        hashlist2 = self.create_hashlist()
        helper = Helper()

        result_before = helper.get_cracks_per_day()
        today = date.today().strftime('%Y-%m-%d')
        count_before = result_before.get(today, 0)

        helper.import_cracked_hashes(hashlist1, 'paste', 'cc03e747a6afbbcbf8be7668acfebee5:test123', ':', 0)
        helper.import_cracked_hashes(hashlist2, 'paste', 'cc03e747a6afbbcbf8be7668acfebee5:test123', ':', 0)

        result_after = helper.get_cracks_per_day()
        count_after = result_after.get(today, 0)

        self.assertEqual(count_after, count_before + 2)

    def test_acl_cracks_are_scoped_to_access_groups(self):
        """A user without access groups must not be told about cracks of other groups."""
        hashlist = self.create_hashlist()
        helper = Helper()
        helper.import_cracked_hashes(hashlist, 'paste', 'cc03e747a6afbbcbf8be7668acfebee5:test123', ':', 0)

        today = date.today().strftime('%Y-%m-%d')
        self.assertGreaterEqual(helper.get_cracks_per_day().get(today, 0), 1,
                                "Expected a crack today for the ACL test")

        auth = create_restricted_user(self, {'permHashlistRead': True, 'permHashRead': True})

        # get_cracks_per_day() always authenticates as the config user, so request it directly
        restricted = Helper()
        restricted.authenticate(auth=auth)
        response = requests.get(restricted._api_endpoint + restricted._model_uri + 'getCracksPerDay',
                                headers=restricted._headers)
        self.assertEqual(response.status_code, 200, response.text)

        self.assertEqual(response.json()['data'], {},
                         "Restricted user should not see cracks outside their access groups")
