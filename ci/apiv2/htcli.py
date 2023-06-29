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
from hashtopolis import Agent, Task, Hashlist


logger = logging.getLogger(__name__)
click_log.basic_config(logger)

ALL_MODELS_PLURAL = [x[0] + 's' for x in inspect.getmembers(hashtopolis, inspect.isclass)
              if issubclass(x[1], hashtopolis.Model) and
              type(x[1]) != hashtopolis.Model]


@click.group()
def main():
    pass


@main.group()
def run():
    pass


@run.command()
@click.option('-c', '--commit', is_flag=True, help="Non-interactive mode")
@click_log.simple_verbosity_option(logger)
def delete_everything(commit):
    if commit is False:
        prefix = '[DRY-RUN]'
        logger.warning("Dry-run, use --commit to apply changes to database")
    else:
        prefix = ''

    for obj in Agent.objects.all():
        logger.warning("%s Deleting %s", prefix, obj)
        if commit is True:
            obj.delete()

    for obj in Hashlist.objects.all():
        logger.warning("%s Deleting %s", prefix, obj)
        if commit is True:
            obj.delete()

    for obj in Task.objects.all():
        logger.warning("%s Deleting %s", prefix, obj)
        if commit is True:
            obj.delete()


@main.command()
@click.argument('model', type=click.Choice(ALL_MODELS_PLURAL, case_sensitive=True))
@click.option('-j', '--json', 'export_json', is_flag=True, help="Output objects in JSON format")
# TODO: Add --filter option to filter objects
# TODO: Add --expand support for objects
@click.option('--fields', help="Comma seperated list of fields to display")
@click_log.simple_verbosity_option(logger)
def list(model, export_json, fields):
    model_class = getattr(hashtopolis, model[:-1])
    objs = model_class.objects.all()

    # List fields to display
    if fields:
        display_field_filter = fields.split(',')
    else:
        display_field_filter = []

    if export_json is True:
        export = []
        for obj in objs:
            obj_dict = obj.serialize()
            if fields:
                export.append(dict([(k, v) for k, v in obj_dict.items() if k in display_field_filter]))
            else:
                export.append(obj_dict)
        print(json.dumps(export, indent=4))
    else:
        rows = []
        if display_field_filter:
            header = display_field_filter
            rows.append(header)
        for obj in objs:
            row = [str(obj)] + [getattr(obj, field) for field in display_field_filter]
            rows.append(map(str, row))

        for row in rows:
            print(' || '.join(row))


if __name__ == '__main__':
    logging.basicConfig()
    main()
