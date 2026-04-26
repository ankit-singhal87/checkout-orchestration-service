<?php

it('returns a successful response from the default route', function () {
    $this->get('/')->assertRedirect('/shop');
});
