<?php

namespace gaswelder\htmlparser\dom;

use gaswelder\htmlparser\dom\ElementNode;

class Spec
{
    static function isVoid(ElementNode $node)
    {
        $voidElements = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'keygen',
            'link',
            'menuitem',
            'meta',
            'param',
            'source',
            'track',
            'wbr',
        ];
        return in_array(strtolower($node->tagName()), $voidElements);
    }

    static function isBlock(ElementNode $node)
    {
        return !self::isInline($node);
    }

    static function isInline(ElementNode $node)
    {
        $inline = [
            'a',
            'abbr',
            'b',
            'bdi',
            'bdo',
            'br',
            'cite',
            'code',
            'data',
            'del',
            'dfn',
            'em',
            'i',
            'img',
            'ins',
            'kbd',
            'mark',
            'q',
            's',
            'samp',
            'small',
            'span',
            'strong',
            'sub',
            'sup',
            'time',
            'u',
            'var',
            'wbr',
        ];
        return in_array(strtolower($node->tagName()), $inline);
    }
}
