import time

import requests

from hashtopolis import HashType, User
from utils import (BaseTest, create_restricted_user, get_bearer_token, get_hashtopolis_uri)


APIV2 = get_hashtopolis_uri() + '/api/v2'


class HashTypeTest(BaseTest):
    model_class = HashType

    def create_test_object(self, *nargs, **kwargs):
        return self.create_hashtype(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'description')

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinaries']
        self._test_expandables(model_obj, expandables)

    def test_acl(self):
        model_obj = self.create_test_object()
        self._test_acl_list(model_obj, {'permHashTypeRead': True})
        self._test_acl_count(model_obj, {'permHashTypeRead': True})


class TestHashtypeVisibility(BaseTest):
    """Hashtypes are only visible through the binaries of the caller's access groups.

    A hashtype is part of the results iff at least one cracker binary of one of
    the caller's access groups is associated with it. Binaries without any
    association do not make hashtypes visible. Administrators bypass the check
    to manage the global hashtype catalog.
    """

    def create_unique_hashtype(self, **kwargs):
        stamp = int(time.time() * 1000)
        kwargs.setdefault('extra_payload', {})
        kwargs['extra_payload'].setdefault(
            'hashTypeId', 200001 + stamp % 99999)
        kwargs['extra_payload'].setdefault(
            'description', f'hashtype-visibility-{stamp}')
        return self.create_hashtype(**kwargs)

    def create_generic_cracker(self, access_group_id=1):
        """Creates a binary of a non-hashcat type, which is never scanned."""
        stamp = int(time.time() * 1000)
        cracker_type = self.create_crackertype(
            extra_payload={'typeName': f'ht-vis-cracker-{stamp}'})
        return self.create_cracker(extra_payload={
            'crackerBinaryTypeId': cracker_type.id,
            'accessGroupId': access_group_id,
        })

    def associate(self, binary, hashtype):
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}
        return requests.patch(
            f'{APIV2}/ui/crackers/{binary.id}/relationships/hashtypes',
            headers=headers, json={'data': [{'type': 'hashType', 'id': hashtype.id}]})

    def add_group_membership(self, group_id, user_id):
        headers = {'Authorization': f'Bearer {get_bearer_token()}',
                   'Content-Type': 'application/json'}
        r = requests.post(f'{APIV2}/ui/accessgroups/{group_id}/relationships/userMembers',
                          headers=headers, json={'data': [{'type': 'user', 'id': user_id}]})
        assert r.status_code == 201, f'Could not add the user to the access group: {r.text}'

    def add_to_default_access_group(self, username):
        self.add_group_membership(1, User.objects.get(name=username).id)

    def restricted_headers(self, username, password):
        r = requests.post(f'{APIV2}/auth/token', auth=(username, password))
        assert r.status_code == 201, f'Could not get a token: {r.text}'
        return {'Authorization': f"Bearer {r.json()['token']}"}

    def test_visible_only_through_accessible_binaries(self):
        """A hashtype of a foreign access group's binary is invisible and responds not found.

        The hashtype becomes visible as soon as a binary of the caller's access
        group is associated with it. Until then, it is neither in the list nor
        readable by id, so its existence is not disclosed.
        """
        # the hashtype is not associated with any binary yet, but the admin
        # manages the global catalog and sees it regardless
        hashtype = self.create_unique_hashtype()
        self.assertEqual(1, len(list(HashType.objects.filter(id=hashtype.id))),
                         'Admin should see the unassociated hashtype')

        foreign_group = self.create_accessgroup()
        # the admin needs to be a member of the group to create a binary in it
        self.add_group_membership(foreign_group.id, 1)
        foreign_binary = self.create_generic_cracker(access_group_id=foreign_group.id)
        r = self.associate(foreign_binary, hashtype)
        self.assertEqual(204, r.status_code, f'Associating failed: {r.text}')

        # a user without access to any of the hashtype's binaries does not see
        # it at all, direct reads respond with a not found error
        username, password = create_restricted_user(self, {
            'permHashTypeRead': True,
            'permCrackerBinaryRead': True,
        })
        auth = (username, password)
        headers = self.restricted_headers(username, password)

        self.assertEqual(0, len(list(HashType.objects.filter(id=hashtype.id).authenticate(auth))),
                         'User without accessible binaries should not see the hashtype')
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}', headers=headers)
        self.assertEqual(404, r.status_code, 'Direct read should respond with not found')

        # a binary of the user's access group makes the hashtype visible
        own_binary = self.create_generic_cracker(access_group_id=1)
        r = self.associate(own_binary, hashtype)
        self.assertEqual(204, r.status_code, f'Associating failed: {r.text}')
        self.add_to_default_access_group(username)

        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries') \
            .authenticate(auth).get(pk=hashtype.id)
        self.assertEqual([own_binary.id],
                         sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set),
                         'Expansion should only show the accessible binary')
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}', headers=headers)
        self.assertEqual(200, r.status_code, f'Direct read should be allowed: {r.text}')

    def test_create_associate_two_step_flow(self):
        """A user creates a new hashtype and associates it with their binary in two steps.

        Creation itself is only permission based, the created hashtype is
        returned although it is not visible yet. It stays unreadable until it
        is associated with a binary of the creator's access group through the
        binary's relationship route, which checks the hashtype only for
        existence.
        """
        binary = self.create_generic_cracker(access_group_id=1)

        username, password = create_restricted_user(self, {
            'permHashTypeCreate': True,
            'permHashTypeRead': True,
            'permCrackerBinaryRead': True,
            'permCrackerBinaryUpdate': True,
        })
        self.add_to_default_access_group(username)
        auth = (username, password)
        # only requests with a body may announce a json content type, the body
        # parser rejects bodyless requests which claim to carry json
        auth_headers = self.restricted_headers(username, password)
        headers = {**auth_headers, 'Content-Type': 'application/json'}

        # the user creates the hashtype and gets it returned
        stamp = int(time.time() * 1000)
        hashtype = HashType(hashTypeId=300001 + stamp % 99999,
                            description=f'two-step-flow-hashtype-{stamp}',
                            isSalted=False, isSlowHash=False)
        HashType.objects.get_conn().create(hashtype, auth=auth)
        self.delete_after_test(hashtype)

        # until the association exists, the hashtype is invisible to its creator
        self.assertEqual(0, len(list(HashType.objects.filter(id=hashtype.id).authenticate(auth))),
                         'Unassociated hashtype should not be visible to its creator')
        r = requests.get(f'{APIV2}/ui/hashtypes/{hashtype.id}', headers=auth_headers)
        self.assertEqual(404, r.status_code, 'Direct read should respond with not found')

        # the association with the user's own binary makes it visible
        r = requests.patch(f'{APIV2}/ui/crackers/{binary.id}/relationships/hashtypes',
                           headers=headers,
                           json={'data': [{'type': 'hashType', 'id': hashtype.id}]})
        self.assertEqual(204, r.status_code, f'Associating failed: {r.text}')

        hashtype_obj = HashType.objects.prefetch_related('crackerBinaries') \
            .authenticate(auth).get(pk=hashtype.id)
        self.assertEqual([binary.id],
                         sorted(cracker.id for cracker in hashtype_obj.crackerBinaries_set))
        r = requests.get(f'{APIV2}/ui/crackers/{binary.id}/hashtypes', headers=auth_headers)
        self.assertEqual(200, r.status_code, f'Related listing should be readable: {r.text}')
        self.assertIn(hashtype.id, [int(item['id']) for item in r.json()['data']])
