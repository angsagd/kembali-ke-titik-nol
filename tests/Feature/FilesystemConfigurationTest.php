<?php

test('public filesystem url follows application url', function () {
    expect(config('filesystems.disks.public.url'))
        ->toBe(rtrim((string) config('app.url'), '/').'/storage');
});
