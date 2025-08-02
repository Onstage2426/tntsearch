<?php
namespace TeamTNT\TNTSearch\Tokenizer;

abstract class AbstractTokenizer
{
    protected static $pattern = '';

    public function getPattern()
    {
        if (empty(static::$pattern)) {
            throw new \LogicException("Tokenizer must define split \$pattern value");
        }

        return static::$pattern;
    }
}
