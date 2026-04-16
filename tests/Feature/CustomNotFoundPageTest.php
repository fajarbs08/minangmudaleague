<?php

test('missing pages render the custom 404 view', function () {
    $response = $this->get('/this-page-does-not-exist');

    $response
        ->assertNotFound()
        ->assertSee('Page Not Found!', false)
        ->assertSee('/images/404.svg', false)
        ->assertDontSee('Sorry, the page you are looking for could not be found.', false);
});
