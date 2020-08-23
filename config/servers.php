<?php

$shlinkrcPath = env('HOME') . DIRECTORY_SEPARATOR . '.shlinkrc';

if (!file_exists($shlinkrcPath)) {
    return [];
}

return json_decode(file_get_contents($shlinkrcPath), true);
