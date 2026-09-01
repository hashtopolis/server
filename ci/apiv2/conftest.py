import os
import pytest


def pytest_addoption(parser):
    parser.addoption(
        '--test-dataset',
        action='store',
        default=os.getenv('HASHTOPOLIS_TEST_DATASET', 'synthetic'),
        choices=('synthetic', 'realworld'),
        help='Dataset profile used by the running Hashtopolis test instance.',
    )


def pytest_configure(config):
    config.addinivalue_line('markers', 'realworld: test requires the real-world dump dataset')
    config.addinivalue_line('markers', 'synthetic_only: test requires the synthetic/default test dataset')


def pytest_collection_modifyitems(config, items):
    dataset = config.getoption('--test-dataset')
    skip_realworld = pytest.mark.skip(reason='requires --test-dataset=realworld')
    skip_synthetic = pytest.mark.skip(reason='requires --test-dataset=synthetic')

    for item in items:
        if 'realworld' in item.keywords and dataset != 'realworld':
            item.add_marker(skip_realworld)
        if 'synthetic_only' in item.keywords and dataset != 'synthetic':
            item.add_marker(skip_synthetic)

if os.getenv('_PYTEST_RAISE', "0") != "0":

    @pytest.hookimpl(tryfirst=True)
    def pytest_exception_interact(call):
        raise call.excinfo.value

    @pytest.hookimpl(tryfirst=True)
    def pytest_internalerror(excinfo):
        raise excinfo.value
