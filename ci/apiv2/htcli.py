#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Hashtopolis Test and Development CLI

Tools & Tricks for development of hashtopolis

PoC implementation of CLI utility of hashtopolis
"""

__version__ = '0.0.1'

import logging
import inspect
import click
import click_log
import json

import hashtopolis
from hashtopolis import AccessGroup
from hashtopolis import Agent
from hashtopolis import Cracker
from hashtopolis import CrackerType
from hashtopolis import File
from hashtopolis import GlobalPermissionGroup
from hashtopolis import Hashlist
from hashtopolis import HealthCheck
from hashtopolis import HashType
from hashtopolis import Notification
from hashtopolis import Pretask
from hashtopolis import Supertask
from hashtopolis import Task
from hashtopolis import User
from hashtopolis import Voucher


logger = logging.getLogger(__name__)
click_log.basic_config(logger)

ALL_MODELS = [x[1] for x in inspect.getmembers(hashtopolis, inspect.isclass)
              if issubclass(x[1], hashtopolis.Model) and x[1] is not hashtopolis.Model]


@click.group()
def main():
    pass


@main.group()
def run():
    pass


@run.command()
@click.option('-c', '--commit', is_flag=True, help="Non-interactive mode")
@click_log.simple_verbosity_option(logger)
def delete_test_data(commit):
    if commit is False:
        prefix = '[DRY-RUN]'
        logger.warning("Dry-run, use --commit to apply changes to database")
    else:
        prefix = ''

    # Order matters, for example a Task needs to be removed before Hashlist can be removed
    # Note: we are not removing default database objects
    test_objs = []
    test_objs.extend(HashType.objects.filter(hashTypeId=98765))
    test_objs.extend(AccessGroup.objects.filter(groupName="Testing Group"))
    test_objs.extend(Notification.objects.all())
    test_objs.extend(HealthCheck.objects.all())
    test_objs.extend(Agent.objects.all())
    test_objs.extend(Voucher.objects.all())
    test_objs.extend(Supertask.objects.all())
    test_objs.extend(Task.objects.all())
    test_objs.extend(Pretask.objects.all())
    test_objs.extend(Hashlist.objects.all())
    test_objs.extend(File.objects.all())
    test_objs.extend(User.objects.filter(id__gt=1))
    test_objs.extend(GlobalPermissionGroup.objects.filter(id__gt=1))
    test_objs.extend(Cracker.objects.filter(_id__gt=1))
    test_objs.extend(CrackerType.objects.filter(_id__gt=1))

    for obj in test_objs:
        logger.warning("%s Deleting %s", prefix, obj)
        if commit is True:
            obj.delete()


@main.command()
@click.argument('model_plural', type=click.Choice([x.verbose_name_plural for x in ALL_MODELS], case_sensitive=True))
@click.option('-b', '--brief', 'is_brief', is_flag=True, help="Condense output to list of items")
@click.option('--expand', 'opt_expand', help="Comma seperated list of items to expand", multiple=True)
@click.option('--fields', 'opt_fields', help="Comma seperated list of fields to display", multiple=True)
@click.option('--filter', 'opt_filter', help="Filter objects based on filter provided", multiple=True)
@click_log.simple_verbosity_option(logger)
def list(model_plural, is_brief, opt_expand, opt_fields, opt_filter):
    model_class = [x for x in ALL_MODELS if x.verbose_name_plural == model_plural][0]

    def get_opt_list(options):
        if options:
            # Options can be specified with comma sperators or as multiple --<name> options
            return ','.join(options).split(',')
        else:
            return ()

    # Parse options and arguments
    expand = get_opt_list(opt_expand)  
    filter = dict([filter_item.split('=', 1) for filter_item in get_opt_list(opt_filter) if filter_item])
    display_field_filter = get_opt_list(opt_fields)

    # Retrieve objects
    if not opt_filter:
        objs = model_class.objects.all(expand)
    else:
        objs = model_class.objects.filter(expand, **filter)

    # Display objects
    if is_brief is True:
        rows = []
        if display_field_filter:
            header = ['object_uri'] + display_field_filter
            rows.append(header)
        for obj in objs:
            row = [str(obj)] + [getattr(obj, field) for field in display_field_filter]
            rows.append(map(str, row))

        for row in rows:
            print(' || '.join(row))
    else:
        export = []
        for obj in objs:
            obj_dict = obj.serialize()
            if display_field_filter:
                export.append(dict([(k, v) for k, v in obj_dict.items() if k in display_field_filter]))
            else:
                export.append(obj_dict)
        print(json.dumps(export, indent=4))


if __name__ == '__main__':
    logging.basicConfig()
    main()
