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
from hashtopolis import Agent, Hashlist, File, Pretask, Task


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

    for model in [Agent, Pretask, Task, Hashlist, File]:
        for obj in model.objects.all():
            logger.warning("%s Deleting %s", prefix, obj)
            if commit is True:
                obj.delete()


@main.command()
@click.argument('model_plural', type=click.Choice([x.verbose_name_plural for x in ALL_MODELS], case_sensitive=True))
@click.option('-b', '--brief', 'is_brief', is_flag=True, help="Condense output to list of items")
# TODO: Add --filter option to filter objects
# TODO: Add --expand support for objects
@click.option('--fields', help="Comma seperated list of fields to display")
@click_log.simple_verbosity_option(logger)
def list(model_plural, is_brief, fields):
    model_class = [x for x in ALL_MODELS if x.verbose_name_plural == model_plural][0]
    objs = model_class.objects.all()

    # List fields to display
    if fields:
        display_field_filter = fields.split(',')
    else:
        display_field_filter = []

    if is_brief is True:
        rows = []
        if display_field_filter:
            header = display_field_filter
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
            if fields:
                export.append(dict([(k, v) for k, v in obj_dict.items() if k in display_field_filter]))
            else:
                export.append(obj_dict)
        print(json.dumps(export, indent=4))


if __name__ == '__main__':
    logging.basicConfig()
    main()
