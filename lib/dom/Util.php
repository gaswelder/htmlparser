<?php

namespace gaswelder\htmlparser\dom;

class Util
{
    static function indent(string $text): string
    {
        return str_replace("\n", "\n\t", $text);
    }
}
