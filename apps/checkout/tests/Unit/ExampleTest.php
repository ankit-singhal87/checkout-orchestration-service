<?php

it('creates isolated test namespaces', function () {
    expect(checkoutTestNamespace('Example Test'))->toContain(checkoutTestRunId());
});
