<?php

$route = app('router');

$route->get('/data/{uuid?}', 'Kepuasan@index')->name('data');
