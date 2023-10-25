#!/usr/bin/env python
"""
Generate dummy data for development/debugging purposes
"""
from utils import do_create_hashlist


def generate_dummy_data():
    for _ in range(1000):
        # TODO: Generate unique hashlists
        do_create_hashlist()


# TODO: Generate different objects like users/tasks/crackerbinaries/etc
if __name__ == '__main__':
    # TODO: Use seed to generate an predictable 'random' test dataset
    generate_dummy_data()
