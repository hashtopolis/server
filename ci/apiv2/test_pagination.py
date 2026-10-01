from hashtopolis import AccessGroup, Hash, Hashlist, HashType, User
from utils import BaseTest, create_apitoken_raw, create_restricted_user, request_with_api_token
import json
import requests
from base64 import b64encode


class PaginationTest(BaseTest):
    model_class = HashType

    def pagination_test_helper(self, after, size):
        after_dict = {"primary": {"hashTypeId": after}}
        after_param = b64encode(json.dumps(after_dict).encode('utf-8')).decode('utf-8')
        objs = HashType.objects.paginate(size=size, after=after_param).get_pagination()
        all_objs = list(HashType.objects.all())
        index = None
        for idx, obj in enumerate(all_objs):
            if obj.id > after:
                index = idx
                break

        self.assertIsNotNone(index)
        self.assertEqual(objs, all_objs[index:index+size])
        pass

    def pagination_with_ordering_helper(self):
        hashlist1 = self.create_hashlist()
        hashlist2 = self.create_hashlist()

        after_dict = {"primary": {"cracked": 0}, "secondary": {"hashlistId": hashlist1.id}}
        after_param = b64encode(json.dumps(after_dict).encode('utf-8')).decode('utf-8')
        
        objs = Hashlist.objects.paginate(size=1, after=after_param).filter(format__nin=3).order_by('cracked').get_pagination()
        self.assertEqual(objs[0].id, hashlist2.id)

        pass

    def test_get_page(self):
        # TODO test can be randomised to get more coverage
        self.pagination_test_helper(1200, 25)
        self.pagination_test_helper(2500, 50)
        self.pagination_test_helper(20, 10)

        self.pagination_with_ordering_helper()

    def test_pagination_cursor_respects_access_groups(self):
        # A page cursor on a non-unique sort value must not return rows the user has no access to. The tie-break
        # branch of the pagination condition (same sort value, higher id) used to bypass the ACL filter.
        auth = create_restricted_user(self, {'permJwtApiKeyCreate': True, 'permHashRead': True})
        token = create_apitoken_raw(self, auth, ['permHashRead'])

        # the admin needs to be a member as well to be able to create a hashlist in the group
        group = self.create_accessgroup()
        members = [{"type": "User", "id": 1}, {"type": "User", "id": User.objects.get(name=auth[0]).id}]
        connector = AccessGroup.objects.get_conn()
        connector.authenticate()
        r = requests.post(connector._api_endpoint + f'/ui/accessgroups/{group.id}/relationships/userMembers',
                          headers={**connector._headers, 'Content-Type': 'application/json'},
                          data=json.dumps({"data": members}))
        self.assertEqual(r.status_code, 201, r.text)

        # identical hash in a visible hashlist and (created afterwards, so with a higher hashId) in a hidden one
        source_data = b64encode(b'6a204bd89f3c8348afd5c77c717a097a\n').decode('utf-8')
        visible = self.create_hashlist(extra_payload={'accessGroupId': group.id, 'sourceData': source_data})
        hidden = self.create_hashlist(extra_payload={'accessGroupId': 1, 'sourceData': source_data})
        visible_hash = Hash.objects.filter(hashlistId=visible.id)[0]
        hidden_hash = Hash.objects.filter(hashlistId=hidden.id)[0]
        self.assertLess(visible_hash.id, hidden_hash.id)

        cursor = {"primary": {"hash": visible_hash.hash}, "secondary": {"hashId": visible_hash.id}}
        after = b64encode(json.dumps(cursor).encode('utf-8')).decode('utf-8')
        response = request_with_api_token(token.token, f'/ui/hashes?sort=hash&page[size]=100&page[after]={after}')
        self.assertEqual(response.status_code, 200, response.text)
        returned_ids = [item['id'] for item in response.json()['data']]
        self.assertNotIn(hidden_hash.id, returned_ids)
