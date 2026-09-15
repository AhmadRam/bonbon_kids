<?php

$slugOrURLKey = 'حقيبة-الطبيبة-الصغيرة';
if (! preg_match('/^([\p{L}\p{N}\p{M}\x{0900}-\x{097F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}_ \+\-]+\/?)+$/u', $slugOrURLKey)) {
    echo "REGEX FAILED\n";
} else {
    echo "REGEX PASSED\n";
}
