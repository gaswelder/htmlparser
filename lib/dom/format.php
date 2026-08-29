<?php

namespace gaswelder\htmlparser\dom;

use Exception;

function format(Node $n)
{
    if ($n instanceof DocumentNode) {
        $dt = $n->doctype();
        $s = "";
        if ($dt != "") {
            $s = "<!DOCTYPE $dt>\n";
        }
        foreach ($n->childNodes() as $c) {
            $q = trim(format($c));
            if ($q == "") {
                continue;
            }
            $s .= $q . "\n";
        }
        return trim($s);
    }
    if ($n instanceof ElementNode) {
        $tn = $n->tagName();
        $open = "<" . $tn;
        foreach ($n->attributes() as $k => $v) {
            $open .= " $k=\"$v\"";
        }
        $open .= ">";

        $mode = "";
        if (empty($n->childNodes()) && Spec::isVoid($n)) {
            $mode = "unit";
        } else if (strtolower($tn) == 'p' || Spec::isInline($n)) {
            $mode = "inline";
        } else {
            $mode = "block";
        }

        $s = $open;
        $close = "</$tn>";
        switch ($mode) {
            case "unit":
                return $s;
            case "inline":
                foreach ($n->childNodes() as $c) {
                    $s .= format($c);
                }
                $s .= $close;
                return $s;
            case "block":
                $s .= "\n";
                $lines = [];
                foreach ($n->childNodes() as $c) {
                    $x = trim(format($c));
                    if (trim($x == "")) {
                        continue;
                    }
                    $lines[] = indent($x);
                    if ($c instanceof ElementNode && $c->tagName() == "p") {
                        $lines[] = "";
                    }
                }
                if (count($lines) == 0) {
                    $s .= $close;
                    return $s;
                }
                if (count($lines) > 0 && $lines[count($lines) - 1] == '') {
                    array_pop($lines);
                }
                $s .= implode("\n", $lines) . "\n";
                $s .= $close;
                return $s;
        }
        throw new Exception("assert");
    }
    if ($n instanceof TextNode) {
        $t = $n->textContent();
        $p = $n->parentNode();
        if ($p instanceof ElementNode && strtolower($p->tagName()) == 'pre') {
            return $t;
        }
        $t = preg_replace('/\s+/s', ' ', $t);
        $t = htmlspecialchars($t);
        $t = str_replace(' ', '&nbsp;', $t);
        return $t;
    }
    if ($n instanceof CommentNode) {
        return "";
    }
    throw new Exception("unhandled node: " . get_class($n));
}

function reflow2(string $s)
{
    $words = preg_split('/\s+/s', $s);
    $chunks = array_chunk($words, 8);
    $lines = [];
    foreach ($chunks as $chunk) {
        $lines[] = implode(' ', $chunk);
    }
    return implode("\n", $lines);
}

function indent(string $s)
{
    $lines = explode("\n", $s);
    $lines = array_map(function ($line) {
        return "  " . $line;
    }, $lines);
    return implode("\n", $lines);
}
